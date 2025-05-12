CREATE TRIGGER trg_Auditoria_Usuario
ON dbo.Usuario
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
    VALUES (SUSER_SNAME(), GETDATE(), 'Usuario', @Transaccion);
END;
GO

CREATE TRIGGER trg_LimiteAdopciones
ON dbo.Adopcion
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @UsuarioID INT;
    DECLARE @TotalAdopciones INT;

    DECLARE cur CURSOR LOCAL FOR
        SELECT UsuarioID FROM inserted;
    OPEN cur;
    FETCH NEXT FROM cur INTO @UsuarioID;
    WHILE @@FETCH_STATUS = 0
    BEGIN
        SELECT @TotalAdopciones = COUNT(*) 
        FROM dbo.Adopcion 
        WHERE UsuarioID = @UsuarioID;

        IF @TotalAdopciones > 3
        BEGIN
            RAISERROR ('Error: Límite de adopciones alcanzado (máximo 3 por usuario).', 16, 1);
            ROLLBACK;
            RETURN;
        END

        FETCH NEXT FROM cur INTO @UsuarioID;
    END;
    CLOSE cur;
    DEALLOCATE cur;
END;
GO
