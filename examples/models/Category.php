<?php

namespace app\models;

use yii\db\ActiveQuery;
use creocoder\behaviors\NestedSet;
use creocoder\behaviors\NestedSetQuery;
/**
 * This is the model class for table "tbl_category".
 *
 * @property string $id
 * @property string $root
 * @property string $lft
 * @property string $rgt
 * @property integer $level
 * @property string $title
 * @property string $description
 */
class Category extends \yii\db\ActiveRecord {
	public $parent;
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return '{{%category}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
				[ [ 'root', 'lft', 'rgt', 'level' ], 'integer' ],
				[ [ 'title' ], 'required' ],
				[ [ 'description' ], 'string' ],
				[ [ 'title' ], 'string', 'max' => 255 ],
				[['parent'], 'safe']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
				'id' => \Yii::t ( 'app', 'ID' ),
				'root' => \Yii::t ( 'app', 'Root' ),
				'lft' => \Yii::t ( 'app', 'Lft' ),
				'rgt' => \Yii::t ( 'app', 'Rgt' ),
				'level' => \Yii::t ( 'app', 'Level' ),
				'title' => \Yii::t ( 'app', 'Title' ),
				'description' => \Yii::t ( 'app', 'Description' )
		];
	}
	/**
	 * Configure extra behaviors
	 *
	 * @see \yii\base\Component::behaviors()
	 */
	public function behaviors() {
		return [
				'nestedSet' => [
						'class' => NestedSet::className (),
						'hasManyRoots' => TRUE
				]
		];
	}

	/**
	 * Override createQuery
	 * @param unknown $config
	 */
	public static function createQuery($config = array())
	{
		$config['modelClass'] = get_called_class();
		return (new CategoryQuery($config))->orderBy('root ASC, lft ASC');
	}
	/**
	 * Return an array of available items, suitable for dropDownList()
	 * @param number $root  ID of root nodes to retrieve
	 * @param string $level  The level of tree to retrieve
	 * @return array $res  An associate array with key - value
	 */
	public static function options($root = 0, $level = NULL){
		$res = [];
		if ($root instanceof self){
			$res[$root->id] = str_repeat('-', $root->level) . ' ' . $root->title;
			if ($level){
				foreach ($root->children()->all() as $childRoot){
					$res += self::options($childRoot, $level - 1);
				}
			} elseif (is_null($level)){
				foreach ($root->children()->all() as $childRoot){
					$res += self::options($childRoot, NULL);
				}
			}
		} elseif (is_scalar($root)){
			if ($root == 0){
				foreach (self::find()->roots()->all() as $rootItem){
					if ($level)
						$res += self::options($rootItem, $level - 1);
					elseif (is_null($level))
					$res += self::options($rootItem, NULL);
				}
			} else {
				$root = self::find($root);
				if ($root) 	$res += self::options($root, $level);
			}
		}
		return $res;
	}
}

/**
 * Extends the ActiveQuery Class
 */
class CategoryQuery extends ActiveQuery {
	public function behaviors() {
		return [
			[
				'class' => NestedSetQuery::className(),
			],
		];
	}
}
