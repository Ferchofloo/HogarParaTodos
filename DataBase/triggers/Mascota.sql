CREATE TRIGGER trg_Auditoria_Mascota
ON dbo.Mascota
AFTER INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @Transaccion VARCHAR(10);

    IF EXISTS (SELECT * FROM inserted) AND EXISTS (SELECT * FROM deleted)
        SET @Transaccion = 'Update';
    ELSE IF EXISTS (SELECT * FROM inserted)
        SET @Transaccion = 'Insert';
    ELSE IF EXISTS (SELECT * FROM deleted)
        SET @Transaccion = 'Delete';

    INSERT INTO dbo.Bitacora (Usuario_sistema, Fecha_hora_sistema, Nombre_tabla, Transaccion)
    VALUES (SUSER_SNAME(), GETDATE(), 'Mascota', @Transaccion);
END;
GO


CREATE TRIGGER trg_ActualizarEstadoMascota
ON dbo.Adopcion
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Si el estado de la adopción se cambia a 'aprobada' o 'aceptada', actualizar la mascota
    IF EXISTS (SELECT * FROM inserted WHERE EstadoAdopcion IN ('aprobada', 'aceptada'))
    BEGIN
        UPDATE m
        SET m.Estado = 'adoptado'
        FROM dbo.Mascota m
        INNER JOIN inserted i ON m.MascotaID = i.MascotaID
        WHERE i.EstadoAdopcion IN ('aprobada', 'aceptada') AND m.Estado <> 'adoptado';
    END;
END;
GO
