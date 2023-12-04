<?php

namespace app\models;

use yii\db\ActiveRecord;

class Pedido extends ActiveRecord
{
  
  public function getCliente()
  {
    return $this->hasOne(Cliente::class, ['id' => 'id_cliente']);
  }

  public function getDetalles()
  {
      return $this->hasMany(PedidoDetalle::class, ['id_pedido' => 'id']);
  }
}