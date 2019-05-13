<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Proveedor {

    private $id_domicilio;
    private $clave;
    private $nombre;
    private $tipo;
    private $rfc;
    private $telefono;
    private $contacto;
    private $correo;
    private $formPago;
    private $cuentaBancaria;
    private $diasCredito;
    private $notificar;
    private $activo;
    private $calle;
    private $numExterior;
    private $numInterior;
    private $colonia;
    private $ciudad;
    private $delegacion;
    private $estado;
    private $pais;
    private $cp;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $UsuarioModificacion;
    private $fechaModificacion;
    private $pantalla;
    private $noiCliente;
    private $referencia;
    private $PorcentajeServicio;
    private $empresa;

    public function getUsuarios() {
        $consulta = ("SELECT * FROM `c_proveedor` ORDER BY NombreComercial");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function get_nombre_prov($id) {
        $consulta = ("SELECT p.NombreComercial FROM c_proveedor p WHERE p.ClaveProveedor='$id'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->nombre = $rs['NombreComercial'];
        }
        return $this->nombre;
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT *,p.Activo AS activo FROM c_proveedor p LEFT JOIN c_domicilio_proveedor dp ON p.ClaveProveedor=dp.ClaveProveedor WHERE p.ClaveProveedor='$id'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->clave = $id;
            $this->nombre = $rs['NombreComercial'];
            $this->rfc = $rs['RFC'];
            $this->tipo = $rs['IdTipoProveedor'];
            $this->telefono = $rs['Telefono'];
            $this->contacto = $rs['Contacto'];
            $this->correo = $rs['Correo'];
            $this->formPago = $rs['FormaPago'];
            $this->cuentaBancaria = $rs['CuentaBancaria'];
            $this->diasCredito = $rs['DiasCredito'];
            $this->notificar = $rs['NotificacionPagar'];
            $this->calle = $rs['Calle'];
            $this->numExterior = $rs['NoExterior'];
            $this->numInterior = $rs['NoInterior'];
            $this->colonia = $rs['Colonia'];
            $this->ciudad = $rs['Ciudad'];
            $this->delegacion = $rs['Delegacion'];
            $this->estado = $rs['Estado'];
            $this->pais = $rs['Pais'];
            $this->cp = $rs['cp'];
            $this->activo = $rs['activo'];
            $this->noiCliente = $rs['noClienteProveedor'];
            $this->referencia = $rs['referencia'];
            $this->PorcentajeServicio = $rs['PorcentajeServicio'];
            return true;
        }
        return false;
    }
    
    public function getRegistroByRFC() {
        $consulta = ("SELECT *, p.ClaveProveedor AS clave "
                . "FROM c_proveedor p "
                . "LEFT JOIN c_domicilio_proveedor dp ON p.ClaveProveedor=dp.ClaveProveedor "
                . "WHERE p.RFC='$this->rfc';");        
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->clave = $rs['clave'];            
            $this->nombre = $rs['NombreComercial'];
            $this->rfc = $rs['RFC'];
            $this->tipo = $rs['IdTipoProveedor'];
            $this->telefono = $rs['Telefono'];
            $this->contacto = $rs['Contacto'];
            $this->correo = $rs['Correo'];
            $this->formPago = $rs['FormaPago'];
            $this->cuentaBancaria = $rs['CuentaBancaria'];
            $this->diasCredito = $rs['DiasCredito'];
            $this->notificar = $rs['NotificacionPagar'];
            $this->calle = $rs['Calle'];
            $this->numExterior = $rs['NoExterior'];
            $this->numInterior = $rs['NoInterior'];
            $this->colonia = $rs['Colonia'];
            $this->ciudad = $rs['Ciudad'];
            $this->delegacion = $rs['Delegacion'];
            $this->estado = $rs['Estado'];
            $this->pais = $rs['Pais'];
            $this->cp = $rs['cp'];
            $this->activo = $rs['Activo'];
            $this->noiCliente = $rs['noClienteProveedor'];
            $this->referencia = $rs['referencia'];
            $this->PorcentajeServicio = $rs['PorcentajeServicio'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $tel = "'$this->telefono'";
        $cont = "'$this->contacto'";
        $mail = "'$this->correo'";
        $forma = "'$this->formPago'";
        $cta = $this->cuentaBancaria;
        $dias = "'$this->diasCredito'";
        $noClient = "'$this->noiCliente'";
        if ($this->telefono == "") {
            $tel = "NULL";
        }
        if ($this->contacto == "") {
            $cont = "NULL";
        }
        if ($this->correo == "") {
            $mail = "NULL";
        }
        if ($this->formPago == "0") {
            $forma = "NULL";
        }
        if ($this->cuentaBancaria == "") {
            $cta = "NULL";
        }
        if ($this->diasCredito == "") {
            $dias = "NULL";
        }
        if ($this->noiCliente == "") {
            $noClient = "NULL";
        }
        
        if(!isset($this->PorcentajeServicio) || empty($this->PorcentajeServicio)){
            $this->PorcentajeServicio = "70";
        }

        $consulta = ("INSERT INTO c_proveedor (ClaveProveedor,NombreComercial,RFC,IdTipoProveedor,noClienteProveedor,Telefono,Contacto,Correo,
            FormaPago,CuentaBancaria,DiasCredito,NotificacionPagar,PorcentajeServicio,
            Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla, referencia) 
            VALUES('" . $this->clave . "','" . $this->nombre . "','" . $this->rfc . "','" . $this->tipo . "',$noClient,$tel,$cont,$mail,
            $forma,$cta,$dias,$this->notificar,$this->PorcentajeServicio,
            $this->activo,'" . $this->usuarioCreacion . "',now(),'$this->UsuarioModificacion',now(),'$this->pantalla','$this->referencia')");
        //echo $consulta;
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $tel = "'$this->telefono'";
        $cont = "'$this->contacto'";
        $mail = "'$this->correo'";
        $forma = "'$this->formPago'";
        $cta = $this->cuentaBancaria;
        $dias = "'$this->diasCredito'";
        $noClient = "'$this->noiCliente'";
        if ($this->telefono == "") {
            $tel = "NULL";
        }
        if ($this->contacto == "") {
            $cont = "NULL";
        }
        if ($this->correo == "") {
            $mail = "NULL";
        }
        if ($this->formPago == "0") {
            $forma = "NULL";
        }
        if ($this->diasCredito == "") {
            $dias = "NULL";
        }
        if ($this->noiCliente == "") {
            $noClient = "NULL";
        }
        if ($this->cuentaBancaria == "") {
            $cta = "NULL";
        }
                
        if(!isset($this->PorcentajeServicio) || empty($this->PorcentajeServicio)){
            $this->PorcentajeServicio = "NULL";
        }
        
        $consulta = ("UPDATE c_proveedor SET NombreComercial = '$this->nombre', RFC = '$this->rfc', IdTipoProveedor = $this->tipo, Activo = $this->activo,
            Telefono=$tel,Contacto=$cont,Correo=$mail,FormaPago=$forma,CuentaBancaria=$cta,DiasCredito=$dias,NotificacionPagar=$this->notificar,
            noClienteProveedor=$noClient,PorcentajeServicio=$this->PorcentajeServicio, 
            UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->pantalla', referencia = '$this->referencia' 
            WHERE ClaveProveedor = '" . $this->clave . "';");
        
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_proveedor WHERE ClaveProveedor = '" . $this->clave . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newDomicilio() {
        $interior = "'$this->numInterior'";
        if ($this->numInterior == "") {
            $interior = "NULL";
        }
        $consulta = ("INSERT INTO c_domicilio_proveedor (IdDomicilioProv,ClaveProveedor,Calle,NoExterior,NoInterior,Colonia,Ciudad,Delegacion,Estado,Pais,cp,Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                            VALUES(0,'" . $this->clave . "','" . $this->calle . "','" . $this->numExterior . "',$interior,'" . $this->colonia . "','" . $this->ciudad . "','" . $this->delegacion . "','" . $this->estado . "',
                            '" . $this->pais . "','" . $this->cp . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'$this->UsuarioModificacion',now(),'$this->pantalla')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }        

    public function editDomicilio() {
        $catalogo = new Catalogo();
        $consulta = "SELECT * FROM c_domicilio_proveedor WHERE ClaveProveedor = '" . $this->clave . "'";
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) > 0){//Si ya tiene domicilio registrado
            $interior = "'$this->numInterior'";
            if ($this->numInterior == "") {
                $interior = "NULL";
            }
            $consulta = "UPDATE c_domicilio_proveedor SET Calle = '$this->calle', NoExterior = '$this->numExterior', NoInterior = $interior, Colonia = '" . $this->colonia . "',
                            Ciudad='" . $this->ciudad . "',Delegacion='" . $this->delegacion . "',Estado='" . $this->estado . "',Pais='" . $this->pais . "',cp='" . $this->cp . "',
                            Activo=$this->activo,UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->pantalla'
                            WHERE ClaveProveedor = '" . $this->clave . "';";                
        }else{
            return $this->newDomicilio();
        }
        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteDomicilio() {
        $consulta = ("DELETE FROM c_domicilio_proveedor WHERE ClaveProveedor = '" . $this->clave . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getDomicilioByOc($oc){
        $consulta = ("SELECT *
            FROM c_domicilio_proveedor dp
            WHERE dp.ClaveProveedor = 
                (SELECT FacturaEmisor FROM c_orden_compra
                WHERE Id_orden_compra = $oc); ");        
        $catalogo = new Catalogo();
        if(isset($this->empresa) && !empty($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->calle = $rs['Calle'];
            $this->numExterior = $rs['NoExterior'];
            $this->numInterior = $rs['NoInterior'];
            $this->colonia = $rs['Colonia'];
            $this->ciudad = $rs['Ciudad'];
            $this->delegacion = $rs['Delegacion'];
            $this->estado = $rs['Estado'];
            $this->pais = $rs['Pais'];
            $this->cp = $rs['cp'];
        }
            
    }
    
    public function getId_domicilio() {
        return $this->id_domicilio;
    }

    public function getClave() {
        return $this->clave;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getRfc() {
        return $this->rfc;
    }

    public function getTelefono() {
        return $this->telefono;
    }

    public function getContacto() {
        return $this->contacto;
    }

    public function getCorreo() {
        return $this->correo;
    }

    public function getFormPago() {
        return $this->formPago;
    }

    public function getCuentaBancaria() {
        return $this->cuentaBancaria;
    }

    public function getDiasCredito() {
        return $this->diasCredito;
    }

    public function getNotificar() {
        return $this->notificar;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getCalle() {
        return $this->calle;
    }

    public function getNumExterior() {
        return $this->numExterior;
    }

    public function getNumInterior() {
        return $this->numInterior;
    }

    public function getColonia() {
        return $this->colonia;
    }

    public function getCiudad() {
        return $this->ciudad;
    }

    public function getDelegacion() {
        return $this->delegacion;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getPais() {
        return $this->pais;
    }

    public function getCp() {
        return $this->cp;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setId_domicilio($id_domicilio) {
        $this->id_domicilio = $id_domicilio;
    }

    public function setClave($clave) {
        $this->clave = $clave;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    public function setContacto($contacto) {
        $this->contacto = $contacto;
    }

    public function setCorreo($correo) {
        $this->correo = $correo;
    }

    public function setFormPago($formPago) {
        $this->formPago = $formPago;
    }

    public function setCuentaBancaria($cuentaBancaria) {
        $this->cuentaBancaria = $cuentaBancaria;
    }

    public function setDiasCredito($diasCredito) {
        $this->diasCredito = $diasCredito;
    }

    public function setNotificar($notificar) {
        $this->notificar = $notificar;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setCalle($calle) {
        $this->calle = $calle;
    }

    public function setNumExterior($numExterior) {
        $this->numExterior = $numExterior;
    }

    public function setNumInterior($numInterior) {
        $this->numInterior = $numInterior;
    }

    public function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    public function setCiudad($ciudad) {
        $this->ciudad = $ciudad;
    }

    public function setDelegacion($delegacion) {
        $this->delegacion = $delegacion;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setPais($pais) {
        $this->pais = $pais;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getNoiCliente() {
        return $this->noiCliente;
    }

    public function setNoiCliente($noiCliente) {
        $this->noiCliente = $noiCliente;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function getPorcentajeServicio() {
        return $this->PorcentajeServicio;
    }

    function setPorcentajeServicio($PorcentajeServicio) {
        $this->PorcentajeServicio = $PorcentajeServicio;
    }
    
    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}
