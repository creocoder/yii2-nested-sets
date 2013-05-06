<?php
/**
 * @link https://github.com/creocoder/nested-set-behavior-2
 * @copyright Copyright (c) 2013 Alexander Kochetov
 * @license http://www.yiiframework.com/license/
 */

use \yii\base\Behavior;
use \yii\base\Event;
use \yii\db\ActiveRecord;
use \yii\db\ActiveQuery;
use \yii\db\Expression;
use \yii\db\Exception;

/**
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class NestedSet extends Behavior
{
	/**
	 * @var ActiveRecord the owner of this behavior.
	 */
	public $owner;
	public $hasManyRoots = false;
	public $rootAttribute = 'root';
	public $leftAttribute = 'lft';
	public $rightAttribute = 'rgt';
	public $levelAttribute = 'level';
	private $_rootAttributeQuoted;
	private $_leftAttributeQuoted;
	private $_rightAttributeQuoted;
	private $_levelAttributeQuoted;
	private $_ignoreEvent = false;
	private $_deleted = false;
	private $_id;
	private static $_cached;
	private static $_c = 0;


	/**
	 * Declares event handlers for the [[owner]]'s events.
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		return array(
			ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
			ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
			ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
		);
	}

	/**
	 * Initializes the object.
	 */
	public function init()
	{
		parent::init();
		self::$_cached[get_class($this->owner)][$this->_id = self::$_c++] = $this->owner;
	}

	/**
	 * Returns [[rootAttribute]] quoted.
	 * @return string.
	 */
	public function getRootAttributeQuoted()
	{
		if ($this->_rootAttributeQuoted === null) {
			$this->_rootAttributeQuoted = $this->owner->db->quoteColumnName($this->rootAttribute);
		}
		return $this->_rootAttributeQuoted;
	}

	/**
	 * Returns [[leftAttribute]] quoted.
	 * @return string.
	 */
	public function getLeftAttributeQuoted()
	{
		if ($this->_leftAttributeQuoted === null) {
			$this->_leftAttributeQuoted = $this->owner->db->quoteColumnName($this->leftAttribute);
		}
		return $this->_leftAttributeQuoted;
	}

	/**
	 * Returns [[rightAttribute]] quoted.
	 * @return string.
	 */
	public function getRightAttributeQuoted()
	{
		if ($this->_rightAttributeQuoted === null) {
			$this->_rightAttributeQuoted = $this->owner->db->quoteColumnName($this->rightAttribute);
		}
		return $this->_rightAttributeQuoted;
	}

	/**
	 * Returns [[levelAttribute]] quoted.
	 * @return string.
	 */
	public function getLevelAttributeQuoted()
	{
		if ($this->_levelAttributeQuoted === null) {
			$this->_levelAttributeQuoted = $this->owner->db->quoteColumnName($this->levelAttribute);
		}
		return $this->_levelAttributeQuoted;
	}

	/**
	 * Named scope. Gets descendants for node.
	 * @param ActiveQuery $query.
	 * @param int $depth the depth.
	 * @return ActiveRecord the owner.
	 */
	public function descendants($query, $depth = null)
	{
		$query->andWhere($this->getLeftAttributeQuoted() . '>' . $this->owner->{$this->leftAttribute});
		$query->andWhere($this->getRightAttributeQuoted() . '<' . $this->owner->{$this->rightAttribute});
		$query->addOrderBy($this->getLeftAttributeQuoted());
		if ($depth !== null) {
			$query->andWhere($this->getLevelAttributeQuoted() . '<=' .
				($this->owner->{$this->levelAttribute} + $depth));
		}
		if ($this->hasManyRoots) {
			$query->andWhere($this->getRootAttributeQuoted() . '=:' . $this->rootAttribute, array(
				':' . $this->rootAttribute => $this->owner->{$this->rootAttribute},
			));
		}
	}

	/**
	 * Named scope. Gets children for node (direct descendants only).
	 * @param ActiveQuery $query.
	 * @return ActiveRecord the owner.
	 */
	public function children($query)
	{
		return $this->descendants($query, 1);
	}

	/**
	 * Named scope. Gets ancestors for node.
	 * @param ActiveQuery $query.
	 * @param int $depth the depth.
	 * @return ActiveRecord the owner.
	 */
	public function ancestors($query, $depth = null)
	{
		$query->andWhere($this->getLeftAttributeQuoted() . '<' . $this->owner->{$this->leftAttribute});
		$query->andWhere($this->getRightAttributeQuoted() . '>' . $this->owner->{$this->rightAttribute});
		$query->addOrderBy($this->getLeftAttributeQuoted());
		if ($depth !== null) {
			$query->andWhere($this->getLevelAttributeQuoted() . '>=' .
				($this->owner->{$this->levelAttribute} - $depth));
		}
		if ($this->hasManyRoots) {
			$query->andWhere($this->getRootAttributeQuoted() . '=:' . $this->rootAttribute, array(
				':' . $this->rootAttribute => $this->owner->{$this->rootAttribute},
			));
		}
	}

	/**
	 * Named scope. Gets root node(s).
	 * @param ActiveQuery $query.
	 * @return ActiveRecord the owner.
	 */
	public function roots($query)
	{
		$query->andWhere($this->getLeftAttributeQuoted() . '=1');
	}

	/**
	 * Named scope. Gets parent of node.
	 * @param ActiveQuery $query.
	 * @return ActiveRecord the owner.
	 */
	public function parent($query)
	{
		$query->andWhere($this->getLeftAttributeQuoted() . '<' . $this->owner->{$this->leftAttribute});
		$query->andWhere($this->getRightAttributeQuoted() . '>' . $this->owner->{$this->rightAttribute});
		$query->addOrderBy($this->getRightAttributeQuoted());
		if ($this->hasManyRoots) {
			$query->andWhere($this->getRootAttributeQuoted() . '=:' . $this->rootAttribute, array(
				':' . $this->rootAttribute => $this->owner->{$this->rootAttribute},
			));
		}
	}

	/**
	 * Named scope. Gets previous sibling of node.
	 * @param ActiveQuery $query.
	 * @return ActiveRecord the owner.
	 */
	public function prev($query)
	{
		$query->andWhere($this->getRightAttributeQuoted() . '=' . ($this->owner->{$this->leftAttribute} - 1));
		if ($this->hasManyRoots) {
			$query->andWhere($this->getRootAttributeQuoted() . '=:' . $this->rootAttribute, array(
				':' . $this->rootAttribute => $this->owner->{$this->rootAttribute},
			));
		}
	}

	/**
	 * Named scope. Gets next sibling of node.
	 * @param ActiveQuery $query.
	 * @return ActiveRecord the owner.
	 */
	public function next($query)
	{
		$query->andWhere($this->getLeftAttributeQuoted() . '=' . ($this->owner->{$this->rightAttribute} + 1));
		if ($this->hasManyRoots) {
			$query->andWhere($this->getRootAttributeQuoted() . '=:' . $this->rootAttribute, array(
				':' . $this->rootAttribute => $this->owner->{$this->rootAttribute},
			));
		}
	}

	/**
	 * Create root node if multiple-root tree mode. Update node if it's not new.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the saving succeeds.
	 */
	public function save($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->owner->validate($attributes)) {
			return false;
		}
		if ($this->owner->getIsNewRecord()) {
			return $this->makeRoot($attributes);
		}
		$this->_ignoreEvent = true;
		$result = $this->owner->update(false, $attributes);
		$this->_ignoreEvent = false;
		return $result;
	}

	/**
	 * Create root node if multiple-root tree mode. Update node if it's not new.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the saving succeeds.
	 */
	public function saveNode($runValidation = true, $attributes = null)
	{
		return $this->save($runValidation, $attributes);
	}

	/**
	 * Deletes node and it's descendants.
	 * @throws Exception.
	 * @throws \Exception.
	 * @return boolean whether the deletion is successful.
	 */
	public function delete()
	{
		if ($this->owner->getIsNewRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node cannot be deleted because it is new.'));
		}
		if ($this->getIsDeletedRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node cannot be deleted because it is already deleted.'));
		}
		$db = $this->owner->getDb();
		if ($db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
		}
		try {
			if ($this->owner->isLeaf()) {
				$this->_ignoreEvent = true;
				$result = $this->owner->delete();
				$this->_ignoreEvent = false;
			} else {
				$condition = $this->getLeftAttributeQuoted() . '>=' . $this->owner->{$this->leftAttribute} . ' AND ' .
					$this->getRightAttributeQuoted() . '<=' . $this->owner->{$this->rightAttribute};
				$params = array();
				if ($this->hasManyRoots) {
					$condition .= ' AND ' . $this->getRootAttributeQuoted() . '=:' . $this->rootAttribute;
					$params[':' . $this->rootAttribute] = $this->owner->{$this->rootAttribute};
				}
				$result = $this->owner->deleteAll($condition, $params) > 0;
			}
			if (!$result) {
				if (isset($transaction)) {
					$transaction->rollback();
				}
				return false;
			}
			$this->shiftLeftRight(
				$this->owner->{$this->rightAttribute} + 1,
				$this->owner->{$this->leftAttribute} - $this->owner->{$this->rightAttribute} - 1
			);
			if (isset($transaction)) {
				$transaction->commit();
			}
			$this->correctCachedOnDelete();
		} catch (\Exception $e) {
			if (isset($transaction)) {
				$transaction->rollback();
			}
			throw $e;
		}
		return true;
	}

	/**
	 * Deletes node and it's descendants.
	 * @return boolean whether the deletion is successful.
	 */
	public function deleteNode()
	{
		return $this->delete();
	}

	/**
	 * Prepends node to target as first child.
	 * @param ActiveRecord $target the target.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the prepending succeeds.
	 */
	public function prependTo($target, $runValidation = true, $attributes = null)
	{
		return $this->addNode($target, $target->{$this->leftAttribute} + 1, 1, $runValidation, $attributes);
	}

	/**
	 * Prepends target to node as first child.
	 * @param ActiveRecord $target the target.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the prepending succeeds.
	 */
	public function prepend($target, $runValidation = true, $attributes = null)
	{
		return $target->prependTo($this->owner, $runValidation, $attributes);
	}

	/**
	 * Appends node to target as last child.
	 * @param ActiveRecord $target the target.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the appending succeeds.
	 */
	public function appendTo($target, $runValidation = true, $attributes = null)
	{
		return $this->addNode($target, $target->{$this->rightAttribute}, 1, $runValidation, $attributes);
	}

	/**
	 * Appends target to node as last child.
	 * @param ActiveRecord $target the target.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the appending succeeds.
	 */
	public function append($target, $runValidation = true, $attributes = null)
	{
		return $target->appendTo($this->owner, $runValidation, $attributes);
	}

	/**
	 * Inserts node as previous sibling of target.
	 * @param ActiveRecord $target the target.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the inserting succeeds.
	 */
	public function insertBefore($target, $runValidation = true, $attributes = null)
	{
		return $this->addNode($target, $target->{$this->leftAttribute}, 0, $runValidation, $attributes);
	}

	/**
	 * Inserts node as next sibling of target.
	 * @param ActiveRecord $target the target.
	 * @param boolean $runValidation whether to perform validation.
	 * @param array $attributes list of attributes.
	 * @return boolean whether the inserting succeeds.
	 */
	public function insertAfter($target, $runValidation = true, $attributes = null)
	{
		return $this->addNode($target, $target->{$this->rightAttribute} + 1, 0, $runValidation, $attributes);
	}

	/**
	 * Move node as previous sibling of target.
	 * @param ActiveRecord $target the target.
	 * @return boolean whether the moving succeeds.
	 */
	public function moveBefore($target)
	{
		return $this->moveNode($target, $target->{$this->leftAttribute}, 0);
	}

	/**
	 * Move node as next sibling of target.
	 * @param ActiveRecord $target the target.
	 * @return boolean whether the moving succeeds.
	 */
	public function moveAfter($target)
	{
		return $this->moveNode($target, $target->{$this->rightAttribute} + 1, 0);
	}

	/**
	 * Move node as first child of target.
	 * @param ActiveRecord $target the target.
	 * @return boolean whether the moving succeeds.
	 */
	public function moveAsFirst($target)
	{
		return $this->moveNode($target, $target->{$this->leftAttribute} + 1, 1);
	}

	/**
	 * Move node as last child of target.
	 * @param ActiveRecord $target the target.
	 * @return boolean whether the moving succeeds.
	 */
	public function moveAsLast($target)
	{
		return $this->moveNode($target, $target->{$this->rightAttribute}, 1);
	}

	/**
	 * Move node as new root.
	 * @throws Exception.
	 * @throws \Exception.
	 * @return boolean whether the moving succeeds.
	 */
	public function moveAsRoot()
	{
		if (!$this->hasManyRoots) {
			throw new Exception(\Yii::t('nestedset', 'Many roots mode is off.'));
		}
		if ($this->owner->getIsNewRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node should not be new record.'));
		}
		if ($this->getIsDeletedRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node should not be deleted.'));
		}
		if ($this->owner->isRoot()) {
			throw new Exception(\Yii::t('nestedset', 'The node already is root node.'));
		}
		$db = $this->owner->getDb();
		if ($db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
		}
		try {
			$left = $this->owner->{$this->leftAttribute};
			$right = $this->owner->{$this->rightAttribute};
			$levelDelta = 1 - $this->owner->{$this->levelAttribute};
			$delta = 1 - $left;
			$this->owner->updateAll(array(
					$this->leftAttribute => new Expression($this->getLeftAttributeQuoted() . sprintf('%+d', $delta)),
					$this->rightAttribute => new Expression($this->getRightAttributeQuoted() . sprintf('%+d', $delta)),
					$this->levelAttribute => new Expression($this->getLevelAttributeQuoted() . sprintf('%+d', $levelDelta)),
					$this->rootAttribute => $this->owner->getPrimaryKey(),
				), $this->getLeftAttributeQuoted() . '>=' . $left . ' AND ' . $this->getRightAttributeQuoted() . '<=' .
				$right . ' AND ' . $this->getRootAttributeQuoted() . '=:' . $this->rootAttribute,
				array(':' . $this->rootAttribute => $this->owner->{$this->rootAttribute})
			);
			$this->shiftLeftRight($right + 1, $left - $right - 1);
			if (isset($transaction)) {
				$transaction->commit();
			}
			$this->correctCachedOnMoveBetweenTrees(1, $levelDelta, $this->owner->getPrimaryKey());
		} catch (\Exception $e) {
			if (isset($transaction)) {
				$transaction->rollback();
			}
			throw $e;
		}
		return true;
	}

	/**
	 * Determines if node is descendant of subject node.
	 * @param ActiveRecord $subj the subject node.
	 * @return boolean whether the node is descendant of subject node.
	 */
	public function isDescendantOf($subj)
	{
		$result = ($this->owner->{$this->leftAttribute} > $subj->{$this->leftAttribute})
			&& ($this->owner->{$this->rightAttribute} < $subj->{$this->rightAttribute});
		if ($this->hasManyRoots) {
			$result = $result && ($this->owner->{$this->rootAttribute} === $subj->{$this->rootAttribute});
		}
		return $result;
	}

	/**
	 * Determines if node is leaf.
	 * @return boolean whether the node is leaf.
	 */
	public function isLeaf()
	{
		return $this->owner->{$this->rightAttribute} - $this->owner->{$this->leftAttribute} === 1;
	}

	/**
	 * Determines if node is root.
	 * @return boolean whether the node is root.
	 */
	public function isRoot()
	{
		return $this->owner->{$this->leftAttribute} == 1;
	}

	/**
	 * Returns if the current node is deleted.
	 * @return boolean whether the node is deleted.
	 */
	public function getIsDeletedRecord()
	{
		return $this->_deleted;
	}

	/**
	 * Sets if the current node is deleted.
	 * @param boolean $value whether the node is deleted.
	 */
	public function setIsDeletedRecord($value)
	{
		$this->_deleted = $value;
	}

	/**
	 * Handle 'afterFind' event of the owner.
	 * @param Event $event event parameter.
	 */
	public function afterFind($event)
	{
		self::$_cached[get_class($this->owner)][$this->_id = self::$_c++] = $this->owner;
	}

	/**
	 * Handle 'beforeInsert' event of the owner.
	 * @param Event $event event parameter.
	 * @throws Exception.
	 * @return boolean.
	 */
	public function beforeInsert($event)
	{
		if ($this->_ignoreEvent) {
			return true;
		} else {
			throw new Exception(\Yii::t('nestedset', 'You should not use ActiveRecord::insert() or ActiveRecord::save() methods when NestedSet behavior attached.'));
		}
	}

	/**
	 * Handle 'beforeUpdate' event of the owner.
	 * @param Event $event event parameter.
	 * @throws Exception.
	 * @return boolean.
	 */
	public function beforeUpdate($event)
	{
		if ($this->_ignoreEvent) {
			return true;
		} else {
			throw new Exception(\Yii::t('nestedset', 'You should not use ActiveRecord::update() or ActiveRecord::save() methods when NestedSet behavior attached.'));
		}
	}

	/**
	 * Handle 'beforeDelete' event of the owner.
	 * @param Event $event event parameter.
	 * @throws Exception.
	 * @return boolean.
	 */
	public function beforeDelete($event)
	{
		if ($this->_ignoreEvent) {
			return true;
		} else {
			throw new Exception(\Yii::t('nestedset', 'You should not use ActiveRecord::delete() method when NestedSet behavior attached.'));
		}
	}

	/**
	 * @param int $key.
	 * @param int $delta.
	 */
	private function shiftLeftRight($key, $delta)
	{
		$db = $this->owner->getDb();
		foreach (array(
			         $this->getLeftAttributeQuoted() => $this->leftAttribute,
			         $this->getRightAttributeQuoted() => $this->rightAttribute,
		         ) as $attributeQuoted => $attribute) {
			$condition = $attributeQuoted . '>=' . $key;
			$params = array();
			if ($this->hasManyRoots) {
				$condition .= ' AND ' . $this->getRootAttributeQuoted() . '=:' . $this->rootAttribute;
				$params[':' . $this->rootAttribute] = $this->owner->{$this->rootAttribute};
			}
			$this->owner->updateAll(array(
				$attribute => new Expression($attributeQuoted . sprintf('%+d', $delta)),
			), $condition, $params);
		}
	}

	/**
	 * @param ActiveRecord $target.
	 * @param int $key.
	 * @param int $levelUp.
	 * @param boolean $runValidation.
	 * @param array $attributes.
	 * @throws Exception.
	 * @throws \Exception.
	 * @return boolean.
	 */
	private function addNode($target, $key, $levelUp, $runValidation, $attributes)
	{
		if (!$this->owner->getIsNewRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node cannot be inserted because it is not new.'));
		}
		if ($this->getIsDeletedRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node cannot be inserted because it is deleted.'));
		}
		if ($target->getIsDeletedRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node cannot be inserted because target node is deleted.'));
		}
		if ($this->owner->equals($target)) {
			throw new Exception(\Yii::t('nestedset', 'The target node should not be self.'));
		}
		if (!$levelUp && $target->isRoot()) {
			throw new Exception(\Yii::t('nestedset', 'The target node should not be root.'));
		}
		if ($runValidation && !$this->owner->validate()) {
			return false;
		}
		if ($this->hasManyRoots) {
			$this->owner->{$this->rootAttribute} = $target->{$this->rootAttribute};
		}
		$db = $this->owner->getDb();
		if ($db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
		}
		try {
			$this->shiftLeftRight($key, 2);
			$this->owner->{$this->leftAttribute} = $key;
			$this->owner->{$this->rightAttribute} = $key + 1;
			$this->owner->{$this->levelAttribute} = $target->{$this->levelAttribute} + $levelUp;
			$this->_ignoreEvent = true;
			$result = $this->owner->insert(false, $attributes);
			$this->_ignoreEvent = false;
			if (!$result) {
				if (isset($transaction)) {
					$transaction->rollback();
				}
				return false;
			}
			if (isset($transaction)) {
				$transaction->commit();
			}
			$this->correctCachedOnAddNode($key);
		} catch (\Exception $e) {
			if (isset($transaction)) {
				$transaction->rollback();
			}
			throw $e;
		}
		return true;
	}

	/**
	 * @param array $attributes.
	 * @throws Exception.
	 * @throws \Exception.
	 * @return boolean.
	 */
	private function makeRoot($attributes)
	{
		$this->owner->{$this->leftAttribute} = 1;
		$this->owner->{$this->rightAttribute} = 2;
		$this->owner->{$this->levelAttribute} = 1;
		if ($this->hasManyRoots) {
			$db = $this->owner->getDb();
			if ($db->getTransaction() === null) {
				$transaction = $db->beginTransaction();
			}
			try {
				$this->_ignoreEvent = true;
				$result = $this->owner->insert(false, $attributes);
				$this->_ignoreEvent = false;
				if (!$result) {
					if (isset($transaction)) {
						$transaction->rollback();
					}
					return false;
				}
				$pk = $this->owner->{$this->rootAttribute} = $this->owner->getPrimaryKey();
				$this->owner->updateAll(
					array($this->rootAttribute => $pk),
					array($this->owner->primaryKey()[0] => $pk)
				);
				if (isset($transaction)) {
					$transaction->commit();
				}
			} catch (\Exception $e) {
				if (isset($transaction)) {
					$transaction->rollback();
				}
				throw $e;
			}
		} else {
			if ($this->owner->roots()->exists()) {
				throw new Exception(\Yii::t('nestedset', 'Cannot create more than one root in single root mode.'));
			}
			$this->_ignoreEvent = true;
			$result = $this->owner->insert(false, $attributes);
			$this->_ignoreEvent = false;
			if (!$result) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param ActiveRecord $target.
	 * @param int $key.
	 * @param int $levelUp.
	 * @throws Exception.
	 * @throws \Exception.
	 * @return boolean.
	 */
	private function moveNode($target, $key, $levelUp)
	{
		if ($this->owner->getIsNewRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node should not be new record.'));
		}
		if ($this->getIsDeletedRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The node should not be deleted.'));
		}
		if ($target->getIsDeletedRecord()) {
			throw new Exception(\Yii::t('nestedset', 'The target node should not be deleted.'));
		}
		if ($this->owner->equals($target)) {
			throw new Exception(\Yii::t('nestedset', 'The target node should not be self.'));
		}
		if ($target->isDescendantOf($this->owner)) {
			throw new Exception(\Yii::t('nestedset', 'The target node should not be descendant.'));
		}
		if (!$levelUp && $target->isRoot()) {
			throw new Exception(\Yii::t('nestedset', 'The target node should not be root.'));
		}
		$db = $this->owner->getDb();
		if ($db->getTransaction() === null) {
			$transaction = $db->beginTransaction();
		}
		try {
			$left = $this->owner->{$this->leftAttribute};
			$right = $this->owner->{$this->rightAttribute};
			$levelDelta = $target->{$this->levelAttribute} - $this->owner->{$this->levelAttribute} + $levelUp;
			if ($this->hasManyRoots && $this->owner->{$this->rootAttribute} !== $target->{$this->rootAttribute}) {
				foreach (array(
					         $this->getLeftAttributeQuoted() => $this->leftAttribute,
					         $this->getRightAttributeQuoted() => $this->rightAttribute,
				         ) as $attributeQuoted => $attribute) {
					$this->owner->updateAll(array(
							$attribute => new Expression($attributeQuoted . sprintf('%+d', $right - $left + 1)),
						), $attributeQuoted . '>=' . $key . ' AND ' . $this->getRootAttributeQuoted() . '=:' .
						$this->rootAttribute, array(':' . $this->rootAttribute => $target->{$this->rootAttribute})
					);
				}
				$delta = $key - $left;
				$this->owner->updateAll(array(
						$this->leftAttribute => new Expression($this->getLeftAttributeQuoted() . sprintf('%+d', $delta)),
						$this->rightAttribute => new Expression($this->getRightAttributeQuoted() . sprintf('%+d', $delta)),
						$this->levelAttribute => new Expression($this->getLevelAttributeQuoted() .
							sprintf('%+d', $levelDelta)),
						$this->rootAttribute => $target->{$this->rootAttribute},
					), $this->getLeftAttributeQuoted() . '>=' . $left . ' AND ' . $this->getRightAttributeQuoted() . '<=' .
					$right . ' AND ' . $this->getRootAttributeQuoted() . '=:' . $this->rootAttribute,
					array(':' . $this->rootAttribute => $this->owner->{$this->rootAttribute})
				);
				$this->shiftLeftRight($right + 1, $left - $right - 1);
				if (isset($transaction)) {
					$transaction->commit();
				}
				$this->correctCachedOnMoveBetweenTrees($key, $levelDelta, $target->{$this->rootAttribute});
			} else {
				$delta = $right - $left + 1;
				$this->shiftLeftRight($key, $delta);
				if ($left >= $key) {
					$left += $delta;
					$right += $delta;
				}
				$condition = $this->getLeftAttributeQuoted() . '>=' . $left . ' AND ' .
					$this->getRightAttributeQuoted() . '<=' . $right;
				$params = array();
				if ($this->hasManyRoots) {
					$condition .= ' AND ' . $this->getRootAttributeQuoted() . '=:' . $this->rootAttribute;
					$params[':' . $this->rootAttribute] = $this->owner->{$this->rootAttribute};
				}
				$this->owner->updateAll(array(
					$this->levelAttribute => new Expression($this->getLevelAttributeQuoted() .
						sprintf('%+d', $levelDelta)),
				), $condition, $params);
				foreach (array(
					         $this->getLeftAttributeQuoted() => $this->leftAttribute,
					         $this->getRightAttributeQuoted() => $this->rightAttribute,
				         ) as $attributeQuoted => $attribute) {
					$condition = $attributeQuoted . '>=' . $left . ' AND ' . $attributeQuoted . '<=' . $right;
					$params = array();
					if ($this->hasManyRoots) {
						$condition .= ' AND ' . $this->getRootAttributeQuoted() . '=:' . $this->rootAttribute;
						$params[':' . $this->rootAttribute] = $this->owner->{$this->rootAttribute};
					}
					$this->owner->updateAll(array(
						$attribute => new Expression($attributeQuoted . sprintf('%+d', $key - $left)),
					), $condition, $params);
				}
				$this->shiftLeftRight($right + 1, -$delta);
				if (isset($transaction)) {
					$transaction->commit();
				}
				$this->correctCachedOnMoveNode($key, $levelDelta);
			}
		} catch (\Exception $e) {
			if (isset($transaction)) {
				$transaction->rollback();
			}
			throw $e;
		}
		return true;
	}

	/**
	 * Correct cache for [[delete()]] and [[deleteNode()]].
	 */
	private function correctCachedOnDelete()
	{
		$left = $this->owner->{$this->leftAttribute};
		$right = $this->owner->{$this->rightAttribute};
		$key = $right + 1;
		$delta = $left - $right - 1;
		foreach (self::$_cached[get_class($this->owner)] as $node) {
			if ($node->getIsNewRecord() || $node->getIsDeletedRecord()) {
				continue;
			}
			if ($this->hasManyRoots && $this->owner->{$this->rootAttribute} !== $node->{$this->rootAttribute}) {
				continue;
			}
			if ($node->{$this->leftAttribute} >= $left && $node->{$this->rightAttribute} <= $right) {
				$node->setIsDeletedRecord(true);
			} else {
				if ($node->{$this->leftAttribute} >= $key) {
					$node->{$this->leftAttribute} += $delta;
				}
				if ($node->{$this->rightAttribute} >= $key) {
					$node->{$this->rightAttribute} += $delta;
				}
			}
		}
	}

	/**
	 * Correct cache for [[addNode()]].
	 * @param int $key.
	 */
	private function correctCachedOnAddNode($key)
	{
		foreach (self::$_cached[get_class($this->owner)] as $node) {
			if ($node->getIsNewRecord() || $node->getIsDeletedRecord()) {
				continue;
			}
			if ($this->hasManyRoots && $this->owner->{$this->rootAttribute} !== $node->{$this->rootAttribute}) {
				continue;
			}
			if ($this->owner === $node) {
				continue;
			}
			if ($node->{$this->leftAttribute} >= $key) {
				$node->{$this->leftAttribute} += 2;
			}
			if ($node->{$this->rightAttribute} >= $key) {
				$node->{$this->rightAttribute} += 2;
			}
		}
	}

	/**
	 * Correct cache for [[moveNode()]].
	 * @param int $key.
	 * @param int $levelDelta.
	 */
	private function correctCachedOnMoveNode($key, $levelDelta)
	{
		$left = $this->owner->{$this->leftAttribute};
		$right = $this->owner->{$this->rightAttribute};
		$delta = $right - $left + 1;
		if ($left >= $key) {
			$left += $delta;
			$right += $delta;
		}
		$delta2 = $key - $left;
		foreach (self::$_cached[get_class($this->owner)] as $node) {
			if ($node->getIsNewRecord() || $node->getIsDeletedRecord()) {
				continue;
			}
			if ($this->hasManyRoots && $this->owner->{$this->rootAttribute} !== $node->{$this->rootAttribute}) {
				continue;
			}
			if ($node->{$this->leftAttribute} >= $key) {
				$node->{$this->leftAttribute} += $delta;
			}
			if ($node->{$this->rightAttribute} >= $key) {
				$node->{$this->rightAttribute} += $delta;
			}
			if ($node->{$this->leftAttribute} >= $left && $node->{$this->rightAttribute} <= $right) {
				$node->{$this->levelAttribute} += $levelDelta;
			}
			if ($node->{$this->leftAttribute} >= $left && $node->{$this->leftAttribute} <= $right) {
				$node->{$this->leftAttribute} += $delta2;
			}
			if ($node->{$this->rightAttribute} >= $left && $node->{$this->rightAttribute} <= $right) {
				$node->{$this->rightAttribute} += $delta2;
			}
			if ($node->{$this->leftAttribute} >= $right + 1) {
				$node->{$this->leftAttribute} -= $delta;
			}
			if ($node->{$this->rightAttribute} >= $right + 1) {
				$node->{$this->rightAttribute} -= $delta;
			}
		}
	}

	/**
	 * Correct cache for [[moveNode()]].
	 * @param int $key.
	 * @param int $levelDelta.
	 * @param int $root.
	 */
	private function correctCachedOnMoveBetweenTrees($key, $levelDelta, $root)
	{
		$left = $this->owner->{$this->leftAttribute};
		$right = $this->owner->{$this->rightAttribute};
		$delta = $right - $left + 1;
		$delta2 = $key - $left;
		$delta3 = $left - $right - 1;
		foreach (self::$_cached[get_class($this->owner)] as $node) {
			if ($node->getIsNewRecord() || $node->getIsDeletedRecord()) {
				continue;
			}
			if ($node->{$this->rootAttribute} === $root) {
				if ($node->{$this->leftAttribute} >= $key) {
					$node->{$this->leftAttribute} += $delta;
				}
				if ($node->{$this->rightAttribute} >= $key) {
					$node->{$this->rightAttribute} += $delta;
				}
			} elseif ($node->{$this->rootAttribute} === $this->owner->{$this->rootAttribute}) {
				if ($node->{$this->leftAttribute} >= $left && $node->{$this->rightAttribute} <= $right) {
					$node->{$this->leftAttribute} += $delta2;
					$node->{$this->rightAttribute} += $delta2;
					$node->{$this->levelAttribute} += $levelDelta;
					$node->{$this->rootAttribute} = $root;
				} else {
					if ($node->{$this->leftAttribute} >= $right + 1) {
						$node->{$this->leftAttribute} += $delta3;
					}
					if ($node->{$this->rightAttribute} >= $right + 1) {
						$node->{$this->rightAttribute} += $delta3;
					}
				}
			}
		}
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		unset(self::$_cached[get_class($this->owner)][$this->_id]);
	}
}
