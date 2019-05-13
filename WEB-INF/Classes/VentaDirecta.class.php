<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class VentaDirecta {
    private $id;
    private $bandera="1";
    
    public function Autorizar_vd($tipo,$clave) {
        $consulta = ("UPDATE c_ventadirecta SET c_ventadirecta.autorizada_vd=".$tipo." WHERE c_ventadirecta.IdVentaDirecta='".$this->id."' AND c_ventadirecta.clave_aut_vd='".$clave."'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function Autorizar_alm($tipo,$clave) {
        $consulta = ("SELECT autorizada_alm FROM c_ventadirecta WHERE IdVentaDirecta='".$this->id."'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while($rs=  mysql_fetch_array($query)){
            if($rs['autorizada_alm']=="1"){
                $this->bandera="0";
            }
        }
        $query = $catalogo->obtenerLista("UPDATE c_ventadirecta SET c_ventadirecta.autorizada_alm=".$tipo." WHERE c_ventadirecta.IdVentaDirecta='".$this->id."' AND c_ventadirecta.clave_aut_alm='".$clave."'");        
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function marcarFacturada(){
        $consulta = ("UPDATE c_ventadirecta SET facturada = 1 WHERE c_ventadirecta.IdVentaDirecta='".$this->id."';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
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

    public function getBandera() {
        return $this->bandera;
    }

    public function setBandera($bandera) {
        $this->bandera = $bandera;
    }

}
?>
