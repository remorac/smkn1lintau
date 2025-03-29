<?php

use backend\models\Presence;
use backend\models\PresenceRule;
use backend\models\User;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Presence Report';
$this->params['breadcrumbs'][] = $this->title;

$interns = [
    // '214155', // PNP
    // '214621',
    // '214622',
    // '214648', // SMK 2
    // '214647',
    '215106', // SMK 1 Lintau
    '215105',
];
$is_intern = in_array($user->id, $interns);

$benefit_base = 25000;

$years = [];
for ($i = 2023; $i <= date('Y'); $i++) {
    $years[$i] = $i;
}
$userIDs = Presence::find()->select(['user_id'])->distinct()->column();
$users = User::find()->joinWith(['authAssignments'])->where([
    'and',
    ['like', 'item_name', 'int'],
    ['in', 'user.id', $userIDs],
])->orderBy('complete_name')->asArray()->all();

$presenceStatusIn  = Presence::STATUS_ABSENCE;
$presenceStatusOut = Presence::STATUS_ABSENCE;

$count    = [];
$total    = 0;
$sequence = 0;

$counter = 0;
$sum_late = 0; 
?>

<div class="box box-primary">
    <div class="box-body">

        <?php $form = ActiveForm::begin(['method' => 'GET', 'action' => ['/presence/report']]); ?>
        <table>
            <tr>
                <?php if (Yii::$app->user->can('superuser')) { ?>
                    <td style="padding-right:8px">
                        <?= Select2::widget([
                            'name' => 'user_id',
                            'value' => $user->id,
                            'data' => ArrayHelper::map($users, 'id', 'complete_name'),
                        ]); ?>
                    </td>
                <?php } ?>
                <td style="padding-right:8px">
                    <?= Select2::widget([
                        'name' => 'month',
                        'value' => $month,
                        'data' => months(),
                    ]); ?>
                </td>
                <td style="padding-right:8px">
                    <?= Select2::widget([
                        'name' => 'year',
                        'value' => $year,
                        'data' => $years,
                    ]); ?>
                </td>
                <td>
                    <?= Html::submitButton('Refresh', ['class' => 'btn btn-primary']) ?>
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    <?= Yii::$app->user->can('superuser') || Yii::$app->user->id == 172492 ? Html::a('All Users', ['/presence/report-all'], ['class' => 'btn btn-default', 'style' => 'vertical-align: baseline']) : '' ?>
                </td>
                <td>
                    &nbsp;
                </td>
                <td>
                    <?= Html::a('Exception', ['/presence-rule/index'], ['class' => 'btn btn-default', 'style' => 'vertical-align: baseline']) ?>
                </td>
            </tr>
        </table>
        <?php ActiveForm::end(); ?>

        <br>

        <table class="table table-striped">
            <tr>
                <th style="width: 1px; white-space:nowrap">Date</th>
                <th>Photo</th>
                <th class="text-right">Status</th>
            </tr>
        <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { ?>
            <?php 
                $deletable = false;
                $late = 0;
                $early = 0;

                $presenceStatusIn  = Presence::STATUS_ABSENCE;
                $presenceStatusOut = Presence::STATUS_ABSENCE;

                $isHoliday = false;
                $dayOfWeek = date('N', strtotime($year.'-'.$month.'-'.$i));
                if ($dayOfWeek == 6 || $dayOfWeek == 7) $isHoliday = true;

                $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT);
                $date_formatted =  $year.'-'.$month.'-'.$date_padded;
                
                if ($date_formatted >= '2024-10-09') $benefit_base = 28000;

                $gate_in  = '09:00';
                $gate_out = '16:00';
                $presenceRule = PresenceRule::findOne(['date' => $date_formatted, 'is_approved' => 1, 'user_id' => $user->id,]);
                if (!$presenceRule) $presenceRule = PresenceRule::findOne(['date' => $date_formatted, 'is_approved' => 1, 'user_id' => null,]);
                if ($presenceRule) {
                    $gate_in  = $presenceRule->in;
                    $gate_out = $presenceRule->out;
                }
            ?>

            <tr>
                <td><?= $date_padded ?></td>
                <td>
                    <?php 
                        $presences = Presence::findAll(['user_id' => $user->id, 'date' => $date_formatted]); 
                        if (count($presences) > 2) $deletable = true;
                    ?>
                    <?php if ($presences) { ?>
                        <?php $presenceFirst = Presence::find()->where(['user_id' => $user->id, 'date' => $date_formatted])->andWhere(['<=', 'time', '12:00:00'])->orderBy('id ASC')->one(); ?>
                        <?php $presenceLast = Presence::find()->where(['user_id' => $user->id, 'date' => $date_formatted])->andWhere(['>', 'time', '12:00:00'])->orderBy('id DESC')->one(); ?>

                        <?php if ($presenceFirst) { ?>
                            <?php 
                                $presenceStatusIn = Presence::STATUS_ONTIME;
                                if (strtotime($presenceFirst->time) > strtotime($gate_in)) {
                                    $presenceStatusIn = Presence::STATUS_COMING_LATE;
                                }
                            ?>
                        <?php } ?>
                        <?php if ($presenceLast) { ?>
                            <?php 
                                $presenceStatusOut = Presence::STATUS_ONTIME;
                                if (strtotime($presenceLast->time) < strtotime($gate_out)) {
                                    $presenceStatusOut = Presence::STATUS_LEAVING_EARLY;
                                }
                            ?>
                        <?php } ?>

                        <?php if ($presenceFirst && $presenceLast) { 
                            $counter++; $total+= $benefit_base;

                            if ($date_formatted > '2024-10-08') {
                                if ($presenceStatusIn  == Presence::STATUS_COMING_LATE)   $late  = strtotime($presenceFirst->time) - strtotime($gate_in);
                                if ($presenceStatusOut == Presence::STATUS_LEAVING_EARLY) $early = strtotime($gate_out) - strtotime($presenceLast->time);
                                $sum_late+= $late + $early;
                            }
                        } ?>
                        <?php if ((int) $year == 2023 && (int) $month == 5 && ($presenceFirst || $presenceLast)) { $counter++; $total+= $benefit_base; } ?>

                    <table>
                        <tr>
                    <?php foreach ($presences as $presence) { ?>
                        <td style="padding-right: 16px;">
                            <small><?= Yii::$app->formatter->asTime($presence->time, 'php:H:i:s') ?>&nbsp;</small>
                            <?= $deletable ? Html::a('<i class="fa fa-trash"></i>', ['/presence/delete', 'id' => $presence->id], [
                                'class' => 'btn btn-danger btn-xs', 
                                'data-method' => 'post',
                                'data-confirm' => 'Delete?',
                                'style' => 'margin-bottom:8px',
                            ]) : '' ?>
                            <br><?= Html::img(['download-photo', 'id' => $presence->id], ['width' => '75px', 'style' => 'border-radius: 8px; border: 1px solid #ddd; margin-bottom:8px']); ?>
                        </td>
                    <?php } ?>
                        </tr>
                    </table>
                    <?php } ?>
                </td>
                <td class="text-right">
                    <?php if ($date_formatted <= date('Y-m-d') && $presences) {
                        $firstText = ($presenceFirst ? '<span class="text-success"><i class="fa fa-check"></i>&nbsp; Pagi</span>' : '<span class="text-danger"><i class="fa fa-times"></i>&nbsp; Pagi</span>').'<br><span class="small">'.Presence::statuses($presenceStatusIn, true).' '.($late ? Yii::$app->formatter->asDecimal($late/60, 2).' menit' : '').'</span><br>';
                        $lastText  = ($presenceLast ? '<span class="text-success"><i class="fa fa-check"></i>&nbsp; Sore</span>' : '<span class="text-danger"><i class="fa fa-times"></i>&nbsp; Sore</span>').'<br><span class="small">'.Presence::statuses($presenceStatusOut, true).' '.($early ? Yii::$app->formatter->asDecimal($early/60, 2).' menit' : '').'</span><br>';
                        $infos = [$firstText, $lastText];
                        array_filter($infos);
                        echo implode('<br>', $infos);
                    } ?>
                </td>
            </tr>
        <?php } ?>
        </table>

        <!-- <br> -->
        <?= '' // Yii::$app->user->can('superuser') ? $counter.' hari x Rp 25.000 = <b>Rp '.Yii::$app->formatter->asInteger($counter*$benefit_base).'</b>' : '' ?>
 
    </div>
</div>
        
<div class="box box-primary">
    <div class="box-body">
        <?php $benefit_gross = $counter*$benefit_base; ?>
        <small>
            <?= $counter.' hari' ?>
            <?= $is_intern ? '' : '<br>Gross: Rp '.Yii::$app->formatter->asInteger($benefit_gross) ?>
            <?= ($sum_late ? '<br>Terlambat / Pulang Cepat : <b>'.(Yii::$app->formatter->asDecimal($sum_late/60, 2)).'</b> menit' : '') ?>
        </small>
        <?php 
            $discount = 0;
            if ($sum_late/60 >= 151) $discount = 0.2;
            if ($sum_late/60 >= 451) $discount = 0.3;
            if ($sum_late/60 >= 1001) $discount = 0.5;
            if ($sum_late/60 >= 2001) $discount = 1;
            $benefit_net = $benefit_gross - ($benefit_gross * $discount);
        ?>
        <?= $is_intern ? '' : '<br><b>Net: Rp '.Yii::$app->formatter->asInteger($benefit_net).'</b>' ?>
    </div>
</div>
