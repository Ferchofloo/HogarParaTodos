CREATE TRIGGER trg_Auditoria_Adopcion
ON dbo.Adopcion
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
    VALUES (SUSER_SNAME(), GETDATE(), 'Adopcion', @Transaccion);
END;
GO
