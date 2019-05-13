<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of ServicioGIM
 *
 * @author MAGG
 */
class KServicioGIM {

    private $IdKServicioGIM;
    private $IdServicioGIM;
    private $IdAnexoClienteCC;
    private $RentaMensual;
    private $PaginasIncluidasBN;
    private $PaginasIncluidasColor;
    private $CostoPaginasExcedentesBN;
    private $CostoPaginasExcedentesColor;
    private $CostoPaginaProcesadaBN;
    private $CostoPaginaProcesadaColor;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `k_serviciogim` WHERE IdKServicioGIM = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdKServicioGIM = $rs['IdKServicioGIM'];
            $this->IdServicioGIM = $rs['IdServicioGIM'];
            $this->IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
            $this->RentaMensual = $rs['RentaMensual'];
            $this->PaginasIncluidasBN = $rs['PaginasIncluidasBN'];
            $this->PaginasIncluidasColor = $rs['PaginasIncluidasColor'];
            $this->CostoPaginasExcedentesBN = $rs['CostoPaginasExcedentesBN'];
            $this->CostoPaginasExcedentesColor = $rs['CostoPaginasExcedentesColor'];
            $this->CostoPaginaProcesadaBN = $rs['CostoPaginaProcesadaBN'];
            $this->CostoPaginaProcesadaColor = $rs['CostoPaginaProcesadaColor'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    /**
     * Obtiene los servicios particulares de impresion por el anexo especificado.
     * @param type $claveAnexo clave del anexo
     * @return type resultset con los servicios del anexo.
     */
    public function getServiciosByAnexo($claveAnexo) {
        $consulta = "SELECT kim.IdKServicioGIM, cim.IdServicioGIM, cim.Nombre AS servicio,
        (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, 
        kim.RentaMensual, kim.PaginasIncluidasBN, kim.PaginasIncluidasColor, kim.CostoPaginaProcesadaBN, 
        kim.CostoPaginaProcesadaColor, kim.CostoPaginasExcedentesBN, kim.CostoPaginasExcedentesColor, 
        carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor,
        kim.UsuarioUltimaModificacion, kim.FechaUltimaModificacion
        FROM `k_serviciogim` AS kim 
        INNER JOIN k_anexoclientecc AS kacc ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC 
        INNER JOIN c_serviciogim AS cim ON kim.IdServicioGIM = cim.IdServicioGIM 
        LEFT JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento 
        WHERE kacc.ClaveAnexoTecnico = '$claveAnexo' AND cim.Activo = 1 AND carr.Activo = 1;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getHistoricoServicios($claveServicio) {
        $consulta = "SELECT kim.IdKServicioGIM, cim.IdServicioGIM, cim.Nombre AS servicio,
        (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, 
        kim.RentaMensual, kim.PaginasIncluidasBN, kim.PaginasIncluidasColor, kim.CostoPaginaProcesadaBN, 
        kim.CostoPaginaProcesadaColor, kim.CostoPaginasExcedentesBN, kim.CostoPaginasExcedentesColor, 
        carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor,
        kim.UsuarioUltimaModificacion, kim.FechaUltimaModificacion
        FROM `k_serviciohistoricogim` AS kim 
        INNER JOIN k_anexoclientecc AS kacc ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC 
        INNER JOIN c_serviciogim AS cim ON kim.IdServicioGIM = cim.IdServicioGIM 
        LEFT JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento 
        WHERE kim.IdKServicioGIM = '$claveServicio' AND cim.Activo = 1 AND carr.Activo = 1 ORDER BY kim.FechaUltimaModificacion DESC;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene los equipos que tienen el mismo IdAnexoClienteCC y mismo servicio que el servicio especificado.
     * @param type $idKAnexo
     * @return type
     */
    public function getEquiposByIdKAnexo($idKAnexo) {
        $consulta = "SELECT cie.NoSerie, kacc.ClaveAnexoTecnico, e.Modelo,
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN CONCAT(c.NombreRazonSocial,' - ',cc.Nombre) ELSE CONCAT(c2.NombreRazonSocial,' - ',cc2.Nombre) END) AS Cliente    
        FROM `k_serviciogim` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioGIM OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC))
        AND cie.ClaveEspKServicioFAIM = kim.IdServicioGIM
        INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cie.IdKserviciogimgfa
        LEFT JOIN k_anexoclientecc AS kacc2 ON kacc2.IdAnexoClienteCC = cie.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc2.CveEspClienteCC
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
        WHERE IdKServicioGIM = $idKAnexo AND b.Activo = 1 AND b.VentaDirecta = 0 ORDER BY NoSerie DESC;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene los equipos que tienen el mismo IdAnexoClienteCC y mismo servicio que el servicio especificado.
     * @param type $idKAnexo
     * @return type
     */
    public function getEquiposByIdKAnexoFiltroCC($idKAnexo, $localidad) {
        $consulta = "SELECT cie.NoSerie, kacc.ClaveAnexoTecnico, e.Modelo,
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE cc2.ClaveCentroCosto END) AS ClaveCentroCosto,
        (CASE WHEN ISNULL(ks.ClaveCentroCosto) THEN CONCAT(c.NombreRazonSocial,' - ',cc.Nombre) ELSE CONCAT(c2.NombreRazonSocial,' - ',cc2.Nombre) END) AS Cliente    
        FROM `k_serviciogim` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioGIM OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC))
        AND cie.ClaveEspKServicioFAIM = kim.IdServicioGIM
        INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cie.IdKserviciogimgfa
        LEFT JOIN k_anexoclientecc AS kacc2 ON kacc2.IdAnexoClienteCC = cie.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc2.CveEspClienteCC
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
        WHERE IdKServicioGIM = $idKAnexo AND b.Activo = 1 AND b.VentaDirecta = 0 HAVING ClaveCentroCosto = '$localidad' ORDER BY NoSerie DESC;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function deleteRegistro($idKServicio) {
        $catalogo = new Catalogo();
        $consulta1 = "DELETE FROM k_serviciohistoricogim WHERE IdKServicioGIM = $idKServicio;";
            $catalogo->obtenerLista($consulta1);
        $consulta = "DELETE FROM k_serviciogim WHERE IdKServicioGIM = $idKServicio;";
        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    public function getRegistrosByIdAnexo($idAnexo) {
        $consulta = "SELECT * FROM `k_serviciogim` WHERE IdAnexoClienteCC = $idAnexo;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {
        $consulta = "INSERT INTO k_serviciogim(IdKServicioGIM, IdServicioGIM, IdAnexoClienteCC, RentaMensual, PaginasIncluidasBN, PaginasIncluidasColor,
            CostoPaginasExcedentesBN, CostoPaginasExcedentesColor, CostoPaginaProcesadaBN, CostoPaginaProcesadaColor, UsuarioCreacion, 
            FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES(0,$this->IdServicioGIM,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->PaginasIncluidasBN,$this->PaginasIncluidasColor,$this->CostoPaginasExcedentesBN,$this->CostoPaginasExcedentesColor,
            $this->CostoPaginaProcesadaBN,$this->CostoPaginaProcesadaColor,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla');";
        $catalogo = new Catalogo();
        $this->IdKServicioGIM = $catalogo->insertarRegistro($consulta);
        if ($this->IdKServicioGIM != NULL && $this->IdKServicioGIM != 0) {
             $consulta = "INSERT INTO k_serviciohistoricogim(IdKServicioHistoricoGIM,IdKServicioGIM, IdServicioGIM, IdAnexoClienteCC, RentaMensual, PaginasIncluidasBN, PaginasIncluidasColor,
            CostoPaginasExcedentesBN, CostoPaginasExcedentesColor, CostoPaginaProcesadaBN, CostoPaginaProcesadaColor, UsuarioCreacion, 
            FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES(0,$this->IdKServicioGIM,$this->IdServicioGIM,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->PaginasIncluidasBN,$this->PaginasIncluidasColor,$this->CostoPaginasExcedentesBN,$this->CostoPaginasExcedentesColor,
            $this->CostoPaginaProcesadaBN,$this->CostoPaginaProcesadaColor,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla');";
             $catalogo->insertarRegistro($consulta);
            return true;
        }
        return false;
    }

    /**
     * Actualiza el arrendamiento (esquema) en el servicio GIM especificado.
     * @param type $idArrendamiento id del arrendamiento que se va a asignar
     * @param type $idServicio id del servicio GIM a actualizar
     * @return boolean true en caso de haber actualizado, false en caso contrario.
     */
    public function actualizarArrendamiento($idArrendamiento, $idServicio) {
        $consulta = "UPDATE `c_serviciogim` SET IdArrendamiento = $idArrendamiento WHERE IdServicioGIM = $idServicio;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene los servicios de impresion globales con su Esquema (Si ocupa Renta mensual, Pag. Incluidas, etc.)
     * @return type ResultSet con el resultado del query.
     */
    public function obtenerEsquemas() {
        $consulta = "SELECT car.rentaMensual, car.IncluidoBN, car.IncluidoColor, car.ExcedentesBN, 
        car.ExcedentesColor, car.CostoProcesadaBN, car.CostoProcesadaColor , cim.IdServicioGIM, cim.Nombre, car.IdArrendamiento
        FROM c_serviciogim AS cim
        INNER JOIN c_arrendamiento AS car ON car.IdArrendamiento = cim.IdArrendamiento
        WHERE cim.Activo = 1 AND car.Activo = 1
        ORDER BY cim.IdServicioGIM;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene los esquemas y los servicios activos (particulares) del anexo y localidad especificado, el postfijo puede ser IM, FA dependiendo del tipo de servicio que se deseee.
     * @param type $ClaveAnexo Clave del anexo tecnico
     * @param type $CC clave de la localidad.
     * @param type $postfijo puede ser IM o FA para otro el query se regresa null;
     * @return type ResultSet con los datos de los esquemas y servicios.
     */
    public function obtenerEsquemasConServiciosActivos($ClaveAnexo, $CC, $postfijo) {
        $upper = strtoupper($postfijo);
        $lower = strtolower($postfijo);
        if ($upper == "GIM" || $upper == "GFA") {
            $consulta = "SELECT car.rentaMensual, car.IncluidoBN, car.IncluidoColor, car.ExcedentesBN, 
            car.ExcedentesColor, car.CostoProcesadaBN, car.CostoProcesadaColor , cim.IdServicio$upper, cim.Nombre, car.IdArrendamiento,
            kgim.*
            FROM c_servicio$lower AS cim
            INNER JOIN c_arrendamiento AS car ON car.IdArrendamiento = cim.IdArrendamiento
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.ClaveAnexoTecnico = '$ClaveAnexo'
            LEFT JOIN k_servicio$lower AS kgim ON
            kgim.IdKServicio$upper = (SELECT MIN(IdKServicio$upper) FROM k_servicio$lower WHERE IdAnexoClienteCC = kacc.IdAnexoClienteCC AND IdServicio$upper = cim.IdServicio$upper)
            WHERE cim.Activo = 1 AND car.Activo = 1
            GROUP BY cim.IdServicio$upper
            ORDER BY cim.IdServicio$upper;";
            $catalogo = new Catalogo();
            $query = $catalogo->obtenerLista($consulta);
            return $query;
        } else {
            return null;
        }
    }

    public function editRegistro() {
        $consulta = "UPDATE k_serviciogim SET IdServicioGIM=$this->IdServicioGIM,
            RentaMensual = $this->RentaMensual, PaginasIncluidasBN = $this->PaginasIncluidasBN, PaginasIncluidasColor =  $this->PaginasIncluidasColor,
            CostoPaginasExcedentesBN = $this->CostoPaginasExcedentesBN, CostoPaginasExcedentesColor = $this->CostoPaginasExcedentesColor,
            CostoPaginaProcesadaBN = $this->CostoPaginaProcesadaBN, CostoPaginaProcesadaColor = $this->CostoPaginaProcesadaColor,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla'
            WHERE IdKServicioGIM = $this->IdKServicioGIM;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
             $consulta = "INSERT INTO k_serviciohistoricogim(IdKServicioHistoricoGIM,IdKServicioGIM, IdServicioGIM, IdAnexoClienteCC, RentaMensual, PaginasIncluidasBN, PaginasIncluidasColor,
            CostoPaginasExcedentesBN, CostoPaginasExcedentesColor, CostoPaginaProcesadaBN, CostoPaginaProcesadaColor, UsuarioCreacion, 
            FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES(0,$this->IdKServicioGIM,$this->IdServicioGIM,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->PaginasIncluidasBN,$this->PaginasIncluidasColor,$this->CostoPaginasExcedentesBN,$this->CostoPaginasExcedentesColor,
            $this->CostoPaginaProcesadaBN,$this->CostoPaginaProcesadaColor,'$this->UsuarioUltimaModificacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla');";
             $catalogo->insertarRegistro($consulta);
            return true;
        }
        return false;
    }

    public function getNoSerieUpdate($idkServicio) {
        $arrayNoSerie = Array();
        $consulta = "SELECT cie.NoSerie FROM `k_serviciogim` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioGIM OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC)) 
        AND cie.ClaveEspKServicioFAIM = kim.IdServicioGIM INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        WHERE IdKServicioGIM = $idkServicio";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        $x = 0;
        while ($rs = mysql_fetch_array($query)) {
            $arrayNoSerie[$x] = $rs['NoSerie'];
            $x++;
        }
        return $arrayNoSerie;
    }

    public function editInvetarioEquipo($array, $servicio) {
        $consulta = "UPDATE c_inventarioequipo SET ClaveEspKServicioFAIM='$servicio' WHERE NoSerie IN ($array)";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdKServicioGIM() {
        return $this->IdKServicioGIM;
    }

    public function setIdKServicioGIM($IdKServicioGIM) {
        $this->IdKServicioGIM = $IdKServicioGIM;
    }

    public function getIdServicioGIM() {
        return $this->IdServicioGIM;
    }

    public function setIdServicioGIM($IdServicioGIM) {
        $this->IdServicioGIM = $IdServicioGIM;
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

    public function getPaginasIncluidasBN() {
        return $this->PaginasIncluidasBN;
    }

    public function setPaginasIncluidasBN($PaginasIncluidasBN) {
        $this->PaginasIncluidasBN = $PaginasIncluidasBN;
    }

    public function getPaginasIncluidasColor() {
        return $this->PaginasIncluidasColor;
    }

    public function setPaginasIncluidasColor($PaginasIncluidasColor) {
        $this->PaginasIncluidasColor = $PaginasIncluidasColor;
    }

    public function getCostoPaginasExcedentesBN() {
        return $this->CostoPaginasExcedentesBN;
    }

    public function setCostoPaginasExcedentesBN($CostoPaginasExcedentesBN) {
        $this->CostoPaginasExcedentesBN = $CostoPaginasExcedentesBN;
    }

    public function getCostoPaginasExcedentesColor() {
        return $this->CostoPaginasExcedentesColor;
    }

    public function setCostoPaginasExcedentesColor($CostoPaginasExcedentesColor) {
        $this->CostoPaginasExcedentesColor = $CostoPaginasExcedentesColor;
    }

    public function getCostoPaginaProcesadaBN() {
        return $this->CostoPaginaProcesadaBN;
    }

    public function setCostoPaginaProcesadaBN($CostoPaginaProcesadaBN) {
        $this->CostoPaginaProcesadaBN = $CostoPaginaProcesadaBN;
    }

    public function getCostoPaginaProcesadaColor() {
        return $this->CostoPaginaProcesadaColor;
    }

    public function setCostoPaginaProcesadaColor($CostoPaginaProcesadaColor) {
        $this->CostoPaginaProcesadaColor = $CostoPaginaProcesadaColor;
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

}

?>
