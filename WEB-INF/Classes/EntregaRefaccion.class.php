<?php

include_once ("Log.class.php");
include_once("Catalogo.class.php");

class EntregaRefaccion {

    private $id;
    private $idTicket;
    private $nota;
    private $noParte;
    private $cantidad;
    private $idAlmacenNuevo;
    private $idAlmacenAnterior;
    private $claveClienteNuevo;
    private $claveClienteAnterior;
    private $cantidadEntregar;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $notaFinal;
    private $idEstatus;
    private $cantidadSurtida;
    private $idAlmacen;
    private $refaccion;
    private $cantidadApartadas;
    private $entradaSalida;
    private $noSerie;
    private $claveCentroCosto;
    private $cantidadTotalSolicitada;
    private $cantidadTotalSurtida;
    private $ticket;
    private $partesSolicitadas;
    private $partesEntregadas;

    //$this->cantidad . "','".$this->idAlmacen."','" . $this->usuarioCreacion 
    //. "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");

    public function getCantidadALmacen() {
        $consulta = ("SELECT * FROM k_almacencomponente 
            WHERE NoParte='" . $this->refaccion . "' AND id_almacen='" . $this->idAlmacen . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->cantidad = $rs['cantidad_existencia'];
            $this->cantidadApartadas = $rs['cantidad_apartados'];
            return TRUE;
        }
        return FALSE;
    }

    public function editAlmacenComponentes() {
        /*Verificamos que no entren apartados negativos*/
        if($this->cantidadApartadas < 0){
            $log = new Log();
            $log->setConsulta("Intento de registrar apartados negativos ($this->cantidadApartadas)");
            $log->setSeccion($this->pantalla);
            $log->setIdUsuario($_SESSION['idUsuario']);
            $log->setTipo("Incidencia sistema");
            $log->newRegistro();
            $this->cantidadApartadas = 0;
        } 
        
        $consulta = ("UPDATE k_almacencomponente SET cantidad_apartados = '" . $this->cantidadApartadas . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE NoParte='" . $this->noParte . "' AND id_almacen='" . $this->idAlmacen . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newMovimiento() {
        $consulta = ("INSERT INTO movimiento_componente(IdMovimiento,IdTicket,IdNotaTicket,NoParteComponente,CantidadMovimiento,IdAlmacenAnterior,ClaveClienteNuevo,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Entradada_Salida,NoSerieNuevo,ClaveCentroCostoNuevo)
            VALUES(0,'" . $this->idTicket . "'," . $this->nota . ",'" . $this->noParte . "','" . $this->cantidadEntregar . "','" . $this->idAlmacenAnterior . "','" . $this->claveClienteNuevo . "',now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "',$this->entradaSalida,'" . $this->noSerie . "','" . $this->claveCentroCosto . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function EntregarRefaccion($idNota) {
        $consulta = ("INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,IdAlmacen,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,CantidadNota)
            VALUES('" . $idNota . "','" . $this->refaccion . "','" . $this->cantidad . "','" . $this->idAlmacen . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','" . $this->cantidad . "')");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function ObtenerCAntidadSurtida($nota, $rafaccion) {
        $consulta = ("SELECT * FROM k_nota_refaccion WHERE IdNotaTicket='" . $nota . "' AND NoParteComponente='" . $rafaccion . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->cantidadSurtida = $rs['CantidadSurtida'];
            return TRUE;
        }
        return FALSE;
    }

    public function EditCantidadSurtidas($nota, $rafaccion, $cantidad) {
        $consulta = ("UPDATE k_nota_refaccion SET CantidadSurtida = '" . $cantidad . "',UsuarioUltimaModificacion='" . $this->usuarioModificacion . "',FechaUltimaModificacion=now()
            WHERE IdNotaTicket='" . $nota . "' AND NoParteComponente='" . $rafaccion . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function getCantidadSolicitadaBYticket($nota) {
        /*$consulta = ("SELECT SUM(nr.Cantidad) AS total,nt.IdTicket 
            FROM c_notaticket nt,k_nota_refaccion nr 
            WHERE nt.IdTicket=(SELECT nt2.IdTicket FROM c_notaticket nt2 WHERE nt2.IdNotaTicket='$nota') 
            AND (nt.IdEstatusAtencion=9) AND nr.IdNotaTicket=nt.IdNotaTicket;");*/
        $consulta = "SELECT SUM(nr.Cantidad) AS total,nt.IdTicket, c.Modelo
            FROM c_notaticket nt
            LEFT JOIN k_nota_refaccion nr ON nr.IdNotaTicket=nt.IdNotaTicket
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            WHERE nt.IdTicket=(SELECT nt2.IdTicket FROM c_notaticket nt2 WHERE nt2.IdNotaTicket='$nota') AND (nt.IdEstatusAtencion=9)
            GROUP BY c.Modelo;";
        
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $this->cantidadTotalSolicitada = 0;
        $query = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($query) > 0){
            while ($rs = mysql_fetch_array($query)) {
                $this->cantidadTotalSolicitada += (int)$rs['total'];
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
            AND (nt.IdEstatusAtencion=17 OR nt.IdEstatusAtencion = 68) AND nr.IdNotaTicket=nt.IdNotaTicket;");*/
        $consulta = "SELECT SUM(nr.Cantidad) AS total, c.Modelo
            FROM c_notaticket nt
            LEFT JOIN k_nota_refaccion nr ON  nr.IdNotaTicket=nt.IdNotaTicket
            LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
            WHERE nt.IdTicket=(SELECT nt2.IdTicket FROM c_notaticket nt2 WHERE nt2.IdNotaTicket='$nota') 
            AND (nt.IdEstatusAtencion=17 OR nt.IdEstatusAtencion = 68)
            GROUP BY c.Modelo;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        $this->cantidadTotalSurtida = 0;
        if(mysql_num_rows($query) > 0){
            while ($rs = mysql_fetch_array($query)) {
                $this->cantidadTotalSurtida += (int)$rs['total'];
                $this->partesEntregadas[$rs['Modelo']] = $rs['total'];                
            }
            return true;
        }
        return false;
    }
    
    public function getCatidadValidadaByTicket($idNota){
        $consulta = ("SELECT t.IdTicket, nt.IdNotaTicket, t.FechaHora,
            (SELECT SUM(knr3.Cantidad) AS Solicitada FROM c_ticket AS t2
            LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
            LEFT JOIN k_nota_refaccion AS knr3 ON knr3.IdNotaTicket = nt2.IdNotaTicket
            WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 9) AS Solicitada,
            (SELECT (CASE WHEN SUM(knr4.Cantidad) IS NULL THEN 0 ELSE SUM(knr4.Cantidad) END)
            FROM c_ticket AS t2
            LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = t2.IdTicket
            LEFT JOIN k_nota_refaccion AS knr4 ON knr4.IdNotaTicket = nt2.IdNotaTicket
            WHERE t2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = 24) AS Validada
            FROM `c_ticket` AS t
            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = $idNota AND nt.IdTicket = t.IdTicket
            WHERE nt.IdEstatusAtencion = 9
            GROUP BY IdTicket
            HAVING !ISNULL(Solicitada)
            ORDER BY t.IdTicket DESC;");
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->cantidadTotalSolicitada = $rs['Validada'];
            $this->ticket = $rs['IdTicket'];
            return true;
        }
        return false;
    }
    
    

    public function getIdEstatus() {
        return $this->idEstatus;
    }

    public function setIdEstatus($idEstatus) {
        $this->idEstatus = $idEstatus;
    }

    public function getNotaFinal() {
        return $this->notaFinal;
    }

    public function setNotaFinal($notaFinal) {
        $this->notaFinal = $notaFinal;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function getNota() {
        return $this->nota;
    }

    public function setNota($nota) {
        $this->nota = $nota;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function getCantidadEntregar() {
        return $this->cantidadEntregar;
    }

    public function setCantidadEntregar($cantidadEntregar) {
        $this->cantidadEntregar = $cantidadEntregar;
    }

    public function getIdAlmacenNuevo() {
        return $this->idAlmacenNuevo;
    }

    public function setIdAlmacenNuevo($idAlmacenNuevo) {
        $this->idAlmacenNuevo = $idAlmacenNuevo;
    }

    public function getIdAlmacenAnterior() {
        return $this->idAlmacenAnterior;
    }

    public function setIdAlmacenAnterior($idAlmacenAnterior) {
        $this->idAlmacenAnterior = $idAlmacenAnterior;
    }

    public function getClaveClienteNuevo() {
        return $this->claveClienteNuevo;
    }

    public function setClaveClienteNuevo($claveClienteNuevo) {
        $this->claveClienteNuevo = $claveClienteNuevo;
    }

    public function getClaveClienteAnterior() {
        return $this->claveClienteAnterior;
    }

    public function setClaveClienteAnterior($claveClienteAnterior) {
        $this->claveClienteAnterior = $claveClienteAnterior;
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

    public function getCantidadSurtida() {
        return $this->cantidadSurtida;
    }

    public function setCantidadSurtida($cantidadSurtida) {
        $this->cantidadSurtida = $cantidadSurtida;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getRefaccion() {
        return $this->refaccion;
    }

    public function setRefaccion($refaccion) {
        $this->refaccion = $refaccion;
    }

    public function getCantidadApartadas() {
        return $this->cantidadApartadas;
    }

    public function setCantidadApartadas($cantidadApartadas) {
        $this->cantidadApartadas = $cantidadApartadas;
    }

    public function getEntradaSalida() {
        return $this->entradaSalida;
    }

    public function setEntradaSalida($entradaSalida) {
        $this->entradaSalida = $entradaSalida;
    }

    public function getNoSerie() {
        return $this->noSerie;
    }

    public function setNoSerie($noSerie) {
        $this->noSerie = $noSerie;
    }

    public function getClaveCentroCosto() {
        return $this->claveCentroCosto;
    }

    public function setClaveCentroCosto($claveCentroCosto) {
        $this->claveCentroCosto = $claveCentroCosto;
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
    
    function getPartesSolicitadas() {
        return $this->partesSolicitadas;
    }

    function setPartesSolicitadas($partesSolicitadas) {
        $this->partesSolicitadas = $partesSolicitadas;
    }

    function getPartesEntregadas() {
        return $this->partesEntregadas;
    }

    function setPartesEntregadas($partesEntregadas) {
        $this->partesEntregadas = $partesEntregadas;
    }

}

?>
