<?php

include_once("Catalogo.class.php");
include_once("Conexion.class.php");
include_once("CatalogoFacturacion.class.php");
include_once("Empresa.class.php");
include_once("Concepto.class.php");
include_once("Inventario.class.php");
include_once("Configuracion.class.php");
include_once("EquipoCaracteristicasFormatoServicio.class.php");
include_once("ServicioGeneral.class.php");
include_once("Cliente.class.php");

class Factura {

    private $IdFactura;
    private $RFCReceptor;
    private $RFCEmisor;
    private $FechaFacturacion;
    private $Serie;
    private $FacturaXML;
    private $PathXML;
    private $PeriodoFacturacion;
    private $EstadoFactura;
    private $FechaModificacion;
    private $TipoComprobante;
    private $PathPDF;
    private $FacturaEnviada;
    private $FacturaPagada;
    private $FechaPago;
    private $NumTransaccion;
    private $Total;
    private $cfdiXML;
    private $cfdiTimbrado;
    private $cfdiRespPac;
    private $folioFiscal;
    private $EstatusFactura;
    private $CanceladaSAT;
    private $PendienteCancelar;
    private $UsuarioEnvio;
    private $FechaEnvio;
    private $MetodoPago;
    private $DescripcionMetodoPago;
    private $FormaPago;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $Folio;
    private $Sello;
    private $CadenaOriginal;
    private $idEmpresa;
    private $Id_TipoFactura = "1";
    private $IdDomicilioFiscal;
    private $NumCtaPago;
    private $folio_respaldo;
    private $TipoArrendamiento;
    private $empresa;
    private $ndc;
    private $mensaje;
    private $folioPendiente;
    private $idFolioPendiente;
    private $RFCEmisorFacturacion;
    private $MostrarSerie;
    private $IdSerie;

    public function NuevaPreFactura() {
        $cat = new Catalogo();
        if (isset($this->empresa)) {
            $cat->setEmpresa($this->empresa);
        }
        /* Obtenemos primero el folio */
        if (!isset($this->idEmpresa)) {
            $this->idEmpresa = $_SESSION['Empresa'];
        }
        
        if(!isset($this->ndc)){
            $this->ndc = false;
        }
        
        $cliente = new Cliente();        
        if(!$this->ndc && $cliente->getRegistroById($this->RFCReceptor)){
            $this->idEmpresa = ($cliente->getIdDatosFacturacionEmpresa());
            $this->RFCEmisor = ($cliente->getIdDatosFacturacionEmpresa());
        }

        //echo $consulta;
        $folio = 1;
        $prefijo = "";
        $SerieC = "";
        $SerieV = "";
        if(isset($this->IdSerie) && ($this->IdSerie != "")){
            $SerieC = ",IdSerie";
            $SerieV = ",$this->IdSerie";
            $consulta = "SELECT Prefijo, FolioInicio FROM `c_serie` WHERE IdSerie = $this->IdSerie;";
            $query = $cat->obtenerLista($consulta);

            if (mysql_num_rows($query) > 0) {
                while ($rs = mysql_fetch_array($query)) {
                    $prefijo = $rs['Prefijo'];
                    $folio = (int) $rs['FolioInicio'];
                }
                $consulta2 = "UPDATE c_serie SET FolioInicio = FolioInicio + 1 WHERE IdSerie = $this->IdSerie;";
                $query = $cat->obtenerLista($consulta2);
                $folio = "'".$prefijo . $folio."'";
            }
        }else{
            if ($this->idEmpresa != NULL) {
                $consulta = "SELECT MAX(Folio) AS UltimoFolio FROM `c_folio_prefactura` WHERE IdEmisor = $this->idEmpresa;";
                $query = $cat->obtenerLista($consulta);

                if (mysql_num_rows($query) > 0) {
                    while ($rs = mysql_fetch_array($query)) {
                        $folio = (int) $rs['UltimoFolio'];
                    }
                    $folio++;
                }
            }   
        }     
        $this->Folio = $folio;  
        if (isset($this->IdDomicilioFiscal) && $this->IdDomicilioFiscal != "") {
            $domicilio = $this->IdDomicilioFiscal;
        } else {
            $domicilio = "null";
        }

        if (!is_array($this->PeriodoFacturacion)) {
            $aux_periodo = $this->PeriodoFacturacion;
            $this->PeriodoFacturacion = array();
            array_push($this->PeriodoFacturacion, $aux_periodo);
        }

        if (isset($this->PeriodoFacturacion) && !empty($this->PeriodoFacturacion)) {
            $periodo = "";
            foreach ($this->PeriodoFacturacion as $value) {
                if ($value != "") {
                    $periodo = "'" . $value . "'";
                    break;
                }
            }

            if ($periodo == "") {//En caso que todos los periodos vengan vacios
                $periodo = "NOW()";
            }
        } else {
            $periodo = "NOW()";
        }

        if (isset($this->NumCtaPago) && $this->NumCtaPago != "") {
            $numCtaPago = "'" . $this->NumCtaPago . "'";
        } else {
            $numCtaPago = "null";
        }

        if (!isset($this->TipoArrendamiento) || $this->TipoArrendamiento == "") {
            $arrendamiento = "NULL";
        } else {
            $arrendamiento = "'$this->TipoArrendamiento'";
        }

        if (!isset($this->TipoComprobante) || $this->TipoComprobante == "") {
            $this->TipoComprobante = "ingreso";
        }
        
        if(!isset($this->MostrarSerie) || ($this->MostrarSerie == "")){
            $this->MostrarSerie = 0;
        }
        $consulta = "INSERT INTO c_factura(MetodoPago,FormaPago,RFCReceptor,RFCEmisor,EstadoFactura,UsuarioCreacion,FechaCreacion,
           UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,TipoComprobante,Folio,FechaFacturacion,IdDomicilioFiscal,Id_TipoFactura,
           NumCtaPago,TipoArrendamiento,MostrarSerie$SerieC)
           VALUES('" . $this->MetodoPago . "','" . $this->FormaPago . "','" . $this->RFCReceptor . "','" . $this->RFCEmisor . "',0,
               '" . $this->UsuarioCreacion . "',now(),'" . $this->UsuarioUltimaModificacion . "',now(),'" . $this->Pantalla . "','$this->TipoComprobante',
               $folio,$periodo,$domicilio,'$this->Id_TipoFactura',$numCtaPago, $arrendamiento,$this->MostrarSerie$SerieV)";
        //echo $consulta;
        $this->IdFactura = $cat->insertarRegistro($consulta);
        if ($this->IdFactura != NULL && $this->IdFactura != "") {
            if(!isset($this->IdSerie) || ($this->IdSerie == "")){
                $consulta = "INSERT INTO c_folio_prefactura(Folio, IdEmisor, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, 
                    Pantalla) VALUES($this->Folio,$this->RFCEmisor,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
                $cat->insertarRegistro($consulta);
            }
            
            if (count($this->PeriodoFacturacion) > 1) {
                $this->insertarMultiPeriodos($this->PeriodoFacturacion, false);
            }
            return true;
        }
        return false;
    }

    public function UpdateFactura() {
        if (isset($this->PeriodoFacturacion) && !empty($this->PeriodoFacturacion)) {
            $periodo = "";
            foreach ($this->PeriodoFacturacion as $value) {
                if ($value != "") {
                    $periodo = "'" . $value . "'";
                    break;
                }
            }

            if ($periodo == "") {//En caso que todos los periodos vengan vacios
                $periodo = "NOW()";
            }
        } else {
            $periodo = "NOW()";
        }

        if (isset($this->NumCtaPago) && $this->NumCtaPago != "") {
            $numCtaPago = "'" . $this->NumCtaPago . "'";
        } else {
            $numCtaPago = "null";
        }

        if (!isset($this->TipoArrendamiento) || $this->TipoArrendamiento == "") {
            $this->TipoArrendamiento = "NULL";
        }

        $consulta = "UPDATE c_factura SET MetodoPago='$this->MetodoPago',FormaPago='$this->FormaPago',NumCtaPago=$numCtaPago,RFCEmisor = '$this->RFCEmisor',
           UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),
           Pantalla='" . $this->Pantalla . "',FechaFacturacion=$periodo,TipoArrendamiento = $this->TipoArrendamiento 
           WHERE IdFactura=" . $this->IdFactura;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $this->eliminarMultiPeriodos();
            if (count($this->PeriodoFacturacion) > 1) {
                $this->insertarMultiPeriodos($this->PeriodoFacturacion, false);
            }
            return true;
        }
        return false;
    }

    public function insertarMultiPeriodosFacturados($IdFactura) {
        $result = $this->getMultiPeriodos();
        $periodos_array = array();
        while ($rs = mysql_fetch_array($result)) {
            array_push($periodos_array, $rs['Periodo']);
        }
        $id_aux = $this->IdFactura;
        $this->IdFactura = $IdFactura;
        $this->insertarMultiPeriodos($periodos_array, true);
        $this->IdFactura = $id_aux;
    }

    public function getMultiPeriodos() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $consulta = "SELECT * FROM k_facturaperiodos WHERE IdFactura = $this->IdFactura ORDER BY Periodo;";

        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    /**
     * 
     * @param type $periodos periodo a insertar
     * @param type $base_facturacion true en caso de quererlo insertar en la base de facturacion, false en caso contrario
     */
    public function insertarMultiPeriodos($periodos, $base_facturacion) {
        if ($base_facturacion) {
            $catalogo = new CatalogoFacturacion();
        } else {
            $catalogo = new Catalogo();
        }
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        foreach ($periodos as $value) {
            if ($value != "") {
                $consulta = "INSERT INTO k_facturaperiodos(IdFactura, Periodo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, 
                    FechaUltimaModificacion, Pantalla) 
                    VALUES($this->IdFactura, '$value','$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');";
                $catalogo->insertarRegistro($consulta);
            }
        }
    }

    public function eliminarMultiPeriodos() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $consulta = "DELETE FROM k_facturaperiodos WHERE IdFactura = $this->IdFactura;";
        $catalogo->obtenerLista($consulta);
    }

    /**
     * Verificamos si el cliente tiene factura vigente de arrendamiento para el periodo especificado
     * @param type $rfc
     * @param type $periodo
     * @return boolean true en caso que el cliente tenga una factura multi periodo vigente, false en caso contrario
     */
    public function tieneFacturaMultiPeriodo($rfc, $periodo) {
        $catalogo = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT fp.IdFactura FROM k_facturaperiodos AS fp
                LEFT JOIN c_factura AS f ON f.IdFactura = fp.IdFactura
                WHERE fp.Periodo = '$periodo' AND f.RFCReceptor = '$rfc' 
                AND f.EstadoFactura = 1 AND f.Serie = '' AND f.TipoComprobante = 'ingreso' AND TipoArrendamiento = 1;";

        $resultMultiPeriodo = $catalogo->obtenerLista($consulta);

        if (mysql_num_rows($resultMultiPeriodo) > 0) {//Si tiene alguna factura multi-periodo que abarque el periodo especificado
            return true;
        } else {        //Si tiene una factura con el periodo especificado
            $consulta = "SELECT IdFactura FROM c_factura AS f WHERE 
                f.PeriodoFacturacion = '$periodo 00:00:00' AND f.RFCReceptor = '$rfc' 
                AND f.EstadoFactura = 1 AND f.Serie = '' AND f.TipoComprobante = 'ingreso';";
            $result = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($result) > 0) {
                return true;
            }
        }
        return false;
    }

    
    public function verificarFacturasDobles() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        //Se busca las facturas del periodo y rfc
        $consulta = "SELECT f.IdFactura, f.Folio, DATE(f.FechaFacturacion) AS FechaFacturacion, f.RFCReceptor, fd.IdBitacora, b.NoSerie, 
            fp.FolioTimbrado AS FolioSAT, fd.RentaMensual, fd.CostoExcedentesBN, fd.CostoExcedentesColor, fd.CostoProcesadosBN, fd.CostoProcesadosColor,
            f2.IdFactura AS IdFacturaTimbrada, f2.Folio AS FolioPref, f2.FechaFacturacion AS PeriodoTimbrado,
            fd2.RentaMensual AS RentaMensualTimbrado, fd2.CostoExcedentesBN AS CostoExcedentesBNTimbrado, 
            fd2.CostoExcedentesColor AS CostoExcedentesColorTimbrado, fd2.CostoProcesadosBN AS CostoProcesadosBNTimbrado, 
            fd2.CostoProcesadosColor AS CostoProcesadosColorTimbrado
            FROM `c_factura` AS f
            LEFT JOIN c_facturadetalle AS fd ON fd.IdFactura = f.IdFactura
            LEFT JOIN c_factura AS f2 ON f2.RFCReceptor = f.RFCReceptor 
            LEFT JOIN c_facturadetalle AS fd2 ON fd2.IdFactura = f2.IdFactura
            LEFT JOIN c_folio_prefactura AS fp ON fp.Folio = f2.Folio AND fp.IdEmisor = f2.RFCEmisor
            LEFT JOIN k_facturaperiodos AS kfp ON f2.IdFactura = kfp.IdFactura
            LEFT JOIN c_bitacora AS b ON b.id_bitacora = fd.IdBitacora
            WHERE f.IdFactura = $this->IdFactura AND !ISNULL(fd2.IdFacturaDetalle)
            AND (
                (MONTH(f.FechaFacturacion) = MONTH(f2.FechaFacturacion) AND YEAR(f.FechaFacturacion) = YEAR(f2.FechaFacturacion))
            OR 
                (MONTH(f.FechaFacturacion) = MONTH(kfp.Periodo) AND YEAR(f.FechaFacturacion) = YEAR(kfp.Periodo))
            )
            AND fd.IdBitacora = fd2.IdBitacora AND f.IdFactura <> f2.IdFactura AND !ISNULL(fp.FolioTimbrado) AND f2.TipoArrendamiento = 1
            GROUP BY f2.Folio, f2.RFCEmisor, fp.FolioTimbrado, fd.IdBitacora;";        
        $result = $catalogo->obtenerLista($consulta);        
        $timbrado = false;
        $this->mensaje = "";
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) { 
                
                if($rs['RentaMensual']!= "" && $rs['RentaMensualTimbrado']!=""){                    
                    $this->mensaje .= "<br/> * La renta mensual del equipo <b>".$rs['NoSerie']."</b> en el periodo <b>".substr($catalogo->formatoFechaReportes($rs['FechaFacturacion']), 5)."</b> ya fue facturada con el folio <b>".$rs['FolioSAT']."</b>";
                    $timbrado = true;
                }
                
                if($rs['CostoExcedentesBNTimbrado']!= "" && $rs['CostoExcedentesBNTimbrado']!=""){                    
                    $this->mensaje .= "<br/> * Las páginas excedente b/n del equipo <b>".$rs['NoSerie']."</b> en el periodo <b>".substr($catalogo->formatoFechaReportes($rs['FechaFacturacion']), 5)."</b> ya fue facturada con el folio <b>".$rs['FolioSAT']."</b>";
                    $timbrado = true;
                }
                
                if($rs['CostoExcedentesColorTimbrado']!= "" && $rs['CostoExcedentesColorTimbrado']!=""){                    
                    $this->mensaje .= "<br/> * Las páginas excedente de color del equipo <b>".$rs['NoSerie']."</b> en el periodo <b>".substr($catalogo->formatoFechaReportes($rs['FechaFacturacion']), 5)."</b> ya fue facturada con el folio <b>".$rs['FolioSAT']."</b>";
                    $timbrado = true;
                }
                
                if($rs['CostoProcesadosBNTimbrado']!= "" && $rs['CostoProcesadosBNTimbrado']!=""){                    
                    $this->mensaje .= "<br/> * Las páginas procesadas b/n del equipo <b>".$rs['NoSerie']."</b> en el periodo <b>".substr($catalogo->formatoFechaReportes($rs['FechaFacturacion']), 5)."</b> ya fue facturada con el folio <b>".$rs['FolioSAT']."</b>";
                    $timbrado = true;
                }
                
                if($rs['CostoProcesadosColorTimbrado']!= "" && $rs['CostoProcesadosColorTimbrado']!=""){                    
                    $this->mensaje .= "<br/> * Las páginas procesadas de color del equipo <b>".$rs['NoSerie']."</b> en el periodo <b>".substr($catalogo->formatoFechaReportes($rs['FechaFacturacion']), 5)."</b> ya fue facturada con el folio <b>".$rs['FolioSAT']."</b>";
                    $timbrado = true;
                }
            }
        }
        return $timbrado;
    }

    public function UpdateXMLFactura() {
        $cat = new Catalogo();
        if (isset($this->empresa)) {
            $cat->setEmpresa($this->empresa);
        }
        $catfact = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catfact->setEmpresa($this->empresa);
        }
        /*$empresa = new Empresa();
        $empresa->setId($this->RFCEmisor);
        $empresa->getRegistrobyID();
        $consulta = "SELECT IdFolio,UltimoFolio FROM `c_folio` WHERE RFCEmisor='" . $empresa->getRFC() . "'";
        $query = $catfact->obtenerLista($consulta);
        $folio = $this->Folio;
        $idFolio = 0;
        if (mysql_num_rows($query) > 0) {
            while ($rs = mysql_fetch_array($query)) {
                //$folio = (int) $rs['UltimoFolio'];
                $idFolio = $rs['IdFolio'];
            }
            //$folio++;
        } else {
            $consulta = "INSERT INTO c_folio(FolioInicial, FolioFinal, Serie, NoAprobacion, AnioAprobacion, UltimoFolio, 
            RFCEmisor, Activo) 
            VALUES(1,1000,'',1,1,1,'$this->RFCEmisor',1);";
            $folio = 1;
            $idFolio = $catfact->insertarRegistro($consulta);
        }*/
        $query = $cat->obtenerLista("UPDATE c_factura SET Invisible=1,Generada=1,PathXML='" . $this->PathXML . "',Sello='" . $this->Sello . "',CadenaOriginal='" . $this->CadenaOriginal . "',folioFiscal='" . $this->folioFiscal . "',UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Pantalla='" . $this->Pantalla . "' WHERE IdFactura=" . $this->IdFactura);
        if ($query == 1) {
            /*if ($this->IdFactura != "") {
                if($this->folioPendiente != NULL && $this->folioPendiente){
                    $consulta = "UPDATE c_foliopendiente SET Activo = 0 WHERE IdFolio = $this->idFolioPendiente;";                    
                    $catfact->obtenerLista($consulta);
                }   
                $consulta = "UPDATE c_folio_prefactura SET FolioTimbrado = $folio WHERE Folio = $this->folio_respaldo AND IdEmisor='$this->RFCEmisor'";
                $cat->insertarRegistro($consulta);
                return true;
            }*/
            return false;
        }
        return false;
    }

    public function UpdateEnviadaFactura() {
        $consulta = ("UPDATE c_factura SET FacturaEnviada=1,UsuarioEnvio='" . $this->UsuarioEnvio . "',FechaEnvio=NOW(),UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW() WHERE IdFactura=" . $this->IdFactura);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getRegistrobyID() {
        $consulta = ("SELECT f.*, fp.Descripcion 
            FROM `c_factura` AS f
            LEFT JOIN c_formapago AS fp ON fp.Nombre = f.MetodoPago
            WHERE IdFactura = $this->IdFactura;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->FechaFacturacion = $rs['FechaFacturacion'];
            $this->PeriodoFacturacion = $rs['FechaFacturacion'];
            $this->MetodoPago = $rs['MetodoPago'];
            $this->DescripcionMetodoPago = $rs['Descripcion'];
            $this->FormaPago = $rs['FormaPago'];
            $this->RFCReceptor = $rs['RFCReceptor'];
            $this->RFCEmisor = $rs['RFCEmisor'];
            $this->EstadoFactura = $rs['EstadoFactura'];
            $this->TipoComprobante = $rs['TipoComprobante'];
            $this->Folio = $rs['Folio'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->Id_TipoFactura = $rs['Id_TipoFactura'];
            $this->IdDomicilioFiscal = $rs['IdDomicilioFiscal'];
            $this->NumCtaPago = $rs['NumCtaPago'];
            $this->TipoArrendamiento = $rs['TipoArrendamiento'];
            $this->MostrarSerie = $rs['MostrarSerie'];
            $this->IdSerie = $rs['IdSerie'];
            return true;
        }
        return false;
    }

    public function getFechaFacturacionNombre() {
        $consulta = ("SELECT CONCAT((CASE MONTH(DATE(FechaFacturacion))
        WHEN '01' THEN 'Enero'
        WHEN '02' THEN 'Febrero'
        WHEN '03' THEN 'Marzo'
        WHEN '04' THEN 'Abril'
        WHEN '05' THEN 'Mayo'
        WHEN '06' THEN 'Junio'
        WHEN '07' THEN 'Julio'
        WHEN '08' THEN 'Agosto'
        WHEN '09' THEN 'Septiembre'
        WHEN '10' THEN 'Octubre'
        WHEN '11' THEN 'Noviembre'
        WHEN '12' THEN 'Diciembre'
        ELSE '' END),' ',YEAR(DATE(FechaFacturacion))) AS Fecha
            FROM c_factura
            WHERE IdFactura =" . $this->IdFactura);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $rs['Fecha'];
        }
        return "";
    }

    /**
     * Regresa el folio maximo (más uno) de la razon social especificada
     * @param type $RFCEmisor RFC de la razón social
     * @return type entero con el maximo folio más uno insertado
     */
    public function getMaxFolioByRazonSocial($RFCEmisor) {
        $consulta = "SELECT (MAX(CAST(Folio AS UNSIGNED))+1) AS maximo FROM c_factura WHERE RFCEmisor = '$RFCEmisor';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $maximo = (int) $rs['maximo'];
        }
        return $maximo;
    }

    /**
     * Elimina conceptos y factura
     * @param type $idFactura id de la factura
     * @return boolean true en caso de eliminar todos los registros, false en casoc ontrario
     */
    public function deleteFactura($idFactura) {
        $consulta = "DELETE FROM `c_conceptos` WHERE idFactura = $idFactura;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result != "0") {
            $consulta = "DELETE FROM c_factura WHERE IdFactura = $idFactura;";
            $result = $catalogo->obtenerLista($consulta);
            if ($result != "0") {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * 
     * @param type $idFactura id de la factura
     * @param type $idBitacoras array con id de las bitacoras
     * @param type $facturacion true en caso de ser a la base de facturacion, false en caso contrario.
     * @param type $usuario Usuario de creacion
     * @param type $pantalla Pantalla de creacion
     */
    public function detalleFactura($idFactura, $todos_array ,$idBitacoras, $idBitacorasColor, $idBitacorasRenta, $facturacion, $usuario, $pantalla, 
            $contadoresBN, $contadoresBNAnterior, $contadoresColor, $contadoresColorAnterior) {
        $configuracion = new Configuracion();
        $inventario = new Inventario();
        $equipo = new EquipoCaracteristicasFormatoServicio();
        if (isset($this->empresa)) {
            $inventario->setEmpresa($this->empresa);
            $configuracion->setEmpresa($this->empresa);
            $equipo->setEmpresa($this->empresa);
        }
        $prefijos = array("gim", "gfa", "im", "fa"); //Prefijos a revisar (siempre se toman las prioridades: gim, gfa, im, fa)               
        
        foreach ($todos_array as $key => $id_bitacora) { //Recorremos todas las bitacoras            
            if ($configuracion->getRegistroById($id_bitacora)) {//Obtenemos el NoSerie de la bitacora
                $result = $inventario->getDatosDeInventario($configuracion->getNoSerie()); //Inventario del NoSerie
                while ($rs = mysql_fetch_array($result)) {//Recorremos los datos del inventario                    
                    $ServicioGeneral = new ServicioGeneral();
                    $ClaveCentroCosto = $rs['ClaveCentroCosto'];
                    $IdKServicio = "null";
                    $IdServicio = "null";
                    $RentaMensual = "null";
                    $IncluidosBN = "null";
                    $IncluidosColor = "null";
                    $CostoExcedentesBN = "null";
                    $CostoExcedentesColor = "null";
                    $CostoProcesadosBN = "null";
                    $CostoProcesadosColor = "null";
                    $contadorBN = $contadoresBN[$key];
                    $contadorBNAnterior = $contadoresBNAnterior[$key];
                    $contadorColor = $contadoresColor[$key];
                    $contadorColorAnterior = $contadoresColorAnterior[$key];                                                          
                    
                    if ($equipo->isColor($rs['NoParteEquipo'])) {//Para saber si el equipo es de color o b/n.
                        $color = 1;
                    } else {
                        $color = 0;
                    }
                    foreach ($prefijos as $pref) {//Buscamos que tipo de servicio tiene asocido el equipo
                        $pref_mayus = strtoupper($pref);
                        $pref_minus = strtolower($pref);
                        if (isset($rs['IdKServicio' . $pref_minus])) { //Si existe el tipo de servicio.                                                        
                            $IdKServicio = $rs['IdKServicio' . $pref_minus];
                            $IdServicio = $rs['IdServicio' . $pref_mayus];
                            if ($ServicioGeneral->getCobranzasByTipoServicio($IdServicio)) {//Guardamos los datos 
                                if ($ServicioGeneral->getCobrarRenta() && in_array($id_bitacora, $idBitacorasRenta)) {//Si para este equipo se cobra la renta mensual
                                    if (isset($rs[$pref_minus . 'Renta'])) {
                                        $RentaMensual = $rs[$pref_minus . 'Renta'];
                                    }
                                }

                                if ($ServicioGeneral->getCobrarExcedenteBN() && in_array($id_bitacora, $idBitacoras)) {//Si se cobran los excedentes B/N y el equipo es b/n.          
                                    if (isset($rs[$pref_minus . 'incluidosBN'])) {
                                        $IncluidosBN = $rs[$pref_minus . 'incluidosBN'];
                                    }
                                    if (isset($rs[$pref_minus . 'ExcedentesBN'])) {
                                        $CostoExcedentesBN = $rs[$pref_minus . 'ExcedentesBN'];
                                    }
                                }

                                if ($ServicioGeneral->getCobrarExcedenteColor() && $color == 1 && in_array($id_bitacora, $idBitacorasColor)) {//Si se cobran los excedentes color y el equipo es a color.
                                    if (isset($rs[$pref_minus . 'incluidosColor'])) {
                                        $IncluidosColor = $rs[$pref_minus . 'incluidosColor'];
                                    }
                                    if (isset($rs[$pref_minus . 'ExcedentesColor'])) {
                                        $CostoExcedentesColor = $rs[$pref_minus . 'ExcedentesColor'];
                                    }
                                }

                                if ($ServicioGeneral->getCobrarProcesadasBN() && in_array($id_bitacora, $idBitacoras)) {//Si se cobran los procesados B/N.
                                    if (isset($rs[$pref_minus . 'ProcesadasBN'])) {
                                        $CostoProcesadosBN = $rs[$pref_minus . 'ProcesadasBN'];
                                    }
                                }

                                if ($ServicioGeneral->getCobrarProcesadasColor() && $color == 1 && in_array($id_bitacora, $idBitacorasColor)) {//Si se cobran los procesados color y el equipo es color.
                                    if (isset($rs[$pref_minus . 'ProcesadosColor'])) {
                                        $CostoProcesadosColor = $rs[$pref_minus . 'ProcesadosColor'];
                                    }
                                }
                            }
                            break;
                        }
                    }
                    //Insertamos el detalle
                    $consulta = "INSERT INTO c_facturadetalle(IdFacturaDetalle, IdFactura, IdBitacora, ClaveCentroCosto, Color, IdKServicio, IdServicio, RentaMensual, 
                        IncluidosBN, IncluidosColor, CostoExcedentesBN, CostoExcedentesColor, CostoProcesadosBN, CostoProcesadosColor, 
                        ContadorBN, ContadorBNAnterior, ContadorColor, ContadorColorAnterior,
                        UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
                            VALUES(0,$idFactura, $id_bitacora, '$ClaveCentroCosto',$color, $IdKServicio, $IdServicio, $RentaMensual,
                            $IncluidosBN, $IncluidosColor, $CostoExcedentesBN, $CostoExcedentesColor,$CostoProcesadosBN, $CostoProcesadosColor,
                            $contadorBN, $contadorBNAnterior, $contadorColor, $contadorColorAnterior,
                            '$usuario', NOW(), '$usuario', NOW(), '$pantalla');";                    
                    if ($facturacion) {
                        $catalogo = new CatalogoFacturacion();
                        if (isset($this->empresa)) {
                            $catalogo->setEmpresa($this->empresa);
                        }
                    } else {
                        $catalogo = new Catalogo();
                        if (isset($this->empresa)) {
                            $catalogo->setEmpresa($this->empresa);
                        }
                    }
                    $catalogo->insertarRegistro($consulta);
                    break;
                }//Fin while para recorrer el inventario del equipo
            }//Fin si se obtuvo el NoSerie
        }//Fin foreache bitacoras
    }

    public function marcarFacturadoEquiposPorFacturar() {
        $consulta = "SELECT DATE(f.FechaFacturacion) AS FechaFacturacion,p.IdPeriodo,b.NoSerie,fd.* 
            FROM `c_facturadetalle` AS fd
            LEFT JOIN c_factura AS f ON f.IdFactura = fd.IdFactura
            LEFT JOIN c_periodo AS p ON p.IdPeriodo = (SELECT MAX(IdPeriodo) FROM c_periodo WHERE MONTH(Periodo) = MONTH(f.FechaFacturacion) AND YEAR(Periodo) = YEAR(f.FechaFacturacion))
            LEFT JOIN c_bitacora AS b ON b.id_bitacora = fd.IdBitacora
            WHERE fd.IdFactura = $this->IdFactura;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {//Recorremos todos los equipos que fueron considerados en esta factura
            $serie = $rs['NoSerie'];
            if (!isset($rs['IdBitacora']) || !isset($rs['IdPeriodo'])) {
                break;
            }
            $bitacora = $rs['IdBitacora'];
            $periodo = $rs['IdPeriodo'];
            $consulta = "SELECT * FROM `k_equiposporfacturar` WHERE IdBitacora = $bitacora AND IdPeriodo = $periodo LIMIT 0,1;";
            $result2 = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($result2) == 0) {
                //echo "<br/>No se tenían registros para marcar como facturado del equipo $serie en el periodo ".substr($catalogo->formatoFechaReportes($rs['FechaFacturacion']),5)."<br/>";
                continue;
            }

            $todo_cobrado = true;
            while ($rs2 = mysql_fetch_array($result2)) {
                if ($rs2['EquipoFacturado'] == "1") {
                    echo "<br/>El equipo $serie ya había sido marcado como facturado en el periodo " . substr($catalogo->formatoFechaReportes($rs['FechaFacturacion']), 5) . "<br/>";
                    continue;
                }
                //Vamos matando los costos que si fueron pagados en la factura.                
                /*                 * *****    Renta Mensual   ********* */
                if (isset($rs2['RentaMensual']) && $rs2['RentaMensual'] != "" && floatval($rs2['RentaMensual']) > 0 && $rs2["RentaMensualFacturado"] == NULL) {//Se tiene que cobrar Renta Mensual
                    if (isset($rs['RentaMensual']) && $rs['RentaMensual'] != "" && floatval($rs['RentaMensual']) > 0) {//Si en la factura se considero la renta mensual                        
                        $resultado = $catalogo->obtenerLista("UPDATE k_equiposporfacturar SET RentaMensualFacturado = $this->IdFactura, 
                            IncluidasBNFacturado = $this->IdFactura, IncluidasColorFacturado = $this->IdFactura,
                            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
                            WHERE IdBitacora = $bitacora AND IdPeriodo = $periodo;");
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado RentaMensual del equipo $serie";
                        }
                    } else {//Si en la factura no viene cobrada la renta mensual
                        echo "<br/>No se pago RentaMensual del equipo $serie<br/>";
                        $todo_cobrado = false;
                    }
                }

                /*                 * *****    CostoExcedentesBN   ********* */
                if (isset($rs2['CostoExcedentesBN']) && $rs2['CostoExcedentesBN'] != "" && floatval($rs2['CostoExcedentesBN']) > 0 && $rs2["CostoExcedentesBNFacturado"] == NULL) {//Se tiene que cobrar CostoExcedentesBN
                    if (isset($rs['CostoExcedentesBN']) && $rs['CostoExcedentesBN'] != "" && floatval($rs['CostoExcedentesBN']) > 0) {//Si en la factura se considero CostoExcedentesBN
                        $resultado = $catalogo->obtenerLista("UPDATE k_equiposporfacturar SET CostoExcedentesBNFacturado = $this->IdFactura,
                            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
                            WHERE IdBitacora = $bitacora AND IdPeriodo = $periodo;");
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoExcedentesBN del equipo $serie";
                        }
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoExcedentesBN del equipo $serie";
                        }
                    } else {//Si en la factura no viene cobrada la renta mensual
                        echo "<br/>No se pago CostoExcedentesBN del equipo $serie<br/>";
                        $todo_cobrado = false;
                    }
                }

                /*                 * *****    CostoExcedentesColor   ********* */
                if (isset($rs2['CostoExcedentesColor']) && $rs2['CostoExcedentesColor'] != "" && floatval($rs2['CostoExcedentesColor']) > 0 && $rs2["CostoExcedentesColorFacturado"] == NULL) {//Se tiene que cobrar CostoExcedentesColor
                    if (isset($rs['CostoExcedentesColor']) && $rs['CostoExcedentesColor'] != "" && floatval($rs['CostoExcedentesColor']) > 0) {//Si en la factura se considero CostoExcedentesColor
                        $resultado = $catalogo->obtenerLista("UPDATE k_equiposporfacturar SET CostoExcedentesColorFacturado = $this->IdFactura,
                            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
                            WHERE IdBitacora = $bitacora AND IdPeriodo = $periodo;");
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoExcedentesColor del equipo $serie";
                        }
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoExcedentesColor del equipo $serie";
                        }
                    } else {//Si en la factura no viene cobrada la renta mensual
                        echo "<br/>No se pago CostoExcedentesColor del equipo $serie<br/>";
                        $todo_cobrado = false;
                    }
                }

                /*                 * *****    CostoProcesadaBN   ********* */
                if (isset($rs2['CostoProcesadaBN']) && $rs2['CostoProcesadaBN'] != "" && floatval($rs2['CostoProcesadaBN']) > 0 && $rs2["CostoProcesadaBNFacturado"] == NULL) {//Se tiene que cobrar CostoProcesadaBN
                    if (isset($rs['CostoProcesadosBN']) && $rs['CostoProcesadosBN'] != "" && floatval($rs['CostoProcesadosBN']) > 0) {//Si en la factura se considero CostoProcesadaBN
                        $resultado = $catalogo->obtenerLista("UPDATE k_equiposporfacturar SET CostoProcesadaBNFacturado = $this->IdFactura,
                            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
                            WHERE IdBitacora = $bitacora AND IdPeriodo = $periodo;");
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoProcesadaBN del equipo $serie";
                        }
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoProcesadaBN del equipo $serie";
                        }
                    } else {//Si en la factura no viene cobrada la renta mensual
                        echo "<br/>No se pago CostoProcesadaBN del equipo $serie<br/>";
                        $todo_cobrado = false;
                    }
                }

                /*                 * *****    CostoProcesadaColor   ********* */
                if (isset($rs2['CostoProcesadaColor']) && $rs2['CostoProcesadaColor'] != "" && floatval($rs2['CostoProcesadaColor']) > 0 && $rs2["CostoProcesadaColorFacturado"] == NULL) {//Se tiene que cobrar CostoProcesadaColor
                    if (isset($rs['CostoProcesadosColor']) && $rs['CostoProcesadosColor'] != "" && floatval($rs['CostoProcesadosColor']) > 0) {//Si en la factura se considero CostoProcesadaColor
                        $resultado = $catalogo->obtenerLista("UPDATE k_equiposporfacturar SET CostoProcesadaColorFacturado = $this->IdFactura,
                            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
                            WHERE IdBitacora = $bitacora AND IdPeriodo = $periodo;");
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoProcesadaColor del equipo $serie";
                        }
                        if ($resultado != "1") {
                            echo "<br/>No se pudo marcar como facturado CostoProcesadaColor del equipo $serie";
                        }
                    } else {//Si en la factura no viene cobrada la renta mensual
                        echo "<br/>No se pago CostoProcesadaColor del equipo $serie<br/>";
                        $todo_cobrado = false;
                    }
                }
            }//Fin while que recorre lo que hay que cobrar por equipo

            if ($todo_cobrado) {//Si todo lo previsto fue cobrado, marcamos el equipo como EquipoFacturado
                $resultado = $catalogo->obtenerLista("UPDATE k_equiposporfacturar SET EquipoFacturado = 1,
                    UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
                    WHERE IdBitacora = $bitacora AND IdPeriodo = $periodo;");
                if ($resultado != "1") {
                    echo "<br/>No se pudo marcar como facturado CostoProcesadaColor del equipo $serie";
                }
            }
        }//Fin while que recorre los equipos por factura
    }
    
    public function marcarFolioTimbrado(){
        $cat = new Catalogo();
        if (isset($this->empresa)) {
            $cat->setEmpresa($this->empresa);
        }
        
        $catfact = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catfact->setEmpresa($this->empresa);
        }
        
        if($this->folioPendiente != NULL && $this->folioPendiente){
            $consulta = "UPDATE c_foliopendiente SET Activo = 0 WHERE IdFolio = $this->idFolioPendiente;";                    
            $result = $catfact->obtenerLista($consulta);
            if($result != "1"){
                echo "<br/>Atención, el folio no se pudo actualizar, es importante reportarlo al adminsitrador del sistema";
                return false;
            }
        }   
        $consulta = "UPDATE c_folio_prefactura SET FolioTimbrado = $this->Folio WHERE Folio = $this->folio_respaldo AND IdEmisor='$this->RFCEmisor'";
        
        $result = $cat->obtenerLista($consulta);
        if($result != "1"){
            echo "<br/>Atención, el folio de la prefactura no se pudo actualizar, es importante reportarlo al adminsitrador del sistema";
            return false;
        }
        return true;
    }
    
    public function nuevoFolioNoTimbrado(){
        $catalogo = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT IdFolio FROM c_foliopendiente WHERE Folio = '$this->Folio' AND IdDatosFacturacionEmpresa = $this->RFCEmisor;";
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) == 0){        
            $consulta = "INSERT INTO c_foliopendiente(IdFolio, Folio, IdDatosFacturacionEmpresa, Activo, UsuarioCreacion, "
                    . "FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) "
                    . "VALUES(0, '$this->Folio', $this->RFCEmisor, 1, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');";
            $this->idFolioPendiente = $catalogo->insertarRegistro($consulta);
            if($this->idFolioPendiente != NULL && $this->idFolioPendiente != 0){
                return true;
            }
            return false;
        }
        return true;                
    }
    
    public function folioNotimbrado(){
        $consulta = "SELECT fp.Folio, fp.IdFolio
            FROM `c_foliopendiente` AS fp
            LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = fp.IdDatosFacturacionEmpresa
            WHERE fp.Activo = 1 AND fe.IdDatosFacturacionEmpresa = $this->RFCEmisor ORDER BY Folio ASC LIMIT 0,1;";
        $catalogo = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) > 0){
            while($rs = mysql_fetch_array($result)){                
                $this->folioPendiente = true;
                $this->folio_respaldo = $this->Folio;//El folio respaldo es el folio de la pre-factura
                $this->Folio = $rs['Folio'];        
                $this->idFolioPendiente = $rs['IdFolio'];
                return TRUE;
            }
        }
        return FALSE;
    }

    public function folioReciente() {
        $catfact = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catfact->setEmpresa($this->empresa);
        }
        $empresa = new Empresa();
        $empresa->setId($this->RFCEmisor);
        $empresa->getRegistrobyID();
        $consulta = "SELECT IdFolio,UltimoFolio+1 AS UltimoFolio FROM `c_folio` WHERE RFCEmisor='" . $empresa->getRFC() . "';";        
        $query = $catfact->obtenerLista($consulta);
        $folio = 0;
        $idFolio = 0;
        if (mysql_num_rows($query) > 0) {
            while ($rs = mysql_fetch_array($query)) {
                $folio = (int) $rs['UltimoFolio'];
                $idFolio = $rs['IdFolio'];
            }            
            $consulta = "UPDATE c_folio SET UltimoFolio = $folio WHERE IdFolio = $idFolio;";            
            $catfact->obtenerLista($consulta);  
        } else {
            $folio = 1;
            $consulta = "INSERT INTO c_folio(FolioInicial, FolioFinal, Serie, NoAprobacion, AnioAprobacion, UltimoFolio, 
            RFCEmisor, Activo) 
            VALUES(1,1000,'',1,1,1,'".$empresa->getRFC()."',1);";            
            $idFolio = $catfact->insertarRegistro($consulta);            
        }
        $this->folio_respaldo = $this->Folio;//El folio respaldo es el folio de la pre-factura
        $this->Folio = $folio;
        $this->folioPendiente = FALSE;
        $this->idFolioPendiente = NULL;
        return $folio;
    }

    public function validarDescripcionConceptos($id) {
        $concepto = new Concepto();
        $result = $concepto->getConceptosByFactura($id);
        $valido = $this->getValorAsciiValido();
        while ($rs = mysql_fetch_array($result)) {
            $descripcion = $rs['Descripcion'];
            $array = str_split($descripcion);
            $i = 0;
            foreach ($array as $value) {
                $i++;
                $ascii_value = ord($value . "");
                if (!in_array($ascii_value, $valido)) {
                    echo "Error: el concepto $descripcion tiene un caracter no válido [$ascii_value] cerca de la posición $i (";
                    for ($j = -5; $j <= 5; $j++) {
                        if (isset($array[$i + $j])) {
                            echo $array[$i + $j];
                        }
                    }
                    echo "), favor de cambiarlo o eliminarlo.";
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Codigo ascii (int) de los caracteres validos en los conceptos.
     * @return array codigos ascii.
     */
    public function getValorAsciiValido() {
        $validos = array();
        array_push($validos, 10); //Salto de línea
        array_push($validos, 13); //Retorno de carro
        for ($i = 32; $i <= 126; $i++) {//Ponemos como validos todos los caracteres imprimibles.
            if ($i != 124) {//la barra vertical no es permitida
                array_push($validos, $i);
            }
        }
        //Agregamos caracteres comunmente usados
        array_push($validos, 164);
        array_push($validos, 165); //letra min. y mayus Ñ
        array_push($validos, 168); //Signo interrogacion abierto
        array_push($validos, 173); //Signo admiracion abierto
        //Agregamos vocales con acentos
        array_push($validos, 160);
        array_push($validos, 181);
        array_push($validos, 195);
        array_push($validos, 129); //letra min. y mayus. a
        array_push($validos, 130);
        array_push($validos, 144);
        array_push($validos, 137); //letra min. y mayus. e
        array_push($validos, 161);
        array_push($validos, 214);
        array_push($validos, 141); //letra min. y mayus. i
        array_push($validos, 162);
        array_push($validos, 224);
        array_push($validos, 147); //letra min. y mayus. o
        array_push($validos, 163);
        array_push($validos, 233);
        array_push($validos, 154); //letra min. y mayus. u
        array_push($validos, 169); //Marca Registrada
        array_push($validos, 179); //Línea simple vertical de recuadro gráfico
        array_push($validos, 186); //Líneas doble vertical de recuadro gráfico
        return $validos;
    }

    public function getNumCtaPago() {
        return $this->NumCtaPago;
    }

    public function setNumCtaPago($NumCtaPago) {
        $this->NumCtaPago = $NumCtaPago;
    }

    public function getSello() {
        return $this->Sello;
    }

    public function getCadenaOriginal() {
        return $this->CadenaOriginal;
    }

    public function setSello($Sello) {
        $this->Sello = $Sello;
    }

    public function setCadenaOriginal($CadenaOriginal) {
        $this->CadenaOriginal = $CadenaOriginal;
    }

    public function getFolio() {
        return $this->Folio;
    }

    public function setFolio($Folio) {
        $this->Folio = $Folio;
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

    public function getFormaPago() {
        return $this->FormaPago;
    }

    public function setFormaPago($FormaPago) {
        $this->FormaPago = $FormaPago;
    }

    public function getMetodoPago() {
        return $this->MetodoPago;
    }

    public function setMetodoPago($MetodoPago) {
        $this->MetodoPago = $MetodoPago;
    }

    public function getIdFactura() {
        return $this->IdFactura;
    }

    public function getRFCReceptor() {
        return $this->RFCReceptor;
    }

    public function getRFCEmisor() {
        return $this->RFCEmisor;
    }

    public function getFechaFacturacion() {
        return $this->FechaFacturacion;
    }

    public function getSerie() {
        return $this->Serie;
    }

    public function getFacturaXML() {
        return $this->FacturaXML;
    }

    public function getPathXML() {
        return $this->PathXML;
    }

    public function getPeriodoFacturacion() {
        return $this->PeriodoFacturacion;
    }

    public function getEstadoFactura() {
        return $this->EstadoFactura;
    }

    public function getFechaModificacion() {
        return $this->FechaModificacion;
    }

    public function getTipoComprobante() {
        return $this->TipoComprobante;
    }

    public function getPathPDF() {
        return $this->PathPDF;
    }

    public function getFacturaEnviada() {
        return $this->FacturaEnviada;
    }

    public function getFacturaPagada() {
        return $this->FacturaPagada;
    }

    public function getFechaPago() {
        return $this->FechaPago;
    }

    public function getNumTransaccion() {
        return $this->NumTransaccion;
    }

    public function getTotal() {
        return $this->Total;
    }

    public function getCfdiXML() {
        return $this->cfdiXML;
    }

    public function getCfdiTimbrado() {
        return $this->cfdiTimbrado;
    }

    public function getCfdiRespPac() {
        return $this->cfdiRespPac;
    }

    public function getFolioFiscal() {
        return $this->folioFiscal;
    }

    public function getEstatusFactura() {
        return $this->EstatusFactura;
    }

    public function getCanceladaSAT() {
        return $this->CanceladaSAT;
    }

    public function getPendienteCancelar() {
        return $this->PendienteCancelar;
    }

    public function getUsuarioEnvio() {
        return $this->UsuarioEnvio;
    }

    public function getFechaEnvio() {
        return $this->FechaEnvio;
    }

    public function setIdFactura($IdFactura) {
        $this->IdFactura = $IdFactura;
    }

    public function setRFCReceptor($RFCReceptor) {
        $this->RFCReceptor = $RFCReceptor;
    }

    public function setRFCEmisor($RFCEmisor) {
        $this->RFCEmisor = $RFCEmisor;
    }

    public function setFechaFacturacion($FechaFacturacion) {
        $this->FechaFacturacion = $FechaFacturacion;
    }

    public function setSerie($Serie) {
        $this->Serie = $Serie;
    }

    public function setFacturaXML($FacturaXML) {
        $this->FacturaXML = $FacturaXML;
    }

    public function setPathXML($PathXML) {
        $this->PathXML = $PathXML;
    }

    public function setPeriodoFacturacion($PeriodoFacturacion) {
        $this->PeriodoFacturacion = $PeriodoFacturacion;
    }

    public function setEstadoFactura($EstadoFactura) {
        $this->EstadoFactura = $EstadoFactura;
    }

    public function setFechaModificacion($FechaModificacion) {
        $this->FechaModificacion = $FechaModificacion;
    }

    public function setTipoComprobante($TipoComprobante) {
        $this->TipoComprobante = $TipoComprobante;
    }

    public function setPathPDF($PathPDF) {
        $this->PathPDF = $PathPDF;
    }

    public function setFacturaEnviada($FacturaEnviada) {
        $this->FacturaEnviada = $FacturaEnviada;
    }

    public function setFacturaPagada($FacturaPagada) {
        $this->FacturaPagada = $FacturaPagada;
    }

    public function setFechaPago($FechaPago) {
        $this->FechaPago = $FechaPago;
    }

    public function setNumTransaccion($NumTransaccion) {
        $this->NumTransaccion = $NumTransaccion;
    }

    public function setTotal($Total) {
        $this->Total = $Total;
    }

    public function setCfdiXML($cfdiXML) {
        $this->cfdiXML = $cfdiXML;
    }

    public function setCfdiTimbrado($cfdiTimbrado) {
        $this->cfdiTimbrado = $cfdiTimbrado;
    }

    public function setCfdiRespPac($cfdiRespPac) {
        $this->cfdiRespPac = $cfdiRespPac;
    }

    public function setFolioFiscal($folioFiscal) {
        $this->folioFiscal = $folioFiscal;
    }

    public function setEstatusFactura($EstatusFactura) {
        $this->EstatusFactura = $EstatusFactura;
    }

    public function setCanceladaSAT($CanceladaSAT) {
        $this->CanceladaSAT = $CanceladaSAT;
    }

    public function setPendienteCancelar($PendienteCancelar) {
        $this->PendienteCancelar = $PendienteCancelar;
    }

    public function setUsuarioEnvio($UsuarioEnvio) {
        $this->UsuarioEnvio = $UsuarioEnvio;
    }

    public function setFechaEnvio($FechaEnvio) {
        $this->FechaEnvio = $FechaEnvio;
    }

    public function getIdEmpresa() {
        return $this->idEmpresa;
    }

    public function setIdEmpresa($idEmpresa) {
        $this->idEmpresa = $idEmpresa;
    }

    public function getId_TipoFactura() {
        return $this->Id_TipoFactura;
    }

    public function setId_TipoFactura($Id_TipoFactura) {
        $this->Id_TipoFactura = $Id_TipoFactura;
    }

    public function getIdDomicilioFiscal() {
        return $this->IdDomicilioFiscal;
    }

    public function setIdDomicilioFiscal($IdDomicilioFiscal) {
        $this->IdDomicilioFiscal = $IdDomicilioFiscal;
    }

    public function getTipoArrendamiento() {
        return $this->TipoArrendamiento;
    }

    public function setTipoArrendamiento($TipoArrendamiento) {
        $this->TipoArrendamiento = $TipoArrendamiento;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getFolio_respaldo() {
        return $this->folio_respaldo;
    }

    function setFolio_respaldo($folio_respaldo) {
        $this->folio_respaldo = $folio_respaldo;
    }

    function getNdc() {
        return $this->ndc;
    }

    function setNdc($ndc) {
        $this->ndc = $ndc;
    }

    function getFolioPendiente() {
        return $this->folioPendiente;
    }

    function setFolioPendiente($folioPendiente) {
        $this->folioPendiente = $folioPendiente;
    }

    function getIdFolioPendiente() {
        return $this->idFolioPendiente;
    }

    function setIdFolioPendiente($idFolioPendiente) {
        $this->idFolioPendiente = $idFolioPendiente;
    }
    
    function getRFCEmisorFacturacion() {
        return $this->RFCEmisorFacturacion;
    }

    function setRFCEmisorFacturacion($RFCEmisorFacturacion) {
        $this->RFCEmisorFacturacion = $RFCEmisorFacturacion;
    }
    
    function getMensaje() {
        return $this->mensaje;
    }

    function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }
    
    function getDescripcionMetodoPago() {
        return $this->DescripcionMetodoPago;
    }

    function setDescripcionMetodoPago($DescripcionMetodoPago) {
        $this->DescripcionMetodoPago = $DescripcionMetodoPago;
    }
    
    function getMostrarSerie() {
        return $this->MostrarSerie;
    }

    function setMostrarSerie($MostrarSerie) {
        $this->MostrarSerie = $MostrarSerie;
    }
    
    function getIdSerie() {
        return $this->IdSerie;
    }

    function setIdSerie($IdSerie) {
        $this->IdSerie = $IdSerie;
    }
}

?>