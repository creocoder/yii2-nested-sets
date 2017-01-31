<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests;

use tests\models\MultipleTree;
use tests\models\Tree;
use Yii;
use yii\db\Connection;
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

        $node = new Tree(['name' => 'Root']);
        $this->assertTrue($node->makeRoot()->save());

        $node = new MultipleTree(['name' => 'Root 1']);
        $this->assertTrue($node->makeRoot()->save());

        $node = new MultipleTree(['name' => 'Root 2']);
        $this->assertTrue($node->makeRoot()->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-make-root-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testMakeRootNewExceptionIsRaisedWhenTreeAttributeIsFalseAndRootIsExists()
    {
        $node = new Tree(['name' => 'Root']);
        $node->makeRoot()->save();
    }

    public function testPrependToNew()
    {
        $node = new Tree(['name' => 'New node']);
        $this->assertTrue($node->prependTo(Tree::findOne(9))->save());

        $node = new MultipleTree(['name' => 'New node']);
        $this->assertTrue($node->prependTo(MultipleTree::findOne(31))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToNewExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->prependTo(new Tree())->save();
    }

    public function testAppendToNew()
    {
        $node = new Tree(['name' => 'New node']);
        $this->assertTrue($node->appendTo(Tree::findOne(9))->save());

        $node = new MultipleTree(['name' => 'New node']);
        $this->assertTrue($node->appendTo(MultipleTree::findOne(31))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendNewToExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->appendTo(new Tree())->save();
    }

    public function testInsertBeforeNew()
    {
        $node = new Tree(['name' => 'New node']);
        $this->assertTrue($node->insertBefore(Tree::findOne(9))->save());

        $node = new MultipleTree(['name' => 'New node']);
        $this->assertTrue($node->insertBefore(MultipleTree::findOne(31))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeNewExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertBefore(new Tree())->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeNewExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertBefore(Tree::findOne(1))->save();
    }

    public function testInsertAfterNew()
    {
        $node = new Tree(['name' => 'New node']);
        $this->assertTrue($node->insertAfter(Tree::findOne(9))->save());

        $node = new MultipleTree(['name' => 'New node']);
        $this->assertTrue($node->insertAfter(MultipleTree::findOne(31))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-new.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterNewExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertAfter(new Tree())->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterNewExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = new Tree(['name' => 'New node']);
        $node->insertAfter(Tree::findOne(1))->save();
    }

    public function testMakeRootExists()
    {
        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->makeRoot()->save());

        $dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-make-root-exists.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testMakeRootExistsExceptionIsRaisedWhenTreeAttributeIsFalse()
    {
        $node = Tree::findOne(9);
        $node->makeRoot()->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testMakeRootExistsExceptionIsRaisedWhenItsRoot()
    {
        $node = MultipleTree::findOne(23);
        $node->makeRoot()->save();
    }

    public function testPrependToExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(Tree::findOne(2))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(MultipleTree::findOne(24))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testPrependToExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(Tree::findOne(16))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(MultipleTree::findOne(38))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testPrependToExistsAnotherTree()
    {
        $node = MultipleTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->prependTo(MultipleTree::findOne(53))->save());

        $dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-prepend-to-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->prependTo(new Tree())->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(9))->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testPrependToExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->prependTo(Tree::findOne(11))->save();
    }

    public function testAppendToExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(Tree::findOne(2))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(MultipleTree::findOne(24))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testAppendToExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(Tree::findOne(16))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(MultipleTree::findOne(38))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testAppendToExistsAnotherTree()
    {
        $node = MultipleTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->appendTo(MultipleTree::findOne(53))->save());

        $dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-append-to-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendToExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->appendTo(new Tree())->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendToExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(9))->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testAppendToExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->appendTo(Tree::findOne(11))->save();
    }

    public function testInsertBeforeExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(Tree::findOne(2))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(MultipleTree::findOne(24))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertBeforeExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(Tree::findOne(16))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(MultipleTree::findOne(38))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertBeforeExistsAnotherTree()
    {
        $node = MultipleTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertBefore(MultipleTree::findOne(53))->save());

        $dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-before-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(new Tree())->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(9))->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(11))->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertBeforeExistsExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = Tree::findOne(9);
        $node->insertBefore(Tree::findOne(1))->save();
    }

    public function testInsertAfterExistsUp()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(Tree::findOne(2))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(MultipleTree::findOne(24))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-up.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertAfterExistsDown()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(Tree::findOne(16))->save());

        $node = MultipleTree::findOne(31);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(MultipleTree::findOne(38))->save());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-down.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    public function testInsertAfterExistsAnotherTree()
    {
        $node = MultipleTree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertTrue($node->insertAfter(MultipleTree::findOne(53))->save());

        $dataSet = $this->getConnection()->createDataSet(['multiple_tree']);
        $expectedDataSet = $this->createFlatXMLDataSet(__DIR__ . '/data/test-insert-after-exists-another-tree.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsNewRecord()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(new Tree())->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsSame()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(9))->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsChild()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(11))->save();
    }

    /**
     * @expectedException \yii\db\Exception
     */
    public function testInsertAfterExistsExceptionIsRaisedWhenTargetIsRoot()
    {
        $node = Tree::findOne(9);
        $node->insertAfter(Tree::findOne(1))->save();
    }

    public function testDeleteWithChildren()
    {
        $this->assertEquals(7, Tree::findOne(9)->deleteWithChildren());
        $this->assertEquals(7, MultipleTree::findOne(31)->deleteWithChildren());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
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
        $this->assertEquals(1, MultipleTree::findOne(31)->delete());

        $dataSet = $this->getConnection()->createDataSet(['tree', 'multiple_tree']);
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
        $node = new Tree(['name' => 'Node']);
        $node->insert();
    }

    public function testUpdate()
    {
        $node = Tree::findOne(9);
        $node->name = 'Updated node 2';
        $this->assertEquals(1, $node->update());
    }

    public function testParents()
    {
        $this->assertEquals(
            require(__DIR__ . '/data/test-parents.php'),
            ArrayHelper::toArray(Tree::findOne(11)->parents()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-parents-multiple-tree.php'),
            ArrayHelper::toArray(MultipleTree::findOne(33)->parents()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-parents-with-depth.php'),
            ArrayHelper::toArray(Tree::findOne(11)->parents(1)->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-parents-multiple-tree-with-depth.php'),
            ArrayHelper::toArray(MultipleTree::findOne(33)->parents(1)->all())
        );
    }

    public function testChildren()
    {
        $this->assertEquals(
            require(__DIR__ . '/data/test-children.php'),
            ArrayHelper::toArray(Tree::findOne(9)->children()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-children-multiple-tree.php'),
            ArrayHelper::toArray(MultipleTree::findOne(31)->children()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-children-with-depth.php'),
            ArrayHelper::toArray(Tree::findOne(9)->children(1)->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-children-multiple-tree-with-depth.php'),
            ArrayHelper::toArray(MultipleTree::findOne(31)->children(1)->all())
        );
    }

    public function testLeaves()
    {
        $this->assertEquals(
            require(__DIR__ . '/data/test-leaves.php'),
            ArrayHelper::toArray(Tree::findOne(9)->leaves()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-leaves-multiple-tree.php'),
            ArrayHelper::toArray(MultipleTree::findOne(31)->leaves()->all())
        );
    }

    public function testPrev()
    {
        $this->assertEquals(
            require(__DIR__ . '/data/test-prev.php'),
            ArrayHelper::toArray(Tree::findOne(9)->prev()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-prev-multiple-tree.php'),
            ArrayHelper::toArray(MultipleTree::findOne(31)->prev()->all())
        );
    }

    public function testNext()
    {
        $this->assertEquals(
            require(__DIR__ . '/data/test-next.php'),
            ArrayHelper::toArray(Tree::findOne(9)->next()->all())
        );

        $this->assertEquals(
            require(__DIR__ . '/data/test-next-multiple-tree.php'),
            ArrayHelper::toArray(MultipleTree::findOne(31)->next()->all())
        );
    }

    public function testIsRoot()
    {
        $this->assertTrue(Tree::findOne(1)->isRoot());
        $this->assertFalse(Tree::findOne(2)->isRoot());
    }

    public function testIsChildOf()
    {
        $node = MultipleTree::findOne(26);
        $this->assertTrue($node->isChildOf(MultipleTree::findOne(25)));
        $this->assertTrue($node->isChildOf(MultipleTree::findOne(23)));
        $this->assertFalse($node->isChildOf(MultipleTree::findOne(3)));
        $this->assertFalse($node->isChildOf(MultipleTree::findOne(1)));
    }

    public function testIsLeaf()
    {
        $this->assertTrue(Tree::findOne(4)->isLeaf());
        $this->assertFalse(Tree::findOne(1)->isLeaf());
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        try {
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
        } catch (\Exception $e) {
            Yii::$app->clear('db');
        }
    }
}
