<?php
/**
 * DboAdodbTest file
 *
 * AdoDB layer for DBO
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2009, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP Datasources v 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Model', 'Model');
App::uses('Adodb', 'Datasources.Model/Datasource/Database');

/**
 * DboAdoTestDb
 */
class DboAdoTestDb extends Adodb
{
    /**
     * simulated property
     *
     * @var array
     */
    public $simulated = [];

    /**
     * testing property
     *
     * @var bool
     */
    public $testing = true;

    /**
     * execute method
     *
     * @param string $sql
     * @param array $params
     * @param array $prepareOptions
     * @return resource
     */
    protected function _execute($sql, $params = [], $prepareOptions = []): ?ADORecordSet
    {
        if ($this->testing) {
            $this->simulated[] = $sql;

            return null;
        }

        return parent::_execute($sql);
    }

    /**
     * Sets the ADOdb data dictionary
     *
     * @param ADODB_DataDict|false|null $dataDict Data dictionary
     * @return void
     */
    public function setDataDictionary($dataDict): void
    {
        $this->_adodbDataDict = $dataDict;
    }

    /**
     * getLastQuery method
     *
     * @return array<string>
     */
    public function getLastQuery(): array
    {
        return $this->simulated[count($this->simulated) - 1];
    }
}

/**
 * AdodbTestModel
 */
class AdodbTestModel extends CakeTestModel
{
    /**
     * useTable property
     *
     * @var bool
     */
    public $useTable = false;

    /**
     * find method
     *
     * @param mixed $conditions
     * @param mixed $fields
     * @param mixed $order
     * @param mixed $recursive
     * @return mixed
     */
    public function find($conditions = null, $fields = null, $order = null, $recursive = null): mixed
    {
        return $conditions;
    }

    /**
     * findAll method
     *
     * @param mixed $conditions
     * @param mixed $fields
     * @param mixed $order
     * @param mixed $recursive
     * @return mixed
     */
    public function findAll($conditions = null, $fields = null, $order = null, $recursive = null): mixed
    {
        return $conditions;
    }

    /**
     * schema method
     *
     * @param string|bool $field Set to true to reload schema, or a string to return a specific field
     * @return array
     */
    public function schema($field = false): array
    {
        return [
            'id' => ['type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'],
            'client_id' => ['type' => 'integer', 'null' => '', 'default' => '0', 'length' => '11'],
            'name' => ['type' => 'string', 'null' => '', 'default' => '', 'length' => '255'],
            'login' => ['type' => 'string', 'null' => '', 'default' => '', 'length' => '255'],
            'passwd' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '255'],
            'addr_1' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '255'],
            'addr_2' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '25'],
            'zip_code' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '155'],
            'city' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '155'],
            'country' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '155'],
            'phone' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '155'],
            'fax' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '155'],
            'url' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '255'],
            'email' => ['type' => 'string', 'null' => '1', 'default' => '', 'length' => '155'],
            'comments' => ['type' => 'text', 'null' => '1', 'default' => '', 'length' => ''],
            'last_login' => ['type' => 'datetime', 'null' => '1', 'default' => '', 'length' => ''],
            'created' => ['type' => 'date', 'null' => '1', 'default' => '', 'length' => ''],
            'updated' => ['type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null],
        ];
    }
}

if (!class_exists('Article')) {
    /**
     * Article class
     */
    class Article extends CakeTestModel
    {
    }
}

/**
 * DboAdodbTest class
 */
class AdodbTest extends CakeTestCase
{
    /**
     * The Dbo instance to be tested
     *
     * @var DataSource
     */
    public $db = null;

    /**
     * fixtures property
     *
     * @var string
     */
    public $fixtures = ['core.article'];

    /**
     * Skip if cannot connect to AdoDb
     */
    public function skip(): void
    {
        $this->_initDb();
        $db = ConnectionManager::getDataSource('test');
        $this->skipIf($db->config['driver'] !== 'adodb', '%s Adodb connection not available');
    }

    /**
     * Sets up a Dbo class instance for testing
     */
    public function startTest($method): void
    {
        $db = ConnectionManager::getDataSource('test');
        $this->db = new DboAdoTestDb($db->config);
        $this->db->startQuote = '`';
        $this->db->endQuote = '`';
        $this->model = new AdodbTestModel();
    }

    /**
     * Sets up a Dbo class instance for testing
     */
    public function tearDown(): void
    {
        unset($this->db);
    }

    /**
     * Test Dbo value method
     */
    public function testQuoting(): void
    {
        $result = $this->db->fields($this->model);
        $expected = [
            '`AdodbTestModel`.`id` AS `AdodbTestModel__id`',
            '`AdodbTestModel`.`client_id` AS `AdodbTestModel__client_id`',
            '`AdodbTestModel`.`name` AS `AdodbTestModel__name`',
            '`AdodbTestModel`.`login` AS `AdodbTestModel__login`',
            '`AdodbTestModel`.`passwd` AS `AdodbTestModel__passwd`',
            '`AdodbTestModel`.`addr_1` AS `AdodbTestModel__addr_1`',
            '`AdodbTestModel`.`addr_2` AS `AdodbTestModel__addr_2`',
            '`AdodbTestModel`.`zip_code` AS `AdodbTestModel__zip_code`',
            '`AdodbTestModel`.`city` AS `AdodbTestModel__city`',
            '`AdodbTestModel`.`country` AS `AdodbTestModel__country`',
            '`AdodbTestModel`.`phone` AS `AdodbTestModel__phone`',
            '`AdodbTestModel`.`fax` AS `AdodbTestModel__fax`',
            '`AdodbTestModel`.`url` AS `AdodbTestModel__url`',
            '`AdodbTestModel`.`email` AS `AdodbTestModel__email`',
            '`AdodbTestModel`.`comments` AS `AdodbTestModel__comments`',
            '`AdodbTestModel`.`last_login` AS `AdodbTestModel__last_login`',
            '`AdodbTestModel`.`created` AS `AdodbTestModel__created`',
            '`AdodbTestModel`.`updated` AS `AdodbTestModel__updated`',
        ];
        $this->assertEquals($expected, $result);

        $expected = "'1.2'";
        $result = $this->db->value(1.2, 'float');
        $this->assertEquals($expected, $result);

        $expected = "'1,2'";
        $result = $this->db->value('1,2', 'float');
        $this->assertEquals($expected, $result);

        $expected = "'4713e29446'";
        $result = $this->db->value('4713e29446');
        $this->assertEquals($expected, $result);

        $expected = "'10010001'";
        $result = $this->db->value('10010001');
        $this->assertEquals($expected, $result);

        $expected = "'00010010001'";
        $result = $this->db->value('00010010001');
        $this->assertEquals($expected, $result);
    }

    /**
     * Test buildColumn with an ADOdb data dictionary
     */
    public function testBuildColumn(): void
    {
        $connection = adoNewConnection('mysqli');
        $this->db->setDataDictionary(NewDataDictionary($connection, 'mysql'));

        $result = $this->db->buildColumn(['name' => 'id', 'type' => 'integer', 'key' => 'primary']);
        $this->assertSame('`id` INTEGER(11) AUTO_INCREMENT', $result);

        $result = $this->db->buildColumn(['name' => 'title', 'type' => 'string', 'null' => false]);
        $this->assertSame('`title` VARCHAR(255) NOT NULL', $result);
    }

    /**
     * Test buildColumn quotes default values
     */
    public function testBuildColumnDefault(): void
    {
        $connection = adoNewConnection('mysqli');
        $this->db->setDataDictionary(NewDataDictionary($connection, 'mysql'));

        $result = $this->db->buildColumn(['name' => 'status', 'type' => 'string', 'null' => false, 'default' => 'draft']);
        $this->assertSame("`status` VARCHAR(255) NOT NULL DEFAULT 'draft'", $result);

        $result = $this->db->buildColumn(['name' => 'note', 'type' => 'string', 'null' => false, 'default' => "it's"]);
        $this->assertSame("`note` VARCHAR(255) NOT NULL DEFAULT 'it''s'", $result);

        $result = $this->db->buildColumn(['name' => 'count', 'type' => 'integer', 'null' => false, 'default' => '0']);
        $this->assertSame("`count` INTEGER(11) NOT NULL DEFAULT '0'", $result);

        $result = $this->db->buildColumn(['name' => 'title', 'type' => 'string', 'null' => false, 'default' => '']);
        $this->assertSame("`title` VARCHAR(255) NOT NULL DEFAULT ''", $result);

        $result = $this->db->buildColumn(['name' => 'status', 'type' => 'string', 'null' => true, 'default' => 'draft']);
        $this->assertSame("`status` VARCHAR(255) DEFAULT 'draft'", $result);

        $result = $this->db->buildColumn(['name' => 'status', 'type' => 'string', 'default' => 'draft']);
        $this->assertSame("`status` VARCHAR(255) DEFAULT 'draft'", $result);

        $result = $this->db->buildColumn(['name' => 'note', 'type' => 'string', 'null' => true]);
        $this->assertSame('`note` VARCHAR(255) DEFAULT NULL', $result);

        $result = $this->db->buildColumn(['name' => 'note', 'type' => 'string', 'null' => true, 'default' => null]);
        $this->assertSame('`note` VARCHAR(255) DEFAULT NULL', $result);
    }

    /**
     * Test buildColumn without an ADOdb data dictionary
     */
    public function testBuildColumnWithoutDataDictionary(): void
    {
        $this->db->setDataDictionary(false);

        $errors = [];
        set_error_handler(function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = [$errno, $errstr];

            return true;
        });
        try {
            $result = $this->db->buildColumn(['name' => 'id', 'type' => 'integer']);
        } finally {
            restore_error_handler();
        }

        $this->assertNull($result);
        $this->assertSame([[E_USER_WARNING, 'ADOdb data dictionary is not available']], $errors);
    }
}
