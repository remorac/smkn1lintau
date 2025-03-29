<?php

namespace app\controllers;

use app\models\Presence;
use app\models\User;
use yii\web\Controller;
use DeviceDetector\DeviceDetector;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;

class ReportController extends Controller
{/**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionDownloadPhoto($id)
    {
        $model = Presence::findOne($id);
        // return downloadFilePresence($model, 'photo');
    }

    public function actionOne($year = null, $month = null, $user_id = null)
    {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');
        if ($user_id) $user = User::findOne($user_id);
        if (!$user_id || !Yii::$app->user->can('superuser')) $user = Yii::$app->user;

        return $this->render('one', [
            'year' => $year,
            'month' => $month,
            'user' => $user,
        ]);
    }

    public function actionAll($year = null, $month = null)
    {
        // if (!Yii::$app->user->can('Administrator')) $this->redirect(['one']);
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');

        return $this->render('all', [
            'year' => $year,
            'month' => $month,
        ]);
    }
}
