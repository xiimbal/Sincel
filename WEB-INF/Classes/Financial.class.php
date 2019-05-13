<?php

include_once("Catalogo.class.php");
include_once("FinancialDetalle.class.php");

/**
 * Description of Financial
 *
 * @author MAGG
 */
class Financial {
    private $IdPrestamo;
    private $Fecha;
    private $IdOperador;
    private $Comentario;
    private $Semana;
    private $FechaSemana;
    private $IdEstatus;
    private $IdTipoRetencion;
    private $PorcentajeInteres;
    private $Total = 0;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $tabla = "c_financial";
    private $nombreCampoId = "IdPrestamo";
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM $this->tabla WHERE $this->nombreCampoId = " . $id . ";");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdPrestamo = $rs['IdPrestamo'];
            $this->Fecha = $rs['Fecha'];
            $this->IdOperador = $rs['IdOperador'];
            $this->Comentario = $rs['Comentario'];
            $this->Semana = $rs['Semana'];
            $this->FechaSemana = $rs['FechaSemana'];
            $this->IdEstatus = $rs['IdEstatus'];
            $this->IdTipoRetencion = $rs['IdTipoRetencion'];
            $this->PorcentajeInteres = $rs['PorcentajeInteres'];
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
    
    function getRegistroByUsuario($IdOperador){
        $consulta = "SELECT * FROM $this->tabla WHERE IdOperador = $IdOperador AND IdEstatus <> 2 ORDER BY Fecha ASC;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdPrestamo = $rs['IdPrestamo'];
            $this->Fecha = $rs['Fecha'];
            $this->IdOperador = $rs['IdOperador'];
            $this->Comentario = $rs['Comentario'];
            $this->Semana = $rs['Semana'];
            $this->FechaSemana = $rs['FechaSemana'];
            $this->IdEstatus = $rs['IdEstatus'];
            $this->IdTipoRetencion = $rs['IdTipoRetencion'];
            $this->PorcentajeInteres = $rs['PorcentajeInteres'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->Total = 0;
            $consulta = "SELECT f.IdPrestamo, f.IdOperador, SUM(CASE WHEN cf.IdTipo = 2 THEN kf.Importe ELSE -kf.Importe END) AS Total 
                FROM `c_financial` AS f
                LEFT JOIN k_financial AS kf ON kf.IdFinancial = f.IdPrestamo
                LEFT JOIN c_conceptofinancial AS cf ON cf.IdConcepto = kf.IdConcepto
                WHERE f.IdPrestamo = $this->IdPrestamo
                GROUP BY f.IdPrestamo;";
            $result1 = $catalogo->obtenerLista($consulta);
            while($rs1 = mysql_fetch_array($result1)){
                $this->Total = $rs1['Total'];
            }
            return true;
        }
        return false;
    }
    
    public function newRegistro(){        
        $consulta = "INSERT INTO $this->tabla(IdPrestamo,Fecha,IdOperador,Comentario,Semana,FechaSemana,IdEstatus,IdTipoRetencion,PorcentajeInteres,"
                . "Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) "
                . "VALUES(0,'$this->Fecha',$this->IdOperador,'$this->Comentario',$this->Semana,'$this->FechaSemana',$this->IdEstatus,"
                . "$this->IdTipoRetencion,$this->PorcentajeInteres,"
                . "$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
        
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdPrestamo = $catalogo->insertarRegistro($consulta);
        if ($this->IdPrestamo != NULL && $this->IdPrestamo != 0) {
            return true;
        }
        return false;
    }
    
    public function updateRegistro(){
        $consulta = "UPDATE $this->tabla SET Fecha = '$this->Fecha',IdOperador = $this->IdOperador,Comentario = '$this->Comentario',Semana = $this->Semana,"
                . "FechaSemana = '$this->FechaSemana',IdEstatus = $this->IdEstatus,IdTipoRetencion = $this->IdTipoRetencion,PorcentajeInteres=$this->PorcentajeInteres,"
                . "Activo = $this->Activo,UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',FechaUltimaModificacion = NOW(),Pantalla = '$this->Pantalla'"
                . "WHERE $this->nombreCampoId = $this->IdPrestamo;";
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
        $consulta = "DELETE FROM $this->tabla WHERE $this->nombreCampoId = $this->IdPrestamo;";
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
    
    public function isFinancialPagado(){
        $consulta = "SELECT f.IdPrestamo, f.IdOperador, SUM(CASE WHEN cf.IdTipo = 2 THEN kf.Importe ELSE -kf.Importe END) AS Total 
            FROM `c_financial` AS f
            LEFT JOIN k_financial AS kf ON kf.IdFinancial = f.IdPrestamo
            LEFT JOIN c_conceptofinancial AS cf ON cf.IdConcepto = kf.IdConcepto
            WHERE f.IdPrestamo = $this->IdPrestamo
            GROUP BY f.IdPrestamo;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        
        while($rs = mysql_fetch_array($result)){            
            if( ((float)$rs['Total']) > 0){
                return false;
            }else{
                return true;
            }
        }
                
        return false;
    }
    
    public function insertarDetalles($parametros){
        $detalle = new FinancialDetalle();
        
        $numero_detalles = 0;        
        if(isset($parametros['TotalDetalles']) && !empty($parametros['TotalDetalles'])){
            $numero_detalles = (int)$parametros['TotalDetalles'];            
        }else{
            return;
        }
        
        $id_procesado = array();
        for($i=0;$i<$numero_detalles;$i++){
            if(empty($parametros['concepto_'.$i]) || empty($parametros['concepto_'.$i])){
                continue;
            }
            
            $detalle->setIdFinancial($this->IdPrestamo);
            $detalle->setIdConcepto($parametros['concepto_'.$i]);
            $detalle->setImporte($parametros['monto_'.$i]);
            $detalle->setComentario($parametros['comentario_'.$i]);
            $detalle->setFecha($parametros['fecha_'.$i]);
            /*$detalle->setSemana($parametros['Semana'.$i]);
            $detalle->setFechaSemana($parametros['FechaSemana'.$i]);*/
            $date = new DateTime($detalle->getFecha());
            $week = $date->format("W");
            $last_monday = date('Y-m-d', strtotime('previous monday', strtotime($detalle->getFecha())) );   
            $detalle->setSemana($week);
            $detalle->setFechaSemana($last_monday);
            $detalle->setUsuarioCreacion($this->UsuarioCreacion);            
            $detalle->setUsuarioUltimaModificacion($this->UsuarioUltimaModificacion);            
            $detalle->setPantalla($this->Pantalla);

            //Editamos o registramos
            if(isset($parametros['id_'.$i]) && !empty($parametros['id_'.$i])){
                $id = $parametros['id_'.$i];
                $detalle->setIdDetalleFinancial($id);
                if($detalle->updateRegistro()){
                    array_push($id_procesado, $id);
                }else{
                    echo "<br/>Error: no se pudo editar el detalle de la partida ".($i+1);
                }
            }else{                
                if($detalle->newRegistro()){
                    array_push($id_procesado, $detalle->getIdDetalleFinancial());                    
                }else{
                    echo "<br/>Error: no se pudo registrar el detalle de la partida ".($i+1);
                }
            }
        }
        
        if(!empty($id_procesado)){
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $consulta = "DELETE FROM k_financial WHERE IdFinancial = $this->IdPrestamo AND IdDetalleFinancial NOT IN(".  implode(",", $id_procesado).");";
            $catalogo->obtenerLista($consulta);
        }
        
        $obj2 = new Financial();
        if($obj2->getRegistroById($this->IdPrestamo) && $obj2->getIdEstatus() == 1 && $obj2->isFinancialPagado()){
            $obj2->setIdEstatus(2);
            if($obj2->updateRegistro()){
                echo "<br/>El registro fue marcada como cerrado, se ha cubierto totalmente el monto prestado<br/>";
            }else{
                echo "<br/>No se pudo cerrar el registro de forma automática<br/>";
            }
        }else if($obj2->getIdEstatus() == 2 && !$obj2->isFinancialPagado()){
            $obj2->setIdEstatus(1);
            if($obj2->updateRegistro()){
                echo "<br/>El registro fue marcada como abierto, no se ha cubierto el monto prestado<br/>";
            }else{
                echo "<br/>No se pudo abrir el registro de forma automática<br/>";
            }
        }
    }
       
            
    function getIdPrestamo() {
        return $this->IdPrestamo;
    }

    function getFecha() {
        return $this->Fecha;
    }

    function getIdOperador() {
        return $this->IdOperador;
    }

    function getComentario() {
        return $this->Comentario;
    }

    function getSemana() {
        return $this->Semana;
    }

    function getFechaSemana() {
        return $this->FechaSemana;
    }

    function getIdEstatus() {
        return $this->IdEstatus;
    }

    function getIdTipoRetencion() {
        return $this->IdTipoRetencion;
    }

    function getActivo() {
        return $this->Activo;
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

    function setIdPrestamo($IdPrestamo) {
        $this->IdPrestamo = $IdPrestamo;
    }

    function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    function setIdOperador($IdOperador) {
        $this->IdOperador = $IdOperador;
    }

    function setComentario($Comentario) {
        $this->Comentario = $Comentario;
    }

    function setSemana($Semana) {
        $this->Semana = $Semana;
    }

    function setFechaSemana($FechaSemana) {
        $this->FechaSemana = $FechaSemana;
    }

    function setIdEstatus($IdEstatus) {
        $this->IdEstatus = $IdEstatus;
    }

    function setIdTipoRetencion($IdTipoRetencion) {
        $this->IdTipoRetencion = $IdTipoRetencion;
    }

    function setActivo($Activo) {
        $this->Activo = $Activo;
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

    function getPorcentajeInteres() {
        return $this->PorcentajeInteres;
    }

    function setPorcentajeInteres($PorcentajeInteres) {
        $this->PorcentajeInteres = $PorcentajeInteres;
    }
    
    function getTotal() {
        return $this->Total;
    }

    function setTotal($Total) {
        $this->Total = $Total;
    }
}
