<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets-behavior
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use tests\models\MultipleRootsTree;
use tests\models\Tree;

/**
 * NestedSetsBehaviorTest
 */
class NestedSetsBehaviorTest extends DatabaseTestCase
{
    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::makeRoot
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     */
    public function testMakeNewRoot()
    {
        $node = new Tree();
        $node->id = 1;
        $node->name = 'Root';
        $this->assertTrue($node->makeRoot());
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-make-new-root.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $node = new MultipleRootsTree();
        $node->id = 1;
        $node->name = 'Root';
        $this->assertTrue($node->makeRoot());
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-make-new-root.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::makeRoot
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testMakeNewRootExceptionIsRaisedWhenRootIsExists()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree();
        $node->id = 2;
        $node->name = 'Root';
        $node->makeRoot();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\base\NotSupportedException
     */
    public function testExceptionIsRaisedWhenInsertIsCalled()
    {
        $node = new Tree();
        $node->id = 1;
        $node->name = 'Node';
        $node->insert();
    }
}
