<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
include_once("CatalogoFacturacion.class.php");
include_once("Factura3.class.php");
include_once("ReporteFacturacion.class.php");
include_once("Cliente.class.php");
include_once("ccliente.class.php");
include_once("Parametros.class.php");
include_once("Empresa.class.php");

class PagoParcial {

    private $id_pago;
    private $id_factura;
    private $referencia;
    private $observaciones;
    private $importe;
    private $fechapago;
    private $PathXML;
    private $PathPDF;
    private $FolioFiscal;
    private $FechaTimbrado;
    private $cuentaBancaria;
    private $Folio;
    private $IdSerie;
    private $IdFormaPago;
    private $IdTipoCadena;
    private $CertPago;
    private $CadPago;
    private $SelloPago;
    private $idEmpresaFactura;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $importeExtra;
    private $NumParcialidad;
    private $ImpSaldoAnt;
    private $RFCBancoEmisorOrd;
    private $NomBancoEmisorOrd;
    private $CtaOrdenante;
    private $empresa;
    private $Estado;
    private $FormaDePagoP = "";

    private $Contador=0;      //Variable que se opcupa para la funcion "getRegistrosPagoParciaol" *JT 04/10/18
    private $SaldoAnteriorE;
    private $TotalPagarE;

    private $IdSeriePPA;        //variables que se ocupa para la funcion "getRegistrosPPAnteriores"     *JT 10/10/18
    private $ObservacionesPPA;

    public function getCanceladas(){
        //***********************************************************************************************  *JT 18/10/18
         $consulta = "SELECT pp.FechaCreacion,
            (SELECT CASE WHEN pp.FechaCreacion<'2018-09-18 11:07:20' THEN CONCAT(cse.Prefijo,pp.Folio) WHEN pp.FechaCreacion>'2018-09-18 11:07:20' THEN CONCAT(cs.Prefijo,pp.Folio) END) AS UNI,
            f.IdFactura,f.Folio,pp.IdPagoParcial AS id_pago,DATE(pp.FechaPago) AS fechapago,pp.IdFormaPago,                
                (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
                CONCAT('$', FORMAT(pp.ImportePagado, 2)) AS importe,pp.Observaciones AS observaciones,pp.Referencia AS referencia,c.NombreRazonSocial,
                pp.FolioFiscal, pp.PathPDF, pp.PathXML
                FROM c_factura AS f 
                INNER JOIN c_pagosparcialescancelados AS pp ON pp.IdFactura = f.IdFactura 
            LEFT JOIN c_serie AS cse On cse.IdSerie = pp.IdSerie 
            LEFT JOIN c_seriepago AS cs On cs.IdSerie = pp.IdSerie 
                LEFT JOIN c_cliente as c ON c.RFC = (SELECT MIN(RFC) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1)
                WHERE f.IdFactura='$this->id_factura' GROUP BY pp.IdPagoParcial;";
         //***********************************************************************************************************************
            $catalogo = new CatalogoFacturacion();
        //echo $consulta;
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    public function CancelarPago(){
        $catalogo = new CatalogoFacturacion();
      //$factura = new Factura_NET();
      $factura = new Factura_NET3();
            $ImportePorPagar = "null";
            if ($factura->getRegistroById($this->id_factura)) {
                $ImportePorPagar = (float) $factura->getTotal() - (float) $this->importe;
            }
            if (!isset($this->importe) || $this->importe == "") {
                $this->importe = "null";
            }
            if (!isset($this->cuentaBancaria) || $this->cuentaBancaria == "") {
                $this->cuentaBancaria = "null";
            }
            if (!isset($this->IdTipoCadena) || empty($this->IdTipoCadena)) {
                $this->IdTipoCadena = "null";
            }
            
            if (!isset($this->IdFormaPago) || empty($this->IdFormaPago)) {
                $this->IdFormaPago = "0";
            }

            if ($this->fechapago != "NOW()") {
                $this->fechapago = "'$this->fechapago'";
            }

            //$this->Folio = "1";

            if (!isset($this->IdSerie) || empty($this->IdSerie)) {
                $this->IdSerie = "null";
            }
//                $consulta = "SELECT (UltimoFolioPago + 1) AS FolioPago FROM c_folio WHERE idEmpresa = $this->idEmpresaFactura AND Activo = 1 LIMIT 1;";
//            } else {
//                $consulta = "SELECT (FolioPago + 1) AS FolioPago FROM c_serie WHERE IdSerie = $this->IdSerie;";
//            }
//            
//            $result = $catalogo->obtenerLista($consulta);
//            while ($rs = mysql_fetch_array($result)) {
//                $this->Folio = $rs['FolioPago'];
//            }
            //***************************************************************************** Modificacion de codigo  *JT 17/10/18
            $consulta = "INSERT INTO c_pagosparcialescancelados(IdFactura,FolioFiscal,PathXML,PathPDF,FechaTimbrado,
                Referencia,Observaciones,ImportePagado,ImportePorPagar,FechaPago,idCuentaBancaria, IdFormaPago,IdTipoCadena,CertPago,CadPago,
                SelloPago,IdSerie,Folio,UsuarioCreacion,FechaCreacion)
                VALUES('".$this->id_factura . "','".$this->FolioFiscal."','".$this->PathXML."','".$this->PathPDF."','".$this->FechaTimbrado."','" . $this->referencia . "','" . $this->observaciones . "','" . $this->importe . "',$ImportePorPagar," .
                    $this->fechapago . "," . $this->cuentaBancaria . ",$this->IdFormaPago,$this->IdTipoCadena,'$this->CertPago','$this->CadPago',"
                    . "'$this->SelloPago',$this->IdSerie,'$this->Folio','" . $this->UsuarioCreacion . "',NOW());";
          //*****************************************************************************************************************
          //echo "<br> ".$consulta;

            $pagoBorrar = $this->id_pago;
        $this->id_pago = $catalogo->insertarRegistro($consulta);
           
        if ($this->id_pago != NULL && $this->id_pago != 0) {
            $delete = "DELETE FROM c_pagosparciales WHERE IdPagoParcial = $pagoBorrar;";
            $catalogo->obtenerLista($delete);
            $update = "UPDATE c_factura SET FacturaPagada = 0 WHERE IdFactura = $this->id_factura;";
            $catalogo->obtenerLista($update);
            return true;
        }
        return false;
    }
    
    public function getTabla($net) {
        if (!$net) {
            $consulta = "SELECT f.IdFactura,f.Folio,pp.id_pago,DATE(pp.fechapago) AS fechapago, pp.idCuentaBancaria,
                CONCAT('$', FORMAT(pp.importe, 2)) AS importe,pp.observaciones,pp.referencia,c.NombreRazonSocial,
                pp.FolioFiscal, pp.PathPDF, pp.PathXML,pp.UsuarioCreacion,pp.FechaCreacion
                FROM c_factura AS f 
                INNER JOIN c_pagosparciales AS pp ON pp.id_factura=f.IdFactura 
                LEFT JOIN c_cliente as c ON c.ClaveCliente=f.RFCReceptor 
                WHERE f.IdFactura='$this->id_factura';";
            $catalogo = new Catalogo();
        } else {
           $consulta ="SELECT pp.FechaCreacion,
            (SELECT CASE WHEN pp.FechaCreacion<'2018-09-18 11:07:20' THEN CONCAT(cse.Prefijo,pp.Folio) WHEN pp.FechaCreacion>'2018-09-18 11:07:20' THEN CONCAT(cs.Prefijo,pp.Folio) END) AS UNI,
            f.IdFactura,f.Folio,pp.IdPagoParcial AS id_pago,DATE(pp.FechaPago) AS fechapago,pp.IdFormaPago,                
                (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
                CONCAT('$', FORMAT(pp.ImportePagado, 2)) AS importe,pp.Observaciones AS observaciones,pp.Referencia AS referencia,c.NombreRazonSocial,
            pp.FolioFiscal, pp.PathPDF, pp.PathXML,pp.PathPDFPre    
            FROM c_factura AS f 
            INNER JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura
            LEFT JOIN c_serie AS cse On cse.IdSerie = pp.IdSerie 
            LEFT JOIN c_seriepago AS cs On cs.IdSerie = pp.IdSerie 
            LEFT JOIN c_cliente as c ON c.RFC = (SELECT MIN(RFC) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1)
            WHERE f.IdFactura='$this->id_factura' GROUP BY pp.IdPagoParcial;";

        
            /*$consulta ="SELECT CONCAT(cs.Prefijo,pp.Folio),f.IdFactura,f.Folio,pp.IdPagoParcial AS id_pago,DATE(pp.FechaPago) AS fechapago,pp.IdFormaPago,
                (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
                CONCAT('$', FORMAT(pp.ImportePagado, 2)) AS importe,pp.Observaciones AS observaciones,pp.Referencia AS referencia,c.NombreRazonSocial,
                pp.FolioFiscal, pp.PathPDF, pp.PathXML,pp.PathPDFPre
                FROM c_factura AS f 
                INNER JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura
                INNER JOIN c_seriepago AS cs On cs.idSerie = pp.IdSerie
                LEFT JOIN c_cliente as c ON c.RFC = (SELECT MIN(RFC) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1)
                WHERE f.IdFactura='$this->id_factura' GROUP BY pp.IdPagoParcial;";
            
            /*$consulta = "SELECT f.IdFactura,f.Folio,pp.IdPagoParcial AS id_pago,DATE(pp.FechaPago) AS fechapago,pp.IdFormaPago,
                (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
                CONCAT('$', FORMAT(pp.ImportePagado, 2)) AS importe,pp.Observaciones AS observaciones,pp.Referencia AS referencia,c.NombreRazonSocial,
                pp.FolioFiscal, pp.PathPDF, pp.PathXML
                FROM c_factura AS f 
                INNER JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura 
                LEFT JOIN c_cliente as c ON c.RFC = (SELECT MIN(RFC) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1)
                WHERE f.IdFactura='$this->id_factura' GROUP BY pp.IdPagoParcial;";*/
            $catalogo = new CatalogoFacturacion();
        }
        //echo $consulta;
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getTablaPago($net) {
            $consulta ="SELECT CONCAT(cs.Prefijo,pp.Folio),f.IdFactura,f.Folio,pp.IdPagoParcial AS id_pago,DATE(pp.FechaPago) AS fechapago,pp.IdFormaPago,
                (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
                CONCAT('$', FORMAT(pp.ImportePagado, 2)) AS importe,pp.Observaciones AS observaciones,pp.Referencia AS referencia,c.NombreRazonSocial,
                pp.FolioFiscal, pp.PathPDF, pp.PathXML,pp.PathPDFPre
                FROM c_factura AS f 
                INNER JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura
                INNER JOIN c_seriepago AS cs On cs.idSerie = pp.IdSerie
                LEFT JOIN c_cliente as c ON c.RFC = (SELECT MIN(RFC) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1)
                WHERE f.IdFactura='$this->id_factura GROUP BY pp.IdPagoParcial';";
            $catalogo = new CatalogoFacturacion();
        
        $query = $catalogo->obtenerLista($consulta);
        echo $query;
        return $query;
    }

    public function getDatosbyFactura($net) {
        if (!$net) {
            $consulta = ("SELECT f.IdFactura,f.Folio,pp.id_pago,DATE(pp.fechapago) AS fechapago,pp.importe,pp.observaciones,
                pp.referencia,c.NombreRazonSocial,dfe.RazonSocial,f.RFCEmisor,
                SUM(DISTINCT con.Cantidad*con.PrecioUnitario)*1.16 AS Total,
                (SUM(DISTINCT con.Cantidad*con.PrecioUnitario)*1.16-SUM(pp.importe)) AS Pagado
                FROM c_factura AS f
                LEFT JOIN c_pagosparciales AS pp ON pp.id_factura=f.IdFactura
                LEFT JOIN c_cliente as c ON c.ClaveCliente=f.RFCReceptor
                LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa = f.RFCEmisor
                LEFT JOIN c_conceptos AS con ON con.idFactura=f.IdFactura
                WHERE f.IdFactura='" . $this->id_factura . "'
                GROUP BY f.IdFactura LIMIT 0,1;");
            $catalogo = new Catalogo();
        } else {
            $consulta = "SELECT f.IdFactura,f.Folio,pp.IdPagoParcial,DATE(pp.FechaPago) AS fechapago,pp.ImportePagado AS importe, pp.IdFormaPago,
                pp.Observaciones,pp.Referencia,c.NombreRazonSocial, dfe.IdDatosFacturacionEmpresa, c.RFC, 
                f.Total AS Total,
                (f.Total - (SELECT SUM(pp2.ImportePagado) FROM c_pagosparciales pp2 WHERE pp2.IdFactura = f.IdFactura)) AS Pagado
                FROM c_factura AS f
                LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura=f.IdFactura
                LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.RFC = f.RFCEmisor
                LEFT JOIN c_cliente as c ON c.RFC = (SELECT MIN(RFC) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1)
                WHERE f.IdFactura='$this->id_factura'
                GROUP BY c.ClaveCliente, f.IdFactura LIMIT 0,1;";
            $catalogo = new CatalogoFacturacion();
        }

        //echo $consulta;
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getDatosbyID() {
        $consulta = ("SELECT f.IdFactura,f.Folio,pp.id_pago,pp.fechapago,pp.importe,pp.observaciones,pp.referencia,c.NombreRazonSocial,e.RazonSocial
            FROM c_factura AS f
            INNER JOIN c_pagosparciales AS pp ON pp.id_factura=f.IdFactura
            INNER JOIN c_cliente as c ON c.ClaveCliente=f.RFCReceptor
            INNER JOIN c_empresa AS e ON e.idEmpresa=f.RFCEmisor
            WHERE pp.id_pago='" . $this->id_pago . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistrobyPagoID() {
        $consulta = ("SELECT cp.IdFormaPago,CONCAT(cf.Nombre,' ',cf.Descripcion) AS FormaPago
                            FROM c_pagosparciales as cp
                            INNER JOIN c_formapago as cf ON cp.IdFormaPago = cf.IdFormaPago
                            WHERE IdPagoParcial='" . $this->id_pago . "'");
        $catalogo = new CatalogoFacturacion();
            if(isset($this->empresa) && $this->empresa != ""){
            $catalogo->setEmpresa($this->empresa);
            }
            $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->FormaDePagoP = $rs['FormaPago'];
            return true;
        }
        return false;
    }

    public function getRegistrobyID($net) {
        if (!$net) {
            $consulta = ("SELECT FolioFiscal,FechaTimbrado,id_factura,referencia,observaciones,importe,DATE(fechapago) AS fechapago,NULL AS IdFormaPago,NULL AS idCuentaBancaria, "
                    . "NULL AS IdTipoCadena, '' AS CertPago, '' AS CadPago, '' AS SelloPago, "
                    . "Pantalla,FechaUltimaModificacion,UsuarioUltimaModificacion,FechaCreacion,UsuarioCreacion FROM c_pagosparciales "
                    . "WHERE id_pago='" . $this->id_pago . "'");
            $catalogo = new Catalogo();
            if(isset($this->empresa) && $this->empresa != ""){
            $catalogo->setEmpresa($this->empresa);
             }
        } else {
            $consulta = "SELECT FolioFiscal,FechaTimbrado,IdFactura AS id_factura,Referencia AS referencia,PathXML,PathPDF,
                Observaciones AS observaciones,ImportePagado AS importe,DATE(FechaPago) AS fechapago,IdFormaPago,
                IdSerie,Folio,idCuentaBancaria,IdTipoCadena,CertPago,CadPago,SelloPago,RFCBancoEmisorOrd,NomBancoEmisorOrd,CtaOrdenante,
                'Ninguna' AS Pantalla,NOW() AS FechaUltimaModificacion,'' AS UsuarioUltimaModificacion,NOW() AS FechaCreacion,UsuarioCreacion 
                FROM c_pagosparciales WHERE IdPagoParcial='$this->id_pago';";
            $catalogo = new CatalogoFacturacion();
            if(isset($this->empresa) && $this->empresa != ""){
            $catalogo->setEmpresa($this->empresa);
             }
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_factura = $rs['id_factura'];
            $this->referencia = $rs['referencia'];
            $this->observaciones = $rs['observaciones'];
            $this->importe = $rs['importe'];
            $this->fechapago = $rs['fechapago'];
            $this->PathXML = $rs['PathXML'];
            $this->PathPDF = $rs['PathPDF'];
            $this->FolioFiscal = $rs['FolioFiscal'];
            $this->FechaTimbrado = $rs['FechaTimbrado'];
            $this->IdFormaPago = $rs['IdFormaPago'];
            $this->IdSerie = $rs['IdSerie'];
            $this->Folio = $rs['Folio'];
            $this->cuentaBancaria = $rs['idCuentaBancaria'];
            $this->IdTipoCadena = $rs['IdTipoCadena'];
            $this->CertPago = $rs['CertPago'];
            $this->CadPago = $rs['CadPago'];
            $this->SelloPago = $rs['SelloPago'];
            $this->RFCBancoEmisorOrd = $rs['RFCBancoEmisorOrd'];
            $this->NomBancoEmisorOrd = $rs['NomBancoEmisorOrd'];
            $this->CtaOrdenante = $rs['CtaOrdenante'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function getDatosBancarioBeneficiaria($idCuentaBancaria) {
        $consulta = "SELECT cb.noCuenta, b.RFC
            FROM `c_cuentaBancaria` AS cb
            LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco
            WHERE cb.idCuentaBancaria = $idCuentaBancaria;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            return array("RfcEmisorCtaBen" => $rs['RFC'], "CtaBeneficiario" => $rs['noCuenta']);
        }
        return false;
    }

    public function verificaPagoMayor($pago, $idFactura, $idPago) {
        if ($idPago != "0") {
            $restarPago = " - (SELECT ImportePagado FROM c_pagosparciales WHERE IdPagoParcial = $idPago) ";
        } else {
            $restarPago = "";
        }
        $consulta = "SELECT 
            (CASE WHEN ISNULL(pp.IdFactura) AND $pago > f.Total THEN 1
            WHEN !ISNULL(pp.IdFactura) AND (SUM(pp.ImportePagado) $restarPago + $pago) > f.Total THEN 1
            ELSE 0 END) isMayor,  
            (CASE WHEN ISNULL(pp.IdFactura) AND $pago > f.Total THEN $pago - f.Total
            WHEN !ISNULL(pp.IdFactura) AND (SUM(pp.ImportePagado) $restarPago + $pago) > f.Total THEN (SUM(pp.ImportePagado) $restarPago + $pago) - f.Total
            ELSE 0 END) extra     
            FROM c_factura AS f
            LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura = f.IdFactura
            WHERE f.IdFactura = $idFactura GROUP BY f.IdFactura;";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            if ($rs['isMayor'] == "1") {
                $this->importeExtra = $rs['extra'];
                return true;
            }
        }
        return false;
    }

    public function verificaPagoMayorNotaRemision($pago, $idFactura, $idPago) {
        if ($idPago != "0") {
            $restarPago = " - (SELECT importe FROM c_pagosparciales WHERE id_pago = $idPago) ";
        } else {
            $restarPago = "";
        }
        $consulta = "SELECT 
            (CASE WHEN ISNULL(pp.id_factura) AND $pago > f.Total THEN 1
            WHEN !ISNULL(pp.id_factura) AND (SUM(pp.importe) $restarPago + $pago) > f.Total THEN 1
            ELSE 0 END) isMayor,  
            (CASE WHEN ISNULL(pp.id_factura) AND $pago > f.Total THEN $pago - f.Total
            WHEN !ISNULL(pp.id_factura) AND (SUM(pp.importe) $restarPago + $pago) > f.Total THEN (SUM(pp.importe) $restarPago + $pago) - f.Total
            ELSE 0 END) extra     
            FROM c_factura AS f
            LEFT JOIN c_pagosparciales AS pp ON pp.id_factura = f.IdFactura
            WHERE f.IdFactura = $idFactura GROUP BY f.IdFactura;";
        //echo $consulta;
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            if ($rs['isMayor'] == "1") {
                $this->importeExtra = $rs['extra'];
                return true;
            }
        }
        return false;
    }
    
    public function nuevoRegistro($net) {
        $cat = new Catalogo();
        if (!$net) {
            $consulta = "INSERT INTO c_pagosparciales(id_factura,referencia,observaciones,importe,fechapago,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $this->id_factura . "','" . $this->referencia . "','" . $this->observaciones . "','" . $this->importe . "','" . $this->fechapago . "','" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "');";
            $catalogo = new Catalogo();
        } else {
            $factura = new Factura_NET3();
            $ImportePorPagar = "null";
            if ($factura->getRegistroById($this->id_factura)) {
                $ImportePorPagar = (float) $factura->getTotal() - (float) $this->importe;
            }
            if (!isset($this->importe) || $this->importe == "") {
                $this->importe = "null";
            }
            if (!isset($this->cuentaBancaria) || $this->cuentaBancaria == "") {
                $this->cuentaBancaria = "null";
            }
            if (!isset($this->IdTipoCadena) || empty($this->IdTipoCadena)) {
                $this->IdTipoCadena = "null";
            }
            
            if (!isset($this->IdFormaPago) || empty($this->IdFormaPago)) {
                $this->IdFormaPago = "0";
            }

            if ($this->fechapago != "NOW()") {
                $this->fechapago = "'$this->fechapago'";
            }

            $this->Folio = "1";

            if (!isset($this->IdSerie) || empty($this->IdSerie)) {
                $this->IdSerie = "null";
                $consulta = "SELECT (UltimoFolioPago + 1) AS FolioPago FROM c_folio WHERE idEmpresa = $this->idEmpresaFactura AND Activo = 1 LIMIT 1;";
            } else {
                $consulta = "SELECT (FolioPago + 1) AS FolioPago FROM c_serie WHERE IdSerie = $this->IdSerie;";
            }
            $result = $cat->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                $this->Folio = $rs['FolioPago'];
            }

            $consulta = "INSERT INTO c_pagosparciales(IdFactura,Referencia,Observaciones,ImportePagado,ImportePorPagar,FechaPago,idCuentaBancaria,
                IdFormaPago,IdTipoCadena,CertPago,CadPago,SelloPago,RFCBancoEmisorOrd,NomBancoEmisorOrd,CtaOrdenante,IdSerie,Folio,UsuarioCreacion,FechaCreacion)
                VALUES('" . $this->id_factura . "','" . $this->referencia . "','" . $this->observaciones . "','" . $this->importe . "',$ImportePorPagar," .
                $this->fechapago . "," . $this->cuentaBancaria . ",$this->IdFormaPago,$this->IdTipoCadena,                
                '$this->CertPago','$this->CadPago','$this->SelloPago', '$this->RFCBancoEmisorOrd','$this->NomBancoEmisorOrd','$this->CtaOrdenante',
                $this->IdSerie,'$this->Folio','" . $this->UsuarioCreacion . "',NOW());";
            $catalogo = new CatalogoFacturacion();
        }
        //echo "Error: ".$consulta;
        $this->id_pago = $catalogo->insertarRegistro($consulta);
        if ($this->id_pago != NULL && $this->id_pago != 0) {
            if ($net) {
                if (!isset($this->IdSerie) || empty($this->IdSerie) || $this->IdSerie == "null") {
                    $consulta = "UPDATE c_folio SET UltimoFolioPago = (UltimoFolioPago + 1) WHERE idEmpresa = $this->idEmpresaFactura;";
                } else {
                    $consulta = "UPDATE c_serie SET FolioPago = (FolioPago + 1) WHERE IdSerie = $this->IdSerie;";
                }
                $result = $cat->obtenerLista($consulta);
            }

            $this->verificarPagado($net);
            return true;
        } else {
            return false;
        }
    }

    
    public function nuevoRegistroPago($net) {
        $cat = new Catalogo();
        if (!$net) {
            $consulta = "INSERT INTO c_pagosparciales(id_factura,referencia,observaciones,importe,fechapago,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $this->id_factura . "','" . $this->referencia . "','" . $this->observaciones . "','" . $this->importe . "','" . $this->fechapago . "','" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "');";
            $catalogo = new Catalogo();
        } else {
            $factura = new Factura_NET3();
            $ImportePorPagar = "null";
            if ($factura->getRegistroById($this->id_factura)) {
                $ImportePorPagar = (float) $factura->getTotal() - (float) $this->importe;
            }
            if (!isset($this->importe) || $this->importe == "") {
                $this->importe = "null";
            }
            if (!isset($this->cuentaBancaria) || $this->cuentaBancaria == "") {
                $this->cuentaBancaria = "null";
            }
            if (!isset($this->IdTipoCadena) || empty($this->IdTipoCadena)) {
                $this->IdTipoCadena = "null";
            }
            
            if (!isset($this->IdFormaPago) || empty($this->IdFormaPago)) {
                $this->IdFormaPago = "0";
            }

            if ($this->fechapago != "NOW()") {
                $this->fechapago = "'$this->fechapago'";
            }

            $this->Folio = "1";

            if (!isset($this->IdSerie) || empty($this->IdSerie)) {
                $this->IdSerie = "null";
                $consulta = "SELECT (UltimoFolioPago + 1) AS FolioPago FROM c_folio WHERE idEmpresa = $this->idEmpresaFactura AND Activo = 1 LIMIT 1;";
            } else {
                $consulta = "SELECT (FolioPago + 1) AS FolioPago FROM c_seriepago WHERE IdSerie = $this->IdSerie;";
                //$consulta = "SELECT (FolioPago + 1) AS FolioPago FROM c_serie WHERE IdSerie = $this->IdSerie;";
            }
            $result = $cat->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                $this->Folio = $rs['FolioPago'];
            }

            $consulta = "INSERT INTO c_pagosparciales(IdFactura,Referencia,Observaciones,ImportePagado,ImportePorPagar,FechaPago,idCuentaBancaria,
                IdFormaPago,IdTipoCadena,CertPago,CadPago,SelloPago,RFCBancoEmisorOrd,NomBancoEmisorOrd,CtaOrdenante,IdSerie,Folio,UsuarioCreacion,FechaCreacion)
                VALUES('" . $this->id_factura . "','" . $this->referencia . "','" . $this->observaciones . "',
                '" . $this->importe . "',$ImportePorPagar," .$this->fechapago . "," . $this->cuentaBancaria . 
                ",$this->IdFormaPago,$this->IdTipoCadena,'$this->CertPago','$this->CadPago','$this->SelloPago',
                '$this->RFCBancoEmisorOrd','$this->NomBancoEmisorOrd','$this->CtaOrdenante',
                $this->IdSerie,'$this->Folio','" . $this->UsuarioCreacion . "',NOW());";
            $catalogo = new CatalogoFacturacion();
        }
        //echo "Error: ".$consulta;
        $this->id_pago = $catalogo->insertarRegistro($consulta);
        if ($this->id_pago != NULL && $this->id_pago != 0) {
            if ($net) {
                if (!isset($this->IdSerie) || empty($this->IdSerie) || $this->IdSerie == "null") {
                    $consulta = "UPDATE c_folio SET UltimoFolioPago = (UltimoFolioPago + 1) WHERE idEmpresa = $this->idEmpresaFactura;";
                } else {
                    $consulta = "UPDATE c_seriepago SET FolioPago = (FolioPago + 1) WHERE IdSerie = $this->IdSerie;";
                }
                $result = $cat->obtenerLista($consulta);
            }

            $this->verificarPagado($net);
            return true;
        } else {
            return false;
        }
    }
    
    private function verificarPagado($net) {
        if (!$net) {
            $consulta = "SELECT 
                IF((SUM(pp.importe) - f.Total)>=0,1,0) AS Pagado,
                f.RFCReceptor,f.Folio, c.ClaveCliente
                FROM c_factura AS f
                LEFT JOIN c_pagosparciales AS pp ON pp.id_factura=f.IdFactura
                LEFT JOIN c_cliente as c ON c.ClaveCliente=f.RFCReceptor
                LEFT JOIN c_datosfacturacionempresa AS dfe ON dfe.IdDatosFacturacionEmpresa=f.RFCEmisor
                LEFT JOIN c_conceptos AS con ON con.idFactura=f.IdFactura
                WHERE f.IdFactura='" . $this->id_factura . "'
                GROUP BY f.IdFactura;";
            $catalogo = new Catalogo();
        } else {
            $consulta = "SELECT 
                f.RFCReceptor, c.ClaveCliente, f.Folio,
                IF((SUM(pp.ImportePagado)-f.Total)>=0,1,0) AS Pagado
                FROM c_factura AS f
                LEFT JOIN c_pagosparciales AS pp ON pp.IdFactura=f.IdFactura
                LEFT JOIN c_cliente AS c ON c.Activo = 1 AND TRIM(f.RFCReceptor) = TRIM(c.RFC) AND c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1) 
                WHERE f.IdFactura='$this->id_factura'
                GROUP BY f.IdFactura;";
            $catalogo = new CatalogoFacturacion();
        }

        //echo $consulta;
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            if ($rs['Pagado'] == "1") {
                $catalogo->obtenerLista("UPDATE c_factura SET EstadoFactura=1,FacturaPagada=1 WHERE IdFactura='" . $this->id_factura . "'");
                echo "<h2>La factura " . $rs['Folio'] . " ha sido marcada como pagada</h2><br/>";
            } else {
                $catalogo->obtenerLista("UPDATE c_factura SET EstadoFactura=1,FacturaPagada=0 WHERE IdFactura='" . $this->id_factura . "'");
                echo "<h2>La factura " . $rs['Folio'] . " está marcada como no pagada</h2><br/>";
            }
            $this->desmarcarClienteMoroso($rs['RFCReceptor'], $rs['ClaveCliente']);
        }
    }

    public function desmarcarClienteMoroso($RFCReceptor, $ClaveCliente) {
        $esMoroso = false;
        $facturas = new ReporteFacturacion();
        $facturas->setRfccliente($RFCReceptor);
        $estatus = array(1);
        $facturas->setStatus($estatus); /* Para que muestre solo las facturas no pagadas */
        $result = $facturas->getTabla(false);

        if (mysql_num_rows($result) == 0) {//Si ya no hay facturas no pagadas, se marca como no moroso
            $esMoroso = false;
        } else {//Aún hay facturas no pagadas, revisamos que no haya facturas NP mayor a 3 meses, en caso que no haya, se quita el estatus de moroso y suspenido
            $parametro = new Parametros();
            $catalogo = new Catalogo();
            $parametro->getRegistroById("12");
            if (($parametro->getValor()) != null) {
                $meses = $parametro->getValor();
            } else {
                $meses = 3;
            }
            $consulta = "SELECT DATE(DATE_SUB(NOW(),INTERVAL $meses MONTH)) AS Fecha_anterior;";
            $result = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                $facturas->setFechaFinal($rs['Fecha_anterior']);
            }
            $result = $facturas->getTabla(false);
            if (mysql_num_rows($result) == 0) {//Si ya no hay facturas no pagadas, mayor a N meses, no es moroso
                $esMoroso = false;
            } else {
                $esMoroso = true;
            }
        }

        $cliente = new Cliente();
        $cliente->getRegistroById($ClaveCliente);
        $cliente->setUsuarioUltimaModificacion($_SESSION['user']);
        $cliente->setPantalla("PHP PagoParcial");
        if (isset($_SESSION['idEmpresa'])) {
            $cliente->setEmpresa($_SESSION['idEmpresa']);
        }

        if (!$esMoroso) {
            $cliente->setIdEstatusCobranza(1);
            $cliente->cambiarEstatusCobranza();
            $cliente->marcarComoSuspendidoRFC(0);
            echo "<br/><b>El cliente " . $cliente->getNombreRazonSocial() . " ha sido marcado como al corriente</b><br/>";
        } else {
            if ($cliente->getNoVolverMoroso() == "0") {
                $cliente->setIdEstatusCobranza(2);
                $cliente->cambiarEstatusCobranza();
                echo "<br/><b>El cliente " . $cliente->getNombreRazonSocial() . " ha sido marcado como moroso por las facturas: ";
                while ($rs = mysql_fetch_array($result)) {
                    echo "<br/>* " . $rs['Folio'] . "  con fecha de facturación " . $rs['FechaFacturacion'];
                }
                echo "</b><br/>";
            } else {
                echo "<br/><b>El cliente " . $cliente->getNombreRazonSocial() . " no pudo ser marcado como moroso, ya que está marcado como NO VOLVER MOROSO</b><br/>";
            }
        }
    }

    public function deleteRegistro($net) {
        if (!$net) {
            $consulta = ("DELETE FROM c_pagosparciales WHERE id_pago='" . $this->id_pago . "'");
            $catalogo = new Catalogo();
        } else {
            $consulta = ("DELETE FROM c_pagosparciales WHERE IdPagoParcial = '" . $this->id_pago . "'");
            $catalogo = new CatalogoFacturacion();
        }
        //echo $consulta;
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $this->verificarPagado($net);
            return true;
        } else {
            return false;
        }
    }

    public function updateRegistro($net) {
        if (!$net) {
            $consulta = ("UPDATE c_pagosparciales SET id_factura='" . $this->id_factura . "',referencia='" . $this->referencia . "',observaciones='" . $this->observaciones . "',
                importe='" . $this->importe . "',fechapago='" . $this->fechapago . "',
                UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Pantalla='" . $this->Pantalla . "' 
                WHERE id_pago='" . $this->id_pago . "'");
            $catalogo = new Catalogo();
        } else {
            $factura = new Factura_NET3();
            $ImportePorPagar = "null";
            if ($factura->getRegistroById($this->id_factura)) {
                $ImportePorPagar = (float) $factura->getTotal() - (float) $this->importe;
            }
            if (!isset($this->importe) || $this->importe == "") {
                $this->importe = "null";
            }
            if (!isset($this->cuentaBancaria) || $this->cuentaBancaria == "") {
                $this->cuentaBancaria = "null";
            }
            if (!isset($this->IdFormaPago) || empty($this->IdFormaPago)) {
                $this->IdFormaPago = "null";
            }
            if (!isset($this->IdTipoCadena) || empty($this->IdTipoCadena)) {
                $this->IdTipoCadena = "null";
            }
            if (!isset($this->IdSerie) || empty($this->IdSerie)) {
                $this->IdSerie = "null";
            }
            $consulta = "UPDATE c_pagosparciales SET IdFactura='" . $this->id_factura . "',Referencia='" . $this->referencia . "',
                Observaciones='" . $this->observaciones . "',ImportePorPagar=$ImportePorPagar,IdFormaPago = $this->IdFormaPago, IdSerie=$this->IdSerie,
                IdTipoCadena = $this->IdTipoCadena, CertPago = '$this->CertPago', CadPago= '$this->CadPago', SelloPago = '$this->SelloPago',
                RFCBancoEmisorOrd = '$this->RFCBancoEmisorOrd', NomBancoEmisorOrd = '$this->NomBancoEmisorOrd', CtaOrdenante = '$this->CtaOrdenante',
                ImportePagado='" . $this->importe . "',FechaPago='" . $this->fechapago . "',idCuentaBancaria = $this->cuentaBancaria WHERE IdPagoParcial='" . $this->id_pago . "'";
            $catalogo = new CatalogoFacturacion();
        }

        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $this->verificarPagado($net);
            return true;
        } else {
            return false;
        }
    }

    public function getNumeroParcialidad() {
        $catalogo = new CatalogoFacturacion();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT COUNT(*) AS Numero, SUM(pp.ImportePagado) AS Pagado
            FROM c_pagosparciales pp 
            WHERE !ISNULL(FolioFiscal) AND IdFactura =  $this->id_factura";
        $result = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($result)) {
            $this->NumParcialidad = $rs['Numero'] + 1;
            $this->ImpSaldoAnt = $rs['Pagado'];
            return true;
        }
        return 1;
    }

    //*************************************************** Metodo que obtiene el saldo anterior de los pagos para PrePagos  *JT 03/10/18
     public function getNumeroParcialidadPago() {
        $catalogo = new CatalogoFacturacion();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT COUNT(*) AS Numero, SUM(pp.ImportePagado) AS Pagado
            FROM c_pagosparciales pp 
            WHERE IdFactura =  $this->id_factura";
        //echo "<br>".$consulta;
        $result = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($result)) {
            $this->NumParcialidad = $rs['Numero'];
            $this->ImpSaldoAnt = $rs['Pagado'];
            return true;
        }
        return 1;
    }
//**************************************************************Metodo que obtiene datos del pago parcial cuando se edita un pago existente *JT 04/10/18
    public function getRegistrosPagoParcial(){
    $catalogo = new CatalogoFacturacion();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT COUNT(*) AS Numero,SUM(ImportePagado) AS SaldoAnterior
                          FROM c_pagosparciales WHERE IdFactura = $this->id_factura AND IdPagoParcial<  $this->id_pago";
        $result = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($result)) {
            $this->SaldoAnteriorE = $rs['SaldoAnterior'];
            $this->NumParcialidad = $rs['Numero']+1;
            return true;
        }
        return false;
    }
//*************************************************************************** Metodo para obtener pagos anteriores      *JT 10/10/18
    public function getRegistrosPPAnteriores(){
        $Observaciones2="Pago realizado a partir de la nota de cr�dito con folio";
        $b=0;
        $catalogo = new CatalogoFacturacion();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT * FROM c_pagosparciales WHERE IdFactura = $this->id_factura AND IdPagoParcial != $this->id_pago";
        //echo "<br>".$consulta;
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            if($rs['IdSerie']==NULL || strncasecmp($rs['Observaciones'], $Observaciones2, 30) === 0){
                $this->SaldoAnteriorE = $rs['ImportePagado'] + $this->SaldoAnteriorE;
            }
            $b=1;
        }
        if($b==1)
            return true;
        else
            return false;
    }
//***************************************************************************
    public function actualizarInfoTimbrado() {
        $tabla = "c_pagosparciales";
        $where = "IdPagoParcial='" . $this->id_pago . "'";
        if (isset($this->PathXML) && !empty($this->PathXML)) {
            $this->PathXML = "'$this->PathXML'";
        } else {
            $this->PathXML = "NULL";
        }
        if (isset($this->PathPDF) && !empty($this->PathPDF)) {
            $this->PathPDF = "'$this->PathPDF'";
        } else {
            $this->PathPDF = "NULL";
        }
        if (isset($this->FolioFiscal) && !empty($this->FolioFiscal)) {
            $this->FolioFiscal = "'$this->FolioFiscal'";
        } else {
            $this->FolioFiscal = "NULL";
        }
        if (isset($this->FechaTimbrado) && !empty($this->FechaTimbrado)) {
            $this->FechaTimbrado = "'$this->FechaTimbrado'";
        } else {
            $this->FechaTimbrado = "NULL";
        }
        $consulta = "UPDATE $tabla SET PathXML = $this->PathXML, PathPDF = $this->PathPDF, 
            FolioFiscal = $this->FolioFiscal, FechaTimbrado = $this->FechaTimbrado WHERE $where;";
        $catalogo = new CatalogoFacturacion();

        $query = $catalogo->obtenerLista($consulta, $tabla, $where);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function actualizarInfoPrepago() {
        $tabla = "c_pagosparciales";
        $where = "IdPagoParcial='" . $this->id_pago . "'";
        if (isset($this->PathPDF) && !empty($this->PathPDF)) {
            $this->PathPDF = "'$this->PathPDF'";
        } else {
            $this->PathPDF = "NULL";
        }
        $consulta = "UPDATE $tabla SET  PathPDFPre = $this->PathPDF 
        WHERE $where;";
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta, $tabla, $where);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getCuentasFiscales($idFacturaTimbrada) {
        $factura = new Factura_NET3();
        if ($factura->getRegistroById($idFacturaTimbrada)) {
            $empresa = new Empresa();
            $empresa->setRFC($factura->getRFCEmisor());
            if (!$empresa->getRegistrobyRFC()) {
                return false;
            }
            $cliente = new ccliente();
            if (!$cliente->getRegistroByRFC($factura->getRFCReceptor())) {
                return false;
            }
            $catalogo = new Catalogo();
            if ($cliente->getModalidad() != "3") {
                $consulta = "SELECT ctt.NumeroCuenta AS CtaOrdenante, b.RFC AS RfcEmisorCtaOrd, b.Nombre AS NomBancoEmisorOrd,
                cb.noCuenta AS CtaBeneficiario, b2.RFC AS RfcEmisorCtaBen
                FROM c_folio_prefactura AS pref
                INNER JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = pref.IdEmisor
                INNER JOIN c_factura AS f ON f.Folio = pref.Folio AND f.RFCEmisor = fe.IdDatosFacturacionEmpresa
                INNER JOIN c_contrato AS ctt ON ctt.NoContrato = f.NoContrato
                LEFT JOIN c_banco AS b ON b.IdBanco = ctt.IdBanco
                LEFT JOIN c_cuentaBancaria AS cb ON cb.idCuentaBancaria = ctt.IdCuentaBancaria 
                LEFT JOIN c_banco AS b2 ON b2.IdBanco = cb.idBanco
                WHERE pref.FolioTimbrado = '" . $factura->getSerie() . $factura->getFolio() . "' AND pref.IdEmisor = " . $empresa->getId() . ";";
                $result = $catalogo->obtenerLista($consulta);
                if (mysql_num_rows($result) > 0) {
                    while ($rs = mysql_fetch_array($result)) {
                        $datos = array("CtaOrdenante" => $rs['CtaOrdenante'], "RfcEmisorCtaOrd" => $rs['RfcEmisorCtaOrd'],
                            "NomBancoEmisorOrd" => $rs['NomBancoEmisorOrd'], "CtaBeneficiario" => $rs['CtaBeneficiario'], "RfcEmisorCtaBen" => $rs['RfcEmisorCtaBen']);
                    }
                    return $datos;
                } else {
                    $consulta = "SELECT ctt.NumeroCuenta AS CtaOrdenante, b.RFC AS RfcEmisorCtaOrd, b.Nombre AS NomBancoEmisorOrd,
                        cb.noCuenta AS CtaBeneficiario, 
                        b2.RFC AS RfcEmisorCtaBen
                        FROM c_contrato AS ctt
                        LEFT JOIN c_banco AS b ON b.IdBanco = ctt.IdBanco
                        LEFT JOIN c_cuentaBancaria AS cb ON cb.idCuentaBancaria = ctt.IdCuentaBancaria 
                        LEFT JOIN c_banco AS b2 ON b2.IdBanco = cb.idBanco
                        WHERE ctt.ClaveCliente = '" . $cliente->getClaveCliente() . "' AND ctt.Activo = 1 AND ctt.FechaTermino >= NOW()
                        LIMIT 1;";
                    $result = $catalogo->obtenerLista($consulta);
                    if (mysql_num_rows($result) > 0) {
                        while ($rs = mysql_fetch_array($result)) {
                            $datos = array("CtaOrdenante" => $rs['CtaOrdenante'], "RfcEmisorCtaOrd" => $rs['RfcEmisorCtaOrd'],
                                "NomBancoEmisorOrd" => $rs['NomBancoEmisorOrd'], "CtaBeneficiario" => $rs['CtaBeneficiario'], "RfcEmisorCtaBen" => $rs['RfcEmisorCtaBen']);
                        }
                        return $datos;
                    } else {
                        return false;
                    }
                }
            }else{
                if($cliente->getDatosFiscalesVenta()){
                    $consulta = "SELECT kvc.NumeroCuenta AS CtaOrdenante, b.RFC AS RfcEmisorCtaOrd, b.Nombre AS NomBancoEmisorOrd,
                        cb.noCuenta AS CtaBeneficiario, 
                        b2.RFC AS RfcEmisorCtaBen
                        FROM k_ventaconfiguracion AS kvc 
                        LEFT JOIN c_banco AS b ON b.IdBanco = kvc.IdBanco
                        LEFT JOIN c_cuentaBancaria AS cb ON cb.idCuentaBancaria = kvc.IdCuentaBancaria
                        LEFT JOIN c_banco AS b2 ON b2.IdBanco = cb.idBanco
                        WHERE kvc.ClaveCliente = '".$cliente->getClaveCliente()."';";     				
                    $result = $catalogo->obtenerLista($consulta);
                    if (mysql_num_rows($result) > 0) {
                        while ($rs = mysql_fetch_array($result)) {
                            $datos = array("CtaOrdenante" => $rs['CtaOrdenante'], "RfcEmisorCtaOrd" => $rs['RfcEmisorCtaOrd'],
                                "NomBancoEmisorOrd" => $rs['NomBancoEmisorOrd'], "CtaBeneficiario" => $rs['CtaBeneficiario'], "RfcEmisorCtaBen" => $rs['RfcEmisorCtaBen']);
                        }
                        return $datos;
                    } else {
                        return false;
                    }
                }
            }
        }
        return false;
    }

    function getCuentaBancaria() {
        return $this->cuentaBancaria;
    }

    function setCuentaBancaria($cuentaBancaria) {
        $this->cuentaBancaria = $cuentaBancaria;
    }

    public function getId_pago() {
        return $this->id_pago;
    }

    public function getId_factura() {
        return $this->id_factura;
    }

    public function getReferencia() {
        return $this->referencia;
    }

    public function getObservaciones() {
        return $this->observaciones;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getFechapago() {
        return $this->fechapago;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setId_pago($id_pago) {
        $this->id_pago = $id_pago;
    }

    public function setId_factura($id_factura) {
        $this->id_factura = $id_factura;
    }

    public function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    public function setImporte($importe) {
        $this->importe = $importe;
    }

    public function setFechapago($fechapago) {
        $this->fechapago = $fechapago;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    function getImporteExtra() {
        return $this->importeExtra;
    }

    function getPathXML() {
        return $this->PathXML;
    }

    function getPathPDF() {
        return $this->PathPDF;
    }

    function getFolioFiscal() {
        return $this->FolioFiscal;
    }

    function getFechaTimbrado() {
        return $this->FechaTimbrado;
    }

    function setPathXML($PathXML) {
        $this->PathXML = $PathXML;
    }

    function setPathPDF($PathPDF) {
        $this->PathPDF = $PathPDF;
    }

    function setFolioFiscal($FolioFiscal) {
        $this->FolioFiscal = $FolioFiscal;
    }

    function setFechaTimbrado($FechaTimbrado) {
        $this->FechaTimbrado = $FechaTimbrado;
    }

    function getNumParcialidad() {
        return $this->NumParcialidad;
    }

    function getImpSaldoAnt() {
        return $this->ImpSaldoAnt;
    }

    function setNumParcialidad($NumParcialidad) {
        $this->NumParcialidad = $NumParcialidad;
    }

    function setImpSaldoAnt($ImpSaldoAnt) {
        $this->ImpSaldoAnt = $ImpSaldoAnt;
    }

    function getIdFormaPago() {
        return $this->IdFormaPago;
    }

    function setIdFormaPago($IdFormaPago) {
        $this->IdFormaPago = $IdFormaPago;
    }

    function getFolio() {
        return $this->Folio;
    }

    function getIdSerie() {
        return $this->IdSerie;
    }

    function setFolio($Folio) {
        $this->Folio = $Folio;
    }

    function setIdSerie($IdSerie) {
        $this->IdSerie = $IdSerie;
    }

    function getIdEmpresaFactura() {
        return $this->idEmpresaFactura;
    }

    function setIdEmpresaFactura($idEmpresaFactura) {
        $this->idEmpresaFactura = $idEmpresaFactura;
    }

    function getIdTipoCadena() {
        return $this->IdTipoCadena;
    }

    function getCertPago() {
        return $this->CertPago;
    }

    function getCadPago() {
        return $this->CadPago;
    }

    function getSelloPago() {
        return $this->SelloPago;
    }

    function setIdTipoCadena($IdTipoCadena) {
        $this->IdTipoCadena = $IdTipoCadena;
    }

    function setCertPago($CertPago) {
        $this->CertPago = $CertPago;
    }

    function setCadPago($CadPago) {
        $this->CadPago = $CadPago;
    }

    function setSelloPago($SelloPago) {
        $this->SelloPago = $SelloPago;
    }
    function getRFCBancoEmisorOrd() {
        return $this->RFCBancoEmisorOrd;
    }

    function getNomBancoEmisorOrd() {
        return $this->NomBancoEmisorOrd;
    }

    function getCtaOrdenante() {
        return $this->CtaOrdenante;
    }

    function setRFCBancoEmisorOrd($RFCBancoEmisorOrd) {
        $this->RFCBancoEmisorOrd = $RFCBancoEmisorOrd;
    }

    function setNomBancoEmisorOrd($NomBancoEmisorOrd) {
        $this->NomBancoEmisorOrd = $NomBancoEmisorOrd;
    }

    function setCtaOrdenante($CtaOrdenante) {
        $this->CtaOrdenante = $CtaOrdenante;
    }

    function setEmpresa($empresa){
        $this->empresa = $empresa;
    }
    function getEmpresa(){
        return $this->empresa;
    }
    function setEstado($estado){
        $this->Estado = $estado;
    }
    function getEstado(){
        return $this->Estado;
    }
    function getFormaDePagoP() {
        return $this->FormaDePagoP;
    }

    function setFormaDePagoP($FormaDePagoP) {
        $this->FormaDePagoP = $FormaDePagoP;
    }
    
    //*********************************************                       *JT 04/10/18
    function getSaldoAnteriorE() {
        return $this->SaldoAnteriorE;
}
    //*********************************************
}
