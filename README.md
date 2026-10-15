# CakePHP 2 Datasources Plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/datasources?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/datasources?label=Packagist)](https://packagist.org/packages/pieceofcake2/datasources)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/datasources/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/datasources/actions/workflows/CI.yml)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/datasources/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/datasources/CI.yml?label=CI)](https://github.com/pieceofcake2/datasources/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/datasources?label=Coverage)](https://codecov.io/gh/pieceofcake2/datasources)

This plugin contains various datasources contributed by the core CakePHP team and the
community, including ArraySource for records defined in a model and CsvSource for CSV and TSV
files. This is a fork of [cakephp/datasources](https://github.com/cakephp/datasources) for
[pieceofcake2/cakephp](https://github.com/pieceofcake2/cakephp).

## Documentation

- Website: https://pieceofcake2.github.io/datasources/
- Markdown: [docs/README.md](docs/README.md) (for AI tools and offline reading)

The Markdown documentation is also included in the Composer package, in the plugin's directory: `plugins/Datasources/docs/README.md` (the location depends on your `installer-paths`). Install the package with `--dev` if you only need it for development.

## Requirements

* PHP 8.0+
* pieceofcake2/cakephp 2.12+

## Installation

```bash
composer require pieceofcake2/datasources
```

## Reporting Issues

Please open an issue on [GitHub](https://github.com/pieceofcake2/datasources/issues).

## Contributing

See the [contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).

## Origin

This package is a maintained fork of [cakephp/datasources](https://github.com/cakephp/datasources)
(`cakephp/datasources` on Packagist, last upstream commit
[`b741921`](https://github.com/cakephp/datasources/commit/b741921) on `master`), originally by the
CakePHP community (Cake Software Foundation, Inc.). It is maintained for CakePHP 2 on modern PHP by
[pieceofcake2](https://github.com/pieceofcake2). Changes from the original are listed in the
[commit history since the fork](https://github.com/pieceofcake2/datasources/compare/b741921...2.x).

## License

The MIT License. See [LICENSE](LICENSE).
