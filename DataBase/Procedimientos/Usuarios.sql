CREATE PROCEDURE [dbo].[sp_CrearUsuario]
    @p_Nombre      VARCHAR(100),
    @p_Email       VARCHAR(100),
    @p_Contrasena  VARCHAR(255),
    @p_Telefono    VARCHAR(15) = NULL,
    @p_Direccion   VARCHAR(200) = NULL
AS
BEGIN
    -- Validar que el email no exista
    IF EXISTS (SELECT 1 FROM Usuario WHERE Email = @p_Email)
        THROW 50000, 'Error: El email ya se encuentra registrado.', 1;
    
    -- Insertar el nuevo usuario
    INSERT INTO Usuario 
    (
        Nombre,
        Email,
        Contrasena,
        Telefono,
        Direccion,
        FechaRegistro
    )
    VALUES 
    (
        @p_Nombre,
        @p_Email,
        @p_Contrasena,
        @p_Telefono,
        @p_Direccion,
        GETDATE()
    );
END;
GO

CREATE PROCEDURE [dbo].[sp_ModificarUsuario]
    @p_UsuarioID INT,
    @p_Nombre    VARCHAR(100),
    @p_Email     VARCHAR(100),
    @p_Telefono  VARCHAR(15) = NULL,
    @p_Direccion VARCHAR(200) = NULL
AS
BEGIN
    -- Verificar que el usuario exista
    IF NOT EXISTS (SELECT 1 FROM Usuario WHERE UsuarioID = @p_UsuarioID)
        THROW 50000, 'Error: Usuario no encontrado.', 1;
    
    -- Validar que el email no esté en uso por otro usuario
    IF EXISTS (SELECT 1 FROM Usuario WHERE Email = @p_Email AND UsuarioID <> @p_UsuarioID)
        THROW 50000, 'Error: El email ya se encuentra registrado para otro usuario.', 1;
    
    -- Actualizar la información del usuario
    UPDATE Usuario
    SET Nombre    = @p_Nombre,
        Email     = @p_Email,
        Telefono  = @p_Telefono,
        Direccion = @p_Direccion
    WHERE UsuarioID = @p_UsuarioID;
END;
GO
