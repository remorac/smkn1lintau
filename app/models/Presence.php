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

}
