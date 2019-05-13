<?php

include_once("Conexion.class.php");
include_once("ConexionFacturacion.class.php");
include_once("PagosParciales.class.php");
include_once("ReporteFacturacion.class.php");
include_once("Catalogo.class.php");
include_once("CatalogoFacturacion.class.php");
include_once("Mail.class.php");
include_once("ParametroGlobal.class.php");

/**
 * Description of Factura
 *
 * @author MAGG
 */
class Factura_NET {

    private $IdFactura;
    private $RFCReceptor;
    private $RFCEmisor;
    private $NombreReceptor;
    private $NombreEmisor;
    private $FechaFacturacion;
    private $Folio;
    private $Serie;
    private $FacturaXML;
    private $PathXML;
    private $PeriodoFacturacion;
    private $EstadoFactura;
    private $FechaModificacion;
    private $TipoComprobante;
    private $IdFacturaRelacion;
    private $PathPDF;
    private $FacturaEnviada;
    private $Observaciones;
    private $FacturaPagada;
    private $FechaPago;
    private $NumTransaccion;
    private $CentrosCosto;
    private $Total;
    private $cfdiXML;
    private $cfdiTimbrado;
    private $cfdiRespPac;
    private $folioFiscal;
    private $EstatusFactura;
    private $CanceladaSAT;
    private $TipoFactura = "0";
    private $IdUsoCFDI;
    private $CFDI33;
    private $MetodoPago;
    private $FormaPago;
    private $UUID;
    private $empresa;

    public function getRegistroByFolio($folio) {
        if (!isset($folio) || $folio == "") {
            return false;
        }
        $consulta = ("SELECT * FROM c_factura WHERE Folio = '$folio';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdFactura = $rs['IdFactura'];
            $this->RFCReceptor = $rs['RFCReceptor'];
            $this->RFCEmisor = $rs['RFCEmisor'];
            $this->NombreReceptor = $rs['NombreReceptor'];
            $this->NombreEmisor = $rs['NombreEmisor'];
            $this->FechaFacturacion = $rs['FechaFacturacion'];
            $this->Folio = $rs['Folio'];
            $this->Serie = $rs['Serie'];
            $this->FacturaXML = $rs['FacturaXML'];
            $this->PathXML = $rs['PathXML'];
            $this->PeriodoFacturacion = $rs['PeriodoFacturacion'];
            $this->EstadoFactura = $rs['EstadoFactura'];
            $this->FechaModificacion = $rs['FechaModificacion'];
            $this->TipoComprobante = $rs['TipoComprobante'];
            $this->IdFacturaRelacion = $rs['IdFacturaRelacion'];
            $this->PathPDF = $rs['PathPDF'];
            $this->FacturaEnviada = $rs['FacturaEnviada'];
            $this->Observaciones = $rs['Observaciones'];
            $this->FacturaPagada = $rs['FacturaPagada'];
            $this->FechaPago = $rs['FechaPago'];
            $this->NumTransaccion = $rs['NumTransaccion'];
            $this->CentrosCosto = $rs['CentrosCosto'];
            $this->Total = $rs['Total'];
            $this->cfdiXML = $rs['cfdiXML'];
            $this->cfdiTimbrado = $rs['cfdiTimbrado'];
            $this->cfdiRespPac = $rs['cfdiRespPac'];
            $this->folioFiscal = $rs['folioFiscal'];
            $this->EstatusFactura = $rs['EstatusFactura'];
            $this->CanceladaSAT = $rs['CanceladaSAT'];
            $this->TipoFactura = $rs['TipoFactura'];
            $this->IdUsoCFDI = $rs['IdUsoCFDI'];
            $this->CFDI33 = $rs['CFDI33'];
            $this->MetodoPago = $rs['MetodoPago'];
            $this->FormaPago = $rs['FormaPago'];
            return true;
        }
        return false;
    }

    public function getRegistroById($id) {
        $consulta = "SELECT * FROM c_factura WHERE IdFactura = '$id';";
        $catalogo = new CatalogoFacturacion();
        if(isset($this->empresa) && $this->empresa != ""){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        
        while ($rs = mysql_fetch_array($query)) {
            $this->IdFactura = $rs['IdFactura'];
            $this->RFCReceptor = $rs['RFCReceptor'];
            $this->RFCEmisor = $rs['RFCEmisor'];
            $this->NombreReceptor = $rs['NombreReceptor'];
            $this->NombreEmisor = $rs['NombreEmisor'];
            $this->FechaFacturacion = $rs['FechaFacturacion'];
            $this->Folio = $rs['Folio'];
            $this->Serie = $rs['Serie'];
            $this->FacturaXML = $rs['FacturaXML'];
            $this->PathXML = $rs['PathXML'];
            $this->PeriodoFacturacion = $rs['PeriodoFacturacion'];
            $this->EstadoFactura = $rs['EstadoFactura'];
            $this->FechaModificacion = $rs['FechaModificacion'];
            $this->TipoComprobante = $rs['TipoComprobante'];
            $this->IdFacturaRelacion = $rs['IdFacturaRelacion'];
            $this->PathPDF = $rs['PathPDF'];
            $this->FacturaEnviada = $rs['FacturaEnviada'];
            $this->Observaciones = $rs['Observaciones'];
            $this->FacturaPagada = $rs['FacturaPagada'];
            $this->FechaPago = $rs['FechaPago'];
            $this->NumTransaccion = $rs['NumTransaccion'];
            $this->CentrosCosto = $rs['CentrosCosto'];
            $this->Total = $rs['Total'];
            $this->cfdiXML = $rs['cfdiXML'];
            $this->cfdiTimbrado = $rs['cfdiTimbrado'];
            $this->cfdiRespPac = $rs['cfdiRespPac'];
            $this->folioFiscal = $rs['folioFiscal'];
            $this->EstatusFactura = $rs['EstatusFactura'];
            $this->CanceladaSAT = $rs['CanceladaSAT'];
            $this->TipoFactura = $rs['TipoFactura'];
            $this->IdUsoCFDI = $rs['IdUsoCFDI'];
            $this->CFDI33 = $rs['CFDI33'];
            $this->MetodoPago = $rs['MetodoPago'];
            $this->FormaPago = $rs['FormaPago'];
            $this->UUID = $rs['folioFiscal'];
            return true;
        }
        return false;
    }

    /**
     * Verifica si la factura existe segun el folio, la fecha, el emisor y el total.
     * @param type $folio
     * @param type $fecha
     * @param type $emisor
     * @param type $total
     * @return boolean
     */
    public function existeFactura($folio, $fecha, $emisor, $total) {
        $consulta = "SELECT IdFactura FROM c_factura WHERE Folio = '$folio' AND (PeriodoFacturacion = '$fecha' OR FechaFacturacion = '$fecha') AND RFCEmisor = '$emisor' AND Total = $total;";
        $catalogo = new CatalogoFacturacion();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($result) > 0) {
            return true;
        }
        return false;
    }

    public function newFactura() {
        if (!isset($this->PathXML)) {
            $this->PathXML = "";
        }
        if (!isset($this->cfdiTimbrado)) {
            $this->cfdiTimbrado = "";
        }
        if (!isset($this->FechaFacturacion)) {
            $this->FechaFacturacion = "NOW()";
        } else {
            $this->FechaFacturacion = "'$this->FechaFacturacion'";
        }
        if (!isset($this->folioFiscal)) {
            $this->folioFiscal = "null";
        } else {
            $this->folioFiscal = "'$this->folioFiscal'";
        }
        if (!isset($this->FacturaEnviada)) {
            $this->FacturaEnviada = "0";
        }
        if(!isset($this->TipoComprobante) || empty($this->TipoComprobante)){
            $this->TipoComprobante = "ingreso";
        }

        $consulta = "INSERT INTO c_factura(RFCReceptor,NombreReceptor,RFCEmisor,NombreEmisor,FechaFacturacion,Folio,Serie,
            FacturaXML,PeriodoFacturacion,Observaciones,EstadoFactura,FechaModificacion,TipoComprobante,FacturaEnviada,FacturaPagada,Total,IdFacturaRelacion,
            CentrosCosto,cfdiRespPac,PathPDF,PathXML,TipoFactura,cfdiTimbrado,folioFiscal)
            VALUES('" . $this->RFCReceptor . "','" . $this->NombreReceptor . "','" . $this->RFCEmisor . "','" . $this->NombreEmisor . "',
             $this->FechaFacturacion,'" . $this->Folio . "','" . $this->Serie . "','$this->FacturaXML','" . $this->PeriodoFacturacion . "','',1,now(),'$this->TipoComprobante',0,0,'" . $this->Total . "',0,
             '','','" . $this->PathPDF . "','$this->PathXML','" . $this->TipoFactura . "','$this->cfdiTimbrado'," . $this->folioFiscal . ")";
        $catalogo = new CatalogoFacturacion();        
        if($this->empresa){
            $catalogo->setEmpresa($this->empresa);
        }
        
        $this->IdFactura = $catalogo->insertarRegistro($consulta);
        if ($this->IdFactura != NULL && $this->IdFactura != 0) {
            return true;
        }
        return false;
    }

    public function marcarFacturaPagada($tipo) {        
        $consulta = ("UPDATE c_factura SET FacturaPagada=$tipo WHERE IdFactura = $this->IdFactura");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getSumaPagada() {
        $pagado = "0";
        $consulta = ("SELECT SUM(ImportePagado) AS pagado FROM `c_pagosparciales` WHERE IdFactura = $this->IdFactura;");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $pagado = $rs['pagado'];
        }        
        return $pagado;
    }

    /**
     * Marca como pagadas las facturas que están como no pagadas pero ya no tienen importe por pagar.
     * @param type $mandarmail true en caso de querer notificar por mail, false en caso contrario.
     * @return boolean true en caso de que todas las facturas se actualizaron correctamente, false en caso contrario.
     */
    public function marcarPagadas($mandarmail) {
        $consulta = "SELECT f.IdFactura, f.Folio, f.Total,
        pp.IdPagoParcial, pp.ImportePagado, pp.ImportePorPagar
        FROM c_factura AS f
        INNER JOIN c_pagosparciales AS pp ON pp.IdPagoParcial = (SELECT MAX(IdPagoParcial) FROM c_pagosparciales WHERE IdFactura = f.IdFactura)
        WHERE f.TipoComprobante = 'ingreso' AND f.Serie = '' AND f.EstadoFactura = 1 
        AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) 
        AND f.FacturaPagada = 0 AND pp.ImportePorPagar = 0;";
        $facturas = "";
        $reporte = new ReporteFacturacion();
        $bien = true;
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);

        while ($rs = mysql_fetch_array($query)) {
            $reporte->setFolio($rs['Folio']);
            if ($reporte->CambiarPagado("1")) {
                $facturas.= ($rs['Folio'] . ",");
            } else {
                $bien = false;
            }
        }

        if ($mandarmail && $facturas != "") {//Si hay que mandar mail
            $facturas = substr($facturas, 0, strlen($facturas) - 1);
            $mail = new Mail();
            $parametroGlobal = new ParametroGlobal();
            if ($parametroGlobal->getRegistroById("8")) {
                $mail->setFrom($parametroGlobal->getValor());
            } else {
                $mail->setFrom("scg-salida@scgenesis.mx");
            }
            $mail->setSubject("Facturas marcadas como pagadas");
            $mail->setBody("Las siguientes facturas fueron marcadas como pagadas porque su importe por pagar ya es de cero: <b>$facturas</b>");
            $facturas = substr($facturas, strlen($facturas) - 1);

            $correos = array();
            $catalogo_aux = new Catalogo();
            /* Correos para el cron por default */
            $result = $catalogo_aux->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 5;");
            while ($rs = mysql_fetch_array($result)) {
                if (isset($rs['correo']) && $rs['correo'] != "" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                    array_push($correos, $rs['correo']);
                }
            }
            /* Correos de usuarios con el puesto de cuentas por cobrar */
            $consulta = "SELECT CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS cxc, correo 
            FROM c_usuario AS u WHERE u.IdPuesto = 15;";
            $result = $catalogo_aux->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                if (isset($rs['correo']) && $rs['correo'] != "" && filter_var($rs['correo'], FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                    array_push($correos, $rs['correo']);
                }
            }

            foreach ($correos as $value) {/* Lo mandamos a los correos de los usuarios de cuentas por cobrar */
                $mail->setTo($value);
                if ($mail->enviarMail() == "1") {
                    //echo "<br/>Un correo fue enviado por resumen global de facturación a $value.";
                } else {
                    echo "<br/>Error: No se pudo enviar el correo a $value.";
                }
            }
        }
        return $bien;
    }

    /**
     * Regresa el folio maximo (más uno) de la razon social especificada
     * @param type $RFCEmisor RFC de la razón social
     * @return type entero con el maximo folio más uno insertado
     */
    public function getMaxFolioByRazonSocial($RFCEmisor) {
        $consulta = "SELECT (MAX(CAST(Folio AS UNSIGNED))+1) AS maximo FROM c_factura WHERE RFCEmisor = '$RFCEmisor';";
        $catalogo = new CatalogoFacturacion();
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $maximo = (int) $rs['maximo'];
        }
        return $maximo;
    }
    public function getConceptosPDF(){
        $catalogo = new Catalogo();
        $catalogoFacturacion = new CatalogoFacturacion();
        
        $folioF = "";
        $consultaFolioF = "SELECT CONCAT(Serie,Folio) AS folio FROM c_factura WHERE IdFactura = $this->IdFactura;";
        $resultFolioF = $catalogoFacturacion->obtenerLista($consultaFolioF);
        while ($row = mysql_fetch_array($resultFolioF)) {
            $folioF = $row['folio'];
        }
        $folioPreFactura = "";
        $consultaFolioPreFactura = "SELECT Folio FROM c_folio_prefactura WHERE FolioTimbrado = '$folioF';";
        $resultFolioPreFactura = $catalogo->obtenerLista($consultaFolioPreFactura);
        while ($row1 = mysql_fetch_array($resultFolioPreFactura)) {
            $folioPreFactura = $row1['Folio'];
        }
        $consultaConcepto = "SELECT f.IdFactura,c.Descripcion,c.Cantidad,c.PrecioUnitario,c.Descuento,(c.Cantidad*c.PrecioUnitario) AS importe
                            FROM c_factura AS f 
                            INNER JOIN c_conceptos AS c ON c.IdFactura = f.IdFactura
                            WHERE f.Folio = '$folioPreFactura';";
        $resultConcepto = $catalogo->obtenerLista($consultaConcepto);
        return $resultConcepto;
    }

    public function getIdFactura() {
        return $this->IdFactura;
    }

    public function setIdFactura($IdFactura) {
        $this->IdFactura = $IdFactura;
    }

    public function getRFCReceptor() {
        return $this->RFCReceptor;
    }

    public function setRFCReceptor($RFCReceptor) {
        $this->RFCReceptor = $RFCReceptor;
    }

    public function getRFCEmisor() {
        return $this->RFCEmisor;
    }

    public function setRFCEmisor($RFCEmisor) {
        $this->RFCEmisor = $RFCEmisor;
    }

    public function getNombreReceptor() {
        return $this->NombreReceptor;
    }

    public function setNombreReceptor($NombreReceptor) {
        $this->NombreReceptor = $NombreReceptor;
    }

    public function getNombreEmisor() {
        return $this->NombreEmisor;
    }

    public function setNombreEmisor($NombreEmisor) {
        $this->NombreEmisor = $NombreEmisor;
    }

    public function getFechaFacturacion() {
        return $this->FechaFacturacion;
    }

    public function setFechaFacturacion($FechaFacturacion) {
        $this->FechaFacturacion = $FechaFacturacion;
    }

    public function getFolio() {
        return $this->Folio;
    }

    public function setFolio($Folio) {
        $this->Folio = $Folio;
    }

    public function getSerie() {
        return $this->Serie;
    }

    public function setSerie($Serie) {
        $this->Serie = $Serie;
    }

    public function getFacturaXML() {
        return $this->FacturaXML;
    }

    public function setFacturaXML($FacturaXML) {
        $this->FacturaXML = $FacturaXML;
    }

    public function getPathXML() {
        return $this->PathXML;
    }

    public function setPathXML($PathXML) {
        $this->PathXML = $PathXML;
    }

    public function getPeriodoFacturacion() {
        return $this->PeriodoFacturacion;
    }

    public function setPeriodoFacturacion($PeriodoFacturacion) {
        $this->PeriodoFacturacion = $PeriodoFacturacion;
    }

    public function getEstadoFactura() {
        return $this->EstadoFactura;
    }

    public function setEstadoFactura($EstadoFactura) {
        $this->EstadoFactura = $EstadoFactura;
    }

    public function getFechaModificacion() {
        return $this->FechaModificacion;
    }

    public function setFechaModificacion($FechaModificacion) {
        $this->FechaModificacion = $FechaModificacion;
    }

    public function getTipoComprobante() {
        return $this->TipoComprobante;
    }

    public function setTipoComprobante($TipoComprobante) {
        $this->TipoComprobante = $TipoComprobante;
    }

    public function getIdFacturaRelacion() {
        return $this->IdFacturaRelacion;
    }

    public function setIdFacturaRelacion($IdFacturaRelacion) {
        $this->IdFacturaRelacion = $IdFacturaRelacion;
    }

    public function getPathPDF() {
        return $this->PathPDF;
    }

    public function setPathPDF($PathPDF) {
        $this->PathPDF = $PathPDF;
    }

    public function getFacturaEnviada() {
        return $this->FacturaEnviada;
    }

    public function setFacturaEnviada($FacturaEnviada) {
        $this->FacturaEnviada = $FacturaEnviada;
    }

    public function getObservaciones() {
        return $this->Observaciones;
    }

    public function setObservaciones($Observaciones) {
        $this->Observaciones = $Observaciones;
    }

    public function getFacturaPagada() {
        return $this->FacturaPagada;
    }

    public function setFacturaPagada($FacturaPagada) {
        $this->FacturaPagada = $FacturaPagada;
    }

    public function getFechaPago() {
        return $this->FechaPago;
    }

    public function setFechaPago($FechaPago) {
        $this->FechaPago = $FechaPago;
    }

    public function getNumTransaccion() {
        return $this->NumTransaccion;
    }

    public function setNumTransaccion($NumTransaccion) {
        $this->NumTransaccion = $NumTransaccion;
    }

    public function getCentrosCosto() {
        return $this->CentrosCosto;
    }

    public function setCentrosCosto($CentrosCosto) {
        $this->CentrosCosto = $CentrosCosto;
    }

    public function getTotal() {
        return $this->Total;
    }

    public function setTotal($Total) {
        $this->Total = $Total;
    }

    public function getCfdiXML() {
        return $this->cfdiXML;
    }

    public function setCfdiXML($cfdiXML) {
        $this->cfdiXML = $cfdiXML;
    }

    public function getCfdiTimbrado() {
        return $this->cfdiTimbrado;
    }

    public function setCfdiTimbrado($cfdiTimbrado) {
        $this->cfdiTimbrado = $cfdiTimbrado;
    }

    public function getCfdiRespPac() {
        return $this->cfdiRespPac;
    }

    public function setCfdiRespPac($cfdiRespPac) {
        $this->cfdiRespPac = $cfdiRespPac;
    }

    public function getFolioFiscal() {
        return $this->folioFiscal;
    }

    public function setFolioFiscal($folioFiscal) {
        $this->folioFiscal = $folioFiscal;
    }

    public function getEstatusFactura() {
        return $this->EstatusFactura;
    }

    public function setEstatusFactura($EstatusFactura) {
        $this->EstatusFactura = $EstatusFactura;
    }

    public function getCanceladaSAT() {
        return $this->CanceladaSAT;
    }

    public function setCanceladaSAT($CanceladaSAT) {
        $this->CanceladaSAT = $CanceladaSAT;
    }

    public function getTipoFactura() {
        return $this->TipoFactura;
    }

    public function setTipoFactura($TipoFactura) {
        $this->TipoFactura = $TipoFactura;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    function getCFDI33() {
        return $this->CFDI33;
    }

    function setCFDI33($CFDI33) {
        $this->CFDI33 = $CFDI33;
    }
    
    function getIdUsoCFDI() {
        return $this->IdUsoCFDI;
    }

    function setIdUsoCFDI($IdUsoCFDI) {
        $this->IdUsoCFDI = $IdUsoCFDI;
    }

    function getMetodoPago() {
        return $this->MetodoPago;
    }

    function getFormaPago() {
        return $this->FormaPago;
    }

    function setMetodoPago($MetodoPago) {
        $this->MetodoPago = $MetodoPago;
    }

    function setFormaPago($FormaPago) {
        $this->FormaPago = $FormaPago;
    }

    function getUUID() {
        return $this->UUID;
    }

    function setUUID($UUID) {
        $this->UUID = $UUID;
    }

}

?>