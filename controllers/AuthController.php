<?php
namespace app\controllers;

use app\dto\LoginDto;
use app\models\Usuario;
use Firebase\JWT\JWT;
use Yii;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;

class AuthController extends Controller
{
  
  public function behaviors()
  {
    $behaviors = parent::behaviors();
    $behaviors['corsFilter'] = [
      'class' => Cors::class,
      'cors'  => [
        // Dominios:
        'Origin' => ['*']
      ]
    ];

    return $behaviors;
  }

  public function actionLogin(){
    $dto = new LoginDto();
    $dto->load(Yii::$app->getRequest()->getBodyParams(),'');
    if(!$dto->validate()){
      Yii::$app->response->setStatusCode(400); 
      return ['errors' => $dto->errors];
    }
    
    $usuario = Usuario::find()->where(["email"=>strtolower($dto->email)])->one();
    if($usuario == null || !Yii::$app->getSecurity()->validatePassword($dto->password, $usuario->password)){
      throw new BadRequestHttpException("Credenciales incorrectas!");
    }
    unset($usuario->password);
    return ["usuario" => $usuario, "token" => $this->generarToken($usuario->id)];
  }

  public function generarToken($id){
    $secreto = $_ENV['SECRETO'];
    $configuracion = [
      'iss' => 'Api Rest', 
      'aud' => 'API REST', 
      'iat' => time(), // Tiempo de emisión del token (en segundos)
      'exp' => time() + (3 * 24 * 60 * 60), //(DIAS HORAS MINUTOS SEGUNDOS)
      'id' => $id
    ];
    $token = JWT::encode($configuracion, $secreto, 'HS256');
    return $token;
  }
}