<?php

include_once ("Catalogo.class.php");
include_once ("CentroCosto.class.php");
include_once ("Cliente.class.php");

/**
 * Description of Contacto
 *
 * @author MAGG
 */
class Contacto {

    private $IdContacto;
    private $ClaveEspecialContacto;
    private $IdTipoContacto;
    private $Nombre;
    private $Telefono;
    private $Celular;
    private $CorreoElectronico;
    private $EnvioFactura;
    private $ContactoCobranza;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $NivelContacto;
    private $empresa;

    public function getRegstroByID($id) {
        $consulta = ("SELECT * FROM `c_contacto` WHERE IdContacto = $id;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdContacto = $rs['IdContacto'];
            $this->ClaveEspecialContacto = $rs['ClaveEspecialContacto'];
            $this->IdTipoContacto = $rs['IdTipoContacto'];
            $this->Nombre = $rs['Nombre'];
            $this->Telefono = $rs['Telefono'];
            $this->Celular = $rs['Celular'];
            $this->CorreoElectronico = $rs['CorreoElectronico'];
            $this->EnvioFactura = $rs['EnvioFactura'];
            $this->ContactoCobranza = $rs['ContactoCobranza'];
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
    public function getTodosContactosCliente($ClaveCliente){
        $consulta = ("SELECT ctt.IdContacto, ctt.Nombre, tc.Nombre AS TipoContacto, cc.Nombre AS localidad,
                ctt.CorreoElectronico, ctt.Telefono, ctt.Celular
            FROM `c_cliente` AS c
            LEFT JOIN c_contacto AS ctt ON ctt.ClaveEspecialContacto = c.ClaveCliente
            LEFT JOIN c_tipocontacto AS tc ON tc.IdTipoContacto = ctt.IdTipoContacto
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ctt.ClaveEspecialContacto
            WHERE c.ClaveCliente = '$ClaveCliente' AND ctt.Activo = 1
            GROUP BY Nombre, tc.IdTipoContacto
            UNION
            SELECT ctt.IdContacto, ctt.Nombre, tc.Nombre AS TipoContacto, cc2.Nombre AS localidad,
            ctt.CorreoElectronico, ctt.Telefono, ctt.Celular
            FROM `c_cliente` AS c
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCliente = c.ClaveCliente
            LEFT JOIN c_contacto AS ctt ON ctt.ClaveEspecialContacto = cc.ClaveCentroCosto
            LEFT JOIN c_tipocontacto AS tc ON tc.IdTipoContacto = ctt.IdTipoContacto
            LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ctt.ClaveEspecialContacto
            WHERE c.ClaveCliente = '$ClaveCliente' AND cc.Activo = 1 AND ctt.Activo = 1
            GROUP BY Nombre, tc.IdTipoContacto
            ORDER BY Nombre;");
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getContactosPorIdTipo($idTipo){
        $consulta = ("SELECT * FROM c_contacto WHERE IdTipoContacto = $idTipo");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getRegistroValidacion($clave) {
        $consulta = ("SELECT * FROM `c_contacto` WHERE ClaveEspecialContacto = '$clave' AND Activo = 1;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getRegistrosLocalidadCliente($clave){
        $consulta = "SELECT ctt.IdContacto,ctt.ClaveEspecialContacto, ctt.Nombre, 
            ctt.Celular,ctt.Telefono,ctt.IdTipoContacto,
            ctt.CorreoElectronico, tc.Nombre AS TipoContacto 
            FROM c_centrocosto AS cc
            LEFT JOIN c_contacto AS ctt ON ctt.ClaveEspecialContacto = cc.ClaveCentroCosto OR ctt.ClaveEspecialContacto = cc.ClaveCliente
            LEFT JOIN c_tipocontacto AS tc ON tc.IdTipoContacto = ctt.IdTipoContacto
            WHERE cc.ClaveCentroCosto = '$clave' AND ctt.Activo = 1 AND cc.Activo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {
        if (isset($this->IdTipoContacto) && $this->IdTipoContacto != "") {
            $idTipoContacto = $this->IdTipoContacto;
        } else {
            $idTipoContacto = "9";
        }
        if (isset($this->Telefono) && $this->Telefono != "") {
            $telefono = "'$this->Telefono'";
        } else {
            $telefono = "null";
        }
        if (isset($this->Celular) && $this->Celular != "") {
            $celular = "'$this->Celular'";
        } else {
            $celular = "null";
        }
        if (isset($this->CorreoElectronico) && $this->CorreoElectronico != "") {
            $correo = "'$this->CorreoElectronico'";
        } else {
            $correo = "'falta@correo.com'";
        }
        
        if(!isset($this->EnvioFactura) || $this->EnvioFactura == ""){
            $this->EnvioFactura = "0";
        }
        
        if(!isset($this->ContactoCobranza) || $this->ContactoCobranza == ""){
            $this->ContactoCobranza = "0";
        }
        $consulta = ("INSERT INTO c_contacto (ClaveEspecialContacto,Nombre,IdTipoContacto,Telefono, Celular, CorreoElectronico, Activo,
            EnvioFactura, ContactoCobranza, UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('$this->ClaveEspecialContacto','" . str_replace("'", "´", $this->Nombre) . "',$idTipoContacto,$telefono,$celular,$correo,$this->Activo,
            $this->EnvioFactura,$this->ContactoCobranza,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newRegistroCompleto() {
        if(!isset($this->EnvioFactura) || $this->EnvioFactura == ""){
            $this->EnvioFactura = "0";
        }
        
        if(!isset($this->ContactoCobranza) || $this->ContactoCobranza == ""){
            $this->ContactoCobranza = "0";
        }
        
        $consulta = ("INSERT INTO c_contacto (ClaveEspecialContacto,Nombre,EnvioFactura, ContactoCobranza, Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla,CorreoElectronico,IdTipoContacto,Telefono,Celular) 
            VALUES('$this->ClaveEspecialContacto','" . str_replace("'", "´", $this->Nombre) . "',$this->EnvioFactura,$this->ContactoCobranza,$this->Activo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla','$this->CorreoElectronico',$this->IdTipoContacto,'$this->Telefono','$this->Celular');");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if (isset($this->IdTipoContacto) && $this->IdTipoContacto != "") {
            $idTipoContacto = $this->IdTipoContacto;
        } else {
            $idTipoContacto = "9";
        }
        if (isset($this->Telefono) && $this->Telefono != "") {
            $telefono = "'$this->Telefono'";
        } else {
            $telefono = "null";
        }
        if (isset($this->Celular) && $this->Celular != "") {
            $celular = "'$this->Celular'";
        } else {
            $celular = "null";
        }
        if (isset($this->CorreoElectronico) && $this->CorreoElectronico != "") {
            $correo = "'$this->CorreoElectronico'";
        } else {
            $correo = "'falta@correo.com'";
        }
        
        if(!isset($this->EnvioFactura) || $this->EnvioFactura == ""){
            $this->EnvioFactura = "0";
        }
        
        if(!isset($this->ContactoCobranza) || $this->ContactoCobranza == ""){
            $this->ContactoCobranza = "0";
        }
        
        $consulta = ("UPDATE c_contacto SET ClaveEspecialContacto = '$this->ClaveEspecialContacto', EnvioFactura = $this->EnvioFactura, ContactoCobranza = $this->ContactoCobranza,
            Nombre = '" . str_replace("'", "´", $this->Nombre) . "', IdTipoContacto = $idTipoContacto, Telefono = $telefono, Celular = $celular, CorreoElectronico = $correo,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', Activo = $this->Activo,
            FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
            WHERE IdContacto = $this->IdContacto;");
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getContactoByClave($clave) {
        $consulta = ("SELECT * FROM `c_contacto` WHERE IdContacto = '$clave';");
        $catalogo = new Catalogo();
        $cliente = new Cliente();
        $localidad = new CentroCosto();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
            $cliente->setEmpresa($this->empresa);
            $localidad->setEmpresa($this->empresa);
        }
        
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdContacto = $rs['IdContacto'];
            $this->ClaveEspecialContacto = $rs['ClaveEspecialContacto'];
            $this->IdTipoContacto = $rs['IdTipoContacto'];
            $this->Nombre = $rs['Nombre'];
            $this->Telefono = $rs['Telefono'];
            $this->Celular = $rs['Celular'];
            $this->CorreoElectronico = $rs['CorreoElectronico'];
            $this->EnvioFactura = $rs['EnvioFactura'];
            $this->ContactoCobranza = $rs['ContactoCobranza'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            
            if($cliente->getRegistroById($this->ClaveEspecialContacto)){
                $this->NivelContacto = 2;
            }else if($localidad->getRegistroById($this->ClaveEspecialContacto)){
                $this->NivelContacto = 1;
            }else{
                $this->NivelContacto = 0;
            }
            
            return true;
        }
        return false;
    }

    public function getContactoByClaveEspecial($clave) {
        $consulta = ("SELECT * FROM `c_contacto` WHERE ClaveEspecialContacto = '$clave' ORDER BY Telefono DESC;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdContacto = $rs['IdContacto'];
            $this->ClaveEspecialContacto = $rs['ClaveEspecialContacto'];
            $this->IdTipoContacto = $rs['IdTipoContacto'];
            $this->Nombre = $rs['Nombre'];
            $this->Telefono = $rs['Telefono'];
            $this->Celular = $rs['Celular'];
            $this->CorreoElectronico = $rs['CorreoElectronico'];
            $this->EnvioFactura = $rs['EnvioFactura'];
            $this->ContactoCobranza = $rs['ContactoCobranza'];
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
    
    public function getUltimoIdContacto(){
        $query = "SELECT MAX(IdContacto) AS IdContacto FROM c_contacto";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($query);
        while($rs = mysql_fetch_array($result)){
           $this->IdContacto = $rs['IdContacto'];
        }
    }
    
    public function deleteRegistro($idContacto){
        $consulta = "DELETE FROM `c_contacto` WHERE IdContacto = $idContacto;";        
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdContacto() {
        return $this->IdContacto;
    }

    public function setIdContacto($IdContacto) {
        $this->IdContacto = $IdContacto;
    }

    public function getClaveEspecialContacto() {
        return $this->ClaveEspecialContacto;
    }

    public function setClaveEspecialContacto($ClaveEspecialContacto) {
        $this->ClaveEspecialContacto = $ClaveEspecialContacto;
    }

    public function getIdTipoContacto() {
        return $this->IdTipoContacto;
    }

    public function setIdTipoContacto($IdTipoContacto) {
        $this->IdTipoContacto = $IdTipoContacto;
    }

    public function getNombre() {
        return $this->Nombre;
    }

    public function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    public function getTelefono() {
        return $this->Telefono;
    }

    public function setTelefono($Telefono) {
        $this->Telefono = $Telefono;
    }

    public function getCelular() {
        return $this->Celular;
    }

    public function setCelular($Celular) {
        $this->Celular = $Celular;
    }

    public function getCorreoElectronico() {
        return $this->CorreoElectronico;
    }

    public function setCorreoElectronico($CorreoElectronico) {
        $this->CorreoElectronico = $CorreoElectronico;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getNivelContacto() {
        return $this->NivelContacto;
    }

    function setNivelContacto($NivelContacto) {
        $this->NivelContacto = $NivelContacto;
    }
    
    function getEnvioFactura() {
        return $this->EnvioFactura;
    }

    function getContactoCobranza() {
        return $this->ContactoCobranza;
    }

    function setEnvioFactura($EnvioFactura) {
        $this->EnvioFactura = $EnvioFactura;
    }

    function setContactoCobranza($ContactoCobranza) {
        $this->ContactoCobranza = $ContactoCobranza;
    }
}

?>
