## CakePHP 2 datasources plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/datasources?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/datasources?label=Packagist)](https://packagist.org/packages/pieceofcake2/datasources)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/datasources/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%205-brightgreen?label=PHPStan)](https://github.com/pieceofcake2/datasources/actions/workflows/CI.yml)
![CakePHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/datasources/pieceofcake2/cakephp?logo=cakephp&logoColor=%23FFFFFF&label=CakePHP&labelColor=%23D33C43&color=%23FFFFFF)
[![CI](https://img.shields.io/github/actions/workflow/status/pieceofcake2/datasources/CI.yml?label=CI)](https://github.com/pieceofcake2/datasources/actions/workflows/CI.yml)
[![Codecov](https://img.shields.io/codecov/c/gh/pieceofcake2/datasources?label=Coverage)](https://codecov.io/gh/pieceofcake2/datasources)

__This is forked for CakePHP2.__

This plugin contains various datasources contributed by the core CakePHP team and the community.

### Already compatible Datasources:

* ArraySource
* CsvSource

**Note:** Only ArraySource and CsvSource are expected to work properly. Other datasources may not function correctly.

### Still Incompatible Datasources:

* XmlrpcSource (tests require external server)
* Database/MysqlLog

* CouchdbSource
* LdapSource
* SoapSource
* Database/Adodb
* Database/Db2
* Database/Firebird
* Database/Odbc

### CsvSource

A read-only datasource for files with delimiter separated values (CSV, TSV). Each model reads the file named after its `$useTable`, and the first line of the file is the header (column names).

```php
class DATABASE_CONFIG {
    public $tsv = [
        'datasource' => 'Datasources.CsvSource',
        'path' => ROOT . DS . 'resources' . DS . 'tsv',
        'extension' => 'tsv',
        'delimiter' => "\t", // null (default) detects ',', ';' or "\t" from the header
    ];
}
```

```php
class Language extends AppModel
{
    public $useDbConfig = 'tsv';
    public $useTable = 'language'; // resources/tsv/language.tsv
}

$this->Language->find('list'); // ['1' => 'English', ...] (displayField is detected from the header)
$this->Language->find('first', ['conditions' => ['Language.id' => [1, 2]]]);
```

- Supported queries: conditions (equality, `IN` by an array value, `!=` / `<>`, `<`, `<=`, `>`, `>=`, nested `OR` / `AND` / `NOT`), `fields`, `limit`, `offset` and `page`. Numeric values are compared as numbers. Unsupported conditions (SQL strings, `LIKE` and so on) throw `InvalidArgumentException`
- `describe()` returns the schema (all columns are strings), so `hasField()` and the default `displayField` work
- A UTF-8 BOM in the header is removed. Rows with fewer columns than the header are skipped
- A missing file throws `MissingTableException`

### Using the datasources plugin

First download the repository and place it in `app/Plugin/Datasources` or on one of your plugin paths. You can then import and use the datasources in your App classes.

### Model validation

Datasource plugin datasources can be used either through App::uses of by defining them in your database configuration

```php
class DATABASE_CONFIG {
    public $mySource = array(
        'datasource' => 'Datasources.XmlrpcSource',
        ...
        );
    }
}
```

or

```php
App::uses('XmlrpcSource', 'Datasources.Model/Datasource');
```

or, if using one of the pdo extended datasources,

```php
class DATABASE_CONFIG {
    public $mySource = array(
        'driver' => 'Datasources.Database/Firebird',
        ...
        );
    }
}
```

## Testing

### Running tests

```bash
./vendor/bin/phpunit
```

### Static analysis

```bash
./vendor/bin/phpstan analyse
```

### Testing LDAP datasource

LDAP tests require a running LDAP server. You can start one using Docker Compose:

```bash
docker compose up -d
```

This will start:
- OpenLDAP server on port 389
- phpLDAPadmin on http://localhost:8080

You can access phpLDAPadmin to inspect the LDAP directory:
- URL: http://localhost:8080
- Login DN: `cn=admin,dc=cakephp,dc=org`
- Password: `password`

## Contributing to datasources

If you have a datasource, or an idea for a datasource that could benefit the CakePHP community, please fork the project on github. Once you have forked the project you can commit your datasource class (and any test cases). Once you have pushed your changes back to github you can send a pull request, and your changes will be reviewed and merged in or feedback will be given.

## Issues with datasources

If you have issues with the datasources plugin, you can report them via [Github issues](https://github.com/pieceofcake2/datasources/issues)
