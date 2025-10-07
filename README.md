## CakePHP 2 datasources plugin

[![GitHub License](https://img.shields.io/github/license/pieceofcake2/datasources?label=License)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/pieceofcake2/datasources?label=Packagist)](https://packagist.org/packages/pieceofcake2/datasources)
![PHP](https://img.shields.io/packagist/dependency-v/pieceofcake2/datasources/php?logo=php&logoColor=%23FFFFFF&label=PHP&labelColor=%23777BB4&color=%23FFFFFF)
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
* Database/Sqlite3
* Database/Sqlsrv
* Database/Sybase

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

## Contributing to datasources

If you have a datasource, or an idea for a datasource that could benefit the CakePHP community, please fork the project on github. Once you have forked the project you can commit your datasource class (and any test cases). Once you have pushed your changes back to github you can send a pull request, and your changes will be reviewed and merged in or feedback will be given.

## Issues with datasources

If you have issues with the datasources plugin, you can report them via [Github issues](https://github.com/pieceofcake2/datasources/issues)
