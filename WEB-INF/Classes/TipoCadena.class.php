<?php
include_once("CatalogoFacturacion.class.php");

/**
 * Description of TipoCadena
 *
 * @author miguel
 */
class TipoCadena {
    private $IdTipoCadena;
    private $TipoCadena;
    private $Descripcion;
    private $Activo;
    
    public function getRegistrobyID($id) {        
        $consulta = "SELECT * FROM `c_tipocadenapago` WHERE IdTipoCadena = $id;";
        $catalogo = new CatalogoFacturacion();        
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdTipoCadena = $rs['IdTipoCadena'];
            $this->TipoCadena = $rs['TipoCadena'];
            $this->Descripcion = $rs['Descripcion'];
            $this->Activo = $rs['Activo'];
            return true;
        }        
        return false;
    }
    
    function getIdTipoCadena() {
        return $this->IdTipoCadena;
    }

    function getTipoCadena() {
        return $this->TipoCadena;
    }

    function getDescripcion() {
        return $this->Descripcion;
    }

    function getActivo() {
        return $this->Activo;
    }

    function setIdTipoCadena($IdTipoCadena) {
        $this->IdTipoCadena = $IdTipoCadena;
    }

    function setTipoCadena($TipoCadena) {
        $this->TipoCadena = $TipoCadena;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    function setActivo($Activo) {
        $this->Activo = $Activo;
    }


}
