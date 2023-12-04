--drop database prueba_tenica;
create database prueba_tecnica;
go
use prueba_tecnica;

create table cliente(
	id int identity(1,1) primary key,
	nombre varchar(255),
	celular varchar(20),
	email varchar(255),
	fecha_registro datetime,
	eliminado bit default 0
);

create table producto(
	codigo varchar(255) primary key,
	nombre varchar(255),
	descripcion varchar(255),
	precio_compra decimal(10,2),
	precio_venta decimal(10,2),
	stock int default 0,
	fecha_registro datetime,
	eliminado bit default 0
)

create table pedido(
	id int identity(1,1) primary key,
	fecha_registro datetime,
	iva decimal(10,2) default 0,
	subtotal decimal(10,2) default 0,
	total decimal(10,2) default 0,
	id_cliente int,
	eliminado bit default 0,
	foreign key(id_cliente) references cliente(id)
)

create table pedido_detalle(
	id int identity(1,1) primary key,
	cantidad int,
	total decimal(10,2) default 0,
	id_pedido int,
	codigo_producto varchar(255),
	foreign key(id_pedido) references pedido(id),
	foreign key(codigo_producto) references producto(codigo)
)

create table usuario(
	id int identity(1,1) primary key,
	email varchar(255),
	password text
);
go
-- ===== Procedimiento para crear un pedido con sus detalles ======
CREATE OR ALTER PROCEDURE crear_pedido
    @id_cliente INT,
    @detalles NVARCHAR(MAX)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @codigo_producto VARCHAR(255);
    DECLARE @cantidad INT;
    DECLARE @stock_actual INT;
    DECLARE @total_detalle DECIMAL(10,2);
    DECLARE @total_pedido DECIMAL(10,2) = 0;
    DECLARE @contador INT = 0;
	DECLARE @num_detalles INT = 0;
    DECLARE @id_pedido INT;

	BEGIN TRY;
		-- Iniciar la transacción
		BEGIN TRANSACTION;

		-- Crear el pedido
		INSERT INTO pedido (id_cliente, fecha_registro) VALUES (@id_cliente, GETUTCDATE());
		SET @id_pedido = SCOPE_IDENTITY();
		-- Obtener la cantidad de detalles en el JSON
		SELECT @num_detalles = COUNT(*) FROM OPENJSON(@detalles);
		-- Iniciar el bucle para iterar a través de los detalles del pedido
		WHILE @contador < @num_detalles
		BEGIN
			-- Extraer detalles del JSON
			SET @codigo_producto = JSON_VALUE(@detalles, CONCAT('$[', @contador, '].codigo_producto'));
			SET @cantidad = JSON_VALUE(@detalles, CONCAT('$[', @contador, '].cantidad'));

			-- Obtener el stock actual del producto
			SELECT @stock_actual = stock FROM producto WHERE codigo = @codigo_producto;

			-- Verificar si hay suficiente stock
			IF @stock_actual < @cantidad
			BEGIN
				RAISERROR (15600, -1, -1, 'No hay suficiente stock');
				ROLLBACK;
				RETURN;
			END;

			-- Actualizar el stock del producto
			UPDATE producto SET stock = @stock_actual - @cantidad WHERE codigo = @codigo_producto;

			-- Calcular el total del detalle y el total del pedido
			SET @total_detalle = @cantidad * (SELECT precio_venta FROM producto WHERE codigo = @codigo_producto);
			SET @total_pedido = @total_pedido + @total_detalle;

			-- Insertar el detalle del pedido
			INSERT INTO pedido_detalle (id_pedido, codigo_producto, cantidad, total) VALUES (@id_pedido, @codigo_producto, @cantidad, @total_detalle);

			SET @contador = @contador + 1;
		END;
	
		-- Actualizar el total del pedido
		UPDATE pedido SET subtotal=@total_pedido, iva=@total_pedido*0.12, total = @total_pedido*1.12 WHERE id = @id_pedido;
		-- Commit de la transacción
		COMMIT;
	END TRY
    BEGIN CATCH
		-- En caso de error, hacer un rollback de la transacción
		ROLLBACK;
		THROW;
    END CATCH;
END;

go
-- ======= Procedimiento para eliminar un pedido ======
CREATE OR ALTER PROCEDURE eliminar_pedido
    @id_pedido INT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @codigo_producto VARCHAR(255);
    DECLARE @eliminado bit;
    DECLARE @stock_actual INT;

	BEGIN TRY;
		-- Iniciar la transacción
		BEGIN TRANSACTION;

		select @eliminado=eliminado from pedido where id=@id_pedido;
		if(not exists(select * from pedido where id=@id_pedido))
		begin
			RAISERROR (15600, -1, -1, 'El ID no existe!');
			ROLLBACK;
		end
		if(@eliminado = 1)
		begin
			RAISERROR (15600, -1, -1, 'El pedido ya fue eliminado!');
			ROLLBACK;
		end

		-- Regreso el stock a los productos
		update producto
			set stock = stock + pd.cantidad
			from pedido_detalle as pd
			where producto.codigo = pd.codigo_producto
			  and pd.id_pedido = @id_pedido;

		-- Cambio el estado a eliminado del pedido
		update pedido set eliminado=1 where id=@id_pedido;
		
		COMMIT;
	END TRY
    BEGIN CATCH
		-- En caso de error, hacer un rollback de la transacción
		ROLLBACK;
		THROW;
    END CATCH;
END;
go

insert into usuario values('admin@gmail.com','$2y$13$ClNI9W0zoaSB6xlCVPUqcuEPYK0ffjiCFCP43K7FS1masTQTir9SW')
-- La contraseña es admin123
insert into cliente(nombre,celular,email,fecha_registro) values('Juan Veliz','0983334657','juan@gmail.com',GETDATE())
insert into producto (codigo,nombre,descripcion,fecha_registro,precio_compra,precio_venta,stock) values('CO001','Comoda','Comoda plegable',GETDATE(),80,150,5)
insert into producto (codigo,nombre,descripcion,fecha_registro,precio_compra,precio_venta,stock) values('CO002','Comoda 2.0','Comoda plegable',GETDATE(),100,180,5)

-- ====== Con este código se puede crear un pedido ========
-- exec crear_pedido 1,N'[{"codigo_producto":"CO001","cantidad":1},{"codigo_producto":"CO002","cantidad":1}]'

-- ===== Eliminar pedido ===============
-- exec eliminar_pedido 2
