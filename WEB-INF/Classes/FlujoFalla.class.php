<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of FlujoFalla
 *
 * @author MAGG
 */
class FlujoFalla {

    private $estado;
    private $area;
    private $activo;
    private $id;
    private $idFlujo;
    private $idEstado;
    private $mostrarClientes;
    private $mostrarContactos;
    private $orden;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $UsuarioModificacion;
    private $fechaModificacion;
    private $pantalla;
    private $idEscalamiento;
    private $tiempoEnvio;
    private $color;
    private $prioridad;
    private $mensaje;
    private $idEscalamientoCorreo;
    private $correo;
    private $enviado;
    private $IdEstadoTicket;
   
    private $FlagValidacion;
    private $FlagCobrar;
    
    public function getFlujosFalla() {
        $consulta = ("SELECT * FROM `k_flujoestado` WHERE IdFlujo = 2 ORDER BY IdEstado");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT e.IdEstado,e.Nombre AS estado,e.Activo,e.IdArea,fe.IdKFlujo,fe.IdFlujo,fe.IdEstado,fe.Orden  FROM c_estado e ,k_flujoestado fe WHERE fe.IdKFlujo = " . $id . " AND e.IdEstado=fe.IdEstado");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id = $id;
            $this->estado = $rs['estado'];
            $this->area = $rs['IdArea'];
            $this->activo = $rs['Activo'];
            $this->idFlujo = $rs['IdFlujo'];
            $this->idEstado = $rs['IdEstado'];
            $this->orden = $rs['Orden'];            
            return true;
        }        
        return false;
    }

    public function newRegistroEstado() {
        if($this->FlagValidacion != "1"){$this->FlagValidacion = "0";}
        if($this->FlagCobrar != "1"){$this->FlagCobrar = "0";}   
        $consulta = ("INSERT INTO c_estado(IdEstado,Nombre,IdArea,mostrarClientes, mostrarContactos, IdEstadoTicket, Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla, FlagValidacion, FlagCobrar) 
            VALUES(0,'$this->estado',$this->area,$this->mostrarClientes,$this->mostrarContactos, $this->IdEstadoTicket, $this->activo,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla', $this->FlagValidacion, $this->FlagCobrar)");        
        //echo $consulta;
        $catalogo = new Catalogo(); $this->idEstado = $catalogo->insertarRegistro($consulta);
        if ($this->idEstado!= NULL && $this->idEstado != 0) {
            return true;
        }        
        return false;
    }

    public function newRegistroEscalamiento(){
        $consulta = ("INSERT INTO c_escalamientoEstado(idEscalamiento,idEstado,tiempoEnvio,color, prioridad, mensaje,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,$this->idEstado,$this->tiempoEnvio,'$this->color',$this->prioridad,'$this->mensaje','$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla')");        
        $catalogo = new Catalogo(); $this->idEscalamiento = $catalogo->insertarRegistro($consulta);
        if ($this->idEscalamiento!= NULL && $this->idEscalamiento != 0) {
            return true;
        }        
        return false;
    }
    
    public function newRegistroCorreo(){
        $consulta = ("INSERT INTO c_escalamientoCorreo(idEscalamientoCorreo,idEscalamiento,correo,enviado, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,$this->idEscalamiento,'$this->correo',0,'$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla')");        
        $catalogo = new Catalogo(); $this->idEscalamientoCorreo = $catalogo->insertarRegistro($consulta);
        if ($this->idEscalamientoCorreo!= NULL && $this->idEscalamientoCorreo != 0) {
            return true;
        }        
        return false;
    }
    
    public function newRegistro() {
        $consulta = ("INSERT INTO k_flujoestado(IdKFlujo,IdFlujo,IdEstado,Orden,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,
            Pantalla) VALUES(0,$this->idFlujo,$this->idEstado,$this->orden,
                '$this->usuarioCreacion',now(),'$this->UsuarioModificacion',now(),'$this->pantalla')");        
        $catalogo = new Catalogo(); $this->id = $catalogo->insertarRegistro($consulta);
        if ($this->id!=NULL && $this->id != 0) {
            return true;
        }        
        return false;
    }

    public function editRegistro() {        
        if($this->FlagValidacion != "1"){$this->FlagValidacion = "0";}
        if($this->FlagCobrar != "1"){$this->FlagCobrar = "0";} 
        $consulta = ("UPDATE c_estado SET FlagValidacion = $this->FlagValidacion, FlagCobrar = $this->FlagCobrar, Nombre ='$this->estado', IdArea = $this->area, mostrarClientes = $this->mostrarClientes, mostrarContactos = $this->mostrarContactos, Activo=$this->activo, 
            UsuarioUltimaModificacion = '$this->UsuarioModificacion',FechaUltimaModificacion = now(), Pantalla = '$this->pantalla', IdEstadoTicket = $this->IdEstadoTicket 
            WHERE IdEstado = " . $this->idEstado . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistroFlujo() {
        $consulta = ("UPDATE k_flujoestado SET Orden =$this->orden, UsuarioUltimaModificacion = '$this->UsuarioModificacion',FechaUltimaModificacion = now(), Pantalla = '$this->pantalla' 
            WHERE IdKFlujo = " . $this->id . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistroEstado() {
        $consulta = ("DELETE FROM c_estado WHERE IdEstado = " . $this->idEstado . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistroEscalamientoByEstado(){
        $consulta = ("SELECT idEscalamiento FROM c_escalamientoEstado WHERE idEstado = " . $this->idEstado . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($query)){
            $idEscalamiento = $rs['idEscalamiento'];
            $borrarCorreos = ("DELETE FROM c_escalamientoCorreo WHERE idEscalamiento = ".$idEscalamiento. ";");
            $queryBorrar = $catalogo->obtenerLista($borrarCorreos);
        }
        $consulta = ("DELETE FROM c_escalamientoEstado WHERE idEstado = $this->idEstado;");
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function deleteRegistro() {
        $consulta = ("DELETE FROM k_flujoestado WHERE IdKFlujo = " . $this->id . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function deleteFlujoByEstado() {
    $consulta = ("DELETE FROM k_flujoestado WHERE IdEstado = '" . $this->idEstado . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getIdFlujo() {
        return $this->idFlujo;
    }

    public function setIdFlujo($idFlujo) {
        $this->idFlujo = $idFlujo;
    }

    public function getIdEstado() {
        return $this->idEstado;
    }

    public function setIdEstado($idEstado) {
        $this->idEstado = $idEstado;
    }

    public function getOrden() {
        return $this->orden;
    }

    public function setOrden($orden) {
        $this->orden = $orden;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function getArea() {
        return $this->area;
    }

    public function setArea($area) {
        $this->area = $area;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    function getMostrarClientes() {
        return $this->mostrarClientes;
    }

    function getMostrarContactos() {
        return $this->mostrarContactos;
    }

    function setMostrarClientes($mostrarClientes) {
        $this->mostrarClientes = $mostrarClientes;
    }

    function setMostrarContactos($mostrarContactos) {
        $this->mostrarContactos = $mostrarContactos;
    }

    function getIdEscalamiento() {
        return $this->idEscalamiento;
    }

    function getTiempoEnvio() {
        return $this->tiempoEnvio;
    }

    function getColor() {
        return $this->color;
    }

    function getPrioridad() {
        return $this->prioridad;
    }

    function getMensaje() {
        return $this->mensaje;
    }

    function setIdEscalamiento($idEscalamiento) {
        $this->idEscalamiento = $idEscalamiento;
    }

    function setTiempoEnvio($tiempoEnvio) {
        $this->tiempoEnvio = $tiempoEnvio;
    }

    function setColor($color) {
        $this->color = $color;
    }

    function setPrioridad($prioridad) {
        $this->prioridad = $prioridad;
    }

    function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    function getIdEscalamientoCorreo() {
        return $this->idEscalamientoCorreo;
    }

    function setIdEscalamientoCorreo($idEscalamientoCorreo) {
        $this->idEscalamientoCorreo = $idEscalamientoCorreo;
    }

    function getCorreo() {
        return $this->correo;
    }

    function getEnviado() {
        return $this->enviado;
    }

    function setCorreo($correo) {
        $this->correo = $correo;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function getIdEstadoTicket() {
        return $this->IdEstadoTicket;
    }

    function setIdEstadoTicket($IdEstadoTicket) {
        $this->IdEstadoTicket = $IdEstadoTicket;
    }
    
    function getFlagValidacion() {
        return $this->FlagValidacion;
    }

    function getFlagCobrar() {
        return $this->FlagCobrar;
    }

    function setFlagValidacion($FlagValidacion) {
        $this->FlagValidacion = $FlagValidacion;
    }

    function setFlagCobrar($FlagCobrar) {
        $this->FlagCobrar = $FlagCobrar;
    }


}

?>
