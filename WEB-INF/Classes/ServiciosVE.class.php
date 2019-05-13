<?php

include_once ("Catalogo.class.php");

class ServiciosVE {

    private $IdServicioVE;
    private $NombreServicio;
    private $Tipo;
    private $IdAnexoClienteCC;
    private $PrecioUnitario;
    private $IdEstado;
    private $IdTarifa;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $IdPartida;
    private $cantidad;
    private $fecha;
    private $idTicket;
    private $IdNotaTicket;
    private $IdNotaTicketFacturar;
    private $CobrarSiNo;
    private $PagarSiNo;
    private $IdUsuario;
    private $Comentario;
    private $Validado;
    private $CantidadOriginal;
    private $empresa;

    public function getServiciosVEByAnexo() {
        $consulta = "SELECT sve.*, e.Nombre,  t.Tarifa 
            FROM c_serviciosve sve 
            LEFT JOIN c_estado e ON e.IdEstado = sve.IdEstado
            LEFT JOIN c_tarifarango AS t ON t.IdTarifa = sve.IdTarifa
            WHERE sve.IdAnexoClienteCC = '$this->IdAnexoClienteCC'";
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {
        if (!isset($this->IdEstado) || empty($this->IdEstado)) {
            $this->IdEstado = "NULL";
        }
        if (!isset($this->IdTarifa) || empty($this->IdTarifa)) {
            $this->IdTarifa = "NULL";
        }
        if (!isset($this->PrecioUnitario) || empty($this->PrecioUnitario)) {
            $this->PrecioUnitario = "NULL";
        }
        if (!isset($this->IdAnexoClienteCC) || empty($this->IdAnexoClienteCC)) {
            $this->IdAnexoClienteCC = "NULL";
        }
        $consulta = "INSERT INTO c_serviciosve(NombreServicio, Tipo, IdAnexoClienteCC, PrecioUnitario, IdEstado, IdTarifa, UsuarioCreacion, 
            FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES('$this->NombreServicio',
            $this->Tipo, $this->IdAnexoClienteCC, $this->PrecioUnitario, $this->IdEstado, $this->IdTarifa, '$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $this->IdServicioVE = $catalogo->insertarRegistro($consulta);
        if ($this->IdServicioVE != NULL && $this->IdServicioVE != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if (!isset($this->IdEstado) || empty($this->IdEstado)) {
            $this->IdEstado = "NULL";
        }

        if (!isset($this->IdTarifa) || empty($this->IdTarifa)) {
            $this->IdTarifa = "NULL";
        }

        if (!isset($this->PrecioUnitario) || empty($this->PrecioUnitario)) {
            $this->PrecioUnitario = "NULL";
        }

        $consulta = "UPDATE c_serviciosve SET NombreServicio = '$this->NombreServicio', Tipo = $this->Tipo, IdTarifa= $this->IdTarifa,
                    PrecioUnitario = $this->PrecioUnitario, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',
                    FechaUltimaModificacion = NOW() WHERE IdServicioVE = $this->IdServicioVE";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result == 1) {
            return true;
        }
        return false;
    }

    public function borrarRegistrosPorAnexo() {
        $consulta = "DELETE FROM c_serviciosve WHERE IdAnexoClienteCC = '$this->IdAnexoClienteCC'";
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    public function borrarRegistrosPorServicios($servicios) {
        $consulta = "DELETE FROM k_serviciove WHERE IdServicioVE IN 
                    (SELECT IdServicioVE FROM c_serviciosve WHERE IdServicioVE NOT IN($servicios) AND IdAnexoClienteCC = '$this->IdAnexoClienteCC')";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            $consulta2 = "DELETE FROM c_serviciosve WHERE IdServicioVE NOT IN ($servicios) AND IdAnexoClienteCC = '$this->IdAnexoClienteCC'";
            $query2 = $catalogo->obtenerLista($consulta2);
            if ($query == "1") {
                return true;
            }
        }
        return false;
    }

    public function newRegistroKServicio() {
        if(!isset($this->IdNotaTicket) || empty($this->IdNotaTicket)){
            $this->IdNotaTicket = "NULL";
        }
        
        if(!isset($this->CobrarSiNo) || ($this->CobrarSiNo)==""){
            $this->CobrarSiNo = "0";
        }
        
        if(!isset($this->PagarSiNo) || ($this->PagarSiNo)==""){
            $this->PagarSiNo = "0";
        }
        
        if(!isset($this->IdUsuario) || empty($this->IdUsuario)){
            $this->IdUsuario = "NULL";
        }               
        
        $consulta = "INSERT INTO k_serviciove(IdServicioVE,cantidad,Fecha,IdTicket,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,
            IdNotaTicket,CobrarSiNo,PagarSiNo,IdUsuario,Comentario, Validado, CantidadOriginal)
            VALUES($this->IdServicioVE,$this->cantidad, '$this->fecha', $this->idTicket,
            '$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla',
            $this->IdNotaTicket,$this->CobrarSiNo,$this->PagarSiNo,$this->IdUsuario,'$this->Comentario', $this->Validado, $this->CantidadOriginal);";         
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdPartida = $catalogo->insertarRegistro($consulta);
        if ($this->IdPartida != NULL && $this->IdPartida != 0) {
            return true;
        }
        return false;
    }

    public function buscarServicioByEstatusNota($estatus) {
        $consulta = "SELECT sve.IdServicioVE FROM c_serviciosve sve
                LEFT JOIN c_anexotecnico ate ON ate.ClaveAnexoTecnico = sve.IdAnexoClienteCC 
                LEFT JOIN c_contrato ct ON ct.NoContrato = ate.NoContrato 
                WHERE ct.ClaveCliente = (SELECT ClaveCliente FROM c_ticket WHERE IdTicket = $this->idTicket)
                AND sve.IdEstado = $estatus AND ct.Activo = 1 ";  
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($result)) {            
            $this->IdServicioVE = $rs['IdServicioVE'];                      
            return true;
        }else{
            $this->IdServicioVE = 74;               
            return true;
        }
        return false;
    }
    
    function guardarIdNotaFactura(){
        $validado = "";
        if(!empty($this->Validado)){
            $validado = ", Validado = $this->Validado";
        }
        //Aquí vamos a guardar la nueva cantidad, ya que CantidadOriginal siempre tendrá el valor que se le asigno por el operador.
        $where = "IdPartida = $this->IdPartida";
        $query = "UPDATE k_serviciove SET cantidad = $this->cantidad, IdNotaTicketFacturar = $this->IdNotaTicketFacturar $validado WHERE $where";
        //echo "$query <br>";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($query);
        if ($result == 1) {
            return true;
        }
        return false;
    }
    
    function getRegistroById($id){
        $consulta = ("SELECT * FROM k_serviciove WHERE IdPartida='" . $id . "'");
        //echo "$consulta<br>";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->IdPartida = $rs['IdPartida'];
            $this->IdServicioVE = $rs['IdServicioVE'];
            $this->IdNotaTicketFacturar = $rs['IdNotaTicketFacturar'];
            $this->cantidad = $rs['cantidad'];
        }
        return $query;
    }
    
    

    function getIdServicioVE() {
        return $this->IdServicioVE;
    }

    function getNombreServicio() {
        return $this->NombreServicio;
    }

    function getTipo() {
        return $this->Tipo;
    }

    function getIdAnexoClienteCC() {
        return $this->IdAnexoClienteCC;
    }

    function getPrecioUnitario() {
        return $this->PrecioUnitario;
    }

    function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    function getPantalla() {
        return $this->Pantalla;
    }

    function setIdServicioVE($IdServicioVE) {
        $this->IdServicioVE = $IdServicioVE;
    }

    function setNombreServicio($NombreServicio) {
        $this->NombreServicio = $NombreServicio;
    }

    function setTipo($Tipo) {
        $this->Tipo = $Tipo;
    }

    function setIdAnexoClienteCC($IdAnexoClienteCC) {
        $this->IdAnexoClienteCC = $IdAnexoClienteCC;
    }

    function setPrecioUnitario($PrecioUnitario) {
        $this->PrecioUnitario = $PrecioUnitario;
    }

    function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    function getIdEstado() {
        return $this->IdEstado;
    }

    function setIdEstado($IdEstado) {
        $this->IdEstado = $IdEstado;
    }

    function getIdTarifa() {
        return $this->IdTarifa;
    }

    function setIdTarifa($IdTarifa) {
        $this->IdTarifa = $IdTarifa;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getIdTicket() {
        return $this->idTicket;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    function getIdNotaTicket() {
        return $this->IdNotaTicket;
    }

    function getCobrarSiNo() {
        return $this->CobrarSiNo;
    }

    function getPagarSiNo() {
        return $this->PagarSiNo;
    }

    function getIdUsuario() {
        return $this->IdUsuario;
    }

    function getComentario() {
        return $this->Comentario;
    }

    function setIdNotaTicket($IdNotaTicket) {
        $this->IdNotaTicket = $IdNotaTicket;
    }

    function setCobrarSiNo($CobrarSiNo) {
        $this->CobrarSiNo = $CobrarSiNo;
    }

    function setPagarSiNo($PagarSiNo) {
        $this->PagarSiNo = $PagarSiNo;
    }

    function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }

    function setComentario($Comentario) {
        $this->Comentario = $Comentario;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    function getValidado() {
        return $this->Validado;
    }

    function setValidado($Validado) {
        $this->Validado = $Validado;
    }

    function getCantidadOriginal() {
        return $this->CantidadOriginal;
    }

    function setCantidadOriginal($CantidadOriginal) {
        $this->CantidadOriginal = $CantidadOriginal;
    }

    function getIdNotaTicketFacturar() {
        return $this->IdNotaTicketFacturar;
    }

    function setIdNotaTicketFacturar($IdNotaTicketFacturar) {
        $this->IdNotaTicketFacturar = $IdNotaTicketFacturar;
    }

    function getIdPartida() {
        return $this->IdPartida;
    }

    function setIdPartida($IdPartida) {
        $this->IdPartida = $IdPartida;
    }


}
