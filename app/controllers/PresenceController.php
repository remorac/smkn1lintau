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
use yii\web\UnprocessableEntityHttpException;

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

    public function actionCreate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        $model            = new Presence();
        $model->user_id   = $post['user_id'];
        $model->latitude  = $post['latitude'];
        $model->longitude = $post['longitude'];
        $model->time      = time();
        if (!$model->save()) {
            throw new UnprocessableEntityHttpException(stringifyModelErrors($model->errors));
        }

        $preImage = str_replace('data:image/jpeg;base64,', '', $post['photo']);
        $preImage = str_replace(' ', '+', $preImage);
        $image = base64_decode($preImage);

        $filepath = 'smkn1lintau/presence/'.$model->id.'-'.$model->time.'.jpg';
        if (Yii::$app->awsS3->put($filepath, $image)) {
            $model->photo = $model->id.'-'.$model->time.'.jpg';
            if (!$model->save()) {
                throw new UnprocessableEntityHttpException(stringifyModelErrors($model->errors));
            }
        }

        return [
            'status'  => 200,
            'message' => '',
            'data'    => [],
        ];
    }

    public function actionStream()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        $preImage = str_replace('data:image/jpeg;base64,', '', $post['photo']);
        $preImage = str_replace(' ', '+', $preImage);
        $image = base64_decode($preImage);

        $filepath = 'smkn1lintau/presence-stream/'.($post['user_id'] ?? 'G').'/'.date('Y-m-d').'/'.date('H-i-s').'.jpg';
        Yii::$app->awsS3->put($filepath, $image);

        return [
            'status'  => 200,
            'message' => '',
            'data'    => [],
        ];
    }
    
    public function actionDownloadPhoto($id)
    {
        $model = Presence::findOne($id);
        return downloadFilePresence($model, 'photo');
    }
}
