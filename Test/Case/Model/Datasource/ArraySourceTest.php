<?php
/**
 * Array Datasource Test file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP Datasources v 0.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('ArraySource', 'Datasources.Model/Datasource');
App::uses('ConnectionManager', 'Model');
App::uses('CakeRequest', 'Network');
App::uses('Controller', 'Controller');

// Add new db config
ConnectionManager::create('test_array', ['datasource' => 'Datasources.ArraySource']);

/**
 * Array Testing Model
 */
class ArrayModel extends CakeTestModel
{
    /**
     * Database Configuration
     *
     * @var string
     */
    public $useDbConfig = 'test_array';

    /**
     * Set recursive
     *
     * @var int
     */
    public $recursive = -1;

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'name' => 'USA',
            'relate_id' => 1,
        ],
        [
            'id' => 2,
            'name' => 'Brazil',
            'relate_id' => 1,
        ],
        [
            'id' => 3,
            'name' => 'Germany',
            'relate_id' => 2,
        ],
    ];
}

/**
 * ArraysRelate Testing Model
 */
class ArraysRelateModel extends CakeTestModel
{
    /**
     * Database Configuration
     *
     * @var string
     */
    public $useDbConfig = 'test_array';

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        ['array_model_id' => 1, 'relate_id' => 1, 'additional' => 98],
        ['array_model_id' => 1, 'relate_id' => 2, 'additional' => null],
        ['array_model_id' => 1, 'relate_id' => 3, 'additional' => 45],
        ['array_model_id' => 2, 'relate_id' => 1, 'additional' => null],
        ['array_model_id' => 2, 'relate_id' => 3, 'additional' => 68],
        ['array_model_id' => 3, 'relate_id' => 1, 'additional' => null],
        ['array_model_id' => 3, 'relate_id' => 2, 'additional' => 148],
    ];
}

/**
 * User Testing Model
 */
class UserModel extends CakeTestModel
{
    /**
     * Use DB Config
     *
     * @var string
     */
    public $useDbConfig = 'test';

    /**
     * Use Table
     *
     * @var string
     */
    public $useTable = 'users';

    /**
     * Belongs To
     *
     * @var array
     */
    public $belongsTo = [
        'Born' => [
            'className' => 'ArrayModel',
            'foreignKey' => 'born_id',
        ],
    ];
}

/**
 * ArraySourceTestModel
 *
 * Base model for the following array models.
 */
abstract class ArraySourceTestModel extends CakeTestModel
{
    /**
     * Use the array config made earlier.
     *
     * @var string
     */
    public $useDbConfig = 'test_array';
}

/**
 * ArraySourceTestProfile
 *
 * Profile simulation model.
 */
class ArraySourceTestProfile extends ArraySourceTestModel
{
    /**
     * hasOne
     *
     * Associate with User.
     *
     * @var array
     */
    public $hasOne = ['ArraySourceTestUser'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        ['id' => 1, 'title' => 'Lad'],
        ['id' => 2, 'title' => 'Lord'],
        ['id' => 3, 'title' => 'Sir'],
    ];
}

/**
 * ArraySourceTestUser
 *
 * User simulation model.
 */
class ArraySourceTestUser extends ArraySourceTestModel
{
    /**
     * belongsTo
     *
     * Associate with Profile.
     *
     * @var array
     */
    public $belongsTo = ['ArraySourceTestProfile'];

    /**
     * hasMany
     *
     * Associate with Post & Comment.
     *
     * @var array
     */
    public $hasMany = ['ArraySourceTestPost', 'ArraySourceTestComment'];

    /**
     * $hasAndBelongsToMany
     *
     * Associate with IpAddress
     *
     * @var array
     */
    public $hasAndBelongsToMany = ['ArraySourceTestIpAddress'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        ['id' => 1, 'array_source_test_profile_id' => 3, 'username' => 'Phally'],
        ['id' => 2, 'array_source_test_profile_id' => 2, 'username' => 'ADmad'],
        ['id' => 3, 'array_source_test_profile_id' => 1, 'username' => 'Jippi'],
    ];
}

/**
 * ArraySourceTestPost
 *
 * Post simulation model.
 */
class ArraySourceTestPost extends ArraySourceTestModel
{
    /**
     * belongsTo
     *
     * Associate with User.
     *
     * @var array
     */
    public $belongsTo = ['ArraySourceTestUser'];

    /**
     * hasMany
     *
     * Associate with Comment.
     *
     * @var array
     */
    public $hasMany = ['ArraySourceTestComment'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        ['id' => 1, 'array_source_test_user_id' => 1, 'title' => 'First post'],
        ['id' => 2, 'array_source_test_user_id' => 1, 'title' => 'Second post'],
        ['id' => 3, 'array_source_test_user_id' => 2, 'title' => 'Third post'],
    ];
}

/**
 * ArraySourceTestComment
 *
 * Comment simulation model.
 */
class ArraySourceTestComment extends ArraySourceTestModel
{
    /**
     * belongsTo
     *
     * Associate with Post & User
     *
     * @var array
     */
    public $belongsTo = ['ArraySourceTestPost', 'ArraySourceTestUser'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        ['id' => 1, 'array_source_test_post_id' => 1, 'array_source_test_user_id' => 3, 'comment' => 'Cool story bro.'],
        ['id' => 2, 'array_source_test_post_id' => 1, 'array_source_test_user_id' => 1, 'comment' => 'Thanks!'],
        ['id' => 3, 'array_source_test_post_id' => 1, 'array_source_test_user_id' => 2, 'comment' => 'I dunno, wasn\'t that good.'],
        ['id' => 4, 'array_source_test_post_id' => 2, 'array_source_test_user_id' => 3, 'comment' => 'Literary masterpiece.'],
        ['id' => 5, 'array_source_test_post_id' => 2, 'array_source_test_user_id' => 2, 'comment' => 'Yep!'],
        ['id' => 6, 'array_source_test_post_id' => 2, 'array_source_test_user_id' => 3, 'comment' => 'I read it again, still brilliant.'],
    ];
}

/**
 * ArraySourceTestIpAddress
 *
 * IpAddress simulation model.
 */
class ArraySourceTestIpAddress extends ArraySourceTestModel
{
    /**
     * $hasAndBelongsToMany
     *
     * Associate with User
     *
     * @var array
     */
    public $hasAndBelongsToMany = ['ArraySourceTestUser'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        ['id' => 1, 'ip' => '127.0.0.1'],
        ['id' => 2, 'ip' => '192.168.1.1'],
        ['id' => 3, 'ip' => '8.8.4.4'],
    ];
}

/**
 * ArraySourceTestIpAddressesArraySourceTestUser
 *
 * User - IpAddress simulation join model.
 */
class ArraySourceTestIpAddressesArraySourceTestUser extends ArraySourceTestModel
{
    /**
     * belongsTo
     *
     * Associate with User & IpAddress
     *
     * @var array
     */
    public $belongsTo = ['ArraySourceTestUser', 'ArraySourceTestIpAddress'];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        ['id' => 1, 'array_source_test_ip_address_id' => 1, 'array_source_test_user_id' => 2],
        ['id' => 2, 'array_source_test_ip_address_id' => 1, 'array_source_test_user_id' => 1],
        ['id' => 3, 'array_source_test_ip_address_id' => 2, 'array_source_test_user_id' => 1],
        ['id' => 4, 'array_source_test_ip_address_id' => 3, 'array_source_test_user_id' => 3],
    ];
}

/**
 * Array Datasource Test
 */
class ArraySourceTest extends CakeTestCase
{
    /**
     * List of fixtures
     *
     * @var array
     */
    public $fixtures = ['plugin.datasources.user'];

    /**
     * Array Source Instance
     *
     * @var ArraySource
     */
    public $Model = null;

    /**
     * Set up for Tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Model = ClassRegistry::init('ArrayModel');
    }

    /**
     * Tear down for tests
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        ClassRegistry::flush();
        $this->Model = null;
    }

    /**
     * testFindAll
     *
     * @return void
     */
    public function testFindAll(): void
    {
        $result = $this->Model->find('all');
        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testFindFields
     *
     * @return void
     */
    public function testFindFields(): void
    {
        $expected = [
            ['ArrayModel' => ['id' => 1]],
            ['ArrayModel' => ['id' => 2]],
            ['ArrayModel' => ['id' => 3]],
        ];
        $result = $this->Model->find('all', ['fields' => ['id']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['fields' => ['ArrayModel.id']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['fields' => ['ArrayModel.id', 'Unknow.id']]);
        $this->assertEquals($expected, $result);
    }

    /**
     * testField
     *
     * @return void
     */
    public function testField(): void
    {
        $expected = 2;
        $result = $this->Model->field('id', ['name' => 'Brazil']);
        $this->assertEquals($expected, $result);

        $expected = 'Germany';
        $result = $this->Model->field('name', ['relate_id' => 2]);
        $this->assertEquals($expected, $result);

        $expected = 'USA';
        $result = $this->Model->field('name', ['relate_id' => 1]);
        $this->assertEquals($expected, $result);
    }

    /**
     * testFindLimit
     *
     * @return void
     */
    public function testFindLimit(): void
    {
        $result = $this->Model->find('all', ['limit' => 2]);
        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['limit' => 2, 'page' => 2]);
        $expected = [
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testFindOrder
     *
     * @return void
     */
    public function testFindOrder(): void
    {
        $expected = [
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
        ];
        $result = $this->Model->find('all', ['order' => 'ArrayModel.name']);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['order' => 'ArrayModel.name ASC']);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['order' => 'name']);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['order' => 'name ASC']);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['order' => ['name' => 'ASC']]);
        $this->assertEquals($expected, $result);

        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
        ];
        $result = $this->Model->find('all', ['order' => 'ArrayModel.name DESC']);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['order' => 'name DESC']);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['order' => ['name' => 'DESC']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['fields' => ['ArrayModel.id'], 'order' => 'ArrayModel.name']);
        $expected = [
            ['ArrayModel' => ['id' => 2]],
            ['ArrayModel' => ['id' => 3]],
            ['ArrayModel' => ['id' => 1]],
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['fields' => ['ArrayModel.id'], 'order' => 'ArrayModel.name', 'limit' => 1, 'page' => 2]);
        $expected = [
            ['ArrayModel' => ['id' => 3]],
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['order' => ['relate_id' => 'DESC', 'id' => 'ASC']]);
        $expected = [
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testFindConditions
     *
     * @return void
     */
    public function testFindConditions(): void
    {
        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name' => 'USA']]);
        $expected = [['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]]];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name =' => 'USA']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name = USA']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name !=' => 'USA']]);
        $expected = [['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]], ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]]];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name != USA']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name LIKE' => '%ra%']]);
        $expected = [['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]]];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name LIKE %ra%']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name LIKE _r%']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name LIKE %b%']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name LIKE %a%']]);
        $expected = [['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]], ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]], ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]]];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name' => ['USA', 'Germany']]]);
        $expected = [['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]], ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]]];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name IN (USA, Germany)']]);
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.name' => 'USA', 'ArrayModel.id' => 2]]);
        $expected = [];
        $this->assertSame($expected, $result);

        $model = ClassRegistry::init('ArraysRelateModel');

        $expected = [
            ['ArraysRelateModel' => ['array_model_id' => 1, 'relate_id' => 2, 'additional' => null]],
            ['ArraysRelateModel' => ['array_model_id' => 2, 'relate_id' => 1, 'additional' => null]],
            ['ArraysRelateModel' => ['array_model_id' => 3, 'relate_id' => 1, 'additional' => null]],
        ];
        $result = $model->find('all', ['conditions' => ['additional' => null]]);
        $this->assertSame($expected, $result);

        $expected = [
            ['ArraysRelateModel' => ['array_model_id' => 1, 'relate_id' => 1, 'additional' => 98]],
            ['ArraysRelateModel' => ['array_model_id' => 1, 'relate_id' => 3, 'additional' => 45]],
            ['ArraysRelateModel' => ['array_model_id' => 2, 'relate_id' => 3, 'additional' => 68]],
            ['ArraysRelateModel' => ['array_model_id' => 3, 'relate_id' => 2, 'additional' => 148]],
        ];
        $result = $model->find('all', ['conditions' => ['additional != ' => null]]);
        $this->assertSame($expected, $result);
    }

    /**
     * testFindconditionsRecursive
     *
     * @return void
     */
    public function testFindConditionsRecursive(): void
    {
        $result = $this->Model->find('all', ['conditions' => ['AND' => ['ArrayModel.name' => 'USA', 'ArrayModel.id' => 2]]]);
        $expected = [];
        $this->assertSame($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['OR' => ['ArrayModel.name' => 'USA', 'ArrayModel.id' => 2]]]);
        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
        ];
        $this->assertSame($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['NOT' => ['ArrayModel.id' => 2]]]);
        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * testFindConditionsWithComparisonOperators
     *
     * @return void
     */
    public function testFindConditionsWithComparisonOperators(): void
    {
        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.id <' => 2]]);
        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
        ];
        $this->assertSame($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.id <=' => 2]]);
        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
        ];
        $this->assertSame($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.id >' => 2]]);
        $expected = [
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
        ];
        $this->assertSame($expected, $result);

        $result = $this->Model->find('all', ['conditions' => ['ArrayModel.id >=' => 2]]);
        $expected = [
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * testFindFirst
     *
     * @return void
     */
    public function testFindFirst(): void
    {
        $result = $this->Model->find('first');
        $expected = ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]];
        $this->assertEquals($expected, $result);

        $result = $this->Model->find('first', ['fields' => ['name']]);
        $expected = ['ArrayModel' => ['name' => 'USA']];
        $this->assertEquals($expected, $result);
    }

    /**
     * testFindCount
     *
     * @return void
     */
    public function testFindCount(): void
    {
        $result = $this->Model->find('count');
        $this->assertEquals($result, 3);

        $result = $this->Model->find('count', ['limit' => 2]);
        $this->assertEquals($result, 2);

        $result = $this->Model->find('count', ['limit' => 5]);
        $this->assertEquals($result, 3);

        $result = $this->Model->find('count', ['limit' => 2, 'page' => 2]);
        $this->assertEquals($result, 1);
    }

    /**
     * testFindList
     *
     * @return void
     */
    public function testFindList(): void
    {
        $result = $this->Model->find('list');
        $expected = [1 => 'USA', 2 => 'Brazil', 3 => 'Germany'];
        $this->assertEquals($expected, $result);
    }

    /**
     * testRead
     *
     * @return void
     */
    public function testRead(): void
    {
        $result = $this->Model->read(null, 1);
        $expected = ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]];
        $this->assertEquals($expected, $result);

        $result = $this->Model->read(['name'], 2);
        $expected = ['ArrayModel' => ['name' => 'Brazil']];
        $this->assertEquals($expected, $result);
    }

    /**
     * testDboToArrayBelongsTo
     *
     * @return void
     */
    public function testDboToArrayBelongsTo(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('UserModel');

        $result = $model->find('all', ['recursive' => 0]);
        // unset primaryKey, wich can be integer/serial or hash value
        foreach ($result as &$row) {
            unset($row['UserModel'][$model->primaryKey]);
        }
        $expected = [
            ['UserModel' => ['born_id' => 1, 'name' => 'User 1'], 'Born' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['UserModel' => ['born_id' => 2, 'name' => 'User 2'], 'Born' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
            ['UserModel' => ['born_id' => 1, 'name' => 'User 3'], 'Born' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['UserModel' => ['born_id' => 3, 'name' => 'User 4'], 'Born' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
        ];
        $this->assertEquals($expected, $result);

        $model->belongsTo['Born']['fields'] = ['name'];
        $result = $model->find('all', ['recursive' => 0]);
        // unset primaryKey, wich can be integer/serial or hash value
        foreach ($result as &$row) {
            unset($row['UserModel'][$model->primaryKey]);
        }
        $expected = [
            ['UserModel' => ['born_id' => 1, 'name' => 'User 1'], 'Born' => ['name' => 'USA']],
            ['UserModel' => ['born_id' => 2, 'name' => 'User 2'], 'Born' => ['name' => 'Brazil']],
            ['UserModel' => ['born_id' => 1, 'name' => 'User 3'], 'Born' => ['name' => 'USA']],
            ['UserModel' => ['born_id' => 3, 'name' => 'User 4'], 'Born' => ['name' => 'Germany']],
        ];
        $this->assertEquals($expected, $result);

        $result = $model->read(null, 1);
        unset($result['UserModel'][$model->primaryKey]);
        $expected = ['UserModel' => ['born_id' => 1, 'name' => 'User 1'], 'Born' => ['name' => 'USA']];
        $this->assertEquals($expected, $result);
    }

    /**
     * testDboToArrayBelongsToWithoutForeignKey
     *
     * @return void
     */
    public function testDboToArrayBelongsToWithoutForeignKey(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('UserModel');

        $result = $model->find('all', [
            'fields' => ['UserModel.id', 'UserModel.name'],
            'recursive' => 0,
        ]);
        // unset primaryKey, wich can be integer/serial or hash value
        foreach ($result as &$row) {
            unset($row['UserModel'][$model->primaryKey]);
        }
        $expected = [
            [
                'UserModel' => ['name' => 'User 1'],
                'Born' => [],
            ],
            [
                'UserModel' => ['name' => 'User 2'],
                'Born' => [],
            ],
            [
                'UserModel' => ['name' => 'User 3'],
                'Born' => [],
            ],
            [
                'UserModel' => ['name' => 'User 4'],
                'Born' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testDboToArrayHasMany
     *
     * @return void
     */
    public function testDboToArrayHasMany(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('UserModel');
        $model->unBindModel(['belongsTo' => ['Born']], false);
        $model->bindModel(['hasMany' => ['Relate' => ['className' => 'ArrayModel', 'foreignKey' => 'relate_id']]], false);

        $result = $model->find('all', ['recursive' => 1]);
        // unset primaryKey, wich can be integer/serial or hash value
        foreach ($result as &$row) {
            unset($row['UserModel'][$model->primaryKey]);
        }
        $expected = [
            [
                'UserModel' => ['name' => 'User 1', 'born_id' => 1],
                'Relate' => [
                    ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                    ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                ],
            ],
            ['UserModel' => ['name' => 'User 2', 'born_id' => 2],
                'Relate' => [
                    ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                ],
            ],
            ['UserModel' => ['name' => 'User 3', 'born_id' => 1],
                'Relate' => [
                ],
            ],
            ['UserModel' => ['name' => 'User 4', 'born_id' => 3],
                'Relate' => [
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testDboToArrayHasOne
     *
     * @return void
     */
    public function testDboToArrayHasOne(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('UserModel');
        $model->unBindModel(['hasMany' => ['Relate'], 'belongsTo' => ['Born']], false);
        $model->bindModel(['hasOne' => ['Relate' => ['className' => 'ArrayModel', 'foreignKey' => 'relate_id']]], false);

        $result = $model->find('all', ['recursive' => 1]);
        // unset primaryKey, wich can be integer/serial or hash value
        foreach ($result as &$row) {
            unset($row['UserModel'][$model->primaryKey]);
        }
        $expected = [
            [
                'UserModel' => ['name' => 'User 1', 'born_id' => 1],
                'Relate' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
            ],
            ['UserModel' => ['name' => 'User 2', 'born_id' => 2],
                'Relate' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
            ],
            [
                'UserModel' => ['name' => 'User 3', 'born_id' => 1],
                'Relate' => [],
            ],
            [
                'UserModel' => ['name' => 'User 4', 'born_id' => 3],
                'Relate' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testArrayToArrayBelongsTo
     *
     * @return void
     */
    public function testArrayToArrayBelongsTo(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('ArrayModel');
        $model->recursive = 0;
        $model->bindModel(['belongsTo' => ['Relate' => ['className' => 'ArrayModel', 'foreignKey' => 'relate_id']]], false);

        $result = $model->find('all');
        $expected = [
            [
                'ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                'Relate' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
            ],
            [
                'ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                'Relate' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
            ],
            [
                'ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                'Relate' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
            ],
        ];
        $this->assertEquals($expected, $result);

        $model->belongsTo['Relate']['fields'] = ['name'];

        $result = $model->find('all');
        $expected = [
            [
                'ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                'Relate' => ['name' => 'USA'],
            ],
            [
                'ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                'Relate' => ['name' => 'USA'],
            ],
            [
                'ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                'Relate' => ['name' => 'Brazil'],
            ],
        ];
        $this->assertEquals($expected, $result);

        $result = $model->read(null, 1);
        $expected = [
            'ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
            'Relate' => ['name' => 'USA'],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testArrayToArrayBelongsToWithoutForeignKey
     *
     * @return void
     */
    public function testArrayToArrayBelongsToWithoutForeignKey(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('ArrayModel');
        $model->bindModel(['belongsTo' => ['Relate' => ['className' => 'ArrayModel', 'foreignKey' => 'relate_id']]], false);

        $result = $model->find('all', [
            'fields' => ['ArrayModel.id', 'ArrayModel.name'],
            'recursive' => 0,
        ]);
        $expected = [
            [
                'ArrayModel' => ['id' => 1, 'name' => 'USA'],
                'Relate' => [],
            ],
            [
                'ArrayModel' => ['id' => 2, 'name' => 'Brazil'],
                'Relate' => [],
            ],
            [
                'ArrayModel' => ['id' => 3, 'name' => 'Germany'],
                'Relate' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testArrayToArrayHasMany
     *
     * @return void
     */
    public function testArrayToArrayHasMany(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('ArrayModel');
        $model->unBindModel(['belongsTo' => ['Relate']], false);
        $model->bindModel(['hasMany' => ['Relate' => ['className' => 'ArrayModel', 'foreignKey' => 'relate_id']]], false);

        $result = $model->find('all', ['recursive' => 1]);
        $expected = [
            [
                'ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                'Relate' => [
                    ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                    ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                ],
            ],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                'Relate' => [
                    ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                ],
            ],
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                'Relate' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testArrayToArrayHasOne
     *
     * @return void
     */
    public function testArrayToArrayHasOne(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('ArrayModel');
        $model->unBindModel(['hasMany' => ['Relate']], false);
        $model->bindModel(['hasOne' => ['Relate' => ['className' => 'ArrayModel', 'foreignKey' => 'relate_id']]], false);

        $result = $model->find('all', ['recursive' => 1]);
        $expected = [
            [
                'ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                'Relate' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
            ],
            [
                'ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                'Relate' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
            ],
            [
                'ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                'Relate' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testArrayToArrayHasAndBelongsToMany
     *
     * @return void
     */
    public function testArrayToArrayHasAndBelongsToMany(): void
    {
        ClassRegistry::config([]);
        $model = ClassRegistry::init('ArrayModel');
        $model->unBindModel(['hasOne' => ['Relate']], false);
        $model->bindModel(['hasAndBelongsToMany' => [
            'Relate' => [
                'className' => 'ArrayModel',
                'with' => 'ArraysRelateModel',
                'associationForeignKey' => 'relate_id',
            ],
        ]], false);

        $result = $model->find('all', ['recursive' => 1]);
        $expected = [
            [
                'ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                'Relate' => [
                    ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                    ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                    ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                ],
            ],
            [
                'ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                'Relate' => [
                    ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                    ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                ],
            ],
            [
                'ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2],
                'Relate' => [
                    ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                    ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1],
                ],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testArrayToTableHasAndBelongsToMany
     *
     * @return void
     */
    public function testArrayToTableHasAndBelongsToMany(): void
    {
        $User = ClassRegistry::init('UserModel');
        $result = $User->find('all', ['recursive' => 1]);
        $User->bindModel(['hasAndBelongsToMany' => [
            'Relate' => [
                'className' => 'ArrayModel',
                'with' => 'ArraysRelateModel',
                'foreignKey' => 'array_model_id',
                'associationForeignKey' => 'relate_id',
            ],
        ]], false);
        $User->unBindModel(['belongsTo' => ['Born']], false);
        $result = $User->find('all', ['recursive' => 1]);

        $User->ArraysRelateModel->records = [
            ['array_model_id' => 1, 'relate_id' => 1],
        ];
        $result = $User->find('all', ['recursive' => 1]);
        $expected = [
            [
                'UserModel' => ['id' => 1, 'born_id' => 1, 'name' => 'User 1'],
                'Relate' => [
                    ['id' => 1, 'name' => 'USA', 'relate_id' => 1],
                ],
            ],
            [
                'UserModel' => ['id' => 2, 'born_id' => 2, 'name' => 'User 2'],
                'Relate' => [],
            ],
            [
                'UserModel' => ['id' => 3, 'born_id' => 1, 'name' => 'User 3'],
                'Relate' => [],
            ],
            [
                'UserModel' => ['id' => 4, 'born_id' => 3, 'name' => 'User 4'],
                'Relate' => [],
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * testDeepRecursion
     *
     * @return void
     */
    public function testDeepRecursion(): void
    {
        $Post = ClassRegistry::init('ArraySourceTestPost');

        $expected = [
            0 => [
                'ArraySourceTestPost' => [
                    'id' => 1,
                    'array_source_test_user_id' => 1,
                    'title' => 'First post',
                ],
                'ArraySourceTestUser' => [
                    'id' => 1,
                    'array_source_test_profile_id' => 3,
                    'username' => 'Phally',
                ],
            ],
            1 => [
                'ArraySourceTestPost' => [
                    'id' => 2,
                    'array_source_test_user_id' => 1,
                    'title' => 'Second post',
                ],
                'ArraySourceTestUser' => [
                    'id' => 1,
                    'array_source_test_profile_id' => 3,
                    'username' => 'Phally',
                ],
            ],
        ];

        $result = $Post->find('all', [
            'recursive' => 0,
            'limit' => 2,
        ]);

        $this->assertSame($expected, $result);

        $expected = [
            0 => [
                'ArraySourceTestPost' => [
                    'id' => 1,
                    'array_source_test_user_id' => 1,
                    'title' => 'First post',
                ],
                'ArraySourceTestUser' => [
                    'id' => 1,
                    'array_source_test_profile_id' => 3,
                    'username' => 'Phally',
                ],
                'ArraySourceTestComment' => [
                    0 => [
                        'id' => 1,
                        'array_source_test_post_id' => 1,
                        'array_source_test_user_id' => 3,
                        'comment' => 'Cool story bro.',
                    ],
                    1 => [
                        'id' => 2,
                        'array_source_test_post_id' => 1,
                        'array_source_test_user_id' => 1,
                        'comment' => 'Thanks!',

                    ],
                    2 => [
                        'id' => 3,
                        'array_source_test_post_id' => 1,
                        'array_source_test_user_id' => 2,
                        'comment' => 'I dunno, wasn\'t that good.',
                    ],
                ],
            ],
        ];

        $result = $Post->find('all', [
            'recursive' => 1,
            'limit' => 1,
        ]);

        $this->assertSame($expected, $result);

        $results = $Post->find('first', [
            'recursive' => 2,
        ]);

        $expected = ['id' => 3, 'title' => 'Sir'];
        $this->assertSame($expected, $results['ArraySourceTestUser']['ArraySourceTestProfile']);

        $expected = [1, 2];
        $result = Hash::extract($results['ArraySourceTestUser']['ArraySourceTestPost'], '{n}.id');
        $this->assertSame($expected, $result);

        $expected = [2];
        $result = Hash::extract($results['ArraySourceTestUser']['ArraySourceTestComment'], '{n}.id');
        $this->assertSame($expected, $result);

        $expected = [
            'id', 'array_source_test_profile_id', 'username', 'ArraySourceTestProfile',
            'ArraySourceTestPost', 'ArraySourceTestComment', 'ArraySourceTestIpAddress',
        ];
        $result = array_keys($results['ArraySourceTestUser']);
        $this->assertSame($expected, $result);

        $expected = [1, 2, 3];
        $result = Hash::extract($results['ArraySourceTestComment'], '{n}.id');
        $this->assertSame($expected, $result);

        $expected = [1, 1, 1];
        $result = Hash::extract($results['ArraySourceTestComment'], '{n}.ArraySourceTestPost.id');
        $this->assertSame($expected, $result);

        $expected = [3, 1, 2];
        $result = Hash::extract($results['ArraySourceTestComment'], '{n}.ArraySourceTestUser.id');
        $this->assertSame($expected, $result);

        $this->assertFalse(isset($results['ArraySourceTestUser']['ArraySourceTestPost'][0]['ArraySourceTestUser']));
        $this->assertFalse(isset($results['ArraySourceTestUser']['ArraySourceTestPost'][0]['ArraySourceTestComment']));
        $this->assertFalse(isset($results['ArraySourceTestUser']['ArraySourceTestComment'][0]['ArraySourceTestPost']));
        $this->assertFalse(isset($results['ArraySourceTestUser']['ArraySourceTestComment'][0]['ArraySourceTestUser']));

        $this->assertFalse(isset($results['ArraySourceTestComment'][0]['ArraySourceTestPost']['ArraySourceTestUser']));
        $this->assertFalse(isset($results['ArraySourceTestComment'][0]['ArraySourceTestPost']['ArraySourceTestComment']));
        $this->assertFalse(isset($results['ArraySourceTestComment'][0]['ArraySourceTestUser']['ArraySourceTestProfile']));
        $this->assertFalse(isset($results['ArraySourceTestComment'][0]['ArraySourceTestUser']['ArraySourceTestComment']));

        $Profile = ClassRegistry::init('ArraySourceTestProfile');

        $expected = [
            'ArraySourceTestProfile' => [
                'id' => 1,
                'title' => 'Lad',
            ],
            'ArraySourceTestUser' => [
                'id' => 3,
                'array_source_test_profile_id' => 1,
                'username' => 'Jippi',
                'ArraySourceTestProfile' => [
                    'id' => 1,
                    'title' => 'Lad',
                ],
                'ArraySourceTestPost' => [],
                'ArraySourceTestComment' => [
                    0 => [
                        'id' => 1,
                        'array_source_test_post_id' => 1,
                        'array_source_test_user_id' => 3,
                        'comment' => 'Cool story bro.',
                    ],
                    1 => [
                        'id' => 4,
                        'array_source_test_post_id' => 2,
                        'array_source_test_user_id' => 3,
                        'comment' => 'Literary masterpiece.',
                    ],
                    2 => [
                        'id' => 6,
                        'array_source_test_post_id' => 2,
                        'array_source_test_user_id' => 3,
                        'comment' => 'I read it again, still brilliant.',
                    ],
                ],
                'ArraySourceTestIpAddress' => [
                    0 => [
                        'id' => 3,
                        'ip' => '8.8.4.4',
                    ],
                ],

            ],
        ];

        $result = $Profile->find('first', ['recursive' => 2]);
        $this->assertSame($expected, $result);
    }

    /**
     * testDeepRecursionWithContainable
     *
     * @return void
     */
    public function testDeepRecursionWithContainable(): void
    {
        $Profile = ClassRegistry::init('ArraySourceTestProfile');
        $Profile->Behaviors->load('Containable');

        $expected = [
            'ArraySourceTestProfile' => [
                'id' => 1,
                'title' => 'Lad',
            ],
            'ArraySourceTestUser' => [
                'id' => 3,
                'array_source_test_profile_id' => 1,
                'username' => 'Jippi',
                'ArraySourceTestComment' => [
                    0 => [
                        'id' => 1,
                        'array_source_test_post_id' => 1,
                        'array_source_test_user_id' => 3,
                        'comment' => 'Cool story bro.',
                        'ArraySourceTestPost' => [
                            'id' => 1,
                            'array_source_test_user_id' => 1,
                            'title' => 'First post',
                        ],
                    ],
                    1 => [
                        'id' => 4,
                        'array_source_test_post_id' => 2,
                        'array_source_test_user_id' => 3,
                        'comment' => 'Literary masterpiece.',
                        'ArraySourceTestPost' => [
                            'id' => 2,
                            'array_source_test_user_id' => 1,
                            'title' => 'Second post',
                        ],
                    ],
                    2 => [
                        'id' => 6,
                        'array_source_test_post_id' => 2,
                        'array_source_test_user_id' => 3,
                        'comment' => 'I read it again, still brilliant.',
                        'ArraySourceTestPost' => [
                            'id' => 2,
                            'array_source_test_user_id' => 1,
                            'title' => 'Second post',
                        ],
                    ],
                ],
            ],
        ];

        $result = $Profile->find('first', [
            'contain' => [
                'ArraySourceTestUser' => [
                    'ArraySourceTestComment' => 'ArraySourceTestPost',
                ],
            ],
        ]);
        $this->assertSame($expected, $result);
    }

    /**
     * Tests that ArraySource works with PaginatorComponent
     *
     * @return void
     */
    public function testPaginator(): void
    {
        $controller = new Controller(new CakeRequest());
        $controller->uses = ['ArrayModel'];
        $controller->components = ['Paginator'];
        $controller->constructClasses();
        $controller->startupProcess();

        $controller->paginate = [
            'sort' => 'name',
            'direction' => 'desc',
        ];

        $expected = [
            ['ArrayModel' => ['id' => 1, 'name' => 'USA', 'relate_id' => 1]],
            ['ArrayModel' => ['id' => 3, 'name' => 'Germany', 'relate_id' => 2]],
            ['ArrayModel' => ['id' => 2, 'name' => 'Brazil', 'relate_id' => 1]],
        ];
        $this->assertEquals($expected, $controller->paginate());
    }
}
