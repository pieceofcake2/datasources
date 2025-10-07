<?php
/**
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
 * @since         CakePHP Datasources v 0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('XmlrpcSource', 'Datasources.Model/Datasource');

/**
 * XML RPC Testing Model
 */
class XmlrpcModel extends CakeTestModel
{
    /**
     * Table to use
     *
     * @var mixed
     */
    public $useTable = false;

    /**
     * Database Configuration
     *
     * @var string
     */
    public $useDbConfig = 'test_xmlrpc';

    /**
     * Get State Name
     *
     * @param string $number State Number
     * @return mixed
     */
    public function getStateName($number): mixed
    {
        $params = ['examples.getStateName', [$number], &$this];
        $db = ConnectionManager::getDataSource($this->useDbConfig);

        return call_user_func_array([$db, 'query'], $params);
    }
}

/**
 * XML RPC Test class
 */
class XmlrpcTestSource extends XmlrpcSource
{
    /**
     * Query
     *
     * @param string $method XML-RPC method name
     * @param array $params List with XML-RPC parameters
     * @param Model $model Reference to model (unused)
     * @return mixed Response of XML-RPC Server. If return false, $this->error contain a error message.
     */
    public function query($method, $params = [], $model = null): mixed
    {
        return [$method, $params, $model];
    }
}

/**
 * XML RPC Datasource Test
 */
class XmlrpcSourceTest extends CakeTestCase
{
    /**
     * XML RPC Source Instance
     *
     * @var XmlrpcSource
     */
    public $Xmlrpc = null;

    /**
     * Set up for Tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->Xmlrpc = new XmlrpcSource();
    }

    /**
     * testGenerateXMLWithoutParams
     *
     * @return void
     */
    public function testGenerateXMLWithoutParams(): void
    {
        $header = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";
        $expected = $header . '<methodCall><methodName>test</methodName><params/></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test'));
    }

    /**
     * testGenerateXMLOneParam
     *
     * @return void
     */
    public function testGenerateXMLOneParam(): void
    {
        $header = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";

        // Integer
        $expected = $header .
            '<methodCall><methodName>test</methodName><params><param><value><int>1</int></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [1]));

        // Double
        $expected = $header .
            '<methodCall><methodName>test</methodName><params><param><value><double>5.2</double></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [5.2]));

        // String
        $expected = $header .
            '<methodCall><methodName>test</methodName><params><param><value><string>testing</string></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', ['testing']));

        // Boolean
        $expected = $header .
            '<methodCall><methodName>test</methodName><params><param><value><boolean>0</boolean></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [false]));
        $expected = $header .
            '<methodCall><methodName>test</methodName><params><param><value><boolean>1</boolean></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [true]));

        // Array
        $expected = $header . '<methodCall><methodName>test</methodName><params><param><value><array><data/></array></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [[]]));
        $expected = $header . '<methodCall><methodName>test</methodName><params><param><value><array><data><value><int>12</int></value><value><string>Egypt</string></value><value><boolean>0</boolean></value><value><int>-31</int></value></data></array></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [[12, 'Egypt', false, -31]]));

        // Struct
        $expected = $header . '<methodCall><methodName>test</methodName><params><param><value><struct><member><name>lowerBound</name><value><int>18</int></value></member><member><name>upperBound</name><value><int>139</int></value></member></struct></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [['lowerBound' => 18, 'upperBound' => 139]]));
    }

    /**
     * testGenerateXMLMultiParams
     *
     * @return void
     */
    public function testGenerateXMLMultiParams(): void
    {
        $header = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";

        $expected = $header . '<methodCall><methodName>test</methodName><params><param><value><int>1</int></value></param><param><value><string>testing</string></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [1, 'testing']));

        $expected = $header . '<methodCall><methodName>test</methodName><params>';
        $expected .= '<param><value><array><data><value><int>12</int></value><value><string>Egypt</string></value><value><boolean>0</boolean></value><value><int>-31</int></value></data></array></value></param>';
        $expected .= '<param><value><int>1</int></value></param>';
        $expected .= '<param><value><struct><member><name>test</name><value><boolean>1</boolean></value></member></struct></value></param>';
        $expected .= '</params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [[12, 'Egypt', false, -31], 1, ['test' => true]]));
    }

    /**
     * testGenerateXMLMultiDimensions
     *
     * @return void
     */
    public function testGenerateXMLMultiDimensions(): void
    {
        $header = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' . "\n";

        // Array
        $expected = $header . '<methodCall><methodName>test</methodName><params><param><value><array><data><value><int>1</int></value><value><array><data><value><int>2</int></value><value><string>b</string></value></data></array></value></data></array></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [[1, [2, 'b']]]));

        // Struct
        $expected = $header . '<methodCall><methodName>test</methodName><params><param><value><struct><member><name>base</name><value><struct><member><name>value</name><value><double>-50.72</double></value></member></struct></value></member></struct></value></param></params></methodCall>' . "\n";
        $this->assertEquals($expected, $this->Xmlrpc->generateXML('test', [['base' => ['value' => -50.720]]]));
    }

    /**
     * testParseResponse
     *
     * @return void
     */
    public function testParseResponse(): void
    {
        // Integer
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><int>555</int></value></param></params></methodResponse>';
        $this->assertEquals(555, $this->Xmlrpc->parseResponse($xml));
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><i4>555</i4></value></param></params></methodResponse>';
        $this->assertEquals(555, $this->Xmlrpc->parseResponse($xml));

        // Double
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><double>57.20</double></value></param></params></methodResponse>';
        $this->assertEquals(57.2, $this->Xmlrpc->parseResponse($xml));

        // String
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><string>South Dakota</string></value></param></params></methodResponse>';
        $this->assertEquals('South Dakota', $this->Xmlrpc->parseResponse($xml));

        // Boolean
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><boolean>1</boolean></value></param></params></methodResponse>';
        $this->assertEquals(true, $this->Xmlrpc->parseResponse($xml));

        // Array
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><array><data></data></array></value></param></params></methodResponse>';
        $this->assertEquals([], $this->Xmlrpc->parseResponse($xml));
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><array><data><value><int>1</int></value><value><string>testing</string></value></data></array></value></param></params></methodResponse>';
        $this->assertEquals([1, 'testing'], $this->Xmlrpc->parseResponse($xml));
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><array><data><value><array><data><value><string>a</string></value><value><string>b</string></value></data></array></value><value><string>testing</string></value></data></array></value></param></params></methodResponse>';
        $this->assertEquals([['a', 'b'], 'testing'], $this->Xmlrpc->parseResponse($xml));

        // Struct
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><struct><member><name>test</name><value><string>testing</string></value></member><member><name>boolean</name><value><boolean>1</boolean></value></member></struct></value></param></params></methodResponse>';
        $this->assertEquals(['test' => 'testing', 'boolean' => true], $this->Xmlrpc->parseResponse($xml));

        /*
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><struct><member><name>test</name><value><struct><member><name>a</name><value><string>b</string></value></member><member><name>c</name><value><string>d</string></value></member></struct></value></member><member><name>test2</name><value><array><data><value><int>1</int></value><value><i4>2</i4></data></array></value></member></struct></value></param></params></methodResponse>';
        $this->assertEquals(array('test' => array('a' => 'b', 'c' => 'd'), 'test2' => array(1, 2)), $this->Xmlrpc->parseResponse($xml));
        */

        // Struct in Array
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><array><data><value><struct><member><name>longitude</name><value><string>53</string></value></member><member><name>altitude</name><value><string>8.72543</string></value></member></struct></value></data></array></value></param></params></methodResponse>';
        $this->assertEquals([['longitude' => 53, 'altitude' => 8.72543]], $this->Xmlrpc->parseResponse($xml));

        // Array in Array
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><array><data><value><array><data><value><int>12</int></value></data></array></value></data></array></value></param></params></methodResponse>';
        $this->assertEquals([[12]], $this->Xmlrpc->parseResponse($xml));
    }

    /**
     * testParseResponseError
     *
     * @return void
     */
    public function testParseResponseError(): void
    {
        $xml = '<?xml version="1.0"?><methodResponse><fault><value><struct><member><name>faultCode</name><value><int>4</int></value></member><member><name>faultString</name><value><string>Too many parameters.</string></value></member></struct></value></fault></methodResponse>';
        $this->assertFalse($this->Xmlrpc->parseResponse($xml));
        $this->assertEquals(4, $this->Xmlrpc->errno);
        $this->assertEquals('Too many parameters.', $this->Xmlrpc->error);

        $xml = '<?xml version="1.0"?><methodInvalid><params /></methodInvalid>';
        $this->assertFalse($this->Xmlrpc->parseResponse($xml));
        $this->assertEquals(-32700, $this->Xmlrpc->errno);
        $this->assertFalse(empty($this->Xmlrpc->error));

        // This is a valid response, but the error must be cleared
        $xml = '<?xml version="1.0"?><methodResponse><params><param><value><boolean>0</boolean></value></param></params></methodResponse>';
        $this->assertFalse($this->Xmlrpc->parseResponse($xml));
        $this->assertEquals(0, $this->Xmlrpc->errno);
        $this->assertTrue(empty($this->Xmlrpc->error));
    }

    /**
     * testRequest
     *
     * @return void
     */
    public function testRequest(): void
    {
        $this->markTestSkipped('External XML-RPC test server (phpxmlrpc.sourceforge.net) is no longer available');

        // All nice
        $config = [
            'host' => 'phpxmlrpc.sourceforge.net',
            'port' => 80,
            'url' => '/server.php',
        ];
        $Xmlrpc = new XmlrpcSource($config);
        $this->assertEquals('Alabama', $Xmlrpc->query('examples.getStateName', [1]));

        $this->assertEquals(5, $Xmlrpc->query('examples.addtwo', [2, 3]));
        $this->assertTrue(is_array($Xmlrpc->query('system.listMethods')));

        // Not 200 (no connection)
        $config = [
            'host' => 'invalid.host',
            'port' => 80,
            'url' => '/RPC2',
        ];
        $Xmlrpc = new XmlrpcSource($config);
        $this->assertFalse($Xmlrpc->query('examples.getStateName', [1]));
        $this->assertEquals(-32300, $Xmlrpc->errno);

        // Not 200 (HTTP 404)
        $config = [
            'host' => 'code.google.com',
            'port' => 80,
            'url' => '/InvalidPath',
        ];
        $Xmlrpc = new XmlrpcSource($config);
        $this->assertFalse($Xmlrpc->query('examples.getStateName', [1]));
        $this->assertEquals(-32300, $Xmlrpc->errno);

        // Not XML-RPC Response
        $config = [
            'host' => 'www.w3.org',
            'port' => 80,
            'url' => '/TR/1999/REC-xpath-19991116.xml',
        ];
        $Xmlrpc = new XmlrpcSource($config);
        $this->assertFalse($Xmlrpc->query('examples.getStateName', [1]));
        $this->assertEquals(-32700, $Xmlrpc->errno);
    }

    /**
     * testDescribe
     *
     * @return void
     */
    public function testDescribe(): void
    {
        $this->markTestSkipped('External XML-RPC test server (phpxmlrpc.sourceforge.net) is no longer available');

        $config = [
            'host' => 'phpxmlrpc.sourceforge.net',
            'port' => 80,
            'url' => '/server.php',
        ];
        $Xmlrpc = new XmlrpcSource($config);
        $result = $Xmlrpc->describe(null);
        $this->assertTrue(is_array($result));
        $this->assertTrue(in_array('examples.getStateName', $result));

        // Not XML-RPC Response
        $config = [
            'host' => 'groups.google.com',
            'port' => 80,
            'url' => '/group/cake-php/feed/rss_v2_0_msgs.xml',
        ];
        $Xmlrpc = new XmlrpcSource($config);
        $this->assertFalse($Xmlrpc->describe(null));
    }

    /**
     * testWithModel
     *
     * @return void
     */
    public function testWithModel(): void
    {
        $connection = [
            'datasource' => 'Datasources.Datasource/XmlrpcTestSource',
        ];
        App::uses('XmlrpcTestSource', 'Datasources.Model/Datasource');
        ConnectionManager::create('test_xmlrpc', $connection);
        $model = ClassRegistry::init('XmlrpcModel');

        // Test implemented method in model
        $result = $model->getStateName(1);
        $expected = ['examples.getStateName', [1], $model];
        $this->assertEquals($expected, $result);

        // Test with call__
        $result = $model->someFunction();
        $expected = ['someFunction', [], $model];
        $this->assertEquals($expected, $result);
    }
}
