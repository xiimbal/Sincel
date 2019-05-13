<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class Cuadrante {

    private $idCuadrante;
    private $descripcion;
    private $latitud;
    private $longitud;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getRegistroById($id) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT * FROM c_area WHERE IdArea='" . $id . "';");
        if ($rs = mysql_fetch_array($query)) {
            $this->idCuadrante = $rs['IdArea'];
            $this->descripcion = $rs['Descripcion'];
            $this->latitud = $rs['Latitud'];
            $this->longitud = $rs['Longitud'];
            $this->activo = $rs['Activo'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $catalogo = new Catalogo();
        $consulta = "INSERT INTO c_estado(Nombre,IdArea,IdPrioridad,mostrarClientes,mostrarContactos,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->descripcion . "',NULL,NULL,0,0,'" . $this->activo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "');";

        $idEstado = $catalogo->insertarRegistro($consulta);
        if ($idEstado != NULL && $idEstado != 0) {
            $consulta = "INSERT INTO c_area(Descripcion,IdEstado,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,ClaveCentroCosto,Latitud,Longitud)
            VALUES('" . $this->descripcion . "'," . $idEstado . ",'" . $this->activo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "',NULL," . $this->latitud . "," . $this->longitud . ");";

            $idCuadrante = $catalogo->insertarRegistro($consulta);
            if ($idCuadrante != NULL && $idCuadrante != 0) {
                $consulta = "INSERT INTO k_flujoestado(IdFlujo,IdEstado,Orden,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES(2," . $idEstado . ",0,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "');";

                $IdKFlujo2 = $catalogo->insertarRegistro($consulta);
                if ($IdKFlujo2 != NULL && $IdKFlujo2 != 0) {
                    $consulta = "INSERT INTO k_flujoestado(IdFlujo,IdEstado,Orden,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                     VALUES(7," . $idEstado . ",0,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "');";

                    $IdKFlujo7 = $catalogo->insertarRegistro($consulta);
                    if ($IdKFlujo7 != NULL && $IdKFlujo7 != 0) {
                        return true;
                    }
                    return false;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    public function editRegistro() {
        $catalogo = new Catalogo();

        $query = $catalogo->obtenerLista("UPDATE c_area SET Descripcion ='" . $this->descripcion . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "',Latitud = " . $this->latitud . ",Longitud = " . $this->longitud . " WHERE IdArea='" . $this->idCuadrante . "';");
        $querys = $catalogo->obtenerLista("SELECT IdEstado FROM c_area WHERE IdArea='" . $this->idCuadrante . "';");
        while ($rs = mysql_fetch_array($querys)) {
            $IdEstado = $rs['IdEstado'];
        }
        if ($query == 1) {
            $query1 = $catalogo->obtenerLista("UPDATE c_estado SET Nombre = '" . $this->descripcion . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdEstado=" . $IdEstado . ";");
            if ($query1 == 1) {
                return true;
            }
        }
        return false;
    }

    public function deleteRegistro() {
        $catalogo = new Catalogo();
        $querys = $catalogo->obtenerLista("SELECT IdEstado FROM c_area WHERE IdArea='" . $this->idCuadrante . "';");
        while ($rs = mysql_fetch_array($querys)) {
            $IdEstado = $rs['IdEstado'];
        }
        $query = $catalogo->obtenerLista("DELETE FROM c_area WHERE IdArea = '" . $this->idCuadrante . "';");
        if ($query == 1) {
            $query = $catalogo->obtenerLista("DELETE FROM c_estado WHERE IdEstado = '" . $IdEstado . "';");
            if ($query == 1) {
                return true;
            }
        }
        return false;
    }

    public function getIdCuadrante() {
        return $this->idCuadrante;
    }

    public function setIdCuadrante($idCuadrante) {
        $this->idCuadrante = $idCuadrante;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getLatitud() {
        return $this->latitud;
    }

    public function setLatitud($latitud) {
        $this->latitud = $latitud;
    }

    public function getLongitud() {
        return $this->longitud;
    }

    public function setLongitud($longitud) {
        $this->longitud = $longitud;
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