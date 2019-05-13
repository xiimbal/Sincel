<?php

include_once("Catalogo.class.php");

/**
 * Description of RangoTarifa
 *
 * @author MAGG
 */
class RangoTarifa {

    private $IdTarifa;
    private $Tarifa;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $tabla = "c_tarifarango";
    private $nombreId = "IdTarifa";
    private $RangoInicial;
    private $RangoFinal;
    private $Costo;
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `$this->tabla` WHERE $this->nombreId = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdTarifa = $rs['IdTarifa'];
            $this->Tarifa = $rs['Tarifa'];
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
        $consulta = ("INSERT INTO $this->tabla (IdTarifa,Tarifa,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,'$this->Tarifa',$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla')");        
        $catalogo = new Catalogo();
        $this->IdTarifa = $catalogo->insertarRegistro($consulta);
        if ($this->IdTarifa != NULL && $this->IdTarifa != 0) {
            return true;
        }
        return false;
    }        

    public function editRegistro() {
        $consulta = ("UPDATE $this->tabla SET Tarifa = '$this->Tarifa',
                UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',FechaUltimaModificacion = NOW(),Pantalla = '$this->Pantalla'
                WHERE IdTarifa = $this->IdTarifa;");        
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM $this->tabla WHERE $this->nombreId = " . $this->IdTarifa . ";");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function insertarDetalles($parametros){
        $numero_detalles = 0;
        
        if(isset($parametros['TotalDetalles']) && !empty($parametros['TotalDetalles'])){
            $numero_detalles = (int)$parametros['TotalDetalles'];            
        }else{
            return;
        }
        
        $id_procesado = array();
        for($i=0;$i<$numero_detalles;$i++){
            $this->RangoInicial = $parametros['r_inicial_'.$i];
            $this->RangoFinal = $parametros['r_final_'.$i];
            $this->Costo = $parametros['costo_'.$i];
            //Editamos o registramos
            if(isset($parametros['id_'.$i]) && !empty($parametros['id_'.$i])){
                $id = $parametros['id_'.$i];                
                if($this->editRegistroDetalle($id)){
                    array_push($id_procesado, $id);
                }else{
                    echo "<br/>Error: no se pudo registrar el detalle de la partida ".($i+1);
                }
            }else{
                $id = $this->newRegistroDetalle();
                if(!$id){
                    echo "<br/>Error: no se pudo registrar la foto de la partida ".($i+1);
                }else{
                    array_push($id_procesado, $id);
                }
            }
        }
        
        if(!empty($id_procesado)){
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $consulta = "DELETE FROM k_tarifarango WHERE IdTarifa = $this->IdTarifa AND IdDetalleTarifa NOT IN(".  implode(",", $id_procesado).");";
            $catalogo->obtenerLista($consulta);
        }
    }
    
    public function newRegistroDetalle() {
        $consulta = ("INSERT INTO k_tarifarango (IdDetalleTarifa,IdTarifa,RangoInicial,RangoFinal,Costo,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,$this->IdTarifa,$this->RangoInicial,$this->RangoFinal,$this->Costo,
                '$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla')");
        $catalogo = new Catalogo();
        $id  = $catalogo->insertarRegistro($consulta);
        if ($id != NULL && $id != 0) {
            return $id;
        }
        return false;
    }
    
    public function editRegistroDetalle($id) {
        $consulta = ("UPDATE k_tarifarango SET RangoInicial = $this->RangoInicial,RangoFinal = $this->RangoFinal,Costo = $this->Costo,
                UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',FechaUltimaModificacion = NOW(),Pantalla = '$this->Pantalla'
                WHERE IdDetalleTarifa = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function getIdTarifa() {
        return $this->IdTarifa;
    }

    function getTarifa() {
        return $this->Tarifa;
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

    function setIdTarifa($IdTarifa) {
        $this->IdTarifa = $IdTarifa;
    }

    function setTarifa($Tarifa) {
        $this->Tarifa = $Tarifa;
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


    function getRangoInicial() {
        return $this->RangoInicial;
    }

    function getRangoFinal() {
        return $this->RangoFinal;
    }

    function getCosto() {
        return $this->Costo;
    }

    function setRangoInicial($RangoInicial) {
        $this->RangoInicial = $RangoInicial;
    }

    function setRangoFinal($RangoFinal) {
        $this->RangoFinal = $RangoFinal;
    }

    function setCosto($Costo) {
        $this->Costo = $Costo;
    }

}
