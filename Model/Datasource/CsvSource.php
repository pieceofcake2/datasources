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
 * Read-only datasource for files with delimiter separated values (CSV, TSV).
 *
 * Each model reads the file named after its `$useTable` in the configured path.
 * The first line of the file is the header (column names).
 *
 * ```
 * // config/database.php
 * public $csv = [
 *     'datasource' => 'Datasources.CsvSource',
 *     'path' => '/path/to/files',
 *     'extension' => 'csv', // File extension
 *     'delimiter' => null, // Column delimiter. null detects ',', ';' or "\t" from the header
 *     'recursive' => false, // Whether listSources() looks into subdirectories
 * ];
 * ```
 *
 * Supported queries: conditions (equality, `IN` by an array value, `!=`, `<>`, `<`, `<=`, `>`, `>=`,
 * nested `OR` / `AND` / `NOT`), fields, limit, offset and page.
 */
class CsvSource extends DataSource
{
    /**
     * Delimiters detected from the header when the `delimiter` option is null.
     *
     * @var array<string>
     */
    protected const DETECTABLE_DELIMITERS = [',', ';', "\t"];

    /**
     * Operators supported in condition keys (e.g. `'User.age >='`).
     *
     * @var array<string>
     */
    protected const OPERATORS = ['=', '!=', '<>', '<', '<=', '>', '>='];

    /**
     * Description of the datasource.
     */
    public string $description = 'CSV Data Source';

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_baseConfig = [
        'datasource' => 'csv',
        'path' => '.',
        'extension' => 'csv',
        'delimiter' => null,
        'readonly' => true,
        'recursive' => false,
    ];

    /**
     * Folder of the files.
     */
    protected ?Folder $folder = null;

    /**
     * Constructor
     *
     * @param array<string, mixed> $config Configuration
     * @param bool $autoConnect Whether to connect immediately
     */
    public function __construct($config = [], $autoConnect = true)
    {
        parent::__construct($config);
        if ($autoConnect) {
            $this->connect();
        }
    }

    /**
     * Opens the folder of the files.
     *
     * @return bool
     */
    public function connect(): bool
    {
        $create = !$this->config['readonly'];
        $this->folder = new Folder($this->config['path'], $create, $create ? 0777 : false);
        $this->connected = $this->folder->path !== null;

        return $this->connected;
    }

    /**
     * Closes the folder.
     *
     * @return bool Always true
     */
    public function close(): bool
    {
        $this->folder = null;
        $this->connected = false;

        return true;
    }

    /**
     * Lists the available files (without the extension).
     *
     * @param mixed $data Unused
     * @return array<string>|null
     */
    public function listSources($data = null): ?array
    {
        $this->config['database'] = 'csv';
        $cache = parent::listSources();
        if ($cache !== null) {
            unset($this->config['database']);

            return $cache;
        }

        $pattern = '.*\.' . preg_quote($this->config['extension'], '/');
        if ($this->config['recursive']) {
            $prefix = rtrim($this->folder->path, DS) . DS;
            $list = array_map(
                fn(string $file): string => substr($file, strlen($prefix)),
                $this->folder->findRecursive($pattern, true),
            );
        } else {
            $list = $this->folder->find($pattern, true);
        }
        $extension = '.' . $this->config['extension'];
        $list = array_map(fn(string $file): string => substr($file, 0, -strlen($extension)), $list);

        parent::listSources($list);
        unset($this->config['database']);

        return $list;
    }

    /**
     * Returns the schema of the model's file. All columns are strings.
     *
     * @param Model|string $model Model or table name
     * @return array<string, array<string, mixed>>
     * @throws MissingTableException When the file does not exist
     */
    public function describe($model): array
    {
        $table = $model instanceof Model ? $model->table : $model;
        $schema = [];
        foreach ($this->readHeader($table)['fields'] as $field) {
            $schema[$field] = ['type' => 'string', 'null' => true, 'default' => null, 'length' => null];
        }

        return $schema;
    }

    /**
     * Reads records.
     *
     * @param Model $model Model
     * @param array<string, mixed> $queryData Query
     * @param int|null $recursive Unused
     * @return array
     * @throws MissingTableException When the file does not exist
     * @throws InvalidArgumentException When a condition is not supported
     */
    public function read(Model $model, $queryData = [], $recursive = null): array
    {
        $conditions = (array)($queryData['conditions'] ?? []);
        $records = [];
        foreach ($this->readRows($model->table) as $row) {
            if ($this->matchesConditions($row, $conditions, $model->alias)) {
                $records[] = $row;
            }
        }

        if ($model->findQueryType === 'count') {
            return [[['count' => count($records)]]];
        }

        $limit = empty($queryData['limit']) ? null : (int)$queryData['limit'];
        $offset = (int)($queryData['offset'] ?? 0);
        if ($limit !== null && $offset === 0 && !empty($queryData['page'])) {
            $offset = ((int)$queryData['page'] - 1) * $limit;
        }
        $records = array_slice($records, $offset, $limit);

        $fields = array_flip($this->normalizeFields((array)($queryData['fields'] ?? []), $model->alias));

        return array_map(
            fn(array $row): array => [$model->alias => $fields ? array_intersect_key($row, $fields) : $row],
            $records,
        );
    }

    /**
     * Calculation is done in read() (only COUNT is supported).
     *
     * @param Model $model Model
     * @param string $func Function
     * @param array $params Parameters
     * @return array<string, bool>
     */
    public function calculate(Model $model, $func, $params = []): array
    {
        return ['count' => true];
    }

    /**
     * Reads the header of a file.
     *
     * @param string $table Table (file name without the extension)
     * @return array{delimiter: string, fields: array<string>}
     * @throws MissingTableException When the file does not exist
     */
    protected function readHeader(string $table): array
    {
        $file = $this->openFile($table);
        // Remove the UTF-8 BOM added by spreadsheet applications
        $line = (string)preg_replace('/\A\xEF\xBB\xBF/', '', (string)$file->fgets());
        $line = rtrim($line, "\r\n");

        $delimiter = $this->config['delimiter'] ?? $this->detectDelimiter($line);
        $fields = array_map('trim', str_getcsv($line, $delimiter, '"', '\\'));

        return ['delimiter' => $delimiter, 'fields' => $fields];
    }

    /**
     * Reads the rows of a file as arrays keyed by the column names.
     *
     * Rows with fewer columns than the header are skipped, and extra columns are ignored.
     *
     * @param string $table Table (file name without the extension)
     * @return Generator<array<string, string>>
     * @throws MissingTableException When the file does not exist
     */
    protected function readRows(string $table): Generator
    {
        ['delimiter' => $delimiter, 'fields' => $fields] = $this->readHeader($table);
        $count = count($fields);

        $file = $this->openFile($table);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($delimiter, '"', '\\');
        foreach ($file as $index => $values) {
            if ($index === 0 || !is_array($values) || count($values) < $count) {
                continue;
            }

            yield array_combine($fields, array_slice($values, 0, $count));
        }
    }

    /**
     * Opens a file.
     *
     * @param string $table Table (file name without the extension)
     * @return SplFileObject
     * @throws MissingTableException When the file does not exist
     */
    protected function openFile(string $table): SplFileObject
    {
        $path = rtrim($this->config['path'], DS) . DS . $table . '.' . $this->config['extension'];
        if (!is_file($path)) {
            throw new MissingTableException(['table' => $table, 'class' => static::class, 'ds' => $this->configKeyName ?? 'csv']);
        }

        return new SplFileObject($path, 'r');
    }

    /**
     * Detects the delimiter used most in the header.
     *
     * @param string $header Header line
     * @return string
     */
    protected function detectDelimiter(string $header): string
    {
        $counts = [];
        foreach (self::DETECTABLE_DELIMITERS as $delimiter) {
            $counts[$delimiter] = substr_count($header, $delimiter);
        }
        arsort($counts);

        return (string)array_key_first($counts);
    }

    /**
     * Removes the model alias from field names (find('list') passes 'Model.field').
     *
     * @param array $fields Fields
     * @param string $alias Model alias
     * @return array<string>
     */
    protected function normalizeFields(array $fields, string $alias): array
    {
        return array_values(array_map(
            fn($field): string => $this->stripAlias((string)$field, $alias),
            $fields,
        ));
    }

    /**
     * Whether a row matches the conditions (combined with AND).
     *
     * @param array<string, string> $row Row
     * @param array $conditions Conditions
     * @param string $alias Model alias
     * @return bool
     * @throws InvalidArgumentException When a condition is not supported
     */
    protected function matchesConditions(array $row, array $conditions, string $alias): bool
    {
        foreach ($conditions as $key => $value) {
            if (is_int($key)) {
                if (!is_array($value)) {
                    throw new InvalidArgumentException('CsvSource does not support SQL conditions: ' . $value);
                }
                $matched = $this->matchesConditions($row, $value, $alias);
            } else {
                $matched = match (strtoupper($key)) {
                    'OR' => $this->matchesAny($row, (array)$value, $alias),
                    'AND' => $this->matchesConditions($row, (array)$value, $alias),
                    'NOT' => !$this->matchesConditions($row, (array)$value, $alias),
                    default => $this->matchesCondition($row, $key, $value, $alias),
                };
            }

            if (!$matched) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether a row matches any of the conditions.
     *
     * @param array<string, string> $row Row
     * @param array $conditions Conditions
     * @param string $alias Model alias
     * @return bool
     */
    protected function matchesAny(array $row, array $conditions, string $alias): bool
    {
        foreach ($conditions as $key => $value) {
            if ($this->matchesConditions($row, [$key => $value], $alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a row matches a condition such as `['User.id' => 1]` or `['User.age >=' => 20]`.
     *
     * @param array<string, string> $row Row
     * @param string $key Field with an optional operator
     * @param mixed $expected Expected value (an array means IN, or NOT IN with `!=`)
     * @param string $alias Model alias
     * @return bool
     * @throws InvalidArgumentException When the operator is not supported
     */
    protected function matchesCondition(array $row, string $key, $expected, string $alias): bool
    {
        $parts = preg_split('/\s+/', trim($key), 2);
        $field = $this->stripAlias($parts[0], $alias);
        $operator = $parts[1] ?? '=';
        if (!in_array($operator, self::OPERATORS, true)) {
            throw new InvalidArgumentException('CsvSource does not support the operator: ' . $operator);
        }
        if (!array_key_exists($field, $row)) {
            return false;
        }

        if (is_array($expected)) {
            $in = false;
            foreach ($expected as $item) {
                if ($this->compare($row[$field], '=', $item)) {
                    $in = true;
                    break;
                }
            }

            return in_array($operator, ['!=', '<>'], true) ? !$in : $in;
        }

        return $this->compare($row[$field], $operator, $expected);
    }

    /**
     * Compares a value. Numeric values are compared as numbers, others as strings.
     *
     * @param string $actual Value in the file
     * @param '='|'!='|'<>'|'<'|'<='|'>'|'>=' $operator Operator
     * @param mixed $expected Expected value (null matches an empty value)
     * @return bool
     */
    protected function compare(string $actual, string $operator, $expected): bool
    {
        if ($expected === null) {
            return in_array($operator, ['!=', '<>'], true) ? $actual !== '' : $actual === '';
        }

        $expected = is_bool($expected) ? (string)(int)$expected : (string)$expected;
        $result = is_numeric($actual) && is_numeric($expected)
            ? (float)$actual <=> (float)$expected
            : strcmp($actual, $expected);

        return match ($operator) {
            '=' => $result === 0,
            '!=', '<>' => $result !== 0,
            '<' => $result < 0,
            '<=' => $result <= 0,
            '>' => $result > 0,
            '>=' => $result >= 0,
        };
    }

    /**
     * Removes the model alias from a field name.
     *
     * @param string $field Field
     * @param string $alias Model alias
     * @return string
     */
    protected function stripAlias(string $field, string $alias): string
    {
        return str_starts_with($field, $alias . '.') ? substr($field, strlen($alias) + 1) : $field;
    }
}
