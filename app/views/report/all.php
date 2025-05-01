<?php

use app\models\Presence;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = 'Report';
$this->params['breadcrumbs'][] = $this->title;

$years = [];
for ($i = 2025; $i <= date('Y'); $i++) {
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

<h2><?= $this->title ?></h2>

<?php $form = ActiveForm::begin(['method' => 'GET', 'action' => ['/report/all']]); ?>
    <table>
        <tr>
            <td style="padding-right:8px"><?= Html::dropDownList('month', $month, months(), ['class' => 'form-control']) ?></td>
            <td style="padding-right:8px"><?= Html::dropDownList('year', $year, $years, ['class' => 'form-control']) ?></td>
            <td><?= Html::submitButton('Refresh', ['class' => 'btn btn-primary d-print-none']) ?></td>
        </tr>
    </table>
<?php ActiveForm::end(); ?>

<br>
<div class="table-notresponsive me-8">
<table class="table table-condensed table-bordered table-striped">
    <tr>
        <th>#</th>
        <th>User</th>
        <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { $count[$i] = 0; $subcount[$i] = 0; ?>
        <?php $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
            <?php 
                $isHoliday = false;
                $dayOfWeek = date('N', strtotime($year.'-'.$month.'-'.$i));
                if (/* $dayOfWeek == 6 ||  */$dayOfWeek == 7) $isHoliday = true;
            ?>
            <th class="<?= $isHoliday ? 'bg-danger text-light' : '' ?>"><?= $date_padded ?></th>
        <?php } ?>
        <th class="text-end border-0 visibility-none">JML</th>
    </tr>

    <?php foreach ($users as $user) { ?>
        <?php $counter = 0; $sequence++; $sum_late = 0; ?>
            
        <tr>
            <td><?= $sequence ?></td>
            <td class="nowrap"><?= Html::a($user->name, ['/report/one', 'user_id' => $user->id, 'month' => $month, 'year' => $year]) ?></td>

            <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { ?>
                <?php 
                    $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $date_formatted =  $year.'-'.$month.'-'.$date_padded;
                    $isHoliday = false;
                    $dayOfWeek = date('N', strtotime($year.'-'.$month.'-'.$i));
                    if (/* $dayOfWeek == 6 ||  */$dayOfWeek == 7) $isHoliday = true;
                ?>
                <td class="<?= $isHoliday ? 'bg-danger text-light' : '' ?>">
                    <?php $presences = Presence::find()->where(['user_id' => $user->id])->andWhere(new \yii\db\Expression("date_format(convert_tz(from_unixtime(`time`),'+00:00','+07:00'), '%Y-%m-%d') = '".$date_formatted."'"))->all(); ?>
                    <?php if ($presences) { $count[$i]++; ?>
                    <table>
                        <tr>
                        <?php $presenceFirst = Presence::find()->where(['user_id' => $user->id])->andWhere(new \yii\db\Expression("date_format(convert_tz(from_unixtime(`time`),'+00:00','+07:00'), '%Y-%m-%d') = '".$date_formatted."' AND convert_tz(from_unixtime(`time`),'+00:00','+07:00') < '".$date_formatted." 12:00:00'"))->orderBy('id ASC')->one(); ?>
                        <?php if ($presenceFirst) { ?>
                            <td style="padding-right: 16px;">
                                <small><?= Yii::$app->formatter->asTime($presenceFirst->time, 'php:H:i') ?></small>
                                <span class="d-print-none"><br><?= Html::img(['download-photo', 'id' => $presenceFirst->id], ['width' => '50px', 'style' => 'border-radius: 8px; border: 1px solid #ddd']); ?></span>
                                <br><small><?= $presenceFirst->status ?></small>
                            </td>
                        <?php } ?>
                        <?php $presenceLast = Presence::find()->where(['user_id' => $user->id])->andWhere(new \yii\db\Expression("date_format(convert_tz(from_unixtime(`time`),'+00:00','+07:00'), '%Y-%m-%d') = '".$date_formatted."' AND convert_tz(from_unixtime(`time`),'+00:00','+07:00') > '".$date_formatted." 12:00:00'"))->orderBy('id DESC')->one(); ?>
                        <?php if ($presenceLast) { ?>
                            <td style="padding-right: 16px;">
                                <small><?= Yii::$app->formatter->asTime($presenceLast->time, 'php:H:i') ?></small>
                                <span class="d-print-none"><br><?= Html::img(['download-photo', 'id' => $presenceLast->id], ['width' => '50px', 'style' => 'border-radius: 8px; border: 1px solid #ddd']); ?></span>
                                <br><small><?= $presenceLast->status ?></small>
                            </td>
                        <?php } ?>
                        </tr>
                    </table>
                    <?php if ($presenceFirst && $presenceLast /* && !$isHoliday */) { 
                        $counter++; $subcount[$i]++; 
                    } ?>
                    <?php if ((int) $year == 2023 && (int) $month == 5 && ($presenceFirst || $presenceLast)) { $counter++; $total+= $benefit_base; $subcount[$i]++; } ?>
                    <?php } ?>
                </td>
            <?php } ?>

            <td class="text-end d-none">
                <small><?= $counter.' hari' ?></small>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <th></th>
        <th>Jumlah</th>
        <?php for ($i = 1; $i <= date('t', strtotime($year.'-'.$month.'-1')); $i++) { ?>
        <?php $date_padded = str_pad($i, 2, '0', STR_PAD_LEFT) ?>
            <?php 
                $isHoliday = false;
                $dayOfWeek = date('N', strtotime($year.'-'.$month.'-'.$i));
                if (/* $dayOfWeek == 6 ||  */$dayOfWeek == 7) $isHoliday = true;
            ?>
            <th class="<?= $isHoliday ? 'bg-danger text-light' : '' ?>"><?= $count[$i] ? $subcount[$i].' orang' : '' ?></th>
        <?php } ?>
        <th class="text-end d-none"><?= '' // Yii::$app->formatter->asInteger($total) ?></th>
    </tr>
</table>
</div>
