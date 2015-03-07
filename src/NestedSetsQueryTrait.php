<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace creocoder\nestedsets;

use yii\db\Expression;

/**
 * NestedSetsQueryTrait
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
trait NestedSetsQueryTrait
{

    /**
     * Gets the root nodes.
     * @return \yii\db\ActiveQuery the owner
     */
    public function roots()
    {
        $model = new $this->modelClass();

        $this->andWhere([$model->leftAttribute => 1])
            ->addOrderBy([$model->primaryKey()[0] => SORT_ASC]);

        return $this;
    }

    /**
     * Gets the leaf nodes.
     * @return \yii\db\ActiveQuery the owner
     */
    public function leaves()
    {
        $model = new $this->modelClass();
        $db = $model->getDb();

        $columns = [$model->leftAttribute => SORT_ASC];

        if ($model->treeAttribute !== false) {
            $columns = [$model->treeAttribute => SORT_ASC] + $columns;
        }

        $this->andWhere([$model->rightAttribute => new Expression($db->quoteColumnName($model->leftAttribute) . '+ 1')])
            ->addOrderBy($columns);

        return $this;
    }

}