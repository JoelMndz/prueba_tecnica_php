# PRUEBA TÉCNICA PHP YII2
Enlace de la prueba: https://docs.google.com/document/d/1DtHu3iz3qsN9B2P-qOY_1ZxcnI40Ft3k_mlL6XIrAtU/edit
### Entorno
- php 7.2
- sql server 2019
- yii2

### Antes de Levantar el proyecto
- Instalar las dependencias con composer install
- Ejecutar el scrip.sql (Este script crea la tablas, insertar datos y crea los procedimientos almacenados)
- Configurar las variables de entorno en el .env que esta en la raíz del proyecto

Ahora si puede levantar el proyecto

# Datos relevantes de la PRUEBA TECNICA
## Parte 1: Conceptos de Yii2
### Modelos
Se crearon los Modelos Cliente, Pedido, Producto y PedidoDetalle, en los módelos estan las refectivas relaciones, este modelo mapea los campos que tiene su respectiva taba en la base de datos.<br>
Se creo el Modelo Usuario para manejar la autenticación

### API REST
Se creo un controlador APIController para las operaciones CRUD del Cliente y Producto. Además se creo el controlador AuthController para manejar al autenticación(login) y PedidoController para la gestión de pedidos. <br><br>
Para la Autenticación usamos JWT y como el API no tiene un registro el usuario se lo crea en el script de la base de datos con la contraseña encriptada <br><br>
Manejamos variables de entorno para los datos sensibles <br><br>
<b>Documentación del API REST: </b>
https://documenter.getpostman.com/view/15737139/2s9YeLXouq

## Parte 2: Trabajando con Base de Datos
### Optimización de Consultas
La optimación se la realizo con los modelos, al principio se obtenía todos los pedidos y luego se consultaba el cliente y los detalles del pedido. Pasamos de eso a tener una sola consulta que traia toda esa información. El c´dogo completo esta en PedidoController
```php
Pedido::find()
    ->with(['cliente','detalles','detalles.producto'])
    ->orderBy(['id' => SORT_DESC])
    ->asArray()
    ->one();
```
### Transacciones y Bloqueo de Base de Datos
Tanto para crear como para eliminar un Pedido se crearon procedimientos almacenados (estan en el script.sql) Cada uno usa transacciones y cuando pasas algo como querer comprar más del stock que un producto tiene se revierte todo y se generar un error en el procedimiento almacenado, este error es controllado en el controlador.

### Parte 3: Desarrollo de Frontend
Como usamos la plantilla MVC de yii2 lo que se hizo fue agregar el CDN de vue3 y a partir de eso de consumió el endpoint que devuelve todos los pedidos(este endpoint no esta protegido por esa razón), cuenta con un filtro en un rango de fechas(inicio,fin). Cuando se aplica el filtro tambien es manejado por vue aplicando conceptos de una SPA ya no la página no se recarga.

## Parte 4: Seguridad y Rendimiento
### Seguridad de la Aplicación
La medidas que consideramos en este punto fue:
- Que el JWT caduque a los 3 días de ser generado
- Configuración de los CORS, a pesar que esta habilitado para que cualquier dominio consuma nuestra API, esto puede ser cambiado con facilidad
- En los endpoinds de definieron Modelos(DTOs) para relalizar validaciónes
- Para evitar ataques de fuerza bruta se implemento Rate Limiting, quedo configrado para que cada cliente no pueda hacer más de 100 peticiones cada 10 minutos
### Caché y Rendimiento
Para Cliente y Producto se implemento el guardado caché, la lógica fue la siguiente: si se consulta la información de un cliente por primera vez va a consultar la base de datos y va a guardar el listado en cache por 10 minutos, si se vuelve a consumir el endpoint devulve lo que tenemos en cache. Además si se elimina, agregar ó edita se limpia el cache para tener consistencia.

## Parte 5: Pruebas Avanzadas
### Pruebas de Seguridad
Se realizo un ataque se fuerza bruta a en endpoint protegido y como tenemos implementado Rate Limiting el backend arrojó un error 429 al atacante.