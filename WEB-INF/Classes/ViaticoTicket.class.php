<?php

include_once("Catalogo.class.php");
include_once("NotaTicket.class.php");
include_once("TipoViatico.class.php");
include_once("ServiciosVE.class.php");
include_once("Estado.class.php");

class ViaticoTicket {
    private $idTicketViatico;
    private $idTicket;
    private $idTipoViatico;
    private $idUsuario;
    private $fecha;
    private $costo;
    private $comentario;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    private $CobrarSiNo;
    private $PagarSiNo;    

    function insertarNuevoViatico($IdNotaTicket){
        $catalogo = new Catalogo();
        $nota = new NotaTicket();
        $tipoViatico = new TipoViatico();
        $servicio = new ServiciosVE();
        
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
            $nota->setEmpresa($this->empresa);
            $tipoViatico->setEmpresa($this->empresa);
            $servicio->setEmpresa($this->empresa);
        }
        $servicio->setIdTicket($this->idTicket);
                
        if(empty($IdNotaTicket)){            
            $nota->setIdTicket($this->idTicket);        
            if($tipoViatico->getRegistroById($this->idTipoViatico)){
                $nota->setDiagnostico("$".  number_format($this->costo)." de ".$tipoViatico->getNombre());
                if($tipoViatico->getIdEstado() != null && $tipoViatico->getIdEstado() != ""){                    
                    $nota->setIdEstatus($tipoViatico->getIdEstado());//Estado de notas del tipo de viatico especifico
                    $servicio->buscarServicioByEstatusNota($tipoViatico->getIdEstado());
                }else{                    
                    $nota->setIdEstatus(274);//Estado de notas de viaticos
                    $servicio->buscarServicioByEstatusNota(274);
                }
            }else{                
                $nota->setDiagnostico("$".  number_format($this->costo)." de viaticos");
                $nota->setIdEstatus(274);//Estado de notas de viaticos
                $servicio->buscarServicioByEstatusNota(274);
            }
            
            $nota->setIdTipoViatico($this->idTipoViatico);
            $nota->setUsuarioSolicitud($this->UsuarioCreacion);
            $nota->setMostrarCliente(1);
            $nota->setActivo(1);
            $nota->setUsuarioCreacion($this->UsuarioCreacion);
            $nota->setUsuarioModificacion($this->UsuarioUltimaModificacion);
            $nota->setPantalla($this->Pantalla);
            $nota->setLatitud("NULL"); $nota->setLongitud("NULL"); $nota->setMinutosDefase("NULL");
            if($nota->newRegistro()){   
                $IdNotaTicket = $nota->getIdNota();
            }else{
                return false;
            }
        }  else {  
            if($tipoViatico->getRegistroById($this->idTipoViatico)){                
                if($tipoViatico->getIdEstado() != null && $tipoViatico->getIdEstado() != ""){                    
                    $servicio->buscarServicioByEstatusNota($tipoViatico->getIdEstado());
                }else{                                        
                    $servicio->buscarServicioByEstatusNota(274);
                }
            }else{                                
                $servicio->buscarServicioByEstatusNota(274);
            }
            $nota->getRegistroById($IdNotaTicket);
            if(!$nota->updateTipoViatico($this->idTipoViatico, $IdNotaTicket)){
                
            }
        }
        
        if(!empty($IdNotaTicket)){
            //Para saber si se debe validar o no, dependiendo la configuración del estado, debemos revisarlo.
            $estado = new Estado();
            $estado->getRegistroById($nota->getIdEstatus());//Cargamos el objeto para saber si hay que validar o cobrar
            $generarPrefactura = false;
            $comentario = "";
            if($estado->getFlagValidacion() == "1"){//Hay que validar
                $servicio->setValidado(0);
            }else if($estado->getFlagCobrar() == "1"){//Sólo pidieron cobrar
                $servicio->setValidado(1);
                $generarPrefactura = true;
                $comentario = "No se solicitó validar, pasa directamente a facturación.";
            }else{//Ninguno de los dos casos, no hay que validar ni cobrar.
                $servicio->setValidado(1);
                $generarPrefactura = false;
                $comentario = "No se solicitó ni validar ni cobrar. Se muestra solamente.";
            }            
            $servicio->setCantidad($this->costo);//Este va a ser editado cuando esten validando
            $servicio->setFecha($this->fecha);
            $servicio->setIdTicket($this->idTicket);
            $servicio->setUsuarioCreacion($this->UsuarioCreacion);
            $servicio->setUsuarioUltimaModificacion($this->UsuarioUltimaModificacion);
            $servicio->setPantalla($this->Pantalla);
            $servicio->setIdNotaTicket($IdNotaTicket);
            $servicio->setPagarSiNo($this->PagarSiNo);
            $servicio->setCobrarSiNo($this->CobrarSiNo);
            $servicio->setIdUsuario($this->idUsuario);
            $servicio->setComentario($this->comentario);
            $servicio->setCantidadOriginal($this->costo);//Este no volverá a ser editado.
            if($servicio->newRegistroKServicio()){
                //Si se tiene que generar ya la prefactura, osea porque no pidieron que se validará, entonces se genera otra nota 
                //con el estado , que es el de facturar
                if($generarPrefactura){
                    $nota->setIdEstatus(55);//Sólo cambiamos el valor del id, los datos serán los mismitos.
                    $nota->setDiagnostico($comentario);
                    if($nota->newRegistro()){//Ya creamos la nueva nota, ahora solo hay que guardar este Id en el registro en k_serviciove
                        $servicio->setIdNotaTicketFacturar($nota->getIdNota());
                        if(!$servicio->guardarIdNotaFactura()){
                            echo "Error: ";
                            return false;
                        }
                    }else{
                        echo "Error: No se pudo mandar a prefacturar";
                        return false;
                    }
                }     
                return true;
            }
            return false;
        }
        return false;                
    }        
            
    function getIdTicketViatico() {
        return $this->idTicketViatico;
    }

    function getIdTicket() {
        return $this->idTicket;
    }

    function getIdTipoViatico() {
        return $this->idTipoViatico;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getCosto() {
        return $this->costo;
    }

    function getComentario() {
        return $this->comentario;
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

    function getEmpresa() {
        return $this->empresa;
    }

    function setIdTicketViatico($idTicketViatico) {
        $this->idTicketViatico = $idTicketViatico;
    }

    function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    function setIdTipoViatico($idTipoViatico) {
        $this->idTipoViatico = $idTipoViatico;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setCosto($costo) {
        $this->costo = $costo;
    }

    function setComentario($comentario) {
        $this->comentario = $comentario;
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

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    function getCobrarSiNo() {
        return $this->CobrarSiNo;
    }

    function getPagarSiNo() {
        return $this->PagarSiNo;
    }

    function setCobrarSiNo($CobrarSiNo) {
        $this->CobrarSiNo = $CobrarSiNo;
    }

    function setPagarSiNo($PagarSiNo) {
        $this->PagarSiNo = $PagarSiNo;
    }

    
}
