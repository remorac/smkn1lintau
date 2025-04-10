<?php

use app\models\Presence;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = $user->name;
$this->params['breadcrumbs'][] = ['label' => 'Report', 'url' => ['all']];
$this->params['breadcrumbs'][] = $this->title;

$years = [];
for ($i = 2025; $i <= date('Y'); $i++) {
    $years[$i] = $i;
}
$users = User::find()->orderBy('name')->asArray()->all();

$count    = [];
$total    = 0;
$sequence = 0;

$counter = 0;
$sum_late = 0; 
?>

<h2><?= $this->title ?></h2>
<div class="box box-primary">
    <div class="box-body">

        <?php $form = ActiveForm::begin(['method' => 'GET', 'action' => ['/report/one']]); ?>
        <table>
            <tr>
                <td style="padding-right:8px"><?= Html::dropDownList('user_id', $user->id, ArrayHelper::map($users, 'id', 'name'), ['class' => 'form-control']) ?></td>
                <td style="padding-right:8px"><?= Html::dropDownList('month', $month, months(), ['class' => 'form-control']) ?></td>
                <td style="padding-right:8px"><?= Html::dropDownList('year', $year, $years, ['class' => 'form-control']) ?></td>
                <td><?= Html::submitButton('Refresh', ['class' => 'btn btn-primary d-print-none']) ?></td>
                <td>&nbsp;</td>
                <td><?= Html::a('All Users', ['/report/all'], ['class' => 'btn btn-default d-print-none', 'style' => 'vertical-align: baseline']) ?></td>
                <td>&nbsp;</td>
            </tr>
        </table>
        <?php ActiveForm::end(); ?>

        <br>

        <table class="table table-striped border">
            <tr>
                <th style="width: 1px; white-space:nowrap">Date</th>
                <th>Photo</th>
            </tr>
        <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { ?>
            <?php 
                $deletable = false;
                $late = 0;
                $early = 0;

                $isHoliday = false;
                $dayOfWeek = date('N', strtotime($year.'-'.$month.'-'.$i));
                if (/* $dayOfWeek == 6 ||  */$dayOfWeek == 7) $isHoliday = true;

                $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT);
                $date_formatted =  $year.'-'.$month.'-'.$date_padded;
            ?>

            <tr>
                <td class="<?= $isHoliday ? 'bg-danger text-light' : '' ?> text-end"><?= $date_padded ?></td>
                <td class="<?= $isHoliday ? 'bg-danger text-light' : '' ?>">
                    <?php 
                        $presences = Presence::find()->where(['user_id' => $user->id])->andWhere(new \yii\db\Expression("date_format(convert_tz(from_unixtime(`time`),'+00:00','+07:00'), '%Y-%m-%d') = '".$date_formatted."'"))->all();
                        if (count($presences) > 2) $deletable = true;
                    ?>
                    <?php if ($presences) { ?>
                        <?php $presenceFirst = Presence::find()->where(['user_id' => $user->id])->andWhere(new \yii\db\Expression("date_format(convert_tz(from_unixtime(`time`),'+00:00','+07:00'), '%Y-%m-%d') = '".$date_formatted."' AND convert_tz(from_unixtime(`time`),'+00:00','+07:00') < '".$date_formatted." 12:00:00'"))->orderBy('id ASC')->one(); ?>
                        <?php $presenceLast = Presence::find()->where(['user_id' => $user->id])->andWhere(new \yii\db\Expression("date_format(convert_tz(from_unixtime(`time`),'+00:00','+07:00'), '%Y-%m-%d') = '".$date_formatted."' AND convert_tz(from_unixtime(`time`),'+00:00','+07:00') > '".$date_formatted." 12:00:00'"))->orderBy('id DESC')->one(); ?>

                        <?php if ($presenceFirst && $presenceLast) { 
                            $counter++;
                        } ?>
                    <table>
                        <tr>
                    <?php foreach ($presences as $presence) { ?>
                        <td class="<?= $isHoliday ? 'bg-danger text-light' : '' ?>" style="padding-right: 16px;">
                            <?= $deletable || $presence->status == '<span class="text-muted">Invalid</span>' ? Html::a('<i class="bi bi-trash"></i>', ['/presence/delete', 'id' => $presence->id], [
                                'class' => 'btn btn-outline-danger btn-xs', 
                                'data-method' => 'post',
                                'data-confirm' => 'Delete?',
                                'style' => 'margin-bottom:8px',
                            ]) : '' ?>&nbsp;
                            <small><?= Yii::$app->formatter->asTime($presence->time, 'php:H:i') ?>&nbsp;</small>
                            <br><?= Html::img(['download-photo', 'id' => $presence->id], ['width' => '75px', 'style' => 'border-radius: 8px; border: 1px solid #ddd; margin-bottom:8px']); ?>
                            <br><small><?= $presence->status ?></small>
                        </td>
                    <?php } ?>
                        </tr>
                    </table>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </table>
    </div>
</div>
        
<div class="card d-none">
    <div class="card-body">
        <b><?= 'Jumlah: '.$counter.' hari' ?></b>
    </div>
</div>
