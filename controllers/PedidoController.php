<?php
namespace app\controllers;

use app\dto\PedidoCreateDto;
use app\models\Pedido;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\filters\RateLimiter;
use yii\rest\Controller;
use yii\validators\DateValidator;
use yii\web\BadRequestHttpException;

class PedidoController extends Controller
{
  public function behaviors()
  {
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
      'class' => HttpBearerAuth::class,
      'except' => ['obtener-pedidos'],
    ];
    $behaviors['corsFilter'] = [
      'class' => Cors::class,
      'cors'  => [
        // Dominios:
        'Origin' => ['*']
      ]
    ];

    $behaviors['rateLimiter'] = [
      // Use class
      'class' => RateLimiter::class,
      'enableRateLimitHeaders' => true,
    ];

    // $behaviors['rateLimiter']['enableRateLimitHeaders'] = false;

    return $behaviors;
  }

  public function actionObtenerPedidos(){
    $query = Yii::$app->request->getQueryParams();
    $validator = new DateValidator();
    $validator->format = 'YYYY-MM-DD';
    $inicio = $query['inicio'] ?? null;
    $fin = $query['fin'] ?? null;
    if ($inicio !== null && !$validator->validate($inicio)) {
      throw new BadRequestHttpException('La fecha de inicio no tiene el formato esperado.');
    }
    if ($fin !== null && !$validator->validate($fin)) {
      throw new BadRequestHttpException('La fecha de fin no tiene el formato esperado.');
    }
    $pedidos = Pedido::find()
      ->where(["eliminado" => 0]);
    if($inicio != null){
      $pedidos = $pedidos->andWhere(['>=', 'fecha_registro', $inicio]);
    }
    if($fin != null){
      $pedidos = $pedidos->andWhere(['<=', 'fecha_registro', $fin]);
    }
    $pedidos = $pedidos->with(['cliente','detalles','detalles.producto'])
      ->asArray()
      ->all();
    return $pedidos;
  }

  public function actionCrear(){
    try {
      $dto = new PedidoCreateDto();
      $dto->load(Yii::$app->getRequest()->getBodyParams(),'');
      if(!$dto->validate()){
        Yii::$app->response->setStatusCode(400); 
        return ['errors' => $dto->errors];
      }
      $command = Yii::$app->db->createCommand("exec crear_pedido :param1, :param2")
        ->bindValue(':param1', $dto->id_cliente)
        ->bindValue(':param2', json_encode($dto->detalles));
      $command->execute();
      return Pedido::find()
        ->with(['cliente','detalles','detalles.producto'])
        ->orderBy(['id' => SORT_DESC])
        ->asArray()
        ->one();
    } catch (\Exception $th) {
      Yii::$app->response->setStatusCode(400);
      return $th;
    }
  }

  public function actionEliminar($id){
    try {
      $command = Yii::$app->db->createCommand("exec eliminar_pedido :param1")
        ->bindValue(':param1', $id);
      $command->execute();
      return Pedido::find()
        ->where(["id"=>$id])
        ->one();
    } catch (\Exception $th) {
      Yii::$app->response->setStatusCode(400);
      return $th;
    }
  }
}