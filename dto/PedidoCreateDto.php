<?php

namespace app\dto;

use app\models\Cliente;
use app\models\Producto;
use yii\base\Model;

class PedidoCreateDto extends Model
{
    public $id_cliente;
    public $detalles; // Un array de detalles [{codigo_producto, cantidad}]

    public function rules()
    {
        return [
            [['id_cliente', 'detalles'], 'required'],
            ['id_cliente', 'exist', 'targetClass' => Cliente::class, 'targetAttribute' => 'id'],
            ['id_cliente', 'integer'],

            ['detalles', 'each', 'rule' => ['validateDetalle']],
        ];
    }

    public function validateDetalle($attribute, $params)
    {
        if (!is_array($this->$attribute)) {
            $this->addError($attribute, 'Los detalles deben ser un array de objetos({codigo_producto,cantidad})');
            return;
        }

        foreach ($this->$attribute as $detalle) {
            if (!isset($detalle['codigo_producto']) || !isset($detalle['cantidad'])) {
                $this->addError($attribute, 'Cada detalle debe contener código_producto y cantidad.');
                return;
            }

            // Validar que la cantidad sea al menos 1
            if ($detalle['cantidad'] < 1) {
                $this->addError($attribute, 'La cantidad de cada detalle debe ser como mínimo 1.');
            }

            // Validar que el código del producto exista en el modelo Producto
            $producto = Producto::findOne(['codigo' => $detalle['codigo_producto']]);
            if (!$producto) {
                $this->addError($attribute, 'El código del producto '.$detalle['codigo_producto'].' no existe.');
            }
        }
    }
}
