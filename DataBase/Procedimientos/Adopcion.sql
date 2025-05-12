CREATE PROCEDURE [dbo].[sp_CompletarAdopcion]
    @p_AdopcionID INT,
    @p_AdminID    INT
AS
BEGIN
    DECLARE @v_MascotaID INT;
    
    -- Validar que el AdminID exista
    IF NOT EXISTS (SELECT 1 FROM Administrador WHERE AdminID = @p_AdminID)
        THROW 50000, 'Error: AdminID no válido.', 1;
    
    -- Validar que la adopción esté pendiente y obtener el MascotaID
    SELECT @v_MascotaID = MascotaID 
    FROM Adopcion 
    WHERE AdopcionID = @p_AdopcionID AND EstadoAdopcion = 'pendiente';
    
    IF @v_MascotaID IS NULL
        THROW 50000, 'Error: Adopción no encontrada o ya completada.', 1;
    
    BEGIN TRANSACTION;
        -- Actualizar estado de la adopción y asignar el AdminID
        UPDATE Adopcion 
        SET EstadoAdopcion = 'aprobada', AdminID = @p_AdminID
        WHERE AdopcionID = @p_AdopcionID;
    
        -- Actualizar automáticamente el estado de la mascota a 'adoptado'
        UPDATE Mascota 
        SET Estado = 'adoptado'
        WHERE MascotaID = @v_MascotaID;
    COMMIT TRANSACTION;
END;
GO

CREATE PROCEDURE [dbo].[sp_VerificarAdopcionesUsuario]
    @p_UsuarioID INT
AS
BEGIN
    DECLARE @v_TotalAdopciones INT;
    
    -- Contar las adopciones del usuario
    SELECT @v_TotalAdopciones = COUNT(*) 
    FROM Adopcion 
    WHERE UsuarioID = @p_UsuarioID;
    
    -- Validar que el usuario no tenga más de 3 adopciones
    IF @v_TotalAdopciones >= 3
        THROW 50000, 'Error: Límite de adopciones alcanzado (máximo 3 por usuario).', 1;
END;
GO
