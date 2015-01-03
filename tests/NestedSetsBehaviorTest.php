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
     * @expectedException \yii\db\Exception
     */
    public function testMakeNewRootExceptionIsRaisedWhenTreeAttributeIsFalseAndRootIsExists()
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
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree();
        $node->id = 23;
        $node->name = 'New node';
        $this->assertTrue($node->prependTo(Tree::findOne(9)));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-prepend-new-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new MultipleRootsTree();
        $node->id = 67;
        $node->name = 'New node';
        $this->assertTrue($node->prependTo(MultipleRootsTree::findOne(31)));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-prepend-new-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testPrependNewToExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->prependTo(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testPrependNewToExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree(['id' => 9, 'name' => 'New node']);
        $node->prependTo(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
     */
    public function testAppendNewTo()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree();
        $node->id = 23;
        $node->name = 'New node';
        $this->assertTrue($node->appendTo(Tree::findOne(9)));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-append-new-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new MultipleRootsTree();
        $node->id = 67;
        $node->name = 'New node';
        $this->assertTrue($node->appendTo(MultipleRootsTree::findOne(31)));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-append-new-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testAppendNewToExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->appendTo(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testAppendNewToExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree(['id' => 9, 'name' => 'New node']);
        $node->appendTo(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
     */
    public function testInsertNewBefore()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree();
        $node->id = 23;
        $node->name = 'New node';
        $this->assertTrue($node->insertBefore(Tree::findOne(9)));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-insert-new-before.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new MultipleRootsTree();
        $node->id = 67;
        $node->name = 'New node';
        $this->assertTrue($node->insertBefore(MultipleRootsTree::findOne(31)));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-new-before.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testInsertNewBeforeExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertBefore(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testInsertNewBeforeExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree(['id' => 9, 'name' => 'New node']);
        $node->insertBefore(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testInsertNewBeforeExceptionIsRaisedWhenTargetIsRoot()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree(['name' => 'New node']);
        $node->insertBefore(Tree::findOne(1));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterInsert
     */
    public function testInsertNewAfter()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree();
        $node->id = 23;
        $node->name = 'New node';
        $this->assertTrue($node->insertAfter(Tree::findOne(9)));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-insert-new-after.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new MultipleRootsTree();
        $node->id = 67;
        $node->name = 'New node';
        $this->assertTrue($node->insertAfter(MultipleRootsTree::findOne(31)));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-new-after.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testInsertNewAfterExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertAfter(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testInsertNewAfterExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree(['id' => 9, 'name' => 'New node']);
        $node->insertAfter(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeInsert
     * @expectedException \yii\db\Exception
     */
    public function testInsertNewAfterExceptionIsRaisedWhenTargetIsRoot()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = new Tree(['name' => 'New node']);
        $node->insertAfter(Tree::findOne(1));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::makeRoot
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testMakeExistsRoot()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->makeRoot());
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-make-exists-root.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::makeRoot
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testMakeExistsRootExceptionIsRaisedWhenTreeAttributeIsFalse()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->makeRoot();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::makeRoot
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testMakeExistsRootExceptionIsRaisedWhenItsRoot()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(23);
        $node->makeRoot();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testPrependExistsTo()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(2));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-prepend-exists-up-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(16));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-prepend-exists-down-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->prependTo(MultipleRootsTree::findOne(24));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-prepend-exists-up-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->prependTo(MultipleRootsTree::findOne(38));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-prepend-exists-down-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(9);
        $node->prependTo(MultipleRootsTree::findOne(53));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-prepend-exists-another-tree-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testPrependExistsToExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->prependTo(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testPrependExistsToExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prependTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testPrependExistsToExceptionIsRaisedWhenTargetIsDescendant()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(11));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testAppendExistsTo()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(2));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-append-exists-up-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(16));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-append-exists-down-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->appendTo(MultipleRootsTree::findOne(24));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-append-exists-up-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->appendTo(MultipleRootsTree::findOne(38));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-append-exists-down-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(9);
        $node->appendTo(MultipleRootsTree::findOne(53));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-append-exists-another-tree-to.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testAppendExistsToExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->appendTo(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testAppendExistsToExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::appendTo
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testAppendExistsToExceptionIsRaisedWhenTargetIsDescendant()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(11));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testInsertExistsBefore()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(2));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-insert-exists-before-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(16));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-insert-exists-before-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->insertBefore(MultipleRootsTree::findOne(24));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-exists-before-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->insertBefore(MultipleRootsTree::findOne(38));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-exists-before-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(9);
        $node->insertBefore(MultipleRootsTree::findOne(53));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-exists-before-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testInsertExistsBeforeExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertBefore(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testInsertExistsBeforeExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertBefore
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testInsertExistsBeforeExceptionIsRaisedWhenTargetIsDescendant()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(11));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterUpdate
     */
    public function testInsertExistsAfter()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(2));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-insert-exists-after-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(16));
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-insert-exists-after-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->insertAfter(MultipleRootsTree::findOne(24));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-exists-after-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $node->insertAfter(MultipleRootsTree::findOne(38));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-exists-after-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(9);
        $node->insertAfter(MultipleRootsTree::findOne(53));
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-insert-exists-after-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testInsertExistsAfterExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertAfter(new Tree());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testInsertExistsAfterExceptionIsRaisedWhenTargetIsSame()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(9));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::insertAfter
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeUpdate
     * @expectedException \yii\db\Exception
     */
    public function testInsertExistsAfterExceptionIsRaisedWhenTargetIsDescendant()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(11));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::deleteWithDescendants
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeDelete
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterDelete
     */
    public function testDeleteWithDescendants()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $this->assertEquals(7, Tree::findOne(9)->deleteWithDescendants());
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-delete-with-descendants.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $this->assertEquals(7, MultipleRootsTree::findOne(31)->deleteWithDescendants());
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-delete-with-descendants.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::deleteWithDescendants
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeDelete
     * @expectedException \yii\db\Exception
     */
    public function testDeleteWithDescendantsExceptionIsRaisedWhenNodeIsNewRecord()
    {
        $node = new Tree();
        $node->deleteWithDescendants();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeDelete
     * @covers \creocoder\nestedsets\NestedSetsBehavior::afterDelete
     */
    public function testDelete()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $this->assertEquals(1, Tree::findOne(9)->delete());
        $dataSet = $this->getConnection()->createDataSet(['tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-delete.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $this->assertEquals(1, MultipleRootsTree::findOne(31)->delete());
        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-delete.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeDelete
     * @expectedException \yii\db\Exception
     */
    public function testDeleteExceptionIsRaisedWhenNodeIsNewRecord()
    {
        $node = new Tree();
        $node->delete();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::beforeDelete
     * @expectedException \yii\base\NotSupportedException
     */
    public function testDeleteExceptionIsRaisedWhenNodeIsRoot()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(1);
        $node->delete();
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

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::descendants
     */
    public function testDescendants()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = Tree::findOne(9);
        $models = $node->descendants()->all();
        $dataSet = new ArrayDataSet(['tree' => ArrayHelper::toArray($models)]);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree-after-descendants.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(31);
        $models = $node->descendants()->all();
        $dataSet = new ArrayDataSet(['multiple_roots_tree' => ArrayHelper::toArray($models)]);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree-after-descendants.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::children
     */
    public function testChildren()
    {
        $this->markTestSkipped();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::ancestors
     */
    public function testAncestors()
    {
        $this->markTestSkipped();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::parent
     */
    public function testParent()
    {
        $this->markTestSkipped();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::prev
     */
    public function testPrev()
    {
        $this->markTestSkipped();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::next
     */
    public function testNext()
    {
        $this->markTestSkipped();
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::isDescendantOf
     */
    public function testIsDescendantOf()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/multiple-roots-tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $node = MultipleRootsTree::findOne(26);
        $this->assertTrue($node->isDescendantOf(MultipleRootsTree::findOne(25)));
        $this->assertTrue($node->isDescendantOf(MultipleRootsTree::findOne(23)));
        $this->assertFalse($node->isDescendantOf(MultipleRootsTree::findOne(3)));
        $this->assertFalse($node->isDescendantOf(MultipleRootsTree::findOne(1)));
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::isLeaf
     */
    public function testIsLeaf()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $this->assertTrue(Tree::findOne(4)->isLeaf());
        $this->assertFalse(Tree::findOne(1)->isLeaf());
    }

    /**
     * @covers \creocoder\nestedsets\NestedSetsBehavior::isRoot
     */
    public function testIsRoot()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/datasets/tree.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();
        $this->assertTrue(Tree::findOne(1)->isRoot());
        $this->assertFalse(Tree::findOne(2)->isRoot());
    }
}
