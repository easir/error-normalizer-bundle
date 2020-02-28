<?php

namespace Easir\ErrorNormalizerBundle\Tests\Normalizer;

use Easir\ErrorNormalizerBundle\NameConverter\FieldNameConverter;
use Easir\ErrorNormalizerBundle\Normalizer\UnifiedConstraintViolationListNormalizer;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListNormalizerTest extends TestCase
{
    /** @var UnifiedConstraintViolationListNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new UnifiedConstraintViolationListNormalizer(new FieldNameConverter());
    }

    /**
     * @dataProvider dataUniqueErrorCodes
     * @param string $code
     * @param string $zebraCode
     */
    public function testDenormalize(string $code, string $zebraCode): void
    {
        $data = [
            'errors' => [
                [
                    'message' => 'a',
                    'code' => $zebraCode,
                    'field' => 'b.c',
                ],
            ],
        ];
        $expected = new ConstraintViolationList([
            new ConstraintViolation(
                'a',
                null,
                [],
                [],
                '[b][c]',
                null,
                null,
                $code
            )
        ]);
        $this->assertEquals($expected, $this->normalizer->denormalize($data, ConstraintViolationList::class));
    }

    /**
     * @return array|mixed[]
     */
    public function dataUniqueErrorCodes(): array
    {
        $returned = [];
        $data = [];
        foreach (UnifiedConstraintViolationListNormalizer::ZEBRA_ERROR_CODES as $code => $zebraCode) {
            if (\in_array($zebraCode, $returned, true)) {
                continue;
            }
            $data[] = [$code, $zebraCode];
            $returned[] = $zebraCode;
        }

        return $data;
    }

    public function testSupportsDenormalization()
    {
        $this->assertTrue($this->normalizer->supportsDenormalization(['errors' => []], ConstraintViolationList::class));
        $this->assertFalse($this->normalizer->supportsDenormalization([], ConstraintViolationList::class));
    }

    /**
     * @dataProvider dataErrorCodes
     * @param string $code
     * @param string $zebraCode
     * @throws ExceptionInterface
     */
    public function testNormalize(string $code, string $zebraCode): void
    {
        $list = new ConstraintViolationList([
            new ConstraintViolation(
                'a',
                'b',
                ['value' => 'foo'],
                'c',
                '[d][e]',
                'f',
                null,
                $code
            ),
        ]);
        $expected = [
            'errors' => [
                [
                    'message' => 'a',
                    'code' => $zebraCode,
                    'field' => 'd.e',
                ],
            ],
        ];
        $this->assertEquals($expected, $this->normalizer->normalize($list));
    }

    /**
     * @return array|mixed[]
     */
    public function dataErrorCodes(): array
    {
        $data = [];
        foreach (UnifiedConstraintViolationListNormalizer::ZEBRA_ERROR_CODES as $code => $zebraCode) {
            $data[] = [$code, $zebraCode];
        }

        return $data;
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new ConstraintViolationList(), 'json'));
        $this->assertFalse($this->normalizer->supportsNormalization(new stdClass(), 'json'));
    }
}
