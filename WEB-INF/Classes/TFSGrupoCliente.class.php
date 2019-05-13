<?php
include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
/**
 * Description of TFSGrupoCliente
 *
 * @author MAGG
 */
class TFSGrupoCliente {
    private $ClaveGrupoAnterior;
    private $IdTfs;
    private $ClaveGrupo;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function tieneGrupo($idUsuario){
        $consulta = "SELECT * FROM `k_tfsgrupo` WHERE IdTfs = $idUsuario;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return true;
        }
        return false;
    }
    
    public function getRegistroByIDs($idUsuario, $ClaveGrupo){
        $consulta = "SELECT * FROM `k_tfsgrupo` WHERE IdTfs = $idUsuario AND ClaveGrupo = '$ClaveGrupo';";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdTfs = $rs['IdTfs'];
            $this->ClaveGrupo = $rs['ClaveGrupo'];
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
    
    public function newRegistro(){
        $consulta = "INSERT INTO k_tfsgrupo(IdTfs, ClaveGrupo, Activo, UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla) VALUES($this->IdTfs,'$this->ClaveGrupo',$this->Activo,'$this->UsuarioCreacion',
            NOW(),'$this->UsuarioUltimaModificacion',NOW(),'Carga Manual');";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if($query == "1"){            
            return true;
        }
        return false;
    }
    
    public function editarRegistro(){
        $consulta = "UPDATE k_tfsgrupo SET ClaveGrupo = '$this->ClaveGrupo', Activo = $this->Activo, 
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), 
            Pantalla = '$this->Pantalla' WHERE IdTfs = $this->IdTfs AND ClaveGrupo = '$this->ClaveGrupoAnterior';";            
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if($query == "1"){            
            return true;
        }
        return false;
    }
    
    public function deleteRegistro(){
        $consulta = "DELETE FROM k_tfsgrupo WHERE IdTfs = $this->IdTfs AND ClaveGrupo = '$this->ClaveGrupo';";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if($query == "1"){            
            return true;
        }
        return false;
    }
    
    public function getIdTfs() {
        return $this->IdTfs;
    }

    public function setIdTfs($IdTfs) {
        $this->IdTfs = $IdTfs;
    }

    public function getClaveGrupo() {
        return $this->ClaveGrupo;
    }

    public function setClaveGrupo($ClaveGrupo) {
        $this->ClaveGrupo = $ClaveGrupo;
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
    
    public function getClaveGrupoAnterior() {
        return $this->ClaveGrupoAnterior;
    }

    public function setClaveGrupoAnterior($ClaveGrupoAnterior) {
        $this->ClaveGrupoAnterior = $ClaveGrupoAnterior;
    }
}

?>
