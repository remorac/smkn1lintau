<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property string|null $verification_token
 * @property string|null $name
 * @property string|null $sex
 * @property string|null $birthdate
 * @property string|null $position
 * @property int $is_excepted
 *
 * @property Presence[] $presences
 */
class User extends \common\models\User
{
    public $password;
    public $birthdate_day;
    public $birthdate_month;
    public $birthdate_year;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password_reset_token', 'verification_token', 'name', 'sex', 'birthdate', 'position'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 10],
            [['is_excepted'], 'default', 'value' => 0],
            [['username', 'auth_key', 'password_hash', 'email', 'name', 'sex', 'birthdate', 'position', 'birthdate_day', 'birthdate_month', 'birthdate_year'], 'required'],
            [['status', 'is_excepted', 'created_at', 'updated_at'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'verification_token', 'name', 'sex', 'birthdate', 'position'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
            [['password'], 'string', 'min' => 8],
            [['password'], 'required', 'on' => 'create'],
            [['birthdate_year'], 'integer', 'min' => 1900, 'max' => date('Y')],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'verification_token' => 'Verification Token',
            'name' => 'Nama Lengkap',
            'sex' => 'Jenis Kelamin',
            'birthdate' => 'Tanggal Lahir',
            'position' => 'Jabatan',
            'is_excepted' => 'Android Web',
            'birthdate_day' => 'Tanggal',
            'birthdate_month' => 'Bulan',
            'birthdate_year' => 'Tahun',
        ];
    }

    /**
     * Gets query for [[Presences]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPresences()
    {
        return $this->hasMany(Presence::class, ['user_id' => 'id']);
    }

    public static function isExceptedList($html = true)
    {
        if ($html) return [
            1 => '<span class="text-warning">Diizinkan</span>',
            0 => '<span class="text-muted">Ditolak</span>',
        ];
        return [
            1 => 'Diizinkan',
            0 => 'Ditolak',
        ];
    }

    public function getIsExceptedLabel($html = true)
    {
        return static::isExceptedList($html)[$this->is_excepted];
    }

    public static function statusList($html = true)
    {
        if ($html) return [
            10 => '<span class="text-success">Active</span>',
            0 => '<span class="text-danger">Inactive</span>',
        ];
        return [
            10 => 'Active',
            0 => 'Inactive',
        ];
    }

    public function getStatusLabel($html = true)
    {
        return static::statusList($html)[$this->status];
    }

}
