<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP Datasources v 0.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Model for User.csv
 */
class CsvUser extends CakeTestModel
{
    public $useTable = 'User';

    public $useDbConfig = 'test_csv';
}

/**
 * Model for Blog.csv (semicolon separated, non numeric primary key)
 */
class CsvBlog extends CakeTestModel
{
    public $useTable = 'Blog';

    public $useDbConfig = 'test_csv';

    public $primaryKey = 'key';
}

/**
 * Model for Tsv/Language.tsv
 */
class TsvLanguage extends CakeTestModel
{
    public $useTable = 'Language';

    public $useDbConfig = 'test_tsv';
}

/**
 * Model for Tsv/Bom.tsv
 */
class TsvBom extends CakeTestModel
{
    public $useTable = 'Bom';

    public $useDbConfig = 'test_tsv';

    public $primaryKey = 'code';
}

class CsvSourceTest extends CakeTestCase
{
    private const MODELS = ['CsvUser', 'CsvBlog', 'TsvLanguage', 'TsvBom'];

    /**
     * @var array<string, mixed>
     */
    private array $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->config = [
            'datasource' => 'Datasources.CsvSource',
            'path' => CakePlugin::path('Datasources') . 'tests' . DS . 'File',
            'extension' => 'csv',
        ];
        ConnectionManager::create('test_csv', $this->config);
        ConnectionManager::create('test_tsv', array_merge($this->config, [
            'path' => $this->config['path'] . DS . 'Tsv',
            'extension' => 'tsv',
            'delimiter' => "\t",
        ]));
    }

    public function tearDown(): void
    {
        foreach (self::MODELS as $model) {
            ClassRegistry::removeObject($model);
        }
        ConnectionManager::drop('test_csv');
        ConnectionManager::drop('test_tsv');
        parent::tearDown();
    }

    public function testConnect(): void
    {
        $source = new CsvSource($this->config);

        $this->assertTrue($source->connected);
        $this->assertTrue($source->connect());
    }

    public function testNoAutoConnect(): void
    {
        $this->assertFalse((new CsvSource($this->config, false))->connected);
    }

    public function testConnectFailsWithoutFolder(): void
    {
        $source = new CsvSource(['path' => $this->config['path'] . DS . 'NotFound'] + $this->config);

        $this->assertFalse($source->connected);
    }

    public function testClose(): void
    {
        $source = new CsvSource($this->config);

        $this->assertTrue($source->close());
        $this->assertFalse($source->connected);
    }

    public function testListSources(): void
    {
        $source = new CsvSource($this->config);
        $source->cacheSources = false;

        $this->assertSame(['Blog', 'Post', 'User'], $source->listSources());
    }

    public function testListSourcesRecursive(): void
    {
        $source = new CsvSource(['recursive' => true] + $this->config);
        $source->cacheSources = false;

        $this->assertSame(['Blog', 'Post', 'User', 'SecondLevel' . DS . 'Thing'], $source->listSources());
    }

    public function testDescribe(): void
    {
        $schema = ClassRegistry::init('CsvUser')->schema();

        $this->assertSame(['id', 'name', 'age'], array_keys($schema));
        $this->assertSame('string', $schema['name']['type']);
    }

    public function testModelUsesSchema(): void
    {
        $model = ClassRegistry::init('TsvLanguage');

        $this->assertTrue($model->hasField('name'));
        $this->assertFalse($model->hasField('missing'));
        $this->assertSame('name', $model->displayField);
    }

    public function testFindAll(): void
    {
        $expected = [
            ['CsvUser' => ['id' => '1', 'name' => 'predominant', 'age' => '29']],
            ['CsvUser' => ['id' => '2', 'name' => 'mr_sandman', 'age' => '21']],
            ['CsvUser' => ['id' => '3', 'name' => 'easter_bunny', 'age' => '202']],
        ];

        $this->assertSame($expected, ClassRegistry::init('CsvUser')->find('all'));
    }

    public function testFindFirst(): void
    {
        $model = ClassRegistry::init('CsvUser');

        $this->assertSame(['CsvUser' => ['id' => '1', 'name' => 'predominant', 'age' => '29']], $model->find('first'));
        $this->assertSame([], $model->find('first', ['conditions' => ['CsvUser.id' => 99]]));
    }

    public function testFindCount(): void
    {
        $model = ClassRegistry::init('CsvUser');

        $this->assertSame(3, $model->find('count'));
        $this->assertSame(2, $model->find('count', ['conditions' => ['CsvUser.age <' => 100]]));
    }

    public function testFindList(): void
    {
        $model = ClassRegistry::init('CsvUser');

        $this->assertSame(['1' => 'predominant', '2' => 'mr_sandman', '3' => 'easter_bunny'], $model->find('list'));
        $this->assertSame(['1' => '29', '2' => '21'], $model->find('list', [
            'fields' => ['CsvUser.id', 'CsvUser.age'],
            'conditions' => ['CsvUser.id <=' => 2],
        ]));
    }

    public function testFindFields(): void
    {
        $result = ClassRegistry::init('CsvUser')->find('all', ['fields' => ['name', 'CsvUser.age'], 'limit' => 1]);

        $this->assertSame([['CsvUser' => ['name' => 'predominant', 'age' => '29']]], $result);
    }

    /**
     * @return array<string, array{array, array<string>}>
     */
    public static function conditionsProvider(): array
    {
        return [
            'equal' => [['CsvUser.id' => 2], ['2']],
            'equal without alias' => [['name' => 'easter_bunny'], ['3']],
            'equal with integer' => [['CsvUser.age' => 21], ['2']],
            'not equal' => [['CsvUser.id !=' => 2], ['1', '3']],
            'not equal (<>)' => [['CsvUser.id <>' => 2], ['1', '3']],
            'less than' => [['CsvUser.id <' => 2], ['1']],
            'less than or equal' => [['CsvUser.id <=' => 2], ['1', '2']],
            'greater than (numeric)' => [['CsvUser.age >' => 29], ['3']],
            'greater than or equal' => [['CsvUser.age >=' => 29], ['1', '3']],
            'string comparison' => [['CsvUser.name >' => 'n'], ['1']],
            'in' => [['CsvUser.id' => [1, 3]], ['1', '3']],
            'not in' => [['CsvUser.id !=' => [1, 3]], ['2']],
            'multiple (AND)' => [['CsvUser.age <' => 100, 'CsvUser.name' => 'mr_sandman'], ['2']],
            'or' => [['OR' => ['CsvUser.id' => 1, 'CsvUser.name' => 'easter_bunny']], ['1', '3']],
            'or with in' => [['or' => ['CsvUser.id' => [1, 2]]], ['1', '2']],
            'nested and' => [['OR' => ['CsvUser.id' => 1, 'AND' => ['CsvUser.age >' => 20, 'CsvUser.age <' => 25]]], ['1', '2']],
            'not' => [['NOT' => ['CsvUser.id' => [1, 2]]], ['3']],
            'unknown field' => [['CsvUser.missing' => 1], []],
            'null' => [['CsvUser.name' => null], []],
            'not null' => [['CsvUser.name !=' => null], ['1', '2', '3']],
            'no match' => [['CsvUser.id' => 99], []],
        ];
    }

    /**
     * @dataProvider conditionsProvider
     * @param array $conditions
     * @param array<string> $expectedIds
     * @return void
     */
    public function testFindWithConditions(array $conditions, array $expectedIds): void
    {
        $result = ClassRegistry::init('CsvUser')->find('list', ['fields' => ['id', 'id'], 'conditions' => $conditions]);

        $this->assertSame($expectedIds, array_values(array_map('strval', $result)));
    }

    /**
     * @return array<string, array{array}>
     */
    public static function unsupportedConditionsProvider(): array
    {
        return [
            'SQL' => [['CsvUser.id = 1']],
            'operator' => [['CsvUser.name LIKE' => 'mr%']],
        ];
    }

    /**
     * @dataProvider unsupportedConditionsProvider
     * @param array $conditions
     * @return void
     */
    public function testUnsupportedConditionsThrowException(array $conditions): void
    {
        $this->expectException(InvalidArgumentException::class);

        ClassRegistry::init('CsvUser')->find('all', ['conditions' => $conditions]);
    }

    /**
     * @return array<string, array{array, array<string>}>
     */
    public static function paginationProvider(): array
    {
        return [
            'limit' => [['limit' => 2], ['1', '2']],
            'limit and page' => [['limit' => 2, 'page' => 2], ['3']],
            'limit and offset' => [['limit' => 1, 'offset' => 1], ['2']],
            'page out of range' => [['limit' => 2, 'page' => 3], []],
        ];
    }

    /**
     * @dataProvider paginationProvider
     * @param array $query
     * @param array<string> $expectedIds
     * @return void
     */
    public function testFindWithPagination(array $query, array $expectedIds): void
    {
        $result = ClassRegistry::init('CsvUser')->find('all', $query);

        $this->assertSame($expectedIds, array_column(array_column($result, 'CsvUser'), 'id'));
    }

    public function testDetectsSemicolonAndSkipsIncompleteRows(): void
    {
        $model = ClassRegistry::init('CsvBlog');

        $this->assertSame(['1st' => '1st Blog', 'myblog' => 'Hello World!'], $model->find('list', ['fields' => ['key', 'title']]));
        $this->assertSame(['CsvBlog' => ['key' => 'myblog', 'title' => 'Hello World!']], $model->find('first', [
            'conditions' => ['CsvBlog.key' => 'myblog'],
        ]));
    }

    public function testDelimiterOption(): void
    {
        $expected = ['1' => 'English', '2' => 'Chinese, Mandarin', '3' => 'French; Belgian'];

        $this->assertSame($expected, ClassRegistry::init('TsvLanguage')->find('list'));
    }

    public function testDetectsTab(): void
    {
        $source = new CsvSource(['path' => $this->config['path'] . DS . 'Tsv', 'extension' => 'tsv'] + $this->config);

        $this->assertSame(['id', 'name'], array_keys($source->describe('Language')));
    }

    public function testRemovesBomAndHandlesCrlf(): void
    {
        $model = ClassRegistry::init('TsvBom');

        $this->assertSame(['code', 'label'], array_keys($model->schema()));
        $this->assertSame([
            ['TsvBom' => ['code' => 'A', 'label' => 'Alpha']],
            ['TsvBom' => ['code' => 'B', 'label' => 'Beta']],
        ], $model->find('all'));
    }

    public function testMissingFileThrowsException(): void
    {
        $this->expectException(MissingTableException::class);

        (new CsvSource($this->config))->describe('NotFound');
    }
}
