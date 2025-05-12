USE [HogarParaTodos];
GO

-- Variables para almacenar los nuevos IDs
DECLARE 
    @AdminID    INT,
    @UserID     INT,
    @MascotaID  INT,
    @AdopcionID INT;

-- Tablas temporales para capturar resultados de SCOPE_IDENTITY()
DECLARE @tmp TABLE(val INT);

-------------------------------------------------
-- 1) Crear Administrador
-------------------------------------------------
PRINT '== Crear Administrador ==';
-- Limpieza previa (solo para pruebas, opcional)
DELETE FROM dbo.Administrador WHERE Email = 'testadmin@hogar.com';

-- Captura el ID devuelto
INSERT INTO @tmp EXEC dbo.sp_CreateAdministrador
    @Nombre = 'Test Admin',
    @Email = 'testadmin@hogar.com',
    @Contrasena = 'adminPass';
SELECT @AdminID = val FROM @tmp;  
DELETE FROM @tmp;

-- Verificar
SELECT * FROM dbo.Administrador WHERE AdminID = @AdminID;

-------------------------------------------------
-- 2) Actualizar Administrador
-------------------------------------------------
PRINT '== Actualizar Administrador ==';
EXEC dbo.sp_UpdateAdministrador
    @AdminID = @AdminID,
    @Nombre  = 'Test Admin Mod',
    @Email   = 'testadmin_mod@hogar.com';
SELECT * FROM dbo.Administrador WHERE AdminID = @AdminID;

-------------------------------------------------
-- 3) Crear Usuario Web
-------------------------------------------------
PRINT '== Crear Usuario Web ==';
DELETE FROM dbo.Usuario WHERE Email = 'testuser@correo.com';

INSERT INTO @tmp EXEC dbo.sp_CreateUsuarioWeb
    @Nombre     = 'Test User',
    @Email      = 'testuser@correo.com',
    @Contrasena = 'userPass',
    @dui.       = '12345678-9',
    @Telefono   = '9999-0000',
    @Direccion  = 'Pruebas 123';
SELECT @UserID = val FROM @tmp;
DELETE FROM @tmp;

-- Verificar
SELECT * FROM dbo.Usuario WHERE UsuarioID = @UserID;

-- Autenticación correcta
PRINT 'Autenticación (correcta):';
EXEC dbo.sp_AuthUsuarioWeb
    @Email = 'testuser@correo.com',
    @Contrasena = 'userPass';

-- Autenticación incorrecta
PRINT 'Autenticación (fallida):';
EXEC dbo.sp_AuthUsuarioWeb
    @Email = 'testuser@correo.com',
    @Contrasena = 'wrong';

-------------------------------------------------
-- 4) Cambiar contraseña de Usuario
-------------------------------------------------
PRINT '== Cambiar Contraseña ==';
EXEC dbo.sp_UpdatePasswordUsuario
    @UsuarioID = @UserID,
    @NuevaPass = 'newUserPass';

-- Verificar autenticar con la nueva
EXEC dbo.sp_AuthUsuarioWeb
    @Email = 'testuser@correo.com',
    @Contrasena = 'newUserPass';

-------------------------------------------------
-- 5) Crear Mascota
-------------------------------------------------
PRINT '== Crear Mascota ==';
DELETE FROM dbo.Mascota WHERE Nombre = 'TestMascota';

INSERT INTO @tmp EXEC dbo.sp_CreateMascota
    @Nombre     = 'TestMascota',
    @Especie    = 'Gato',
    @UsuarioID  = @UserID,
    @Raza       = 'Siames',
    @Edad       = 1,
    @Descripcion= 'Pruebas gatunas',
    @FotoURL    = 'http://img/test.jpg';
SELECT @MascotaID = val FROM @tmp;
DELETE FROM @tmp;

-- Verificar
SELECT * FROM dbo.Mascota WHERE MascotaID = @MascotaID;

-- Listar disponibles
EXEC dbo.sp_ListarMascotasDisponibles;

-------------------------------------------------
-- 6) Crear Adopción y probar triggers
-------------------------------------------------
PRINT '== Crear Adopción ==';
DELETE FROM dbo.Adopcion WHERE UsuarioID = @UserID AND MascotaID = @MascotaID;

INSERT INTO @tmp EXEC dbo.sp_CreateAdopcion
    @UsuarioID = @UserID,
    @MascotaID = @MascotaID;
SELECT @AdopcionID = val FROM @tmp;
DELETE FROM @tmp;

-- Trigger AFTER INSERT debe poner mascota en 'en proceso'
SELECT MascotaID, Estado
FROM dbo.Mascota
WHERE MascotaID = @MascotaID;

-- Listar adopciones
EXEC dbo.sp_ListarAdopcionesPorUsuario @UsuarioID = @UserID;

-------------------------------------------------
-- 7) Completar Adopción
-------------------------------------------------
PRINT '== Completar Adopción ==';
EXEC dbo.sp_ActualizarEstadoAdopcion
    @AdopcionID  = @AdopcionID,
    @NuevoEstado = 'completada',
    @Comentarios = 'Prueba exitosa';

-- Ver estado final de adopción y mascota
SELECT * FROM dbo.Adopcion   WHERE AdopcionID = @AdopcionID;
SELECT MascotaID, Estado 
FROM dbo.Mascota
WHERE MascotaID = @MascotaID;

-------------------------------------------------
-- 8) Revisar Bitácora
-------------------------------------------------
PRINT '== Bitácora ==';
SELECT TOP(20) * 
FROM dbo.Bitacora 
ORDER BY Fecha_hora_sistema DESC;
GO
