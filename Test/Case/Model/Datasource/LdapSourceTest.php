<?php
/**
 * LDAP Datasource Test file
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         CakePHP Datasources v 0.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('ConnectionManager', 'Model');
App::uses('Model', 'Model');

/**
 * Import required classes
 */
class LdapPerson extends Model
{
    public $useDbConfig = 'test_ldap';

    public $useTable = 'ou=ldap_people';

    public $primaryKey = 'cn';
}

class LdapSourceTest extends CakeTestCase
{
    public $autoFixtures = false;

    public $fixtures = ['plugin.Datasources.ldapPerson'];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $config = [
            'datasource' => 'Datasources.LdapSource',
            'host' => '127.0.0.1',
            'port' => 389,
            'login' => 'cn=admin,dc=cakephp,dc=org',
            'password' => 'password',
            'basedn' => 'dc=cakephp,dc=org',
            'version' => 3,
            'tls' => false,
            'database' => '',
        ];

        $this->loadLdapFixtures($config);

        $ldap = ConnectionManager::create('test_ldap', $config);
        if ($ldap) {
            $ldap->cacheSources = false;
            $ldap->fullDebug = false;
        }

        if (CakeLog::stream('stderr')) {
            CakeLog::disable('stderr');
        }
    }

    public function tearDown(): void
    {
        parent::tearDown();

        ConnectionManager::drop('test_ldap');

        if (CakeLog::stream('stderr')) {
            CakeLog::enable('stderr');
        }
    }

    public function loadLdapFixtures($config): void
    {
        $ldap = ldap_connect($config['host'], $config['port']);

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, $config['version']);

        if (!ldap_bind($ldap, $config['login'], $config['password'])) {
            $this->markTestSkipped('Could not connect to LDAP server. Skip test.');
        }

        // Retrieve fixture objects
        $prop = new ReflectionProperty($this->fixtureManager, '_loaded');
        $prop->setAccessible(true);
        $loaded = $prop->getValue($this->fixtureManager);

        $tables = [];

        $result = ldap_list($ldap, $config['basedn'], 'objectClass=organizationalUnit');
        for ($entry = ldap_first_entry($ldap, $result); $entry; $entry = ldap_next_entry($ldap, $entry)) {
            $tables[] = ldap_get_dn($ldap, $entry);
        }
        ldap_free_result($result);

        foreach ($this->fixtures as $name) {
            $fixture = $loaded[$name];

            $table = 'ou=' . $fixture->table . ',' . $config['basedn'];

            // DROP TABLE
            if (in_array($table, $tables, true)) {
                $result = ldap_search($ldap, $table, 'objectclass=*');
                $dns = [];
                for ($entry = ldap_first_entry($ldap, $result); $entry; $entry = ldap_next_entry($ldap, $entry)) {
                    $dns[] = ldap_get_dn($ldap, $entry);
                }
                ldap_free_result($result);

                foreach (array_reverse($dns) as $dn) {
                    ldap_delete($ldap, $dn);
                }
            }

            // CREATE TABLE
            @ldap_add($ldap, $table, ['objectclass' => 'organizationalUnit', 'ou' => $fixture->table]);

            // INSERT
            foreach ($fixture->records as $record) {
                $dn = $fixture->primaryKey . '=' . $record[$fixture->primaryKey] . ',' . $table;
                @ldap_add($ldap, $dn, $record);
            }
        }

        ldap_close($ldap);
    }

    public function testIsConnected(): void
    {
        $ldap = ConnectionManager::getDataSource('test_ldap');
        $this->assertTrue($ldap->isConnected());
    }

    public function testDisconnect(): void
    {
        $ldap = ConnectionManager::getDataSource('test_ldap');
        $ldap->disconnect();
        $this->assertFalse($ldap->isConnected());
    }

    public function testFind(): void
    {
        $model = new LdapPerson();

        $people = $model->find('all', [
            'conditions' => ['sn' => 'doe'],  // lowercase to match fixture
        ]);

        $expected = [
            [
                'LdapPerson' => [
                    'dn' => 'cn=jane,ou=ldap_people,dc=cakephp,dc=org',
                    'objectclass' => 'person',
                    'cn' => 'jane',
                    'sn' => 'doe',
                ],
                [
                    'count' => 2,
                ],
            ],
            [
                'LdapPerson' => [
                    'dn' => 'cn=john,ou=ldap_people,dc=cakephp,dc=org',
                    'objectclass' => 'person',
                    'cn' => 'john',
                    'sn' => 'doe',
                ],
            ],
        ];

        $this->assertEquals($expected, $people);
    }

    public function testSaveCreate(): void
    {
        $model = new LdapPerson();

        $data = [
            'objectclass' => 'person',
            'cn' => 'foo',
            'sn' => 'bar',
        ];

        $model->save($data);

        $person = $model->read();

        $expected = [
            'LdapPerson' => [
                'dn' => 'cn=foo,ou=ldap_people,dc=cakephp,dc=org',
                'objectclass' => 'person',
                'cn' => 'foo',
                'sn' => 'bar',
            ],
            [
                'count' => 1,
            ],
        ];

        $this->assertEquals($expected, $person);
    }

    public function testSaveUpdate(): void
    {
        $model = new LdapPerson();

        $data = [
            'cn' => 'john',
            'sn' => 'smith',
        ];

        $model->save($data);

        $person = $model->find('first', [
            'conditions' => ['cn' => 'john'],
        ]);

        $expected = [
            'LdapPerson' => [
                'dn' => 'cn=john,ou=ldap_people,dc=cakephp,dc=org',
                'objectclass' => 'person',
                'cn' => 'john',
                'sn' => 'smith',
            ],
            [
                'count' => 1,
            ],
        ];

        $this->assertEquals($expected, $person);
    }

    public function testDelete(): void
    {
        $model = new LdapPerson();
        $model->delete('john');

        $person = $model->find('first', [
            'conditions' => ['cn' => 'john'],
        ]);

        $this->assertSame([], $person);
    }

    public function testFindSchema(): void
    {
        $model = new LdapPerson();
        $schema = $model->findSchema();

        $expected = [
            'oid' => '2.5.6.6',
            'name' => 'person',
            'description' => "'RFC2256: a person'",
            'sup_classes' => ['top'],
            'type' => 'structural',
            'must' => ['cn', 'sn'],
            'may' => ['description', 'seeAlso', 'telephoneNumber', 'userPassword'],
        ];

        $this->assertEquals($expected, $schema['objectclasses']['person']);
    }

    public function testConvertTimestampADToUnix(): void
    {
        $ldap = ConnectionManager::getDataSource('test_ldap');
        $time = $ldap->convertTimestampADToUnix('131277152960000000');
        $this->assertEquals(1483241696, $time);
    }

    public function testGetNumRows(): void
    {
        $model = new LdapPerson();

        $model->find('all', [
            'conditions' => ['sn' => 'Doe'],
        ]);

        $this->assertEquals(2, $model->getNumRows());
    }
}
