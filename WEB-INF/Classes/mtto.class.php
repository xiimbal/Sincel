<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
/**
 * Description of Mantenimiento
 *
 * @author MAGG
 */
class mtto {

    private $id_mtto;
    private $NoSerie;
    private $Fecha;
    private $estatus;
    private $cliente;
    private $localidad;
    private $usuarioultimamodificacion;
    private $userCreacion;

    public function nuevomtto() {
        $consulta = ("INSERT INTO c_mantenimiento(ClaveCentroCosto,NoSerie,Fecha,Estatus,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES('" . $this->localidad . "','" . $this->NoSerie . "','" . $this->Fecha . "','0','" . $this->userCreacion . "',NOW(),'" . $this->userCreacion . "',NOW(),'PHP NuevoMtto');");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getMantenimientoByID() {        
        $consulta = "SELECT
                c_mantenimiento.IdMantenimiento AS ID,
                c_mantenimiento.NoSerie AS NoSerie,
                c_mantenimiento.Fecha AS Fecha,
                if(c_mantenimiento.Estatus=0,'En proceso','') AS Estatus,
                c_centrocosto.ClaveCentroCosto AS CentroCosto,
                c_cliente.ClaveCliente AS Cliente
        FROM
                c_mantenimiento
        INNER JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
        INNER JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente
        WHERE c_mantenimiento.IdMantenimiento='" . $this->id_mtto . "'";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->NoSerie = $rs['NoSerie'];
            $this->Fecha = $rs['Fecha'];
            $this->estatus = $rs['Estatus'];
            $this->localidad = $rs['CentroCosto'];
            $this->cliente = $rs['Cliente'];
        }        
        return $query;
    }

    public function ActualizarFecha() {
        $consulta = ("UPDATE c_mantenimiento SET c_mantenimiento.Fecha='" . $this->Fecha . "',UsuarioUltimaModificacion = '" . $this->usuarioultimamodificacion . "',FechaUltimaModificacion = now() WHERE c_mantenimiento.IdMantenimiento='" . $this->id_mtto . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deletebyid() {
        $consulta = ("DELETE FROM c_mantenimiento WHERE c_mantenimiento.IdMantenimiento = " . $this->id_mtto . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId_mtto() {
        return $this->id_mtto;
    }

    public function setId_mtto($id_mtto) {
        $this->id_mtto = $id_mtto;
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function getLocalidad() {
        return $this->localidad;
    }

    public function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    public function getUsuarioultimamodificacion() {
        return $this->usuarioultimamodificacion;
    }

    public function setUsuarioultimamodificacion($usuarioultimamodificacion) {
        $this->usuarioultimamodificacion = $usuarioultimamodificacion;
    }
    public function getUserCreacion() {
        return $this->userCreacion;
    }

    public function setUserCreacion($userCreacion) {
        $this->userCreacion = $userCreacion;
    }


}

?>
