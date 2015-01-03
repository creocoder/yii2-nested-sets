<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets-behavior
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace creocoder\nestedsets;

use yii\base\Behavior;
use yii\db\Expression;

/**
 * NestedSetsQueryBehavior
 *
 * @property \yii\db\ActiveQuery $owner
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class NestedSetsQueryBehavior extends Behavior
{
    /**
     * Gets the root nodes.
     * @return \yii\db\ActiveQuery the owner
     */
    public function roots()
    {
        $this->owner->andWhere([(new $this->owner->modelClass())->leftAttribute => 1]);

        return $this->owner;
    }

    /**
     * Gets the leaf nodes.
     * @return \yii\db\ActiveQuery the owner
     */
    public function leaf()
    {
        $model = new $this->owner->modelClass();
        $db = $model->getDb();

        $this->owner->andWhere(new Expression(
            $db->quoteColumnName($model->rightAttribute) . ' - ' . $db->quoteColumnName($model->leftAttribute) . ' = 1'
        ))->addOrderBy([$model->rightAttribute => SORT_ASC]);

        return $this->owner;
    }
}
