<?php

namespace app\dto;

use yii\base\Model;

class LoginDto extends Model
{
    public $email;
    public $password;

    public function rules()
    {
      return [
        [['email', 'password'], 'required'],
        ['email', 'email'],
      ];
    }
    
}
