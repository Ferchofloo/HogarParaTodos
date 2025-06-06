-- ============================================
-- SQL Server Script: HogarParaTodos - Procedimientos y Triggers Finales
-- Instrucciones: Ejecute este script en SQL Server Management Studio.
-- ============================================

-- 0) Crear base de datos y tablas si no existen
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'HogarParaTodos')
    CREATE DATABASE [HogarParaTodos]
GO
USE [HogarParaTodos]
GO

-- Crear tablas necesarias
IF OBJECT_ID('dbo.Administrador','U') IS NULL
BEGIN
    CREATE TABLE dbo.Administrador(
        AdminID INT IDENTITY(1,1) PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Email VARCHAR(100) NOT NULL UNIQUE,
        Contrasena VARCHAR(255) NOT NULL
    )
END
GO

IF OBJECT_ID('dbo.Usuario','U') IS NULL
BEGIN
    CREATE TABLE dbo.Usuario(
        UsuarioID INT IDENTITY(1,1) PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Email VARCHAR(100) NOT NULL UNIQUE,
        Contrasena VARCHAR(255) NOT NULL,
        dui VARCHAR(15) NULL,
        Telefono VARCHAR(15) NULL,
        Direccion VARCHAR(200) NULL,
        FechaRegistro DATETIME NOT NULL DEFAULT GETDATE()
    )
END
GO

IF OBJECT_ID('dbo.Mascota','U') IS NULL
BEGIN
    CREATE TABLE dbo.Mascota(
        MascotaID INT IDENTITY(1,1) PRIMARY KEY,
        Nombre VARCHAR(100) NOT NULL,
        Especie VARCHAR(50) NOT NULL,
        Raza VARCHAR(50) NULL,
        Edad INT NULL,
        Descripcion TEXT NULL,
        FotoURL VARCHAR(255) NULL,
        Estado VARCHAR(20) NOT NULL DEFAULT 'disponible',
        FechaSubida DATETIME NOT NULL DEFAULT GETDATE(),
        UsuarioID INT NOT NULL REFERENCES dbo.Usuario(UsuarioID)
    )
END
GO

IF OBJECT_ID('dbo.Adopcion','U') IS NULL
BEGIN
    CREATE TABLE dbo.Adopcion(
        AdopcionID INT IDENTITY(1,1) PRIMARY KEY,
        UsuarioID INT NOT NULL REFERENCES dbo.Usuario(UsuarioID),
        MascotaID INT NOT NULL UNIQUE REFERENCES dbo.Mascota(MascotaID),
        FechaAdopcion DATETIME NOT NULL DEFAULT GETDATE(),
        EstadoAdopcion VARCHAR(20) NOT NULL DEFAULT 'pendiente',
        Comentarios TEXT NULL
    )
END
GO

IF OBJECT_ID('dbo.Bitacora','U') IS NULL
BEGIN
    CREATE TABLE dbo.Bitacora(
        Id_reg INT IDENTITY(1,1) PRIMARY KEY,
        Usuario_sistema VARCHAR(100) NOT NULL,
        Fecha_hora_sistema DATETIME NOT NULL DEFAULT GETDATE(),
        Nombre_tabla VARCHAR(50) NOT NULL,
        Transaccion VARCHAR(10) NOT NULL
    )
END
GO

-- 1) Procedimientos Almacenados

-- Administrador CRUD
DROP PROCEDURE IF EXISTS dbo.sp_CreateAdministrador
GO
CREATE PROCEDURE dbo.sp_CreateAdministrador
    @Nombre VARCHAR(100),
    @Email VARCHAR(100),
    @Contrasena VARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON
    INSERT INTO dbo.Administrador(Nombre, Email, Contrasena)
    VALUES(@Nombre, @Email, @Contrasena)
    SELECT SCOPE_IDENTITY() AS AdminID
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_UpdateAdministrador
GO
CREATE PROCEDURE dbo.sp_UpdateAdministrador
    @AdminID INT,
    @Nombre VARCHAR(100),
    @Email VARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON
    UPDATE dbo.Administrador
    SET Nombre=@Nombre, Email=@Email
    WHERE AdminID=@AdminID
    SELECT @@ROWCOUNT AS FilasAfectadas
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_DeleteAdministrador
GO
CREATE PROCEDURE dbo.sp_DeleteAdministrador
    @AdminID INT
AS
BEGIN
    SET NOCOUNT ON
    DELETE FROM dbo.Administrador WHERE AdminID=@AdminID
    SELECT @@ROWCOUNT AS FilasAfectadas
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_GetAdministrador
GO
CREATE PROCEDURE dbo.sp_GetAdministrador
    @AdminID INT
AS
BEGIN
    SET NOCOUNT ON
    SELECT AdminID, Nombre, Email
    FROM dbo.Administrador
    WHERE AdminID=@AdminID
END
GO

-- Usuario CRUD + Auth
DROP PROCEDURE IF EXISTS dbo.sp_CreateUsuarioWeb
GO
CREATE PROCEDURE dbo.sp_CreateUsuarioWeb
    @Nombre VARCHAR(100),
    @Email VARCHAR(100),
    @Contrasena VARCHAR(255),
    @Telefono VARCHAR(15)=NULL,
    @Direccion VARCHAR(200)=NULL
AS
BEGIN
    SET NOCOUNT ON
    INSERT INTO dbo.Usuario(Nombre, Email, Contrasena, Telefono, Direccion, FechaRegistro)
    VALUES(@Nombre,@Email,@Contrasena,@Telefono,@Direccion,GETDATE())
    SELECT SCOPE_IDENTITY() AS UsuarioID
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_UpdatePasswordUsuario
GO
CREATE PROCEDURE dbo.sp_UpdatePasswordUsuario
    @UsuarioID INT,
    @NuevaPass VARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON
    UPDATE dbo.Usuario SET Contrasena=@NuevaPass WHERE UsuarioID=@UsuarioID
    IF @@ROWCOUNT=0 THROW 50001,'Usuario no encontrado.',1
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_AuthUsuarioWeb
GO
CREATE PROCEDURE dbo.sp_AuthUsuarioWeb
    @Email VARCHAR(100),
    @Contrasena VARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON
    SELECT UsuarioID, Nombre, Email
    FROM dbo.Usuario
    WHERE Email=@Email AND Contrasena=@Contrasena
END
GO

-- Mascota CRUD + Listar Disponibles
DROP PROCEDURE IF EXISTS dbo.sp_CreateMascota
GO
CREATE PROCEDURE dbo.sp_CreateMascota
    @Nombre VARCHAR(100),
    @Especie VARCHAR(50),
    @UsuarioID INT,
    @Raza VARCHAR(50)=NULL,
    @Edad INT=NULL,
    @Descripcion TEXT=NULL,
    @FotoURL VARCHAR(255)=NULL
AS
BEGIN
    SET NOCOUNT ON
    IF NOT EXISTS(SELECT 1 FROM dbo.Usuario WHERE UsuarioID=@UsuarioID)
        THROW 50002,'Usuario no existe.',1
    INSERT INTO dbo.Mascota(Nombre, Especie, Raza, Edad, Descripcion, FotoURL, Estado, FechaSubida, UsuarioID)
    VALUES(@Nombre,@Especie,@Raza,@Edad,@Descripcion,@FotoURL,'disponible',GETDATE(),@UsuarioID)
    SELECT SCOPE_IDENTITY() AS MascotaID
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_UpdateMascota
GO
CREATE PROCEDURE dbo.sp_UpdateMascota
    @MascotaID INT,
    @Nombre VARCHAR(100),
    @Especie VARCHAR(50),
    @Raza VARCHAR(50)=NULL,
    @Edad INT=NULL,
    @Descripcion TEXT=NULL,
    @FotoURL VARCHAR(255)=NULL,
    @Estado VARCHAR(20)='disponible'
AS
BEGIN
    SET NOCOUNT ON
    UPDATE dbo.Mascota
    SET Nombre=@Nombre, Especie=@Especie, Raza=@Raza, Edad=@Edad,
        Descripcion=@Descripcion, FotoURL=@FotoURL, Estado=@Estado
    WHERE MascotaID=@MascotaID
    SELECT @@ROWCOUNT AS FilasAfectadas
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_DeleteMascota
GO
CREATE PROCEDURE dbo.sp_DeleteMascota
    @MascotaID INT
AS
BEGIN
    SET NOCOUNT ON
    DELETE FROM dbo.Mascota WHERE MascotaID=@MascotaID
    SELECT @@ROWCOUNT AS FilasAfectadas
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_ListarMascotasDisponibles
GO
CREATE PROCEDURE dbo.sp_ListarMascotasDisponibles
AS
BEGIN
    SET NOCOUNT ON
    SELECT MascotaID, Nombre, Especie, Raza, Edad, Descripcion, FotoURL
    FROM dbo.Mascota WHERE Estado='disponible'
END
GO

-- Adopcion CRUD + Estado
DROP PROCEDURE IF EXISTS dbo.sp_CreateAdopcion
GO
CREATE PROCEDURE dbo.sp_CreateAdopcion
    @UsuarioID INT,
    @MascotaID INT
AS
BEGIN
    SET NOCOUNT ON
    INSERT INTO dbo.Adopcion(UsuarioID, MascotaID, FechaAdopcion, EstadoAdopcion)
    VALUES(@UsuarioID,@MascotaID,GETDATE(),'pendiente')
    SELECT SCOPE_IDENTITY() AS AdopcionID
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_ActualizarEstadoAdopcion
GO
CREATE PROCEDURE dbo.sp_ActualizarEstadoAdopcion
    @AdopcionID INT,
    @NuevoEstado VARCHAR(20),
    @Comentarios TEXT=NULL
AS
BEGIN
    SET NOCOUNT ON
    IF @NuevoEstado NOT IN('aprobada','rechazada','cancelada','completada')
        THROW 50003,'Estado inválido.',1
    UPDATE dbo.Adopcion
    SET EstadoAdopcion=@NuevoEstado, Comentarios=@Comentarios
    WHERE AdopcionID=@AdopcionID
    IF @@ROWCOUNT=0 THROW 50004,'Adopción no encontrada.',1
END
GO

DROP PROCEDURE IF EXISTS dbo.sp_ListarAdopcionesPorUsuario
GO
CREATE PROCEDURE dbo.sp_ListarAdopcionesPorUsuario
    @UsuarioID INT
AS
BEGIN
    SET NOCOUNT ON
    SELECT a.AdopcionID, a.MascotaID, m.Nombre AS MascotaNombre,
           a.FechaAdopcion, a.EstadoAdopcion, a.Comentarios
    FROM dbo.Adopcion a
    JOIN dbo.Mascota m ON a.MascotaID=m.MascotaID
    WHERE a.UsuarioID=@UsuarioID
END
GO

-- 2) Triggers de Integridad y Auditoría

DROP TRIGGER IF EXISTS dbo.trg_Adopcion_AfterInsert
GO
CREATE TRIGGER dbo.trg_Adopcion_AfterInsert
ON dbo.Adopcion
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON
    UPDATE m SET Estado='en proceso'
    FROM dbo.Mascota m JOIN inserted i ON m.MascotaID=i.MascotaID
END
GO

DROP TRIGGER IF EXISTS dbo.trg_Adopcion_AfterUpdate
GO
CREATE TRIGGER dbo.trg_Adopcion_AfterUpdate
ON dbo.Adopcion
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON
    UPDATE m SET Estado=
        CASE
            WHEN i.EstadoAdopcion='completada' THEN 'adoptado'
            WHEN i.EstadoAdopcion IN('cancelada','rechazada') THEN 'disponible'
            ELSE m.Estado
        END
    FROM dbo.Mascota m JOIN inserted i ON m.MascotaID=i.MascotaID
END
GO

DROP TRIGGER IF EXISTS dbo.trg_Administrador_Audit
GO
CREATE TRIGGER dbo.trg_Administrador_Audit
ON dbo.Administrador
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON
    INSERT INTO dbo.Bitacora(Usuario_sistema, Fecha_hora_sistema, Nombre_tabla, Transaccion)
    SELECT SYSTEM_USER, GETDATE(), 'Administrador',
           CASE
                WHEN i.AdminID IS NOT NULL AND d.AdminID IS NULL THEN 'Insert'
                WHEN i.AdminID IS NULL AND d.AdminID IS NOT NULL THEN 'Delete'
                ELSE 'Update'
           END
    FROM deleted d FULL OUTER JOIN inserted i ON d.AdminID=i.AdminID
END
GO

DROP TRIGGER IF EXISTS dbo.trg_Usuario_Audit
GO
CREATE TRIGGER dbo.trg_Usuario_Audit
ON dbo.Usuario
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON
    INSERT INTO dbo.Bitacora(Usuario_sistema, Fecha_hora_sistema, Nombre_tabla, Transaccion)
    SELECT SYSTEM_USER, GETDATE(), 'Usuario',
           CASE
                WHEN i.UsuarioID IS NOT NULL AND d.UsuarioID IS NULL THEN 'Insert'
                WHEN i.UsuarioID IS NULL AND d.UsuarioID IS NOT NULL THEN 'Delete'
                ELSE 'Update'
           END
    FROM deleted d FULL OUTER JOIN inserted i ON d.UsuarioID=i.UsuarioID
END
GO

DROP TRIGGER IF EXISTS dbo.trg_Mascota_Audit
GO
CREATE TRIGGER dbo.trg_Mascota_Audit
ON dbo.Mascota
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON
    INSERT INTO dbo.Bitacora(Usuario_sistema, Fecha_hora_sistema, Nombre_tabla, Transaccion)
    SELECT SYSTEM_USER, GETDATE(), 'Mascota',
           CASE
                WHEN i.MascotaID IS NOT NULL AND d.MascotaID IS NULL THEN 'Insert'
                WHEN i.MascotaID IS NULL AND d.MascotaID IS NOT NULL THEN 'Delete'
                ELSE 'Update'
           END
    FROM deleted d FULL OUTER JOIN inserted i ON d.MascotaID=i.MascotaID
END
GO

DROP TRIGGER IF EXISTS dbo.trg_Adopcion_Audit
GO
CREATE TRIGGER dbo.trg_Adopcion_Audit
ON dbo.Adopcion
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON
    INSERT INTO dbo.Bitacora(Usuario_sistema, Fecha_hora_sistema, Nombre_tabla, Transaccion)
    SELECT SYSTEM_USER, GETDATE(), 'Adopcion',
           CASE
                WHEN i.AdopcionID IS NOT NULL AND d.AdopcionID IS NULL THEN 'Insert'
                WHEN i.AdopcionID IS NULL AND d.AdopcionID IS NOT NULL THEN 'Delete'
                ELSE 'Update'
           END
    FROM deleted d FULL OUTER JOIN inserted i ON d.AdopcionID=i.AdopcionID
END
GO

INSERT INTO Administrador(Nombre, Email, Contrasena)
VALUES ('Administrador Principal', 'admin@example1.com', 'admin123');
GO