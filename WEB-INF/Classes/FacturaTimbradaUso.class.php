<?php

include_once("Catalogo.class.php");

/**
 * Description of Menu
 *
 * @author JTJ
 */
class FacturaTimbradaUso {
    
    private $IdFactura;
    
public function nuevoRegistroBandera(){
    
    $catalogo = new Catalogo();
    $i=0;
    $valores = array();
    
    $consulta = ("INSERT INTO c_Bandera(IdBandera,BanderaTimbrado,UltimaModificacion) VALUES (0,1,NOW()); ");
    $this->IdFactura = $catalogo->insertarRegistro($consulta);
    
    $consulta = ("SELECT *  FROM c_Bandera WHERE IdBandera <= $this->IdFactura ");
    $query = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($query)) {
        $i++;
    }
    
     return $i;
}

public function liberarProcesoTimbreado(){ 
    $consulta = ("DELETE FROM c_Bandera WHERE IdBandera = $this->IdFactura ");
    $catalogo = new Catalogo();
    if(!$catalogo->obtenerLista($consulta)) 
        return false;
    return true;
}

}
?>
