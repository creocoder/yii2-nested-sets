<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\models\Category $model
 */

$this->title = Yii::t('app', 'Create {modelClass}', [
  'modelClass' => 'Category',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
