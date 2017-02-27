<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets
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
     * @param mixed $tree optional tree id. Default is `null` to query for all root nodes.
     * @return \yii\db\ActiveQuery the owner
     */
    public function roots($tree = null)
    {
        $model = new $this->owner->modelClass();

        $this->owner
            ->andWhere([$model->leftAttribute => 1])
            ->addOrderBy([$model->primaryKey()[0] => SORT_ASC]);

        if ($model->treeAttribute !== false && $tree !== null) {
            $this->owner->andWhere([$model->treeAttribute => $tree]);
        }

        return $this->owner;
    }

    /**
     * Gets the leaf nodes.
     * @return \yii\db\ActiveQuery the owner
     */
    public function leaves()
    {
        $model = new $this->owner->modelClass();
        $db = $model->getDb();

        $columns = [$model->leftAttribute => SORT_ASC];

        if ($model->treeAttribute !== false) {
            $columns = [$model->treeAttribute => SORT_ASC] + $columns;
        }

        $this->owner
            ->andWhere([$model->rightAttribute => new Expression($db->quoteColumnName($model->leftAttribute) . '+ 1')])
            ->addOrderBy($columns);

        return $this->owner;
    }
}
