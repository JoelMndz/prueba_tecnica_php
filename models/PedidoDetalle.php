<?php

namespace app\models;

use yii\db\ActiveRecord;

class PedidoDetalle extends ActiveRecord
{
  public static function tableName()
  {
    return 'pedido_detalle';
  }

  public function getProducto()
  {
    return $this->hasOne(Producto::class, ['codigo' => 'codigo_producto']);
  }
}