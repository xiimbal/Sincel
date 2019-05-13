<?php

include_once("Catalogo.class.php");

/**
 * Description of PeriodoSinFacturar
 *
 * @author MAGG
 */
class PeriodoSinFacturar {
    private $IdPeriodo;
    private $Periodo;
    private $empresa;
    
    public function getRegistroById($id){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT IdPeriodo, Periodo FROM `c_periodo` WHERE IdPeriodo = $id;";
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $this->IdPeriodo = $rs['IdPeriodo'];
            $this->Periodo = $rs['Periodo'];
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene los clientes con fecha de corte el dia de la fecha recibida
     * @param type $fecha Fecha para obtener la fecha de corte.
     * @return type resultset con los datos del cliente
     */
    public function clientesConFechaCorte($fecha){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.IdTipoFacturacion, ctt.NoContrato, cat.ClaveAnexoTecnico, kacc.IdAnexoClienteCC, kacc.Fecha, DAY(kacc.Fecha) AS DiaCorte,
            CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoPaterno) AS EjecutivoCuenta, u.correo,
            (CASE WHEN !ISNULL(u.IdUsuario) THEN u.IdUsuario ELSE 'NA' END) AS IdUsuario
            FROM c_cliente AS c
            LEFT JOIN c_usuario AS u ON c.EjecutivoCuenta = u.IdUsuario
            LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = (SELECT MIN(NoContrato) FROM c_contrato WHERE ClaveCliente = c.ClaveCliente AND ctt.Activo = 1 AND ctt.FechaTermino >= NOW())
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = (SELECT MIN(ClaveAnexoTecnico) FROM c_anexotecnico WHERE NoContrato = ctt.NoContrato)
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = cat.ClaveAnexoTecnico)
            WHERE c.Activo = 1 AND DAY(kacc.Fecha) = DAY('$fecha')
            ORDER BY EjecutivoCuenta,c.NombreRazonSocial, ctt.NoContrato, cat.ClaveAnexoTecnico, kacc.IdAnexoClienteCC;";
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    /**
     * Inserta un nuevo periodo en caso de que este no exista.
     * @param type $mes Mes del periodo
     * @param type $anio Anio del periodo
     * @param type $usuario Usuario de creación
     * @param type $pantalla Pantalla de creación
     * @return boolean
     */
    public function insertarNuevoPeriodo($mes, $anio, $usuario, $pantalla){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT IdPeriodo FROM `c_periodo` WHERE MONTH(Periodo) = '$mes' AND YEAR(Periodo)='$anio';";
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) > 0){//El periodo ya existe
            while($rs = mysql_fetch_array($result)){
                $this->IdPeriodo = $rs['IdPeriodo'];
                $this->Periodo = "$anio-$mes-01";
                return true;
            }            
        }else{//No existe aun el periodo            
            $consulta = "INSERT INTO c_periodo
                (IdPeriodo, Periodo, Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
                VALUES(0,'$anio-$mes-01',1,'$usuario',NOW(),'$usuario',NOW(),'$pantalla');";
            $this->IdPeriodo = $catalogo->insertarRegistro($consulta);
            if($this->IdPeriodo != NULL && $this->IdPeriodo !=0){     
                $this->Periodo = "$anio-$mes-01";
                return true;
            }
            return false;
        }        
    }
    
    public function insertarEquipoSinFacturar($idPeriodo, $idBitacora, $ClaveCliente, $ColorBN, $IdServicio, $idKServicio, $Renta, $IncluidosBN, 
            $IncluidosColor, $CostoProcesadoBN, $CostoProcesadoColor, $CostoExcedenteBN, $CostoExcedenteColor, $Usuario, $Pantalla){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT IdPeriodo, IdBitacora FROM k_equiposporfacturar WHERE IdPeriodo = $idPeriodo AND IdBitacora = $idBitacora;";        
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) == 0){
            $consulta = "INSERT INTO k_equiposporfacturar(IdPeriodo, IdBitacora, ClaveCliente, ColorBN, IdServicio, IdKServicio, RentaMensual, IncluidasBN, 
                IncluidasColor, CostoExcedentesBN, CostoExcedentesColor, CostoProcesadaBN, CostoProcesadaColor,UsuarioCreacion,FechaCreacion,
                UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                VALUES($idPeriodo, $idBitacora, '$ClaveCliente', $ColorBN, $IdServicio, $idKServicio, $Renta, $IncluidosBN, $IncluidosColor,
                    $CostoExcedenteBN, $CostoExcedenteColor, $CostoProcesadoBN, $CostoProcesadoColor,'$Usuario',NOW(),'$Usuario',NOW(),'$Pantalla');";
            $query = $catalogo->obtenerLista($consulta);
            if($query == "1"){
                return true;
            }else{
                //echo "<br/>$consulta<br/>";
                return false;
            }
        }else{
            return false;
        }
    }
    
    public function marcarFacturado($periodo, $bitacora, $cliente, $comentario, $usuario, $pantalla){
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $where = "WHERE IdPeriodo = $periodo AND IdBitacora = $bitacora AND ClaveCliente = '$cliente';";
        $consulta = "SELECT * FROM k_equiposporfacturar $where";        
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            if(isset($rs['RentaMensual'])){
                $consulta = "UPDATE k_equiposporfacturar SET RentaMensualFacturado = 1 $where;";
                $catalogo->obtenerLista($consulta);
            }
            if(isset($rs['IncluidasBN'])){
                $consulta = "UPDATE k_equiposporfacturar SET IncluidasBNFacturado = 1 $where;";
                $catalogo->obtenerLista($consulta);
            }
            if(isset($rs['IncluidasColor'])){
                $consulta = "UPDATE k_equiposporfacturar SET IncluidasColorFacturado = 1 $where;";
                $catalogo->obtenerLista($consulta);
            }
            if(isset($rs['CostoExcedentesBN'])){
                $consulta = "UPDATE k_equiposporfacturar SET CostoExcedentesBNFacturado = 1 $where;";
                $catalogo->obtenerLista($consulta);
            }
            if(isset($rs['CostoExcedentesColor'])){
                $consulta = "UPDATE k_equiposporfacturar SET CostoExcedentesColorFacturado = 1 $where;";
                $catalogo->obtenerLista($consulta);
            }
            if(isset($rs['CostoProcesadaBN'])){
                $consulta = "UPDATE k_equiposporfacturar SET CostoProcesadaBNFacturado = 1 $where;";
                $catalogo->obtenerLista($consulta);
            }
            if(isset($rs['CostoProcesadaColor'])){
                $consulta = "UPDATE k_equiposporfacturar SET CostoProcesadaColorFacturado = 1 $where;";
                $catalogo->obtenerLista($consulta);
            }
            
            $consulta = "UPDATE k_equiposporfacturar SET EquipoFacturado = 1, Comentario = '$comentario', FechaUltimaModificacion = NOW(), 
                UsuarioUltimaModificacion = '$usuario', Pantalla = '$pantalla' $where;";
            $result = $catalogo->obtenerLista($consulta);
            if($result == "1"){
                return true;
            }
        }
        return false;
    }
    
    public function getIdPeriodo() {
        return $this->IdPeriodo;
    }

    public function setIdPeriodo($IdPeriodo) {
        $this->IdPeriodo = $IdPeriodo;
    }
    
    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getPeriodo() {
        return $this->Periodo;
    }

    public function setPeriodo($Periodo) {
        $this->Periodo = $Periodo;
    }
}

?>
