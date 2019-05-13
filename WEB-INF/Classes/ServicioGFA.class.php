<?php
include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of ServicioGFA
 *
 * @author MAGG
 */
class ServicioGFA {
    private $IdKServicioGFA;
    private $IdServicioGFA;
    private $IdAnexoClienteCC;
    private $RentaMensual;
    private $MLIncluidosBN;
    private $MLIncluidosColor;
    private $CostoMLExcedentesBN;
    private $CostoMLExcedentesColor;
    private $CostoMLProcesadosBN;
    private $CostoMLProcesadosColor;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $FechaTomaLectura;
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `k_serviciogfa` WHERE IdKServicioGFA = $id;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdKServicioGFA = $rs['IdKServicioGFA'];
            $this->IdServicioGFA = $rs['IdServicioGFA'];
            $this->IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
            $this->RentaMensual = $rs['RentaMensual'];
            $this->MLIncluidosBN = $rs['MLIncluidosBN'];
            $this->MLIncluidosColor = $rs['MLIncluidosColor'];
            $this->CostoMLExcedentesBN = $rs['CostoMLExcedentesBN'];
            $this->CostoMLExcedentesColor = $rs['CostoMLExcedentesColor'];
            $this->CostoMLProcesadosBN = $rs['CostoMLProcesadosBN'];
            $this->CostoMLProcesadosColor = $rs['CostoMLProcesadosColor'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->FechaTomaLectura = $rs['FechaTomaLectura'];
            return true;
        }
        return false;
    }
    
    public function newRegistro(){        
        $consulta = "INSERT INTO k_serviciogfa(IdKServicioGFA, IdServicioGFA, IdAnexoClienteCC, RentaMensual, MLIncluidosBN, MLIncluidosColor, 
            CostoMLExcedentesBN, CostoMLExcedentesColor, CostoMLProcesadosBN, CostoMLProcesadosColor,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) VALUES(0,$this->IdServicioGFA,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->MLIncluidosBN,$this->MLIncluidosColor,$this->CostoMLExcedentesBN,$this->CostoMLExcedentesColor,$this->CostoMLProcesadosBN,
            $this->CostoMLProcesadosColor,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";
        $catalogo = new Catalogo();        $this->IdKServicioGFA = $catalogo->insertarRegistro($consulta);
        if ($this->IdKServicioGFA!=NULL && $this->IdKServicioGFA!=0) { 
            $consulta = "INSERT INTO k_serviciohistoricogfa(IdKServicioHistoricoGFA,IdKServicioGFA, IdServicioGFA, IdAnexoClienteCC, RentaMensual, MLIncluidosBN, MLIncluidosColor, 
            CostoMLExcedentesBN, CostoMLExcedentesColor, CostoMLProcesadosBN, CostoMLProcesadosColor,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) VALUES(0,$this->IdKServicioGFA,$this->IdServicioGFA,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->MLIncluidosBN,$this->MLIncluidosColor,$this->CostoMLExcedentesBN,$this->CostoMLExcedentesColor,$this->CostoMLProcesadosBN,
            $this->CostoMLProcesadosColor,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";
            $catalogo->insertarRegistro($consulta);
            return true;
        }        
        return false;
    }
    
    public function editRegistro(){        
        $consulta = "UPDATE k_serviciogfa SET IdServicioGFA=$this->IdServicioGFA, 
            RentaMensual = $this->RentaMensual, MLIncluidosBN = $this->MLIncluidosBN, MLIncluidosColor =  $this->MLIncluidosColor,
            CostoMLExcedentesBN = $this->CostoMLExcedentesBN, CostoMLExcedentesColor = $this->CostoMLExcedentesColor,
            CostoMLProcesadosBN = $this->CostoMLProcesadosBN, CostoMLProcesadosColor = $this->CostoMLProcesadosColor,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla'
            WHERE IdKServicioGFA = $this->IdKServicioGFA;";                
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
             $consulta = "INSERT INTO k_serviciohistoricogfa(IdKServicioHistoricoGFA,IdKServicioGFA, IdServicioGFA, IdAnexoClienteCC, RentaMensual, MLIncluidosBN, MLIncluidosColor, 
            CostoMLExcedentesBN, CostoMLExcedentesColor, CostoMLProcesadosBN, CostoMLProcesadosColor,UsuarioCreacion,FechaCreacion, 
            UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) VALUES(0,$this->IdKServicioGFA,$this->IdServicioGFA,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->MLIncluidosBN,$this->MLIncluidosColor,$this->CostoMLExcedentesBN,$this->CostoMLExcedentesColor,$this->CostoMLProcesadosBN,
            $this->CostoMLProcesadosColor,'$this->UsuarioUltimaModificacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";
            $catalogo->insertarRegistro($consulta);
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene los equipos que tienen el mismo IdAnexoClienteCC y mismo servicio que el servicio especificado.
     * @param type $idKAnexo
     * @return type
     */
    public function getEquiposByIdKAnexo($idKAnexo){
        $consulta = "SELECT cie.NoSerie, kacc.ClaveAnexoTecnico, e.Modelo,
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN CONCAT(c.NombreRazonSocial,' - ',cc.Nombre) ELSE CONCAT(c2.NombreRazonSocial,' - ',cc2.Nombre) END) AS Cliente  
        FROM `k_serviciogfa` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioGFA OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC)) AND cie.ClaveEspKServicioFAIM = kim.IdServicioGFA
        INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cie.IdKserviciogimgfa
        LEFT JOIN k_anexoclientecc AS kacc2 ON kacc2.IdAnexoClienteCC = cie.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc2.CveEspClienteCC
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
        WHERE IdKServicioGFA = $idKAnexo AND b.Activo = 1 AND b.VentaDirecta = 0 ORDER BY Cliente, NoSerie DESC;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    /**
     * Obtiene los equipos que tienen el mismo IdAnexoClienteCC y mismo servicio que el servicio especificado.
     * @param type $idKAnexo
     * @return type
     */
    public function getEquiposByIdKAnexoFiltroCC($idKAnexo, $localidad){
        $consulta = "SELECT cie.NoSerie, kacc.ClaveAnexoTecnico, e.Modelo,
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE cc2.ClaveCentroCosto END) AS ClaveCentroCosto,
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN CONCAT(c.NombreRazonSocial,' - ',cc.Nombre) ELSE CONCAT(c2.NombreRazonSocial,' - ',cc2.Nombre) END) AS Cliente  
        FROM `k_serviciogfa` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioGFA OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC)) AND cie.ClaveEspKServicioFAIM = kim.IdServicioGFA
        INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cie.IdKserviciogimgfa
        LEFT JOIN k_anexoclientecc AS kacc2 ON kacc2.IdAnexoClienteCC = cie.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc2.CveEspClienteCC
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
        WHERE IdKServicioGFA = $idKAnexo AND b.Activo = 1 AND b.VentaDirecta = 0 HAVING ClaveCentroCosto = '$localidad' ORDER BY Cliente, NoSerie DESC;";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function deleteRegistro($idKServicio){
        $catalogo = new Catalogo();
        $consulta1 = "DELETE FROM k_serviciohistoricogfa WHERE IdKServicioGFA = $idKServicio;";
        $catalogo->obtenerLista($consulta1);
        
        $consulta = "DELETE FROM k_serviciogfa WHERE IdKServicioGFA = $idKServicio;";        
         $query = $catalogo->obtenerLista($consulta);
        if($query == "1"){
            return true;
        }
        return false;
    }
    
    /**
     * Obtiene los servicios particulares de formato amplio por el anexo especificado.
     * @param type $claveAnexo clave del anexo
     * @return type resultset con los servicios del anexo.
     */
    public function getServiciosByAnexo($claveAnexo){
        $consulta = "SELECT kim.IdKServicioGFA, cim.IdServicioGFA, cim.Nombre AS servicio,
        (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, 
        kim.RentaMensual, kim.MLIncluidosBN, kim.MLIncluidosColor, kim.CostoMLProcesadosBN, kim.CostoMLProcesadosColor,
	kim.CostoMLExcedentesBN, kim.CostoMLExcedentesColor, 
        carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor,
        kim.UsuarioUltimaModificacion, kim.FechaUltimaModificacion
        FROM `k_serviciogfa` AS kim 
        INNER JOIN k_anexoclientecc AS kacc ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC 
        INNER JOIN c_serviciogfa AS cim ON kim.IdServicioGFA = cim.IdServicioGFA
        LEFT JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento 
        WHERE kacc.ClaveAnexoTecnico = '$claveAnexo' AND cim.Activo = 1 AND carr.Activo = 1;";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getHistoricoServicios($claveServicio){
        $consulta = "SELECT kim.IdKServicioGFA, cim.IdServicioGFA, cim.Nombre AS servicio,
        (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, 
        kim.RentaMensual, kim.MLIncluidosBN, kim.MLIncluidosColor, kim.CostoMLProcesadosBN, kim.CostoMLProcesadosColor,
	kim.CostoMLExcedentesBN, kim.CostoMLExcedentesColor, 
        carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor,
        kim.UsuarioUltimaModificacion, kim.FechaUltimaModificacion
        FROM `k_serviciohistoricogfa` AS kim 
        INNER JOIN k_anexoclientecc AS kacc ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC 
        INNER JOIN c_serviciogfa AS cim ON kim.IdServicioGFA = cim.IdServicioGFA
        LEFT JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento 
        WHERE kim.IdKServicioGFA = '$claveServicio' AND cim.Activo = 1 AND carr.Activo = 1 ORDER BY kim.FechaUltimaModificacion DESC;";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getNoSerieUpdate($idkServicio) {
        $arrayNoSerie = Array();
        $consulta = "SELECT cie.NoSerie FROM `k_serviciogfa` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioGFA OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC)) 
        AND cie.ClaveEspKServicioFAIM = kim.IdServicioGFA INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        WHERE IdKServicioGFA = $idkServicio";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        $x = 0;
        while ($rs = mysql_fetch_array($query)) {
            $arrayNoSerie[$x] = $rs['NoSerie'];
            $x++;
        }
        return $arrayNoSerie;
    }

    public function editInvetarioEquipo($array, $servicio) {        
        $consulta = "UPDATE c_inventarioequipo SET ClaveEspKServicioFAIM='$servicio' WHERE NoSerie IN ($array)";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function getIdKServicioGFA() {
        return $this->IdKServicioGFA;
    }

    public function setIdKServicioGFA($IdKServicioGFA) {
        $this->IdKServicioGFA = $IdKServicioGFA;
    }

    public function getIdServicioGFA() {
        return $this->IdServicioGFA;
    }

    public function setIdServicioGFA($IdServicioGFA) {
        $this->IdServicioGFA = $IdServicioGFA;
    }

    public function getIdAnexoClienteCC() {
        return $this->IdAnexoClienteCC;
    }

    public function setIdAnexoClienteCC($IdAnexoClienteCC) {
        $this->IdAnexoClienteCC = $IdAnexoClienteCC;
    }

    public function getRentaMensual() {
        return $this->RentaMensual;
    }

    public function setRentaMensual($RentaMensual) {
        $this->RentaMensual = $RentaMensual;
    }

    public function getMLIncluidosBN() {
        return $this->MLIncluidosBN;
    }

    public function setMLIncluidosBN($MLIncluidosBN) {
        $this->MLIncluidosBN = $MLIncluidosBN;
    }

    public function getMLIncluidosColor() {
        return $this->MLIncluidosColor;
    }

    public function setMLIncluidosColor($MLIncluidosColor) {
        $this->MLIncluidosColor = $MLIncluidosColor;
    }

    public function getCostoMLExcedentesBN() {
        return $this->CostoMLExcedentesBN;
    }

    public function setCostoMLExcedentesBN($CostoMLExcedentesBN) {
        $this->CostoMLExcedentesBN = $CostoMLExcedentesBN;
    }

    public function getCostoMLExcedentesColor() {
        return $this->CostoMLExcedentesColor;
    }

    public function setCostoMLExcedentesColor($CostoMLExcedentesColor) {
        $this->CostoMLExcedentesColor = $CostoMLExcedentesColor;
    }

    public function getCostoMLProcesadosBN() {
        return $this->CostoMLProcesadosBN;
    }

    public function setCostoMLProcesadosBN($CostoMLProcesadosBN) {
        $this->CostoMLProcesadosBN = $CostoMLProcesadosBN;
    }

    public function getCostoMLProcesadosColor() {
        return $this->CostoMLProcesadosColor;
    }

    public function setCostoMLProcesadosColor($CostoMLProcesadosColor) {
        $this->CostoMLProcesadosColor = $CostoMLProcesadosColor;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getFechaTomaLectura() {
        return $this->FechaTomaLectura;
    }

    public function setFechaTomaLectura($FechaTomaLectura) {
        $this->FechaTomaLectura = $FechaTomaLectura;
    }
}

?>
