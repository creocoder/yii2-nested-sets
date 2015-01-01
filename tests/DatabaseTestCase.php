<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets-behavior
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

/**
 * DatabaseTestCase
 */
abstract class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @inheritdoc
     */
    public function getConnection()
    {
        return $this->createDefaultDBConnection(\Yii::$app->db->pdo);
    }

    /**
     * @inheritdoc
     */
    public function getDataSet()
    {
        return $this->createFlatXMLDataSet(__DIR__ . '/datasets/_init.xml');
    }
}
