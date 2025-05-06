<?php

/** @var \yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..600;1,300..600&display=swap" rel="stylesheet"> -->
    <link href="<?= Yii::getAlias('@web') ?>/fonts/fellix/stylesheet.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100 bg-light">
<?php $this->beginBody() ?>

<header>
    <?php
    NavBar::begin([
        'brandLabel' => '<b>'.Yii::$app->name.'</b>',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar navbar-expand-md navbar-dark bg-dark fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => '', 'url' => ['/site/index']],
    ];
    if (!Yii::$app->user->isGuest) {
        $menuItems = [
            ['label' => 'Profile', 'url' => ['/user/view']],
            ['label' => 'Users', 'url' => ['/user/index'], 'visible' => Yii::$app->user->identity->position == 'Administrator'],
            ['label' => 'Report', 'url' => ['/report/all'], 'visible' => Yii::$app->user->identity->position == 'Administrator'],
            ['label' => 'Presence', 'url' => ['/presence/index']],
        ];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav me-auto mb-2 mb-md-0'],
        'items' => $menuItems,
    ]);
    if (Yii::$app->user->isGuest) {
        echo Html::tag('div',Html::a('Login',['/site/login'],['class' => ['btn btn-link login text-decoration-none']]),['class' => ['d-flex']]);
    } else {
        echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
            . Html::submitButton(
                '<span class="d-print-none">Logout</span> (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout text-decoration-none text-danger']
            )
            . Html::endForm();
    }
    NavBar::end();
    ?>
</header>

<main role="main" class="flex-shrink-0">
    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer class="footer mt-auto py-3 text-white bg-secondary">
    <div class="container">
        <p class="float-start mb-0">SMKN 1 Lintau Buo &copy; <?= date('Y') ?></p>
        <p class="float-end"><?=  '' // Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage();
