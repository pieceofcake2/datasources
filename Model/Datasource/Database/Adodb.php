<?php
/**
 * AdoDB layer for DBO.
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

App::uses('DataSource', 'Model/Datasource');

/**
 * AdoDB DBO implementation.
 *
 * Database abstraction implementation for the AdoDB library.
 */
class Adodb extends DataSource
{
    /**
     * Enter description here...
     *
     * @var string
     */
    public $description = 'ADOdb DBO Driver';

    /**
     * ADOConnection object with which we connect.
     *
     * @var ADOConnection The connection object.
     */
    protected $_adodb = null;

    /**
     * Result
     *
     * @var ADORecordSet|null
     */
    protected $_result = null;

    /**
     * The starting character that this DataSource uses for quoted identifiers.
     *
     * @var string
     */
    public $startQuote = null;

    /**
     * The ending character that this DataSource uses for quoted identifiers.
     *
     * @var string
     */
    public $endQuote = null;

    /**
     * Database keyword used to assign aliases to identifiers.
     *
     * @var string
     */
    public $alias = 'AS ';

    /**
     * Are we connected to the DataSource?
     *
     * @var bool
     */
    public $connected = false;

    /**
     * Indicates the level of nested transactions
     *
     * @var int
     */
    protected $_transactionStarted = false;

    /**
     * Result set
     *
     * @var array
     */
    public $results = null;

    /**
     * Map of data
     *
     * @var array
     */
    public $map = [];

    /**
     * Array translating ADOdb column MetaTypes to cake-supported metatypes
     *
     * @var array
     */
    protected $_adodbColumnTypes = [
        'string' => 'C',
        'text' => 'X',
        'date' => 'D',
        'timestamp' => 'T',
        'time' => 'T',
        'datetime' => 'T',
        'boolean' => 'L',
        'float' => 'N',
        'integer' => 'I',
        'binary' => 'R',
    ];

    /**
     * ADOdb column definition
     *
     * @var array
     */
    public $columns = [
        'primary_key' => ['name' => 'R', 'limit' => 11],
        'string' => ['name' => 'C', 'limit' => '255'],
        'text' => ['name' => 'X'],
        'integer' => ['name' => 'I', 'limit' => '11', 'formatter' => 'intval'],
        'float' => ['name' => 'N', 'formatter' => 'floatval'],
        'timestamp' => ['name' => 'T', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'],
        'time' => ['name' => 'T', 'format' => 'H:i:s', 'formatter' => 'date'],
        'datetime' => ['name' => 'T', 'format' => 'Y-m-d H:i:s', 'formatter' => 'date'],
        'date' => ['name' => 'D', 'format' => 'Y-m-d', 'formatter' => 'date'],
        'binary' => ['name' => 'B'],
        'boolean' => ['name' => 'L', 'limit' => '1'],
    ];

    /**
     * Connects to the database using options in the given configuration array.
     *
     * @return bool
     */
    public function connect()
    {
        $config = $this->config;
        $persistent = strrpos($config['connect'], '|p');

        if ($persistent === false) {
            $adodbDriver = $config['connect'];
            $connect = 'Connect';
        } else {
            $adodbDriver = substr($config['connect'], 0, $persistent);
            $connect = 'PConnect';
        }
        if (!$this->enabled()) {
            return false;
        }
        $this->_adodb = adoNewConnection($adodbDriver);

        $this->_adodbDataDict = NewDataDictionary($this->_adodb, $adodbDriver);

        $this->startQuote = $this->_adodb->nameQuote;
        $this->endQuote = $this->_adodb->nameQuote;

        $this->connected = $this->_adodb->$connect($config['host'], $config['login'], $config['password'], $config['database']);
        $this->_adodbMetatyper = $this->_adodb->execute('Select 1');

        return $this->connected;
    }

    /**
     * Check that AdoDB is available.
     *
     * @return bool
     */
    public function enabled()
    {
        return function_exists('adoNewConnection');
    }

    /**
     * Disconnects from database.
     *
     * @return bool True if the database could be disconnected, else false
     */
    public function disconnect()
    {
        return $this->_adodb->Close();
    }

    /**
     * Executes given SQL statement.
     *
     * @param string $sql SQL statement
     * @param array $params list of params to be bound to query
     * @param array $prepareOptions Options to be used in the prepare statement
     * @return ADORecordSet|null
     */
    protected function _execute($sql, $params = [], $prepareOptions = []): ?ADORecordSet
    {
        // @codingStandardsIgnoreStart
        global $ADODB_FETCH_MODE;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        // @codingStandardsIgnoreEnd

        return $this->_adodb->execute($sql) ?: null;
    }

    /**
     * Returns a row from current resultset as an array .
     *
     * @param string|null $sql Some SQL to be executed.
     * @return array|null The fetched row as an array
     */
    public function fetchRow(?string $sql = null): ?array
    {
        if (!empty($sql) && is_string($sql) && strlen($sql) > 5) {
            if (!$this->execute($sql)) {
                return null;
            }
        }

        if (!$this->hasResult()) {
            return null;
        } else {
            $resultRow = $this->_result->FetchRow();
            $this->resultSet($resultRow);

            return $this->fetchResult();
        }
    }

    /**
     * Begin a transaction
     *
     * @return bool True on success, false on fail
     * (i.e. if the database/model does not support transactions).
     */
    public function begin()
    {
        if (parent::begin()) {
            if ($this->_adodb->BeginTrans()) {
                $this->_transactionStarted = true;

                return true;
            }
        }

        return false;
    }

    /**
     * Commit a transaction
     *
     * @return bool True on success, false on fail
     * (i.e. if the database/model does not support transactions,
     * or a transaction has not started).
     */
    public function commit()
    {
        if (parent::commit()) {
            $this->_transactionStarted = false;

            return $this->_adodb->CommitTrans();
        }

        return false;
    }

    /**
     * Rollback a transaction
     *
     * @return bool True on success, false on fail
     * (i.e. if the database/model does not support transactions,
     * or a transaction has not started).
     */
    public function rollback()
    {
        if (parent::rollback()) {
            return $this->_adodb->RollbackTrans();
        }

        return false;
    }

    /**
     * Returns an array of tables in the database. If there are no tables, an error is raised and the application exits.
     *
     * @param mixed $data
     * @return array Array of tablenames in the database
     */
    public function listSources($data = null)
    {
        $tables = $this->_adodb->MetaTables('TABLES');

        if (!count($tables) > 0) {
            trigger_error('[Database] Couldn\'t get table list, check database config', E_USER_NOTICE);
            exit;
        }

        return $tables;
    }

    /**
     * Returns an array of the fields in the table used by the given model.
     *
     * @param AppModel $model Model object
     * @return array Fields in table. Keys are name and type
     */
    public function describe($model)
    {
        $cache = parent::describe($model);
        if ($cache != null) {
            return $cache;
        }

        $fields = false;
        $cols = $this->_adodb->MetaColumns($this->fullTableName($model, false));

        foreach ($cols as $column) {
            $fields[$column->name] = [
                'type' => $this->column($column->type),
                'null' => !$column->not_null,
                'length' => $column->max_length,
            ];
            if ($column->has_default) {
                $fields[$column->name]['default'] = $column->default_value;
            }
            if ($column->primary_key == 1) {
                $fields[$column->name]['key'] = 'primary';
            }
        }

        $this->_cacheDescription($this->fullTableName($model, false), $fields);

        return $fields;
    }

    /**
     * Returns a formatted error message from previous database operation.
     *
     * @return string Error message
     */
    public function lastError()
    {
        return $this->_adodb->ErrorMsg();
    }

    /**
     * Returns number of affected rows in previous database operation, or false if no previous operation exists.
     *
     * @param mixed $source
     * @return int Number of affected rows
     */
    public function lastAffected($source = null)
    {
        return $this->_adodb->Affected_Rows();
    }

    /**
     * Returns number of rows in previous resultset, or false if no previous resultset exists.
     *
     * @param mixed $source
     * @return int Number of rows in resultset
     */
    public function lastNumRows($source = null)
    {
        return $this->_result ? $this->_result->RecordCount() : false;
    }

    /**
     * Returns the ID generated from the previous INSERT operation.
     *
     * @param mixed $source
     * @return int Returns the last autonumbering ID inserted. Returns false if function not supported.
     */
    public function lastInsertId($source = null)
    {
        return $this->_adodb->Insert_ID();
    }

    /**
     * Returns a LIMIT statement in the correct format for the particular database.
     *
     * @param int $limit Limit of results returned
     * @param int $offset Offset from which to start results
     * @return string SQL limit/offset statement
     * @todo Please change output string to whatever select your database accepts. adodb doesn't allow us to get the correct limit string out of it.
     */
    public function limit($limit, $offset = null)
    {
        if ($limit) {
            $rt = '';
            if (!strpos(strtolower($limit), 'limit') || strpos(strtolower($limit), 'limit') === 0) {
                $rt = ' LIMIT';
            }

            if ($offset) {
                $rt .= ' ' . $offset . ',';
            }

            $rt .= ' ' . $limit;

            return $rt;
        }

        return null;
        // please change to whatever select your database accepts
        // adodb doesn't allow us to get the correct limit string out of it
    }

    /**
     * Converts database-layer column types to basic types
     *
     * @param string $real Real database-layer column type (i.e. "varchar(255)")
     * @return string Abstract column type (i.e. "string")
     */
    public function column($real)
    {
        $metaTypes = array_flip($this->_adodbColumnTypes);

        $interpretedType = $this->_adodbMetatyper->MetaType($real);

        if (!isset($metaTypes[$interpretedType])) {
            return 'text';
        }

        return $metaTypes[$interpretedType];
    }

    /**
     * Returns a quoted and escaped string of $data for use in an SQL statement.
     *
     * @param string $data String to be prepared for use in an SQL statement
     * @param string $column The type of the column into which this data will be inserted
     * @param bool $null Whether or not numeric data should be handled automagically if no column data is provided
     * @return string Quoted and escaped data
     */
    public function value($data, $column = null, $null = false)
    {
        // Handle arrays
        if (is_array($data) && !empty($data)) {
            return array_map(
                [&$this, 'value'],
                $data,
                array_fill(0, count($data), $column),
            );
        }

        // Handle NULL
        if ($data === null || (is_array($data) && empty($data))) {
            return 'NULL';
        }

        // Handle empty strings
        if ($data === '') {
            return $null ? 'NULL' : "''";
        }

        // Use ADOdb's qstr for quoting
        if ($this->_adodb && method_exists($this->_adodb, 'qstr')) {
            return $this->_adodb->qstr($data);
        }

        // Fallback for testing without ADOdb connection
        return "'" . str_replace("'", "''", $data) . "'";
    }

    /**
     * Generates the fields list of an SQL query.
     *
     * @param Model $model
     * @param string $alias Alias tablename
     * @param mixed $fields
     * @param bool $quote
     * @return array
     */
    public function fields(Model $model, $alias = null, $fields = [], $quote = true)
    {
        if (empty($alias)) {
            $alias = $model->alias;
        }

        // Get fields from schema if empty
        $allFields = empty($fields);
        if ($allFields) {
            $fields = array_keys($model->schema());
        } elseif (!is_array($fields)) {
            $fields = [$fields];
        }

        $fields = array_values(array_filter($fields));

        if (!$quote) {
            return $fields;
        }
        $count = count($fields);

        if ($count >= 1 && $fields[0] !== '*' && strpos($fields[0], 'COUNT(*)') === false) {
            for ($i = 0; $i < $count; $i++) {
                if (!preg_match('/^.+\\(.*\\)/', $fields[$i]) && !preg_match('/\s+AS\s+/', $fields[$i])) {
                    $prepend = '';
                    if (strpos($fields[$i], 'DISTINCT') !== false) {
                        $prepend = 'DISTINCT ';
                        $fields[$i] = trim(str_replace('DISTINCT', '', $fields[$i]));
                    }

                    if (strrpos($fields[$i], '.') === false) {
                        $fields[$i] = $prepend . $this->name($alias) . '.' . $this->name($fields[$i]) . ' AS ' . $this->name($alias . '__' . $fields[$i]);
                    } else {
                        $build = explode('.', $fields[$i]);
                        $fields[$i] = $prepend . $this->name($build[0]) . '.' . $this->name($build[1]) . ' AS ' . $this->name($build[0] . '__' . $build[1]);
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Build ResultSets and map data
     *
     * @param array $results
     */
    public function resultSet(&$results)
    {
        $numFields = count($results);
        $fields = array_keys($results);
        $this->results =& $results;
        $this->map = [];
        $index = 0;
        $j = 0;

        while ($j < $numFields) {
            $columnName = $fields[$j];

            if (strpos($columnName, '__')) {
                $parts = explode('__', $columnName);
                $this->map[$index++] = [$parts[0], $parts[1]];
            } else {
                $this->map[$index++] = [0, $columnName];
            }
            $j++;
        }
    }

    /**
     * Fetches the next row from the current result set
     *
     * @return unknown
     */
    public function fetchResult()
    {
        if (!empty($this->results)) {
            $row = $this->results;
            $this->results = null;
        } else {
            $row = $this->_result->FetchRow();
        }

        if (empty($row)) {
            return false;
        }

        $resultRow = [];
        $fields = array_keys($row);
        $count = count($fields);
        $i = 0;
        for ($i = 0; $i < $count; $i++) { //$row as $index => $field) {
            [$table, $column] = $this->map[$i];
            $resultRow[$table][$column] = $row[$fields[$i]];
        }

        return $resultRow;
    }

    /**
     * Generate a database-native column schema string
     *
     * @param array $column An array structured like the following: array('name'=>'value', 'type'=>'value'[, options]),
     *                      where options can be 'default', 'length', or 'key'.
     * @return string
     */
    public function buildColumn($column)
    {
        $name = $type = null;
        extract(array_merge(['null' => true], $column));

        if (empty($name) || empty($type)) {
            trigger_error('Column name or type not defined in schema', E_USER_WARNING);

            return null;
        }

        //$metaTypes = array_flip($this->_adodbColumnTypes);
        if (!isset($this->_adodbColumnTypes[$type])) {
            trigger_error("Column type {$type} does not exist", E_USER_WARNING);

            return null;
        }
        $metaType = $this->_adodbColumnTypes[$type];
        $concreteType = $this->_adodbDataDict->ActualType($metaType);
        $real = $this->columns[$type];

        //UUIDs are broken so fix them.
        if ($type === 'string' && isset($real['length']) && $real['length'] == 36) {
            $concreteType = 'CHAR';
        }

        $out = $this->name($name) . ' ' . $concreteType;

        if (isset($real['limit']) || isset($real['length']) || isset($column['limit']) || isset($column['length'])) {
            if (isset($column['length'])) {
                $length = $column['length'];
            } elseif (isset($column['limit'])) {
                $length = $column['limit'];
            } elseif (isset($real['length'])) {
                $length = $real['length'];
            } else {
                $length = $real['limit'];
            }
            $out .= '(' . $length . ')';
        }
        $_notNull = $_default = $_autoInc = $_constraint = $_unsigned = false;

        if (isset($column['key']) && $column['key'] === 'primary' && $type === 'integer') {
            $_constraint = '';
            $_autoInc = true;
        } elseif (isset($column['key']) && $column['key'] === 'primary') {
            $_notNull = '';
        } elseif (isset($column['default']) && isset($column['null']) && $column['null'] == false) {
            $_notNull = true;
            $_default = $column['default'];
        } elseif (isset($column['null']) && $column['null'] == true) {
            $_notNull = false;
            $_default = 'NULL';
        }
        if (isset($column['default']) && $_default == false) {
            $_default = $this->value($column['default']);
        }
        if (isset($column['null']) && $column['null'] == false) {
            $_notNull = true;
        }
        //use concrete instance of DataDict to make the suffixes for us.
        $out .= $this->_adodbDataDict->_CreateSuffix($out, $metaType, $_notNull, $_default, $_autoInc, $_constraint, $_unsigned);

        return $out;
    }

    /**
     * Checks if the result is valid
     *
     * @return bool True if the result is valid, else false
     */
    public function hasResult(): bool
    {
        return is_object($this->_result) && !$this->_result->EOF;
    }

    /**
     * Executes given SQL statement.
     *
     * @param string $sql SQL statement
     * @param array $options Options array
     * @param array $params Parameters to bind
     * @return ADORecordSet|null Result resource identifier
     */
    public function execute($sql, $options = [], $params = []): ?ADORecordSet
    {
        $this->_result = $this->_execute($sql, $params);

        return $this->_result;
    }

    /**
     * Returns a quoted name of $data for use in an SQL statement.
     * Strips fields out of SQL functions before quoting.
     *
     * @param mixed $data Either a string with a column to quote. An array of columns to quote or an
     *   object from DboSource::expression() or DboSource::identifier()
     * @return array|string SQL field
     */
    public function name($data)
    {
        if (is_array($data)) {
            foreach ($data as $i => $dataItem) {
                $data[$i] = $this->name($dataItem);
            }

            return $data;
        }
        $data = trim($data);
        if ($data === '*') {
            return '*';
        }
        if (preg_match('/^[\w-]+(?:\.[^ \*]*)*$/', $data)) { // string, string.string
            if (!str_contains($data, '.')) { // string
                return $this->startQuote . $data . $this->endQuote;
            }
            $items = explode('.', $data);

            return $this->startQuote . implode($this->endQuote . '.' . $this->startQuote, $items) . $this->endQuote;
        }
        if (preg_match('/^[\w-]+\.\*$/', $data)) { // string.*
            return $this->startQuote . str_replace('.*', $this->endQuote . '.*', $data);
        }

        return $data;
    }

    /**
     * Returns the $field name with the table prefix if exists.
     *
     * @param Model|string $model Model object or table name
     * @param bool $quote Whether you want the table name quoted.
     * @param bool $schema Whether you want the schema name included.
     * @return string Full table name
     */
    public function fullTableName($model, $quote = true, $schema = true)
    {
        if (is_object($model)) {
            $table = $model->tablePrefix . $model->table;
        } elseif (!empty($this->config['prefix']) && !str_starts_with($model, $this->config['prefix'])) {
            $table = $this->config['prefix'] . strval($model);
        } else {
            $table = strval($model);
        }

        if ($quote) {
            return $this->name($table);
        }

        return $table;
    }

    /**
     * Cache the description of a table
     *
     * @param string $object The name of the object (table)
     * @param mixed $data The description of the table
     * @return mixed
     */
    protected function _cacheDescription($object, $data = null)
    {
        if ($this->cacheSources === false) {
            return null;
        }

        if ($data !== null) {
            $this->_descriptions[$object] =& $data;
        }

        if (isset($this->_descriptions[$object])) {
            return $this->_descriptions[$object];
        }

        return null;
    }
}
