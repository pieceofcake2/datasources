<?php
/**
 * User Fixture
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

/**
 * User Fixture
 */
class UserFixture extends CakeTestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'key' => 'primary'],
        'born_id' => ['type' => 'integer', 'null' => false],
        'name' => ['type' => 'string', 'null' => false],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['born_id' => 1, 'name' => 'User 1'],
        ['born_id' => 2, 'name' => 'User 2'],
        ['born_id' => 1, 'name' => 'User 3'],
        ['born_id' => 3, 'name' => 'User 4'],
    ];
}
