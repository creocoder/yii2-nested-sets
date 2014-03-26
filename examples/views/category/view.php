<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var app\models\Category $model
 */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
//             'id',
//             'root',
//             'lft',
//             'rgt',
//             'level',
            'title',
            'description:ntext',
        ],
    ]) ?>

	<h3>Ancestors</h3>
	<?php \yii\helpers\VarDumper::dump($model->ancestors()->all(), 3, TRUE)?>

	<h3>Descendants</h3>
	<?php \yii\helpers\VarDumper::dump($model->descendants()->all(), 3, TRUE)?>

	<h3>Previous</h3>
	<?php \yii\helpers\VarDumper::dump($model->prev()->all(), 3, TRUE)?>

	<h3>Next</h3>
	<?php \yii\helpers\VarDumper::dump($model->next()->all(), 3, TRUE)?>

</div>
