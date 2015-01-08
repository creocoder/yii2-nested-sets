<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use tests\models\MultipleRootsTree;
use tests\models\Tree;
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
}
