<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

class MovimientoBancario {
    
    private $id_movimientoBancario;
    private $id_cuentaBancaria;
    private $fecha;
    private $descripcion;
    private $tipo;
    private $monto;
    private $referencia;
    private $pago;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function newRegistro() {    
        $pago = "";
        $rpago = "";
        if(isset($this->pago)){
            $pago = "id_pago, tipoConciliacion";
            $rpago = $this->pago.", 'A' , ";
        }
        $consulta = ("INSERT INTO c_movimientoBancario(id_cuentaBancaria, fecha, descripcion , tipo, monto, referencia, $pago 
             UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES($this->id_cuentaBancaria,'$this->fecha','$this->descripcion', '$this->tipo', $this->monto, '$this->referencia', $rpago "
                . "'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo(); 
        $this->id_movimientoBancario = $catalogo->insertarRegistro($consulta);
        if ($this->id_movimientoBancario !=NULL && $this->id_movimientoBancario !=0) {                        
            return true;
        }
        echo $consulta;
        return false;
    }
    
    public function editRegistro() {    
        $comentario = null;
        if(!($this->comentario == "" || !isset($this->comentario))){
            $comentario = "comentario = '" . $this->comentario . "',";
        }
        $consulta = ("UPDATE c_movimientoBancario SET factura = '$this->factura', tipo = '$this->tipo', total = $this->total, "
                . "noCuenta = $this->noCuenta, idBanco = $this->idBanco, fecha = '$this->fecha', referencia =  '$this->referencia' ,". $comentario. "
             UsuarioCreacion = '$this->UsuarioCreacion', FechaCreacion = NOW(), UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), "
                . "Pantalla = '$this->Pantalla' WHERE idMovimientoBancario = $this->idMovimientoBancario;");
        echo $consulta;
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }     
        return false;
    }
    
    function getId_cuentaBancaria() {
        return $this->id_cuentaBancaria;
    }

    function setId_cuentaBancaria($id_cuentaBancaria) {
        $this->id_cuentaBancaria = $id_cuentaBancaria;
    }
    
    function getId_movimientoBancario() {
        return $this->id_movimientoBancario;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getMonto() {
        return $this->monto;
    }

    function getReferencia() {
        return $this->referencia;
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

    function setId_movimientoBancario($id_movimientoBancario) {
        $this->id_movimientoBancario = $id_movimientoBancario;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setMonto($monto) {
        $this->monto = $monto;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
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

    function getPago() {
        return $this->pago;
    }

    function setPago($pago) {
        $this->pago = $pago;
    }

}
