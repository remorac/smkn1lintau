<?php

use app\models\Presence;
use app\models\User;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Presence Report';
$this->params['breadcrumbs'][] = $this->title;

$years = [];
for ($i = 2023; $i <= date('Y'); $i++) {
    $years[$i] = $i;
}
$userIDs = Presence::find()->select(['user_id'])->distinct()->column();
$users = User::find()->where([
    'and',
    ['in', 'user.id', $userIDs],
])->orderBy('name')->all();

$count    = [];
$subcount = [];
$total    = 0;
$sequence = 0;
?>

<div class="box box-primary">
    <div class="box-body">

        <?php $form = ActiveForm::begin(['method' => 'GET', 'action' => ['/presence/report-all']]); ?>
        <table>
            <tr>
                <td style="padding-right:8px">
                    <?= Html::dropDownList('month', $month, months(), ['class' => 'form-control']) ?>
                </td>
                <td style="padding-right:8px">
                    <?= Html::dropDownList('year', $year, $years, ['class' => 'form-control']) ?>
                </td>
                <td>
                    <?= Html::submitButton('Refresh', ['class' => 'btn btn-primary']) ?>
                </td>
            </tr>
        </table>
        <?php ActiveForm::end(); ?>

        <br>

        <table class="table table-condensed table-bordered table-striped">
            <tr>
                <th></th>
                <th>User</th>
                <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { $count[$i] = 0; $subcount[$i] = 0; ?>
                <?php $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                    <th><?= $date_padded ?></th>
                <?php } ?>
                <th class="text-right">Jumlah</th>
            </tr>
        <?php foreach ($users as $user) { ?>
        <?php $counter = 0; $sequence++; $sum_late = 0; ?>
            <tr>
                <td><?= $sequence ?></td>
                <td><?= Html::a($user->name, ['/report/one', 'user_id' => $user->id, 'month' => $month, 'year' => $year], ['target' => '_blank']) ?></td>

                <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { ?>
                    <?php 
                        $isHoliday = false;
                        $dayOfWeek = date('N', strtotime($year.'-'.$month.'-'.$i));
                        if ($dayOfWeek == 6 || $dayOfWeek == 7) $isHoliday = true;

                        $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $date_formatted =  $year.'-'.$month.'-'.$date_padded;

                        if ($date_formatted >= '2024-10-09') $benefit_base = 28000;
                        
                        $gate_in  = '09:00';
                        $gate_out = '16:00';
                    ?>
                    <td class="<?= $isHoliday ? 'bg-danger' : '' ?>">
                        <?php $presences = Presence::find()->where(['user_id' => $user->id])/* ->andWhere(new \yii\db\Expression("from_unixtime(`time`, '%Y-%M-%D') = '".$date_formatted."'")) */->all(); ?>
                        <?php if ($presences) { $count[$i]++; ?>
                        <table>
                            <tr>
                            <?php $presenceFirst = Presence::find()->where(['user_id' => $user->id, 'date' => $date_formatted])->andWhere(['<=', 'time', '12:00:00'])->orderBy('id ASC')->one(); ?>
                            <?php if ($presenceFirst) { ?>
                                <td style="padding-right: 16px;">
                                    <?= Yii::$app->formatter->asDatetime($presenceFirst->time, 'short') ?>
                                    <br><?= Html::img(['download-photo', 'id' => $presenceFirst->id], ['width' => '50px', 'style' => 'border-radius: 8px; border: 1px solid #ddd']); ?>
                                </td>
                            <?php } ?>
                            <?php $presenceLast = Presence::find()->where(['user_id' => $user->id, 'date' => $date_formatted])->andWhere(['>', 'time', '12:00:00'])->orderBy('id DESC')->one(); ?>
                            <?php if ($presenceLast) { ?>
                                <td style="padding-right: 16px;">
                                    <?= Yii::$app->formatter->asDatetime($presenceLast->time, 'short') ?>
                                    <br><?= Html::img(['download-photo', 'id' => $presenceLast->id], ['width' => '50px', 'style' => 'border-radius: 8px; border: 1px solid #ddd']); ?>
                                </td>
                            <?php } ?>
                            </tr>
                        </table>
                        <?php if ($presenceFirst && $presenceLast && !$isHoliday) { 
                            $counter++; $subcount[$i]++; 
                        } ?>
                        <?php if ((int) $year == 2023 && (int) $month == 5 && ($presenceFirst || $presenceLast)) { $counter++; $total+= $benefit_base; $subcount[$i]++; } ?>
                        <?php } ?>
                    </td>
                <?php } ?>

                <td class="text-right">
                    <small>
                        <?= $counter.' hari' ?>
                    </small>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th></th>
            <th>Jumlah</th>
            <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { ?>
            <?php $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
                <th><?= $count[$i] ? $subcount[$i].'/'.$count[$i].' orang' : '-' ?></th>
            <?php } ?>
            <th class="text-right"><?= Yii::$app->formatter->asInteger($total) ?></th>
        </tr>
        </table>

    </div>
</div>
