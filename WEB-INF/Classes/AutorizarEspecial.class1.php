<?php

include_once("Catalogo.class.php");
include_once("Ticket.class.php");
include_once("DomicilioTicket.class.php");
include_once("TicketRelacion.class.php");

class AutorizarEspecial {

    private $idEspecial;
    private $idEmpleado;
    private $contacto;
    private $datoContacto;
    private $tipoServicio;
    private $idCampania;
    private $idTurno;
    private $fecha;
    private $hora;
    private $origen;
    private $destino;
    private $Nombre_ruta;
    private $calle_or;
    private $exterior_or;
    private $interior_or;
    private $colonia_or;
    private $ciudad_or;
    private $delegacion_or;
    private $cp_or;
    private $localidad_or;
    private $estado_or;
    private $latitud_or;
    private $longitud_or;
    private $comentario_or;
    private $cuadrante;
    private $calle_des;
    private $exterior_des;
    private $interior_des;
    private $colonia_des;
    private $ciudad_des;
    private $delegacion_des;
    private $cp_des;
    private $localidad_des;
    private $estado_des;
    private $latitud_des;
    private $longitud_des;
    private $comentario_des;
    private $PrecioParticular;
    private $activo;
    private $usuarioCreacion;
    private $usuarioUltimaModificacion;
    private $pantalla;
    private $idTicket;
    private $idKEspecial;
    private $Informacion;
    private $empresa;
    private $ruta = false;

    public function getRegistroById($id) {
        $consulta = ("SELECT DATE(ce.FechaHora) AS Fecha, TIME(ce.FechaHora) AS Hora, ce.* FROM c_especial AS ce WHERE ce.idEspecial =" . $id . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idEspecial = $rs['idEspecial'];
            $this->idEmpleado = $rs['idUsuario'];
            $this->contacto = $rs['IdFormaContacto'];
            $this->tipoServicio = $rs['TipoServicio'];
            $this->idCampania = $rs['idCampania'];
            $this->idTurno = $rs['idTurno'];
            $this->datoContacto = $rs['DatoContacto'];
            $this->fecha = $rs['Fecha'];
            $this->hora = $rs['Hora'];
            $this->origen = $rs['Origen'];
            $this->destino = $rs['Destino'];
            $this->Informacion = $rs['Informacion'];

            $this->calle_or = $rs['Calle_or'];
            $this->exterior_or = $rs['NoExterior_or'];
            $this->interior_or = $rs['NoInterior_or'];
            $this->colonia_or = $rs['Colonia_or'];
            $this->ciudad_or = $rs['Ciudad_or'];
            $this->delegacion_or = $rs['Delegacion_or'];
            $this->cp_or = $rs['CodigoPostal_or'];
            $this->localidad_or = $rs['Localidad_or'];
            $this->estado_or = $rs['Estado_or'];
            $this->latitud_or = $rs['Latitud_or'];
            $this->longitud_or = $rs['Longitud_or'];
            $this->comentario_or = $rs['Comentario_or'];
            $this->cuadrante = $rs['Cuadrante'];

            $this->calle_des = $rs['Calle_des'];
            $this->exterior_des = $rs['NoExterior_des'];
            $this->interior_des = $rs['NoInterior_des'];
            $this->colonia_des = $rs['Colonia_des'];
            $this->ciudad_des = $rs['Ciudad_des'];
            $this->delegacion_des = $rs['Delegacion_des'];
            $this->cp_des = $rs['CodigoPostal_des'];
            $this->localidad_des = $rs['Localidad_des'];
            $this->estado_des = $rs['Estado_des'];
            $this->latitud_des = $rs['Latitud_des'];
            $this->longitud_des = $rs['Longitud_des'];
            $this->comentario_des = $rs['Comentario_des'];
            $this->PrecioParticular = $rs['PrecioParticular'];
            
            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            //$this->FechaCreacion = $rs['FechaCreacion'];
            $this->usuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            //$this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->idTicket = $rs['idTicket'];

            return true;
        }
        return false;
    }
    
    public function getRegistroByIdTicket($idTicket) {
        $consulta = ("SELECT DATE(ce.FechaHora) AS Fecha, TIME(ce.FechaHora) AS Hora, ce.* FROM c_especial AS ce WHERE ce.idTicket = $idTicket;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idEspecial = $rs['idEspecial'];
            $this->idEmpleado = $rs['idUsuario'];
            $this->contacto = $rs['IdFormaContacto'];
            $this->tipoServicio = $rs['TipoServicio'];
            $this->idCampania = $rs['idCampania'];
            $this->idTurno = $rs['idTurno'];
            $this->datoContacto = $rs['DatoContacto'];
            $this->fecha = $rs['Fecha'];
            $this->hora = $rs['Hora'];
            $this->origen = $rs['Origen'];
            $this->destino = $rs['Destino'];
            $this->Informacion = $rs['Informacion'];

            $this->calle_or = $rs['Calle_or'];
            $this->exterior_or = $rs['NoExterior_or'];
            $this->interior_or = $rs['NoInterior_or'];
            $this->colonia_or = $rs['Colonia_or'];
            $this->ciudad_or = $rs['Ciudad_or'];
            $this->delegacion_or = $rs['Delegacion_or'];
            $this->cp_or = $rs['CodigoPostal_or'];
            $this->localidad_or = $rs['Localidad_or'];
            $this->estado_or = $rs['Estado_or'];
            $this->latitud_or = $rs['Latitud_or'];
            $this->longitud_or = $rs['Longitud_or'];
            $this->comentario_or = $rs['Comentario_or'];
            $this->cuadrante = $rs['Cuadrante'];

            $this->calle_des = $rs['Calle_des'];
            $this->exterior_des = $rs['NoExterior_des'];
            $this->interior_des = $rs['NoInterior_des'];
            $this->colonia_des = $rs['Colonia_des'];
            $this->ciudad_des = $rs['Ciudad_des'];
            $this->delegacion_des = $rs['Delegacion_des'];
            $this->cp_des = $rs['CodigoPostal_des'];
            $this->localidad_des = $rs['Localidad_des'];
            $this->estado_des = $rs['Estado_des'];
            $this->latitud_des = $rs['Latitud_des'];
            $this->longitud_des = $rs['Longitud_des'];
            $this->comentario_des = $rs['Comentario_des'];
            $this->PrecioParticular = $rs['PrecioParticular'];
            
            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            //$this->FechaCreacion = $rs['FechaCreacion'];
            $this->usuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            //$this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->idTicket = $rs['idTicket'];

            return true;
        }
        return false;
    }

    public function getRegistroDetalleById($id) {
        $consulta = ("SELECT * FROM `k_especialescala` WHERE idkEspecial = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idKEspecial = $rs['idkEspecial'];
            $this->IdEspecial = $rs['IdEspecial'];
            $this->Calle_des = $rs['Calle_des'];
            $this->NoExterior_des = $rs['NoExterior_des'];
            $this->NoInterior_des = $rs['NoInterior_des'];
            $this->Colonia_des = $rs['Colonia_des'];
            $this->Ciudad_des = $rs['Ciudad_des'];
            $this->Delegacion_des = $rs['Delegacion_des'];
            $this->CodigoPostal_des = $rs['CodigoPostal_des'];
            $this->Localidad_des = $rs['Localidad_des'];
            $this->Estado_des = $rs['Estado_des'];
            $this->Latitud_des = $rs['Latitud_des'];
            $this->Longitud_des = $rs['Longitud_des'];
            $this->Comentario_des = $rs['Comentario_des'];
            $this->idTicket = $rs['idTicket'];
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
    
    public function getRegistroRutaById($id) {
        $consulta = ("SELECT DATE(ce.FechaHora) AS Fecha, TIME(ce.FechaHora) AS Hora, ce.* FROM c_rutaespecial AS ce WHERE ce.idEspecial =" . $id . ";");        
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idEspecial = $rs['idEspecial'];
            $this->idEmpleado = $rs['idUsuario'];
            $this->contacto = $rs['IdFormaContacto'];
            $this->tipoServicio = $rs['TipoServicio'];
            $this->Nombre_ruta = $rs['Nombre_ruta'];
            $this->idCampania = $rs['idCampania'];
            $this->idTurno = $rs['idTurno'];
            $this->datoContacto = $rs['DatoContacto'];
            $this->fecha = $rs['Fecha'];
            $this->hora = $rs['Hora'];
            $this->origen = $rs['Origen'];
            $this->destino = $rs['Destino'];

            $this->calle_or = $rs['Calle_or'];
            $this->exterior_or = $rs['NoExterior_or'];
            $this->interior_or = $rs['NoInterior_or'];
            $this->colonia_or = $rs['Colonia_or'];
            $this->ciudad_or = $rs['Ciudad_or'];
            $this->delegacion_or = $rs['Delegacion_or'];
            $this->cp_or = $rs['CodigoPostal_or'];
            $this->localidad_or = $rs['Localidad_or'];
            $this->estado_or = $rs['Estado_or'];
            $this->latitud_or = $rs['Latitud_or'];
            $this->longitud_or = $rs['Longitud_or'];
            $this->comentario_or = $rs['Comentario_or'];
            $this->cuadrante = $rs['Cuadrante'];

            $this->calle_des = $rs['Calle_des'];
            $this->exterior_des = $rs['NoExterior_des'];
            $this->interior_des = $rs['NoInterior_des'];
            $this->colonia_des = $rs['Colonia_des'];
            $this->ciudad_des = $rs['Ciudad_des'];
            $this->delegacion_des = $rs['Delegacion_des'];
            $this->cp_des = $rs['CodigoPostal_des'];
            $this->localidad_des = $rs['Localidad_des'];
            $this->estado_des = $rs['Estado_des'];
            $this->latitud_des = $rs['Latitud_des'];
            $this->longitud_des = $rs['Longitud_des'];
            $this->comentario_des = $rs['Comentario_des'];
            $this->PrecioParticular = $rs['PrecioParticular'];

            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            //$this->FechaCreacion = $rs['FechaCreacion'];
            $this->usuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            //$this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->idTicket = $rs['idTicket'];

            return true;
        }
        return false;
    }

    public function getRegistroRutaDetalleById($id) {
        $consulta = ("SELECT * FROM `k_rutaespecial` WHERE idkEspecial = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idKEspecial = $rs['idkEspecial'];
            $this->IdEspecial = $rs['IdEspecial'];
            $this->Calle_des = $rs['Calle_des'];
            $this->NoExterior_des = $rs['NoExterior_des'];
            $this->NoInterior_des = $rs['NoInterior_des'];
            $this->Colonia_des = $rs['Colonia_des'];
            $this->Ciudad_des = $rs['Ciudad_des'];
            $this->Delegacion_des = $rs['Delegacion_des'];
            $this->CodigoPostal_des = $rs['CodigoPostal_des'];
            $this->Localidad_des = $rs['Localidad_des'];
            $this->Estado_des = $rs['Estado_des'];
            $this->Latitud_des = $rs['Latitud_des'];
            $this->Longitud_des = $rs['Longitud_des'];
            $this->Comentario_des = $rs['Comentario_des'];
            $this->idTicket = $rs['idTicket'];
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

    public function newRegistro() {
        $catalogo = new Catalogo();
        //$consulta = "SELECT idEspecial FROM `c_especial` WHERE idUsuario = $this->idEmpleado;";
        //$result = $catalogo->obtenerLista($consulta);
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        if (!isset($this->latitud_or) || empty($this->latitud_or)) {
            $this->latitud_or = "NULL";
        }
        if (!isset($this->longitud_or) || empty($this->longitud_or)) {
            $this->longitud_or = "NULL";
        }
        if (!isset($this->latitud_des) || empty($this->latitud_des)) {
            $this->latitud_des = "NULL";
        }
        if (!isset($this->longitud_des) || empty($this->longitud_des)) {
            $this->longitud_des = "NULL";
        }
        if (!isset($this->PrecioParticular) || empty($this->PrecioParticular)) {
            $this->PrecioParticular = "NULL";
        }
        if (!isset($this->cuadrante) || empty($this->cuadrante)) {
            $this->cuadrante = "NULL";
        }
        if (!isset($this->idCampania) || empty($this->idCampania)) {
            $this->idCampania = "NULL";
        }
        if (!isset($this->idTurno) || empty($this->idTurno)) {
            $this->idTurno = "NULL";
        }
        if (!isset($this->contacto) || empty($this->contacto)) {
            $this->contacto = "NULL";
        }
        if (!isset($this->tipoServicio) || empty($this->tipoServicio)) {
            $this->tipoServicio = "NULL";
        }
        //if (mysql_num_rows($result) == 0) {
        $consulta = ("INSERT INTO c_especial(idUsuario, IdFormaContacto, TipoServicio, idCampania, idTurno, DatoContacto, FechaHora, Origen, Destino, Calle_or, NoExterior_or, NoInterior_or,Colonia_or,Ciudad_or,Delegacion_or,CodigoPostal_or,Localidad_or,Estado_or,Latitud_or,Longitud_or,Comentario_or,Cuadrante,
                          Calle_des, NoExterior_des, NoInterior_des,Colonia_des,Ciudad_des,Delegacion_des,CodigoPostal_des,Localidad_des,Estado_des,Latitud_des,Longitud_des,Comentario_des,
                          PrecioParticular,Activo,UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla, Informacion) 
            VALUES(" . $this->idEmpleado . "," . $this->contacto . "," . $this->tipoServicio . "," . $this->idCampania . "," . $this->idTurno . ",'" . $this->datoContacto . "','" . $this->fecha . " " . $this->hora . "','" . $this->origen . "','" . $this->destino . "','" . $this->calle_or . "','" . $this->exterior_or . "','" . $this->interior_or . "','" . $this->colonia_or . "','" . $this->ciudad_or . "','" . $this->delegacion_or . "','" . $this->cp_or . "','" . $this->localidad_or . "','" . $this->estado_or . "',
                    " . $this->latitud_or . ", " . $this->longitud_or . ",'" . $this->comentario_or . "'," . $this->cuadrante . ",'" . $this->calle_des . "','" . $this->exterior_des . "','" . $this->interior_des . "','" . $this->colonia_des . "','" . $this->ciudad_des . "','" . $this->delegacion_des . "','" . $this->cp_des . "','" . $this->localidad_des . "','" . $this->estado_des . "',
                    " . $this->latitud_des . ", " . $this->longitud_des . ",'" . $this->comentario_des . "',$this->PrecioParticular,'" . $this->activo . "','" . $this->usuarioCreacion . "',NOW(),'" . $this->usuarioUltimaModificacion . "',NOW(),'" . $this->pantalla . "','$this->Informacion');");
        //echo $consulta;
        $this->idEspecial = $catalogo->insertarRegistro($consulta);
        if ($this->idEspecial != NULL && $this->idEspecial != 0) {
            return true;
        }
        return false;
    }
    
    public function newRegistroRuta() {
        $catalogo = new Catalogo();
        //$consulta = "SELECT idEspecial FROM `c_especial` WHERE idUsuario = $this->idEmpleado;";
        //$result = $catalogo->obtenerLista($consulta);
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        if (!isset($this->latitud_or) || empty($this->latitud_or)) {
            $this->latitud_or = "NULL";
        }
        if (!isset($this->longitud_or) || empty($this->longitud_or)) {
            $this->longitud_or = "NULL";
        }
        if (!isset($this->latitud_des) || empty($this->latitud_des)) {
            $this->latitud_des = "NULL";
        }
        if (!isset($this->longitud_des) || empty($this->longitud_des)) {
            $this->longitud_des = "NULL";
        }
        if (!isset($this->PrecioParticular) || empty($this->PrecioParticular)) {
            $this->PrecioParticular = "NULL";
        }
        
        $consulta = ("INSERT INTO c_rutaespecial(idUsuario, IdFormaContacto, TipoServicio, idCampania, idTurno, Nombre_ruta, DatoContacto, FechaHora, Origen, Destino, Calle_or, NoExterior_or, NoInterior_or,Colonia_or,Ciudad_or,Delegacion_or,CodigoPostal_or,Localidad_or,Estado_or,Latitud_or,Longitud_or,Comentario_or,Cuadrante,
                          Calle_des, NoExterior_des, NoInterior_des,Colonia_des,Ciudad_des,Delegacion_des,CodigoPostal_des,Localidad_des,Estado_des,Latitud_des,Longitud_des,Comentario_des,
                          PrecioParticular,Activo,UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES(" . $this->idEmpleado . "," . $this->contacto . "," . $this->tipoServicio . "," . $this->idCampania . "," . $this->idTurno . ",'$this->Nombre_ruta','" . $this->datoContacto . "','" . $this->fecha . " " . $this->hora . "','" . $this->origen . "','" . $this->destino . "','" . $this->calle_or . "','" . $this->exterior_or . "','" . $this->interior_or . "','" . $this->colonia_or . "','" . $this->ciudad_or . "','" . $this->delegacion_or . "','" . $this->cp_or . "','" . $this->localidad_or . "','" . $this->estado_or . "',
                    " . $this->latitud_or . ", " . $this->longitud_or . ",'" . $this->comentario_or . "'," . $this->cuadrante . ",'" . $this->calle_des . "','" . $this->exterior_des . "','" . $this->interior_des . "','" . $this->colonia_des . "','" . $this->ciudad_des . "','" . $this->delegacion_des . "','" . $this->cp_des . "','" . $this->localidad_des . "','" . $this->estado_des . "',
                    " . $this->latitud_des . ", " . $this->longitud_des . ",'" . $this->comentario_des . "',$this->PrecioParticular,'" . $this->activo . "','" . $this->usuarioCreacion . "',NOW(),'" . $this->usuarioUltimaModificacion . "',NOW(),'" . $this->pantalla . "');");
        $this->idEspecial = $catalogo->insertarRegistro($consulta);
        if ($this->idEspecial != NULL && $this->idEspecial != 0) {
            return true;
        }
        return false;
    }

    public function newRegistroDetalle() {
        $catalogo = new Catalogo();
        //$consulta = "SELECT idEspecial FROM `c_especial` WHERE idUsuario = $this->idEmpleado;";
        //$result = $catalogo->obtenerLista($consulta);
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        if (!isset($this->latitud_or) || empty($this->latitud_or)) {
            $this->latitud_or = "NULL";
        }
        if (!isset($this->longitud_or) || empty($this->longitud_or)) {
            $this->longitud_or = "NULL";
        }
        if (!isset($this->latitud_des) || empty($this->latitud_des)) {
            $this->latitud_des = "NULL";
        }
        if (!isset($this->longitud_des) || empty($this->longitud_des)) {
            $this->longitud_des = "NULL";
        }
        if (!isset($this->idTicket) || empty($this->idTicket)) {
            $this->idTicket = "NULL";
        }

        $consulta = ("INSERT INTO k_especialescala(idkEspecial,	IdEspecial,	Calle_des,	NoExterior_des,	NoInterior_des,	Colonia_des,	
            Ciudad_des,	Delegacion_des,	CodigoPostal_des,	Localidad_des,	Estado_des,	Latitud_des,	Longitud_des,	
            Comentario_des,	idTicket,	
            Activo,	UsuarioCreacion,	FechaCreacion,	UsuarioUltimaModificacion,	FechaUltimaModificacion,	Pantalla) 
        VALUES(0," . $this->idEspecial . ",'" . $this->calle_des . "','" . $this->exterior_des . "','" . $this->interior_des . "','" . $this->colonia_des . "',
                '" . $this->ciudad_des . "','" . $this->delegacion_des . "','" . $this->cp_des . "','" . $this->localidad_des . "','" . $this->estado_des . "',
                " . $this->latitud_des . ", " . $this->longitud_des . ",'" . $this->comentario_des . "',$this->idTicket,
                '" . $this->activo . "','" . $this->usuarioCreacion . "',NOW(),'" . $this->usuarioUltimaModificacion . "',NOW(),'" . $this->pantalla . "');");

        $this->idKEspecial = $catalogo->insertarRegistro($consulta);
        if ($this->idKEspecial != NULL && $this->idKEspecial != 0) {
            return true;
        }
        return false;
    }

    public function newRegistroRutaDetalle() {
        $catalogo = new Catalogo();
        //$consulta = "SELECT idEspecial FROM `c_especial` WHERE idUsuario = $this->idEmpleado;";
        //$result = $catalogo->obtenerLista($consulta);
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        if (!isset($this->latitud_or) || empty($this->latitud_or)) {
            $this->latitud_or = "NULL";
        }
        if (!isset($this->longitud_or) || empty($this->longitud_or)) {
            $this->longitud_or = "NULL";
        }
        if (!isset($this->latitud_des) || empty($this->latitud_des)) {
            $this->latitud_des = "NULL";
        }
        if (!isset($this->longitud_des) || empty($this->longitud_des)) {
            $this->longitud_des = "NULL";
        }
        if (!isset($this->idTicket) || empty($this->idTicket)) {
            $this->idTicket = "NULL";
        }

        $consulta = ("INSERT INTO k_rutaespecial(idkEspecial,	IdEspecial,	Calle_des,	NoExterior_des,	NoInterior_des,	Colonia_des,	
            Ciudad_des,	Delegacion_des,	CodigoPostal_des,	Localidad_des,	Estado_des,	Latitud_des,	Longitud_des,	
            Comentario_des,	idTicket,	
            Activo,	UsuarioCreacion,	FechaCreacion,	UsuarioUltimaModificacion,	FechaUltimaModificacion,	Pantalla) 
        VALUES(0," . $this->idEspecial . ",'" . $this->calle_des . "','" . $this->exterior_des . "','" . $this->interior_des . "','" . $this->colonia_des . "',
                '" . $this->ciudad_des . "','" . $this->delegacion_des . "','" . $this->cp_des . "','" . $this->localidad_des . "','" . $this->estado_des . "',
                " . $this->latitud_des . ", " . $this->longitud_des . ",'" . $this->comentario_des . "',$this->idTicket,
                '" . $this->activo . "','" . $this->usuarioCreacion . "',NOW(),'" . $this->usuarioUltimaModificacion . "',NOW(),'" . $this->pantalla . "');");

        $this->idKEspecial = $catalogo->insertarRegistro($consulta);
        if ($this->idKEspecial != NULL && $this->idKEspecial != 0) {
            return true;
        }
        return false;
    }
    
    public function editRegistro() {
        $catalogo = new Catalogo();
        if (!isset($this->latitud_des) || empty($this->latitud_des)) {
            $this->latitud_des = "NULL";
        }
        if (!isset($this->longitud_des) || empty($this->longitud_des)) {
            $this->longitud_des = "NULL";
        }
        if (!isset($this->idTicket) || empty($this->idTicket)) {
            $this->idTicket = "NULL";
        }
        if (!isset($this->PrecioParticular) || empty($this->PrecioParticular)) {
            $this->PrecioParticular = "NULL";
        }

        $consulta = ("UPDATE c_especial SET IdFormaContacto = " . $this->contacto . ",TipoServicio = " . $this->tipoServicio . ",DatoContacto = '" . $this->datoContacto . "',FechaHora='" . $this->fecha . " " . $this->hora . "',Origen = '" . $this->origen . "',Destino = '" . $this->destino . "',Calle_or = '" . $this->calle_or . "', NoExterior_or = '" . $this->exterior_or . "', NoInterior_or='" . $this->interior_or . "',Colonia_or = '" . $this->colonia_or . "',Ciudad_or ='" . $this->ciudad_or . "',Delegacion_or ='" . $this->delegacion_or . "',
                    CodigoPostal_or ='" . $this->cp_or . "',Localidad_or = '" . $this->localidad_or . "',Estado_or ='" . $this->estado_or . "',Latitud_or='" . $this->latitud_or . "',Longitud_or='" . $this->longitud_or . "',Comentario_or='" . $this->comentario_or . "',Cuadrante='" . $this->cuadrante . "',Calle_des = '" . $this->calle_des . "', NoExterior_des = '" . $this->exterior_des . "', NoInterior_des='" . $this->interior_des . "',
                    Colonia_des = '" . $this->colonia_des . "',Ciudad_des ='" . $this->ciudad_des . "',Delegacion_des ='" . $this->delegacion_des . "',
                    CodigoPostal_des ='" . $this->cp_des . "',Localidad_des = '" . $this->localidad_des . "',Estado_des='" . $this->estado_des . "',
                    Latitud_des='" . $this->latitud_des . "',Longitud_des='" . $this->longitud_des . "',Comentario_des='" . $this->comentario_des . "',PrecioParticular=$this->PrecioParticular,
                    UsuarioUltimaModificacion = '" . $this->usuarioUltimaModificacion . "',Informacion='$this->Informacion',
                    FechaUltimaModificacion = now(), Pantalla = '" . $this->pantalla . "' WHERE idEspecial = '" . $this->idEspecial . "';");

        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function editRegistroRuta() {
        $catalogo = new Catalogo();
        if (!isset($this->latitud_des) || empty($this->latitud_des)) {
            $this->latitud_des = "NULL";
        }
        if (!isset($this->longitud_des) || empty($this->longitud_des)) {
            $this->longitud_des = "NULL";
        }
        if (!isset($this->idTicket) || empty($this->idTicket)) {
            $this->idTicket = "NULL";
        }
        if (!isset($this->PrecioParticular) || empty($this->PrecioParticular)) {
            $this->PrecioParticular = "NULL";
        }

        $consulta = ("UPDATE c_rutaespecial SET IdFormaContacto = " . $this->contacto . ",TipoServicio = " . $this->tipoServicio . ",DatoContacto = '" . $this->datoContacto . "',FechaHora='" . $this->fecha . " " . $this->hora . "',Origen = '" . $this->origen . "',Destino = '" . $this->destino . "',Calle_or = '" . $this->calle_or . "', NoExterior_or = '" . $this->exterior_or . "', NoInterior_or='" . $this->interior_or . "',Colonia_or = '" . $this->colonia_or . "',Ciudad_or ='" . $this->ciudad_or . "',Delegacion_or ='" . $this->delegacion_or . "',
                    CodigoPostal_or ='" . $this->cp_or . "',Localidad_or = '" . $this->localidad_or . "',Estado_or ='" . $this->estado_or . "',Latitud_or='" . $this->latitud_or . "',Longitud_or='" . $this->longitud_or . "',Comentario_or='" . $this->comentario_or . "',Cuadrante='" . $this->cuadrante . "',Calle_des = '" . $this->calle_des . "', NoExterior_des = '" . $this->exterior_des . "', NoInterior_des='" . $this->interior_des . "',
                    Colonia_des = '" . $this->colonia_des . "',Ciudad_des ='" . $this->ciudad_des . "',Delegacion_des ='" . $this->delegacion_des . "',
                    CodigoPostal_des ='" . $this->cp_des . "',Localidad_des = '" . $this->localidad_des . "',Estado_des='" . $this->estado_des . "',
                    Latitud_des='" . $this->latitud_des . "',Longitud_des='" . $this->longitud_des . "',Comentario_des='" . $this->comentario_des . "',PrecioParticular=$this->PrecioParticular,
                    UsuarioUltimaModificacion = '" . $this->usuarioUltimaModificacion . "',Nombre_ruta='$this->Nombre_ruta',
                    FechaUltimaModificacion = now(), Pantalla = '" . $this->pantalla . "' WHERE idEspecial = '" . $this->idEspecial . "';");
        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistroDetalle($id, $especial) {
        $catalogo = new Catalogo();
        if ($especial->getIdTicket() == "") {
            $especial->setIdticket("NULL");
        }
        if ($especial->getLatitud_des() == "") {
            $especial->setLatitud_des("NULL");
        }
        if ($especial->getLongitud_des() == "") {
            $especial->setLongitud_des("NULL");
        }
        $consulta = ("UPDATE k_especialescala SET "
                . "Calle_des = '" . $especial->getCalle_des() . "',NoExterior_des = '" . $especial->getExterior_des() . "',NoInterior_des = '" . $especial->getInterior_des() . "',	"
                . "Colonia_des = '" . $especial->getColonia_des() . "',	Ciudad_des = '" . $especial->getCiudad_des() . "',Delegacion_des = '" . $especial->delegacion_des . "',	"
                . "CodigoPostal_des = '" . $especial->getCp_des() . "',	Localidad_des = '" . $especial->getLocalidad_des() . "',Estado_des = '" . $especial->estado_des . "',	"
                . "Latitud_des = " . $especial->getLatitud_des() . ",	Longitud_des = " . $especial->getLongitud_des() . ",	Comentario_des = '" . $especial->getComentario_des() . "',	"
                . "idTicket = " . $especial->getIdTicket() . ",	Activo = " . $especial->getActivo() . ",UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',	"
                . "FechaUltimaModificacion = NOW(),	Pantalla = '" . $especial->getPantalla() . "'
                WHERE idkEspecial = $id;");

        $query = $catalogo->obtenerLista($consulta);

        if ($query != "0") {
            return true;
        }
        return false;
    }
    
    public function editRegistroRutaDetalle($id, $especial) {
        $catalogo = new Catalogo();
        if ($especial->getIdTicket() == "") {
            $especial->setIdticket("NULL");
        }
        if ($especial->getLatitud_des() == "") {
            $especial->setLatitud_des("NULL");
        }
        if ($especial->getLongitud_des() == "") {
            $especial->setLongitud_des("NULL");
        }
        $consulta = ("UPDATE k_rutaespecial SET "
                . "Calle_des = '" . $especial->getCalle_des() . "',NoExterior_des = '" . $especial->getExterior_des() . "',NoInterior_des = '" . $especial->getInterior_des() . "',	"
                . "Colonia_des = '" . $especial->getColonia_des() . "',	Ciudad_des = '" . $especial->getCiudad_des() . "',Delegacion_des = '" . $especial->delegacion_des . "',	"
                . "CodigoPostal_des = '" . $especial->getCp_des() . "',	Localidad_des = '" . $especial->getLocalidad_des() . "',Estado_des = '" . $especial->estado_des . "',	"
                . "Latitud_des = " . $especial->getLatitud_des() . ",	Longitud_des = " . $especial->getLongitud_des() . ",	Comentario_des = '" . $especial->getComentario_des() . "',	"
                . "idTicket = " . $especial->getIdTicket() . ",	Activo = " . $especial->getActivo() . ",UsuarioUltimaModificacion = '" . $especial->getUsuarioUltimaModificacion() . "',	"
                . "FechaUltimaModificacion = NOW(),	Pantalla = '" . $especial->getPantalla() . "'
                WHERE idkEspecial = $id;");

        $query = $catalogo->obtenerLista($consulta);

        if ($query != "0") {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = "DELETE FROM c_especial WHERE idEspecial ='" . $this->idEspecial . "';";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistrosDetalle() {
        $consulta = "DELETE FROM k_especialescala WHERE IdEspecial = $this->idEspecial;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function obtenerEscalasDeViaje() {
        $consulta = "SELECT * FROM `k_especialescala` WHERE IdEspecial = $this->idEspecial;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    public function obtenerEscalasDeRuta() {
        $consulta = "SELECT * FROM `k_rutaespecial` WHERE IdEspecial = $this->idEspecial;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function insertaEscalas($parametros, $IdTicketMaestro, $NombreCliente, $ClaveCentroCosto, $ClaveCliente, $NombreCentroCosto, 
            $Loggin, $Modelo, $NombreResp, $TelefonoResp, $CelularResp, $CorreoResp, $areaAtencion, $pantalla, $insertaTicket, $idRuta) {
        $especial = new AutorizarEspecial();
        $catalogo = new Catalogo();
        $relacion = new TicketRelacion();

        $contador = $parametros['TotalEscalas'];
        $id_array = array();

        if ($insertaTicket && $contador <= 1) {
            $insertaTicket = false;
        }


        for ($ultimo_index = 0; $ultimo_index < $contador; $ultimo_index++) {
            if(empty($idRuta)){
                $especial->setIdEspecial($this->idEspecial);
            }else{
                $especial->setIdEspecial($idRuta);
            }
            
            $especial->setCalle_des($parametros['txtCalle_des' . $ultimo_index]);
            $especial->setExterior_des($parametros['txtExterior_des' . $ultimo_index]);
            $especial->setInterior_des($parametros['txtInterior_des' . $ultimo_index]);
            $especial->setColonia_des($parametros['txtColonia_des' . $ultimo_index]);
            $especial->setCiudad_des($parametros['txtCiudad_des' . $ultimo_index]);
            $especial->setDelegacion_des($parametros['txtDelegacion_des' . $ultimo_index]);
            $especial->setCp_des($parametros['txtcp_des' . $ultimo_index]);
            $especial->setLocalidad_des($parametros['txtLocalidad_des' . $ultimo_index]);
            $especial->setEstado_des($parametros['slcEstado_des' . $ultimo_index]);
            $especial->setLatitud_des($parametros['Latitud_des' . $ultimo_index]);
            $especial->setLongitud_des($parametros['Longitud_des' . $ultimo_index]);
            $especial->setComentario_des($parametros['Comentario_des' . $ultimo_index]);
            $especial->setActivo(1);
            $especial->setUsuarioCreacion($_SESSION['user']);
            $especial->setUsuarioUltimaModificacion($_SESSION['user']);
            $especial->setPantalla($pantalla);

            $ticket = new Ticket();
            $domicilioT = new DomicilioTicket();
            //Damos de alta ticket
            $ticket->setUsuario($especial->getUsuarioCreacion());
            $ticket->setEstadoDeTicket(3);
            $ticket->setTipoReporte(281);
            $ticket->setNombreCliente($NombreCliente);
            $ticket->setClaveCliente($ClaveCliente);
            $ticket->setNombreCentroCosto($NombreCentroCosto);
            $ticket->setClaveCentroCosto($ClaveCentroCosto);
            $ticket->setNoSerieEquipo($Loggin);
            $ticket->setModeloEquipo($Modelo);
            $ticket->setDescripcionReporte("Viaje Especial-Escala de Usuario $Loggin del Evento $IdTicketMaestro: " . $especial->getCalle_des() . ", " . $especial->getEstado_des() . ", " . $especial->getColonia_des() . ", " . $especial->getCp_des());
            $ticket->setAreaAtencion($areaAtencion);
            $ticket->setActivo(1);
            $ticket->setUsuarioCreacion($especial->getUsuarioCreacion());
            $ticket->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
            $ticket->setPantalla($pantalla);
            $ticket->setUbicacion(1);
            $ticket->setNombreResp($NombreResp);
            $ticket->setTelefono1Resp($TelefonoResp);
            $ticket->setObservacionAdicional("");

            $domicilioT->setCalle($especial->getCalle_des());
            $domicilioT->setActivo(1);
            $domicilioT->setCiudad($especial->getCiudad_des());
            $domicilioT->setClaveZona("NULL");
            $domicilioT->setCodigoPostal($especial->getCp_des());
            $domicilioT->setColonia($especial->getColonia_des());
            $domicilioT->setDelegacion($especial->getDelegacion_des());
            $query = $catalogo->obtenerLista("SELECT Ciudad FROM c_ciudades WHERE IdCiudad='" . $especial->getEstado_des() . "';");
            $rse = mysql_fetch_array($query);
            $domicilioT->setEstado($rse['Ciudad']);
            $domicilioT->setLatitud($especial->getLatitud_des());
            $domicilioT->setLongitud($especial->getLongitud_des());
            $domicilioT->setNoExterior($especial->getExterior_des());
            $domicilioT->setNoInterior($especial->getInterior_des());
            $domicilioT->setPais("NULL");
            $domicilioT->setUsuarioCreacion($especial->getUsuarioCreacion());
            $domicilioT->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
            $domicilioT->setPantalla($especial->getPantalla());

            $especial->setIdTicket($ticket->getIdTicket());
            $relacion->setIdTicketMultiple($IdTicketMaestro);
            $relacion->setEstatus(0);
            $relacion->setUsuarioCreacion($especial->getUsuarioCreacion());
            $relacion->setUsuarioUltimaModificacion($especial->getUsuarioUltimaModificacion());
            $relacion->setPantalla($pantalla);
            if (!isset($parametros['idDetalle' . $ultimo_index]) || empty($parametros['idDetalle' . $ultimo_index])) {
                if (
                        (!empty($idRuta) && $especial->newRegistroRutaDetalle()) || 
                        (empty($idRuta) && $especial->newRegistroDetalle()) ) {
                    array_push($id_array, $especial->getIdKEspecial());
                    if ($insertaTicket && empty($idRuta)) {
                        if ($ticket->newRegistro()) {
                            $domicilioT->setIdTicket($ticket->getIdTicket());
                            if (!$domicilioT->newRegistro()) {
                                echo "Error: El domicilio del servicio (" . $especial->getCalle_des() . ") no se pudo registrar";
                            }
                            $relacion->setIdTicketSimple($ticket->getIdTicket());
                            if (!$relacion->newRegistro()) {
                                echo "Error: No se pudo relacionar el evento " . $ticket->getIdTicket() . " con el evento maestro  " . $IdTicketMaestro;
                            }
                        } else {
                            echo "Error: no se pudo registrar el ticket de " . $especial->getCalle_des();
                        }
                    }
                } else {
                    echo "Error: no se pudo registrar la escala " . $especial->getCalle_des();
                }
            } else {
                if(empty($idRuta)){
                    $especial_aux = new AutorizarEspecial();
                    $especial_aux->getRegistroDetalleById($parametros['idDetalle' . $ultimo_index]);
                    array_push($id_array, $especial_aux->getIdKEspecial());
                    if ($especial_aux->getIdTicket() == "" && $insertaTicket) {
                        if ($ticket->newRegistro()) {
                            $domicilioT->setIdTicket($ticket->getIdTicket());
                            if (!$domicilioT->newRegistro()) {
                                echo "Error: El domicilio del servicio (" . $especial->getCalle_des() . ") no se pudo registrar";
                            }
                            $especial->setIdTicket($ticket->getIdTicket());
                            $relacion->setIdTicketSimple($ticket->getIdTicket());                           
                        } else {
                            echo "Error: no se pudo registrar el ticket de " . $especial->getCalle_des();
                        }
                    } else {
                        $ticket->setIdTicket($especial_aux->getIdTicket());
                    }
                }

                if (
                        (empty($idRuta) && $especial->editRegistroDetalle($parametros['idDetalle' . $ultimo_index], $especial)) ||
                        (!empty(($idRuta)) && $especial->editRegistroRutaDetalle($parametros['idDetalleRuta' . $ultimo_index], $especial))
                    ) {
                    
                    if(empty($idRuta)){
                        $relacion->setIdTicketMultiple($IdTicketMaestro);
                        if ($ultimo_index == 0) {
                            $relacion->deleteRegistroTicketMultiple();
                        }
                        if ($insertaTicket) {
                            $domicilioT->setIdTicket($ticket->getIdTicket());
                            if (!$domicilioT->updateDomicilioTicket()) {
                                echo "Error: El domicilio del servicio (" . $especial->getCalle_des() . ") no se pudo actualizar";
                            }
                        }

                        $relacion->setIdTicketSimple($ticket->getIdTicket());
                        if (!$relacion->newRegistro()) {
                            echo "Error: No se pudo relacionar el evento " . $ticket->getIdTicket() . " con el evento maestro " . $IdTicketMaestro;
                        }
                    }
                } else {
                    echo "Error: no se pudo actualizar la escala " . $especial->getCalle_des();
                }
            }
        }

        if (!empty($id_array)) {
            $consulta = "DELETE FROM `k_especialescala` WHERE IdEspecial = " . $especial->getIdEspecial() . " AND idkEspecial NOT IN(" . implode(",", $id_array) . ");";
            $catalogo->obtenerLista($consulta);
        }
    }

    public function getIdEspecial() {
        return $this->idEspecial;
    }

    public function getIdEmpleado() {
        return $this->idEmpleado;
    }

    public function getContacto() {
        return $this->contacto;
    }

    public function getTipoServicio() {
        return $this->tipoServicio;
    }

    public function getIdCampania() {
        return $this->idCampania;
    }

    public function getIdTurno() {
        return $this->idTurno;
    }

    public function getDatoContacto() {
        return $this->datoContacto;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getOrigen() {
        return $this->origen;
    }

    public function getDestino() {
        return $this->destino;
    }

    public function getCalle_or() {
        return $this->calle_or;
    }

    public function getExterior_or() {
        return $this->exterior_or;
    }

    public function getInterior_or() {
        return $this->interior_or;
    }

    public function getColonia_or() {
        return $this->colonia_or;
    }

    public function getCiudad_or() {
        return $this->ciudad_or;
    }

    public function getDelegacion_or() {
        return $this->delegacion_or;
    }

    public function getCp_or() {
        return $this->cp_or;
    }

    public function getLocalidad_or() {
        return $this->localidad_or;
    }

    public function getEstado_or() {
        return $this->estado_or;
    }

    public function getLatitud_or() {
        return $this->latitud_or;
    }

    public function getLongitud_or() {
        return $this->longitud_or;
    }

    public function getComentario_or() {
        return $this->comentario_or;
    }

    public function getCuadrante() {
        return $this->cuadrante;
    }

    public function getCalle_des() {
        return $this->calle_des;
    }

    public function getExterior_des() {
        return $this->exterior_des;
    }

    public function getInterior_des() {
        return $this->interior_des;
    }

    public function getColonia_des() {
        return $this->colonia_des;
    }

    public function getCiudad_des() {
        return $this->ciudad_des;
    }

    public function getDelegacion_des() {
        return $this->delegacion_des;
    }

    public function getCp_des() {
        return $this->cp_des;
    }

    public function getLocalidad_des() {
        return $this->localidad_des;
    }

    public function getEstado_des() {
        return $this->estado_des;
    }

    public function getLatitud_des() {
        return $this->latitud_des;
    }

    public function getLongitud_des() {
        return $this->longitud_des;
    }

    public function getComentario_des() {
        return $this->comentario_des;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->usuarioUltimaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    public function setIdEspecial($idEspecial) {
        $this->idEspecial = $idEspecial;
    }

    public function setIdEmpleado($idEmpleado) {
        $this->idEmpleado = $idEmpleado;
    }

    public function setContacto($contacto) {
        $this->contacto = $contacto;
    }

    public function setDatoContacto($datoContacto) {
        $this->datoContacto = $datoContacto;
    }

    public function setTipoServicio($tipoServicio) {
        $this->tipoServicio = $tipoServicio;
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

    public function setOrigen($origen) {
        $this->origen = $origen;
    }

    public function setDestino($destino) {
        $this->destino = $destino;
    }

    public function setCalle_or($calle_or) {
        $this->calle_or = $calle_or;
    }

    public function setExterior_or($exterior_or) {
        $this->exterior_or = $exterior_or;
    }

    public function setInterior_or($interior_or) {
        $this->interior_or = $interior_or;
    }

    public function setColonia_or($colonia_or) {
        $this->colonia_or = $colonia_or;
    }

    public function setCiudad_or($ciudad_or) {
        $this->ciudad_or = $ciudad_or;
    }

    public function setDelegacion_or($delegacion_or) {
        $this->delegacion_or = $delegacion_or;
    }

    public function setCp_or($cp_or) {
        $this->cp_or = $cp_or;
    }

    public function setLocalidad_or($localidad_or) {
        $this->localidad_or = $localidad_or;
    }

    public function setEstado_or($estado_or) {
        $this->estado_or = $estado_or;
    }

    public function setLatitud_or($latitud_or) {
        $this->latitud_or = $latitud_or;
    }

    public function setLongitud_or($longitud_or) {
        $this->longitud_or = $longitud_or;
    }

    public function setComentario_or($comentario_or) {
        $this->comentario_or = $comentario_or;
    }

    public function setCuadrante($cuadrante) {
        $this->cuadrante = $cuadrante;
    }

    public function setCalle_des($calle_des) {
        $this->calle_des = $calle_des;
    }

    public function setExterior_des($exterior_des) {
        $this->exterior_des = $exterior_des;
    }

    public function setInterior_des($interior_des) {
        $this->interior_des = $interior_des;
    }

    public function setColonia_des($colonia_des) {
        $this->colonia_des = $colonia_des;
    }

    public function setCiudad_des($ciudad_des) {
        $this->ciudad_des = $ciudad_des;
    }

    public function setDelegacion_des($delegacion_des) {
        $this->delegacion_des = $delegacion_des;
    }

    public function setCp_des($cp_des) {
        $this->cp_des = $cp_des;
    }

    public function setLocalidad_des($localidad_des) {
        $this->localidad_des = $localidad_des;
    }

    public function setEstado_des($estado_des) {
        $this->estado_des = $estado_des;
    }

    public function setLatitud_des($latitud_des) {
        $this->latitud_des = $latitud_des;
    }

    public function setLongitud_des($longitud_des) {
        $this->longitud_des = $longitud_des;
    }

    public function setComentario_des($comentario_des) {
        $this->comentario_des = $comentario_des;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUsuarioUltimaModificacion($usuarioUltimaModificacion) {
        $this->usuarioUltimaModificacion = $usuarioUltimaModificacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getIdKEspecial() {
        return $this->idKEspecial;
    }

    function setIdKEspecial($idKEspecial) {
        $this->idKEspecial = $idKEspecial;
    }

    function getPrecioParticular() {
        return $this->PrecioParticular;
    }

    function setPrecioParticular($PrecioParticular) {
        $this->PrecioParticular = $PrecioParticular;
    }

    function getNombre_ruta() {
        return $this->Nombre_ruta;
    }

    function setNombre_ruta($Nombre_ruta) {
        $this->Nombre_ruta = $Nombre_ruta;
    }

    function getRuta() {
        return $this->ruta;
    }

    function setRuta($ruta) {
        $this->ruta = $ruta;
    }
    
    function getInformacion() {
        return $this->Informacion;
    }

    function setInformacion($Informacion) {
        $this->Informacion = $Informacion;
    }

}

?>