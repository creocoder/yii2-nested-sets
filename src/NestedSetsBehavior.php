<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets-behavior
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace creocoder\nestedsets;

use yii\base\Behavior;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;

/**
 * NestedSetsBehavior
 *
 * @property \yii\db\ActiveRecord $owner
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class NestedSetsBehavior extends Behavior
{
    const OPERATION_MAKE_ROOT = 'makeRoot';
    const OPERATION_PREPEND_TO = 'prependTo';
    const OPERATION_APPEND_TO = 'appendTo';
    const OPERATION_INSERT_BEFORE = 'insertBefore';
    const OPERATION_INSERT_AFTER = 'insertAfter';
    const OPERATION_DELETE_WITH_CHILDREN = 'deleteWithChildren';

    /**
     * @var string
     */
    public $leftAttribute = 'lft';
    /**
     * @var string
     */
    public $rightAttribute = 'rgt';
    /**
     * @var string|false
     */
    public $treeAttribute = false;
    /**
     * @var string
     */
    public $depthAttribute = 'depth';
    /**
     * @var string|null
     */
    protected $operation;
    /**
     * @var \yii\db\ActiveRecord|null
     */
    protected $node;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * Creates the root node if the active record is new or moves it
     * as the root node.
     * @param boolean $runValidation
     * @param array $attributes
     * @return boolean
     */
    public function makeRoot($runValidation = true, $attributes = null)
    {
        $this->operation = self::OPERATION_MAKE_ROOT;

        return $this->owner->save($runValidation, $attributes);
    }

    /**
     * Creates a node as the first child of the target node if the active
     * record is new or moves it as the first child of the target node.
     * @param \yii\db\ActiveRecord $node
     * @param boolean $runValidation
     * @param array $attributes
     * @return boolean
     */
    public function prependTo($node, $runValidation = true, $attributes = null)
    {
        $this->operation = self::OPERATION_PREPEND_TO;
        $this->node = $node;

        return $this->owner->save($runValidation, $attributes);
    }

    /**
     * Creates a node as the last child of the target node if the active
     * record is new or moves it as the last child of the target node.
     * @param \yii\db\ActiveRecord $node
     * @param boolean $runValidation
     * @param array $attributes
     * @return boolean
     */
    public function appendTo($node, $runValidation = true, $attributes = null)
    {
        $this->operation = self::OPERATION_APPEND_TO;
        $this->node = $node;

        return $this->owner->save($runValidation, $attributes);
    }

    /**
     * Creates a node as the previous sibling of the target node if the active
     * record is new or moves it as the previous sibling of the target node.
     * @param \yii\db\ActiveRecord $node
     * @param boolean $runValidation
     * @param array $attributes
     * @return boolean
     */
    public function insertBefore($node, $runValidation = true, $attributes = null)
    {
        $this->operation = self::OPERATION_INSERT_BEFORE;
        $this->node = $node;

        return $this->owner->save($runValidation, $attributes);
    }

    /**
     * Creates a node as the next sibling of the target node if the active
     * record is new or moves it as the next sibling of the target node.
     * @param \yii\db\ActiveRecord $node
     * @param boolean $runValidation
     * @param array $attributes
     * @return boolean
     */
    public function insertAfter($node, $runValidation = true, $attributes = null)
    {
        $this->operation = self::OPERATION_INSERT_AFTER;
        $this->node = $node;

        return $this->owner->save($runValidation, $attributes);
    }

    /**
     * Deletes a node and its children.
     * @return integer|false the number of rows deleted or false if
     * the deletion is unsuccessful for some reason.
     * @throws \Exception
     */
    public function deleteWithChildren()
    {
        $this->operation = self::OPERATION_DELETE_WITH_CHILDREN;

        if (!$this->owner->isTransactional(ActiveRecord::OP_DELETE)) {
            return $this->deleteWithChildrenInternal();
        }

        $transaction = $this->owner->getDb()->beginTransaction();

        try {
            $result = $this->deleteWithChildrenInternal();

            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }

            return $result;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return integer|false the number of rows deleted or false if
     * the deletion is unsuccessful for some reason.
     */
    protected function deleteWithChildrenInternal()
    {
        if (!$this->owner->beforeDelete()) {
            return false;
        }

        $condition = [
            'and',
            ['>=', $this->leftAttribute, $this->owner->getAttribute($this->leftAttribute)],
            ['<=', $this->rightAttribute, $this->owner->getAttribute($this->rightAttribute)]
        ];

        $this->applyTreeAttributeCondition($condition);
        $result = $this->owner->deleteAll($condition);
        $this->owner->setOldAttributes(null);
        $this->owner->afterDelete();

        return $result;
    }

    /**
     * Gets the children of the node.
     * @param integer $depth the depth
     * @return \yii\db\ActiveQuery
     */
    public function children($depth = null)
    {
        $condition = [
            'and',
            ['>', $this->leftAttribute, $this->owner->getAttribute($this->leftAttribute)],
            ['<', $this->rightAttribute, $this->owner->getAttribute($this->rightAttribute)],
        ];

        if ($depth !== null) {
            $condition[] = ['<=', $this->depthAttribute, $this->owner->getAttribute($this->depthAttribute) + $depth];
        }

        $this->applyTreeAttributeCondition($condition);

        return $this->owner->find()->andWhere($condition)->addOrderBy([$this->leftAttribute => SORT_ASC]);
    }

    /**
     * Gets the ancestors of the node.
     * @param integer $depth the depth
     * @return \yii\db\ActiveQuery
     */
    public function parents($depth = null)
    {
        $condition = [
            'and',
            ['<', $this->leftAttribute, $this->owner->getAttribute($this->leftAttribute)],
            ['>', $this->rightAttribute, $this->owner->getAttribute($this->rightAttribute)],
        ];

        if ($depth !== null) {
            $condition[] = ['>=', $this->depthAttribute, $this->owner->getAttribute($this->depthAttribute) - $depth];
        }

        $this->applyTreeAttributeCondition($condition);

        return $this->owner->find()->andWhere($condition)->addOrderBy([$this->leftAttribute => SORT_ASC]);
    }

    /**
     * Gets the previous sibling of the node.
     * @return \yii\db\ActiveQuery
     */
    public function prev()
    {
        $condition = [$this->rightAttribute => $this->owner->getAttribute($this->leftAttribute) - 1];
        $this->applyTreeAttributeCondition($condition);

        return $this->owner->find()->andWhere($condition);
    }

    /**
     * Gets the next sibling of the node.
     * @return \yii\db\ActiveQuery
     */
    public function next()
    {
        $condition = [$this->leftAttribute => $this->owner->getAttribute($this->rightAttribute) + 1];
        $this->applyTreeAttributeCondition($condition);

        return $this->owner->find()->andWhere($condition);
    }

    /**
     * Determines whether the node is child of the parent node.
     * @param \yii\db\ActiveRecord $node the parent node
     * @return boolean whether the node is child of the parent node
     */
    public function isChildOf($node)
    {
        $result = ($this->owner->getAttribute($this->leftAttribute) > $node->getAttribute($this->leftAttribute))
            && ($this->owner->getAttribute($this->rightAttribute) < $node->getAttribute($this->rightAttribute));

        if ($this->treeAttribute !== false) {
            $result = $result
                && ($this->owner->getAttribute($this->treeAttribute) === $node->getAttribute($this->treeAttribute));
        }

        return $result;
    }

    /**
     * Determines whether the node is leaf.
     * @return boolean whether the node is leaf
     */
    public function isLeaf()
    {
        return $this->owner->getAttribute($this->rightAttribute) - $this->owner->getAttribute($this->leftAttribute) === 1;
    }

    /**
     * Determines whether the node is root.
     * @return boolean whether the node is root
     */
    public function isRoot()
    {
        return $this->owner->getAttribute($this->leftAttribute) == 1;
    }

    /**
     * @throws Exception
     * @throws NotSupportedException
     */
    public function beforeInsert()
    {
        if ($this->node !== null && !$this->node->getIsNewRecord()) {
            $this->node->refresh();
        }

        switch ($this->operation) {
            case self::OPERATION_MAKE_ROOT:
                $this->owner->setAttribute($this->leftAttribute, 1);
                $this->owner->setAttribute($this->rightAttribute, 2);
                $this->owner->setAttribute($this->depthAttribute, 0);

                if ($this->treeAttribute === false && $this->owner->find()->roots()->exists()) {
                    throw new Exception('Can not create more than one root when "treeAttribute" is false.');
                }

                return;
            case self::OPERATION_PREPEND_TO:
                $value = $this->node->getAttribute($this->leftAttribute) + 1;
                $depth = 1;
                break;
            case self::OPERATION_APPEND_TO:
                $value = $this->node->getAttribute($this->rightAttribute);
                $depth = 1;
                break;
            case self::OPERATION_INSERT_BEFORE:
                $value = $this->node->getAttribute($this->leftAttribute);
                $depth = 0;
                break;
            case self::OPERATION_INSERT_AFTER:
                $value = $this->node->getAttribute($this->rightAttribute) + 1;
                $depth = 0;
                break;
            default:
                throw new NotSupportedException('Method "'. get_class($this->owner) . '::insert" is not supported for inserting new nodes.');
        }

        if ($this->node->getIsNewRecord()) {
            throw new Exception('Can not create a node when the target node is new record.');
        }

        if ($depth === 0 && $this->node->isRoot()) {
            throw new Exception('Can not create a node when the target node is root.');
        }

        $this->owner->setAttribute($this->leftAttribute, $value);
        $this->owner->setAttribute($this->rightAttribute, $value + 1);
        $this->owner->setAttribute($this->depthAttribute, $this->node->getAttribute($this->depthAttribute) + $depth);

        if ($this->treeAttribute !== false) {
            $this->owner->setAttribute($this->treeAttribute, $this->node->getAttribute($this->treeAttribute));
        }

        $this->shiftLeftRightAttribute($value, 2);
    }

    /**
     * @throws Exception
     */
    public function afterInsert()
    {
        if ($this->operation === self::OPERATION_MAKE_ROOT && $this->treeAttribute !== false) {
            $this->owner->setAttribute($this->treeAttribute, $this->owner->getPrimaryKey());
            $primaryKey = $this->owner->primaryKey();

            if (!isset($primaryKey[0])) {
                throw new Exception('"' . get_class($this->owner) . '" must have a primary key.');
            }

            $this->owner->updateAll(
                [$this->treeAttribute => $this->owner->getAttribute($this->treeAttribute)],
                [$primaryKey[0] => $this->owner->getAttribute($this->treeAttribute)]
            );
        }

        $this->operation = null;
        $this->node = null;
    }

    /**
     * @throws Exception
     */
    public function beforeUpdate()
    {
        if ($this->node !== null && !$this->node->getIsNewRecord()) {
            $this->node->refresh();
        }

        switch ($this->operation) {
            case self::OPERATION_MAKE_ROOT:
                if ($this->treeAttribute === false) {
                    throw new Exception('Can not move a node as the root when "treeAttribute" is false.');
                }

                if ($this->owner->isRoot()) {
                    throw new Exception('Can not move the root node as the root.');
                }

                break;
            case self::OPERATION_INSERT_BEFORE:
            case self::OPERATION_INSERT_AFTER:
                if ($this->node->isRoot()) {
                    throw new Exception('Can not move a node when the target node is root.');
                }
            case self::OPERATION_PREPEND_TO:
            case self::OPERATION_APPEND_TO:
                if ($this->node->getIsNewRecord()) {
                    throw new Exception('Can not move a node when the target node is new record.');
                }
    
                if ($this->owner->equals($this->node)) {
                    throw new Exception('Can not move a node when the target node is same.');
                }
    
                if ($this->node->isChildOf($this->owner)) {
                    throw new Exception('Can not move a node when the target node is child.');
                }
        }
    }

    /**
     * @throws Exception
     */
    public function afterUpdate()
    {
        switch ($this->operation) {
            case self::OPERATION_MAKE_ROOT:
                $this->moveNodeAsRoot();
                break;
            case self::OPERATION_PREPEND_TO:
                $this->moveNode($this->node->getAttribute($this->leftAttribute) + 1, 1);
                break;
            case self::OPERATION_APPEND_TO:
                $this->moveNode($this->node->getAttribute($this->rightAttribute), 1);
                break;
            case self::OPERATION_INSERT_BEFORE:
                $this->moveNode($this->node->getAttribute($this->leftAttribute), 0);
                break;
            case self::OPERATION_INSERT_AFTER:
                $this->moveNode($this->node->getAttribute($this->rightAttribute) + 1, 0);
                break;
            default:
                return;
        }

        $this->operation = null;
        $this->node = null;
    }

    /**
     * @return void
     */
    protected function moveNodeAsRoot()
    {
        $db = $this->owner->getDb();
        $leftValue = $this->owner->getAttribute($this->leftAttribute);
        $rightValue = $this->owner->getAttribute($this->rightAttribute);
        $depthValue = $this->owner->getAttribute($this->depthAttribute);
        $treeValue = $this->owner->getAttribute($this->treeAttribute);
        $leftAttribute = $db->quoteColumnName($this->leftAttribute);
        $rightAttribute = $db->quoteColumnName($this->rightAttribute);
        $depthAttribute = $db->quoteColumnName($this->depthAttribute);

        $this->owner->updateAll(
            [
                $this->leftAttribute => new Expression($leftAttribute . sprintf('%+d', 1 - $leftValue)),
                $this->rightAttribute => new Expression($rightAttribute . sprintf('%+d', 1 - $leftValue)),
                $this->depthAttribute => new Expression($depthAttribute  . sprintf('%+d', -$depthValue)),
                $this->treeAttribute => $this->owner->getPrimaryKey(),
            ],
            [
                'and',
                ['>=', $this->leftAttribute, $leftValue],
                ['<=', $this->rightAttribute, $rightValue],
                [$this->treeAttribute => $treeValue]
            ]
        );

        $this->shiftLeftRightAttribute($rightValue + 1, $leftValue - $rightValue - 1);
    }

    /**
     * @param integer $value
     * @param integer $depth
     * @throws Exception
     */
    protected function moveNode($value, $depth)
    {
        $db = $this->owner->getDb();
        $leftValue = $this->owner->getAttribute($this->leftAttribute);
        $rightValue = $this->owner->getAttribute($this->rightAttribute);
        $depthValue = $this->owner->getAttribute($this->depthAttribute);
        $leftAttribute = $db->quoteColumnName($this->leftAttribute);
        $rightAttribute = $db->quoteColumnName($this->rightAttribute);
        $depthAttribute = $db->quoteColumnName($this->depthAttribute);
        $nodeRootValue = $this->node->getAttribute($this->treeAttribute);
        $depth = $this->node->getAttribute($this->depthAttribute) - $depthValue + $depth;

        if ($this->treeAttribute === false || $this->owner->getAttribute($this->treeAttribute) === $nodeRootValue) {
            $delta = $rightValue - $leftValue + 1;
            $this->shiftLeftRightAttribute($value, $delta);

            if ($leftValue >= $value) {
                $leftValue += $delta;
                $rightValue += $delta;
            }

            $condition = ['and', ['>=', $this->leftAttribute, $leftValue], ['<=', $this->rightAttribute, $rightValue]];
            $this->applyTreeAttributeCondition($condition);

            $this->owner->updateAll(
                [$this->depthAttribute => new Expression($depthAttribute . sprintf('%+d', $depth))],
                $condition
            );

            foreach ([$this->leftAttribute, $this->rightAttribute] as $attribute) {
                $condition = ['and', ['>=', $attribute, $leftValue], ['<=', $attribute, $rightValue]];
                $this->applyTreeAttributeCondition($condition);

                $this->owner->updateAll(
                    [$attribute => new Expression($db->quoteColumnName($attribute) . sprintf('%+d', $value - $leftValue))],
                    $condition
                );
            }

            $this->shiftLeftRightAttribute($rightValue + 1, -$delta);
        } else {
            foreach ([$this->leftAttribute, $this->rightAttribute] as $attribute) {
                $this->owner->updateAll(
                    [$attribute => new Expression($db->quoteColumnName($attribute) . sprintf('%+d', $rightValue - $leftValue + 1))],
                    ['and', ['>=', $attribute, $value], [$this->treeAttribute => $nodeRootValue]]
                );
            }

            $delta = $value - $leftValue;

            $this->owner->updateAll(
                [
                    $this->leftAttribute => new Expression($leftAttribute . sprintf('%+d', $delta)),
                    $this->rightAttribute => new Expression($rightAttribute . sprintf('%+d', $delta)),
                    $this->depthAttribute => new Expression($depthAttribute . sprintf('%+d', $depth)),
                    $this->treeAttribute => $nodeRootValue,
                ],
                [
                    'and',
                    ['>=', $this->leftAttribute, $leftValue],
                    ['<=', $this->rightAttribute, $rightValue],
                    [$this->treeAttribute => $this->owner->getAttribute($this->treeAttribute)],
                ]
            );

            $this->shiftLeftRightAttribute($rightValue + 1, $leftValue - $rightValue - 1);
        }
    }

    /**
     * @throws Exception
     * @throws NotSupportedException
     */
    public function beforeDelete()
    {
        if ($this->owner->getIsNewRecord()) {
            throw new Exception('Can not delete a node when it is new record.');
        }

        if ($this->owner->isRoot() && $this->operation !== self::OPERATION_DELETE_WITH_CHILDREN) {
            throw new NotSupportedException('Method "'. get_class($this->owner) . '::delete" is not supported for deleting root nodes.');
        }

        $this->owner->refresh();
    }

    /**
     * @throws Exception
     */
    public function afterDelete()
    {
        $leftValue = $this->owner->getAttribute($this->leftAttribute);
        $rightValue = $this->owner->getAttribute($this->rightAttribute);

        if ($this->owner->isLeaf() || $this->operation === self::OPERATION_DELETE_WITH_CHILDREN) {
            $this->shiftLeftRightAttribute($rightValue + 1, $leftValue - $rightValue - 1);
        } else {
            $condition = [
                'and',
                ['>=', $this->leftAttribute, $this->owner->getAttribute($this->leftAttribute)],
                ['<=', $this->rightAttribute, $this->owner->getAttribute($this->rightAttribute)]
            ];

            $this->applyTreeAttributeCondition($condition);
            $db = $this->owner->getDb();

            $this->owner->updateAll(
                [
                    $this->leftAttribute => new Expression($db->quoteColumnName($this->leftAttribute) . sprintf('%+d', -1)),
                    $this->rightAttribute => new Expression($db->quoteColumnName($this->rightAttribute) . sprintf('%+d', -1)),
                    $this->depthAttribute => new Expression($db->quoteColumnName($this->depthAttribute) . sprintf('%+d', -1)),
                ],
                $condition
            );

            $this->shiftLeftRightAttribute($rightValue + 1, -2);
        }

        $this->operation = null;
        $this->node = null;
    }

    /**
     * @param integer $value
     * @param integer $delta
     */
    protected function shiftLeftRightAttribute($value, $delta)
    {
        $db = $this->owner->getDb();

        foreach ([$this->leftAttribute, $this->rightAttribute] as $attribute) {
            $condition = ['>=', $attribute, $value];
            $this->applyTreeAttributeCondition($condition);

            $this->owner->updateAll(
                [$attribute => new Expression($db->quoteColumnName($attribute) . sprintf('%+d', $delta))],
                $condition
            );
        }
    }

    /**
     * @param array $condition
     */
    protected function applyTreeAttributeCondition(&$condition)
    {
        if ($this->treeAttribute !== false) {
            $condition = [
                'and',
                $condition,
                [$this->treeAttribute => $this->owner->getAttribute($this->treeAttribute)]
            ];
        }

    }
}
