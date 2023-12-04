<?php
namespace app\controllers;

use app\dto\ClienteCreateDto;
use app\dto\ProductoCreateDto;
use app\dto\ProductoUpdateDto;
use app\models\Cliente;
use app\models\Producto;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;

class ApiController extends Controller
{
  static $cacheCliente="clientes";
  static $cacheProductos="productos";
  
  public function behaviors()
  {
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
      'class' => HttpBearerAuth::class,
    ];
    $behaviors['corsFilter'] = [
      'class' => Cors::class,
      'cors'  => [
          //Dominio
          'Origin' => ['*'],
      ]
    ];
    return $behaviors;
  }

  public function actionObtenerClientes()
  {
    $clientes = Yii::$app->cache->get(self::$cacheCliente);
    if($clientes == null){
      $clientes = Cliente::find()->where(["eliminado"=>0])->all();
      Yii::$app->cache->set(self::$cacheCliente, $clientes, 10*60);//Por 10 minutos, a menos que se use otro endpoint
    }
    return $clientes;
  }

  public function actionCrearCliente(){
    $dto = new ClienteCreateDto();
    $dto->load(Yii::$app->getRequest()->getBodyParams(),'');
    if(!$dto->validate()){
      Yii::$app->response->setStatusCode(400); 
      return ['errors' => $dto->errors];
    }
    if(Cliente::find()->where(["email"=>strtolower($dto->email)])->one() != null){
      throw new BadRequestHttpException("El email ya está registrado!");
    }
    $cliente = new Cliente();
    $cliente->nombre = $dto->nombre;
    $cliente->celular = $dto->celular;
    $cliente->email = strtolower($dto->email);
    $cliente->fecha_registro = gmdate("Y-m-d H:i:s");//Guardamos la fecha en UTC
    $cliente->save();
    Yii::$app->cache->delete(self::$cacheCliente);
    return $cliente;
  }
  
  public function actionActualizarCliente($id){
    $dto = new ClienteCreateDto();
    $dto->load(Yii::$app->getRequest()->getBodyParams(),'');
    if(!$dto->validate()){
      Yii::$app->response->setStatusCode(400); 
      return ['errors' => $dto->errors];
    }
    $cliente = Cliente::find()->where(["id"=>$id])->one();
    if($cliente == null){
      throw new BadRequestHttpException("El id no existe!");
    }
    if($cliente->email != strtolower($dto->email) && Cliente::find()->where(["email"=>strtolower($dto->email)])->one() != null){
      throw new BadRequestHttpException("El email ya está registrado!");
    }
    $cliente->nombre = $dto->nombre;
    $cliente->celular = $dto->celular;
    $cliente->email = strtolower($dto->email);
    $cliente->save();
    Yii::$app->cache->delete(self::$cacheCliente);
    return $cliente;
  }

  public function actionEliminarCliente($id){
    $cliente = Cliente::find()->where(["id"=>$id])->one();
    if($cliente == null){
      throw new BadRequestHttpException("No existe el id");
    }
    $cliente->eliminado = true;
    $cliente->save();
    Yii::$app->cache->delete(self::$cacheCliente);
    return $cliente;
  }

  public function actionObtenerProductos()
  {
    $productos = Yii::$app->cache->get(self::$cacheProductos);
    if($productos == null){
      $productos = Producto::find()->where(["eliminado"=>0])->all();
      Yii::$app->cache->set(self::$cacheProductos, $productos, 5*60);//Por 5 minutos, a menos que se use otro endpoint
    }
    return $productos;
  }

  public function actionCrearProducto(){
    $dto = new ProductoCreateDto();
    $dto->load(Yii::$app->getRequest()->getBodyParams(),'');
    if(!$dto->validate()){
      Yii::$app->response->setStatusCode(400); 
      return ['errors' => $dto->errors];
    }
    $producto = new Producto($dto->getAttributes());
    $producto->fecha_registro = gmdate("Y-m-d H:i:s");//Guardamos la fecha en UTC
    $producto->save();
    Yii::$app->cache->delete(self::$cacheProductos);
    return $producto;
  }

  public function actionActualizarProducto($codigo){
    $dto = new ProductoUpdateDto();
    $dto->load(Yii::$app->getRequest()->getBodyParams(),'');
    if(!$dto->validate()){
      Yii::$app->response->setStatusCode(400); 
      return ['errors' => $dto->errors];
    }
    $producto = Producto::find()->where(["codigo"=>$codigo])->one();
    if($producto == null){
      throw new BadRequestHttpException("No existe el codigo");
    }
    
    $producto->nombre = $dto->nombre;
    $producto->descripcion = $dto->descripcion;
    $producto->precio_compra = $dto->precio_compra;
    $producto->precio_venta = $dto->precio_venta;
    $producto->stock = $dto->stock;
    $producto->save();
    Yii::$app->cache->delete(self::$cacheProductos);
    return $producto;
  }

  public function actionEliminarProducto($codigo){
    $producto = Producto::find()->where(["codigo"=>$codigo])->one();
    if($producto == null){
      throw new BadRequestHttpException("No existe el id");
    }
    $producto->eliminado = true;
    $producto->save();
    Yii::$app->cache->delete(self::$cacheProductos);
    return $producto;
  }
}
