<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "presence".
 *
 * @property int $id
 * @property int $user_id
 * @property int $time
 * @property string $latitude
 * @property string $longitude
 * @property string|null $photo
 *
 * @property User $user
 */
class Presence extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'presence';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['photo'], 'default', 'value' => null],
            [['user_id', 'time', 'latitude', 'longitude'], 'required'],
            [['user_id', 'time'], 'integer'],
            [['latitude', 'longitude', 'photo'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'time' => 'Time',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'photo' => 'Photo',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getStatus() 
    {
        $time = Yii::$app->formatter->asTime($this->time, 'php:H:i:s');

        $type = 'in';
        if ($time >= '12:00:00') $type = 'out';
        
        if ($type == 'in') {
            $pre = Yii::$app->params['inPre'];
            $start = Yii::$app->params['inStart'];
            $end = Yii::$app->params['inEnd'];
            $post = Yii::$app->params['inPost'];
        } else if ($type == 'out') {
            $pre = Yii::$app->params['outPre'];
            $start = Yii::$app->params['outStart'];
            $end = Yii::$app->params['outEnd'];
            $post = Yii::$app->params['outPost'];
        } else {
            return 'Invalid Type';
        }

        $status = '<span class="text-muted">Invalid</span>';
        if ($time >= $start && $time <= $end) {
            $status = '<span class="text-success">Ontime</span>';
        } else if ($time > $end && $time <= $post) {
            $status = '<span class="text-danger">Terlambat</span>';
        }
        return $status;
    }
}
