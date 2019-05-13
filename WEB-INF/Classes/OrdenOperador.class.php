<?php

include_once ("Parametros.class.php");
include_once ("Catalogo.class.php");

class OrdenOperador {

    private $idOrdenOperador;
    private $idUsuario;
    private $idBase;
    private $fecha;
    private $hora;
    private $orden;
    private $activo;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $usuarioModificacion;
    private $fechaModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT DATE(coo.FechaHora) AS Fecha, TIME(coo.FechaHora) AS Hora, coo.* FROM c_orden_operador AS coo WHERE IdOrdenOperador='" . $id . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idOrdenOperador = $rs['IdOrdenOperador'];
            $this->idUsuario = $rs['IdUsuario'];
            $this->idBase = $rs['IdBase'];
            $this->fecha = $rs['Fecha'];
            $this->hora = $rs['Hora'];
            $this->orden = $rs['Orden'];
            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            $this->fechaCreacion = $rs['FechaCreacion'];
            $this->usuarioModificacion = $rs['UsuarioUltimaModificacion'];
            $this->fechaModificacion = $rs['FechaUltimaModificacion'];
            $this->pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $catalogo = new Catalogo();
        $consulta = "INSERT INTO c_orden_operador(IdUsuario,IdBase,FechaHora,Orden,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(" . $this->idUsuario . "," . $this->idBase . ",NOW(),'" . $this->orden . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "');";


        $this->idOrdenOperador = $catalogo->insertarRegistro($consulta);
        if ($this->idOrdenOperador != NULL && $this->idOrdenOperador != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro($orden, $ordenOperador) {
        $catalogo = new Catalogo();

        $query = $catalogo->obtenerLista("UPDATE c_orden_operador SET Orden ='" . $orden . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdOrdenOperador=" . $ordenOperador . ";");

        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function editRegistroA($ordenOperador) {
        $catalogo = new Catalogo();

        $query = $catalogo->obtenerLista("UPDATE c_orden_operador SET Activo = 0,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdOrdenOperador=" . $ordenOperador . ";");

        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM c_orden_operador WHERE IdOrdenOperador = " . $this->idOrdenOperador . ";");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getMayorOrden() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT MAX(Orden) AS Orden FROM c_orden_operador WHERE DATE(FechaHora)= DATE(NOW());");
        if (mysql_num_rows($query) > 0) {
            if ($rs = mysql_fetch_array($query)) {
                if ($rs['Orden'] != NULL && $rs['Orden'] != "") {
                    return $rs['Orden'];
                } else {
                    return 0;
                }
            }
        } else {
            return 0;
        }
    }

    public function getPorOrden($numero, $oper) {
        $catalogo = new Catalogo();
        if ($oper == 1) {
            $orden = $this->orden - $numero;
        } else {
            $orden = $this->orden + $numero;
        }
//        echo "SELECT * FROM c_orden_operador WHERE DATE(FechaHora)= DATE(NOW()) AND Orden= " . $orden . " ;";
        $query = $catalogo->obtenerLista("SELECT * FROM c_orden_operador WHERE DATE(FechaHora)= DATE(NOW()) AND Orden= " . $orden . " ;");
        if ($rs = mysql_fetch_array($query)) {
            return $rs['IdOrdenOperador'];
        } else {
            return 0;
        }
    }

    public function getIdOrdenOperador() {
        return $this->idOrdenOperador;
    }

    public function setIdOrdenOperador($idOrden) {
        $this->idOrdenOperador = $idOrden;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }
    
    public function getIdBase() {
        return $this->idBase;
    }

    public function setIdBase($idBase) {
        $this->idBase = $idBase;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function getHora() {
        return $this->hora;
    }

    public function setHora($hora) {
        $this->hora = $hora;
    }

    public function getOrden() {
        return $this->orden;
    }

    public function setOrden($orden) {
        $this->orden = $orden;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
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

    public function setFechaCreacion($usuarioCreacion) {
        $this->fechaCreacion = $usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function setFechaModificacion($usuarioModificacion) {
        $this->fechaModificacion = $usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

}

?>