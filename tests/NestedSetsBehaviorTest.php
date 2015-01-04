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
    public function testMakeRootNew()
    {
        $dataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/clean.xml');
        $this->getDatabaseTester()->setDataSet($dataSet);
        $this->getDatabaseTester()->onSetUp();

        $node = new Tree(['id' => 1, 'name' => 'Root']);
        $this->assertTrue($node->makeRoot());

        $node = new MultipleRootsTree(['id' => 1, 'name' => 'Root']);
        $this->assertTrue($node->makeRoot());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-make-root-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testMakeRootNewExceptionIsRaisedWhenTreeAttributeIsFalseAndRootIsExists()
    {
        $node = new Tree(['id' => 2, 'name' => 'Root']);
        $node->makeRoot();
    }

    public function testPrependToNew()
    {
        $node = new Tree(['id' => 23, 'name' => 'New node']);
        $this->assertTrue($node->prependTo(Tree::findOne(9)));

        $node = new MultipleRootsTree(['id' => 67, 'name' => 'New node']);
        $this->assertTrue($node->prependTo(MultipleRootsTree::findOne(31)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToNewExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->prependTo(new Tree());
    }

    public function testAppendToNew()
    {
        $node = new Tree(['id' => 23, 'name' => 'New node']);
        $this->assertTrue($node->appendTo(Tree::findOne(9)));

        $node = new MultipleRootsTree(['id' => 67, 'name' => 'New node']);
        $this->assertTrue($node->appendTo(MultipleRootsTree::findOne(31)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendNewToExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->appendTo(new Tree());
    }

    public function testInsertBeforeNew()
    {
        $node = new Tree(['id' => 23, 'name' => 'New node']);
        $this->assertTrue($node->insertBefore(Tree::findOne(9)));

        $node = new MultipleRootsTree(['id' => 67, 'name' => 'New node']);
        $this->assertTrue($node->insertBefore(MultipleRootsTree::findOne(31)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeNewExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertBefore(new Tree());
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeNewExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertBefore(Tree::findOne(1));
    }

    public function testInsertAfterNew()
    {
        $node = new Tree(['id' => 23, 'name' => 'New node']);
        $this->assertTrue($node->insertAfter(Tree::findOne(9)));

        $node = new MultipleRootsTree(['id' => 67, 'name' => 'New node']);
        $this->assertTrue($node->insertAfter(MultipleRootsTree::findOne(31)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterNewExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertAfter(new Tree());
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterNewExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertAfter(Tree::findOne(1));
    }

    public function testMakeRootExists()
    {
        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->makeRoot());

        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-make-root-exists.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testMakeRootExistsExceptionIsRaisedWhenTreeAttributeIsFalse()
    {
        $node = Tree::findOne(9);
        $node->makeRoot();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testMakeRootExistsExceptionIsRaisedWhenItsRoot()
    {
        $node = MultipleRootsTree::findOne(23);
        $node->makeRoot();
    }

    public function testPrependToExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(Tree::findOne(2)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(MultipleRootsTree::findOne(24)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testPrependToExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(Tree::findOne(16)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(MultipleRootsTree::findOne(38)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testPrependToExistsAnotherTree()
    {
        $node = MultipleRootsTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(MultipleRootsTree::findOne(53)));

        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->prependTo(new Tree());
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(9));
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(11));
    }

    public function testAppendToExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(Tree::findOne(2)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(MultipleRootsTree::findOne(24)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testAppendToExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(Tree::findOne(16)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(MultipleRootsTree::findOne(38)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testAppendToExistsAnotherTree()
    {
        $node = MultipleRootsTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(MultipleRootsTree::findOne(53)));

        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendToExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->appendTo(new Tree());
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendToExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(9));
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendToExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(11));
    }

    public function testInsertBeforeExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(Tree::findOne(2)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(MultipleRootsTree::findOne(24)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertBeforeExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(Tree::findOne(16)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(MultipleRootsTree::findOne(38)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertBeforeExistsAnotherTree()
    {
        $node = MultipleRootsTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(MultipleRootsTree::findOne(53)));

        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(new Tree());
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(9));
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(11));
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(1));
    }

    public function testInsertAfterExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(Tree::findOne(2)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(MultipleRootsTree::findOne(24)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertAfterExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(Tree::findOne(16)));

        $node = MultipleRootsTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(MultipleRootsTree::findOne(38)));

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertAfterExistsAnotherTree()
    {
        $node = MultipleRootsTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(MultipleRootsTree::findOne(53)));

        $dataSet = $this->getConnection()->createDataSet(['multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(new Tree());
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(9));
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(11));
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(1));
    }

    public function testDeleteWithChildren()
    {
        $this->assertEquals(7, Tree::findOne(9)->deleteWithChildren());
        $this->assertEquals(7, MultipleRootsTree::findOne(31)->deleteWithChildren());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-delete-with-children.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testDeleteWithChildrenExceptionIsRaisedWhenNodeIsNewRecord()
    {
        $node = new Tree();
        $node->deleteWithChildren();
    }

    public function testDelete()
    {
        $this->assertEquals(1, Tree::findOne(9)->delete());
        $this->assertEquals(1, MultipleRootsTree::findOne(31)->delete());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_roots_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-delete.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testDeleteExceptionIsRaisedWhenNodeIsNewRecord()
    {
        $node = new Tree();
        $node->delete();
    }

    /**
     * @expectedException \yii\base\NotSupportedException
     */
    public function testDeleteExceptionIsRaisedWhenNodeIsRoot()
    {
        $node = Tree::findOne(1);
        $node->delete();
    }

    /**
     * @expectedException \yii\base\NotSupportedException
     */
    public function testExceptionIsRaisedWhenInsertIsCalled()
    {
        $node = new Tree(['id' => 1, 'name' => 'Node']);
        $node->insert();
    }

    public function testUpdate()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertEquals(1, $node->update());
    }

    public function testChildren()
    {
        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::findOne(9)->children()->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::findOne(31)->children()->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-children.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::findOne(9)->children(1)->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::findOne(31)->children(1)->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-children-with-depth.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testParents()
    {
        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::findOne(11)->parents()->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::findOne(33)->parents()->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-parents.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);

        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::findOne(11)->parents(1)->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::findOne(33)->parents(1)->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-parents-with-depth.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testPrev()
    {
        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::findOne(9)->prev()->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::findOne(31)->prev()->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prev.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testNext()
    {
        $dataSet = new ArrayDataSet([
            'tree' => ArrayHelper::toArray(Tree::findOne(9)->next()->all()),
            'multiple_roots_tree' => ArrayHelper::toArray(MultipleRootsTree::findOne(31)->next()->all()),
        ]);

        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-next.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testIsChildOf()
    {
        $node = MultipleRootsTree::findOne(26);
        $this->assertTrue($node->isChildOf(MultipleRootsTree::findOne(25)));
        $this->assertTrue($node->isChildOf(MultipleRootsTree::findOne(23)));
        $this->assertFalse($node->isChildOf(MultipleRootsTree::findOne(3)));
        $this->assertFalse($node->isChildOf(MultipleRootsTree::findOne(1)));
    }

    public function testIsLeaf()
    {
        $this->assertTrue(Tree::findOne(4)->isLeaf());
        $this->assertFalse(Tree::findOne(1)->isLeaf());
    }

    public function testIsRoot()
    {
        $this->assertTrue(Tree::findOne(1)->isRoot());
        $this->assertFalse(Tree::findOne(2)->isRoot());
    }
}
