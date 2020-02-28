<?php declare(strict_types = 1);

namespace Easir\ErrorNormalizerBundle\NameConverter;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class FieldNameConverter implements NameConverterInterface
{
    /**
     * @inheritDoc
     */
    public function normalize($propertyName)
    {
        return \trim(\str_replace('][', '.', $propertyName), '[]');
    }

    /**
     * @inheritDoc
     */
    public function denormalize($propertyName)
    {
        return \sprintf(
            '[%s]',
            \implode('][', \explode('.', $propertyName))
        );
    }
}
