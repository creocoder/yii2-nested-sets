<?php

use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\CategorySearch $searchModel
 */

$this->title = Yii::t('app', 'Categories');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create {modelClass}', [
  'modelClass' => 'Category',
]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

//             'id',
//             'root',
//             'lft',
//             'rgt',
//             'level',
            ['attribute' => 'title', 'value' => function($data){ return str_repeat('--', $data->level) . ' ' . $data->title; }],
            'description:ntext',

            [
				'class' => 'yii\grid\ActionColumn',
				'template' => '{view} {up} {down} {update} {delete}',
				'buttons' => [
					'up' => function ($url, $model) {
						return Html::a('<span class="glyphicon glyphicon-chevron-up"></span>', $url, [
							'title' => Yii::t('yii', 'Move Up'),
							'data-pjax' => '0',
						]); },
					'down' => function ($url, $model) {
						return Html::a('<span class="glyphicon glyphicon-chevron-down"></span>', $url, [
							'title' => Yii::t('yii', 'Move Down'),
							'data-pjax' => '0',
						]); },
				],
        	],
		],
    ]); ?>

</div>
