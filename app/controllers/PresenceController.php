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

class PresenceController extends Controller
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

    public function actionIndex()
    {
        $this->layout = 'blank';
        $flag = 0;

        if (Yii::$app->user->id == 1) $flag = 1;

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if ($user_agent == 'SMKN1LintauPresenceApplication') $flag = 1;

        $dd = new DeviceDetector($user_agent);
        $dd->parse();
        if ($dd->getModel() == 'iPhone') $flag = 1;

        if ($flag) return $this->render('index');

        return $this->render('download');
    }

    public function actionDownload()
    {
        return Yii::$app->response->sendFile(Yii::getAlias('@uploads/smkn1lintau-presence.apk'));
    }

    public function actionView($year = null, $month = null)
    {
        if (!$year) $year = date('Y');
        if (!$month) $month = date('m');

        return $this->render('view', [
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Presence::findOne($id);
        if ($model && (Yii::$app->user->id == $model->user_id || Yii::$app->user->can('superuser'))) {
            if ($model->delete()) {
                $field      = 'photo';
                $filepath   = $model->tableName().'/'.$field.'/'.$model->$field;
                $fileExists = Yii::$app->awsS3->has($filepath);

                if ($fileExists) Yii::$app->awsS3->delete($filepath);
            }
            return $this->redirect(Yii::$app->request->referrer);
        }
        throw new ForbiddenHttpException('You are not allowed to delete this data.');
    }
}
