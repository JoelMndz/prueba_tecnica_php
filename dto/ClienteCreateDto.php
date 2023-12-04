<?php

namespace app\dto;

use yii\base\Model;

class ClienteCreateDto extends Model
{
  public $nombre;
  public $celular;
  public $email;

  // Define tus reglas de validación, campos, etc.
  public function rules()
  {
    return [
      [['nombre','celular', 'email'], 'required'],
      ['email', 'email'],
    ];
  }
}