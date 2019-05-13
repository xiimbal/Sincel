<?php

include_once("ConexionFacturacion.class.php");
include_once("Conexion.class.php");
include_once("ParametroGlobal.class.php");
include_once("Parametros.class.php");
include_once("Catalogo.class.php");
include_once("CatalogoFacturacion.class.php");
include_once("Usuario.class.php");

class ReporteFacturacion {

    private $FechaInicial;
    private $FechaFinal;
    private $PeriodoFacturacion;
    private $RFC = "";
    private $rfccliente;
    private $status;
    private $docto;
    private $folio;
    private $cliente = "";
    private $ejecutivo;  
    private $tipoFactura;
    private $empresa;

    public function getTabla($prefactura) {
        /*Obtenemos el vendedor que se pone por default segun los parametros globales*/
        $parametro = new ParametroGlobal();
        if($parametro->getRegistroById("1")){
            $vendedor = $parametro->getValor();
        }else{
            $vendedor = "";
        }
        
        $parametros = new Parametros();
        if($parametros->getRegistroById("17")){
            $dias_credito = $parametros->getValor();
        }else{
            $dias_credito = "90";
        }
        
        $tiene_filtro = false;
        $mostrar_ndc = true;
        $where = "WHERE";
        
        if (isset($this->RFC) && $this->RFC != "") {
            $tiene_filtro = true;
            $where .= " f.RFCEmisor='" . $this->RFC . "'";            
        }
        
        if(isset($_SESSION['idUsuario'])){//En dado caso que haya un usuario en sesion y que tenga negocios asignados, se filtra solo las facturas de esos negocios
            $usuario = new Usuario();
            $usuario->setId($_SESSION['idUsuario']);

            $clientes_permitidos = $usuario->obtenerRFCNegociosDEUsuario();
            $array_clientes = implode("','", $clientes_permitidos);
            if(!empty($array_clientes)){
                $array_clientes = "'$array_clientes'";    
            }
            
            if(!empty($clientes_permitidos)){
                foreach ($clientes_permitidos as $value) {
                    if ($where != "WHERE") {
                        $where .= " AND f.RFCReceptor IN($array_clientes) ";
                    } else {
                        $where .= " f.RFCReceptor IN($array_clientes) ";
                    } 
                }
                
            }
        }
        
        if (isset($this->FechaInicial) && $this->FechaInicial != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            } else {
                $where .= " f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            }            
        }
        
        if(isset($this->ejecutivo) && $this->ejecutivo != ""){
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.EjecutivoCuenta = $this->ejecutivo ";
            } else {
                $where .= " c.EjecutivoCuenta = $this->ejecutivo ";
            }
        }
        
        if(isset($this->PeriodoFacturacion) && $this->PeriodoFacturacion != ""){
            $year = substr($this->PeriodoFacturacion, 3);
            $mes = substr($this->PeriodoFacturacion, 0, 2);
            $ultimo_dia = cal_days_in_month(CAL_GREGORIAN, $mes, $year);
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.PeriodoFacturacion BETWEEN '$year-$mes-01 00:00:00' AND '$year-$mes-$ultimo_dia 23:59:59'";
            } else {
                $where .= " f.PeriodoFacturacion BETWEEN '$year-$mes-01 00:00:00' AND '$year-$mes-$ultimo_dia 23:59:59'";
            }
        }
        
        if (isset($this->FechaFinal) && $this->FechaFinal != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            } else {
                $where .= " f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            }            
        }
        
        if (isset($this->rfccliente) && $this->rfccliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.RFCReceptor = '" . $this->rfccliente . "'";
            } else {
                $where .= " f.RFCReceptor = '" . $this->rfccliente . "'";
            }            
        }
        
        if (isset($this->cliente) && $this->cliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.RFCReceptor = '" . $this->cliente . "'";
            } else {
                $where .= " f.RFCReceptor = '" . $this->cliente . "'";
            }            
        }
        
        if (isset($this->status)) {
            $tiene_filtro = true;
            $mostrar_ndc = false;            
            if ($where != "WHERE") {
                $where .= " AND( ";
                foreach ($this->status as $value) {
                    if($value == 5){
                        $where .= " (f.PendienteCancelar = 1 AND f.EstadoFactura!=0) OR ";
                    }else if ($value == 3) {
                        $where .= " (f.EstatusFactura = $value AND f.EstadoFactura!=0 AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else {
                        $where .= " (f.EstadoFactura = $value) OR ";
                    }
                }
                if($where != ""){
                    $where = substr($where, 0, strlen($where)-3);
                }
                $where .= ")";
            } else {
                $where .= " ( ";
                foreach ($this->status as $value) {
                    if($value == 5){
                        $where .= " (f.PendienteCancelar = 1 AND f.EstadoFactura!=0) OR ";
                    }else if ($value == 3) {
                        $where .= " (f.EstatusFactura = $value AND f.EstadoFactura!=0 AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else {
                        $where .= " (f.EstadoFactura = $value) OR ";
                    }
                }
                if($where != ""){
                    $where = substr($where, 0, strlen($where)-3);
                }
                $where .= ")";
            }
        }
        
        if (isset($this->folio) && $this->folio != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND CONCAT(f.Serie,f.Folio) = '" . $this->folio . "'";
            } else {
                $where .= " CONCAT(f.Serie,f.Folio) = '" . $this->folio . "'";
            }
        }
        
        if (isset($this->docto) && $this->docto != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.TipoComprobante = '" . $this->docto . "'";
            } else {
                $where .= " f.TipoComprobante = '" . $this->docto . "'";
            }
        }else if(!$mostrar_ndc){
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.TipoComprobante = 'ingreso'";
            } else {
                $where .= " f.TipoComprobante = 'ingreso'";
            }
        }
        
        if(isset($this->tipoFactura)){
            $tiene_filtro = true;            
            if ($where != "WHERE") {            
                $where .= " AND( ";
            }else{
                $where .= " ( ";
            }
            foreach ($this->tipoFactura as $value) {
                if($value == "0"){                    
                    $where .= " ISNULL(f.TipoArrendamiento) OR ";                                   
                }else{                    
                    $where .= " f.TipoArrendamiento = $value OR ";                                    
                }
            }
            
            if(count($this->tipoFactura) > 0){
                $where = substr($where, 0, strlen($where)-3);
            }
            $where .= ")";
        }                

        if ($where == "WHERE") {
            $where = "";
            if (!$prefactura) {
                $where = " WHERE f.Serie <> 'PREF' ";
            }
        } else {
            if (!$prefactura) {
                $where .= " AND f.Serie <> 'PREF' ";
            }
        }
        
        /*Agregamos un having para que no muestre las notas de credito en caso de que sea necesario*/
        $having = "";
        /*if(!$mostrar_ndc){
            $having = " HAVING TipoComprobante <> 'NDC'";f.UsuarioUltimaModificacion,
        }*/        

        $consulta = "SELECT f.Folio,f.UsuarioCreacion as usr,f.UsuarioModificacion as usrm,
            (SELECT CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE '$vendedor' END) AS ejecutivo,
            CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) AS Ejecutivo_atencion,
            (SELECT SUM(pp.ImportePagado) AS suma) AS pagado, 
            (CASE 
            WHEN !ISNULL(c.DiasCredito) AND DATEDIFF(NOW(),f.PeriodoFacturacion) > c.DiasCredito THEN 'red'
            WHEN ISNULL(c.DiasCredito) AND DATEDIFF(NOW(),f.PeriodoFacturacion) > $dias_credito THEN 'red' ELSE 'normal' END) AS color,
                
            (GROUP_CONCAT(CONCAT('',CONCAT('$', FORMAT(pp.ImportePagado, 2)),' [',CAST(DATE(pp.FechaPago) AS CHAR),']  (', pp.Observaciones, ') (',pp.Referencia,') / ',pp.UsuarioCreacion) SEPARATOR ', <br/>')) AS pagos, 
            DATE(f.FechaFacturacion) AS FechaFacturacion,
            DATE(f.PeriodoFacturacion) AS PeriodoFacturacion,
            f.FechaCancelacion,
            f.NombreReceptor,
            f.NombreEmisor,
            f.Serie, f.CFDI33,
            c.ClaveCliente,
            SUBSTRING(f.RFCEmisor,1,3) AS RFCEmisor,
            FORMAT((f.Total/1.16),2) AS subtotal,
            FORMAT(((f.Total/1.16)*.16),2) AS importe,
            FORMAT(f.Total,2) as Total,            
            f.Total AS TotalSinFormato,
            f.RFCReceptor,
            f.IdFactura AS IdFactura,
            f.PathPDF AS PDF,
            f.PathXML AS XML,
            tf.TipoFactura,
            (SELECT GROUP_CONCAT(DISTINCT(fndc2.Folio) SEPARATOR ', ') FROM c_factura AS fndc2 WHERE fndc2.IdFacturaRelacion = f.IdFactura AND fndc2.TipoComprobante = 'egreso'  ) AS PagadoNDC,
            (SELECT CASE WHEN f.TipoComprobante = 'ingreso' THEN 'F' ELSE 'NDC' END) AS TipoComprobante,
            (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
            (SELECT CASE WHEN f.FacturaEnviada = 1 THEN 'Si' ELSE 'No' END) AS Enviado, 
            (SELECT CASE WHEN f.TipoComprobante <> 'ingreso' THEN 'NA' WHEN f.EstadoFactura = 0 THEN 'No' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'No' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'No' ELSE 'Si' END) END) END) AS PagadoSiNo,            
            f.CanceladaSAT as CanceladaSAT,
            (CASE WHEN !ISNULL(pp.IdPagoParcial) THEN (pp.FechaPago) ELSE NULL END) AS FechaPago,
            f.folioFiscal AS FolioFiscal
            FROM c_factura AS f  
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1) 
            LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta 
            LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c.EjecutivoAtencionCliente             
            LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura 
            LEFT JOIN c_tipofacturaexp AS tf ON f.TipoArrendamiento = tf.IdTipoFactura 
            $where GROUP BY f.IdFactura $having ";                         
        if(!$tiene_filtro && $prefactura){/*Si no tiene filtro y se permiten pre-facturas(no es el excel)*/
            $consulta.=" ORDER BY FechaFacturacion DESC LIMIT 0,500";
        }
        //echo $consulta;
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);        
        return $query;
    }

    public function getTablaCXC($prefactura) {
        /*Obtenemos el vendedor que se pone por default segun los parametros globales*/
        
        $parametro = new ParametroGlobal();
        if(isset($this->empresa)){
            $parametro->setEmpresa($this->empresa);
        }
        
        if($parametro->getRegistroById("1")){
            $vendedor = $parametro->getValor();
        }else{
            $vendedor = "";
        }
        
        $parametros = new Parametros();
        if(isset($this->empresa)){
            $parametros->setEmpresa($this->empresa);
        }
        
        if($parametros->getRegistroById("17")){
            $dias_credito = $parametros->getValor();
        }else{
            $dias_credito = "90";
        }
        
        $tiene_filtro = false;
        $mostrar_ndc = true;
        $where = "WHERE";
        
        if (isset($this->RFC) && $this->RFC != "") {
            $tiene_filtro = true;
            $where .= " f.RFCEmisor='" . $this->RFC . "'";            
        }
        
        if (isset($this->FechaInicial) && $this->FechaInicial != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            } else {
                $where .= " f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            }            
        }
        
        if(isset($this->ejecutivo) && $this->ejecutivo != ""){
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.EjecutivoCuenta = $this->ejecutivo ";
            } else {
                $where .= " c.EjecutivoCuenta = $this->ejecutivo ";
            }
        }
        
        if(isset($this->PeriodoFacturacion) && $this->PeriodoFacturacion != ""){
            $year = substr($this->PeriodoFacturacion, 3);
            $mes = substr($this->PeriodoFacturacion, 0, 2);
            $ultimo_dia = cal_days_in_month(CAL_GREGORIAN, $mes, $year);
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.PeriodoFacturacion BETWEEN '$year-$mes-01 00:00:00' AND '$year-$mes-$ultimo_dia 23:59:59'";
            } else {
                $where .= " f.PeriodoFacturacion BETWEEN '$year-$mes-01 00:00:00' AND '$year-$mes-$ultimo_dia 23:59:59'";
            }
        }
        
        if (isset($this->FechaFinal) && $this->FechaFinal != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            } else {
                $where .= " f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            }            
        }
        
        if (isset($this->rfccliente) && $this->rfccliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.RFCReceptor = '" . $this->rfccliente . "'";
            } else {
                $where .= " f.RFCReceptor = '" . $this->rfccliente . "'";
            }            
        }
        
        if (isset($this->cliente) && $this->cliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.RFCReceptor = '" . $this->cliente . "'";
            } else {
                $where .= " f.RFCReceptor = '" . $this->cliente . "'";
            }            
        }
        
        if (isset($this->status)) {
            $tiene_filtro = true;
            $mostrar_ndc = false;            
            if ($where != "WHERE") {
                $where .= " AND( ";
                foreach ($this->status as $value) {
                    if($value == 5){
                        $where .= " (f.PendienteCancelar = 1 AND f.EstadoFactura!=0) OR ";
                    }else if ($value == 3) {
                        $where .= " (f.EstatusFactura = $value AND f.EstadoFactura!=0 AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else {
                        $where .= " (f.EstadoFactura = $value) OR ";
                    }
                }
                if($where != ""){
                    $where = substr($where, 0, strlen($where)-3);
                }
                $where .= ")";
            } else {
                $where .= " ( ";
                foreach ($this->status as $value) {
                    if($value == 5){
                        $where .= " (f.PendienteCancelar = 1 AND f.EstadoFactura!=0) OR ";
                    }else if ($value == 3) {
                        $where .= " (f.EstatusFactura = $value AND f.EstadoFactura!=0 AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else {
                        $where .= " (f.EstadoFactura = $value) OR ";
                    }
                }
                if($where != ""){
                    $where = substr($where, 0, strlen($where)-3);
                }
                $where .= ")";
            }
        }
        
        if (isset($this->folio) && $this->folio != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND CONCAT(f.Serie,f.Folio) = '" . $this->folio . "'";
            } else {
                $where .= " CONCAT(f.Serie,f.Folio) = '" . $this->folio . "'";
            }
        }
        
        if (isset($this->docto) && $this->docto != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.TipoComprobante = '" . $this->docto . "'";
            } else {
                $where .= " f.TipoComprobante = '" . $this->docto . "'";
            }
        }else if(!$mostrar_ndc){
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.TipoComprobante = 'ingreso'";
            } else {
                $where .= " f.TipoComprobante = 'ingreso'";
            }
        }
        
        if(isset($this->tipoFactura)){
            $tiene_filtro = true;            
            if ($where != "WHERE") {            
                $where .= " AND( ";
            }else{
                $where .= " ( ";
            }
            foreach ($this->tipoFactura as $value) {
                if($value == "0"){                    
                    $where .= " ISNULL(f.TipoArrendamiento) OR ";                                   
                }else{                    
                    $where .= " f.TipoArrendamiento = $value OR ";                                    
                }
            }
            
            if(count($this->tipoFactura) > 0){
                $where = substr($where, 0, strlen($where)-3);
            }
            $where .= ")";
        }                

        if ($where == "WHERE") {
            $where = "";
            if (!$prefactura) {
                $where = " WHERE f.Serie <> 'PREF' ";
            }
        } else {
            if (!$prefactura) {
                $where .= " AND f.Serie <> 'PREF' ";
            }
        }
        
        /*Agregamos un having para que no muestre las notas de credito en caso de que sea necesario*/
        $having = "";
        /*if(!$mostrar_ndc){
            $having = " HAVING TipoComprobante <> 'NDC'";
        }*/        

        $consulta = "SELECT f.MetodoPago,f.Folio,
            (SELECT CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE '$vendedor' END) AS ejecutivo,
            CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) AS Ejecutivo_atencion,
            pp.ImportePagado, 
            (CASE 
            WHEN !ISNULL(c.DiasCredito) AND DATEDIFF(NOW(),f.PeriodoFacturacion) > c.DiasCredito THEN 'red'
            WHEN ISNULL(c.DiasCredito) AND DATEDIFF(NOW(),f.PeriodoFacturacion) > $dias_credito THEN 'red' ELSE 'normal' END) AS color,            
            (GROUP_CONCAT(CONCAT('',CONCAT('$', FORMAT(pp.ImportePagado, 2)),' [',CAST(DATE(pp.FechaPago) AS CHAR),']  (', pp.Observaciones, ') (',pp.Referencia,') / ',pp.UsuarioCreacion) SEPARATOR ', <br/>')) AS pagos, 
            DATE(f.FechaFacturacion) AS FechaFacturacion,
            DATE(f.FechaFacturacion) AS Fecha_de_comentario,
            DATE(f.PeriodoFacturacion) AS PeriodoFacturacion,
            f.NombreReceptor,
            f.NombreEmisor,
            f.Serie,
            c.ClaveCliente,
            pp.idCuentaBancaria,
            f.RFCEmisor AS RFCEmisorCompleto,
            SUBSTRING(f.RFCEmisor,1,3) AS RFCEmisor,
            FORMAT((f.Total/1.16),2) AS subtotal,
            FORMAT(((f.Total/1.16)*.16),2) AS importe,
            FORMAT(f.Total,2) as Total,            
            f.Total AS TotalSinFormato,
            f.RFCReceptor,
            f.IdFactura AS IdFactura,
            f.PathPDF AS PDF,
            f.PathXML AS XML,
            tf.TipoFactura,
            pp.UsuarioCreacion,
            pp.Observaciones,
            pp.Referencia,
            DATE(pp.FechaCreacion) AS Fecha_de_captura,
            (SELECT GROUP_CONCAT(DISTINCT(fndc2.Folio) SEPARATOR ', ') FROM c_factura AS fndc2 WHERE fndc2.IdFacturaRelacion = f.IdFactura AND fndc2.TipoComprobante = 'egreso' AND fndc2.Total = pp.ImportePagado) AS PagadoNDC,
            (SELECT CASE WHEN f.TipoComprobante = 'ingreso' THEN 'F' ELSE 'NDC' END) AS TipoComprobante,
            (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
            (SELECT CASE WHEN f.FacturaEnviada = 1 THEN 'Si' ELSE 'No' END) AS Enviado, 
            (SELECT CASE WHEN f.TipoComprobante <> 'ingreso' THEN 'NA' WHEN f.EstadoFactura = 0 THEN 'No' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'No' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'No' ELSE 'Si' END) END) END) AS PagadoSiNo,            
            f.CanceladaSAT as CanceladaSAT,
            (CASE WHEN !ISNULL(pp.IdPagoParcial) THEN (pp.FechaPago) ELSE NULL END) AS FechaPago,
            f.folioFiscal AS FolioFiscal
            FROM c_factura AS f  
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1) 
            LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta 
            LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c.EjecutivoAtencionCliente             
            LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura 
            LEFT JOIN c_tipofacturaexp AS tf ON f.TipoArrendamiento = tf.IdTipoFactura 
            $where GROUP BY f.IdFactura, pp.IdPagoParcial $having ";                         
        if(!$tiene_filtro && $prefactura){/*Si no tiene filtro y se permiten pre-facturas(no es el excel)*/
            $consulta.=" ORDER BY FechaFacturacion DESC LIMIT 0,500";
        }
        $catalogo = new CatalogoFacturacion();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);        
        return $query;
    }
    
    public function getTablaNotaRemision() {
        /*Obtenemos el vendedor que se pone por default segun los parametros globales*/
        $parametro = new ParametroGlobal();
        if($parametro->getRegistroById("1")){
            $vendedor = $parametro->getValor();
        }else{
            $vendedor = "";
        }
        
        $parametros = new Parametros();
        if($parametros->getRegistroById("17")){
            $dias_credito = $parametros->getValor();
        }else{
            $dias_credito = "90";
        }
        
        $tiene_filtro = false;
        $mostrar_ndc = true;
        $where = "WHERE";
        
        $tiene_filtro = true;
        $where .= " f.TipoComprobante = 'ingreso' AND Generada = 0";
        
        if (isset($this->RFC) && $this->RFC != "") {
            $tiene_filtro = true;
            $where .= " AND dfe.RFC='" . $this->RFC . "'";            
        }
        
        if(isset($_SESSION['idUsuario'])){//En dado caso que haya un usuario en sesion y que tenga negocios asignados, se filtra solo las facturas de esos negocios
            $usuario = new Usuario();
            $usuario->setId($_SESSION['idUsuario']);

            $clientes_permitidos = $usuario->obtenerRFCNegociosDEUsuario();
            $array_clientes = implode("','", $clientes_permitidos);
            if(!empty($array_clientes)){
                $array_clientes = "'$array_clientes'";    
            }
            
            if(!empty($clientes_permitidos)){
                foreach ($clientes_permitidos as $value) {
                    if ($where != "WHERE") {
                        $where .= " AND c.RFC IN($array_clientes) ";
                    } else {
                        $where .= " c.RFC IN($array_clientes) ";
                    } 
                }
                
            }
        }
        
        if (isset($this->FechaInicial) && $this->FechaInicial != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            } else {
                $where .= " f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            }            
        }
        
        if(isset($this->ejecutivo) && $this->ejecutivo != ""){
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.EjecutivoCuenta = $this->ejecutivo ";
            } else {
                $where .= " c.EjecutivoCuenta = $this->ejecutivo ";
            }
        }
        
        if (isset($this->FechaFinal) && $this->FechaFinal != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            } else {
                $where .= " f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            }            
        }
        
        if (isset($this->rfccliente) && $this->rfccliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.RFC = '" . $this->rfccliente . "'";
            } else {
                $where .= " c.RFC  = '" . $this->rfccliente . "'";
            }            
        }
        
        if (isset($this->cliente) && $this->cliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.RFC = '" . $this->cliente . "'";
            } else {
                $where .= " c.RFC = '" . $this->cliente . "'";
            }            
        }
        
        if (isset($this->status)) {
            $tiene_filtro = true;
            $mostrar_ndc = false;            
            if ($where != "WHERE") {
                $where .= " AND( ";
                foreach ($this->status as $value) {
                    if ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else if ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } 
                }
                if($where != ""){
                    $where = substr($where, 0, strlen($where)-3);
                }
                $where .= ")";
            } else {
                $where .= " ( ";
                foreach ($this->status as $value) {
                    if ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else if ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    }
                }
                if($where != ""){
                    $where = substr($where, 0, strlen($where)-3);
                }
                $where .= ")";
            }
        }
        
        if (isset($this->folio) && $this->folio != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND CONCAT(f.Folio) = '" . $this->folio . "'";
            } else {
                $where .= " CONCAT(f.Folio) = '" . $this->folio . "'";
            }
        }
        
        if(isset($this->tipoFactura)){
            $tiene_filtro = true;            
            if ($where != "WHERE") {            
                $where .= " AND( ";
            }else{
                $where .= " ( ";
            }
            foreach ($this->tipoFactura as $value) {
                if($value == "0"){                    
                    $where .= " ISNULL(f.TipoArrendamiento) OR ";                                   
                }else{                    
                    $where .= " f.TipoArrendamiento = $value OR ";                                    
                }
            }
            
            if(count($this->tipoFactura) > 0){
                $where = substr($where, 0, strlen($where)-3);
            }
            $where .= ")";
        }                

        if ($where == "WHERE") {
            $where = "";
            if (!$prefactura) {
                $where = " WHERE (s.Prefijo <> 'PREF' OR ISNULL(s.Prefijo)) ";
            }
        } else {
            if (!$prefactura) {
                $where .= " AND (s.Prefijo <> 'PREF' OR ISNULL(s.Prefijo)) ";
            }
        }
        
        /*Agregamos un having para que no muestre las notas de credito en caso de que sea necesario*/
        $having = ""; 
        /*if(!$mostrar_ndc){
            $having = " HAVING TipoComprobante <> 'NDC'";
        }*/        

        $consulta = "SELECT f.Folio,
            (SELECT CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE '$vendedor' END) AS ejecutivo,
            CONCAT(u2.Nombre,' ',u2.ApellidoPaterno,' ',u2.ApellidoMaterno) AS Ejecutivo_atencion,
            (SELECT SUM(pp.importe) AS suma) AS pagado, 
            (CASE 
            WHEN !ISNULL(c.DiasCredito) AND DATEDIFF(NOW(),CAST(DATE_FORMAT(f.FechaFacturacion ,'%Y-%m-01') AS DATE)) > c.DiasCredito THEN 'red'
            WHEN ISNULL(c.DiasCredito) AND DATEDIFF(NOW(),CAST(DATE_FORMAT(f.FechaFacturacion ,'%Y-%m-01') AS DATE)) > $dias_credito THEN 'red' ELSE 'normal' END) AS color,
            (GROUP_CONCAT(CONCAT('',CONCAT('$', FORMAT(pp.importe, 2)),' [',CAST(DATE(pp.FechaPago) AS CHAR),']  (', pp.Observaciones, ') (',pp.Referencia,') / ',pp.UsuarioCreacion) SEPARATOR ', <br/>')) AS pagos, 
            DATE(f.FechaFacturacion) AS FechaFacturacion,
            DATE(CAST(DATE_FORMAT(f.FechaFacturacion ,'%Y-%m-01') AS DATE)) AS PeriodoFacturacion,
            c.NombreRazonSocial AS NombreReceptor,
            dfe.RazonSocial AS NombreEmisor,
            s.Prefijo AS Serie, f.CFDI33,
            c.ClaveCliente,
            SUBSTRING(dfe.RFC,1,3) AS RFCEmisor,
            FORMAT((f.Total/1.16),2) AS subtotal,
            FORMAT(((f.Total/1.16)*0.16),2) AS importe,
            FORMAT(f.Total,2) as Total,            
            f.Total AS TotalSinFormato,
            c.RFC AS RFCReceptor,
            f.IdFactura AS IdFactura,
            f.PathPDF AS PDF,
            f.PathXML AS XML,
            tf.TipoFactura,
            (SELECT CASE WHEN f.TipoComprobante = 'ingreso' THEN 'F' ELSE 'NDC' END) AS TipoComprobante,
            (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
            (SELECT CASE WHEN f.FacturaEnviada = 1 THEN 'Si' ELSE 'No' END) AS Enviado, 
            (SELECT CASE WHEN f.TipoComprobante <> 'ingreso' THEN 'NA' WHEN f.EstadoFactura = 0 THEN 'No' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'No' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'No' ELSE 'Si' END) END) END) AS PagadoSiNo,            
            f.CanceladaSAT as CanceladaSAT,
            (CASE WHEN !ISNULL(pp.id_pago) THEN (pp.fechapago) ELSE NULL END) AS FechaPago,
            f.folioFiscal AS FolioFiscal
            FROM c_factura AS f  
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = f.RFCReceptor 
            LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = f.RFCEmisor
            LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta 
            LEFT JOIN c_usuario AS u2 ON u2.IdUsuario = c.EjecutivoAtencionCliente             
            LEFT JOIN c_pagosparciales AS pp ON pp.id_factura = f.IdFactura 
            LEFT JOIN c_tipofacturaexp AS tf ON f.TipoArrendamiento = tf.IdTipoFactura 
            LEFT JOIN c_serie AS s ON s.IdSerie = f.IdSerie
            $where GROUP BY f.IdFactura $having ";                         
        if(!$tiene_filtro && $prefactura){/*Si no tiene filtro y se permiten pre-facturas(no es el excel)*/
            $consulta.=" ORDER BY FechaFacturacion DESC LIMIT 0,500";
        }
        //echo $consulta;
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);        
        return $query;
    }
    
    public function CambiarPagado($tipo) {        
        $consulta = ("UPDATE c_factura SET FacturaPagada=" . $tipo . " WHERE Folio='" . $this->folio."'");
        $catalogo = new CatalogoFacturacion(); $query = new $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function obtenerXML() {
        $consulta = ("SELECT FacturaXML FROM c_factura WHERE Folio='" . $this->folio."'");
        $catalogo = new CatalogoFacturacion(); $query = new $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function obtenerXMLCancelada() {
        $consulta = ("SELECT CanceladaSAT FROM c_factura WHERE Folio='" . $this->folio."'");
        $catalogo = new CatalogoFacturacion(); $query = new $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
    }

    public function getDocto() {
        return $this->docto;
    }

    public function setDocto($docto) {
        $this->docto = $docto;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getRfccliente() {
        return $this->rfccliente;
    }

    public function setRfccliente($rfccliente) {
        $this->rfccliente = $rfccliente;
    }

    public function getVendedor() {
        return $this->vendedor;
    }

    public function setVendedor($vendedor) {
        $this->vendedor = $vendedor;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function getFechaInicial() {
        return $this->FechaInicial;
    }

    public function setFechaInicial($FechaInicial) {
        $this->FechaInicial = $FechaInicial;
    }

    public function getFechaFinal() {
        return $this->FechaFinal;
    }

    public function setFechaFinal($FechaFinal) {
        $this->FechaFinal = $FechaFinal;
    }

    public function getRFC() {
        return $this->RFC;
    }

    public function setRFC($RFC) {
        $this->RFC = $RFC;
    }

    public function getEjecutivo() {
        return $this->ejecutivo;
    }

    public function setEjecutivo($ejecutivo) {
        $this->ejecutivo = $ejecutivo;
    }
    
    public function getTipoFactura() {
        return $this->tipoFactura;
    }

    public function setTipoFactura($tipoFactura) {
        $this->tipoFactura = $tipoFactura;
    }
    
    public function getPeriodoFacturacion() {
        return $this->PeriodoFacturacion;
    }

    public function setPeriodoFacturacion($PeriodoFacturacion) {
        $this->PeriodoFacturacion = $PeriodoFacturacion;
    }
    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
