<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => 'Congreso de Ciencias Exactas e Ingenierias (ConCEI) 2026',
        'brandUrl' => 'https://concei.uady.mx/',
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
			[
				'label' => 'Registros',
				'url' => ['/registration/index'],
				'visible' => !Yii::$app->user->isGuest,
			],
			[
				'label' => 'Registration Type',
				'url' => ['/registration-type/index'],
				'visible' => !Yii::$app->user->isGuest,
			],
            // [
            //     'label' => 'Workshops',
            //     'url' => ['/workshops/index'],
            //     'visible' => !Yii::$app->user->isGuest,
            // ],
            [
                'label' => 'Talleres',
                'url' => ['/talleres/index'],
                'visible' => !Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Visitas',
                'url' => ['/visitas/index'],
                'visible' => !Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Registros Talleres',
                'url' => ['/registros-talleres/index'],
                'visible' => !Yii::$app->user->isGuest,
            ],
            [
                'label' => 'Registros Visitas',
                'url' => ['/registros-visitas/index'],
                'visible' => !Yii::$app->user->isGuest,
            ],
            [
				'label' => 'Registration Code',
				'url' => ['/registration-code/index'],
				'visible' => !Yii::$app->user->isGuest,
			],
			// [
			// 	'label' => 'Users',
			// 	'url' => ['/user/index'],
			// 	'visible' => !Yii::$app->user->isGuest,
			// ],
			[
				// 'label' => Yii::$app->user->isGuest ? 'Logout' : 'Logout (' . Yii::$app->user->identity->username . ')',
                'label' => Yii::$app->user->isGuest ? 'Logout' : 'Logout',
				'url' => ['/site/logout'],
				'linkOptions' => ['data-method' => 'post'],
				'visible' => !Yii::$app->user->isGuest,
			],
            
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
