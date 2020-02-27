<?php declare(strict_types=1);

namespace Easir\ErrorNormalizerBundle\Normalizer;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UnifiedConstraintViolationListNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @see https://easir.io/errors/index.html */
    public const ZEBRA_DEFAULT_ERROR_CODE = 'validation.invalid';
    public const ZEBRA_ERROR_CODES = [
        // Code: validation.active_url
        // Explanation: The url must have a DNS record

        // Code: validation.after
        // Explanation: The date provided must be after a specific date

        // Code: validation.alpha
        // Explanation: The value can only contain letters

        // Code: validation.alpha_dash
        // Explanation: The value can only contain letters, numbers, and dashes

        // Code: validation.alpha_num
        // Explanation: The value can only contain letters and numbers

        // Code: validation.before
        // Explanation: The date provided must be before a specific date

        // Code: validation.numeric_between
        // Explanation: The number must be in a specific range
        Constraints\GreaterThanOrEqual::TOO_LOW_ERROR => 'validation.numeric_between',
        Constraints\LessThanOrEqual::TOO_HIGH_ERROR => 'validation.numeric_between',

        // Code: validation.length_between
        // Explanation: The value length must be in a specific range

        // Code: validation.boolean
        // Explanation: The value must be either true or false
        Constraints\IsFalse::NOT_FALSE_ERROR => 'validation.boolean',
        Constraints\IsTrue::NOT_TRUE_ERROR => 'validation.boolean',

        // Code: validation.date
        // Explanation: The value must be a valid date
        Constraints\Date::INVALID_DATE_ERROR => 'validation.date',
        Constraints\Date::INVALID_FORMAT_ERROR => 'validation.date',

        // Code: validation.email
        // Explanation: The value must be a valid email
        Constraints\Email::INVALID_FORMAT_ERROR => 'validation.email',

        // Code: validation.invalid
        // Explanation: The value is invalid

        // Code: validation.image
        // Explanation: The file must be a image

        // Code: validation.integer
        // Explanation: The value must be a integer

        // Code: validation.ip
        // Explanation: The value must be a valid IP
        Constraints\Ip::INVALID_IP_ERROR => 'validation.ip',

        // Code: validation.numeric_max
        // Explanation: The number must be under a specific value
        Constraints\LessThan::TOO_HIGH_ERROR => 'validation.numeric_max',

        // Code: validation.filesize_max
        // Explanation: The filesize must be under a specific value
        Constraints\File::TOO_LARGE_ERROR => 'validation.filesize_max',

        // Code: validation.length_max
        // Explanation: The value length must be under a specific value
        Constraints\Length::TOO_LONG_ERROR => 'validation.length_max',

        // Code: validation.numeric_min
        // Explanation: The number must be over a specific value
        Constraints\GreaterThan::TOO_LOW_ERROR => 'validation.numeric_min',

        // Code: validation.filesize_min
        // Explanation: The filesize must be over a specific value
        Constraints\File::EMPTY_ERROR => 'validation.filesize_min',

        // Code: validation.length_min
        // Explanation: The value length must be over a specific value
        Constraints\Length::TOO_SHORT_ERROR => 'validation.length_min',

        // Code: validation.numeric
        // Explanation: The value must be numeric

        // Code: validation.required
        // Explanation: The field is required
        Constraints\Collection::MISSING_FIELD_ERROR => 'validation.required',

        // Code: validation.exists
        // Explanation: The value is already taken
        Constraints\Unique::IS_NOT_UNIQUE => 'validation.exists',

        // Code: validation.url
        // Explanation: The value must be a valid URL
        Constraints\Url::INVALID_URL_ERROR => 'validation.url',

        // Code: validation.timezone
        // Explanation: The value must be a valid timezone
        Constraints\Timezone::TIMEZONE_IDENTIFIER_ERROR => 'validation.timezone',
        Constraints\Timezone::TIMEZONE_IDENTIFIER_IN_COUNTRY_ERROR => 'validation.timezone',
        Constraints\Timezone::TIMEZONE_IDENTIFIER_IN_ZONE_ERROR => 'validation.timezone',
        Constraints\Timezone::TIMEZONE_IDENTIFIER_INTL_ERROR => 'validation.timezone',
    ];

    /** @var NameConverterInterface|null */
    private $nameConverter;

    public function __construct(?NameConverterInterface $nameConverter = null)
    {
        $this->nameConverter = $nameConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $errors = [];
        /** @var ConstraintViolationListInterface $object */
        /** @var ConstraintViolationInterface $violation */
        foreach ($object as $violation) {
            $propertyPath = $this->nameConverter ?
                $this->nameConverter->normalize($violation->getPropertyPath()) :
                $violation->getPropertyPath();

            $errors[] = [
                'message' => $violation->getMessage(),
                'code' => self::ZEBRA_ERROR_CODES[$violation->getCode()] ?? self::ZEBRA_DEFAULT_ERROR_CODE,
                'field' => $propertyPath,
            ];
        }

        return ['errors' => $errors];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ConstraintViolationListInterface && $format === 'json';
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return static::class === self::class;
    }

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data    Data to restore
     * @param string $type    The expected class to instantiate
     * @param string $format  Format the given data was extracted from
     * @param array|mixed[] $context Options available to the denormalizer
     *
     * @return object|ConstraintViolationList
     */
    public function denormalize($data, $type, $format = null, array $context = []): ConstraintViolationList
    {
        $constraintViolationList = new ConstraintViolationList();
        if (empty($data['errors'])) {
            return $constraintViolationList;
        }
        foreach ($data['errors'] as $error) {
            if (empty($error['message']) || empty($error['field'])) {
                continue;
            }
            $propertyPath = $this->nameConverter ?
                $this->nameConverter->denormalize($error['field']) :
                $error['field'];

            $code = !empty($error['code']) && \in_array($error['code'], self::ZEBRA_ERROR_CODES, true) ?
                \array_search($error['code'], self::ZEBRA_ERROR_CODES, true) :
                null;

            $constraintViolation = new ConstraintViolation(
                (string) $error['message'],
                null,
                [],
                [],
                $propertyPath,
                null,
                null,
                $code
            );
            $constraintViolationList->add($constraintViolation);
        }

        return $constraintViolationList;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed $data Data to denormalize from
     * @param string $type The class to which the data should be denormalized
     * @param string $format The format being deserialized from
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return \is_array($data) &&
            \array_key_exists('errors', $data) &&
            \is_a($type, ConstraintViolationList::class, true);
    }
}
