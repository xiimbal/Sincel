<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class MovimientoComponente {

    private $idMovimiento;
    private $idTicket;
    private $idNotaTicket;
    private $noParteComponente;
    private $cantidadMovimiento;
    private $idAlmacenAnterior;
    private $idAlmacenNuevo;
    private $claveClienteNuevo;
    private $claveClienteAnterior;
    private $fecha;
    private $UsuarioCreacion;
    private $UsuarioModificacion;
    private $Pantalla;
    private $noSerieEquipoAnterior;
    private $noSerieEquipoNuevo;
    private $ClaveCentroCostoAnterior;
    private $ClaveCentroCostoNuevo;
    private $entradaSalida;
    private $comentario;
    private $idOrden;
    private $idTicketDevolucion;
    private $empresa;
    
    public function getDevolucionByTicket($idTicket){
        $consulta = "SELECT mc.NoParteComponente AS NoParteComponente, CONCAT(tc.Nombre,' (Devolución)') AS TipoComponente, CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS Modelo,
            mc.CantidadMovimiento AS Cantidad, mc.Fecha AS FechaEntrega, mc.IdTicketDevolucion AS IdTicket
            FROM movimiento_componente AS mc
            LEFT JOIN c_componente AS c ON c.NoParte = mc.NoParteComponente 
            LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
            WHERE IdTicketDevolucion = $idTicket;";
        
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function newRegistro() {
        if ($this->idAlmacenAnterior == "") {
            $idAlmacenAnterior = "NULL";
        } else {
            $idAlmacenAnterior = $this->idAlmacenAnterior;
        }

        if ($this->idAlmacenNuevo == "") {
            $idAlmacenNuevo = "NULL";
        } else {
            $idAlmacenNuevo = $this->idAlmacenNuevo;
        }

        if ($this->idTicket == "") {
            $idTicket = "NULL";
        } else {
            $idTicket = $this->idTicket;
        }

        if ($this->idNotaTicket == "") {
            $idNotaTicket = "NULL";
        } else {
            $idNotaTicket = $this->idNotaTicket;
        }
        if ($this->ClaveCentroCostoNuevo == "") {
            $cc = "NULL";
        } else {
            $cc = "'$this->ClaveCentroCostoNuevo'";
        }
        if ($this->noSerieEquipoNuevo == "") {
            $serie = "NULL";
        } else {
            $serie = "'$this->noSerieEquipoNuevo'";
        }
        if ($this->claveClienteNuevo == "") {
            $clClienteN = "NULL";
        } else {
            $clClienteN = "'$this->claveClienteNuevo'";
        }
        if ($this->claveClienteAnterior == "") {
            $clClienteA = "NULL";
        } else {
            $clClienteA = "'$this->claveClienteAnterior'";
        }

        if ($this->claveClienteNuevo == "") {
            $consulta = "INSERT INTO movimiento_componente(IdTicket,IdNotaTicket,NoParteComponente,CantidadMovimiento,IdAlmacenAnterior,IdAlmacenNuevo,ClaveClienteAnterior,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Entradada_Salida,ClaveCentroCostoNuevo,NoSerieNuevo)
                        VALUES($idTicket,$idNotaTicket,'$this->noParteComponente',$this->cantidadMovimiento,$idAlmacenAnterior,$idAlmacenNuevo,$clClienteA,NOW(),'" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioModificacion . "',NOW(),'$this->Pantalla',$this->entradaSalida, $cc ,$serie);";
        }
        if ($this->claveClienteAnterior == "") {
            $consulta = "INSERT INTO movimiento_componente(IdTicket,IdNotaTicket,NoParteComponente,CantidadMovimiento,IdAlmacenAnterior,IdAlmacenNuevo,ClaveClienteNuevo,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Entradada_Salida,ClaveCentroCostoNuevo,NoSerieNuevo)
                        VALUES($idTicket,$idNotaTicket,'$this->noParteComponente',$this->cantidadMovimiento,$idAlmacenAnterior,$idAlmacenNuevo,$clClienteN,NOW(),'" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioModificacion . "',NOW(),'$this->Pantalla',$this->entradaSalida,$cc ,$serie);";
        }

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->idMovimiento = $catalogo->insertarRegistro($consulta);
        if ($this->idMovimiento != NULL && $this->idMovimiento != 0) {
            return true;
        }
        return false;
    }

    public function newRegistroMovimientoAlmacen() {
        $consulta = "INSERT INTO movimiento_componente(IdTicket,IdNotaTicket,NoParteComponente,CantidadMovimiento,IdAlmacenAnterior,IdAlmacenNuevo,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Entradada_Salida,Comentario)
                        VALUES(NULL,NULL,'$this->noParteComponente',$this->cantidadMovimiento,$this->idAlmacenNuevo,$this->idAlmacenNuevo,now(),'" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioModificacion . "',NOW(),'$this->Pantalla',$this->entradaSalida,'" . $this->comentario . "');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->idMovimiento = $catalogo->insertarRegistro($consulta);
        if ($this->idMovimiento != NULL && $this->idMovimiento != 0) {
            return true;
        }
        return false;
    }

    public function EditarIdTicket($idTicket) {
        $consulta = ("UPDATE movimiento_componente SET IdTicket = '$idTicket' WHERE IdMovimiento='" . $this->idMovimiento . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newRegistroCompraComponente() {
        if (!isset($this->idOrden) || $this->idOrden == "") {
            $this->idOrden = "NULL";
        }

        if (!isset($this->idTicketDevolucion) || $this->idTicketDevolucion == "") {
            $this->idTicketDevolucion = "NULL";
        }

        $consulta = "INSERT INTO movimiento_componente(NoParteComponente,CantidadMovimiento,IdAlmacenNuevo,Fecha,UsuarioCreacion,
                    FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Entradada_Salida,Comentario,Id_compra,IdTicketDevolucion)
                        VALUES('$this->noParteComponente',$this->cantidadMovimiento,$this->idAlmacenNuevo,now(),
                        '" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioModificacion . "',NOW(),'$this->Pantalla',$this->entradaSalida,
                        '" . $this->comentario . "',$this->idOrden, $this->idTicketDevolucion);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->idMovimiento = $catalogo->insertarRegistro($consulta);
        if ($this->idMovimiento != NULL && $this->idMovimiento != 0) {
            return true;
        }
        return false;
    }

    public function getIdMovimiento() {
        return $this->idMovimiento;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function getIdNotaTicket() {
        return $this->idNotaTicket;
    }

    public function getNoParteComponente() {
        return $this->noParteComponente;
    }

    public function getCantidadMovimiento() {
        return $this->cantidadMovimiento;
    }

    public function getIdAlmacenAnterior() {
        return $this->idAlmacenAnterior;
    }

    public function getIdAlmacenNuevo() {
        return $this->idAlmacenNuevo;
    }

    public function getClaveClienteNuevo() {
        return $this->claveClienteNuevo;
    }

    public function getClaveClienteAnterior() {
        return $this->claveClienteAnterior;
    }

    public function getFecha() {
        return $this->fecha;
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

    public function setIdMovimiento($idMovimiento) {
        $this->idMovimiento = $idMovimiento;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function setIdNotaTicket($idNotaTicket) {
        $this->idNotaTicket = $idNotaTicket;
    }

    public function setNoParteComponente($noParteComponente) {
        $this->noParteComponente = $noParteComponente;
    }

    public function setCantidadMovimiento($cantidadMovimiento) {
        $this->cantidadMovimiento = $cantidadMovimiento;
    }

    public function setIdAlmacenAnterior($idAlmacenAnterior) {
        $this->idAlmacenAnterior = $idAlmacenAnterior;
    }

    public function setIdAlmacenNuevo($idAlmacenNuevo) {
        $this->idAlmacenNuevo = $idAlmacenNuevo;
    }

    public function setClaveClienteNuevo($claveClienteNuevo) {
        $this->claveClienteNuevo = $claveClienteNuevo;
    }

    public function setClaveClienteAnterior($claveClienteAnterior) {
        $this->claveClienteAnterior = $claveClienteAnterior;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
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

    public function getNoSerieEquipoAnterior() {
        return $this->noSerieEquipoAnterior;
    }

    public function getNoSerieEquipoNuevo() {
        return $this->noSerieEquipoNuevo;
    }

    public function getClaveCentroCostoAnterior() {
        return $this->ClaveCentroCostoAnterior;
    }

    public function getClaveCentroCostoNuevo() {
        return $this->ClaveCentroCostoNuevo;
    }

    public function setNoSerieEquipoAnterior($noSerieEquipoAnterior) {
        $this->noSerieEquipoAnterior = $noSerieEquipoAnterior;
    }

    public function setNoSerieEquipoNuevo($noSerieEquipoNuevo) {
        $this->noSerieEquipoNuevo = $noSerieEquipoNuevo;
    }

    public function setClaveCentroCostoAnterior($ClaveCentroCostoAnterior) {
        $this->ClaveCentroCostoAnterior = $ClaveCentroCostoAnterior;
    }

    public function setClaveCentroCostoNuevo($ClaveCentroCostoNuevo) {
        $this->ClaveCentroCostoNuevo = $ClaveCentroCostoNuevo;
    }

    public function getEntradaSalida() {
        return $this->entradaSalida;
    }

    public function setEntradaSalida($entradaSalida) {
        $this->entradaSalida = $entradaSalida;
    }

    public function getComentario() {
        return $this->comentario;
    }

    public function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getIdOrden() {
        return $this->idOrden;
    }

    public function setIdOrden($idOrden) {
        $this->idOrden = $idOrden;
    }

    public function getIdTicketDevolucion() {
        return $this->idTicketDevolucion;
    }

    public function setIdTicketDevolucion($idTicketDevolucion) {
        $this->idTicketDevolucion = $idTicketDevolucion;
    }

}

?>