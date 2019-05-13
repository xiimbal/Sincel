<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

class CuentaBancaria {
    private $idCuentaBancaria;
    private $banco;
    private $noCuenta;
    private $tipoCuenta;
    private $RFC;
    private $clave;
    private $sucursal;
    private $ejecutivoCuenta;
    private $telEjecutivo;
    private $correoEjecutivo;
    private $activo;
    private $descripcion;
    private $fechaCorte;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $usuarioUltimaModificacion;
    private $fechaUltimaModificacion;
    private $pantalla;
    
    function cuentaBan($idCuentaBancaria, $banco, $noCuenta, $tipoCuenta, $RFC, $clave, $sucursal, $ejecutivoCuenta, $telEjecutivo, $correoEjecutivo, $activo, $descripcion, $idUsuario, $usuarioCreacion, $fechaCreacion, $usuarioUltimaModificacion, $fechaUltimaModificacion) {
        $this->idCuentaBancaria = $idCuentaBancaria;
        $this->banco = $banco;
        $this->noCuenta = $noCuenta;
        $this->tipoCuenta = $tipoCuenta;
        $this->RFC = $RFC;
        $this->clave = $clave;
        $this->sucursal = $sucursal;
        $this->ejecutivoCuenta = $ejecutivoCuenta;
        $this->telEjecutivo = $telEjecutivo;
        $this->correoEjecutivo = $correoEjecutivo;
        $this->activo = $activo;
        $this->descripcion = $descripcion;
        $this->idUsuario = $idUsuario;
        $this->usuarioCreacion = $usuarioCreacion;
        $this->fechaCreacion = $fechaCreacion;
        $this->usuarioUltimaModificacion = $usuarioUltimaModificacion;
        $this->fechaUltimaModificacion = $fechaUltimaModificacion;
    }
    
    public function getRegistroById($id) {
        $consulta = ("SELECT c.*, b.Nombre from c_cuentaBancaria c LEFT JOIN c_banco b ON c.IdBanco = b.idBanco WHERE c.idCuentaBancaria = $id;");
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idCuentaBancaria = $rs['idCuentaBancaria'];
            $this->banco = $rs['Nombre'];
            $this->noCuenta = $rs['noCuenta'];
            $this->tipoCuenta = $rs['tipoCuenta'];
            $this->RFC = $rs['RFC'];
            $this->clave = $rs['clave'];
            $this->sucursal = $rs['sucursal'];
            $this->ejecutivoCuenta = $rs['ejecutivoCuenta'];
            $this->telEjecutivo = $rs['telEjecutivo'];
            $this->correoEjecutivo = $rs['correoEjecutivo'];
            $this->fechaCorte = $rs['FechaCorte'];
            $this->activo = $rs['Activo'];
            $this->descripcion = $rs['descripcion'];
            $this->idUsuario = $rs['idUsuario'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            $this->fechaCreacion = $rs['FechaCreacion'];
            $this->usuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->fechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            return true;
        }
        return false;
    }
    
    public function deleteRegistro($idCuentaBancaria){
        $consulta = "DELETE FROM `c_cuentaBancaria` WHERE idCuentaBancaria = $idCuentaBancaria;";        
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function newRegistro() {    
        $vcorreo = null;
        $tel = null;
        $clave = null;
        $descripcion = null;
        if(!($this->correoEjecutivo == "" || !isset($this->correoEjecutivo))){
            $vcorreo = "correoEjecutivo, ";
            $ncorreo = "'" . $this->correoEjecutivo . "',";
        }
        if(!($this->telEjecutivo == "" || !isset($this->telEjecutivo))){
            $tel = "telEjecutivo, ";
            $ntel = "'" . $this->telEjecutivo . "',";
        }
        if(!($this->clave == "" || !isset($this->clave))){
            $clave = "clave, ";
            $nclave = "'" . $this->clave . "',";
        }
        if(!($this->descripcion == "" || !isset($this->descripcion))){
            $descripcion = "descripcion, ";
            $ndescripcion = "'" . $this->descripcion . "',";
        }
        $consulta = ("INSERT INTO c_cuentaBancaria(idBanco, noCuenta , tipoCuenta, RFC,". $clave. " sucursal, ejecutivoCuenta,".$tel. " ". $vcorreo. " 
             Activo, FechaCorte, " . $descripcion . " UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES($this->banco,'$this->noCuenta', '$this->tipoCuenta', '$this->RFC', $nclave '$this->sucursal', '$this->ejecutivoCuenta', $ntel $ncorreo "
                . "$this->activo, $this->fechaCorte, $ndescripcion '$this->usuarioCreacion',NOW(),'$this->usuarioUltimaModificacion',NOW(),'$this->pantalla');");
        $catalogo = new Catalogo(); 
        $this->idCuentaBancaria = $catalogo->insertarRegistro($consulta);
        if ($this->idCuentaBancaria !=NULL && $this->idCuentaBancaria !=0) {                        
            return true;
        }        
        return false;
    }
    
    public function editRegistro() {
        $vcorreo = null;
        $tel = null;
        $clave = null;
        $descripcion = null;
        if(!($this->correoEjecutivo == "" || !isset($this->correoEjecutivo))){
            $vcorreo = "correoEjecutivo = '$this->correoEjecutivo', ";
        }
        if(!($this->telEjecutivo == "" || !isset($this->telEjecutivo))){
            $tel = "telEjecutivo = '$this->telEjecutivo',";
        }
        if(!($this->clave == "" || !isset($this->clave))){
            $clave = "clave = '$this->clave',";
        }
        if(!($this->descripcion == "" || !isset($this->descripcion))){
            $descripcion = "descripcion = '$this->descripcion', ";
        }
        $consulta = ("UPDATE c_cuentaBancaria SET noCuenta = '$this->noCuenta',tipoCuenta = '$this->tipoCuenta' , RFC = '$this->RFC', sucursal = '$this->sucursal',
                        Activo = $this->activo, FechaCorte = $this->fechaCorte, UsuarioUltimaModificacion = '$this->usuarioUltimaModificacion',  ejecutivoCuenta = '$this->ejecutivoCuenta',
                        " . $tel ." ". $vcorreo ." " . $descripcion . " ". $clave . "
                        FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE idCuentaBancaria = " . $this->idCuentaBancaria . ";");
        echo $consulta;
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function getIdCuentaBancaria() {
        return $this->idCuentaBancaria;
    }

    function getBanco() {
        return $this->banco;
    }

    function getNoCuenta() {
        return $this->noCuenta;
    }

    function getTipoCuenta() {
        return $this->tipoCuenta;
    }

    function getRFC() {
        return $this->RFC;
    }

    function getClave() {
        return $this->clave;
    }

    function getSucursal() {
        return $this->sucursal;
    }

    function getEjecutivoCuenta() {
        return $this->ejecutivoCuenta;
    }

    function getTelEjecutivo() {
        return $this->telEjecutivo;
    }

    function getCorreoEjecutivo() {
        return $this->correoEjecutivo;
    }

    function getActivo() {
        return $this->activo;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    function getUsuarioUltimaModificacion() {
        return $this->usuarioUltimaModificacion;
    }

    function getFechaUltimaModificacion() {
        return $this->fechaUltimaModificacion;
    }

    function setIdCuentaBancaria($idCuentaBancaria) {
        $this->idCuentaBancaria = $idCuentaBancaria;
    }

    function setBanco($banco) {
        $this->banco = $banco;
    }

    function setNoCuenta($noCuenta) {
        $this->noCuenta = $noCuenta;
    }

    function setTipoCuenta($tipoCuenta) {
        $this->tipoCuenta = $tipoCuenta;
    }

    function setRFC($RFC) {
        $this->RFC = $RFC;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setSucursal($sucursal) {
        $this->sucursal = $sucursal;
    }

    function setEjecutivoCuenta($ejecutivoCuenta) {
        $this->ejecutivoCuenta = $ejecutivoCuenta;
    }

    function setTelEjecutivo($telEjecutivo) {
        $this->telEjecutivo = $telEjecutivo;
    }

    function setCorreoEjecutivo($correoEjecutivo) {
        $this->correoEjecutivo = $correoEjecutivo;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
    
    function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    function setUsuarioUltimaModificacion($usuarioUltimaModificacion) {
        $this->usuarioUltimaModificacion = $usuarioUltimaModificacion;
    }

    function setFechaUltimaModificacion($fechaUltimaModificacion) {
        $this->fechaUltimaModificacion = $fechaUltimaModificacion;
    }

    function getPantalla() {
        return $this->pantalla;
    }

    function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }
    
    function getFechaCorte() {
        return $this->fechaCorte;
    }

    function setFechaCorte($fechaCorte) {
        $this->fechaCorte = $fechaCorte;
    }

}
