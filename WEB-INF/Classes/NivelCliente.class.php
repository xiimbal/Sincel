<?php

include_once("Catalogo.class.php");

/**
 * Description of NivelCliente
 *
 * @author MAGG
 */
class NivelCliente {
    private $IdNivelCliente;
    private $NivelCliente;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_clientenivel WHERE IdNivelCliente = '" . $id . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdNivelCliente = $rs['IdNivelCliente'];
            $this->NivelCliente = $rs['NivelCliente'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    public function newRegistro() {
        $consulta = ("INSERT INTO c_clientenivel(IdNivelCliente,NivelCliente,Activo,UsuarioCreacion,FechaCreacion,
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'$this->NivelCliente',$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_clientenivel SET NivelCliente = '$this->NivelCliente', 
            Activo = " . $this->Activo . ",UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = now(),
            Pantalla = '" . $this->Pantalla . "' WHERE IdNivelCliente='" . $this->IdNivelCliente . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM c_clientenivel WHERE IdNivelCliente = '" . $this->IdNivelCliente . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdNivelCliente() {
        return $this->IdNivelCliente;
    }

    public function setIdNivelCliente($IdNivelCliente) {
        $this->IdNivelCliente = $IdNivelCliente;
    }

    public function getNivelCliente() {
        return $this->NivelCliente;
    }

    public function setNivelCliente($NivelCliente) {
        $this->NivelCliente = $NivelCliente;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }
}

?>
