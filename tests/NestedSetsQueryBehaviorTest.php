<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use tests\models\MultipleRootsTree;
use tests\models\Tree;
use Yii;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

/**
 * NestedSetsQueryBehaviorTest
 */
class NestedSetsQueryBehaviorTest extends DatabaseTestCase
{
    public function testRoots()
    {
        $this->assertEquals(
            require(__DIR__ . '/data/test-roots-query.php'),
            ArrayHelper::toArray(Tree::find()->roots()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-roots-multiple-roots-query.php'),
            ArrayHelper::toArray(MultipleRootsTree::find()->roots()->all())
        );
    }

    public function testLeaves()
    {
        $this->assertEquals(
            require(__DIR__ . '/data/test-leaves-query.php'),
            ArrayHelper::toArray(Tree::find()->leaves()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-leaves-multiple-roots-query.php'),
            ArrayHelper::toArray(MultipleRootsTree::find()->leaves()->all())
        );
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        Yii::$app->set('db', [
            'class' => Connection::className(),
            'dsn' => 'sqlite::memory:',
        ]);

        Yii::$app->getDb()->open();
        $lines = explode(';', file_get_contents(__DIR__ . '/migrations/sqlite.sql'));

        foreach ($lines as $line) {
            if (trim($line) !== '') {
                Yii::$app->getDb()->pdo->exec($line);
            }
        }
    }
}
