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
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
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
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
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
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
     */
    public function testPrependNewTo()
    {
        $this->markTestSkipped();
    }

    // @todo: prependTo exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
     */
    public function testAppendNewTo()
    {
        $this->markTestSkipped();
    }

    // @todo: appendTo exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
     */
    public function testInsertNewBefore()
    {
        $this->markTestSkipped();
    }

    // @todo: insertBefore exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
     */
    public function testInsertNewAfter()
    {
        $this->markTestSkipped();
    }

    // @todo: insertAfter exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::makeRoot
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testMakeExistsRoot()
    {
        $this->markTestSkipped();
    }

    // @todo: makeRoot exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testPrependExistsTo()
    {
        $this->markTestSkipped();
    }

    // @todo: prependTo exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testAppendExistsTo()
    {
        $this->markTestSkipped();
    }

    // @todo: appendTo exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testInsertExistsBefore()
    {
        $this->markTestSkipped();
    }

    // @todo: insertBefore exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testInsertExistsAfter()
    {
        $this->markTestSkipped();
    }

    // @todo: insertAfter exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::deleteWithDescendants
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeDelete
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterDelete
     */
    public function testDeleteWithDescendants()
    {
        $this->markTestSkipped();
    }

    // @todo: deleteWithDescendants exceptions tests here

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeDelete
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterDelete
     */
    public function testDelete()
    {
        $this->markTestSkipped();
    }

    // @todo: delete exceptions tests here

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
