<?php

include_once("Catalogo.class.php");

/**
 * Description of FinancialDetalle
 *
 * @author MAGG
 */
class FinancialDetalle {
    private $IdDetalleFinancial;
    private $IdFinancial;
    private $IdConcepto;
    private $Importe;
    private $Comentario;
    private $Fecha;
    private $Semana;
    private $FechaSemana;
    private $IdTicket;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;    
    private $tabla = "k_financial";
    private $nombreCampoId = "IdDetalleFinancial";
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM $this->tabla WHERE $this->nombreCampoId = " . $id . ";");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdDetalleFinancial = $rs['IdDetalleFinancial'];
            $this->IdFinancial = $rs['IdFinancial'];
            $this->IdConcepto = $rs['IdConcepto'];
            $this->Importe = $rs['Importe'];
            $this->Comentario = $rs['Comentario'];
            $this->Fecha = $rs['Fecha'];
            $this->Semana = $rs['Semana'];            
            $this->FechaSemana = $rs['FechaSemana'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    public function newRegistro(){        
        $consulta = "INSERT INTO $this->tabla(IdDetalleFinancial,IdFinancial,IdConcepto,Importe,Comentario,Fecha,Semana,FechaSemana,"
                . "UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) "
                . "VALUES(0,$this->IdFinancial,	$this->IdConcepto,$this->Importe,'$this->Comentario','$this->Fecha',$this->Semana,'$this->FechaSemana',"
                . "'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";        
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdDetalleFinancial = $catalogo->insertarRegistro($consulta);
        if ($this->IdDetalleFinancial != NULL && $this->IdDetalleFinancial != 0) {
            return true;
        }
        return false;
    }
    
    public function newRegistroFinancialTicket(){        
        $consulta = "INSERT INTO k_financialticket(IdDetalleFinancialTicket,IdDetalleFinancial,IdTicket,"
                . "UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) "
                . "VALUES(0,$this->IdDetalleFinancial,$this->IdTicket,"
                . "'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";        
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $id = $catalogo->insertarRegistro($consulta);
        if ($id != NULL && $id != 0) {
            return true;
        }
        return false;
    }
    
    public function updateRegistro(){
        $consulta = "UPDATE $this->tabla SET IdConcepto = $this->IdConcepto,Importe = $this->Importe,Comentario = '$this->Comentario',"
                . "Fecha = '$this->Fecha',Semana = $this->Semana,FechaSemana = '$this->FechaSemana',"
                . "UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',FechaUltimaModificacion = NOW(),Pantalla = '$this->Pantalla'"
                . "WHERE $this->nombreCampoId = $this->IdDetalleFinancial;";
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
    
    public function deleteRegistro(){
        $consulta = "DELETE FROM $this->tabla WHERE $this->nombreCampoId = $this->IdDetalleFinancial;";
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

    function getIdDetalleFinancial() {
        return $this->IdDetalleFinancial;
    }

    function getIdFinancial() {
        return $this->IdFinancial;
    }

    function getIdConcepto() {
        return $this->IdConcepto;
    }

    function getImporte() {
        return $this->Importe;
    }

    function getComentario() {
        return $this->Comentario;
    }

    function getFecha() {
        return $this->Fecha;
    }

    function getSemana() {
        return $this->Semana;
    }

    function getFechaSemana() {
        return $this->FechaSemana;
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

    function setIdDetalleFinancial($IdDetalleFinancial) {
        $this->IdDetalleFinancial = $IdDetalleFinancial;
    }

    function setIdFinancial($IdFinancial) {
        $this->IdFinancial = $IdFinancial;
    }

    function setIdConcepto($IdConcepto) {
        $this->IdConcepto = $IdConcepto;
    }

    function setImporte($Importe) {
        $this->Importe = $Importe;
    }

    function setComentario($Comentario) {
        $this->Comentario = $Comentario;
    }

    function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    function setSemana($Semana) {
        $this->Semana = $Semana;
    }

    function setFechaSemana($FechaSemana) {
        $this->FechaSemana = $FechaSemana;
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

    function getIdTicket() {
        return $this->IdTicket;
    }

    function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

}
