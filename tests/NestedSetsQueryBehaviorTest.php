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
        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::find()->roots()->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::find()->roots()->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-roots-query.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testLeaves()
    {
        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::find()->leaves()->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::find()->leaves()->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-leaves-query.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }
}
