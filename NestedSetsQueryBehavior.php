<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets-behavior
 * @copyright Copyright (c) 2014 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace creocoder\nestedsets;

use yii\base\Behavior;

/**
 * NestedSetsQueryBehavior
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class NestedSetsQueryBehavior extends Behavior
{
    /**
     * @var \yii\db\ActiveQuery the owner of this behavior.
     */
    public $owner;

    /**
     * Gets root node(s).
     * @return \yii\db\ActiveRecord the owner.
     */
    public function roots()
    {
        $this->owner->andWhere([(new $this->owner->modelClass)->leftAttribute => 1]);

        return $this->owner;
    }
}
