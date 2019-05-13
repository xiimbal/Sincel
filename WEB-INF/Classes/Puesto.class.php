<?php
include_once("Conexion.class.php");
include_once("Catalogo.class.php");
class Puesto
{
    private $idPuesto;
    private $nombre;
    private $descripcion;
    private $ReAbrirTicket;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    
     public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_puesto WHERE IdPuesto='" . $id . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idPuesto = $rs['IdPuesto'];
            $this->nombre = $rs['Nombre'];
            $this->activo = $rs['Activo'];
            $this->descripcion = $rs['Descripcion'];
            $this->ReAbrirTicket = $rs['ReAbrirTicket'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_puesto(IdPuesto,Nombre,Descripcion,ReAbrirTicket,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->nombre . "','" . $this->descripcion . "',$this->ReAbrirTicket," . $this->activo . ",'" . $this->usuarioCreacion . "',
                now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");                
        $catalogo = new Catalogo(); $this->idPuesto = $catalogo->insertarRegistro($consulta);
        if ($this->idPuesto!=NULL && $this->idPuesto!=0) {            
            return true;
        }        
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_puesto SET Nombre = '" . $this->nombre . "',Descripcion = '" . $this->descripcion . "', ReAbrirTicket = $this->ReAbrirTicket,
            Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdPuesto='" . $this->idPuesto . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteRegistro() {
        $catalogo = new Catalogo(); 
        $consulta = ("DELETE FROM `permisos_especiales_puesto` WHERE IdPuesto = $this->idPuesto;");
        $catalogo->obtenerLista($consulta);
        $consulta = ("DELETE FROM c_puesto WHERE IdPuesto = '" . $this->idPuesto . "';");        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }    
    
    /**
     * Asocia el permiso especial al puesto del objeto actual.
     * @param type $idPermiso id del permiso
     * @return boolean true en caso de que sea un registro exitoso, false en caso contrario.
     */
    public function registrarPermisoEspecial($idPermiso){
        $consulta = "SELECT IdPuesto FROM `permisos_especiales_puesto` WHERE IdPermisoEspecial = $idPermiso AND IdPuesto = $this->idPuesto;";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows ($query)){/*Ya esta registrada esta combinacion*/            
            return true;
        }else{/*No esta registrada la combinacion*/
            $consulta = "INSERT INTO permisos_especiales_puesto(IdPuesto, IdPermisoEspecial,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,
                FechaUltimaModificacion,Pantalla) VALUES($this->idPuesto,$idPermiso,'$this->usuarioCreacion',NOW(),'$this->usuarioModificacion',NOW(),
                '$this->pantalla');";            
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            } return false;
        }
    }
    
    public function eliminarPermisosEspeciales(){
        $consulta = "DELETE FROM permisos_especiales_puesto WHERE IdPuesto = $this->idPuesto;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }return false;
    }

    public function registrarAreasPuesto($areas){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "DELETE FROM k_areapuesto WHERE IdPuesto = $this->idPuesto;";
        $catalogo->obtenerLista($consulta);
        if(isset($areas) && !empty($areas) && is_array($areas)){
            foreach ($areas as $value) {
                $consulta = "INSERT INTO k_areapuesto"
                        . "(IdEstado, IdPuesto, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) "
                        . "VALUES($value, $this->idPuesto,'$this->usuarioCreacion', NOW(), '$this->UsuarioModificacion', NOW(), '$this->pantalla');";
                $catalogo->obtenerLista($consulta);
            }
        }
    }
    
    public function obtenerAreasPuesto(){
        $consulta = "SELECT IdEstado FROM `k_areapuesto` WHERE IdPuesto = $this->idPuesto;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        $estados = array();
        
        while($rs = mysql_fetch_array($result)){
            array_push($estados, $rs['IdEstado']);
        }        
        return $estados;
    }

    public function getIdPuesto() {
        return $this->idPuesto;
    }

    public function setIdPuesto($idPuesto) {
        $this->idPuesto = $idPuesto;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
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

    public function getReAbrirTicket() {
        return $this->ReAbrirTicket;
    }

    public function setReAbrirTicket($ReAbrirTicket) {
        $this->ReAbrirTicket = $ReAbrirTicket;
    }


}
?>
