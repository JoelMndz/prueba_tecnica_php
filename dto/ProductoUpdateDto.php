<?php

namespace app\dto;

use yii\base\Model;
use app\models\Producto; // Asegúrate de importar la clase Producto aquí

class ProductoUpdateDto extends Model
{
    public $nombre;
    public $descripcion;
    public $precio_compra;
    public $precio_venta;
    public $stock;

    public function rules()
    {
      return [
        [[ 'nombre', 'descripcion', 'precio_compra', 'precio_venta', 'stock'], 'required'],
        [['precio_compra', 'precio_venta'], 'number', 'min'=>0],
        ['stock', 'integer','min' => 0],
        [['nombre'], 'string', 'max' => 255],
        [['descripcion'], 'string', 'max' => 255],
        ['precio_venta', 'compare', 'compareAttribute' => 'precio_compra', 'operator' => '>', 'type' => 'number', 'message' => 'El precio de venta debe ser mayor que el precio de compra.'],
      ];
    }
    
}
