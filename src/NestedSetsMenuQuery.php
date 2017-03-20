<?php
/**
 * @link https://github.com/creocoder/yii2-nested-sets
 * @copyright Copyright (c) 2015 Alexander Kochetov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace creocoder\nestedsets;

/**
 * NestedSetsMenuQuery
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */

use yii\db\ActiveQuery;

class MenuQuery extends ActiveQuery
{
    use MenuQueryTrait;
}