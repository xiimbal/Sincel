<?php

include_once("Log.class.php");
include_once("Catalogo.class.php");
include_once("AlmacenConmponente.class.php");

class SolicitudToner {

    private $notaAnterior;
    private $idNota;
    private $IdEstadoNota;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $noParteComponente;
    private $cantidadSolicitada;
    private $idAlmacen;
    private $cantidadExistente;
    private $cantidadMaxima;
    private $cantidadMinima;
    private $cantidadResurtido;
    private $idTicket;
    private $cantidadTotalSolicitada;
    private $cantidadTotalSurtida;
    private $ticket;
    private $claveCliente;
    private $claveCentroCosto;
    private $idSolicitud;
    private $IdSolicitudEquipo;
    private $idMensajeria;
    private $noGuia;
    private $idVehiculo;
    private $idConductor;
    private $estatus;
    private $activo;
    private $descripcion;
    private $nombreAlmacen;
    private $nombreCliente;
    private $claveCentroCostoTicket;
    private $claveClienteTicket;
    private $nombreCC;
    private $idAlmaceNuevo;
    private $idMiniAlmcenLocalidad;
    private $descripcionToner;
    private $modeloComponente;
    private $noParteResurtido = array();
    private $totalResurtidoAlmacen = array();
    private $mostrarCliente;
    private $NoSerieEquipo;
    private $empresa;
    private $otros;
    private $NumeroSurtido;
    private $partesSolicitadas;
    private $partesEntregadas;

    public function newNotaSolicitudToner() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        if (!isset($this->NumeroSurtido) || empty($this->NumeroSurtido)) {
            $this->NumeroSurtido = "NULL";
        }

        if (isset($this->descripcion) && $this->descripcion != "") {
            $consulta = ("INSERT INTO c_notaticket (IdTicket,DiagnosticoSol,IdEstatusAtencion,NumeroSurtido,
            Activo,UsuarioSolicitud,MostrarCliente,FechaHora,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            SELECT IdTicket,'$this->descripcion','" . $this->IdEstadoNota . "',$this->NumeroSurtido,
            1,UsuarioSolicitud,$this->mostrarCliente,now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'  FROM c_notaticket cc WHERE cc.IdNotaTicket='" . $this->notaAnterior . "'");
        } else {
            $consulta = ("INSERT INTO c_notaticket (IdTicket,DiagnosticoSol,IdEstatusAtencion,NumeroSurtido,
            Activo,UsuarioSolicitud,MostrarCliente,FechaHora,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            SELECT IdTicket,cc.DiagnosticoSol,'" . $this->IdEstadoNota . "',$this->NumeroSurtido,
            1,UsuarioSolicitud,$this->mostrarCliente,now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'  FROM c_notaticket cc WHERE cc.IdNotaTicket='" . $this->notaAnterior . "'");
        }

        $this->idNota = $catalogo->insertarRegistro($consulta);
        if ($this->idNota != NULL && $this->idNota != 0) {
            return true;
        }
        return false;
    }

    public function newNotaSolicitudTonerTicket() {
        $consulta = ("INSERT INTO c_notaticket (IdTicket,DiagnosticoSol,IdEstatusAtencion,Activo,UsuarioSolicitud,MostrarCliente,FechaHora,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                SELECT IdTicket,cc.DiagnosticoSol,'" . $this->IdEstadoNota . "',0,UsuarioSolicitud,$this->mostrarCliente,now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'  FROM c_notaticket cc WHERE cc.IdNotaTicket='" . $this->notaAnterior . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->idNota = $catalogo->insertarRegistro($consulta);
        if ($this->idNota != NULL && $this->idNota != 0) {
            return true;
        }
        return false;
    }

    public function copyTonerNota() {
        $consulta = ("INSERT INTO k_nota_refaccion (IdNotaTicket,NoParteComponente,Cantidad,CantidadSurtida,IdAlmacen,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla,NoSerieEquipo)
            SELECT '" . $this->idNota . "',NoParteComponente,Cantidad,CantidadSurtida,IdAlmacen,'" . $this->usuarioCreacion . "',NOW(),
            '" . $this->usuarioModificacion . "',NOW(),'" . $this->pantalla . "',NoSerieEquipo FROM k_nota_refaccion cc WHERE cc.IdNotaTicket=$this->notaAnterior");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function InsertNotaToner() {
        if ($this->idAlmacen == "0") {
            $consulta = ("INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,CantidadSurtida,CantidadNota,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $this->idNota . "','" . $this->noParteComponente . "','" . $this->cantidadSolicitada . "','0','" . $this->cantidadSolicitada . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $query = $catalogo->obtenerLista($consulta);
        } else {
            $consulta = ("INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,CantidadSurtida,CantidadNota,IdAlmacen,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $this->idNota . "','" . $this->noParteComponente . "','" . $this->cantidadSolicitada . "','0','" . $this->cantidadSolicitada . "','" . $this->idAlmacen . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $query = $catalogo->obtenerLista($consulta);
        }

        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function estaTodoListo($serie) {
        if (isset($serie) && $serie != NULL && $serie != "") {
            $consulta = ("SELECT CantidadSurtida,Cantidad 
                FROM k_nota_refaccion
                WHERE NoParteComponente='$this->noParteComponente' AND IdNotaTicket='$this->notaAnterior' AND NoSerieEquipo = '$serie';");
        } else {
            $consulta = ("SELECT CantidadSurtida,Cantidad 
                FROM k_nota_refaccion
                WHERE NoParteComponente='$this->noParteComponente' AND IdNotaTicket='$this->notaAnterior';");
        }
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            if ($rs['Cantidad'] == "0") {
                return true;
            }
        }
        return false;
    }

    public function EditCantidadPedido($totalRestantes, $serie) {
        if (isset($serie) && $serie != NULL && $serie != "") {
            $consulta = ("UPDATE k_nota_refaccion SET CantidadSurtida = CantidadSurtida+'" . $this->cantidadSolicitada . "',
                Cantidad=Cantidad-$totalRestantes,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',
                FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
                WHERE NoParteComponente='" . $this->noParteComponente . "' AND IdNotaTicket='" . $this->notaAnterior . "' AND NoSerieEquipo = '$serie';");
        } else {
            $consulta = ("UPDATE k_nota_refaccion SET CantidadSurtida = CantidadSurtida+'" . $this->cantidadSolicitada . "',
                Cantidad=Cantidad-$totalRestantes,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',
                FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
                WHERE NoParteComponente='" . $this->noParteComponente . "' AND IdNotaTicket='" . $this->notaAnterior . "';");
        }
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

    public function EditCantidadPedidoEntrega() {
        $consulta = ("UPDATE k_nota_refaccion SET CantidadSurtida =CantidadSurtida+'" . $this->cantidadSolicitada . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE NoParteComponente='" . $this->noParteComponente . "' AND IdNotaTicket='" . $this->notaAnterior . "';");
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

    public function EditCantidadAlmacen() {
        /* Verificamos que no entren existencias negativas */
        $almacenComponente = new AlmacenComponente();
        if ($almacenComponente->getRegistroById($this->noParteComponente, $this->idAlmacen)) {
            if ($this->cantidadSolicitada > $almacenComponente->getExistencia()) {
                $log = new Log();
                $log->setConsulta("Intento de registrar existencias negativas ($this->cantidadSolicitada)");
                $log->setSeccion($this->pantalla);
                $log->setIdUsuario($_SESSION['idUsuario']);
                $log->setTipo("Incidencia sistema");
                $log->newRegistro();
                $this->cantidadSolicitada = $almacenComponente->getExistencia();
            }
        }

        $consulta = ("UPDATE k_almacencomponente SET cantidad_existencia = cantidad_existencia - $this->cantidadSolicitada, 
            cantidad_apartados = cantidad_apartados + $this->cantidadSolicitada,
            UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
            WHERE NoParte='" . $this->noParteComponente . "' AND id_almacen='" . $this->idAlmacen . "';");
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

    public function editAlmacenComponenteApartado($totalApartados) {
        /* Verificamos que no entren existencias negativas */
        $almacenComponente = new AlmacenComponente();
        if ($almacenComponente->getRegistroById($this->noParteComponente, $this->idAlmacen)) {
            if ($totalApartados > $almacenComponente->getApartados()) {
                $log = new Log();
                $log->setConsulta("Intento de registrar existencias negativas ($totalApartados)");
                $log->setSeccion($this->pantalla);
                $log->setIdUsuario($_SESSION['idUsuario']);
                $log->setTipo("Incidencia sistema");
                $log->newRegistro();
                $totalApartados = $almacenComponente->getApartados();
            }
        }

        $consulta = ("UPDATE k_almacencomponente SET cantidad_apartados = cantidad_apartados - $totalApartados,
            UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
            WHERE NoParte='" . $this->noParteComponente . "' AND id_almacen='" . $this->idAlmacen . "';");
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

    public function getDatosAlmacen() {
        $consulta = ("SELECT * FROM k_almacencomponente WHERE id_almacen='" . $this->idAlmacen . "' AND NoParte='" . $this->noParteComponente . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->cantidadExistente = $rs['cantidad_existencia'];
            $this->cantidadMinima = $rs['CantidadMinima'];
            $this->cantidadMaxima = $rs['CantidadMaxima'];
        }
        return $query;
    }

    public function newResurtidoToner() {
        $consulta = ("INSERT INTO k_resurtidotoner(NoComponenteToner,Cantidadresurtido,IdTicket,FechaSoliciud,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,IdAlmacen)
                                SELECT '" . $this->noParteComponente . "','" . $this->cantidadResurtido . "',IdTicket,now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','" . $this->idAlmacen . "'  FROM c_notaticket cc WHERE cc.IdNotaTicket=$this->idNota");
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

    public function getNotaByIdTicket($idTicket) {
        $consulta = ("SELECT nt.IdNotaTicket FROM c_notaticket nt WHERE nt.IdTicket=$idTicket ");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->notaAnterior = $rs['IdNotaTicket'];
            return true;
        }
        return false;
    }

    public function getCantidadSolicitadaBYticket($nota) {
        /*$consulta = ("SELECT SUM(nr.Cantidad) AS total,nt.IdTicket 
        FROM c_notaticket nt,k_nota_refaccion nr 
        WHERE nt.IdTicket=(SELECT nt2.IdTicket FROM c_notaticket nt2 WHERE nt2.IdNotaTicket='$nota') 
        AND (nt.IdEstatusAtencion=67) AND nr.IdNotaTicket=nt.IdNotaTicket;");*/
        $consulta = "SELECT SUM(nr.Cantidad) AS total,nt.IdTicket, c.Modelo
            FROM c_notaticket nt
            LEFT JOIN k_nota_refaccion nr ON nr.IdNotaTicket=nt.IdNotaTicket
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            WHERE nt.IdTicket=(SELECT nt2.IdTicket FROM c_notaticket nt2 WHERE nt2.IdNotaTicket='$nota') 
            AND (nt.IdEstatusAtencion=67)
            GROUP BY c.Modelo;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $this->cantidadTotalSolicitada = 0;
        if (mysql_num_rows($query) > 0) {
            while ($rs = mysql_fetch_array($query)) {
                $this->cantidadTotalSolicitada += (int) $rs['total'];
                $this->partesSolicitadas[$rs['Modelo']] = $rs['total'];
                $this->ticket = $rs['IdTicket'];
            }
            return true;
        }
        return false;
    }

    public function getCantidadSurtidaByticket($nota) {
        /*$consulta = ("SELECT SUM(nr.Cantidad) AS total 
        FROM c_notaticket nt,k_nota_refaccion nr 
        WHERE nt.IdTicket=(SELECT nt2.IdTicket FROM c_notaticket nt2 WHERE nt2.IdNotaTicket='$nota') 
        AND (nt.IdEstatusAtencion=66 OR nt.IdEstatusAtencion = 68) AND nr.IdNotaTicket=nt.IdNotaTicket;");*/
        $consulta = "SELECT SUM(nr.Cantidad) AS total, c.Modelo
            FROM c_notaticket nt
            LEFT JOIN k_nota_refaccion nr ON nr.IdNotaTicket=nt.IdNotaTicket
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            WHERE nt.IdTicket=(SELECT nt2.IdTicket FROM c_notaticket nt2 WHERE nt2.IdNotaTicket='$nota') 
            AND (nt.IdEstatusAtencion=66 OR nt.IdEstatusAtencion = 68)
            GROUP BY c.Modelo;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $this->cantidadTotalSurtida = 0;
        if (mysql_num_rows($query) > 0) {
            while ($rs = mysql_fetch_array($query)) {
                $this->cantidadTotalSurtida += (int)$rs['total'];
                $this->partesEntregadas[$rs['Modelo']] = $rs['total'];
            }
            return true;
        }
        return false;
    }

    public function marcarSurtidoResurtidoToner() {
        $consulta = "UPDATE `k_resurtidotoner` SET Surtido = 1 WHERE NoComponenteToner = '$this->noParteComponente' AND IdTicket = $this->idTicket;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query != "0") {
            return true;
        }
        return false;
    }

    public function editTicket() {
        $consulta = ("UPDATE c_ticket SET EstadoDeTicket = '2',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdTicket='" . $this->ticket . "';");
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

    public function newMovimientoToner() {
        if ($this->claveCliente != "") {
            $consulta = ("INSERT INTO movimiento_componente(IdTicket,IdNotaTicket,NoParteComponente,CantidadMovimiento,IdAlmacenAnterior,ClaveClienteNuevo,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,ClaveCentroCostoNuevo)
            VALUES('" . $this->ticket . "','" . $this->idNota . "','" . $this->noParteComponente . "','" . $this->cantidadSolicitada . "','" . $this->idAlmacen . "','" . $this->claveCliente . "',now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','" . $this->claveCentroCosto . "')");
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            } $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        } else {
            $consulta = ("INSERT INTO movimiento_componente(IdTicket,IdNotaTicket,NoParteComponente,CantidadMovimiento,IdAlmacenAnterior,IdAlmacenNuevo,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,ClaveCentroCostoNuevo)
            VALUES('" . $this->ticket . "','" . $this->idNota . "','" . $this->noParteComponente . "','" . $this->cantidadSolicitada . "','" . $this->idAlmacen . "','" . $this->idAlmaceNuevo . "',now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','" . $this->claveCentroCosto . "')");
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            } $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }
    }

    public function newEnvioToner($tipoEnvio) {
        $idSolicitud = "null";
        if (isset($this->IdSolicitudEquipo) && $this->IdSolicitudEquipo != "") {
            $idSolicitud = $this->IdSolicitudEquipo;
        }
        $idPedido = "null";
        if (isset($this->idSolicitud) && $this->idSolicitud != "") {
            $idPedido = $this->idSolicitud;
        }
        if ($tipoEnvio == "1") {//transporte propio            
            $consulta = "INSERT INTO k_enviotoner(NoParte,Cantidad,ClaveCentroCosto,IdSolicitud,IdSolicitudEquipo,IdVehiculo,IdConductor,
                Estatus,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $this->noParteComponente . "','" . $this->cantidadSolicitada . "','" . $this->claveCentroCosto . "',$idPedido,
                $idSolicitud,'" . $this->idVehiculo . "','" . $this->idConductor . "',0,1,'" . $this->usuarioCreacion . "',now(),
                '" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')";
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            } $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        } else if ($tipoEnvio == 2) {//mensajeria            
            $consulta = "INSERT INTO k_enviotoner(NoParte,Cantidad,ClaveCentroCosto,IdSolicitud,IdSolicitudEquipo,IdMensajeria,NoGuia,Estatus,
                Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noParteComponente . "','" . $this->cantidadSolicitada . "','" . $this->claveCentroCosto . "',$idPedido,
            $idSolicitud,'" . $this->idMensajeria . "','" . $this->noGuia . "',0,1,'" . $this->usuarioCreacion . "',now(),
            '" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')";
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            } $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        } else {
            $consulta = "INSERT INTO k_enviotoner(NoParte,Cantidad,ClaveCentroCosto,IdSolicitud,IdSolicitudEquipo,Otro,Estatus,
                Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noParteComponente . "','" . $this->cantidadSolicitada . "','" . $this->claveCentroCosto . "',$idPedido,
            $idSolicitud,'" . $this->otros . "',0,1,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')";
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            } $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }
    }

    public function getDatosTicketAnterior($idTicket) {
        $consulta = ("SELECT * FROM c_ticket t WHERE t.IdTicket='" . $idTicket . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->nombreCliente = $rs['NombreCliente'];
            $this->claveCentroCostoTicket = $rs['ClaveCentroCosto'];
            $this->claveClienteTicket = $rs['ClaveCliente'];
            $this->nombreCC = $rs['NombreCentroCosto'];
            return true;
        }
        return false;
    }

    /* INSERT INTO c_pedido(IdTicket,ClaveEspEquipo,TonerNegro,TonerCian,TonerMagenta,TonerAmarillo,IdLecturaTicket,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Estado,Modelo) SELECT '',ClaveEspEquipo,'17','0','0','0',IdLecturaTicket,1,'hugosh',now(),'hugosh',now(),'Ticket de resurtido de toner',Estado,Modelo FROM c_pedido p WHERE p.IdTicket='10079' */

    public function NewPedidoToner($idTicket, $tNegro, $tCia, $tMagenta, $tAmarrillo) {
        $consulta = ("INSERT INTO c_pedido(IdTicket,ClaveEspEquipo,TonerNegro,TonerCian,TonerMagenta,TonerAmarillo,IdLecturaTicket,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Estado,Modelo)
                                SELECT '" . $this->idTicket . "',ClaveEspEquipo,'" . $tNegro . "','" . $tCia . "','" . $tMagenta . "','" . $tAmarrillo . "',IdLecturaTicket,1,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "',Estado,Modelo FROM c_pedido p WHERE p.IdTicket='" . $idTicket . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function NombreAlmacen($idAlmacen) {
        $consulta = ("SELECT * FROM c_almacen a WHERE a.id_almacen='" . $idAlmacen . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->nombreAlmacen = $rs['nombre_almacen'];
            return true;
        }
        return false;
    }

    public function ComprobarExistencia($ticket) {
        $consulta = ("SELECT * FROM k_resurtidotoner rt WHERE rt.IdTicket='" . $ticket . "'AND rt.NoComponenteToner='" . $this->noParteComponente . "' AND rt.IdAlmacen='" . $this->idAlmacen . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return FALSE;
        }
        return TRUE;
    }

    public function EditarEstatusMail($contestada, $idMail) {
        $consulta = ("UPDATE c_mailpedidotoner SET Contestada = $contestada,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' "
                . "WHERE idTicket = (SELECT * FROM (SELECT m2.IdTicket FROM c_mailpedidotoner m2 WHERE IdMail = '$idMail') AS a);");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function AgregarNota() {
        $consulta = ("INSERT INTO c_notaticket (IdTicket,DiagnosticoSol,IdEstatusAtencion,Activo,UsuarioSolicitud,MostrarCliente,FechaHora,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                SELECT IdTicket,'Ticket cerrado','16',1,UsuarioSolicitud,1,now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'  FROM c_notaticket cc WHERE cc.IdNotaTicket=$this->notaAnterior");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->idNota = $catalogo->insertarRegistro($consulta);
        if ($this->idNota != NULL && $this->idNota != 0) {
            return true;
        }
        return false;
    }

//26/02/2014
    public function VerifyComponenteMiniAlamcen($claveCC) {
        $consulta = ("SELECT * FROM c_centrocosto cc ,k_minialmacenlocalidad ma WHERE cc.ClaveCentroCosto=ma.ClaveCentroCosto AND cc.ClaveCentroCosto='" . $claveCC . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function GetStockMiniAlamcen($claveCC) {
        $consulta = ("SELECT * FROM c_centrocosto cc ,k_minialmacenlocalidad ma WHERE cc.ClaveCentroCosto=ma.ClaveCentroCosto AND cc.ClaveCentroCosto='" . $claveCC . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $rs['IdAlmacen'];
        }
        return false;
    }

    public function EditarComponentesMiniALmacen($cantidad) {
        /* Verificamos que no entren existencias negativas */
        $almacenComponente = new AlmacenComponente();
        if ($almacenComponente->getRegistroById($this->noParteComponente, $this->idAlmacen)) {
            if ($cantidad > $almacenComponente->getExistencia()) {
                $log = new Log();
                $log->setConsulta("Intento de registrar existencias negativas ($cantidad)");
                $log->setSeccion($this->pantalla);
                $log->setIdUsuario($_SESSION['idUsuario']);
                $log->setTipo("Incidencia sistema");
                $log->newRegistro();
                $cantidad = $almacenComponente->getExistencia();
            }
        }
        $consulta = ("UPDATE k_almacencomponente SET cantidad_existencia = cantidad_existencia - $cantidad, 
            UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
            WHERE NoParte='" . $this->noParteComponente . "' AND id_almacen='" . $this->idAlmacen . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function crearResurtidoToner() {
        $consulta = ("INSERT INTO k_resurtidotoner(NoComponenteToner,Cantidadresurtido,IdTicket,FechaSoliciud,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,IdAlmacen)
                              VALUES('" . $this->noParteComponente . "','" . $this->cantidadResurtido . "','" . $this->ticket . "',now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','" . $this->idAlmacen . "')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function GetDatosToner($componente) {
        $consulta = ("SELECT * FROM c_componente a WHERE a.NoParte='" . $componente . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->modeloComponente = $rs['Modelo'];
            $this->descripcionToner = $rs['Descripcion'];
            return true;
        }
        return false;
    }

    public function getComponentesMiniAlmacenResurtido($idMiniALmacen) {
        $consulta = ("SELECT ac.NoParte,ac.CantidadMaxima - ac.cantidad_existencia AS total FROM k_almacencomponente ac WHERE ac.id_almacen='" . $idMiniALmacen . "' AND ac.NoParte<>'" . $this->noParteComponente . "' ORDER BY ac.NoParte ASC");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->noParteResurtido[$contador] = $rs['NoParte'];
            $this->totalResurtidoAlmacen[$contador] = $rs['total'];
            $contador++;
        }
    }

    public function getAlmacenByCC($cc) {
        $consulta = ("SELECT ml.IdAlmacen FROM  k_minialmacenlocalidad ml WHERE ml.ClaveCentroCosto='$cc'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return $rs['IdAlmacen'];
        }
    }

    /**
     * Obtiene un resultSet con los toner compatibles con el toner especificado
     * @param type $noParte NoParte del toner
     * @return type resultSet con los toner compatibles
     */
    public function getTonerCompatible($noParte) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT kecc.NoParteComponente, c.Modelo, kecc.NoParteEquipo, kecc2.NoParteComponente AS ParteCompatible, c2.Modelo AS ModeloCompatible
            FROM k_equipocomponentecompatible AS kecc
            LEFT JOIN k_equipocomponentecompatible AS kecc2 ON kecc.NoParteEquipo = kecc2.NoParteEquipo
            LEFT JOIN c_componente AS c ON c.NoParte = kecc.NoParteComponente
            LEFT JOIN c_componente AS c2 ON c2.NoParte = kecc2.NoParteComponente
            WHERE kecc.NoParteComponente = '$noParte' AND c2.IdTipoComponente = c.IdTipoComponente AND c.IdColor = c2.IdColor 
            AND c2.Activo = 1
            GROUP BY c2.NoParte;";
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function getNotaAnterior() {
        return $this->notaAnterior;
    }

    public function setNotaAnterior($notaAnterior) {
        $this->notaAnterior = $notaAnterior;
    }

    public function getIdNota() {
        return $this->idNota;
    }

    public function setIdNota($idNota) {
        $this->idNota = $idNota;
    }

    public function getIdEstadoNota() {
        return $this->IdEstadoNota;
    }

    public function setIdEstadoNota($IdEstadoNota) {
        $this->IdEstadoNota = $IdEstadoNota;
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

    public function getNoParteComponente() {
        return $this->noParteComponente;
    }

    public function setNoParteComponente($noParteComponente) {
        $this->noParteComponente = $noParteComponente;
    }

    public function getCantidadSolicitada() {
        return $this->cantidadSolicitada;
    }

    public function setCantidadSolicitada($cantidadSolicitada) {
        $this->cantidadSolicitada = $cantidadSolicitada;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getCantidadExistente() {
        return $this->cantidadExistente;
    }

    public function setCantidadExistente($cantidadExistente) {
        $this->cantidadExistente = $cantidadExistente;
    }

    public function getCantidadMaxima() {
        return $this->cantidadMaxima;
    }

    public function setCantidadMaxima($cantidadMaxima) {
        $this->cantidadMaxima = $cantidadMaxima;
    }

    public function getCantidadMinima() {
        return $this->cantidadMinima;
    }

    public function setCantidadMinima($cantidadMinima) {
        $this->cantidadMinima = $cantidadMinima;
    }

    public function getCantidadResurtido() {
        return $this->cantidadResurtido;
    }

    public function setCantidadResurtido($cantidadResurtido) {
        $this->cantidadResurtido = $cantidadResurtido;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function getCantidadTotalSolicitada() {
        return $this->cantidadTotalSolicitada;
    }

    public function setCantidadTotalSolicitada($cantidadTotalSolicitada) {
        $this->cantidadTotalSolicitada = $cantidadTotalSolicitada;
    }

    public function getCantidadTotalSurtida() {
        return $this->cantidadTotalSurtida;
    }

    public function setCantidadTotalSurtida($cantidadTotalSurtida) {
        $this->cantidadTotalSurtida = $cantidadTotalSurtida;
    }

    public function getTicket() {
        return $this->ticket;
    }

    public function setTicket($ticket) {
        $this->ticket = $ticket;
    }

    public function getClaveCliente() {
        return $this->claveCliente;
    }

    public function setClaveCliente($claveCliente) {
        $this->claveCliente = $claveCliente;
    }

    public function getClaveCentroCosto() {
        return $this->claveCentroCosto;
    }

    public function setClaveCentroCosto($claveCentroCosto) {
        $this->claveCentroCosto = $claveCentroCosto;
    }

    public function getIdSolicitud() {
        return $this->idSolicitud;
    }

    public function setIdSolicitud($idSolicitud) {
        $this->idSolicitud = $idSolicitud;
    }

    public function getIdMensajeria() {
        return $this->idMensajeria;
    }

    public function setIdMensajeria($idMensajeria) {
        $this->idMensajeria = $idMensajeria;
    }

    public function getNoGuia() {
        return $this->noGuia;
    }

    public function setNoGuia($noGuia) {
        $this->noGuia = $noGuia;
    }

    public function getIdVehiculo() {
        return $this->idVehiculo;
    }

    public function setIdVehiculo($idVehiculo) {
        $this->idVehiculo = $idVehiculo;
    }

    public function getIdConductor() {
        return $this->idConductor;
    }

    public function setIdConductor($idConductor) {
        $this->idConductor = $idConductor;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getNombreAlmacen() {
        return $this->nombreAlmacen;
    }

    public function setNombreAlmacen($nombreAlmacen) {
        $this->nombreAlmacen = $nombreAlmacen;
    }

    public function getNombreCliente() {
        return $this->nombreCliente;
    }

    public function setNombreCliente($nombreCliente) {
        $this->nombreCliente = $nombreCliente;
    }

    public function getClaveCentroCostoTicket() {
        return $this->claveCentroCostoTicket;
    }

    public function setClaveCentroCostoTicket($claveCentroCostoTicket) {
        $this->claveCentroCostoTicket = $claveCentroCostoTicket;
    }

    public function getClaveClienteTicket() {
        return $this->claveClienteTicket;
    }

    public function setClaveClienteTicket($claveClienteTicket) {
        $this->claveClienteTicket = $claveClienteTicket;
    }

    public function getNombreCC() {
        return $this->nombreCC;
    }

    public function setNombreCC($nombreCC) {
        $this->nombreCC = $nombreCC;
    }

    public function getIdAlmaceNuevo() {
        return $this->idAlmaceNuevo;
    }

    public function setIdAlmaceNuevo($idAlmaceNuevo) {
        $this->idAlmaceNuevo = $idAlmaceNuevo;
    }

    public function getIdMiniAlmcenLocalidad() {
        return $this->idMiniAlmcenLocalidad;
    }

    public function setIdMiniAlmcenLocalidad($idMiniAlmcenLocalidad) {
        $this->idMiniAlmcenLocalidad = $idMiniAlmcenLocalidad;
    }

    public function getDescripcionToner() {
        return $this->descripcionToner;
    }

    public function getModeloComponente() {
        return $this->modeloComponente;
    }

    public function setDescripcionToner($descripcionToner) {
        $this->descripcionToner = $descripcionToner;
    }

    public function setModeloComponente($modeloComponente) {
        $this->modeloComponente = $modeloComponente;
    }

    public function getNoParteResurtido() {
        return $this->noParteResurtido;
    }

    public function setNoParteResurtido($noParteResurtido) {
        $this->noParteResurtido = $noParteResurtido;
    }

    public function getTotalResurtidoAlmacen() {
        return $this->totalResurtidoAlmacen;
    }

    public function setTotalResurtidoAlmacen($totalResurtidoAlmacen) {
        $this->totalResurtidoAlmacen = $totalResurtidoAlmacen;
    }

    public function getMostrarCliente() {
        return $this->mostrarCliente;
    }

    public function setMostrarCliente($mostrarCliente) {
        $this->mostrarCliente = $mostrarCliente;
    }

    public function getIdSolicitudEquipo() {
        return $this->IdSolicitudEquipo;
    }

    public function setIdSolicitudEquipo($IdSolicitudEquipo) {
        $this->IdSolicitudEquipo = $IdSolicitudEquipo;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getNoSerieEquipo() {
        return $this->NoSerieEquipo;
    }

    public function setNoSerieEquipo($NoSerieEquipo) {
        $this->NoSerieEquipo = $NoSerieEquipo;
    }

    function getOtros() {
        return $this->otros;
    }

    function setOtros($otros) {
        $this->otros = $otros;
    }

    function getNumeroSurtido() {
        return $this->NumeroSurtido;
    }

    function setNumeroSurtido($NumeroSurtido) {
        $this->NumeroSurtido = $NumeroSurtido;
    }

    function getPartesSolicitadas() {
        return $this->partesSolicitadas;
    }

    function getPartesEntregadas() {
        return $this->partesEntregadas;
    }

    function setPartesSolicitadas($partesSolicitadas) {
        $this->partesSolicitadas = $partesSolicitadas;
    }

    function setPartesEntregadas($partesEntregadas) {
        $this->partesEntregadas = $partesEntregadas;
    }

}

?>
