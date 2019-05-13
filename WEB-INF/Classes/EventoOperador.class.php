<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class EventoOperador {

    private $idBitacora;
    private $fecha;
    private $hora;
    private $lineaNegocio;
    private $evento;
    private $operador;
    private $comentario;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $PathImagen;

    public function getRegistroById($id) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT IdBitacora,IdLineaNegocio, IdUsuario, IdEvento, DATE(FechaHora) AS Fecha, TIME(FechaHora) AS Hora, Comentario, Activo, PathImagen FROM c_bitacora_operador WHERE IdBitacora=" . $id . "");
        if ($rs = mysql_fetch_array($query)) {
            $this->idBitacora = $rs['IdBitacora'];
            $this->fecha = $rs['Fecha'];
            $this->hora = $rs['Hora'];
            $this->lineaNegocio = $rs['IdLineaNegocio'];
            $this->evento = $rs['IdEvento'];
            $this->operador = $rs['IdUsuario'];
            $this->comentario = $rs['Comentario'];
            $this->activo = $rs['Activo'];
            $this->PathImagen = $rs['PathImagen'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        if (isset($this->operador) && $this->operador == 0) {
            $this->operador = "NULL";
        }
        if (isset($this->evento) && $this->evento == 0) {
            $this->evento = "NULL";
        }
        $catalogo = new Catalogo();
        $consulta = "INSERT INTO c_bitacora_operador(IdLineaNegocio,IdUsuario,IdEvento,FechaHora,Comentario,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(" . $this->lineaNegocio . "," . $this->operador . "," . $this->evento . ",'" . $this->fecha . " " . $this->hora . "','" . $this->comentario . "','" . $this->activo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "');";
        //echo $consulta;

        $this->idBitacora = $catalogo->insertarRegistro($consulta);
        if ($this->idBitacora != NULL && $this->idBitacora != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if (isset($this->operador) && $this->operador == 0) {
            $this->operador = "NULL";
        }
        if (isset($this->evento) && $this->evento == 0) {
            $this->evento = "NULL";
        }
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("UPDATE c_bitacora_operador SET IdLineaNegocio =" . $this->lineaNegocio . ", IdUsuario=" . $this->operador . ", IdEvento=" . $this->evento . ", FechaHora='" . $this->fecha . " " . $this->hora . "', Comentario='" . $this->comentario . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdBitacora='" . $this->idBitacora . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM c_bitacora_operador WHERE IdBitacora = '" . $this->idBitacora . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function updateImagen() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("UPDATE c_bitacora_operador SET PathImagen ='" . $this->PathImagen . "' WHERE IdBitacora='" . $this->idBitacora . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdBitacora() {
        return $this->idBitacora;
    }

    public function setIdBitacora($idBitacora) {
        $this->idBitacora = $idBitacora;
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

    public function getLineaNegocio() {
        return $this->lineaNegocio;
    }

    public function setLineaNegocio($lineaNegocio) {
        $this->lineaNegocio = $lineaNegocio;
    }

    public function getEvento() {
        return $this->evento;
    }

    public function setEvento($evento) {
        $this->evento = $evento;
    }

    public function getOperador() {
        return $this->operador;
    }

    public function setOperador($operador) {
        $this->operador = $operador;
    }

    public function getComentario() {
        return $this->comentario;
    }

    public function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    function getPathImagen() {
        return $this->PathImagen;
    }

    function setPathImagen($PathImagen) {
        $this->PathImagen = $PathImagen;
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