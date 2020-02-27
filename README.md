# Unified Error Normalizer Bundle

Symfony bundle which provides Unified Error Normalizer allowing to easily normalize/denormalize `ConstraintViolationList` from Symfony's Validator component.

## Status

This package is currently in the active development.

## Requirements

* [PHP 7.2](http://php.net/releases/7_2_0.php) or greater
* [Symfony 4.4](https://symfony.com/roadmap/4.4) or [Symfony 5.x](https://symfony.com/roadmap/5.0)

## Example

Normalization example:

```php
use Easir\ErrorNormalizerBundle\NameConverter\FieldNameConverter;
use Easir\ErrorNormalizerBundle\Normalizer\UnifiedConstraintViolationListNormalizer;use Symfony\Component\Validator\ConstraintViolation;use Symfony\Component\Validator\ConstraintViolationList;

$normalizer = new UnifiedConstraintViolationListNormalizer(new FieldNameConverter());

/** @var Symfony\Component\Validator\Validator\ValidatorInterface $validator */
$constraintViolationList = $validator->validate($data);
$errors = $this->normalizer->normalize($data, ConstraintViolationList::class);
```

Normalization process will return an array with like:

```php
$data = [
    'errors' => [
        [
            'message' => 'Error message here',
            'code' => 'validation.email',
            'field' => 'contact.email',
        ],
    ],
];
```

> *Note!* This bundle provides Symfony's DependencyInjection service definition 
> which means it should be possible to use Symfony's Serializer component without 
> explicitly creating the normalizer.

## Installation

This bundle is being served only from private repository therefore it is required to add proper repository into your `composer.json` file.

```json

{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/easir/error-normalizer-bundle.git"
        }
    ]
}
```

Require the bundle implementation with Composer:

```sh
composer require easir/error-normalizer-bundle
```

## Development

Install all the needed packages required to develop the project:

```sh
composer install
```

> *Note!* This bundle is developed against different versions of Symfony, please remember to run the tests with lowest dependencies.
> You can easily achieve that by running dependency installation with `--prefer-lowest`.

### Testing

You can run the test suite using the following command:

```sh
vendor/bin/phpunit
```

### Code Style

This bundle enforces the Easir code standards during development using the [PHP CS Fixer](https://cs.sensiolabs.org/) utility. Before committing any code, you can run the utility so it can fix any potential rule violations for you:

```sh
vendor/bin/phpcs
```

## Reporting issues

Use the [issue tracker](https://github.com/easir/error-normalizer-bundle/issues) to report any issues you might have.

## License

See the [LICENSE](LICENSE.md) file for license rights and limitations (MIT).
