<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h2><?= Html::encode($this->title) ?></h2>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'pager' => ['class' => \yii\bootstrap5\LinkPager::class],
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-condensed table-hover border'],
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
            ],
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, User $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
                'contentOptions' => ['class' => 'fit nowrap'],
                'buttons' => [
                    'view' => function ($url) {
                        return Html::a('<i class="bi bi-eye-fill"></i>', $url, ['data-pjax' => 0, 'class' => 'btn btn-outline-info btn-xs']);
                    },
                    'update' => function ($url) {
                        return Html::a('<i class="bi bi-pencil"></i>', $url, ['data-pjax' => 0, 'class' => 'btn btn-outline-warning btn-xs']);
                    },
                    'delete' => function ($url) {
                        return Html::a('<i class="bi bi-trash"></i>', $url, [
                            'class'        => 'btn btn-outline-danger btn-xs',
                            'data-method'  => 'post',
                            'data-confirm' => 'Are you sure you want to delete this item?',
                            'data-pjax'    => 0,
                        ]);
                    },
                ],
            ],
            [
                'attribute' => 'id',
                'contentOptions' => ['class' => 'fit nowrap'],
            ],
            'name',
            [
                'attribute' => 'username',
                'contentOptions' => ['class' => 'fit nowrap'],
            ],[
                'attribute' => 'email',
                'contentOptions' => ['class' => 'fit nowrap'],
            ],
            [
                'attribute' => 'birthdate',
                'contentOptions' => ['class' => 'fit nowrap'],
            ],
            [
                'attribute' => 'sex',
                'contentOptions' => ['class' => 'fit nowrap'],
            ],
            [
                'attribute' => 'position',
                'contentOptions' => ['class' => 'fit nowrap'],
            ],
            [
                'attribute' => 'status',
                'value' => function($model) { return $model->statusLabel; },
                'format' => 'html',
                'filter' => User::statusList(false),
            ],
            [
                'attribute' => 'is_excepted',
                'value' => function($model) { return $model->isExceptedLabel; },
                'format' => 'html',
                'filter' => User::isExceptedList(false),
            ],
            //'created_at',
            //'updated_at',
        ],
    ]); ?>

</div>
