<?php
include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class ComponentesDetalle
{
    private $noPartePadre;
    private $noParteHijo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    
    public function getRegistroById($id,$id2) {
        $consulta = ("SELECT * FROM k_componentecomponenteinicial WHERE NoParteComponentePadre='" . $id . "' AND NoParteComponente='".$id2."'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->noPartePadre = $rs['NoParteComponentePadre'];
            $this->noParteHijo = $rs['NoParteComponente'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_componentecomponenteinicial(NoParteComponentePadre,NoParteComponente,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noPartePadre . "','" . $this->noParteHijo . "','". $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    
    function deleteRegistro(){
        $consulta = ("DELETE FROM k_componentecomponenteinicial WHERE NoParteComponentePadre='" .$this->noPartePadre . "' AND NoParteComponente='".$this->noParteHijo."';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if($query==1){
            return true;
        }
       return false;
    }
    
    
    public function getNoPartePadre() {
        return $this->noPartePadre;
    }

    public function setNoPartePadre($noPartePadre) {
        $this->noPartePadre = $noPartePadre;
    }

    public function getNoParteHijo() {
        return $this->noParteHijo;
    }

    public function setNoParteHijo($noParteHijo) {
        $this->noParteHijo = $noParteHijo;
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
