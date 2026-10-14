<?php
/**
 * SoapSource Test file
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('SoapSource', 'Datasources.Model/Datasource');

/**
 * SoapSource that behaves as if the SOAP extension was not available
 */
class SoapSourceWithoutExtension extends SoapSource
{
    /**
     * Simulates a missing SOAP extension
     *
     * @return array|false
     */
    protected function _parseConfig()
    {
        return false;
    }
}

/**
 * SoapSource Test
 */
class SoapSourceTest extends CakeTestCase
{
    /**
     * Configuration for a non-WSDL SOAP client that points to a closed port
     *
     * @var array
     */
    public $config = [
        'wsdl' => null,
        'location' => 'http://127.0.0.1:1/soap',
        'uri' => 'urn:test',
    ];

    /**
     * Skip the tests when the SOAP extension is not loaded
     *
     * @return void
     */
    public function skip(): void
    {
        $this->skipIf(!class_exists('SoapClient'), 'SOAP extension is not available');
    }

    /**
     * testConnect
     *
     * @return void
     */
    public function testConnect(): void
    {
        $source = new SoapSource($this->config);

        $this->assertTrue($source->connected);
        $this->assertInstanceOf(SoapClient::class, $source->client);
        $this->assertFalse($source->error);
    }

    /**
     * testConnectWithoutExtension
     *
     * @return void
     */
    public function testConnectWithoutExtension(): void
    {
        $source = new SoapSourceWithoutExtension($this->config);

        $this->assertFalse($source->connected);
        $this->assertNull($source->client);
        $this->assertFalse($source->connect());
    }

    /**
     * testClose
     *
     * @return void
     */
    public function testClose(): void
    {
        $source = new SoapSource($this->config);

        $this->assertTrue($source->close());
        $this->assertFalse($source->connected);
        $this->assertNull($source->client);
    }

    /**
     * testQueryWithoutArguments
     *
     * @return void
     */
    public function testQueryWithoutArguments(): void
    {
        $source = new SoapSource($this->config);

        $this->assertFalse($source->query());
        $this->assertFalse($source->error);
    }

    /**
     * testQueryWhenNotConnected
     *
     * @return void
     */
    public function testQueryWhenNotConnected(): void
    {
        $source = new SoapSource($this->config);
        $source->close();

        $this->assertFalse($source->query('test', []));
    }

    /**
     * testQueryWithSoapFault
     *
     * @return void
     */
    public function testQueryWithSoapFault(): void
    {
        Configure::write('debug', 0);
        $source = new SoapSource($this->config);

        $this->assertFalse($source->query('test', []));
        $this->assertIsString($source->error);
        $this->assertNotEmpty($source->error);
    }

    /**
     * testShowError
     *
     * @return void
     */
    public function testShowError(): void
    {
        Configure::write('debug', 2);
        $source = new SoapSource($this->config);
        $source->error = 'Something failed';

        $errors = [];
        set_error_handler(function (int $errno, string $errstr) use (&$errors): bool {
            $errors[] = [$errno, $errstr];

            return true;
        });
        try {
            $source->showError();
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $errors);
        $this->assertSame(E_USER_WARNING, $errors[0][0]);
        $this->assertStringContainsString('Something failed', $errors[0][1]);
    }

    /**
     * testShowErrorWithResult
     *
     * @return void
     */
    public function testShowErrorWithResult(): void
    {
        Configure::write('debug', 2);
        $source = new SoapSource($this->config);

        $this->expectOutputString('<p><b>Result:</b> response </p>');
        $source->showError('response');
    }
}
