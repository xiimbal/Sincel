<?php

include_once ("Catalogo.class.php");
include_once ("AddendaDetalle.class.php");

/**
 * Description of Addenda
 *
 * @author MAGG
 */
class Addenda {

    private $id_addenda;
    private $nombre_addenda;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $Nombres;
    private $Valores;
    private $Dinamicos;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_addenda` WHERE id_addenda = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_addenda = $rs['id_addenda'];
            $this->nombre_addenda = $rs['nombre_addenda'];
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
        $consulta = ("INSERT INTO c_addenda(id_addenda, nombre_addenda, Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) "
                . "VALUES(0, '$this->nombre_addenda', $this->Activo, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');");
        $catalogo = new Catalogo();
        $this->id_addenda = $catalogo->insertarRegistro($consulta);
        if ($this->id_addenda != NULL && $this->id_addenda != 0) {
            $this->insertarMultiConceptos($this->Nombres, $this->Valores, $this->Dinamicos);
            return true;
        }
        return false;
    }

    public function editRegistro() {        
        $consulta = ("UPDATE c_addenda SET nombre_addenda = '$this->nombre_addenda', "
                . "Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', 
                FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE id_addenda = " . $this->id_addenda . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $this->insertarMultiConceptos($this->Nombres, $this->Valores, $this->Dinamicos);
            return true;
        }
        return false;
    }

    public function deleteRegistro() {        
        $catalogo = new Catalogo();        
        $catalogo->obtenerLista("DELETE FROM `k_addenda` WHERE id_addenda = $this->id_addenda;");//Borramos el detalle de la addenda
        $consulta = "DELETE FROM `c_addenda` WHERE id_addenda = $this->id_addenda;";
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function insertarMultiConceptos($nombres, $valores, $dinamicos){
        $detalle = new AddendaDetalle();
        $detalle->setId_addenda($this->id_addenda);
        $detalle->setUsuarioCreacion($this->UsuarioCreacion);
        $detalle->setUsuarioUltimaModificacion($this->UsuarioUltimaModificacion);
        $detalle->setPantalla($this->Pantalla);
        $detalle->deleteRegistros();
        if(isset($nombres) && !empty($nombres)){
            foreach ($nombres as $key => $value) {
                $detalle->setCampo($value);
                $detalle->setValor($valores[$key]);
                $detalle->setDinamicos($dinamicos[$key]);
                $detalle->newRegistro();
            }
        }
    }
    
    public function getId_addenda() {
        return $this->id_addenda;
    }

    public function getNombre_addenda() {
        return $this->nombre_addenda;
    }

    public function getActivo() {
        return $this->Activo;
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

    public function setId_addenda($id_addenda) {
        $this->id_addenda = $id_addenda;
    }

    public function setNombre_addenda($nombre_addenda) {
        $this->nombre_addenda = $nombre_addenda;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
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

    function getNombres() {
        return $this->Nombres;
    }

    function getValores() {
        return $this->Valores;
    }

    function setNombres($Nombres) {
        $this->Nombres = $Nombres;
    }

    function setValores($Valores) {
        $this->Valores = $Valores;
    }
    
    function getDinamicos() {
        return $this->Dinamicos;
    }

    function setDinamicos($Dinamicos) {
        $this->Dinamicos = $Dinamicos;
    }
}
