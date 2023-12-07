# Silo: Modular Storage Connectors

### Transparently access different storage providers with a unified interface.

Currently, supports:
- Google Drive
- Confluence

## Installation

You can install the package via composer:

```bash
composer require jscott-rawnet/silo-storage-connectors
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="silo-storage-connectors-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
TBD
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Joe Scott](https://github.com/jscott-rawnet)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
