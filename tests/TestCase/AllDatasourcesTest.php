<?php
/**
 * All Datasources plugin tests
 */
class AllDatasourcesTest extends CakeTestCase
{
    /**
     * Suite define the tests for this suite
     *
     * @return CakeTestSuite
     */
    public static function suite(): CakeTestSuite
    {
        $suite = new CakeTestSuite('All Datasources test');

        $path = CakePlugin::path('Datasources') . 'tests' . DS . 'TestCase' . DS;
        $suite->addTestDirectoryRecursive($path);

        return $suite;
    }
}
