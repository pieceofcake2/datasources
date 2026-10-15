# Datasources ドキュメント

このプラグインには、CakePHP のコアチームとコミュニティが提供したさまざまなデータソースが
含まれています。

- [インストール](#installation)
- [利用できるデータソース](#available-datasources)
- [データソースを使う](#using-the-datasources)
- [ArraySource](#arraysource)
- [CsvSource](#csvsource)
- [開発](#development)

<a id="installation"></a>

## インストール

[Composer](https://getcomposer.org/) でプラグインをインストールします。

```bash
composer require pieceofcake2/datasources
```

プラグインのタイプは `cakephp-plugin` なので、`composer/installers` によってプラグインディレクトリに
(`Datasources` として) インストールされます。正しい場所にインストールされるように、
アプリケーションの `composer.json` で `installer-paths` を設定してください。

| レイアウト | `installer-paths` |
| --- | --- |
| モダン (`config/`、`src/`、`templates/`、`plugins/`) | `"plugins/{$name}/": ["type:cakephp-plugin"]` |
| レガシー (`app/Config/`、`app/Plugin/`) | `"app/Plugin/{$name}/": ["type:cakephp-plugin"]` |

`config/bootstrap.php` (レガシーレイアウトでは `app/Config/bootstrap.php`) でプラグインを読み込みます。
すでに `CakePlugin::loadAll()` を使っている場合は不要です。

```php
CakePlugin::load('Datasources');
```

<a id="available-datasources"></a>

## 利用できるデータソース

pieceofcake2/cakephp に対応しているもの:

* ArraySource
* CsvSource

**注意:** 正しく動作する見込みがあるのは ArraySource と CsvSource だけです。それ以外の
データソースは正しく動作しない可能性があります。

まだ対応していないもの:

* XmlrpcSource (テストには外部のサーバーが必要です)
* Database/MysqlLog
* CouchdbSource
* LdapSource (`ldap` 拡張モジュールが必要です)
* SoapSource
* Database/Adodb
* Database/Db2
* Database/Firebird
* Database/Odbc

<a id="using-the-datasources"></a>

## データソースを使う

データソースは、データベースの設定 `config/database.php`
(レガシーレイアウトでは `app/Config/database.php`) で定義して使うことができます。

```php
class DATABASE_CONFIG
{
    public $mySource = [
        'datasource' => 'Datasources.XmlrpcSource',
        // ...
    ];
}
```

または `App::uses()` で読み込みます。

```php
App::uses('XmlrpcSource', 'Datasources.Model/Datasource');
```

また、PDO ベースのデータベースのデータソースの場合は次のようにします。

```php
class DATABASE_CONFIG
{
    public $mySource = [
        'datasource' => 'Datasources.Database/Firebird',
        // ...
    ];
}
```

<a id="arraysource"></a>

## ArraySource

モデルの `$records` プロパティで定義したレコードを扱う、読み取り専用のデータソースです。
国やステータスのような、小さな固定のリストに便利です。

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

- 対応しているクエリは `conditions`、`fields`、`order`、`limit`、`page` と `find('count')` です。
  そのため、このモデルはページネーションでも使えます
- 配列のモデルとデータベースのモデルの間のアソシエーションは、どちらの方向にも対応しています
- スキーマは最初のレコードのキーから取得されます

<a id="csvsource"></a>

## CsvSource

区切り文字で区切られた値のファイル (CSV、TSV) を扱う、読み取り専用のデータソースです。
各モデルは `$useTable` の名前のファイルを読み込み、ファイルの 1 行目がヘッダー (カラム名) になります。

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

- 対応しているクエリは、conditions (等価、配列の値による `IN`、`!=` / `<>`、`<`、`<=`、
  `>`、`>=`、ネストした `OR` / `AND` / `NOT`)、`fields`、`limit`、`offset` と `page` です。
  数値は数値として比較されます。対応していない条件 (SQL の文字列、`LIKE` など) は
  `InvalidArgumentException` を投げます
- `describe()` はスキーマ (すべてのカラムが文字列) を返すので、`hasField()` とデフォルトの
  `displayField` が動作します
- ヘッダーの UTF-8 BOM は取り除かれます。ヘッダーよりカラムの少ない行はスキップされます
- ファイルが存在しない場合は `MissingTableException` を投げます

<a id="development"></a>

## 開発

<a id="running-tests"></a>

### テストの実行

```bash
./vendor/bin/phpunit
```

<a id="static-analysis"></a>

### 静的解析

```bash
./vendor/bin/phpstan analyse
```

<a id="testing-the-ldap-datasource"></a>

### LDAP データソースのテスト

LDAP のテストには、動作している LDAP サーバーが必要です。Docker Compose で起動できます。

```bash
docker compose up -d
```

これで次のものが起動します。

- ポート 389 の OpenLDAP サーバー
- http://localhost:8080 の phpLDAPadmin

テストはポート 389 の LDAP サーバーに接続します。別のポートで待ち受けている場合は、
環境変数 `LDAP_PORT` を設定してください。

```bash
LDAP_PORT=10389 ./vendor/bin/phpunit
```

phpLDAPadmin にアクセスして、LDAP ディレクトリの内容を確認できます。

- URL: http://localhost:8080
- ログイン DN: `cn=admin,dc=cakephp,dc=org`
- パスワード: `password`

<a id="contributing-a-datasource"></a>

### データソースの提供

CakePHP コミュニティの役に立つデータソースや、そのアイデアがあれば、GitHub でプロジェクトを
フォークし、データソースのクラス (とそのテストケース) をコミットして、プルリクエストを送ってください。
[コントリビューションガイドライン](https://github.com/pieceofcake2/.github/blob/master/CONTRIBUTING.md)
をご覧ください。

<a id="origin"></a>

## 由来

このパッケージは [cakephp/datasources](https://github.com/cakephp/datasources)
(Packagist では `cakephp/datasources`、アップストリームの最終コミットは `master` の
[`b741921`](https://github.com/cakephp/datasources/commit/b741921)) のメンテナンスされたフォークで、
元の作者は CakePHP コミュニティ (Cake Software Foundation, Inc.) です。
[pieceofcake2](https://github.com/pieceofcake2) が、モダンな PHP で動く CakePHP 2 向けに
メンテナンスしています。オリジナルからの変更点は
[フォーク以降のコミット履歴](https://github.com/pieceofcake2/datasources/compare/b741921...2.x)
で確認できます。
