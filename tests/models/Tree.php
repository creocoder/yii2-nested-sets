<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets-behavior
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace tests\models;

use creocoder\nestedsets\NestedSetsBehavior;

/**
 * Tree
 *
 * @property integer $id
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property string $name
 */
class Tree extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tree';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            NestedSetsBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new TreeQuery(get_called_class());
    }
}
