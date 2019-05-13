<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class TFSCliente {

    private $id;
    private $idUsuario;
    private $idLocalidad;
    private $claveCliente;
    private $localidad;
    private $tipo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM k_tfscliente WHERE IdUsuario='" . $id . "' AND ClaveCliente='" . $id2 . "' AND Tipo = 1");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idUsuario = $rs['IdUsuario'];
            $this->claveCliente = $rs['ClaveCliente'];
            $this->localidad = $rs['ClaveCentroCosto'];
        }
        return $query;
    }

    public function getClientesByTFS(){
        $clientes = array();
        $consulta = ("SELECT DISTINCT c.ClaveCliente, c.NombreRazonSocial FROM k_tfscliente tc
            INNER JOIN c_cliente AS c ON c.ClaveCliente = tc.ClaveCliente                 
            WHERE IdUsuario = $this->idUsuario");
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function newRegistro() {
        $consulta = ("INSERT INTO k_tfscliente(IdUsuario,ClaveCliente,Tipo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idUsuario . "','" . $this->claveCliente . "','" . $this->tipo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newRegistroTecnicoCliente() {
        $consulta = ("INSERT INTO k_tfscliente(IdUsuario,ClaveCliente,Tipo,ClaveCentroCosto,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idUsuario . "','" . $this->claveCliente . "','" . $this->tipo . "','" . $this->localidad . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_tfscliente SET ClaveCliente = '" . $this->claveCliente . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdUsuario='" . $this->idUsuario . "' AND ClaveCliente='" . $this->id . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistroTecnicoCliente() {
        $consulta = ("UPDATE k_tfscliente SET ClaveCliente = '" . $this->claveCliente . "',ClaveCentroCosto='" . $this->localidad . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE IdUsuario='" . $this->idUsuario . "' AND ClaveCliente='" . $this->id . "' AND ClaveCentroCosto='" . $this->idLocalidad . "';");        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_tfscliente WHERE IdUsuario='" . $this->idUsuario . "' AND ClaveCliente='" . $this->claveCliente . "';");
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

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function getIdLocalidad() {
        return $this->idLocalidad;
    }

    public function setIdLocalidad($idLocalidad) {
        $this->idLocalidad = $idLocalidad;
    }

    public function getClaveCliente() {
        return $this->claveCliente;
    }

    public function setClaveCliente($claveCliente) {
        $this->claveCliente = $claveCliente;
    }

    public function getLocalidad() {
        return $this->localidad;
    }

    public function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

}

?>
