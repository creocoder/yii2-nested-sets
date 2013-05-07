<?php
/**
 * @link https://github.com/creocoder/nested-set-behavior-2
 * @copyright Copyright (c) 2013 Alexander Kochetov
 * @license http://www.yiiframework.com/license/
 */

namespace creocoder\yii\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

/**
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class NestedSetQuery extends Behavior
{
	/**
	 * @var ActiveQuery the owner of this behavior.
	 */
	public $owner;
	public $hasManyRoots = false;
	public $rootAttribute = 'root';
	public $leftAttribute = 'lft';
	public $rightAttribute = 'rgt';
	public $levelAttribute = 'level';

	/**
	 * Gets root node(s).
	 * @return ActiveRecord the owner.
	 */
	public function roots()
	{
		/** @var $modelClass ActiveRecord */
		$modelClass=$this->owner->modelClass;
		$this->owner->andWhere($modelClass::getDb()->quoteColumnName($this->leftAttribute) . '=1');

		return $this->owner;
	}
}
