<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class ResurtidoToner {

    private $almacen;
    private $fecha1;
    private $fecha2;
    private $cliente;
    private $localidad;
    private $equipo;
    private $almacenN;
    private $clienteN;
    private $localidadN;
    private $idResurtido;
    private $noParte;
    private $cantidadSurtido;
    private $idTicket;
    private $idAlmacen;
    private $fechaSolicitud;
    private $fechaSurtido;
    private $surtido;
    private $UsuarioCreacion;
    private $UsuarioModificacion;
    private $Pantalla;
    private $idTicketF;
    private $idMail;

    public function getTabla() {
        $where = "";
        if ($this->almacen != "") {
            $where .= " WHERE k_resurtidotoner.IdAlmacen='" . $this->almacen . "'";
        }
        if ($this->fecha1 != "" && $this->fecha2 != "") {
            if ($where == "") {
                $where = " WHERE c_ticket.FechaHora>='" . $this->fecha1 . "' AND c_ticket.FechaHora<='" . $this->fecha2 . "'";
            } else {
                $where .= " AND c_ticket.FechaHora>='" . $this->fecha1 . "' AND c_ticket.FechaHora<='" . $this->fecha2 . "'";
            }
        }
        if ($this->cliente != "") {
            if ($where == "") {
                $where = " WHERE c_ticket.ClaveCliente='" . $this->cliente . "'";
            } else {
                $where .= " AND c_ticket.ClaveCliente='" . $this->cliente . "'";
            }
        }
        if (isset($this->localidad) && $this->localidad != "") {
            if ($where == "") {
                $where = " WHERE c_ticket.ClaveCentroCosto='" . $this->localidad . "'";
            } else {
                $where .= " AND c_ticket.ClaveCentroCosto='" . $this->localidad . "'";
            }
        }
        if (isset($this->equipo) && $this->equipo != "") {
            if ($where == "") {
                $where = " WHERE c_pedido.ClaveEspEquipo='" . $this->equipo . "'";
            } else {
                $where .= " AND c_pedido.ClaveEspEquipo='" . $this->equipo . "'";
            }
        }
        if(isset($this->idTicket) && $this->idTicket != ""){
            $consulta = "SELECT
            c_ticket.FechaHora AS Fecha,  
            c_pedido.ClaveEspEquipo AS NoSerie,
            c_bitacora.NoParte AS Equipo, 
            c_pedido.Modelo AS Modelo,
            c_componente.NoParte AS NoParteT,
            c_componente.Modelo AS ModeloT,
            c_componente.Descripcion AS DescripcionT,
            k_resurtidotoner.Cantidadresurtido AS CantidadSolicitada,
            k_resurtidotoner.NoComponenteToner AS NoComponenteToner,
            c_ticket.IdTicket AS IdTicket,
            c_ticket.ClaveCliente AS ClaveCliente,
            c_ticket.ClaveCentroCosto AS ClaveCentroCosto,
            k_resurtidotoner.IdAlmacen AS IdAlmacen,
            act.CantidadMinima AS minimo,
            act.CantidadMaxima AS maximo,
            act.cantidad_existente AS existencia,
            act.NoEquiposBeneficiados AS equiposUso,
            ac.CantidadMinima AS minimoA,
            ac.CantidadMaxima AS maximoA,
            ac.cantidad_existencia AS existenciaA,
            ag.cantidad_existencia AS existenciaG,
            c_componente.PrecioDolares AS precio,
            c_componente.IdColor AS IdColor,
            nt.FechaCreacion AS fechaComponente,
            (SELECT SUM(nr.Cantidad)
            FROM `c_notaticket` AS nt
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
            WHERE nt.IdTicket = c_ticket.IdTicket AND !ISNULL(nr.IdNotaTicket)
            AND nt.IdEstatusAtencion = 66 AND nr.NoParteComponente = c_componente.NoParte) AS Cantidad,
            mpt.Contestada AS mail, mpt.FechaUltimaModificacion AS fechaValidado,
            (SELECT cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCentroCosto=c_ticket.ClaveCentroCosto) AS localidad,
            (SELECT cl.NombreRazonSocial FROM c_cliente cl WHERE cl.ClaveCliente=c_ticket.ClaveCliente) AS cliente,
            (SELECT al.nombre_almacen FROM c_almacen al WHERE al.id_almacen=k_resurtidotoner.IdAlmacen) AS almacen
            FROM c_ticket
            LEFT JOIN c_mailpedidotoner AS mpt ON mpt.idTicket = c_ticket.IdTicket 
            INNER JOIN c_pedido ON c_pedido.IdTicket = c_ticket.IdTicket
            LEFT JOIN c_notaticket AS nt ON nt.IdTicket = c_ticket.IdTicket
            INNER JOIN k_resurtidotoner ON k_resurtidotoner.IdTicket = c_ticket.IdTicket
            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(lta.id_lecturaticket) FROM c_lecturasticket lta WHERE lta.ClvEsp_Equipo = c_pedido.ClaveEspEquipo)
            LEFT JOIN c_bitacora ON c_pedido.ClaveEspEquipo = c_bitacora.NoSerie
            INNER JOIN c_componente ON c_componente.NoParte = k_resurtidotoner.NoComponenteToner 
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket AND nr.NoParteComponente = c_componente.NoParte
            LEFT JOIN k_almacencomponenteticket AS act ON act.NoParte = k_resurtidotoner.NoComponenteToner AND act.id_almacen = k_resurtidotoner.IdAlmacen AND act.IdTicket = c_ticket.IdTicket
            LEFT JOIN k_almacencomponente AS ac ON ac.NoParte = k_resurtidotoner.NoComponenteToner AND ac.id_almacen = k_resurtidotoner.IdAlmacen 
            LEFT JOIN k_almacencomponente AS ag ON ag.NoParte = k_resurtidotoner.NoComponenteToner AND ag.id_almacen = 6
            WHERE c_ticket.IdTicket = ".$this->idTicket." GROUP BY CONCAT(c_ticket.IdTicket,c_componente.NoParte) ORDER BY c_ticket.IdTicket";
        }else{
            $consulta = "SELECT
            c_ticket.FechaHora AS Fecha,  
            c_pedido.ClaveEspEquipo AS NoSerie,
            c_bitacora.NoParte AS Equipo, 
            c_pedido.Modelo AS Modelo,
            c_componente.NoParte AS NoParteT,
            c_componente.Modelo AS ModeloT,
            c_componente.Descripcion AS DescripcionT,
            k_resurtidotoner.Cantidadresurtido AS CantidadSolicitada,
            k_resurtidotoner.NoComponenteToner AS NoComponenteToner,
            c_ticket.IdTicket AS IdTicket,
            c_ticket.ClaveCliente AS ClaveCliente,
            c_ticket.ClaveCentroCosto AS ClaveCentroCosto,
            k_resurtidotoner.IdAlmacen AS IdAlmacen,
            act.CantidadMinima AS minimo,
            act.CantidadMaxima AS maximo,
            act.cantidad_existente AS existencia,
            ac.CantidadMinima AS minimoA,
            ac.CantidadMaxima AS maximoA,
            ac.cantidad_existencia AS existenciaA,
            c_componente.PrecioDolares AS precio,
            (SELECT SUM(nr.Cantidad)
            FROM `c_notaticket` AS nt
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
            WHERE nt.IdTicket = c_ticket.IdTicket AND !ISNULL(nr.IdNotaTicket)
            AND nt.IdEstatusAtencion = 66 AND nr.NoParteComponente = c_componente.NoParte) AS Cantidad,
            mpt.Contestada AS mail, mpt.FechaUltimaModificacion AS fechaValidado,
            (SELECT cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCentroCosto=c_ticket.ClaveCentroCosto) AS localidad,
            (SELECT cl.NombreRazonSocial FROM c_cliente cl WHERE cl.ClaveCliente=c_ticket.ClaveCliente) AS cliente,
            (SELECT al.nombre_almacen FROM c_almacen al WHERE al.id_almacen=k_resurtidotoner.IdAlmacen) AS almacen
            FROM c_ticket
            LEFT JOIN c_mailpedidotoner AS mpt ON mpt.idTicket = c_ticket.IdTicket 
            INNER JOIN c_pedido ON c_pedido.IdTicket = c_ticket.IdTicket
            LEFT JOIN c_notaticket AS nt ON nt.IdTicket = c_ticket.IdTicket
            INNER JOIN k_resurtidotoner ON k_resurtidotoner.IdTicket = c_ticket.IdTicket
            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(lta.id_lecturaticket) FROM c_lecturasticket lta WHERE lta.ClvEsp_Equipo = c_pedido.ClaveEspEquipo)
            LEFT JOIN c_bitacora ON c_pedido.ClaveEspEquipo = c_bitacora.NoSerie
            INNER JOIN c_componente ON c_componente.NoParte = k_resurtidotoner.NoComponenteToner 
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket AND nr.NoParteComponente = c_componente.NoParte
            LEFT JOIN k_almacencomponenteticket AS act ON act.NoParte = k_resurtidotoner.NoComponenteToner AND act.id_almacen = k_resurtidotoner.IdAlmacen AND act.IdTicket = c_ticket.IdTicket 
            LEFT JOIN k_almacencomponente AS ac ON ac.NoParte = k_resurtidotoner.NoComponenteToner AND ac.id_almacen = k_resurtidotoner.IdAlmacen " . 
            $where . " GROUP BY CONCAT(c_ticket.IdTicket,c_componente.NoParte) ORDER BY c_ticket.IdTicket DESC";  
        }    
        //echo $consulta;
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getLectura($nserie, $fecha) {
        $consulta = ("SELECT 
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN DATE(l.Fecha) ELSE DATE(lt.Fecha) END) AS Fecha,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNPaginas ELSE lt.ContadorBN END) AS ContadorBN,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorPaginas ELSE lt.ContadorCL END)AS ContadorCL
            FROM
            c_usuario
            LEFT JOIN c_lectura AS l ON l.NoSerie = '" . $nserie . "' AND l.Fecha = (SELECT MAX(Fecha) FROM c_lectura WHERE c_lectura.NoSerie = '" . $nserie . "' AND Fecha<='" . $fecha . "')
            LEFT JOIN c_lecturasticket AS lt ON lt.ClvEsp_Equipo = '" . $nserie . "' AND lt.Fecha = (SELECT MAX(Fecha) FROM c_lecturasticket WHERE c_lecturasticket.ClvEsp_Equipo = '" . $nserie . "' AND Fecha<='" . $fecha . "' AND (!ISNULL(ContadorBN) OR!ISNULL(ContadorCL)))
            ");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function ponerNombre() {
        $catalogo = new Catalogo();
        if ($this->almacen != "") {
            $query = $catalogo->obtenerLista("SELECT nombre_almacen AS Nombre FROM c_almacen WHERE id_almacen='" . $this->almacen . "'");
            if ($rs = mysql_fetch_array($query)) {
                $this->almacenN = $rs['Nombre'];
            }
        }
        if ($this->cliente != "") {
            $query = $catalogo->obtenerLista("SELECT NombreRazonSocial AS Nombre FROM c_cliente WHERE ClaveCliente='" . $this->cliente . "'");
            if ($rs = mysql_fetch_array($query)) {
                $this->clienteN = $rs['Nombre'];
            }
        }
        if (isset($_GET['localidad']) && $_GET['localidad'] != "") {
            $query = $catalogo->obtenerLista("SELECT Nombre AS Nombre FROM c_centrocosto WHERE ClaveCentroCosto='" . $this->localidad . "'");
            if ($rs = mysql_fetch_array($query)) {
                $this->localidadN = $rs['Nombre'];
            }
        }        
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO k_resurtidotoner(IdResurtidoToner,NoComponenteToner,Cantidadresurtido,IdTicket,IdAlmacen,FechaSoliciud,FechaSurtido,Surtido,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->noParte . "','" . $this->cantidadSurtido . "'," . $this->idTicket . ",'" . $this->idAlmacen . "',NOW(),NULL,0,'" . $this->UsuarioCreacion . "',now(),'" . $this->UsuarioModificacion . "',now(),'" . $this->Pantalla . "')");        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistroTicket($idTicketAnterior, $idTicketNuevo) {
        $consulta = ("UPDATE k_resurtidotoner SET IdTicket = '$idTicketNuevo' WHERE IdTicket='$idTicketAnterior';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function verificarResurtido() {        
        $consulta = "SELECT * FROM k_resurtidotoner krt 
            LEFT JOIN c_mailpedidotoner AS pt ON pt.idTicket = krt.IdTicket
            INNER JOIN c_ticket t ON t.IdTicket=pt.idTicket AND (t.EstadoDeTicket<>2 AND t.EstadoDeTicket<>4)
            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
            WHERE (ISNULL(nt.IdNotaTicket) OR (nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59)) 
            AND krt.NoComponenteToner='$this->noParte' AND krt.IdAlmacen='$this->idAlmacen' AND krt.Surtido=0 AND (pt.Contestada=1 OR pt.Contestada=0);";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function verificarResurtidoExistente() {        
        $consulta = "SELECT * FROM k_resurtidotoner krt 
            LEFT JOIN c_mailpedidotoner AS pt ON pt.idTicket = krt.IdTicket 
            INNER JOIN c_ticket t ON t.IdTicket=pt.idTicket AND (t.EstadoDeTicket<>2 OR t.EstadoDeTicket<>4) 
            WHERE krt.NoComponenteToner='$this->noParte' AND krt.IdAlmacen='$this->idAlmacen' AND krt.Surtido=0 AND pt.Contestada=0;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            while ($rs = mysql_fetch_array($query)) {
                $this->idResurtido = $rs['IdResurtidoToner'];
                $this->idTicket = $rs['IdTicket'];
            }            
            $consulta = ("UPDATE k_resurtidotoner SET Cantidadresurtido = Cantidadresurtido+1,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now()
                                    WHERE IdResurtidoToner='" . $this->idResurtido . "';");
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return TRUE;
            }
            return FALSE;
        }
        return FALSE;
    }

    public function verificarAlmacenTicketExistente() {        
        $consulta = "SELECT * FROM k_resurtidotoner krt 
            LEFT JOIN c_mailpedidotoner AS pt ON pt.idTicket = krt.IdTicket
            INNER JOIN c_ticket t ON t.IdTicket=pt.idTicket AND (t.EstadoDeTicket<>2 AND t.EstadoDeTicket<>4)
            WHERE krt.IdAlmacen='$this->idAlmacen' AND pt.Contestada=0 ORDER BY krt.IdResurtidoToner DESC LIMIT 1;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idTicketF = $rs['IdTicket'];
            $this->idMail = $rs['IdMail'];
        }
        if (mysql_num_rows($query) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function verificarResurtidoByAlamcen($idAlamcen, $idTicketNuevo) {        
        if($idTicketNuevo == NULL || $idTicketNuevo == ""){
            $consulta = "SELECT krt.IdTicket,c.Modelo,krt.Cantidadresurtido,DATE(krt.FechaSoliciud) as fecha 
                FROM k_resurtidotoner krt 
                LEFT JOIN c_mailpedidotoner AS pt ON pt.idTicket = krt.IdTicket
                LEFT JOIN c_componente AS c ON krt.NoComponenteToner = c.NoParte
                INNER JOIN c_ticket t ON t.IdTicket=pt.idTicket AND (t.EstadoDeTicket<>2 AND t.EstadoDeTicket<>4)
                WHERE krt.IdAlmacen='$idAlamcen' AND krt.Surtido = 0 AND pt.Contestada = 0
                ORDER BY krt.IdResurtidoToner;";
        }else{
            $consulta = "SELECT krt.IdTicket,c.Modelo,krt.Cantidadresurtido,DATE(krt.FechaSoliciud) as fecha 
                FROM k_resurtidotoner krt 
                LEFT JOIN c_mailpedidotoner AS pt ON pt.idTicket = krt.IdTicket
                LEFT JOIN c_componente AS c ON krt.NoComponenteToner = c.NoParte
                INNER JOIN c_ticket t ON t.IdTicket=pt.idTicket AND (t.EstadoDeTicket<>2 AND t.EstadoDeTicket<>4)
                WHERE krt.IdAlmacen='$idAlamcen' AND krt.Surtido = 0 AND pt.Contestada = 0 AND krt.IdTicket<>$idTicketNuevo
                ORDER BY krt.IdResurtidoToner;";
        }
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function ticketAnteriorResurtidoPorComponenteYAlmacen($NoParteComponente, $IdTicket, $IdAlmacen){
        $consulta = "SELECT MAX(rt.IdTicket) AS IdTicketAnterior FROM k_resurtidotoner rt
            WHERE rt.NoComponenteToner = '$NoParteComponente' AND rt.IdTicket < $IdTicket AND rt.IdAlmacen = $IdAlmacen";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            return $rs['IdTicketAnterior'];
        }
    }

    public function getAlmacen() {
        return $this->almacen;
    }

    public function setAlmacen($almacen) {
        $this->almacen = $almacen;
    }

    public function getFecha1() {
        return $this->fecha1;
    }

    public function setFecha1($fecha1) {
        $this->fecha1 = $fecha1;
    }

    public function getFecha2() {
        return $this->fecha2;
    }

    public function setFecha2($fecha2) {
        $this->fecha2 = $fecha2;
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

    public function getEquipo() {
        return $this->equipo;
    }

    public function setEquipo($equipo) {
        $this->equipo = $equipo;
    }

    public function getAlmacenN() {
        return $this->almacenN;
    }

    public function getClienteN() {
        return $this->clienteN;
    }

    public function getLocalidadN() {
        return $this->localidadN;
    }

    public function getIdResurtido() {
        return $this->idResurtido;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function getCantidadSurtido() {
        return $this->cantidadSurtido;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function getFechaSolicitud() {
        return $this->fechaSolicitud;
    }

    public function getFechaSurtido() {
        return $this->fechaSurtido;
    }

    public function getSurtido() {
        return $this->surtido;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setIdResurtido($idResurtido) {
        $this->idResurtido = $idResurtido;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function setCantidadSurtido($cantidadSurtido) {
        $this->cantidadSurtido = $cantidadSurtido;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function setFechaSolicitud($fechaSolicitud) {
        $this->fechaSolicitud = $fechaSolicitud;
    }

    public function setFechaSurtido($fechaSurtido) {
        $this->fechaSurtido = $fechaSurtido;
    }

    public function setSurtido($surtido) {
        $this->surtido = $surtido;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getIdTicketF() {
        return $this->idTicketF;
    }

    public function setIdTicketF($idTicketF) {
        $this->idTicketF = $idTicketF;
    }

    public function getIdMail() {
        return $this->idMail;
    }

    public function setIdMail($idMail) {
        $this->idMail = $idMail;
    }

}
