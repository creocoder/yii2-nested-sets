<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets-behavior
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
    /**
     * @covers \creocoder\nestedsets\NestedSetsQueryBehavior::roots
     */
    public function testRoots()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $models = Tree::find()->roots()->all();
        $dataSet = new ArrayDataSet(['tree' => ArrayHelper::toArray($models)]);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-query-roots.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $models = MultipleRootsTree::find()->roots()->all();
        $dataSet = new ArrayDataSet(['multiple_roots_tree' => ArrayHelper::toArray($models)]);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-query-roots.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsQueryBehavior::leaf
     */
    public function testLeaf()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $models = Tree::find()->leaf()->all();
        $dataSet = new ArrayDataSet(['tree' => ArrayHelper::toArray($models)]);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-query-leaf.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $models = MultipleRootsTree::find()->leaf()->all();
        $dataSet = new ArrayDataSet(['multiple_roots_tree' => ArrayHelper::toArray($models)]);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-query-leaf.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }
}
