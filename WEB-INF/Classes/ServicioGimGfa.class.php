<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class ServicioGim {

    private $idC;
    private $idServicio;
    private $claveEsp;
    private $claveCentroCosto;
    private $idAnexoCliente;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    
    public function getRegistrosByIdAnexoAndCC($idAnexo, $cc){
        $consulta = ("SELECT IdKserviciogimgfa FROM `k_serviciogimgfa` WHERE IdAnexoClienteCC = $idAnexo AND ClaveCentroCosto = '$cc';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);        
        return $query;
    }

    public function getRegistroById($id, $id2, $id3) {
        $consulta = ("SELECT * FROM k_serviciogimgfa WHERE IdKserviciogimgfa='" . $id . "' AND CveEspKservicioimfa='" . $id2 . "' AND IdAnexoClienteCC='" . $id3 . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idServicio = $rs['IdKserviciogimgfa'];
            $this->claveEsp = $rs['CveEspKservicioimfa'];
            $this->claveCentroCosto = $rs['ClaveCentroCosto'];
            $this->idAnexoCliente = $rs['IdAnexoClienteCC'];
        }
        return $query;
    }

    public function newRegistro() {        
        $consulta = "INSERT INTO k_serviciogimgfa(IdKserviciogimgfa,CveEspKservicioimfa,ClaveCentroCosto,IdAnexoClienteCC,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->claveEsp . "','" . $this->claveCentroCosto . "'," . $this->idAnexoCliente . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')";                
        $catalogo = new Catalogo(); $this->idServicio = $catalogo->insertarRegistro($consulta);
        if ($this->idServicio!=NULL && $this->idServicio!=0) {            
            return true;
        }        
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_serviciogimgfa SET  ClaveCentroCosto='" . $this->claveCentroCosto . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE IdKserviciogimgfa='" . $this->idServicio . "'AND ClaveCentroCosto='".$this->idC."' AND IdAnexoClienteCC='" . $this->idAnexoCliente . "';");
       $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_serviciogimgfa WHERE IdKserviciogimgfa='" . $this->noPartesEquipo . "' AND IdAnexoClienteCC='" . $this->idAnexoCliente . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdC() {
        return $this->idC;
    }

    public function setIdC($idC) {
        $this->idC = $idC;
    }

    public function getIdServicio() {
        return $this->idServicio;
    }

    public function setIdServicio($idServicio) {
        $this->idServicio = $idServicio;
    }

    public function getClaveEsp() {
        return $this->claveEsp;
    }

    public function setClaveEsp($claveEsp) {
        $this->claveEsp = $claveEsp;
    }

    public function getClaveCentroCosto() {
        return $this->claveCentroCosto;
    }

    public function setClaveCentroCosto($claveCentroCosto) {
        $this->claveCentroCosto = $claveCentroCosto;
    }

    public function getIdAnexoCliente() {
        return $this->idAnexoCliente;
    }

    public function setIdAnexoCliente($idAnexoCliente) {
        $this->idAnexoCliente = $idAnexoCliente;
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
