-- CONSULTA QUE CARGA LOS DATOS NECESARIOS EN LA TABLA DE LA INTERFAZ "cargar_camion_log.php"
DELIMITER $$
DROP PROCEDURE IF EXISTS SELECT_LOGISTICA$$
CREATE PROCEDURE SELECT_LOGISTICA()
BEGIN
    SELECT c_logistica.fecha, c_logistica.ruta, c_logistica.nombre_destino, c_logistica.pedido, c_logistica.etiqueta, c_logistica.clave_embarque, c_logistica.cv, c_logistica.suma_piezas, c_logistica.piezas_encamion, c_logistica.piezas_entregadas, c_estado.Nombre  
    FROM c_logistica 
    INNER JOIN c_estado
    ON c_estado.IDEstado = c_logistica.estatus
    WHERE c_logistica.estatus = 303 AND c_logistica.Activo = 1;
END$$
DELIMITER ;


-- CREACION DE PROCEDIMIENTO ALMACENADO QUE BUSCA POR CLAVE DE EMBARQUE "asignacion_chofer.php"
DELIMITER $$
DROP PROCEDURE IF EXISTS SELECT_CLAVES_VEHICULARES$$
CREATE PROCEDURE SELECT_CLAVES_VEHICULARES(fecha_embarque date)
BEGIN
    SELECT * FROM c_logistica
    WHERE c_logistica.estatus = 303 AND c_logistica.fecha = fecha_embarque GROUP BY c_logistica.CV;
END$$
DELIMITER ;