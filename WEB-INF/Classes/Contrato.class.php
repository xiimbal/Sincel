<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once("Cliente.class.php");

/**
 * Description of Contrato
 *
 * @author samsung
 */
class Contrato {

    private $NoContrato;
    private $FechaFirma;
    private $FechaInicio;
    private $FechaTermino;
    private $ClaveCliente;
    private $TipoContrato;
    private $FormaPago;
    private $IdMetodoPago;
    private $IdFormaComprobantePago;
    private $IdUsoCFDI;
    private $DomicilioFiscal;
    private $RazonSocial;
    private $NumeroCuenta;
    private $IdBanco;
    private $IdCuentaBancaria;
    private $FacturarA;
    private $DiasCredito;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $domicilio;
    private $empresa;
    private $contratoLimbo = false;
    private $campos;
    private $valores;
    private $mostrar;

    public function getContratosVigentesByLocalidad($claveCC) {
        $consulta = "SELECT NoContrato, date(FechaInicio) AS FechaInicio, date(FechaTermino) AS FechaTermino FROM `c_contrato` AS c 
        INNER JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto ='$claveCC' AND cc.ClaveCliente= c.ClaveCliente AND c.Activo = 1 AND c.FechaTermino >= NOW();";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistroValidacion($claveCliente) {
        $consulta = ("SELECT * FROM `c_contrato` WHERE ClaveCliente = '$claveCliente' AND Activo = 1 AND FechaTermino >= NOW();");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getValorPorContrato($NoContrato) {
        $consulta = ("SELECT * FROM `k_contrato` WHERE NoContrato = '$NoContrato';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getRegistroValidacionVencidos($claveCliente) {
        $consulta = ("SELECT * FROM `c_contrato` WHERE ClaveCliente = '$claveCliente' AND Activo = 1;");        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistroById($clave) {
        $consulta = ("SELECT * FROM `c_contrato` WHERE NoContrato = '$clave';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->NoContrato = $rs['NoContrato'];
            $this->FechaFirma = $rs['FechaFirma'];
            $this->FechaInicio = $rs['FechaInicio'];
            $this->FechaTermino = $rs['FechaTermino'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->TipoContrato = $rs['TipoContrato'];
            $this->FormaPago = $rs['FormaPago'];
            $this->IdMetodoPago = $rs['IdMetodoPago'];
            $this->RazonSocial = $rs['RazonSocial'];
            $this->NumeroCuenta = $rs['NumeroCuenta'];
            $this->IdBanco = $rs['IdBanco'];
            $this->IdCuentaBancaria = $rs['IdCuentaBancaria'];
            $this->FacturarA = $rs['FacturarA'];
            $this->DiasCredito = $rs['DiasCredito'];
            $this->IdFormaComprobantePago = $rs['IdFormaComprobantePago'];
            $this->IdUsoCFDI = $rs['IdUsoCFDI'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function getdomicilioById($clave) {
        $consulta = ("SELECT * FROM `c_domicilio` WHERE ClaveEspecialDomicilio = '$clave' AND IdTipoDomicilio='3';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->domicilio = $rs['IdDomicilio'];
            return true;
        }
        return false;
    }

    /**
     * Registra un nuevo contrato con los parametro recibidos.
     * @param type $fechaInicio
     * @param type $FechaTermino
     * @param type $ClaveCliente
     * @param type $pantalla
     * @return type true en caso de hacerlo correctamente, false en caso contrario.
     */
    public function newRegistroDefault($fechaInicio, $FechaTermino, $ClaveCliente, $pantalla) {
        $this->FechaInicio = $fechaInicio;
        $this->FechaTermino = $FechaTermino;
        $this->ClaveCliente = $ClaveCliente;
        $this->Activo = 1;
        $this->FormaPago = 1;
        $cliente = new Cliente();
        if(isset($this->empresa)){
            $cliente->setEmpresa($this->empresa);
        }
        if ($cliente->getRegistroById($ClaveCliente)) {
            $this->RazonSocial = $cliente->getIdDatosFacturacionEmpresa();
        }
        if (isset($_SESSION['user'])) {
            $usuario = $_SESSION['user'];
        } else {
            $usuario = "kyocera";
        }
        $this->UsuarioCreacion = $usuario;
        $this->UsuarioUltimaModificacion = $usuario;
        $this->Pantalla = $pantalla;
        return $this->newRegistro();
    }

    public function newRegistro() {
        $consulta = ("SELECT MAX(CAST(NoContrato AS UNSIGNED)) AS maximo FROM `c_contrato`;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query2 = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query2)) {
            if($this->contratoLimbo)
            {
                $maximo = $this->NoContrato;
            }else{
                $maximo = (int) $rs['maximo'];
                if ($maximo == "" || $maximo == 0) {
                    $maximo = 1000;
                }
                    $maximo++;
            }

            if (isset($this->FormaPago) && $this->FormaPago != "") {
                $formaPago = "$this->FormaPago";
            } else {
                $formaPago = "null";
            }
            
            if (!isset($this->IdMetodoPago) || empty($this->IdMetodoPago)) {
                $this->IdMetodoPago = "null";
            } 
            
            if (!isset($this->IdFormaComprobantePago) || empty($this->IdFormaComprobantePago)) {
                $this->IdFormaComprobantePago = "null";
            } 
            
            if (!isset($this->IdUsoCFDI) || empty($this->IdUsoCFDI)) {
                $this->IdUsoCFDI = "null";
            } 
            if (isset($this->RazonSocial) && $this->RazonSocial != "") {
                $razonSocial = "'$this->RazonSocial'";
            } else {
                $razonSocial = "null";
            }
            
            if (isset($this->FacturarA) && $this->FacturarA != "") {
                $facturarA = "'$this->FacturarA'";
            } else {
                $facturarA = "null";
            }
            
            if(!isset($this->IdBanco) || empty($this->IdBanco)){
                $this->IdBanco = "NULL";
            }
            
            if(!isset($this->IdCuentaBancaria) || empty($this->IdCuentaBancaria)){
                $this->IdCuentaBancaria = "NULL";
            }

            $consulta = "INSERT INTO c_contrato(NoContrato, FechaFirma, FechaInicio, FechaTermino, ClaveCliente, NumeroCuenta, IdBanco, 
                IdCuentaBancaria, FacturarA,DiasCredito,Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,
                FormaPago,IdMetodoPago,IdFormaComprobantePago,IdUsoCFDI,RazonSocial) VALUES('$maximo','$this->FechaInicio',
                    '$this->FechaInicio','$this->FechaTermino','$this->ClaveCliente','$this->NumeroCuenta',$this->IdBanco,$this->IdCuentaBancaria, $facturarA,'$this->DiasCredito',$this->Activo,
                    '$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla',"
                    . "$formaPago,$this->IdMetodoPago,$this->IdFormaComprobantePago,$this->IdUsoCFDI,$razonSocial)";
            echo $consulta;
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                $this->NoContrato = $maximo;
                foreach ($this->campos as $key => $value){
                    $valor = $this->valores[$key];
                    $mostrarPDF = $this->mostrar[$key];
                    $query2 = $catalogo->obtenerLista("INSERT INTO k_contrato(NoContrato,campo,valor,mostrarPDF,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                        VALUES ('$this->NoContrato','$value','$valor',$mostrarPDF,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')");
                    //echo $query2;
                }
                return true;
            }
        }
        return false;
    }

    public function editRegistro() {
        if (isset($this->FacturarA) && $this->FacturarA != "") {
            $facturarA = "'$this->FacturarA'";
        } else {
            $facturarA = "null";
        }
        
        $banco = "";
        if(isset($this->IdBanco) && !empty($this->IdBanco)){
            $banco = "IdBanco = $this->IdBanco, ";
        }
        
        $cuentaBancaria = "";
        if(isset($this->IdCuentaBancaria) && !empty($this->IdCuentaBancaria)){
            $cuentaBancaria = "IdCuentaBancaria = $this->IdCuentaBancaria, ";
        }
        
        if (!isset($this->IdMetodoPago) || empty($this->IdMetodoPago)) {
            $this->IdMetodoPago = "null";
        } 

        if (!isset($this->IdFormaComprobantePago) || empty($this->IdFormaComprobantePago)) {
            $this->IdFormaComprobantePago = "null";
        } 

        if (!isset($this->IdUsoCFDI) || empty($this->IdUsoCFDI)) {
            $this->IdUsoCFDI = "null";
        }
        
        if (!isset($this->FormaPago) || empty($this->FormaPago)) {
            $this->FormaPago = "null";
        }
            
        $consulta = ("UPDATE c_contrato SET FechaInicio = '$this->FechaInicio', FechaTermino = '$this->FechaTermino',$banco $cuentaBancaria
            ClaveCliente = '$this->ClaveCliente', NumeroCuenta = '$this->NumeroCuenta', DiasCredito='$this->DiasCredito',
            IdMetodoPago = $this->IdMetodoPago, IdFormaComprobantePago = $this->IdFormaComprobantePago, IdUsoCFDI = $this->IdUsoCFDI,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FacturarA = $facturarA,Activo = $this->Activo,
            FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla',FormaPago=$this->FormaPago,RazonSocial='$this->RazonSocial'
            WHERE NoContrato = '$this->NoContrato';");
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            foreach ($this->campos as $key => $value){
                $valor = $this->valores[$key];
                $mostrarPDF = $this->mostrar[$key];
                $query2 = $catalogo->obtenerLista("INSERT INTO k_contrato(NoContrato,campo,valor,mostrarPDF,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                    VALUES ('$this->NoContrato','$value','$valor',$mostrarPDF,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')");
            }
            return true;
        }
        return false;
    }
    
    public function getCamposByNoContrato($NoContrato) {
        $consulta = "SELECT * FROM k_contrato WHERE NoContrato = '".$NoContrato."'";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function eliminarCampos(){
        $consulta = "DELETE FROM k_contrato WHERE NoContrato = '".$this->NoContrato."'";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if($query){
            return true;
        }
        return false;
    }

    public function getNombreClienteGrupoFacturacion(){
        $consulta = "SELECT cl.NombreRazonSocial, cl.RFC FROM c_contrato co
            INNER JOIN c_cliente AS cl ON co.FacturarA = cl.ClaveCliente
            WHERE co.NoContrato = '$this->NoContrato'";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($query) > 0){
            if($rs = mysql_fetch_array($query)){
                $this->FacturarA = $rs['NombreRazonSocial'];
                $this->RazonSocial = $rs['RFC'];
            }
            return true;
        }
        return false;
    }
    
    public function getRegistroValidacion2($claveCliente) {
        $consulta = ("SELECT * FROM `c_contrato` WHERE ClaveCliente = '$claveCliente' AND Activo = 1 AND FechaTermino >= NOW();");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($query)){
            $this->NoContrato = $rs['NoContrato'];
            $this->FechaFirma = $rs['FechaFirma'];
            $this->FechaInicio = $rs['FechaInicio'];
            $this->FechaTermino = $rs['FechaTermino'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->TipoContrato = $rs['TipoContrato'];
            $this->FormaPago = $rs['FormaPago'];
            $this->IdMetodoPago = $rs['IdMetodoPago'];
            $this->RazonSocial = $rs['RazonSocial'];
            $this->NumeroCuenta = $rs['NumeroCuenta'];
            $this->IdBanco = $rs['IdBanco'];
            $this->IdCuentaBancaria = $rs['IdCuentaBancaria'];
            $this->FacturarA = $rs['FacturarA'];
            $this->DiasCredito = $rs['DiasCredito'];
            $this->IdFormaComprobantePago = $rs['IdFormaComprobantePago'];
            $this->IdUsoCFDI = $rs['IdUsoCFDI'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    public function getFormaPago() {
        return $this->FormaPago;
    }

    public function getDomicilioFiscal() {
        return $this->DomicilioFiscal;
    }

    public function getRazonSocial() {
        return $this->RazonSocial;
    }

    public function setFormaPago($FormaPago) {
        $this->FormaPago = $FormaPago;
    }

    public function setDomicilioFiscal($DomicilioFiscal) {
        $this->DomicilioFiscal = $DomicilioFiscal;
    }

    public function setRazonSocial($RazonSocial) {
        $this->RazonSocial = $RazonSocial;
    }

    public function getNoContrato() {
        return $this->NoContrato;
    }

    public function setNoContrato($NoContrato) {
        $this->NoContrato = $NoContrato;
    }

    public function getFechaFirma() {
        return $this->FechaFirma;
    }

    public function setFechaFirma($FechaFirma) {
        $this->FechaFirma = $FechaFirma;
    }

    public function getFechaInicio() {
        return $this->FechaInicio;
    }

    public function setFechaInicio($FechaInicio) {
        $this->FechaInicio = $FechaInicio;
    }

    public function getFechaTermino() {
        return $this->FechaTermino;
    }

    public function setFechaTermino($FechaTermino) {
        $this->FechaTermino = $FechaTermino;
    }

    public function getClaveCliente() {
        return $this->ClaveCliente;
    }

    public function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }

    public function getTipoContrato() {
        return $this->TipoContrato;
    }

    public function setTipoContrato($TipoContrato) {
        $this->TipoContrato = $TipoContrato;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getDomicilio() {
        return $this->domicilio;
    }

    public function setDomicilio($domicilio) {
        $this->domicilio = $domicilio;
    }

    public function getNumeroCuenta() {
        return $this->NumeroCuenta;
    }

    public function setNumeroCuenta($NumeroCuenta) {
        $this->NumeroCuenta = $NumeroCuenta;
    }

    public function getDiasCredito() {
        return $this->DiasCredito;
    }

    public function setDiasCredito($DiasCredito) {
        $this->DiasCredito = $DiasCredito;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getContratoLimbo() {
        return $this->contratoLimbo;
    }

    function setContratoLimbo($contratoLimbo) {
        $this->contratoLimbo = $contratoLimbo;
    }
    
    function getCampos() {
        return $this->campos;
    }

    function getValores() {
        return $this->valores;
    }

    function setCampos($campos) {
        $this->campos = $campos;
    }

    function setValores($valores) {
        $this->valores = $valores;
    }
    
    function getMostrar() {
        return $this->mostrar;
    }

    function setMostrar($mostrar) {
        $this->mostrar = $mostrar;
    } 
    
    function getFacturarA() {
        return $this->FacturarA;
    }

    function setFacturarA($FacturarA) {
        $this->FacturarA = $FacturarA;
    }
    
    function getIdBanco() {
        return $this->IdBanco;
    }

    function setIdBanco($IdBanco) {
        $this->IdBanco = $IdBanco;
    }
    
    function getIdCuentaBancaria() {
        return $this->IdCuentaBancaria;
    }

    function setIdCuentaBancaria($IdCuentaBancaria) {
        $this->IdCuentaBancaria = $IdCuentaBancaria;
    }
    
    function getIdMetodoPago() {
        return $this->IdMetodoPago;
    }

    function getIdFormaComprobantePago() {
        return $this->IdFormaComprobantePago;
    }

    function getIdUsoCFDI() {
        return $this->IdUsoCFDI;
    }

    function setIdMetodoPago($IdMetodoPago) {
        $this->IdMetodoPago = $IdMetodoPago;
    }

    function setIdFormaComprobantePago($IdFormaComprobantePago) {
        $this->IdFormaComprobantePago = $IdFormaComprobantePago;
    }

    function setIdUsoCFDI($IdUsoCFDI) {
        $this->IdUsoCFDI = $IdUsoCFDI;
    }
}

?>
