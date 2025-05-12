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

-- =============================================
-- Procedimiento: ProcesarSolicitud
-- Descripción: Permite al administrador aprobar o rechazar una solicitud.
-- Parámetros:
--   @IdSolicitud INT - ID de la solicitud a procesar.
--   @NuevaDecision NVARCHAR(50) - 'Aprobada' o 'Rechazada'
-- =============================================
CREATE PROCEDURE ProcesarSolicitud
    @IdSolicitud INT,
    @NuevaDecision NVARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @EstadoActual NVARCHAR(50);

    -- Verificar existencia de la solicitud
    IF NOT EXISTS (SELECT 1 FROM Solicitudes WHERE IdSolicitud = @IdSolicitud)
    BEGIN
        PRINT 'Error: La solicitud no existe.';
        RETURN;
    END

    -- Obtener estado actual
    SELECT @EstadoActual = Estado
    FROM Solicitudes
    WHERE IdSolicitud = @IdSolicitud;

    -- Verificar si ya fue procesada
    IF @EstadoActual IN ('Aprobada', 'Rechazada')
    BEGIN
        PRINT 'La solicitud ya ha sido procesada anteriormente.';
        RETURN;
    END

    -- Validar entrada del parámetro @NuevaDecision
    IF @NuevaDecision NOT IN ('Aprobada', 'Rechazada')
    BEGIN
        PRINT 'Error: La decisión debe ser Aprobada o Rechazada.';
        RETURN;
    END

    -- Aplicar la decisión
    UPDATE Solicitudes
    SET Estado = @NuevaDecision
    WHERE IdSolicitud = @IdSolicitud;

    PRINT 'Solicitud procesada correctamente. Nuevo estado: ' + @NuevaDecision;
END;
GO

-- =============================================
-- Procedimiento: ProcesarSolicitudesPendientes
-- Descripción: Recorre todas las solicitudes con estado 'Pendiente' y las cambia a 'Rechazada'.
--              Útil para limpiar solicitudes no atendidas después de cierto tiempo.
-- =============================================
CREATE PROCEDURE ProcesarSolicitudesPendientes
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE 
        @IdSolicitud INT;

    -- Cursor basado en WHILE para procesar las solicitudes pendientes una por una
    DECLARE solicitudes_cursor CURSOR FOR
        SELECT IdSolicitud
        FROM Solicitudes
        WHERE Estado = 'Pendiente';

    OPEN solicitudes_cursor;
    FETCH NEXT FROM solicitudes_cursor INTO @IdSolicitud;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- Rechazar solicitud
        UPDATE Solicitudes
        SET Estado = 'Rechazada'
        WHERE IdSolicitud = @IdSolicitud;

        -- Mensaje informativo
        PRINT 'Solicitud ID ' + CAST(@IdSolicitud AS NVARCHAR) + ' ha sido rechazada.';

        FETCH NEXT FROM solicitudes_cursor INTO @IdSolicitud;
    END

    CLOSE solicitudes_cursor;
    DEALLOCATE solicitudes_cursor;

    PRINT 'Todas las solicitudes pendientes han sido procesadas.';
END;
GO

