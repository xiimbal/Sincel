<?php

include_once("Catalogo.class.php");

class Ubicacion {

    private $idUbicacion;
    private $descripcion;
    private $calle;
    private $exterior;
    private $colonia;
    private $delegacion;
    private $cp;
    private $estado;
    private $latitud;
    private $longitud;
    private $activo;
    private $usuarioCreacion;
    private $usuarioUltimaModificacion;
    private $pantalla;
    private $empresa;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_ubicaciones WHERE IdUbicacion =" . $id . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idUbicacion = $rs['IdUbicacion'];
            $this->descripcion = $rs['Descripcion'];

            $this->calle = $rs['Calle'];
            $this->exterior = $rs['NoExterior'];
            $this->colonia = $rs['Colonia'];
            $this->delegacion = $rs['Delegacion'];
            $this->cp = $rs['CodigoPostal'];
            $this->estado = $rs['Estado'];
            $this->latitud = $rs['Latitud'];
            $this->longitud = $rs['Longitud'];
            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            //$this->FechaCreacion = $rs['FechaCreacion'];
            $this->usuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            //$this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if (!isset($this->latitud) || empty($this->latitud)) {
            $this->latitud = "NULL";
        }
        if (!isset($this->longitud) || empty($this->longitud)) {
            $this->longitud = "NULL";
        }
        
        //if (mysql_num_rows($result) == 0) {
        $consulta = ("INSERT INTO c_ubicaciones(Descripcion, Calle, NoExterior, Colonia, Delegacion, CodigoPostal, Estado, Latitud, Longitud,
                          Activo,UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('" . $this->descripcion . "','" . $this->calle . "','" . $this->exterior . "','" . $this->colonia . "','" . $this->delegacion . "','" . $this->cp . "','" . $this->estado . "',
                    " . $this->latitud . ", " . $this->longitud . ",'" . $this->activo . "','" . $this->usuarioCreacion . "',NOW(),'" . $this->usuarioUltimaModificacion . "',NOW(),'" . $this->pantalla . "');");
//        echo $consulta;
        $this->idUbicacion = $catalogo->insertarRegistro($consulta);
        if ($this->idUbicacion != NULL && $this->idUbicacion != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $catalogo = new Catalogo();
        $consulta = ("UPDATE c_ubicaciones SET Descripcion = '" . $this->descripcion . "',Calle = '" . $this->calle . "', NoExterior = '" . $this->exterior . "', Colonia = '" . $this->colonia . "',Delegacion ='" . $this->delegacion . "',
                        CodigoPostal ='" . $this->cp . "',Estado ='" . $this->estado . "',Latitud='" . $this->latitud . "',Longitud='" . $this->longitud . "',
                        UsuarioUltimaModificacion = '" . $this->usuarioUltimaModificacion . "', Activo = ". $this->activo .",
                        FechaUltimaModificacion = now(), Pantalla = '" . $this->pantalla . "' WHERE IdUbicacion = '" . $this->idUbicacion . "';");
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = "DELETE FROM c_ubicaciones WHERE IdUbicacion ='" . $this->idUbicacion . "';";
//        echo $consulta;
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdUbicacion() {
        return $this->idUbicacion;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getCalle() {
        return $this->calle;
    }

    public function getExterior() {
        return $this->exterior;
    }

    public function getColonia() {
        return $this->colonia;
    }

    public function getDelegacion() {
        return $this->delegacion;
    }

    public function getCp() {
        return $this->cp;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getLatitud() {
        return $this->latitud;
    }

    public function getLongitud() {
        return $this->longitud;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->usuarioUltimaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    public function setIdUbicacion($idUbicacion) {
        $this->idUbicacion = $idUbicacion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setCalle($calle) {
        $this->calle = $calle;
    }

    public function setExterior($exterior) {
        $this->exterior = $exterior;
    }

    public function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    public function setDelegacion($delegacion) {
        $this->delegacion = $delegacion;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setLatitud($latitud) {
        $this->latitud = $latitud;
    }

    public function setLongitud($longitud) {
        $this->longitud = $longitud;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUsuarioUltimaModificacion($usuarioUltimaModificacion) {
        $this->usuarioUltimaModificacion = $usuarioUltimaModificacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>