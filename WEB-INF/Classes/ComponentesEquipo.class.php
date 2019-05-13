<?php
include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class ComponentesEquipo
{
    private $id;
    private $noPartesEquipo;
    private $noPartesComponentes;
    private $instalado;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    
     public function getRegistroById($id,$id2) {
        $consulta = ("SELECT * FROM k_equipocomponenteinicial WHERE NoParteEquipo='" . $id . "' AND NoParteComponente='".$id2."'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->noPartesEquipo = $rs['NoParteEquipo'];
            $this->noPartesComponentes = $rs['NoParteComponente'];
            $this->instalado = $rs['Instalado'];
        }
        return $query;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_equipocomponenteinicial(NoParteEquipo,NoParteComponente,Instalado,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noPartesEquipo . "','" . $this->noPartesComponentes . "','".$this->instalado."','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE k_equipocomponenteinicial SET Instalado = '" . $this->instalado . "', NoParteComponente='".$this->noPartesComponentes."',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE NoParteEquipo='" .$this->noPartesEquipo . "' AND NoParteComponente='".$this->id."';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function deleteRegistro(){
        $consulta = ("DELETE FROM k_equipocomponenteinicial WHERE NoParteEquipo='" .$this->noPartesEquipo . "' AND NoParteComponente='".$this->noPartesComponentes."';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if($query==1){
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

        public function getNoPartesEquipo() {
        return $this->noPartesEquipo;
    }

    public function setNoPartesEquipo($noPartesEquipo) {
        $this->noPartesEquipo = $noPartesEquipo;
    }

    public function getNoPartesComponentes() {
        return $this->noPartesComponentes;
    }

    public function setNoPartesComponentes($noPartesComponentes) {
        $this->noPartesComponentes = $noPartesComponentes;
    }

    public function getInstalado() {
        return $this->instalado;
    }

    public function setInstalado($instalado) {
        $this->instalado = $instalado;
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
