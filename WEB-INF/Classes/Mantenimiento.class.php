<?php
include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
/**
 * Description of Mantenimiento
 *
 * @author MAGG
 */
class Mantenimiento {
    private $id_mtto;
    private $NoSerie;
    private $Fecha;
    private $estatus;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function getMantenimientosBySerie(){        
        $consulta = "SELECT * FROM `k_mantenimiento` WHERE NoSerie = '$this->NoSerie';";                      
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function newRegistro(){        
        $consulta = "INSERT INTO k_mantenimiento(NoSerie,Fecha,estatus,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla) VALUES('$this->NoSerie','$this->Fecha',$this->estatus,
                '$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId_mtto() {
        return $this->id_mtto;
    }

    public function setId_mtto($id_mtto) {
        $this->id_mtto = $id_mtto;
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
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
