<?php

include_once("Catalogo.class.php");

/**
 * Description of Promocion
 *
 * @author MAGG
 */
class Promocion {

    private $IdPromocion;
    private $ClaveCliente;
    private $Descripcion;
    private $Vigencia;
    private $Telefono;
    private $CodigoPromocion;
    private $IdGiro;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    private $Titulo;
    private $Localidad;
    private $IdUsuario;
    private $Vigencia_Fin;
    private $ManejaCupon;
    private $NumeroCupones;
    private $CuponesUsados;
    private $Imagen;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_promocion WHERE IdPromocion = '" . $id . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdPromocion = $rs['IdPromocion'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->Descripcion = $rs['Descripcion'];
            $this->Vigencia = $rs['Vigencia'];
            $this->Telefono = $rs['Telefono'];
            $this->CodigoPromocion = $rs['CodigoPromocion'];
            $this->IdGiro = $rs['IdGiro'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->Titulo = $rs['Titulo'];
            $this->Localidad = $rs['Localidad'];
            $this->IdUsuario = $rs['IdUsuario'];
            $this->Vigencia_Fin = $rs['Vigencia_Fin'];
            $this->ManejaCupon = $rs['ManejaCupon'];
            $this->NumeroCupones = $rs['NumeroCupones'];
            $this->CuponesUsados = $rs['CuponesUsados'];
            $this->Imagen = $rs['Imagen'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_promocion(IdPromocion,Titulo,Localidad,IdUsuario,ClaveCliente,Descripcion,Vigencia,Vigencia_Fin,
            CodigoPromocion,ManejaCupon,NumeroCupones,CuponesUsados,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'$this->Titulo','$this->Localidad',$this->IdUsuario,'$this->ClaveCliente','$this->Descripcion','$this->Vigencia','$this->Vigencia_Fin',
            '$this->CodigoPromocion',$this->ManejaCupon,$this->NumeroCupones,$this->CuponesUsados,
            $this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdPromocion = $catalogo->insertarRegistro($consulta);
        if ($this->IdPromocion != NULL && $this->IdPromocion != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_promocion SET Titulo = '$this->Titulo', Localidad = '$this->Localidad', IdUsuario = $this->IdUsuario, ClaveCliente = '$this->ClaveCliente',
            Descripcion = '$this->Descripcion', Vigencia = '$this->Vigencia', Vigencia_Fin = '$this->Vigencia_Fin',
            CodigoPromocion = '$this->CodigoPromocion', ManejaCupon = $this->ManejaCupon, NumeroCupones = $this->NumeroCupones, CuponesUsados = $this->CuponesUsados,    
            Activo = " . $this->Activo . ", UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "', FechaUltimaModificacion = NOW(),
            Pantalla = '" . $this->Pantalla . "' WHERE IdPromocion=" . $this->IdPromocion . ";");

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

    function deleteRegistro() {
        $consulta = ("DELETE FROM c_promocion WHERE IdPromocion = " . $this->IdPromocion . ";");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getPromociones($Clavecliente) {
        $where = "";
        if(!empty($Clavecliente)){
            $where = " c.ClaveCliente = '$Clavecliente' AND ";
        }
        $consulta = ("SELECT p.*, c.ClaveCliente, c.NombreRazonSocial
            FROM `c_promocion` AS p
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = p.ClaveCliente
            WHERE $where p.Activo = 1 AND p.Vigencia <= NOW() AND p.Vigencia_Fin >= NOW() AND c.Activo = 1 
            AND (p.ManejaCupon = 0 OR (p.CuponesUsados < p.NumeroCupones));");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    public function actualizarImagen($archivo){
        $consulta = "UPDATE `c_promocion` SET Imagen = '$archivo' WHERE IdPromocion = $this->IdPromocion;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if($result == "1"){
            return true;
        }
        return false;
    }
    
    public function insertaPromocionCuponera($idUsuario){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT ("
                . "CASE WHEN ISNULL(NumeroCupones) THEN 1 "
                . "WHEN NumeroCupones <= 0 THEN 1 "
                . "WHEN NumeroCupones > CuponesUsados THEN 1 "
                . "ELSE 0 END) AS CuponesDisponibles FROM c_promocion WHERE IdPromocion = $this->IdPromocion;";
        $result = $catalogo->obtenerLista($consulta);
        $cupones_disponibles = 1;
        while($rs = mysql_fetch_array($result)){
            $cupones_disponibles = $rs['CuponesDisponibles'];
        }
                
        if($cupones_disponibles == "1"){
            $consulta = "INSERT INTO k_usuariopromocion(IdUsuario, IdPromocion, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, "
                . "FechaUltimaModificacion, Pantalla) VALUES($idUsuario,$this->IdPromocion, '$this->UsuarioCreacion', "
                . "NOW(), '$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
            $result = $catalogo->obtenerLista($consulta);
            if($result == "1"){
                $consulta = "UPDATE c_promocion SET CuponesUsados = CuponesUsados+1 WHERE IdPromocion = $this->IdPromocion;";
                $catalogo->obtenerLista($consulta);
                return 1;
            }
            return -4;
        }else{
            return -3;
        }        
    }
    
    public function obtenerCuponeraUsuario($Clavecliente, $idUsuario){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $where = "";
        if(!empty($Clavecliente)){
            $where = " c.ClaveCliente = '$Clavecliente' AND ";
        }
        
        $consulta = "SELECT p.ClaveCliente, p.IdPromocion, c.NombreRazonSocial, p.Descripcion, p.Vigencia, p.Vigencia_Fin, p.Titulo, p.Imagen, p.ManejaCupon,
            (CASE WHEN p.ManejaCupon = 1 THEN p.CodigoPromocion ELSE NULL END) AS CodigoPromocion 
            FROM `k_usuariopromocion` AS kup
            LEFT JOIN c_promocion AS p ON p.IdPromocion = kup.IdPromocion
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = p.ClaveCliente
            WHERE $where p.Activo = 1 AND c.Activo = 1 AND NOW() <= p.Vigencia_Fin AND kup.IdUsuario = $idUsuario;";
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function getIdPromocion() {
        return $this->IdPromocion;
    }

    public function setIdPromocion($IdPromocion) {
        $this->IdPromocion = $IdPromocion;
    }

    public function getClaveCliente() {
        return $this->ClaveCliente;
    }

    public function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }

    public function getDescripcion() {
        return $this->Descripcion;
    }

    public function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    public function getVigencia() {
        return $this->Vigencia;
    }

    public function setVigencia($Vigencia) {
        $this->Vigencia = $Vigencia;
    }

    public function getTelefono() {
        return $this->Telefono;
    }

    public function setTelefono($Telefono) {
        $this->Telefono = $Telefono;
    }

    public function getCodigoPromocion() {
        return $this->CodigoPromocion;
    }

    public function setCodigoPromocion($CodigoPromocion) {
        $this->CodigoPromocion = $CodigoPromocion;
    }

    public function getIdGiro() {
        return $this->IdGiro;
    }

    public function setIdGiro($IdGiro) {
        $this->IdGiro = $IdGiro;
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

    function getLocalidad() {
        return $this->Localidad;
    }

    function getIdUsuario() {
        return $this->IdUsuario;
    }

    function getVigencia_Fin() {
        return $this->Vigencia_Fin;
    }

    function getManejaCupon() {
        return $this->ManejaCupon;
    }

    function getNumeroCupones() {
        return $this->NumeroCupones;
    }

    function getCuponesUsados() {
        return $this->CuponesUsados;
    }
    
    function getTitulo() {
        return $this->Titulo;
    }

    function setTitulo($Titulo) {
        $this->Titulo = $Titulo;
    }
    
    function setLocalidad($Localidad) {
        $this->Localidad = $Localidad;
    }

    function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }

    function setVigencia_Fin($Vigencia_Fin) {
        $this->Vigencia_Fin = $Vigencia_Fin;
    }

    function setManejaCupon($ManejaCupon) {
        $this->ManejaCupon = $ManejaCupon;
    }

    function setNumeroCupones($NumeroCupones) {
        $this->NumeroCupones = $NumeroCupones;
    }

    function setCuponesUsados($CuponesUsados) {
        $this->CuponesUsados = $CuponesUsados;
    }
    
    function getImagen() {
        return $this->Imagen;
    }

    function setImagen($Imagen) {
        $this->Imagen = $Imagen;
    }
}

?>
