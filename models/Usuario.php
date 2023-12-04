<?php

namespace app\models;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Yii;
use yii\db\ActiveRecord;
use yii\filters\RateLimitInterface;
use yii\web\BadRequestHttpException;

class Usuario extends ActiveRecord implements \yii\web\IdentityInterface, RateLimitInterface
{
    public $rateLimit = 1;
    public $allowance;
    public $allowance_updated_at;
    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        try {
            $secreto = $_ENV['SECRETO'];
            $decodificado = JWT::decode($token, new Key($secreto, 'HS256'));
            if(!isset($decodificado->id)){
                return null;
            }
            $usuario = self::find()->where(["id" => $decodificado->id])->one();
            return $usuario;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function getRateLimit($request, $action)
    {
        return [100, 60*10]; // 100 solicitudes maximas por cada 10 minutos
    }

    public function loadAllowance($request, $action)
    {
        return [$this->allowance, $this->allowance_updated_at];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->allowance = $allowance;
        $this->allowance_updated_at = $timestamp;
        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        // return self::find()->where()
        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        // foreach (self::$users as $user) {
        //     if (strcasecmp($user['username'], $username) === 0) {
        //         return new static($user);
        //     }
        // }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
