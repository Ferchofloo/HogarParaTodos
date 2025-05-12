CREATE PROCEDURE [dbo].[sp_CrearMascota]
    @p_Nombre      VARCHAR(100),
    @p_Especie     VARCHAR(50),
    @p_UsuarioID   INT,
    @p_Raza        VARCHAR(50) = NULL,
    @p_Edad        INT = NULL,
    @p_Descripcion TEXT = NULL,
    @p_FotoURL     VARCHAR(255) = NULL
AS
BEGIN
    -- Validar que el UsuarioID exista
    IF NOT EXISTS (SELECT 1 FROM Usuario WHERE UsuarioID = @p_UsuarioID)
        THROW 50000, 'Error: El UsuarioID no existe.', 1;

    -- Insertar la mascota
    INSERT INTO Mascota 
    (
        Nombre,
        Especie,
        UsuarioID,
        Raza,
        Edad,
        Descripcion,
        FotoURL,
        FechaSubida
    )
    VALUES 
    (
        @p_Nombre,
        @p_Especie,
        @p_UsuarioID,
        @p_Raza,
        @p_Edad,
        @p_Descripcion,
        @p_FotoURL,
        GETDATE()
    );
END;
GO
CREATE PROCEDURE [dbo].[sp_CrearMascota]
    @p_Nombre      VARCHAR(100),
    @p_Especie     VARCHAR(50),
    @p_UsuarioID   INT,
    @p_Raza        VARCHAR(50) = NULL,
    @p_Edad        INT = NULL,
    @p_Descripcion TEXT = NULL,
    @p_FotoURL     VARCHAR(255) = NULL
AS
BEGIN
    -- Validar que la edad sea entre 0 y 100 (si se suministra)
    IF @p_Edad IS NOT NULL AND (@p_Edad < 0 OR @p_Edad > 100)
    BEGIN
        THROW 50000, 'Error: La edad debe estar entre 0 y 100.', 1;
    END

    -- Validar el formato de la imagen (si se suministra)
    -- Se usa LOWER() para hacer la comparación insensible a mayúsculas
    IF @p_FotoURL IS NOT NULL AND 
       (LOWER(@p_FotoURL) NOT LIKE '%.jpg' AND LOWER(@p_FotoURL) NOT LIKE '%.png' AND LOWER(@p_FotoURL) NOT LIKE '%.giff')
    BEGIN
        THROW 50000, 'Error: El formato de la imagen debe ser jpg, png o giff.', 1;
    END

    -- Validar que el UsuarioID exista
    IF NOT EXISTS (SELECT 1 FROM Usuario WHERE UsuarioID = @p_UsuarioID)
    BEGIN
        THROW 50000, 'Error: El UsuarioID no existe.', 1;
    END

    -- Insertar la nueva mascota
    INSERT INTO Mascota (
        Nombre, Especie, UsuarioID, Raza, Edad, Descripcion, FotoURL, FechaSubida
    )
    VALUES (
        @p_Nombre, @p_Especie, @p_UsuarioID, @p_Raza, @p_Edad, @p_Descripcion, @p_FotoURL, GETDATE()
    );
END;
GO
