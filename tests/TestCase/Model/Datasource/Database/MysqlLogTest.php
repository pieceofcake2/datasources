<?php
/**
 * MysqlLog Test file
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Mysql', 'Model/Datasource/Database');
App::uses('MysqlLog', 'Datasources.Model/Datasource/Database');
App::uses('BaseLog', 'Log/Engine');

/**
 * Log engine that keeps the written messages in memory
 */
class MysqlLogTestLog extends BaseLog
{
    /**
     * Written messages
     *
     * @var array
     */
    public static $messages = [];

    /**
     * Stores the message
     *
     * @param string $type Message type
     * @param string $message Message
     * @return bool
     */
    public function write($type, $message): bool
    {
        static::$messages[] = [$type, $message];

        return true;
    }
}

/**
 * MysqlLog Test
 */
class MysqlLogTest extends CakeTestCase
{
    /**
     * Skip the tests when the test connection is not MySQL
     *
     * @return void
     */
    public function skip(): void
    {
        $this->skipIf(!(ConnectionManager::getDataSource('test') instanceof Mysql), 'MySQL connection not available');
    }

    /**
     * Set up
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        MysqlLogTestLog::$messages = [];
        CakeLog::config('mysql_log_test', ['engine' => 'MysqlLogTestLog', 'scopes' => ['sql']]);
    }

    /**
     * Tear down
     *
     * @return void
     */
    public function tearDown(): void
    {
        CakeLog::drop('mysql_log_test');
        Configure::delete('logQueries');
        parent::tearDown();
    }

    /**
     * testLogQuery
     *
     * @return void
     */
    public function testLogQuery(): void
    {
        $db = new MysqlLog(ConnectionManager::getDataSource('test')->config);

        $db->logQuery('SELECT 1');
        $this->assertSame([], MysqlLogTestLog::$messages);

        Configure::write('logQueries', true);
        $db->logQuery('SELECT 2');
        $this->assertCount(1, MysqlLogTestLog::$messages);
        $this->assertSame('sql', MysqlLogTestLog::$messages[0][0]);
        $this->assertStringContainsString('SELECT 2', MysqlLogTestLog::$messages[0][1]);
    }
}
