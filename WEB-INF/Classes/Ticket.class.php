<?php

include_once("Conexion.class.php");
include_once("AgregarNota.class.php");
include_once("Catalogo.class.php");
include_once("CentroCosto.class.php");
include_once("ParametroGlobal.class.php");
include_once("Usuario.class.php");

/**
 * Description of Ticket
 *
 * @author MAGG
 */
class Ticket {

    private $IdTicket;
    private $FechaHora;
    private $Usuario;
    private $EstadoDeTicket;
    private $TipoReporte;
    private $ActualizarInfoEstatCobra;
    private $ActualizarInfoCliente;
    private $NombreCliente;
    private $ClaveCentroCosto;
    private $ClaveCliente;
    private $NombreCentroCosto;
    private $NoSerieEquipo;
    private $ModeloEquipo;
    private $ActualizarInfoEquipo;
    private $NombreResp;
    private $Telefono1Resp;
    private $Extension1Resp;
    private $Telefono2Resp;
    private $Extension2Resp;
    private $CelularResp;
    private $CorreoEResp;
    private $HorarioAtenInicResp;
    private $HorarioAtenFinResp;
    private $NombreAtenc;
    private $Telefono1Atenc;
    private $Extension1Atenc;
    private $Telefono2Atenc;
    private $Extension2Atenc;
    private $CorreoEAtenc;
    private $CelularAtenc;
    private $HorarioAtenInicAtenc;
    private $HorarioAtenFinAtenc;
    private $NoTicketCliente;
    private $NoTicketDistribuidor;
    private $FechHoraInicRep;
    private $DescripcionReporte;
    private $ObservacionAdicional;
    private $AreaAtencion;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $FechaUltimaModificacion;
    private $UsuarioUltimaModificacion;
    private $Pantalla;
    private $Ubicacion;
    private $UbicacionEmp;
    private $resurtido;
    private $CambioToner;
    private $empresa;
    private $mensaje;
    private $FechaCheckIn;
    private $FechaCheckOut;
    private $Prioridad;
    private $NoGuia;
    private $Tecnico;
    private $PermitirTicketSinSerie;
    //Proyecto
    private $IdSubtipo;
    private $FechaFinPrevisto;
    private $FechaFinReal;
    private $Presupuesto;
    private $Progreso;
    private $Nombre;
    private $Contacto;
    private $UsuarioOrigen;

    public function reAbrirTicket($idTicket) {
        $consulta = "UPDATE c_ticket SET EstadoDeTicket = 5 WHERE IdTicket = $idTicket;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            /* Insertamos nota de reabierto */
            $obj = new AgregarNota();
            $obj->setIdTicket($idTicket);
            $obj->setDiagnosticoSolucion("Ticket re-abierto");
            $obj->setIdestatusAtencion(60);
            $obj->setActivo(1);
            $obj->setShow(1);
            $obj->setUsuarioCreacion($_SESSION['user']);
            $obj->setUsuarioModificacion($_SESSION['user']);
            $obj->setPantalla("PHP Lista ticket");
            //if($obj->newRegistro()){
            if ($obj->newRegistro() && $obj->reabrirNotas()) {
                //echo "El ticket se re-abrio correctamente";
                return 1;
            } else {
                //echo "Error: No se pudo agregar la nueva nota";
                return 2;
            }
        } else {
            return 3;
            //echo "Error: El ticket no se pudo re-abrir correctamente, intente de nuevo por favor";
        }
    }

    /**
     * Elimina las relaciones con tecnicos anteriores del ticket 
     * @param type $tipo 1 es hw y 2 es sw, 3 TFS
     * @return boolean true en caso de haber eliminado correctamente, false en caso contrario.
     */
    public function eliminarAsignacionesAnteriores($tipo) {
        $consulta = ("DELETE FROM k_tecnicoticket WHERE IdTicket = '$this->IdTicket' AND tipo = $tipo");
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

    /**
     * Elimina las relaciones con tecnicos anteriores del ticket 
     * @param type $tipo 1 es hw y 2 es sw, 3 TFS
     * @return boolean true en caso de haber eliminado correctamente, false en caso contrario.
     */
    public function eliminarAsignaciones() {
        $consulta = ("DELETE FROM k_tecnicoticket WHERE IdTicket = '$this->IdTicket';");
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

    /**
     * Inserta un nuevo registro en la tabla que asocia tickets con tecnicos de HW o SW
     * @param type $idTicket
     * @param type $idUsuario
     * @param type $tipo 1 es hw y 2 es sw, 3 TFS
     */
    public function asociarTicketTecnico($idUsuario, $tipo) {
        $consulta = ("INSERT INTO k_tecnicoticket(IdUsuario,IdTicket,tipo,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES($idUsuario,$this->IdTicket,$tipo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');");
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

    /**
     * 
     * @param type $idUsuario
     * @param type $IdPrioridad
     * @param type $Duracion
     * @param type $IdUnidadDuracion
     * @param type $FechaHora
     * @return boolean
     */
    public function asociarTicketTecnicoGeneral($idUsuario, $IdPrioridad, $Duracion, $IdUnidadDuracion, $FechaHora) {
        if (empty($IdPrioridad)) {
            $IdPrioridad = "NULL";
        }
        if (empty($Duracion)) {
            $Duracion = "NULL";
        }
        if (empty($IdUnidadDuracion)) {
            $IdUnidadDuracion = "NULL";
        }
        if (empty($FechaHora) || strpos($FechaHora, '_') !== FALSE) {
            $FechaHora = "NOW()";
        }
        $consulta = ("INSERT INTO k_tecnicoticket(IdUsuario,IdTicket,tipo,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,IdPrioridad,Duracion,IdUnidadDuracion,FechaHoraInicio) 
            VALUES($idUsuario,$this->IdTicket,4,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla',"
                . "$IdPrioridad,$Duracion,$IdUnidadDuracion,'$FechaHora');");
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

    /**
     * 
     */
    public function eliminarAsignacionesEjecutivo() {
        $consulta = ("DELETE FROM k_ematicket WHERE IdTicket = '$this->IdTicket';");
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

    public function asociarTicketEjecutivo($idUsuario) {
        $consulta = ("INSERT INTO k_ematicket(IdUsuario,IdTicket,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES($idUsuario,$this->IdTicket,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');");
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

    /**
     * Crea una nota de asignacion de tecnico     
     * @param type $nombreTecnico Nombre del tecnico al que fue asignado el ticket
     * @param type $tipo HW/SW.. etc
     * @return boolean true en caso de haber sido creada correcatmente, false en caso contrario
     */
    public function crearNotaGeneral($nombreTecnico, $tipo, $idnota) {
        $consulta = ("INSERT INTO c_notaticket(IdNotaTicket,IdTicket,DiagnosticoSol,IdEstatusAtencion,Activo,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,FechaHora) VALUES(0,$this->IdTicket,'Asignado a $tipo: $nombreTecnico',
            $idnota,1,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla',now());");
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

    /**
     * Crea una nota de asignacion de tecnico     
     * @param type $nombreTecnico Nombre del tecnico al que fue asignado el ticket
     * @param type $tipo HW/SW.. etc
     * @return boolean true en caso de haber sido creada correcatmente, false en caso contrario
     */
    public function crearNota($nombreTecnico, $tipo) {
        $consulta = ("INSERT INTO c_notaticket(IdNotaTicket,IdTicket,DiagnosticoSol,IdEstatusAtencion,Activo,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,FechaHora) VALUES(0,$this->IdTicket,'Asignado a $tipo: $nombreTecnico',
            22,1,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla',now());");
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

    public function validarTicket($NoSerie, $idTicket) {
        $consulta = ("SELECT NoParteEquipo FROM `c_inventarioequipo` WHERE NoSerie = '$NoSerie';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {/* Si el NoSerie ya esta dentro de inventario */
            /* Actualizamos el Ticket a que ya esta validado */
            $update = $catalogo->obtenerLista("UPDATE `c_ticket` SET ActualizarInfoCliente = 0, ActualizarInfoEquipo = 0 WHERE IdTicket = $idTicket;");
            if ($update == "1") {
                return "2";
            } else {
                return "1";
            }
        }
        return "0";
    }

    public function actualizarCentroCosto($idTicket, $idCentro) {
        $consulta = ("SELECT Nombre FROM `c_centrocosto` WHERE ClaveCentroCosto = '$idCentro';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $update = $catalogo->obtenerLista("UPDATE `c_ticket` SET ClaveCentroCosto = '$idCentro', NombreCentroCosto = '" . $rs['Nombre'] . "' 
                WHERE IdTicket = $idTicket;");
            if ($update == "1") {
                return true;
            }
        }
        return false;
    }

    public function actualizarCliente($idTicket, $idCliente) {
        $consulta = ("SELECT NombreRazonSocial FROM `c_cliente` WHERE ClaveCliente = '$idCliente';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $update = $catalogo->obtenerLista("UPDATE `c_ticket` SET ClaveCliente = '$idCliente', NombreCliente = '" . $rs['NombreRazonSocial'] . "' 
                WHERE IdTicket = $idTicket;");
            if ($update == "1") {
                return true;
            }
        }
        return false;
    }

    public function actualizarEquipo($idTicket, $idEquipo) {
        $consulta = ("SELECT Modelo FROM `c_equipo` WHERE NoParte = '$idEquipo';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $update = $catalogo->obtenerLista("UPDATE `c_ticket` SET NoSerieEquipo = '$idEquipo', ModeloEquipo = '" . $rs['Modelo'] . "' 
                WHERE IdTicket = $idTicket;");
            if ($update == "1") {
                return true;
            }
        }
        return false;
    }

    public function getTicketByID($id) {
        $consulta = ("SELECT * FROM `c_ticket` WHERE IdTicket = $id;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdTicket = $rs['IdTicket'];
            $this->FechaHora = $rs['FechaHora'];
            $this->Usuario = $rs['Usuario'];
            $this->EstadoDeTicket = $rs['EstadoDeTicket'];
            $this->TipoReporte = $rs['TipoReporte'];
            $this->ActualizarInfoEstatCobra = $rs['ActualizarInfoEstatCobra'];
            $this->ActualizarInfoCliente = $rs['ActualizarInfoCliente'];
            $this->NombreCliente = $rs['NombreCliente'];
            $this->ClaveCentroCosto = $rs['ClaveCentroCosto'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->NombreCentroCosto = $rs['NombreCentroCosto'];
            $this->NoSerieEquipo = $rs['NoSerieEquipo'];
            $this->ModeloEquipo = $rs['ModeloEquipo'];
            $this->ActualizarInfoEquipo = $rs['ActualizarInfoEquipo'];
            $this->NombreResp = $rs['NombreResp'];
            $this->Telefono1Resp = $rs['Telefono1Resp'];
            $this->Extension1Resp = $rs['Extension1Resp'];
            $this->Telefono2Resp = $rs['Telefono2Resp'];
            $this->Extension2Resp = $rs['Extension2Resp'];
            $this->CelularResp = $rs['CelularResp'];
            $this->CorreoEResp = $rs['CorreoEResp'];
            $this->HorarioAtenInicResp = $rs['HorarioAtenInicResp'];
            $this->HorarioAtenFinResp = $rs['HorarioAtenFinResp'];
            $this->NombreAtenc = $rs['NombreAtenc'];
            $this->Telefono1Atenc = $rs['Telefono1Atenc'];
            $this->Extension1Atenc = $rs['Extension1Atenc'];
            $this->Telefono2Atenc = $rs['Telefono2Atenc'];
            $this->Extension2Atenc = $rs['Extension2Atenc'];
            $this->CorreoEAtenc = $rs['CorreoEAtenc'];
            $this->CelularAtenc = $rs['CelularAtenc'];
            $this->HorarioAtenInicAtenc = $rs['HorarioAtenInicAtenc'];
            $this->HorarioAtenFinAtenc = $rs['HorarioAtenFinAtenc'];
            $this->NoTicketCliente = $rs['NoTicketCliente'];
            $this->NoTicketDistribuidor = $rs['NoTicketDistribuidor'];
            $this->FechHoraInicRep = $rs['FechHoraInicRep'];
            $this->DescripcionReporte = $rs['DescripcionReporte'];
            $this->ObservacionAdicional = $rs['ObservacionAdicional'];
            $this->AreaAtencion = $rs['AreaAtencion'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->Ubicacion = $rs['Ubicacion'];
            $this->resurtido = $rs['Resurtido'];
            $this->CambioToner = $rs['CambioToner'];
            $this->Prioridad = $rs['Prioridad'];
            $this->NoGuia = $rs['NoGuia'];
            $this->IdSubtipo = $rs['IdSubtipo'];
            $this->FechaFinPrevisto = $rs['FechaFinPrevisto'];
            $this->FechaFinReal = $rs['FechaFinReal'];
            $this->Presupuesto = $rs['Presupuesto'];
            $this->Progreso = $rs['Progreso'];
            $this->Nombre = $rs['Nombre'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        if (isset($this->ClaveCentroCosto) && $this->ClaveCentroCosto != "") {
            $cc = "'$this->ClaveCentroCosto'";
        } else {
            $cc = "null";
        }

        if (!isset($this->ClaveCliente) || $this->ClaveCliente == "") {
            $cc_obj = new CentroCosto();
            $cc_obj->getRegistroById($this->ClaveCentroCosto);
            $this->ClaveCliente = $cc_obj->getClaveCliente();
        }

        if (!isset($this->ObservacionAdicional)) {
            $this->ObservacionAdicional = "";
        }

        if (!isset($this->PermitirTicketSinSerie) || $this->PermitirTicketSinSerie == false) {
            if ($this->TipoReporte != "15" && $this->NoSerieEquipo == "") {
                echo "<br/>Error: No se puede insertar el ticket ya que no contiene un No. Serie";
                return false;
            }
        }

        if ($this->NoSerieEquipo != "") {
            $serie = "'$this->NoSerieEquipo'";
        } else {
            $serie = "NULL";
        }

        if (!isset($this->CambioToner) || empty($this->CambioToner)) {
            $this->CambioToner = "0";
        }

        $consulta = "INSERT INTO c_ticket (
        FechaHora,
        Usuario,
        EstadoDeTicket,
        TipoReporte,
        NombreCliente,
        ClaveCentroCosto,
        ClaveCliente,
        NombreCentroCosto,
        NoSerieEquipo,
        ModeloEquipo,
        DescripcionReporte,
        AreaAtencion,
        CambioToner,
        Activo,
        UsuarioCreacion,
        FechaCreacion,
        FechaUltimaModificacion,
        UsuarioUltimaModificacion,
        Pantalla,
        Ubicacion,
        ActualizarInfoEstatCobra, ActualizarInfoCliente, ActualizarInfoEquipo,NombreResp,Telefono1Resp,HorarioAtenInicResp,HorarioAtenFinResp,
        NombreAtenc,Telefono1Atenc,CorreoEAtenc,HorarioAtenInicAtenc,HorarioAtenFinAtenc,NoTicketCliente,NoTicketDistribuidor,ObservacionAdicional) 
            VALUES(NOW(), '$this->Usuario', $this->EstadoDeTicket, $this->TipoReporte, '$this->NombreCliente',$cc,'$this->ClaveCliente',
            '$this->NombreCentroCosto',$serie,'$this->ModeloEquipo','$this->DescripcionReporte',$this->AreaAtencion,$this->CambioToner,$this->Activo,
            '$this->UsuarioCreacion',NOW(),NOW(),'$this->UsuarioUltimaModificacion','$this->Pantalla','$this->Ubicacion',0,0,0,'$this->NombreResp','$this->Telefono1Resp','0,00,AM','0,00,AM',
             '','','falta@correo.com','0,00,AM','0,00,AM','','','$this->ObservacionAdicional');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdTicket = $catalogo->insertarRegistro($consulta);
        if ($this->IdTicket != NULL && $this->IdTicket != 0) {
            if (!$this->nuevaNotaAlAbrir()) {
                echo "<br/>No se pudo crear la nota de ticket abierto por TFS";
            }
            return true;
        }
        return false;
    }

    public function getRegistroProyecto($id) {
        $consulta = "SELECT t.IdTicket,t.UsuarioOrigen,t.IdSubtipo,t.FechaFinPrevisto,t.FechaFinReal,t.Presupuesto,t.Progreso,t.Nombre,t.Prioridad,t.FechaHora,
        t.Usuario,t.EstadoDeTicket,t.Contacto,t.TipoReporte,t.NombreCliente,t.ClaveCliente,t.DescripcionReporte,t.ObservacionAdicional,t.AreaAtencion,
        t.NombreResp,t.Telefono1Resp,t.Activo, k.IdUsuario FROM c_ticket t LEFT JOIN k_tecnicoticket k ON k.IdTicket = t.IdTicket WHERE t.IdTicket = $id";
        //echo $consulta;
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $this->IdTicket = $rs['IdTicket'];
            $this->IdSubtipo = $rs['IdSubtipo'];
            $this->FechaFinPrevisto = $rs['FechaFinPrevisto'];
            $this->FechaFinReal = $rs['FechaFinReal'];
            $this->Presupuesto = $rs['Presupuesto'];
            $this->Progreso = $rs['Progreso'];
            $this->Nombre = $rs['Nombre'];
            $this->Prioridad = $rs['Prioridad'];
            $this->FechaHora = $rs['FechaHora'];
            $this->Usuario = $rs['Usuario'];
            $this->EstadoDeTicket = $rs['EstadoDeTicket'];
            $this->TipoReporte = $rs['TipoReporte'];
            $this->NombreCliente = $rs['NombreCliente'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->DescripcionReporte = $rs['DescripcionReporte'];
            $this->ObservacionAdicional = $rs['ObservacionAdicional'];
            $this->AreaAtencion = $rs['AreaAtencion'];
            $this->NombreResp = $rs['NombreResp'];
            $this->Telefono1Resp = $rs['Telefono1Resp'];
            $this->Activo = $rs['Activo'];
            $this->Contacto = $rs['Contacto'];
            $this->Tecnico = $rs['IdUsuario'];
            $this->UsuarioOrigen = $rs['UsuarioOrigen'];
        }
    }

    public function newRegistroProyecto() {
        if (empty($this->Presupuesto)) {
            $this->Presupuesto = "NULL";
        }
        if (empty($this->Prioridad) || $this->Prioridad == "0") {
            $this->Prioridad = "NULL";
        }
        if (empty($this->IdSubtipo) || $this->IdSubtipo == "0") {
            $this->IdSubtipo = "NULL";
        }
        if (empty($this->FechaHora)) {
            $this->FechaHora = "NULL";
        } else {
            $this->FechaHora = "'$this->FechaHora'";
        }
        if (empty($this->FechaFinPrevisto)) {
            $this->FechaFinPrevisto = "NULL";
        } else {
            $this->FechaFinPrevisto = "'$this->FechaFinPrevisto'";
        }
        if (empty($this->FechaFinReal)) {
            $this->FechaFinReal = "NULL";
        } else {
            $this->FechaFinReal = "'$this->FechaFinReal'";
        }
        if (empty($this->Contacto)) {
            $this->Contacto = "NULL";
        }
        if (empty($this->UsuarioOrigen)) {
            $this->UsuarioOrigen = "NULL";
        }
        $consulta = "INSERT INTO c_ticket (
        IdSubtipo,FechaFinPrevisto,FechaFinReal,Presupuesto,Progreso,Nombre,
        Prioridad,
        FechaHora,
        Usuario,
        EstadoDeTicket,
        TipoReporte,
        NombreCliente,        
        ClaveCliente,                
        DescripcionReporte,
        ObservacionAdicional,
        AreaAtencion,
        NombreResp,Telefono1Resp,
        Contacto,
        Activo,
        UsuarioCreacion,
        FechaCreacion,
        FechaUltimaModificacion,
        UsuarioUltimaModificacion,
        Pantalla, UsuarioOrigen) 
            VALUES($this->IdSubtipo,$this->FechaFinPrevisto,$this->FechaFinReal,$this->Presupuesto,$this->Progreso,'$this->Nombre',$this->Prioridad,
            $this->FechaHora, '$this->Usuario', $this->EstadoDeTicket, $this->TipoReporte, '$this->NombreCliente','$this->ClaveCliente',
            '$this->DescripcionReporte','$this->ObservacionAdicional',$this->AreaAtencion,'$this->NombreResp','$this->Telefono1Resp',$this->Contacto,$this->Activo,
            '$this->UsuarioCreacion',NOW(),NOW(),'$this->UsuarioUltimaModificacion','$this->Pantalla', $this->UsuarioOrigen);";
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdTicket = $catalogo->insertarRegistro($consulta);
        if ($this->IdTicket != NULL && $this->IdTicket != 0) {
            return true;
        }
        return false;
    }

    public function editarProyecto() {
        if (empty($this->Presupuesto)) {
            $this->Presupuesto = "NULL";
        }
        if (empty($this->Prioridad) || $this->Prioridad == "0") {
            $this->Prioridad = "NULL";
        }
        if (empty($this->IdSubtipo) || $this->IdSubtipo == "0") {
            $this->IdSubtipo = "NULL";
        }
        if (empty($this->FechaHora)) {
            $this->FechaHora = "NULL";
        } else {
            $this->FechaHora = "'$this->FechaHora'";
        }
        if (empty($this->FechaFinPrevisto)) {
            $this->FechaFinPrevisto = "NULL";
        } else {
            $this->FechaFinPrevisto = "'$this->FechaFinPrevisto'";
        }
        if (empty($this->FechaFinReal)) {
            $this->FechaFinReal = "NULL";
        } else {
            $this->FechaFinReal = "'$this->FechaFinReal'";
        }
        if (empty($this->Contacto)) {
            $this->Contacto = "NULL";
        }
        if (empty($this->UsuarioOrigen)) {
            $this->UsuarioOrigen = "NULL";
        }
        $consulta = "UPDATE c_ticket SET IdSubtipo = $this->IdSubtipo,FechaFinPrevisto =$this->FechaFinPrevisto, FechaFinReal = $this->FechaFinReal,
            Presupuesto = $this->Presupuesto, Progreso = $this->Progreso, Nombre = '$this->Nombre',
            Prioridad = $this->Prioridad,
            NombreResp = '$this->NombreResp', Telefono1Resp = '$this->Telefono1Resp',
            FechaHora = $this->FechaHora,
            Usuario = '$this->Usuario',            
            TipoReporte = $this->TipoReporte,
            NombreCliente = '$this->NombreCliente', 
            ClaveCliente = '$this->ClaveCliente',                
            DescripcionReporte = '$this->DescripcionReporte',
            ObservacionAdicional = '$this->ObservacionAdicional',
            AreaAtencion = $this->AreaAtencion,
            Activo = $this->Activo, 
            Contacto = $this->Contacto,
            FechaUltimaModificacion = NOW(),
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',
            Pantalla = '$this->Pantalla', UsuarioOrigen = $this->UsuarioOrigen WHERE IdTicket = $this->IdTicket;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result == "1") {
            return true;
        }
        return false;
    }

    public function newRegistroResurtido() {
        if (isset($this->ClaveCentroCosto) && $this->ClaveCentroCosto != "") {
            $cc = "'$this->ClaveCentroCosto'";
        } else {
            $cc = "null";
        }
        if (!isset($this->ClaveCliente) || $this->ClaveCliente == "") {
            $cc = new CentroCosto();
            $cc->getRegistroById($this->ClaveCentroCosto);
            $this->ClaveCliente = $cc->getClaveCliente();
        }
        if ($this->NoSerieEquipo != "") {
            $serie = "'$this->NoSerieEquipo'";
        } else {
            $serie = "NULL";
        }
        $consulta = "INSERT INTO c_ticket (
        FechaHora,
        Usuario,
        EstadoDeTicket,
        TipoReporte,
        NombreCliente,
        ClaveCentroCosto,
        ClaveCliente,
        NombreCentroCosto,
        NoSerieEquipo,
        ModeloEquipo,
        DescripcionReporte,
        AreaAtencion,
        Activo,
        UsuarioCreacion,
        FechaCreacion,
        FechaUltimaModificacion,
        UsuarioUltimaModificacion,
        Pantalla,
        Ubicacion,
        ActualizarInfoEstatCobra, ActualizarInfoCliente, ActualizarInfoEquipo,NombreResp,Telefono1Resp,HorarioAtenInicResp,HorarioAtenFinResp,
        NombreAtenc,Telefono1Atenc,CorreoEAtenc,HorarioAtenInicAtenc,HorarioAtenFinAtenc,NoTicketCliente,NoTicketDistribuidor,ObservacionAdicional
        ,Resurtido) VALUES(NOW(), '$this->Usuario', $this->EstadoDeTicket, $this->TipoReporte, '$this->NombreCliente',$cc,'$this->ClaveCliente',
            '$this->NombreCentroCosto',$serie,'$this->ModeloEquipo','$this->DescripcionReporte',$this->AreaAtencion,$this->Activo,
            '$this->UsuarioCreacion',NOW(),NOW(),'$this->UsuarioUltimaModificacion','$this->Pantalla','$this->Ubicacion',0,0,0,'FALTA',00000000,'0,00,AM','0,00,AM',
             '','','falta@correo.com','0,00,AM','0,00,AM','','','',$this->resurtido);";
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdTicket = $catalogo->insertarRegistro($consulta);
        if ($this->IdTicket != NULL && $this->IdTicket != 0) {
            if (!$this->nuevaNotaAlAbrir()) {
                echo "<br/>No se pudo crear la nota de ticket abierto por TFS";
            }
            return true;
        }
        return false;
    }

    public function newRegistroCompleto() {
        if (!isset($this->ClaveCliente) || $this->ClaveCliente == "") {
            $cc = new CentroCosto();
            if (isset($this->empresa)) {
                $cc->setEmpresa($this->empresa);
            }
            $cc->getRegistroById($this->ClaveCentroCosto);
            $this->ClaveCliente = $cc->getClaveCliente();
        }

        if (!isset($this->FechaCheckIn) || empty($this->FechaCheckIn)) {
            $this->FechaCheckIn = "NULL";
        } else {
            $this->FechaCheckIn = "'$this->FechaCheckIn'";
        }

        if (!isset($this->FechaCheckOut) || empty($this->FechaCheckOut)) {
            $this->FechaCheckOut = "NULL";
        } else {
            $this->FechaCheckOut = "'$this->FechaCheckOut'";
        }

        if (!isset($this->Prioridad) || empty($this->Prioridad)) {
            $this->Prioridad = "NULL";
        }

        if (!isset($this->PermitirTicketSinSerie) || $this->PermitirTicketSinSerie == false) {
            if ($this->TipoReporte != "15" && $this->NoSerieEquipo == "") {
                echo "<br/>Error: No se puede insertar el ticket ya que no contiene un No. Serie";
                return false;
            }
        }

        if ($this->NoSerieEquipo != "") {
            $serie = "'$this->NoSerieEquipo'";
        } else {
            $serie = "NULL";
        }

        $consulta = "INSERT INTO c_ticket (
        FechaHora,Usuario,EstadoDeTicket,TipoReporte,
        ActualizarInfoEstatCobra, ActualizarInfoCliente,
        NombreCliente,ClaveCentroCosto,ClaveCliente,NombreCentroCosto,
        NoSerieEquipo,ModeloEquipo,ActualizarInfoEquipo,
         NombreResp,Telefono1Resp,Extension1Resp,Telefono2Resp,Extension2Resp,CelularResp,CorreoEResp,HorarioAtenInicResp,HorarioAtenFinResp,
         NombreAtenc,Telefono1Atenc,Extension1Atenc,Telefono2Atenc,Extension2Atenc,CorreoEAtenc,CelularAtenc,HorarioAtenInicAtenc,HorarioAtenFinAtenc,
        NoTicketCliente,NoTicketDistribuidor, NoGuia, 
        FechHoraInicRep,DescripcionReporte,ObservacionAdicional,AreaAtencion,
        Activo,UsuarioCreacion,FechaCreacion, FechaUltimaModificacion,UsuarioUltimaModificacion,Pantalla,
        Ubicacion,UbicacionEmp,FechaCheckIn,FechaCheckOut, Prioridad, FechaModificacionEstadoTicket) 
        VALUES(NOW(), '$this->Usuario', '$this->EstadoDeTicket', '$this->TipoReporte',
             " . $this->ActualizarInfoEstatCobra . "," . $this->ActualizarInfoCliente . ",
             '$this->NombreCliente','" . $this->ClaveCentroCosto . "','$this->ClaveCliente','$this->NombreCentroCosto',
             $serie,'$this->ModeloEquipo'," . $this->ActualizarInfoEquipo . ",
             '" . $this->NombreResp . "','" . $this->Telefono1Resp . "','" . $this->Extension1Resp . "','" . $this->Telefono2Resp . "','" . $this->Extension2Resp . "','" . $this->CelularResp . "','" . $this->CorreoEResp . "','" . $this->HorarioAtenInicResp . "','" . $this->HorarioAtenFinResp . "',
             '" . $this->NombreAtenc . "','" . $this->Telefono1Atenc . "','" . $this->Extension1Atenc . "','" . $this->Telefono2Atenc . "','" . $this->Extension2Atenc . "','" . $this->CorreoEAtenc . "','" . $this->CelularAtenc . "','" . $this->HorarioAtenInicAtenc . "','" . $this->HorarioAtenFinAtenc . "',
             '" . $this->NoTicketCliente . "','" . $this->NoTicketDistribuidor . "','$this->NoGuia',now(),  
             '$this->DescripcionReporte','$this->ObservacionAdicional','$this->AreaAtencion',
             $this->Activo,'$this->UsuarioCreacion',NOW(),NOW(),'$this->UsuarioUltimaModificacion','$this->Pantalla',
                 '$this->Ubicacion','" . $this->UbicacionEmp . "',$this->FechaCheckIn, $this->FechaCheckOut, $this->Prioridad, NOW());";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $this->mensaje = $consulta;
        $this->IdTicket = $catalogo->insertarRegistro($consulta);
        if ($this->IdTicket != NULL && $this->IdTicket != 0) {
            if (!$this->nuevaNotaAlAbrir()) {
                echo "<br/>No se pudo crear la nota de ticket abierto por TFS";
            }
            return true;
        }

        return false;
    }

    public function editTicket() {
        $consulta = "UPDATE c_ticket SET TipoReporte = '" . $this->TipoReporte . "',
            ActualizarInfoEstatCobra = " . $this->ActualizarInfoEstatCobra . ",ActualizarInfoCliente = " . $this->ActualizarInfoCliente . ", 
            NombreCliente = '" . $this->NombreCliente . "',ClaveCentroCosto = '" . $this->ClaveCentroCosto . "', 
            ClaveCliente = '" . $this->ClaveCliente . "',NombreCentroCosto = '" . $this->NombreCentroCosto . "', 
            NoSerieEquipo = '" . $this->NoSerieEquipo . "',ModeloEquipo = '" . $this->ModeloEquipo . "',
            ActualizarInfoEquipo = " . $this->ActualizarInfoEquipo . ",NombreResp = '" . $this->NombreResp . "',
            Telefono1Resp = '" . $this->Telefono1Resp . "',Extension1Resp = '" . $this->Extension1Resp . "',
            Telefono2Resp = '" . $this->Telefono2Resp . "',Extension2Resp = '" . $this->Extension2Resp . "',
            CelularResp = '" . $this->CelularResp . "',CorreoEResp = '" . $this->CorreoEResp . "',
            HorarioAtenInicResp = '" . $this->HorarioAtenInicResp . "',HorarioAtenFinResp = '" . $this->HorarioAtenFinResp . "',
            NombreAtenc = '" . $this->NombreAtenc . "',Telefono1Atenc = '" . $this->Telefono1Atenc . "',
            Extension1Atenc = '" . $this->Extension1Atenc . "',Telefono2Atenc = '" . $this->Telefono2Atenc . "',
            Extension2Atenc = '" . $this->Extension2Atenc . "',CorreoEAtenc = '" . $this->CorreoEAtenc . "',
            CelularAtenc = '" . $this->CelularAtenc . "',HorarioAtenInicAtenc = '" . $this->HorarioAtenInicAtenc . "',
            HorarioAtenFinAtenc = '" . $this->HorarioAtenFinAtenc . "',NoTicketCliente = '" . $this->NoTicketCliente . "',
            NoTicketDistribuidor = '" . $this->NoTicketDistribuidor . "',DescripcionReporte = '" . $this->DescripcionReporte . "',
            ObservacionAdicional = '" . $this->ObservacionAdicional . "',AreaAtencion = '" . $this->AreaAtencion . "',
            Ubicacion = '" . $this->Ubicacion . "', UbicacionEmp = '" . $this->UbicacionEmp . "', NoGuia = '$this->NoGuia', 
            Activo = " . $this->Activo . ",UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->Pantalla . "'
            WHERE IdTicket='" . $this->IdTicket . "';";
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

    public function BuscarTicketAbierto($noSerie, $tipo, $conFecha) {
        $wFecha = "";
        $max = "MAX";
        if (isset($conFecha) && $conFecha == 1) {
            $max = "";
            $wFecha = " AND TIMESTAMPDIFF(SECOND, t.FechaCreacion,NOW()) <= 120 AND TIMESTAMPDIFF(SECOND, t.FechaCreacion,NOW()) >= 0";
        }
        $consulta = "";
        if ($tipo != "15") {
            $consulta = "SELECT  $max(t.IdTicket) AS IdTicket
                FROM c_ticket t  
                LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
                WHERE t.NoSerieEquipo='$noSerie' 
                AND (t.EstadoDeTicket<>2 AND t.EstadoDeTicket <> 4)
                AND (ISNULL(nt.IdNotaTicket) OR (nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59) ) $wFecha;";
        } else if ($tipo == "15") {
            $consulta = "SELECT $max(t.IdTicket) AS IdTicket
                FROM c_pedido p
                LEFT JOIN c_ticket t  ON p.IdTicket=t.IdTicket
                LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
                WHERE (t.NoSerieEquipo='$noSerie' OR p.ClaveEspEquipo = '$noSerie')
                AND (t.EstadoDeTicket<>2 AND t.EstadoDeTicket <> 4)
                AND (ISNULL(nt.IdNotaTicket) OR (nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59) ) $wFecha;";
        }
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $rs["IdTicket"];
        }
    }

    public function BuscarTicketXdia($noSerie, $tipo) {
        $consulta = "";
        if ($tipo == "1") {
            $consulta = "SELECT datediff(now(),MAX(t.FechaHora)) as fecha FROM c_ticket t WHERE t.NoSerieEquipo='$noSerie'";
        } else if ($tipo == "15") {
            $consulta = "SELECT datediff(now(),MAX(t.FechaHora)) as fecha FROM c_pedido p,c_ticket t WHERE p.IdTicket=t.IdTicket AND p.ClaveEspEquipo='$noSerie' ";
        }
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $rs["fecha"];
        }
    }

    public function BuscarClienteBYNoSerie($noSerie) {
        $consulta = "SELECT ie.NoSerie,ax.CveEspClienteCC,cc.ClaveCliente,c.NombreRazonSocial,c.IdEstatusCobranza,c.Suspendido,cc.ClaveCentroCosto
                    FROM c_inventarioequipo ie,k_anexoclientecc ax,c_centrocosto cc,c_cliente c WHERE ie.NoSerie='$noSerie' AND ax.IdAnexoClienteCC=ie.IdAnexoClienteCC AND (cc.ClaveCentroCosto=ax.CveEspClienteCC OR cc.ClaveCliente=ax.CveEspClienteCC) AND c.ClaveCliente=cc.ClaveCliente";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $rs["IdEstatusCobranza"] . " // " . $rs['Suspendido'] . " // " . $rs['NombreRazonSocial'] . " // " . $rs['ClaveCentroCosto'];
        }
    }

    public function EditarEquipoTicket() {
        if (isset($this->Prioridad) && $this->Prioridad != "") {
            $modPrio = ", Prioridad = $this->Prioridad ";
        }
        if (isset($this->NoGuia) && $this->NoGuia != "") {
            $modGuia = ", NoGuia = '$this->NoGuia' ";
        }

        if ($this->NoSerieEquipo == "" || $this->ModeloEquipo == "") {
            $obj = new Ticket();
            if ($obj->getTicketByID($this->getIdTicket())) {
                $this->NoSerieEquipo = $obj->getNoSerieEquipo();
                $this->ModeloEquipo = $obj->getModeloEquipo();
            } else {
                $consulta = ("UPDATE c_ticket SET NombreCliente = '" . $this->NombreCliente . "',ClaveCentroCosto = '" . $this->ClaveCentroCosto . "', 
                    ClaveCliente = '" . $this->ClaveCliente . "',NombreCentroCosto = '" . $this->NombreCentroCosto . "' $modPrio $modGuia                      
                    WHERE IdTicket='" . $this->IdTicket . "';");
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
        }
        $parametroGlobal = new ParametroGlobal();
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $modificaEstado = "";
        //Vemos si se modificó el estado del ticket
        $resultAux = $catalogo->obtenerLista("SELECT EstadoDeTicket FROM c_ticket WHERE IdTicket = $this->IdTicket");
        if ($rsAux = mysql_fetch_array($resultAux)) {
            if ((int) $rsAux['EstadoDeTicket'] != (int) $this->EstadoDeTicket) {
                $modificaEstado = ", FechaModificacionEstadoTicket = now() ";
                //Buscamos si hay escalamientos con tiempo de espera 0 para enviar correo
                $consulta = "SELECT cee.*, e.Nombre from c_escalamientoEstado cee LEFT JOIN c_estado AS e ON e.IdEstado = cee.idEstado"
                        . " WHERE e.IdEstadoTicket = " . $this->EstadoDeTicket . " AND cee.tiempoEnvio = 0 ";
                $result = $catalogo->obtenerLista($consulta);
                while ($row = mysql_fetch_array($result)) {
                    if ($row['prioridad'] < $parametros['prioridad']) {
                        $updatePrioridad = "UPDATE c_ticket t SET t.Prioridad = (SELECT pt.IdPrioridad from c_prioridadticket pt WHERE pt.Prioridad = " . $this->Prioridad . ")
                                WHERE t.IdTicket = " . $this->IdTicket;
                        $rsUpdate = $catalogo->obtenerLista($updatePrioridad);
                        if ($rsUpdate) {
                            echo "Se ha modificado la prioridad del ticket " . $this->IdTicket() . " debido a que el escalamiento tenía una prioridad mayor";
                        }
                    }
                    $correos = array();
                    $mail = new Mail();
                    $NombreCliente = "";
                    $mail->setSubject("Atención al ticket " . $this->IdTicket);
                    $consultaNombreCliente = "SELECT NombreCliente from c_ticket WHERE IdTicket =" . $this->IdTicket;
                    $resultNombre = $catalogo->obtenerLista($consultaNombreCliente);
                    if ($rsNombre = mysql_fetch_array($resultNombre)) {
                        $NombreCliente = $rsNombre['NombreCliente'];
                    }

                    $message = "<br/>Es importante que se atienda el ticket <b>" . $this->IdTicket . "</b> del cliente " . $NombreCliente . " se
                        encuentra en el estado " . $row['Nombre'] . "<br/>";
                    if ($this->TipoReporte != "15" && !empty($this->NoSerieEquipo)) {
                        $message .= ("Serie: " . $this->NoSerieEquipo);
                    }

                    $mail->setBody($message . "<br/>Mensaje: " . $row['mensaje']);
                    if ($parametroGlobal->getRegistroById("8")) {
                        $mail->setFrom($parametroGlobal->getValor());
                    } else {
                        $mail->setFrom("scg-salida@scgenesis.mx");
                    }
                    /* Obtenemos los correos a los que le enviaremos la informacion */
                    $queryCorreos = "SELECT correo from c_escalamientoCorreo ec WHERE idEscalamiento = " . $row['idEscalamiento'];
                    $resultCorreos = $catalogo->obtenerLista($queryCorreos);
                    while ($rsCorreo = mysql_fetch_array($resultCorreos)) {
                        $tipo = substr($rsCorreo['correo'], 0, 2);
                        $queryFinal = "";
                        if (strcmp($tipo, "cl") == 0) {
                            $queryFinal = "SELECT CorreoElectronicoEnvioFact1, CorreoElectronicoEnvioFact2, CorreoElectronicoEnvioFact3,CorreoElectronicoEnvioFact4
                                    from c_cliente WHERE ClaveCliente = " . $this->ClaveCliente;
                        } else if (strcmp($tipo, "co") == 0) {
                            $queryFinal = "SELECT CorreoElectronico from c_contacto WHERE IdTipoContacto = " . substr($rsCorreo['correo'], 2);
                        } else if (strcmp($tipo, "us") == 0) {
                            $queryFinal = "SELECT correo from c_usuario WHERE idUsuario = " . substr($rsCorreo['correo'], 2);
                        } else if (strcmp($tipo, "tf") == 0) {
                            $queryFinal = "SELECT u.correo from k_tfscliente ktc 
                                LEFT JOIN c_usuario u ON ktc.IdUsuario = u.IdUsuario 
                                LEFT JOIN c_ticket t ON t.ClaveCliente = ktc.ClaveCliente
                                WHERE t.IdTicket = " . $this->IdTicket;
                        }
                        $resultFinal = $catalogo->obtenerLista($queryFinal);
                        while ($rsFinal = mysql_fetch_array($resultFinal)) {
                            if (isset($rsFinal['CorreoElectronicoEnvioFact1']) && $rsFinal['CorreoElectronicoEnvioFact1'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact1'], FILTER_VALIDATE_EMAIL)) {
                                array_push($correos, $rsFinal['CorreoElectronicoEnvioFact1']);
                            }
                            if (isset($rsFinal['CorreoElectronicoEnvioFact2']) && $rsFinal['CorreoElectronicoEnvioFact2'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact2'], FILTER_VALIDATE_EMAIL)) {
                                array_push($correos, $rsFinal['CorreoElectronicoEnvioFact2']);
                            }
                            if (isset($rsFinal['CorreoElectronicoEnvioFact3']) && $rsFinal['CorreoElectronicoEnvioFact3'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact3'], FILTER_VALIDATE_EMAIL)) {
                                array_push($correos, $rsFinal['CorreoElectronicoEnvioFact3']);
                            }
                            if (isset($rsFinal['CorreoElectronicoEnvioFact4']) && $rsFinal['CorreoElectronicoEnvioFact4'] != "" && filter_var($rsFinal['CorreoElectronicoEnvioFact4'], FILTER_VALIDATE_EMAIL)) {
                                array_push($correos, $rsFinal['CorreoElectronicoEnvioFact4']);
                            }
                            if (isset($rsFinal['CorreoElectronico']) && $rsFinal['CorreoElectronico'] != "" && filter_var($rsFinal['CorreoElectronico'], FILTER_VALIDATE_EMAIL)) {
                                array_push($correos, $rsFinal['CorreoElectronico']);
                            }
                            if (isset($rsFinal['correo']) && $rsFinal['correo'] != "" && filter_var($rsFinal['correo'], FILTER_VALIDATE_EMAIL)) {
                                array_push($correos, $rsFinal['correo']);
                            }
                        }
                    }
                    foreach ($correos as $value) {/* Lo mandamos a los correos de los usuarios de cuentas por cobrar */
                        $mail->setTo($value);
                        if ($mail->enviarMail() == "1") {
                            echo "<br/>Un correo fue enviado por escalamientos a $value. <br/>";
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a $value. <br/>";
                        }
                    }
                }
            }
        }

        $consulta = ("UPDATE c_ticket SET NombreCliente = '" . $this->NombreCliente . "',ClaveCentroCosto = '" . $this->ClaveCentroCosto . "', 
            ClaveCliente = '" . $this->ClaveCliente . "',NombreCentroCosto = '" . $this->NombreCentroCosto . "', 
            NoSerieEquipo = '" . $this->NoSerieEquipo . "',ModeloEquipo = '" . $this->ModeloEquipo . "',EstadoDeTicket=$this->EstadoDeTicket $modPrio $modificaEstado $modGuia
            WHERE IdTicket='" . $this->IdTicket . "';");
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editTicketDescripcion() {
        $consulta = ("UPDATE c_ticket SET NoGuia = '$this->NoGuia', DescripcionReporte=(SELECT CONCAT('Solicitud de toner:', GROUP_CONCAT('(',nr.Cantidad,') ',c.Modelo))  
                        FROM c_notaticket nt,k_nota_refaccion nr,c_componente c 
                        WHERE nr.IdNotaTicket=nt.IdNotaTicket AND c.NoParte=nr.NoParteComponente AND nt.IdEstatusAtencion=67 AND nt.IdTicket='" . $this->IdTicket . "') WHERE IdTicket='" . $this->IdTicket . "'");

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

    public function actualizarUbicacion($noSerie, $ubicacion) {
        $consulta = ("UPDATE c_inventarioequipo SET Ubicacion = '$ubicacion',UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->Pantalla . "' WHERE NoSerie='$noSerie';");
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

    public function nuevaNotaAlAbrir() {
        if (isset($_SESSION['idUsuario'])) {
            $usuario = new Usuario();
            if ($usuario->getRegistroById($_SESSION['idUsuario']) && $usuario->getPuesto() == "21") {
                $nota = new AgregarNota();
                $nota->setIdTicket($this->IdTicket);
                $nota->setDiagnosticoSolucion("Ticket abierto por el TFS " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno());
                $nota->setIdestatusAtencion(103);
                $nota->setShow(1);
                $nota->setActivo(1);
                $nota->setUsuarioCreacion($usuario->getUsuario());
                $nota->setUsuarioCreacion($this->UsuarioCreacion);
                $nota->setUsuarioModificacion($this->UsuarioUltimaModificacion);
                $nota->setPantalla($this->Pantalla);
                if (!$nota->newRegistro()) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getUltimoTicketToner() {
        $consulta = "SELECT MAX(t.IdTicket) AS ticket FROM c_ticket t
            WHERE t.TipoReporte = 15 AND t.Resurtido = 0";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($result)) {
            $this->IdTicket = $rs['ticket'];
        }
    }

    public function getIdTicket() {
        return $this->IdTicket;
    }

    public function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    public function getFechaHora() {
        return $this->FechaHora;
    }

    public function setFechaHora($FechaHora) {
        $this->FechaHora = $FechaHora;
    }

    public function getUsuario() {
        return $this->Usuario;
    }

    public function setUsuario($Usuario) {
        $this->Usuario = $Usuario;
    }

    public function getEstadoDeTicket() {
        return $this->EstadoDeTicket;
    }

    public function setEstadoDeTicket($EstadoDeTicket) {
        $this->EstadoDeTicket = $EstadoDeTicket;
    }

    public function getTipoReporte() {
        return $this->TipoReporte;
    }

    public function setTipoReporte($TipoReporte) {
        $this->TipoReporte = $TipoReporte;
    }

    public function getActualizarInfoEstatCobra() {
        return $this->ActualizarInfoEstatCobra;
    }

    public function setActualizarInfoEstatCobra($ActualizarInfoEstatCobra) {
        $this->ActualizarInfoEstatCobra = $ActualizarInfoEstatCobra;
    }

    public function getActualizarInfoCliente() {
        return $this->ActualizarInfoCliente;
    }

    public function setActualizarInfoCliente($ActualizarInfoCliente) {
        $this->ActualizarInfoCliente = $ActualizarInfoCliente;
    }

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

    public function getModeloEquipo() {
        return $this->ModeloEquipo;
    }

    public function setModeloEquipo($ModeloEquipo) {
        $this->ModeloEquipo = $ModeloEquipo;
    }

    public function getActualizarInfoEquipo() {
        return $this->ActualizarInfoEquipo;
    }

    public function setActualizarInfoEquipo($ActualizarInfoEquipo) {
        $this->ActualizarInfoEquipo = $ActualizarInfoEquipo;
    }

    public function getNombreResp() {
        return $this->NombreResp;
    }

    public function setNombreResp($NombreResp) {
        $this->NombreResp = $NombreResp;
    }

    public function getTelefono1Resp() {
        return $this->Telefono1Resp;
    }

    public function setTelefono1Resp($Telefono1Resp) {
        $this->Telefono1Resp = $Telefono1Resp;
    }

    public function getExtension1Resp() {
        return $this->Extension1Resp;
    }

    public function setExtension1Resp($Extension1Resp) {
        $this->Extension1Resp = $Extension1Resp;
    }

    public function getTelefono2Resp() {
        return $this->Telefono2Resp;
    }

    public function setTelefono2Resp($Telefono2Resp) {
        $this->Telefono2Resp = $Telefono2Resp;
    }

    public function getExtension2Resp() {
        return $this->Extension2Resp;
    }

    public function setExtension2Resp($Extension2Resp) {
        $this->Extension2Resp = $Extension2Resp;
    }

    public function getCelularResp() {
        return $this->CelularResp;
    }

    public function setCelularResp($CelularResp) {
        $this->CelularResp = $CelularResp;
    }

    public function getCorreoEResp() {
        return $this->CorreoEResp;
    }

    public function setCorreoEResp($CorreoEResp) {
        $this->CorreoEResp = $CorreoEResp;
    }

    public function getHorarioAtenInicResp() {
        return $this->HorarioAtenInicResp;
    }

    public function setHorarioAtenInicResp($HorarioAtenInicResp) {
        $this->HorarioAtenInicResp = $HorarioAtenInicResp;
    }

    public function getHorarioAtenFinResp() {
        return $this->HorarioAtenFinResp;
    }

    public function setHorarioAtenFinResp($HorarioAtenFinResp) {
        $this->HorarioAtenFinResp = $HorarioAtenFinResp;
    }

    public function getNombreAtenc() {
        return $this->NombreAtenc;
    }

    public function setNombreAtenc($NombreAtenc) {
        $this->NombreAtenc = $NombreAtenc;
    }

    public function getTelefono1Atenc() {
        return $this->Telefono1Atenc;
    }

    public function setTelefono1Atenc($Telefono1Atenc) {
        $this->Telefono1Atenc = $Telefono1Atenc;
    }

    public function getExtension1Atenc() {
        return $this->Extension1Atenc;
    }

    public function setExtension1Atenc($Extension1Atenc) {
        $this->Extension1Atenc = $Extension1Atenc;
    }

    public function getTelefono2Atenc() {
        return $this->Telefono2Atenc;
    }

    public function setTelefono2Atenc($Telefono2Atenc) {
        $this->Telefono2Atenc = $Telefono2Atenc;
    }

    public function getExtension2Atenc() {
        return $this->Extension2Atenc;
    }

    public function setExtension2Atenc($Extension2Atenc) {
        $this->Extension2Atenc = $Extension2Atenc;
    }

    public function getCorreoEAtenc() {
        return $this->CorreoEAtenc;
    }

    public function setCorreoEAtenc($CorreoEAtenc) {
        $this->CorreoEAtenc = $CorreoEAtenc;
    }

    public function getCelularAtenc() {
        return $this->CelularAtenc;
    }

    public function setCelularAtenc($CelularAtenc) {
        $this->CelularAtenc = $CelularAtenc;
    }

    public function getHorarioAtenInicAtenc() {
        return $this->HorarioAtenInicAtenc;
    }

    public function setHorarioAtenInicAtenc($HorarioAtenInicAtenc) {
        $this->HorarioAtenInicAtenc = $HorarioAtenInicAtenc;
    }

    public function getHorarioAtenFinAtenc() {
        return $this->HorarioAtenFinAtenc;
    }

    public function setHorarioAtenFinAtenc($HorarioAtenFinAtenc) {
        $this->HorarioAtenFinAtenc = $HorarioAtenFinAtenc;
    }

    public function getNoTicketCliente() {
        return $this->NoTicketCliente;
    }

    public function setNoTicketCliente($NoTicketCliente) {
        $this->NoTicketCliente = $NoTicketCliente;
    }

    public function getNoTicketDistribuidor() {
        return $this->NoTicketDistribuidor;
    }

    public function setNoTicketDistribuidor($NoTicketDistribuidor) {
        $this->NoTicketDistribuidor = $NoTicketDistribuidor;
    }

    public function getFechHoraInicRep() {
        return $this->FechHoraInicRep;
    }

    public function setFechHoraInicRep($FechHoraInicRep) {
        $this->FechHoraInicRep = $FechHoraInicRep;
    }

    public function getDescripcionReporte() {
        return $this->DescripcionReporte;
    }

    public function setDescripcionReporte($DescripcionReporte) {
        $this->DescripcionReporte = $DescripcionReporte;
    }

    public function getObservacionAdicional() {
        return $this->ObservacionAdicional;
    }

    public function setObservacionAdicional($ObservacionAdicional) {
        $this->ObservacionAdicional = $ObservacionAdicional;
    }

    public function getAreaAtencion() {
        return $this->AreaAtencion;
    }

    public function setAreaAtencion($AreaAtencion) {
        $this->AreaAtencion = $AreaAtencion;
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

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getUbicacion() {
        return $this->Ubicacion;
    }

    public function setUbicacion($Ubicacion) {
        $this->Ubicacion = $Ubicacion;
    }

    public function getUbicacionEmp() {
        return $this->UbicacionEmp;
    }

    public function setUbicacionEmp($UbicacionEmp) {
        $this->UbicacionEmp = $UbicacionEmp;
    }

    public function getResurtido() {
        return $this->resurtido;
    }

    public function setResurtido($resurtido) {
        $this->resurtido = $resurtido;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getMensaje() {
        return $this->mensaje;
    }

    function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    function getFechaCheckIn() {
        return $this->FechaCheckIn;
    }

    function getFechaCheckOut() {
        return $this->FechaCheckOut;
    }

    function setFechaCheckIn($FechaCheckIn) {
        $this->FechaCheckIn = $FechaCheckIn;
    }

    function setFechaCheckOut($FechaCheckOut) {
        $this->FechaCheckOut = $FechaCheckOut;
    }

    function getPrioridad() {
        return $this->Prioridad;
    }

    function setPrioridad($Prioridad) {
        $this->Prioridad = $Prioridad;
    }

    function getNoGuia() {
        return $this->NoGuia;
    }

    function setNoGuia($NoGuia) {
        $this->NoGuia = $NoGuia;
    }

    function getIdSubtipo() {
        return $this->IdSubtipo;
    }

    function getFechaFinPrevisto() {
        return $this->FechaFinPrevisto;
    }

    function getFechaFinReal() {
        return $this->FechaFinReal;
    }

    function getPresupuesto() {
        return $this->Presupuesto;
    }

    function getProgreso() {
        return $this->Progreso;
    }

    function getNombre() {
        return $this->Nombre;
    }

    function setIdSubtipo($IdSubtipo) {
        $this->IdSubtipo = $IdSubtipo;
    }

    function setFechaFinPrevisto($FechaFinPrevisto) {
        $this->FechaFinPrevisto = $FechaFinPrevisto;
    }

    function setFechaFinReal($FechaFinReal) {
        $this->FechaFinReal = $FechaFinReal;
    }

    function setPresupuesto($Presupuesto) {
        $this->Presupuesto = $Presupuesto;
    }

    function setProgreso($Progreso) {
        $this->Progreso = $Progreso;
    }

    function setNombre($Nombre) {
        $this->Nombre = $Nombre;
    }

    function getContacto() {
        return $this->Contacto;
    }

    function setContacto($Contacto) {
        $this->Contacto = $Contacto;
    }

    function getTecnico() {
        return $this->Tecnico;
    }

    function setTecnico($Tecnico) {
        $this->Tecnico = $Tecnico;
    }

    function getUsuarioOrigen() {
        return $this->UsuarioOrigen;
    }

    function setUsuarioOrigen($UsuarioOrigen) {
        $this->UsuarioOrigen = $UsuarioOrigen;
    }

    function getCambioToner() {
        return $this->CambioToner;
    }

    function setCambioToner($CambioToner) {
        $this->CambioToner = $CambioToner;
    }

    function getPermitirTicketSinSerie() {
        return $this->PermitirTicketSinSerie;
    }

    function setPermitirTicketSinSerie($PermitirTicketSinSerie) {
        $this->PermitirTicketSinSerie = $PermitirTicketSinSerie;
    }

}
