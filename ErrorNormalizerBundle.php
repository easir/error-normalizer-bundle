<?php declare(strict_types = 1);

namespace Easir\ErrorNormalizerBundle;

use Easir\ErrorNormalizerBundle\DependencyInjection\ErrorNormalizerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ErrorNormalizerBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new ErrorNormalizerExtension();
    }
}
