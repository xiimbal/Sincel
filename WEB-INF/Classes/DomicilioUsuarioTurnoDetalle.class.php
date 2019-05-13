<?php

include_once ("Catalogo.class.php");

class DomicilioUsuarioTurnoDetalle {

    private $idUscamtur;
    private $idUsuario;
    private $idCampania;
    private $idTurno;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `k_uscamtur` WHERE idUsuario = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idUscamtur = $rs['idUsCamTur'];
            $this->idUsuario = $rs['idUsuario'];
            $this->idCampania = $rs['idCampania'];
            $this->idTurno = $rs['idTurno'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function getRegistrosByUsuario($id) {
        $consulta = ("SELECT * FROM `k_uscamtur` WHERE idUsuario = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_uscamtur(idUsCamTur, idUsuario, idCampania, idTurno, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) "
                . "VALUES(0, $this->idUsuario, '$this->idCampania', '$this->idTurno' , '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');");
        $catalogo = new Catalogo();
        $this->idUscamtur = $catalogo->insertarRegistro($consulta);
        if ($this->idUscamtur != NULL && $this->idUscamtur != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_uscamtur SET idUsuario = $this->idUsuario, idCampania = '$this->idCampania', idTurno = '$this->idTurno',
                UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla'
                WHERE id_kaddenda = " . $this->idUscamtur . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistros() {
        $consulta = "DELETE FROM `k_uscamtur` WHERE idUsuario = $this->idUsuario;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdUsCamTur() {
        return $this->idUscamtur;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function getIdCampania() {
        return $this->idCampania;
    }

    public function getIdTurno() {
        return $this->idTurno;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setIdUsCamTur($idUscamtur) {
        $this->idUscamtur = $idUscamtur;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function setIdCampania($idCampania) {
        $this->idCampania = $idCampania;
    }

    public function setIdTurno($idTurno) {
        $this->idTurno = $idTurno;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

}
