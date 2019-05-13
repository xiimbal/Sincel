<?php

include_once("Catalogo.class.php");

/**
 * Description of PermisosSubMenu
 *
 * @author MAGG
 */
class PermisosSubMenu {

    private $alta;
    private $modificar;
    private $baja;
    private $consulta;
    private $nombre = "";
    private $empresa;

    /**
     * Asigna a los atributos el valor correspondiente (true en caso de tener el permiso, false en caso contrario) a los permisos del usuario con respecto al submenu especificado.
     * @param type $idUsuario id del Usuario.
     * @param type $pagina nombre de la pagina que esta asociada al submenu que se desea.
     * @return boolean true en caso de haber hecho la consulta con exito, false en caso contrario.
     */
    public function getPermisosSubmenu($idUsuario, $pagina) {
        if ($idUsuario == NULL) {
            return false;
        }
        $consulta = "SELECT pm.alta, pm.baja, pm.consulta, pm.modificacion, s.nom_sub
        FROM `c_usuario` AS u
        INNER JOIN m_submenu AS s ON s.ref_sub = '$pagina'
        INNER JOIN m_dpuestomenu AS pm ON u.IdUsuario = $idUsuario AND pm.IdPuesto = u.IdPuesto AND pm.IdSubmenu = s.id_sub;";        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->alta = ($rs['alta'] == "1") ? true : false;
            $this->baja = ($rs['baja'] == "1") ? true : false;
            $this->consulta = ($rs['consulta'] == "1") ? true : false;
            $this->modificar = ($rs['modificacion'] == "1") ? true : false;
            $this->nombre = $rs['nom_sub'];
            return true;
        }
        $this->alta = false;
        $this->baja = false;
        $this->consulta = false;
        $this->modificar = false;
        return false;
    }

    /**
     * True en caso de que el usuario tenga el permiso especial indicado dentro de la pagina señalada, false en caso contrario.
     * @param type $idUsuario id del Usuario.     
     * @param type $idPermisoEspecial
     * @return boolean
     */
    public function tienePermisoEspecial($idUsuario, $idPermisoEspecial) {
        $consulta = "SELECT pe.IdPermisoEspecial, pe.NombrePermiso FROM `permisos_especiales_puesto` AS pep 
            INNER JOIN c_usuario AS u ON u.IdUsuario = $idUsuario
            INNER JOIN c_puesto AS p ON u.IdPuesto = p.IdPuesto            
            INNER JOIN permisos_especiales AS pe ON pep.IdPermisoEspecial = pe.IdPermisoEspecial 
            AND pep.IdPuesto = p.IdPuesto AND pe.IdPermisoEspecial = $idPermisoEspecial;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return true;
        }
        return false;
    }

    /**
     * Obteiene todos los permisos especiales del submenu, y la columna de agregado (1 si ya esta asociado el permiso al puesto o 0(cero) en caso contrario)
     * @param type $idSubmenu id del submenu.
     * @param type $idPuesto id del puesto.
     * @return type resultSet con IdPermisoEspecial, NombrePermiso, agregado
     */
    public function getPermisosEspecialesSubMenuByPuesto($idPuesto) {
        $consulta = "SELECT p.IdPermisoEspecial,p.NombrePermiso,
                (
                        SELECT
                                COUNT(pep.IdPermisoEspecial)
                        FROM
                                `permisos_especiales_puesto` AS pep
                        WHERE pep.IdPuesto = '$idPuesto' AND pep.IdPermisoEspecial = p.IdPermisoEspecial
                ) AS agregado
        FROM `permisos_especiales` AS p;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getNombreTicketSistema() {        
        $nombre_objeto = "Ticket";
        $consulta = "SELECT Titulo FROM `c_titulos` WHERE IdTitulo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $nombre_objeto = $rs['Titulo'];
        }
        
        return $nombre_objeto;
    }

    public function getNombreTecnicoSistema() {
        $nombre_objeto = "Ticket";
        $consulta = "SELECT Titulo FROM `c_titulos` WHERE IdTitulo = 2;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $nombre_objeto = $rs['Titulo'];
        }
        
        return $nombre_objeto;
    }

    public function getNombreTipoReporteSistema() {
        include_once("Estado.class.php");
        $estado_obj = new Estado();
        $nombre_estado = "Falla";
        if ($estado_obj->getRegistroById(1)) {
            $nombre_estado = $estado_obj->getNombre();
        }
        return $nombre_estado;
    }
    
    public function getTitulo($idTitulo){
        $nombre_objeto = "";
        $consulta = "SELECT Titulo FROM `c_titulos` WHERE IdTitulo = $idTitulo;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $nombre_objeto = $rs['Titulo'];
        }
        
        return $nombre_objeto;
    }
    
    public function getLatitudSistema(){
        $latitud = "19.3983527";
        include_once("ParametroGlobal.class.php");
        $parametro_global = new ParametroGlobal();
        if($parametro_global->getRegistroById(17)){
            $latitud = $parametro_global->getValor();
        }
        return $latitud;
    }
    
    public function getLongitudSistema(){
        $longitud = "-99.0788268";
        include_once("ParametroGlobal.class.php");
        $parametro_global = new ParametroGlobal();
        if($parametro_global->getRegistroById(18)){
            $longitud = $parametro_global->getValor();
        }
        return $longitud;
    }

    public function getAlta() {
        return $this->alta;
    }

    public function setAlta($alta) {
        $this->alta = $alta;
    }

    public function getModificar() {
        return $this->modificar;
    }

    public function setModificar($modificar) {
        $this->modificar = $modificar;
    }

    public function getBaja() {
        return $this->baja;
    }

    public function setBaja($baja) {
        $this->baja = $baja;
    }

    public function getConsulta() {
        return $this->consulta;
    }

    public function setConsulta($consulta) {
        $this->consulta = $consulta;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getNombre() {
        return $this->nombre;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

}

?>
