# Datasources Documentation

This plugin contains various datasources contributed by the core CakePHP team and the
community.

- [Installation](#installation)
- [Available Datasources](#available-datasources)
- [Using the Datasources](#using-the-datasources)
- [ArraySource](#arraysource)
- [CsvSource](#csvsource)
- [Development](#development)

## Installation

Install the plugin with [Composer](https://getcomposer.org/):

```
composer require pieceofcake2/datasources
```

The plugin has the `cakephp-plugin` type, so it is installed into your plugins directory
(as `Datasources`) by `composer/installers`. Set `installer-paths` in your application's
`composer.json` so it is installed into the right place:

| Layout | `installer-paths` |
| --- | --- |
| Modern (`config/`, `src/`, `templates/`, `plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| Legacy (`app/Config/`, `app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

Load the plugin in `config/bootstrap.php` (legacy layout: `app/Config/bootstrap.php`).
If you already use `CakePlugin::loadAll()`, this is not necessary:

```php
CakePlugin::load('Datasources');
```

## Available Datasources

Compatible with pieceofcake2/cakephp:

* ArraySource
* CsvSource

**Note:** Only ArraySource and CsvSource are expected to work properly. Other datasources
may not function correctly.

Still incompatible:

* XmlrpcSource (tests require an external server)
* Database/MysqlLog
* CouchdbSource
* LdapSource (requires the `ldap` extension)
* SoapSource
* Database/Adodb
* Database/Db2
* Database/Firebird
* Database/Odbc

## Using the Datasources

The datasources can be used by defining them in your database configuration,
`config/database.php` (legacy layout: `app/Config/database.php`):

```php
class DATABASE_CONFIG
{
    public $mySource = [
        'datasource' => 'Datasources.XmlrpcSource',
        // ...
    ];
}
```

or with `App::uses()`:

```php
App::uses('XmlrpcSource', 'Datasources.Model/Datasource');
```

or, for one of the PDO based database datasources:

```php
class DATABASE_CONFIG
{
    public $mySource = [
        'datasource' => 'Datasources.Database/Firebird',
        // ...
    ];
}
```

## ArraySource

A read-only datasource for records defined in the model's `$records` property. Useful for
small, fixed lists such as countries or statuses.

```php
class DATABASE_CONFIG
{
    public $array = [
        'datasource' => 'Datasources.ArraySource',
    ];
}
```

```php
class Country extends AppModel
{
    public $useDbConfig = 'array';

    public $records = [
        ['id' => 1, 'name' => 'USA'],
        ['id' => 2, 'name' => 'Brazil'],
        ['id' => 3, 'name' => 'Germany'],
    ];
}

$this->Country->find('list');
$this->Country->find('all', ['conditions' => ['Country.id' => [1, 2]], 'order' => ['Country.name' => 'asc']]);
```

- Supported queries: `conditions`, `fields`, `order`, `limit`, `page` and `find('count')`,
  so the model works with pagination
- Associations between array models and database models are supported in both directions
- The schema is taken from the keys of the first record

## CsvSource

A read-only datasource for files with delimiter separated values (CSV, TSV). Each model reads
the file named after its `$useTable`, and the first line of the file is the header (column
names).

```php
class DATABASE_CONFIG
{
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

- Supported queries: conditions (equality, `IN` by an array value, `!=` / `<>`, `<`, `<=`,
  `>`, `>=`, nested `OR` / `AND` / `NOT`), `fields`, `limit`, `offset` and `page`. Numeric
  values are compared as numbers. Unsupported conditions (SQL strings, `LIKE` and so on)
  throw `InvalidArgumentException`
- `describe()` returns the schema (all columns are strings), so `hasField()` and the default
  `displayField` work
- A UTF-8 BOM in the header is removed. Rows with fewer columns than the header are skipped
- A missing file throws `MissingTableException`

## Development

### Running tests

```bash
./vendor/bin/phpunit
```

### Static analysis

```bash
./vendor/bin/phpstan analyse
```

### Testing the LDAP datasource

LDAP tests require a running LDAP server. You can start one with Docker Compose:

```bash
docker compose up -d
```

This starts:

- OpenLDAP server on port 389
- phpLDAPadmin on http://localhost:8080

You can access phpLDAPadmin to inspect the LDAP directory:

- URL: http://localhost:8080
- Login DN: `cn=admin,dc=cakephp,dc=org`
- Password: `password`

### Contributing a datasource

If you have a datasource, or an idea for a datasource that could benefit the CakePHP
community, fork the project on GitHub, commit your datasource class (and its test cases) and
send a pull request. See the
[contribution guidelines](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md).
