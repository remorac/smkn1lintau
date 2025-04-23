<?php

use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\widgets\ActiveForm $form */

$days = ['' => 'tanggal'];
for ($i = 1; $i <= 31; $i++) {
    $days[$i] = $i;
}

$months = [
    '' => 'bulan',
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];
?>

<div class="row">
<div class="col-md-6">
<div class="card">
<div class="card-body">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')->textInput(['maxlength' => true])->hint($model->isNewRecord ? '' : 'boleh dikosongkan jika tidak ingin mengganti password.') ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <label class="control-label"><?= $model->getAttributeLabel('birthdate') ?></label>
    <div class="row">
        <div class="col-3">
            <?= $form->field($model, 'birthdate_day')->dropDownList($days)->label(false) ?>
        </div>
        <div class="col-6 ps-0">
            <?= $form->field($model, 'birthdate_month')->dropDownList($months)->label(false) ?>
        </div>
        <div class="col-3 ps-0">
            <?= $form->field($model, 'birthdate_year')->textInput(['maxlength' => true, 'placeholder' => 'tahun'])->label(false) ?>
        </div>
    </div>

    <?= $form->field($model, 'sex')->radioList([
        'Laki-laki' => 'Laki-laki',
        'Perempuan' => 'Perempuan',
    ]) ?>

    <?php if (Yii::$app->user->identity->position == 'Administrator') { ?>

        <?= $form->field($model, 'position')->radioList([
            'Guru' => 'Guru',
            'Tata Usaha' => 'Tata Usaha',
            'Kepala Sekolah' => 'Kepala Sekolah',
            'Administrator' => 'Administrator',
        ]) ?>

        <hr>
        
        <label class="control-label mb-3">Pengaturan</label>
        <?= Yii::$app->user->id == $model->id ? '' : $form->field($model, 'status')->checkbox([
            'label' => 'Status Akun Aktif',
            'uncheck' => User::STATUS_INACTIVE,
            'value' => User::STATUS_ACTIVE,
        ])->hint('hanya user dengan status aktif yang dapat mengakses sistem') ?>

        <?= $form->field($model, 'is_excepted')->checkbox([
            'label' => 'Izinkan Absensi via Website',
        ])->hint('jika user ini mengakses via android dan terkendala dalam menggunakan aplikasi, aktifkan opsi ini untuk mengizinkan absensi via website') ?>

        <?php } ?>

    </div>

    <div class="card-footer py-3">
        <?= Html::submitButton('<i class="bi bi-save me-1"></i> Save', ['class' => 'btn btn-success float-end']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
</div>
</div>
