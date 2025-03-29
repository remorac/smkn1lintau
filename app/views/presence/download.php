<?php

use yii\helpers\Url;
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Appskep Presence';
$this->params['breadcrumbs'][] = $this->title;
?>
<br>
<br>
<br>

<h4>SMKN 1 Lintau Presence App</h4>
<p>Download dan install apk utk melanjutkan.</p>
<br>
<?= Html::a('Download APK', ['download'], ['class' => 'btn btn-success btn-lg']) ?>
<br>
<br>
<?= Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
. Html::submitButton(
	'Logout (' . Yii::$app->user->identity->username . ')',
	['class' => 'btn btn-link logout text-decoration-none text-danger']
)
. Html::endForm(); ?>

