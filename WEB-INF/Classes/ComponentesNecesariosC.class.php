<?php
include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class ComponentesNecesariosC
{
    private $noPartePadre;
    private $noParteHijo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    
    public function newRegistro() {
        $consulta = ("INSERT INTO k_componentecomponentenecesario(NoParteComponentePadre,NoParteComponente,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noPartePadre . "','" . $this->noParteHijo . "','". $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
   
    function deleteRegistro(){
        $consulta = ("DELETE FROM k_componentecomponentenecesario WHERE NoParteComponentePadre='" .$this->noPartePadre . "' AND NoParteComponente='".$this->noParteHijo."';");
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
