<?php

include_once ("Catalogo.class.php");

/**
 * Description of Evento
 *
 * @author MAGG
 */
class Evento {
    private $IdEvento;
    private $ClaveCliente;
    private $Nombre;
    private $Descripcion;
    private $FechaInicio;
    private $FechaFin;
    private $Imagen;
    private $Calle;
    private $NoExterior;
    private $NoInterior;
    private $Colonia;
    private $Ciudad;
    private $Estado;
    private $Delegacion;
    private $Pais;
    private $CodigoPostal;
    private $Latitud;
    private $Longitud;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_evento WHERE IdEvento = '" . $id . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdEvento = $rs['IdEvento'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->Nombre = $rs['Nombre'];
            $this->Descripcion = $rs['Descripcion'];
            $this->FechaInicio = $rs['FechaInicio'];
            $this->FechaFin = $rs['FechaFin'];
            $this->Imagen = $rs['Imagen'];
            $this->Calle = $rs['Calle'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->Colonia = $rs['Colonia'];
            $this->Ciudad = $rs['Ciudad'];
            $this->Estado = $rs['Estado'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Pais = $rs['Pais'];
            $this->CodigoPostal = $rs['CodigoPostal'];
            $this->Latitud = $rs['Latitud'];
            $this->Longitud = $rs['Longitud'];
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
    
    public function newRegistro(){
        if(!isset($this->Latitud) || empty($this->Latitud)){
            $this->Latitud = "NULL";
        }
        
        if(!isset($this->Longitud) || empty($this->Longitud)){
            $this->Longitud = "NULL";
        }
        $consulta = "INSERT INTO c_evento(IdEvento, ClaveCliente, Nombre, Descripcion, FechaInicio, FechaFin, Imagen, Calle, NoExterior, NoInterior, "
                . "Colonia, Ciudad, Estado, Delegacion, Pais, CodigoPostal, Latitud, Longitud, "
                . "Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) "
                . "VALUES(0,'$this->ClaveCliente','$this->Nombre','$this->Descripcion','$this->FechaInicio','$this->FechaFin','$this->Imagen','$this->Calle',"
                . "'$this->NoExterior','$this->NoInterior','$this->Colonia','$this->Ciudad','$this->Estado','$this->Delegacion','$this->Pais',"
                . "'$this->CodigoPostal',$this->Latitud,$this->Longitud,"
                . "$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
        
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdEvento = $catalogo->insertarRegistro($consulta);
        if ($this->IdEvento != NULL && $this->IdEvento != 0) {
            return true;
        }
        return false;
    }
    
    public function updateRegistro(){
        $consulta = "UPDATE c_evento SET ClaveCliente = '$this->ClaveCliente', Nombre = '$this->Nombre', Descripcion = '$this->Descripcion', "
                . "FechaInicio = '$this->FechaInicio', FechaFin='$this->FechaFin', Imagen='$this->Imagen', "
                . "Calle='$this->Calle', NoExterior = '$this->NoExterior', NoInterior = '$this->NoInterior', "
                . "Colonia='$this->Colonia', Ciudad='$this->Ciudad', Estado='$this->Estado', Delegacion='$this->Delegacion', Pais='$this->Pais', "
                . "CodigoPostal = '$this->CodigoPostal', Latitud=$this->Latitud, Longitud=$this->Longitud, "
                . "Activo=$this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla' "
                . "WHERE IdEvento = $this->IdEvento;";
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result == "1") {
            return true;
        }
        return false;
    }
    
    public function deleteRegistro(){
        $consulta = "DELETE FROM c_evento WHERE IdEvento = $this->IdEvento;";
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result == "1") {
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene los eventos vigentes registrados en el sistema
     * @return type
     */
    public function getEventosVigentes($ClaveCliente){
        $where = "";
        if(!empty($ClaveCliente)){
            $where = " c.ClaveCliente = '$ClaveCliente' AND ";
        }
        
        $consulta = "SELECT c.NombreRazonSocial, e.* 
            FROM `c_evento` AS e
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = e.ClaveCliente
            WHERE $where e.FechaInicio <= NOW() AND e.FechaFin >= NOW() AND e.Activo = 1 AND c.Activo = 1;";
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    function getIdEvento() {
        return $this->IdEvento;
    }

    function getClaveCliente() {
        return $this->ClaveCliente;
    }

    function getNombre() {
        return $this->Nombre;
    }

    function getDescripcion() {
        return $this->Descripcion;
    }

    function getFechaInicio() {
        return $this->FechaInicio;
    }

    function getFechaFin() {
        return $this->FechaFin;
    }

    function getImagen() {
        return $this->Imagen;
    }

    function getCalle() {
        return $this->Calle;
    }

    function getNoExterior() {
        return $this->NoExterior;
    }

    function getNoInterior() {
        return $this->NoInterior;
    }

    function getColonia() {
        return $this->Colonia;
    }

    function getCiudad() {
        return $this->Ciudad;
    }

    function getEstado() {
        return $this->Estado;
    }

    function getDelegacion() {
        return $this->Delegacion;
    }

    function getPais() {
        return $this->Pais;
    }

    function getCodigoPostal() {
        return $this->CodigoPostal;
    }

    function getLatitud() {
        return $this->Latitud;
    }

    function getLongitud() {
        return $this->Longitud;
    }

    function getActivo() {
        return $this->Activo;
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

    function setIdEvento($IdEvento) {
        $this->IdEvento = $IdEvento;
    }

    function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }

    function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    function setFechaInicio($FechaInicio) {
        $this->FechaInicio = $FechaInicio;
    }

    function setFechaFin($FechaFin) {
        $this->FechaFin = $FechaFin;
    }

    function setImagen($Imagen) {
        $this->Imagen = $Imagen;
    }

    function setCalle($Calle) {
        $this->Calle = $Calle;
    }

    function setNoExterior($NoExterior) {
        $this->NoExterior = $NoExterior;
    }

    function setNoInterior($NoInterior) {
        $this->NoInterior = $NoInterior;
    }

    function setColonia($Colonia) {
        $this->Colonia = $Colonia;
    }

    function setCiudad($Ciudad) {
        $this->Ciudad = $Ciudad;
    }

    function setEstado($Estado) {
        $this->Estado = $Estado;
    }

    function setDelegacion($Delegacion) {
        $this->Delegacion = $Delegacion;
    }

    function setPais($Pais) {
        $this->Pais = $Pais;
    }

    function setCodigoPostal($CodigoPostal) {
        $this->CodigoPostal = $CodigoPostal;
    }

    function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    function setLongitud($Longitud) {
        $this->Longitud = $Longitud;
    }

    function setActivo($Activo) {
        $this->Activo = $Activo;
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

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}
