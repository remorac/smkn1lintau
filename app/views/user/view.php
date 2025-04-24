<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->name ?? $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

    <h2><?= Html::encode($this->title) ?></h2>

    <p>
        <?= Html::a('<i class="bi bi-pencil-square"></i> Update', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= Yii::$app->user->identity->position == 'Administrator' && $model->id != Yii::$app->user->id ? Html::a('<i class="bi bi-trash"></i> Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) : '' ?>
        <?= Html::a('<i class="bi bi-person-raised-hand"></i> Ambil Absen', ['/presence/index', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'options' => ['class' => 'table detail-view'],
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'username',
            'email',
            'birthdate',
            'sex',
            'position',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

    <?= Yii::$app->user->identity->position == 'Administrator' ? DetailView::widget([
        'options' => ['class' => 'table detail-view'],
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'status',
                'value' => $model->statusLabel,
                'format' => 'html',
            ],
            [
                'attribute' => 'is_excepted',
                'value' => $model->isExceptedLabel,
                'format' => 'html',
            ],
        ],
    ]) : '' ?>

</div>
