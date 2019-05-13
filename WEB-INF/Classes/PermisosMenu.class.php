<?php
include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
class PermisosMemu{
    private $id;
    private $idPuesto;
    private $idSubmenu;
    private $alta;
    private $baja;
    private $modificacion;
    private $consulta;
    
    public function getRegistroById($id, $id2) {
        $consulta = ("SELECT * FROM m_dpuestomenu WHERE IdPuesto='" . $id . "' AND IdSubmenu='" . $id2 . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idPuesto = $rs['IdPuesto'];
            $this->idSubmenu = $rs['IdSubmenu'];
            $this->alta = $rs['alta'];
            $this->baja = $rs['baja'];
            $this->modificacion = $rs['modificacion'];
            $this->consulta = $rs['consulta'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO m_dpuestomenu(IdPuesto,IdSubmenu,alta,baja,modificacion,consulta)
            VALUES('" . $this->idPuesto . "','" . $this->idSubmenu . "','" . $this->alta . "','" . $this->baja . "','" . $this->modificacion . "','" . $this->consulta . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE m_dpuestomenu SET IdSubmenu= '" . $this->idSubmenu . "',alta = '" . $this->alta . "',baja = '".$this->baja."',modificacion = '" . $this->modificacion . "',consulta = '" . $this->consulta . "'  WHERE IdPuesto='" . $this->idPuesto . "' AND IdSubmenu ='" . $this->id . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM m_dpuestomenu WHERE IdPuesto='" . $this->idPuesto . "' AND IdSubmenu ='" . $this->idSubmenu . "';");
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

    public function getIdPuesto() {
        return $this->idPuesto;
    }

    public function setIdPuesto($idPuesto) {
        $this->idPuesto = $idPuesto;
    }

    public function getIdSubmenu() {
        return $this->idSubmenu;
    }

    public function setIdSubmenu($idSubmenu) {
        $this->idSubmenu = $idSubmenu;
    }

    public function getAlta() {
        return $this->alta;
    }

    public function setAlta($alta) {
        $this->alta = $alta;
    }

    public function getBaja() {
        return $this->baja;
    }

    public function setBaja($baja) {
        $this->baja = $baja;
    }

    public function getModificacion() {
        return $this->modificacion;
    }

    public function setModificacion($modificacion) {
        $this->modificacion = $modificacion;
    }

    public function getConsulta() {
        return $this->consulta;
    }

    public function setConsulta($consulta) {
        $this->consulta = $consulta;
    }


}
?>
