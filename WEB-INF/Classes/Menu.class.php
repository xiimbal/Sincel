<?php

include_once("Catalogo.class.php");

/**
 * Description of Menu
 *
 * @author MAGG
 */
class Menu {
    private $id_sub;
    private $id_menu;
    private $nom_sub;
    private $ref_sub;
    private $descripcion;
    
    private $empresa;
    
    public function getPermisos($idUsu) {
        $consulta = ("SELECT
                    m_menu.nom_menu AS menu,                        
                    sm.nom_sub AS submenu,
                    sm.ref_sub AS referencia
            FROM `c_usuario`
            INNER JOIN m_dpuestomenu AS dp ON c_usuario.IdUsuario = $idUsu AND dp.IdPuesto = c_usuario.IdPuesto
            INNER JOIN m_submenu AS sm ON dp.IdSubmenu = sm.id_sub
            INNER JOIN m_menu ON sm.id_menu = m_menu.id_menu
            ORDER BY posicion ASC,sm.nom_sub;");
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Verifica si el usuario tiene permiso del submenu especificado
     * @param type $idUsu id del usuario en cuestion
     * @param type $idSubmenu id del submenu en cuestion
     * @return boolean true en caso de que tenga permiso, false en caso contrario.
     */
    public function tieneSubmenu($idUsu, $idSubmenu) {
        $consulta = "SELECT
            m_menu.nom_menu AS menu,                        
            sm.nom_sub AS submenu,
            sm.ref_sub AS referencia
            FROM `c_usuario`
            INNER JOIN m_dpuestomenu AS dp ON c_usuario.IdUsuario = $idUsu AND dp.IdPuesto = c_usuario.IdPuesto
            INNER JOIN m_submenu AS sm ON dp.IdSubmenu = sm.id_sub AND sm.id_sub = $idSubmenu
            INNER JOIN m_menu ON sm.id_menu = m_menu.id_menu
            ORDER BY posicion ASC, sm.nom_sub;";
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) >= 1) {
            return true;
        }
        return false;
    }
    
    public function getSubmenuById($id){
        $consulta = "SELECT * FROM `m_submenu` WHERE id_sub = $id;";
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $this->id_sub = $rs['id_sub'];
            $this->id_menu = $rs['id_menu'];
            $this->nom_sub = $rs['nom_sub'];
            $this->ref_sub = $rs['ref_sub'];
            $this->descripcion = $rs['descripcion'];
            return true;
        }
        return false;
    }

    function getId_sub() {
        return $this->id_sub;
    }

    function getId_menu() {
        return $this->id_menu;
    }

    function getNom_sub() {
        return $this->nom_sub;
    }

    function getRef_sub() {
        return $this->ref_sub;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setId_sub($id_sub) {
        $this->id_sub = $id_sub;
    }

    function setId_menu($id_menu) {
        $this->id_menu = $id_menu;
    }

    function setNom_sub($nom_sub) {
        $this->nom_sub = $nom_sub;
    }

    function setRef_sub($ref_sub) {
        $this->ref_sub = $ref_sub;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

        
    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}

?>
