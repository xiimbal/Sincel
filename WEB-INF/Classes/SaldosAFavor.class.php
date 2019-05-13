<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
include_once("CatalogoFacturacion.class.php");

class SaldosAFavor {
    private $RFC;
    private $IdPagoParcial;
    private $Cantidad;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $mensajes;
    private $idFactura;
    
    public function nuevoRegistro(){
        $consulta = "INSERT INTO c_saldosAFavor(RFCCliente,IdPagoParcial,Cantidad,UsuarioCreacion,FechaCreacion,
                UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES('$this->RFC',
                $this->IdPagoParcial, $this->Cantidad, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion',
                NOW(), '$this->Pantalla')";
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function nuevoRegistro2(){
        $consulta = "INSERT INTO c_multi_saldosAFavor(RFCCliente,IdMPagoParcial,Cantidad,UsuarioCreacion,FechaCreacion,
                UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES('$this->RFC',
                $this->IdPagoParcial, $this->Cantidad, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion',
                NOW(), '$this->Pantalla')";
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getSaldoByCliente(){
        $cantidadTotal = 0;
        $consulta = "SELECT Cantidad FROM c_saldosAFavor saf WHERE saf.RFCCliente = '$this->RFC' AND Cantidad != 0";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $cantidadTotal += $rs['Cantidad'];
        }
        return $cantidadTotal;
    }

    public function getSaldoByCliente2(){
        $cantidadTotal = 0;
        $consulta = "SELECT Cantidad FROM c_multi_saldosAFavor saf WHERE saf.RFCCliente = '$this->RFC' AND Cantidad != 0";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $cantidadTotal += $rs['Cantidad'];
        }
        return $cantidadTotal;
    }
    
    public function getSaldoByIdPago($idPago){
        $cantidadTotal = 0;
        $consulta = "SELECT Cantidad FROM c_saldosAFavor saf WHERE saf.IdPagoParcial = '$idPago' AND saf.Cantidad != 0";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        if($rs = mysql_fetch_array($result)){
            $cantidadTotal = $rs['Cantidad'];
        }
        return $cantidadTotal;
    }

    public function restarCantidadDeSaldos($cantidadARestar){
        $pagos = array();
        $consulta = "SELECT Cantidad, IdPagoParcial FROM c_saldosAFavor WHERE Cantidad != 0 AND RFCCliente = '$this->RFC'";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $pagos[$rs['IdPagoParcial']] = $rs['Cantidad']; 
        }
        foreach($pagos as $clave => $valor){
            $respaldo = $cantidadARestar;
            $cantidadARestar = $cantidadARestar - $valor;
            if($cantidadARestar >= 0){
                $consulta = "UPDATE c_saldosAFavor SET Cantidad = 0 WHERE RFCCliente = '$this->RFC'
                            AND IdPagoParcial = $clave";
                $consulta2 = "INSERT INTO c_log_saldos(IdPagoParcial,IdPagoParcialUsado, Cantidad, UsuarioCreacion, FechaCreacion,
                        UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES ($clave, $this->IdPagoParcial,
                        $valor, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla')";
                $consulta3 = "INSERT INTO c_pagosparciales(IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, idCuentaBancaria,
                        Referencia, Observaciones, UsuarioCreacion, FechaCreacion) SELECT * FROM ((SELECT IdFactura FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS a, (SELECT Folio FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS b,
                        (SELECT $valor * -1) AS c,(SELECT (SELECT ImportePorPagar FROM c_pagosparciales WHERE IdPagoParcial = $clave) + $valor) AS d, (SELECT NOW()) AS e, (SELECT NULL) AS f ,
                        (SELECT CONCAT((SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura),' ')) AS g, (SELECT CONCAT('Saldo usado en la factura: ',(SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura))) AS h, (SELECT '$this->UsuarioCreacion') AS i, (SELECT NOW()) AS j)";
            }else{
                $consulta = "UPDATE c_saldosAFavor SET Cantidad = Cantidad - $respaldo WHERE RFCCliente = '$this->RFC'
                            AND IdPagoParcial = $clave";
                $consulta2 = "INSERT INTO c_log_saldos(IdPagoParcial,IdPagoParcialUsado, Cantidad, UsuarioCreacion, FechaCreacion,
                        UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES ($clave, $this->IdPagoParcial,
                        $respaldo, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla')";
                $consulta3 = "INSERT INTO c_pagosparciales(IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, idCuentaBancaria,
                        Referencia, Observaciones, UsuarioCreacion, FechaCreacion) SELECT * FROM ((SELECT IdFactura FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS a, (SELECT Folio FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS b,
                        (SELECT $respaldo * -1) AS c,(SELECT (SELECT ImportePorPagar FROM c_pagosparciales WHERE IdPagoParcial = $clave) + $respaldo) AS d, (SELECT NOW()) AS e, (SELECT NULL) AS f ,
                        (SELECT CONCAT((SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura),' ')) AS g, (SELECT CONCAT('Saldo usado en la factura: ',(SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura))) AS h, (SELECT '$this->UsuarioCreacion') AS i, (SELECT NOW()) AS j)";
            }
            $result2 = $catalogo->obtenerLista($consulta);
            $result3 = $catalogo->obtenerLista($consulta2);
            $result4 = $catalogo->obtenerLista($consulta3);
            if($cantidadARestar <= 0){
                break;
            }
        }
    }

    public function restarCantidadDeSaldos2($cantidadARestar){
        $pagos = array();
        $consulta = "SELECT Cantidad, IdMPagoParcial FROM c_multi_saldosAFavor WHERE Cantidad != 0 AND RFCCliente = '$this->RFC'";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $pagos[$rs['IdMPagoParcial']] = $rs['Cantidad']; 
        }
        foreach($pagos as $clave => $valor){
            $respaldo = $cantidadARestar;
            $cantidadARestar = $cantidadARestar - $valor;
            if($cantidadARestar >= 0){
                $consulta = "UPDATE c_multi_saldosAFavor SET Cantidad = 0 WHERE RFCCliente = '$this->RFC'
                            AND IdMPagoParcial = $clave";
                $consulta2 = "INSERT INTO c_log_multi_saldos(IdMPagoParcial,IdMPagoParcialUsado, Cantidad, UsuarioCreacion, FechaCreacion,
                        UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES ($clave, $this->IdPagoParcial,
                        $valor, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla')";
                $consulta3 = "INSERT INTO c_pagosparciales(IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, idCuentaBancaria,
                        Referencia, Observaciones, UsuarioCreacion, FechaCreacion) SELECT * FROM ((SELECT IdFactura FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS a, (SELECT Folio FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS b,
                        (SELECT $valor * -1) AS c,(SELECT (SELECT ImportePorPagar FROM c_pagosparciales WHERE IdPagoParcial = $clave) + $valor) AS d, (SELECT NOW()) AS e, (SELECT NULL) AS f ,
                        (SELECT CONCAT((SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura),' ')) AS g, (SELECT CONCAT('Saldo usado en la factura: ',(SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura))) AS h, (SELECT '$this->UsuarioCreacion') AS i, (SELECT NOW()) AS j)";
            }else{
                $consulta = "UPDATE c_multi_saldosAFavor SET Cantidad = Cantidad - $respaldo WHERE RFCCliente = '$this->RFC'
                            AND IdMPagoParcial = $clave";
                $consulta2 = "INSERT INTO c_log_multi_saldos(IdMPagoParcial,IdMPagoParcialUsado, Cantidad, UsuarioCreacion, FechaCreacion,
                        UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES ($clave, $this->IdPagoParcial,
                        $respaldo, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla')";
                $consulta3 = "INSERT INTO c_pagosparciales(IdFactura, Folio, ImportePagado, ImportePorPagar, FechaPago, idCuentaBancaria,
                        Referencia, Observaciones, UsuarioCreacion, FechaCreacion) SELECT * FROM ((SELECT IdFactura FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS a, (SELECT Folio FROM c_pagosparciales WHERE IdPagoParcial = $clave) AS b,
                        (SELECT $respaldo * -1) AS c,(SELECT (SELECT ImportePorPagar FROM c_pagosparciales WHERE IdPagoParcial = $clave) + $respaldo) AS d, (SELECT NOW()) AS e, (SELECT NULL) AS f ,
                        (SELECT CONCAT((SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura),' ')) AS g, (SELECT CONCAT('Saldo usado en la factura: ',(SELECT Folio FROM c_factura WHERE IdFactura = $this->idFactura))) AS h, (SELECT '$this->UsuarioCreacion') AS i, (SELECT NOW()) AS j)";
            }
            $result2 = $catalogo->obtenerLista($consulta);
            $result3 = $catalogo->obtenerLista($consulta2);
            $result4 = $catalogo->obtenerLista($consulta3);
            if($cantidadARestar <= 0){
                break;
            }
        }
    }
    
    public function restaurarDatosByPago(){
        $pagos = array();
        //Primero regresamos los valores del log a la tabla de saldos a Favor
        $consulta = "SELECT IdPagoParcial, Cantidad FROM c_log_saldos WHERE IdPagoParcialUsado = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $pagos[$rs['IdPagoParcial']] = $rs['Cantidad']; 
        }
        foreach($pagos as $clave => $valor){
            $consulta2 = "UPDATE c_saldosAFavor SET Cantidad = Cantidad + $valor WHERE IdPagoParcial = $clave";
            $result2 = $catalogo->obtenerLista($consulta2);
            $consulta3 = "DELETE FROM c_pagosparciales
                    WHERE IdFactura = (SELECT * FROM ((SELECT p.IdFactura FROM c_pagosparciales p WHERE p.IdPagoParcial = $clave)AS p))
                    AND ImportePagado = $valor*-1";
            $result3 = $catalogo->obtenerLista($consulta3);
        }
        //Ahora que se restauro la cantidad, vamos a eliminar estos registros del log
        $consulta = "DELETE FROM c_log_saldos WHERE IdPagoParcialUsado = $this->IdPagoParcial";
        $result = $catalogo->obtenerLista($consulta);
    }
    
    public function restaurarDatosByPago2(){
        $pagos = array();
        //Primero regresamos los valores del log a la tabla de saldos a Favor
        $consulta = "SELECT IdMPagoParcial, Cantidad FROM c_log_multi_saldos WHERE IdMPagoParcialUsado = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $pagos[$rs['IdMPagoParcial']] = $rs['Cantidad']; 
        }
        foreach($pagos as $clave => $valor){
            $consulta2 = "UPDATE c_multi_saldosAFavor SET Cantidad = Cantidad + $valor WHERE IdMPagoParcial = $clave";
            $result2 = $catalogo->obtenerLista($consulta2);
            $consulta3 = "DELETE FROM c_multipagosparciales
                    WHERE IdFactura = (SELECT * FROM ((SELECT p.IdFactura FROM c_pagosparciales p WHERE p.IdPagoParcial = $clave)AS p))
                    AND ImportePagado = $valor*-1";
            $result3 = $catalogo->obtenerLista($consulta3);
        }
        //Ahora que se restauro la cantidad, vamos a eliminar estos registros del log
        $consulta = "DELETE FROM c_log_saldos WHERE IdPagoParcialUsado = $this->IdPagoParcial";
        $result = $catalogo->obtenerLista($consulta);
    }

    public function obtenerPagadoConSaldoAFavorPorPago(){
        $consulta = "SELECT SUM(Cantidad) AS Saldo FROM c_saldosAFavor WHERE IdPagoParcial = $this->IdPagoParcial";
        //$consulta = "SELECT SUM(Cantidad) AS Saldo FROM c_saldosAFavor WHERE IdMPagoParcial = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        if($rs = mysql_fetch_array($result)){
            return $rs['Saldo'];
        }
        return 0;
    }
    public function obtenerPagadoConSaldoAFavorPorPago2(){
        //$consulta = "SELECT SUM(Cantidad) AS Saldo FROM c_saldosAFavor WHERE IdPagoParcial = $this->IdPagoParcial";
        $consulta = "SELECT SUM(Cantidad) AS Saldo FROM c_multi_saldosAFavor WHERE IdMPagoParcial = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        if($rs = mysql_fetch_array($result)){
            return $rs['Saldo'];
        }
        return 0;
    }
    
    public function verificarSaldoAFavorUsado(){
        $return = false;
        $consulta = "SELECT * FROM c_log_saldos WHERE IdPagoParcial = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $return = true;
            $this->mensajes .= $rs['IdPagoParcialUsado'].",";
        }
        return $return;
    }

    public function verificarSaldoAFavorUsado2(){
        $return = false;
        $consulta = "SELECT * FROM c_log_multi_saldos WHERE IdMPagoParcial = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $return = true;
            $this->mensajes .= $rs['IdMPagoParcialUsado'].",";
        }
        return $return;
    }
    
    public function eliminarSaldoAFavorByPago(){
        $consulta = "DELETE FROM c_saldosAFavor WHERE IdPagoParcial = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function eliminarSaldoAFavorByPago2(){
        $consulta = "DELETE FROM c_multi_saldosAFavor WHERE IdMPagoParcial = $this->IdPagoParcial";
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function getIdPagoParcial() {
        return $this->IdPagoParcial;
    }

    function getCantidad() {
        return $this->Cantidad;
    }

    function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    function getPantalla() {
        return $this->Pantalla;
    }

    function getRFC() {
        return $this->RFC;
    }

    function setRFC($RFC) {
        $this->RFC = $RFC;
    }
    
    function setIdPagoParcial($IdPagoParcial) {
        $this->IdPagoParcial = $IdPagoParcial;
    }

    function setCantidad($Cantidad) {
        $this->Cantidad = $Cantidad;
    }

    function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    function getMensajes() {
        return $this->mensajes;
    }
    
    function getIdFactura() {
        return $this->idFactura;
    }

    function setIdFactura($idFactura) {
        $this->idFactura = $idFactura;
    }

}
