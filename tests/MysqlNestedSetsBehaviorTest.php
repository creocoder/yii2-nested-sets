<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use Yii;
use yii\db\Connection;

/**
 * MysqlNestedSetsBehaviorTest
 */
class MysqlNestedSetsBehaviorTest extends NestedSetsBehaviorTest
{
    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        Yii::$app->set('db', [
            'class' => Connection::className(),
            'dsn' => 'mysql:host=localhost;dbname=yii2_nested_sets_test',
            'username' => 'root',
            'password' => '',
        ]);

        Yii::$app->getDb()->open();
        $lines = explode(';', file_get_contents(__DIR__ . '/migrations/mysql.sql'));

        foreach ($lines as $line) {
            if (trim($line) !== '') {
                Yii::$app->getDb()->pdo->exec($line);
            }
        }
    }
}
