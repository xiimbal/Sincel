<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class AutorizarPlantilla {

    private $idPlantilla;
    private $activo;
    private $idCampania;
    private $idTurno;
    private $fecha;
    private $hora;
    private $estatus;
    private $tipoEvento;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $NombreCliente;
    private $ClaveCentroCosto;
    private $NoSerieEquipo;
    private $NombreCentroCosto;
    private $ClaveCliente;
    private $IdUsuario;

    public function getRegistroById($id) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT cp.* FROM c_plantilla cp WHERE idPlantilla='" . $id . "'");
        if ($rs = mysql_fetch_array($query)) {
            $this->idPlantilla = $rs['idPlantilla'];
            $this->idCampania = $rs['idCampania'];
            $this->idTurno = $rs['idTurno'];
            $this->fecha = $rs['Fecha'];
            $this->hora = $rs['Hora'];
            $this->estatus = $rs['Estatus'];
            $this->tipoEvento = $rs['TipoEvento'];
            $this->activo = $rs['Activo'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $catalogo = new Catalogo();
        $consulta = "INSERT INTO c_plantilla(idCampania, idTurno, idTicket, TipoEvento, Fecha, Hora, Estatus, Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla, IdUsuarioAutorizacion)
            VALUES('" . $this->idCampania . "','" . $this->idTurno . "',NULL,'" . $this->tipoEvento . "','" . $this->fecha . "','" . $this->hora . "',0,'" . $this->activo . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "',NULL);";


        $this->idPlantilla = $catalogo->insertarRegistro($consulta);
        if ($this->idPlantilla != NULL && $this->idPlantilla != 0) {
            return true;
        }
        return false;
    }
    
    public function editRegistro() {
        $catalogo = new Catalogo();

        $query = $catalogo->obtenerLista("UPDATE c_plantilla SET TipoEvento =" . $this->tipoEvento . ", Fecha = '" . $this->fecha . "', Hora = '" . $this->hora . "', Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE idPlantilla='" . $this->idPlantilla . "';");

        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $catalogo = new Catalogo();
        $q = 0;
        $query1 = $catalogo->obtenerLista("SELECT kp.idK_Plantilla AS KP, kpa.idK_Plantilla_asistencia KPA FROM c_plantilla AS cp INNER JOIN k_plantilla AS kp 
                                            ON cp.idPlantilla=kp.idPlantilla JOIN k_plantilla_asistencia AS kpa ON kpa.idK_Plantilla=kp.idK_Plantilla 
                                            WHERE cp.idPlantilla='" . $this->idPlantilla . "'");

        while ($rs = mysql_fetch_array($query1)) {
            $catalogo1 = new Catalogo();
            $query = $catalogo1->obtenerLista("DELETE FROM k_plantilla_asistencia WHERE idK_Plantilla_asistencia = " . $rs['KPA'] . ";");
            if ($query == 1) {
                $query = $catalogo1->obtenerLista("DELETE FROM k_plantilla WHERE idK_Plantilla = " . $rs['KP'] . ";");
                if ($query == 1) {
                    $q++;
                } else {
                    
                    return false;
                }
            } else {
                
                return false;
            }
        }
        if ($q != 0) {
            $query = $catalogo->obtenerLista("DELETE FROM c_plantilla WHERE idPlantilla = " . $this->idPlantilla . ";");
            if ($query == 1) {
                return true;
            }
            
            return false;
        }
    }
    
    public function UsuarioAgregado(){
        $catalogo = new Catalogo();
        $querycp = $catalogo->obtenerLista("SELECT * FROM c_cambio_plantilla WHERE IdUsuario='" . $this->IdUsuario . "' AND IdPlantilla=".$this->idPlantilla.";");
        if (mysql_num_rows($querycp) > 0) {
            return true;
        }else{
            return false;
        }
    }

    public function getIdPlantilla() {
        return $this->idPlantilla;
    }

    public function getIdCampania() {
        return $this->idCampania;
    }

    public function getIdTurno() {
        return $this->idTurno;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getHora() {
        return $this->hora;
    }
    
    public function getEstatus() {
        return $this->estatus;
    }

    public function getTipoEvento() {
        return $this->tipoEvento;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setIdPlantilla($idPlantilla) {
        $this->idPlantilla = $idPlantilla;
    }

    public function setIdCampania($idCampania) {
        $this->idCampania = $idCampania;
    }

    public function setIdTurno($idTurno) {
        $this->idTurno = $idTurno;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setHora($hora) {
        $this->hora = $hora;
    }
    
     public function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

    public function setTipoEvento($tipoEvento) {
        $this->tipoEvento = $tipoEvento;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }
    
    //Datos de Ticket
    public function getNombreCliente() {
        return $this->NombreCliente;
    }
    
    public function setNombreCliente($NombreCliente) {
        $this->NombreCliente = $NombreCliente;
    }
    
    public function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
    }
    
    public function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
    }
    
    public function getClaveCliente() {
        return $this->ClaveCliente;
    }
    
    public function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }
    
    public function getNombreCentroCosto() {
        return $this->NombreCentroCosto;
    }
    
    public function setNombreCentroCosto($NombreCentroCosto) {
        $this->NombreCentroCosto = $NombreCentroCosto;
    }
    
    public function getNoSerieEquipo() {
        return $this->NoSerieEquipo;
    }
    
    public function setNoSerieEquipo($NoSerieEquipo) {
        $this->NoSerieEquipo = $NoSerieEquipo;
    }
    
    public function getIdUsuario() {
        return $this->IdUsuario;
    }
    
    public function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }
}
