<?php

include_once("Catalogo.class.php");
include_once("NotaTicket.class.php");
include_once("Ticket.class.php");

class NotaRefaccion {

    private $idNota;
    private $NoParte;
    private $cantidad;
    private $cantidadSurtidas;
    private $idAlmacen;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $usuarioModificacion;
    private $fechaModificacion;
    private $pantalla;
    private $idTicket;
    private $noSerie;
    private $empresa;

    public function newRegistro() {
        $consulta = ("INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,CantidadSurtida,CantidadNota,IdAlmacen,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idNota . "','" . $this->NoParte . "','" . $this->cantidad . "','" . $this->cantidadSurtidas . "','" . $this->cantidad . "'," . $this->idAlmacen . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
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

    public function newRegistroDetalle() {
        if(isset($this->noSerie) && !empty($this->noSerie)){
            $serie = "'$this->noSerie'";
        }else{
            $serie = "NULL";
        }
        
        $consulta = ("INSERT INTO k_detalle_notarefaccion(IdDetalleNotaRefaccion,IdNota,Componente,Cantidad,NoSerieEquipo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->idNota . "','" . $this->NoParte . "','" . $this->cantidad . "',$serie,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
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

    public function newRegistroDetallefusion() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        //Validamos la serie en caso de ser un resurtido, la serie siempre tiene que ser la misma:
        $nota = new NotaTicket();
        $ticket = new Ticket();
        $resurtido = false;
        if ($nota->getRegistroById($this->idNota)) {
            if ($ticket->getTicketByID($nota->getIdTicket())) {
                if ($ticket->getResurtido() == "1") {
                    $resurtido = true;
                }
            }
        }

        if ($resurtido) {
            $consulta = "select MIN(IdDetalleNotaRefaccion), NoSerieEquipo from k_detalle_notarefaccion WHERE IdNota = $this->idNota;";
            $query = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($query)) {
                $this->noSerie = $rs['NoSerieEquipo'];
            }
        }

        $consulta = "SELECT IdDetalleNotaRefaccion from k_detalle_notarefaccion WHERE Componente = '$this->NoParte' AND IdNota = $this->idNota;";
        $query = $catalogo->obtenerLista($consulta);

        if (mysql_num_rows($query) > 0) {
            while ($rs = mysql_fetch_array($query)) {
                $consulta = "UPDATE k_detalle_notarefaccion SET Cantidad = $this->cantidad, NoSerieEquipo = '$this->noSerie', UsuarioUltimaModificacion = '$this->usuarioModificacion', FechaUltimaModificacion = NOW(),
                        Pantalla = '$this->pantalla' WHERE IdDetalleNotaRefaccion = " . $rs['IdDetalleNotaRefaccion'];
            }
        } else {
            $consulta = "INSERT INTO k_detalle_notarefaccion(IdDetalleNotaRefaccion,IdNota,Componente,Cantidad,NoSerieEquipo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES(0,'" . $this->idNota . "','" . $this->NoParte . "','" . $this->cantidad . "','" . $this->noSerie . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')";
        }

        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newRegistroSerie() {
        $consulta = "INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,CantidadSurtida,IdAlmacen,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,NoSerieEquipo)
            VALUES('" . $this->idNota . "','" . $this->NoParte . "','" . $this->cantidad . "','" . $this->cantidadSurtidas . "'," . $this->idAlmacen . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','" . $this->noSerie . "')";
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

    public function VerificarExistencia() {
        $consulta = "SELECT * FROM k_nota_refaccion e WHERE IdNotaTicket='" . $this->idNota . "' AND NoParteComponente='" . $this->NoParte . "'";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {//existe
            return TRUE;
        }
        return FAlSE;
    }

    public function getNotaRefaccionByidNota($id) {
        $consulta = "SELECT c.Modelo,nr.Cantidad FROM k_nota_refaccion nr,c_componente c WHERE IdNotaTicket='$id' AND c.NoParte=nr.NoParteComponente";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function editarCantidadTonerRepetido() {
        $consulta = "UPDATE k_nota_refaccion SET Cantidad = Cantidad+" . $this->cantidad . ",CantidadNota=CantidadNota+" . $this->cantidad . " WHERE IdNotaTicket='" . $this->idNota . "' AND NoParteComponente='" . $this->NoParte . "';";
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

    public function editarCantidadResurtido($idTicket, $noParte) {
        $consulta = ("UPDATE k_nota_refaccion SET Cantidad=Cantidad+1 ,CantidadNota=CantidadNota+1 ,UsuarioUltimaModificacion='$this->usuarioModificacion',FechaUltimaModificacion=now()
                                WHERE IdNotaTicket=(SELECT nt.IdNotaTicket FROM c_ticket t INNER JOIN c_notaticket nt ON t.IdTicket=nt.IdTicket AND t.IdTicket='$idTicket') 
                                AND NoParteComponente='$noParte'");
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

    public function deleteRegitro() {
        $consulta = ("DELETE FROM k_nota_refaccion WHERE IdNotaTicket IN (SELECT nt.IdNotaTicket FROM c_notaticket nt WHERE nt.IdTicket='$this->idTicket')");
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

    public function deleteDetalleRefaccion() {
        $consulta = ("DELETE FROM k_detalle_notarefaccion WHERE IdNota=(SELECT nt.IdNotaTicket FROM c_notaticket nt WHERE nt.IdTicket='$this->idTicket' AND nt.IdEstatusAtencion=67)");
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

    public function getIdNota() {
        return $this->idNota;
    }

    public function getNoParte() {
        return $this->NoParte;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getCantidadSurtidas() {
        return $this->cantidadSurtidas;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setIdNota($idNota) {
        $this->idNota = $idNota;
    }

    public function setNoParte($NoParte) {
        $this->NoParte = $NoParte;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function setCantidadSurtidas($cantidadSurtidas) {
        $this->cantidadSurtidas = $cantidadSurtidas;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function getNoSerie() {
        return $this->noSerie;
    }

    public function setNoSerie($noSerie) {
        $this->noSerie = $noSerie;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
