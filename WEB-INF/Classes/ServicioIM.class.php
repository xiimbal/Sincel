<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of ServicioIM
 *
 * @author MAGG
 */
class ServicioIM {

    private $IdKServicioIM;
    private $IdServicioIM;
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
    private $empresa;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `k_servicioim` WHERE IdKServicioIM = $id;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdKServicioIM = $rs['IdKServicioIM'];
            $this->IdServicioIM = $rs['IdServicioIM'];
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

    public function getRegistrosValidacion($id) {
        $consulta = ("SELECT ks.IdKServicioIM,cs.Nombre,c.NoParteEquipo, c.NoSerie, ks.RentaMensual FROM `k_anexoclientecc` AS ka 
            INNER JOIN k_servicioim AS ks ON ka.IdAnexoClienteCC = $id AND ka.IdAnexoClienteCC = ks.IdAnexoClienteCC
            LEFT JOIN c_inventarioequipo AS c ON c.IdAnexoClienteCC = ka.IdAnexoClienteCC
            LEFT JOIN c_servicioim AS cs on cs.IdServicioIM = ks.IdServicioIM;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getServiciosIMByIdAnexo($idAnexoClienteCC) {
        $consulta = "SELECT  DISTINCT(kim.IdServicioIM) AS IdServicio, cim.Nombre, kim.IdKServicioIM AS IdKServicio 
        FROM `k_servicioim` AS kim INNER JOIN c_servicioim AS cim ON kim.IdAnexoClienteCC = $idAnexoClienteCC 
        AND kim.IdServicioIM = cim.IdServicioIM GROUP BY kim.IdServicioIM;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getServiciosGIMByIdAnexo($idAnexoClienteCC) {
        $consulta = "SELECT  DISTINCT(kim.IdServicioGIM) AS IdServicio, cim.Nombre, kim.IdKServicioGIM AS IdKServicio 
        FROM `k_serviciogim` AS kim INNER JOIN c_serviciogim AS cim ON kim.IdAnexoClienteCC = $idAnexoClienteCC
        AND kim.IdServicioGIM = cim.IdServicioGIM GROUP BY kim.IdServicioGIM;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getServiciosGFAByIdAnexoClienteCC($idAnexoClienteCC) {
        $consulta = "SELECT  DISTINCT(kim.IdServicioGFA) AS IdServicio, cim.Nombre, kim.IdKServicioGFA AS IdKServicio 
        FROM `k_serviciogfa` AS kim INNER JOIN c_serviciogfa AS cim ON kim.IdAnexoClienteCC = $idAnexoClienteCC
        AND kim.IdServicioGFA = cim.IdServicioGFA GROUP BY kim.IdServicioGFA;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getServiciosFAByIdAnexoClienteCC($idAnexoClienteCC) {
        $consulta = "SELECT  DISTINCT(kim.IdServicioFA) AS IdServicio, cim.Nombre, kim.IdKServicioFA AS IdKServicio 
        FROM `k_serviciofa` AS kim INNER JOIN c_serviciofa AS cim ON kim.IdAnexoClienteCC = $idAnexoClienteCC
        AND kim.IdServicioFA = cim.IdServicioFA GROUP BY kim.IdServicioFA;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
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
        FROM `k_servicioim` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioIM OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC)) 
        AND cie.ClaveEspKServicioFAIM = kim.IdServicioIM
        INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cie.IdKserviciogimgfa
        LEFT JOIN k_anexoclientecc AS kacc2 ON kacc2.IdAnexoClienteCC = cie.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc2.CveEspClienteCC
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
        WHERE IdKServicioIM = $idKAnexo AND b.Activo = 1 AND b.VentaDirecta = 0 ORDER BY NoSerie DESC;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene las solicitudes donde está asignado el servicio especificado
     * @param type $idKServicio
     * @param type $idServicio
     * @return type
     */
    public function getSolicitudesAbiertasAsignadas($idKServicio, $idServicio) {
        $consulta = "SELECT ks.id_partida, ks.id_solicitud 
            FROM `k_solicitud` AS ks
            LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = ks.id_solicitud
            WHERE ks.IdKServicio = $idKServicio AND ks.IdServicio = $idServicio AND cs.estatus IN (0,1,2);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
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
        FROM `k_servicioim` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioIM OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC)) 
        AND cie.ClaveEspKServicioFAIM = kim.IdServicioIM
        INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cie.IdKserviciogimgfa
        LEFT JOIN k_anexoclientecc AS kacc2 ON kacc2.IdAnexoClienteCC = cie.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc2.CveEspClienteCC
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
        WHERE IdKServicioIM = $idKAnexo AND b.Activo = 1 AND b.VentaDirecta = 0 HAVING ClaveCentroCosto = '$localidad' ORDER BY NoSerie DESC;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function deleteRegistro($idKServicio) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        
            $consulta1 = "DELETE FROM k_serviciohistoricoim WHERE IdKServicioIM = $idKServicio;";
            $catalogo->obtenerLista($consulta1);
        
        $consulta = "DELETE FROM k_servicioim WHERE IdKServicioIM = $idKServicio;";
        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
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
        $consulta = "SELECT kim.IdKServicioIM, cim.IdServicioIM, cim.Nombre AS servicio,
        (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, 
        kim.RentaMensual, kim.PaginasIncluidasBN, kim.PaginasIncluidasColor, kim.CostoPaginaProcesadaBN, 
        kim.CostoPaginaProcesadaColor, kim.CostoPaginasExcedentesBN, kim.CostoPaginasExcedentesColor, 
        carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor ,
        kim.UsuarioUltimaModificacion, kim.FechaUltimaModificacion
        FROM `k_servicioim` AS kim 
        INNER JOIN k_anexoclientecc AS kacc ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC 
        INNER JOIN c_servicioim AS cim ON kim.IdServicioIM = cim.IdServicioIM 
        LEFT JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento 
        WHERE kacc.ClaveAnexoTecnico = '$claveAnexo' AND cim.Activo = 1 AND carr.Activo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
        public function getHistoricoServicios($claveServicio) {
        $consulta = "SELECT kim.IdKServicioIM, cim.IdServicioIM, cim.Nombre AS servicio,
        (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, 
        kim.RentaMensual, kim.PaginasIncluidasBN, kim.PaginasIncluidasColor, kim.CostoPaginaProcesadaBN, 
        kim.CostoPaginaProcesadaColor, kim.CostoPaginasExcedentesBN, kim.CostoPaginasExcedentesColor, 
        carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor ,
        kim.UsuarioUltimaModificacion, kim.FechaUltimaModificacion
        FROM `k_serviciohistoricoim` AS kim 
        INNER JOIN k_anexoclientecc AS kacc ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC 
        INNER JOIN c_servicioim AS cim ON kim.IdServicioIM = cim.IdServicioIM 
        LEFT JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento 
        WHERE kim.IdKServicioIM = '$claveServicio' AND cim.Activo = 1 AND carr.Activo = 1 ORDER BY kim.FechaUltimaModificacion DESC;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene los servicios asociados al anexo tecnico del k_anexoclientecc especificado
     * @param type $idAnexoClienteCC
     * @return type resultset con los servicios del anexo.
     */
    public function getServiciosAnexoByIdAnexoClienteCC($idAnexoClienteCC, $prefijo) {
        $prefijo_upper = strtoupper($prefijo);
        $prefijo_lower = strtolower($prefijo);

        if ($prefijo_lower == "im") {
            $incluidasbn = "PaginasIncluidasBN";
            $incluidasColor = "PaginasIncluidasColor";
            $costoExcedentesBN = "CostoPaginasExcedentesBN";
            $costoExcedentesColor = "CostoPaginasExcedentesColor";
            $costoProcesadosBN = "CostoPaginaProcesadaBN";
            $costoProcesadosColor = "CostoPaginaProcesadaColor";
        } else if ($prefijo_lower == "gim") {
            $incluidasbn = "PaginasIncluidasBN";
            $incluidasColor = "PaginasIncluidasColor";
            $costoExcedentesBN = "CostoPaginasExcedentesBN";
            $costoExcedentesColor = "CostoPaginasExcedentesColor";
            $costoProcesadosBN = "CostoPaginaProcesadaBN";
            $costoProcesadosColor = "CostoPaginaProcesadaColor";
        } else if ($prefijo_lower == "fa") {
            $incluidasbn = "MLIncluidosBN";
            $incluidasColor = "MLIncluidosColor";
            $costoExcedentesBN = "CostoMLExcedentesBN";
            $costoExcedentesColor = "CostoMLExcedentesColor";
            $costoProcesadosBN = "CostoMLProcesadosBN";
            $costoProcesadosColor = "CostoMLProcesadosColor";
        } else if ($prefijo_lower == "gfa") {
            $incluidasbn = "MLIncluidosBN";
            $incluidasColor = "MLIncluidosColor";
            $costoExcedentesBN = "CostoMLExcedentesBN";
            $costoExcedentesColor = "CostoMLExcedentesColor";
            $costoProcesadosBN = "CostoMLProcesadosBN";
            $costoProcesadosColor = "CostoMLProcesadosColor";
        } else {
            return null;
        }

        $consulta = "SELECT DISTINCT(ks.IdKServicio$prefijo_upper) AS IdKServicio$prefijo_upper, kacc.IdAnexoClienteCC, kacc2.IdAnexoClienteCC, kacc2.ClaveAnexoTecnico, cs.Nombre, ks.IdServicio$prefijo_upper,
            ks.RentaMensual,ks.$incluidasbn,ks.$incluidasColor,ks.$costoExcedentesBN,ks.$costoExcedentesColor,ks.$costoProcesadosBN,ks.$costoProcesadosColor
            FROM k_anexoclientecc AS kacc
            INNER JOIN k_anexoclientecc AS kacc2 ON kacc2.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            INNER JOIN k_servicio$prefijo_lower AS ks ON kacc2.IdAnexoClienteCC = ks.IdAnexoClienteCC
            LEFT JOIN c_servicio$prefijo_lower AS cs ON cs.IdServicio$prefijo_upper = ks.IdServicio$prefijo_upper
            WHERE kacc.IdAnexoClienteCC = '$idAnexoClienteCC';";

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function escribirServicioAbreviado($rs, $prefijo) {
        $prefijo_lower = strtolower($prefijo);

        if ($prefijo_lower == "im") {
            $incluidasbn = "PaginasIncluidasBN";
            $incluidasColor = "PaginasIncluidasColor";
            $costoExcedentesBN = "CostoPaginasExcedentesBN";
            $costoExcedentesColor = "CostoPaginasExcedentesColor";
            $costoProcesadosBN = "CostoPaginaProcesadaBN";
            $costoProcesadosColor = "CostoPaginaProcesadaColor";
        } else if ($prefijo_lower == "gim") {
            $incluidasbn = "PaginasIncluidasBN";
            $incluidasColor = "PaginasIncluidasColor";
            $costoExcedentesBN = "CostoPaginasExcedentesBN";
            $costoExcedentesColor = "CostoPaginasExcedentesColor";
            $costoProcesadosBN = "CostoPaginaProcesadaBN";
            $costoProcesadosColor = "CostoPaginaProcesadaColor";
        } else if ($prefijo_lower == "fa") {
            $incluidasbn = "MLIncluidosBN";
            $incluidasColor = "MLIncluidosColor";
            $costoExcedentesBN = "CostoMLExcedentesBN";
            $costoExcedentesColor = "CostoMLExcedentesColor";
            $costoProcesadosBN = "CostoMLProcesadosBN";
            $costoProcesadosColor = "CostoMLProcesadosColor";
        } else if ($prefijo_lower == "gfa") {
            $incluidasbn = "MLIncluidosBN";
            $incluidasColor = "MLIncluidosColor";
            $costoExcedentesBN = "CostoMLExcedentesBN";
            $costoExcedentesColor = "CostoMLExcedentesColor";
            $costoProcesadosBN = "CostoMLProcesadosBN";
            $costoProcesadosColor = "CostoMLProcesadosColor";
        } else {
            return null;
        }

        $respuesta = "";
        if (isset($rs['RentaMensual']) && !empty($rs['RentaMensual'])) {
            $respuesta .= " - RM: $" . number_format($rs['RentaMensual'], 2);
        }

        if (isset($rs[$incluidasbn]) && !empty($rs[$incluidasbn])) {
            $respuesta .= " - I B/N: " . number_format($rs[$incluidasbn], 0);
        }

        if (isset($rs[$incluidasColor]) && !empty($rs[$incluidasColor])) {
            $respuesta .= " - I Cl: " . number_format($rs[$incluidasColor], 0);
        }

        if (isset($rs[$costoExcedentesBN]) && !empty($rs[$costoExcedentesBN])) {
            $respuesta .= " - E B/N: $" . number_format($rs[$costoExcedentesBN], 2);
        }

        if (isset($rs[$costoExcedentesColor]) && !empty($rs[$costoExcedentesColor])) {
            $respuesta .= " - E Cl: $" . number_format($rs[$costoExcedentesColor], 2);
        }

        if (isset($rs[$costoProcesadosBN]) && !empty($rs[$costoProcesadosBN])) {
            $respuesta .= " - P B/N: $" . number_format($rs[$costoProcesadosBN], 2);
        }

        if (isset($rs[$costoProcesadosColor]) && !empty($rs[$costoProcesadosColor])) {
            $respuesta .= " - P Color: $" . number_format($rs[$costoProcesadosColor], 2);
        }
        return $respuesta;
    }

    /**
     * Obtiene los servicios particulares de impresion por el anexo especificado y el centro de costo.
     * @param type $claveAnexo clave del anexo
     * @return type resultset con los servicios del anexo.
     */
    public function getServiciosByAnexoAndCC($claveAnexo, $CC) {
        $consulta = "SELECT kim.IdKServicioIM, cim.IdServicioIM, cim.Nombre AS servicio,
        (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, 
        kim.RentaMensual, kim.PaginasIncluidasBN, kim.PaginasIncluidasColor, kim.CostoPaginaProcesadaBN, 
        kim.CostoPaginaProcesadaColor, kim.CostoPaginasExcedentesBN, kim.CostoPaginasExcedentesColor, 
        carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor 
        FROM `k_servicioim` AS kim 
        INNER JOIN k_anexoclientecc AS kacc ON kim.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC 
        INNER JOIN c_servicioim AS cim ON kim.IdServicioIM = cim.IdServicioIM 
        LEFT JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento 
        WHERE kacc.ClaveAnexoTecnico = '$claveAnexo' AND kacc.CveEspClienteCC = '$CC' AND cim.Activo = 1 AND carr.Activo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {
        $consulta = "INSERT INTO k_servicioim(IdKServicioIM,IdServicioIM,IdAnexoClienteCC,RentaMensual,
            PaginasIncluidasBN,PaginasIncluidasColor,CostoPaginasExcedentesBN,CostoPaginasExcedentesColor,
            CostoPaginaProcesadaBN,CostoPaginaProcesadaColor,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla) VALUES(0,$this->IdServicioIM,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->PaginasIncluidasBN,$this->PaginasIncluidasColor,$this->CostoPaginasExcedentesBN,$this->CostoPaginasExcedentesColor,
            $this->CostoPaginaProcesadaBN,$this->CostoPaginaProcesadaColor,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdKServicioIM = $catalogo->insertarRegistro($consulta);
        if ($this->IdKServicioIM != NULL && $this->IdKServicioIM != 0) {
            //Insertamos en tabla de historico
            $consulta = "INSERT INTO k_serviciohistoricoim(IdKServicioHistoricoIM,IdKServicioIM,IdServicioIM,IdAnexoClienteCC,RentaMensual,
            PaginasIncluidasBN,PaginasIncluidasColor,CostoPaginasExcedentesBN,CostoPaginasExcedentesColor,
            CostoPaginaProcesadaBN,CostoPaginaProcesadaColor,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla) VALUES(0,$this->IdKServicioIM,$this->IdServicioIM,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->PaginasIncluidasBN,$this->PaginasIncluidasColor,$this->CostoPaginasExcedentesBN,$this->CostoPaginasExcedentesColor,
            $this->CostoPaginaProcesadaBN,$this->CostoPaginaProcesadaColor,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla');";
            $catalogo->insertarRegistro($consulta);
            return true;
        }
        return false;
    }

    public function getServiciosImpresion() {
        $consulta = "SELECT IdServicioIM AS IdServico, Nombre, 'Particular' AS tipo FROM c_servicioim WHERE Activo = 1
        UNION
        SELECT IdServicioGIM AS IdServico, Nombre, 'Global' AS tipo FROM c_serviciogim WHERE Activo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function setIdKServicioToInventarios($NoSerie, $idKServicio, $idServicio) {
        $consulta = "UPDATE c_inventarioequipo SET IdKServicio = $idKServicio, ClaveEspKServicioFAIM = $idServicio WHERE NoSerie IN ($NoSerie)";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = "UPDATE k_servicioim SET IdServicioIM=$this->IdServicioIM,
            RentaMensual = $this->RentaMensual, PaginasIncluidasBN = $this->PaginasIncluidasBN, PaginasIncluidasColor =  $this->PaginasIncluidasColor,
            CostoPaginasExcedentesBN = $this->CostoPaginasExcedentesBN, CostoPaginasExcedentesColor = $this->CostoPaginasExcedentesColor,
            CostoPaginaProcesadaBN = $this->CostoPaginaProcesadaBN, CostoPaginaProcesadaColor = $this->CostoPaginaProcesadaColor,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla'
            WHERE IdKServicioIM = $this->IdKServicioIM;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            //Insertamos en tabla de historico
            $consulta = "INSERT INTO k_serviciohistoricoim(IdKServicioHistoricoIM,IdKServicioIM,IdServicioIM,IdAnexoClienteCC,RentaMensual,
            PaginasIncluidasBN,PaginasIncluidasColor,CostoPaginasExcedentesBN,CostoPaginasExcedentesColor,
            CostoPaginaProcesadaBN,CostoPaginaProcesadaColor,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla) VALUES(0,$this->IdKServicioIM,$this->IdServicioIM,$this->IdAnexoClienteCC,$this->RentaMensual,
            $this->PaginasIncluidasBN,$this->PaginasIncluidasColor,$this->CostoPaginasExcedentesBN,$this->CostoPaginasExcedentesColor,
            $this->CostoPaginaProcesadaBN,$this->CostoPaginaProcesadaColor,'$this->UsuarioUltimaModificacion',now(),'$this->UsuarioUltimaModificacion',now(),
            '$this->Pantalla');";
            $catalogo->insertarRegistro($consulta);
            return true;
        }
        return false;
    }

    /**
     * Actualiza el arrendamiento (esquema) en el servicio IM especificado.
     * @param type $idArrendamiento id del arrendamiento que se va a asignar
     * @param type $idServicio id del servicio IM a actualizar
     * @return boolean true en caso de haber actualizado, false en caso contrario.
     */
    public function actualizarArrendamiento($idArrendamiento, $idServicio) {
        $consulta = "UPDATE `c_servicioim` SET IdArrendamiento = $idArrendamiento WHERE IdServicioIM = $idServicio;";
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

    /**
     * Obtiene los servicios de impresion particulares con su Esquema (Si ocupa Renta mensual, Pag. Incluidas, etc.)
     * @return type ResultSet con el resultado del query.
     */
    public function obtenerEsquemas() {
        $consulta = "SELECT car.rentaMensual, car.IncluidoBN, car.IncluidoColor, car.ExcedentesBN, 
        car.ExcedentesColor, car.CostoProcesadaBN, car.CostoProcesadaColor , cim.IdServicioIM, cim.Nombre, car.IdArrendamiento
        FROM c_servicioim AS cim
        INNER JOIN c_arrendamiento AS car ON car.IdArrendamiento = cim.IdArrendamiento
        WHERE cim.Activo = 1 AND car.Activo = 1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * 
     * @param type $TipoServicio
     */
    public function obtenerEsquemaByTipoServicio($TipoServicio, $prefijo) {
        $prefijo_mayor = strtoupper($prefijo);
        $prefijo_menor = strtolower($prefijo);
        $consulta = "SELECT cim.Nombre, carr.Tipo, carr.rentaMensual AS RM, carr.IncluidoBN, carr.IncluidoColor, carr.ExcedentesBN, 
        carr.ExcedentesColor, carr.CostoProcesadaBN, carr.CostoProcesadaColor
        FROM `c_servicio$prefijo_menor` AS cim
        INNER JOIN c_arrendamiento AS carr ON carr.IdArrendamiento = cim.IdArrendamiento
        WHERE cim.IdServicio$prefijo_mayor = $TipoServicio;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
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
        if ($upper == "IM" || $upper == "FA") {
            $consulta = "SELECT car.rentaMensual, car.IncluidoBN, car.IncluidoColor, car.ExcedentesBN, 
            car.ExcedentesColor, car.CostoProcesadaBN, car.CostoProcesadaColor , cim.IdServicio$upper, cim.Nombre, car.IdArrendamiento,
            kgim.*
            FROM c_servicio$lower AS cim
            INNER JOIN c_arrendamiento AS car ON car.IdArrendamiento = cim.IdArrendamiento
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.ClaveAnexoTecnico = '$ClaveAnexo' AND kacc.CveEspClienteCC = '$CC'
            LEFT JOIN k_servicio$lower AS kgim ON
            kgim.IdKServicio$upper = (SELECT MIN(IdKServicio$upper) FROM k_servicio$lower WHERE IdAnexoClienteCC = kacc.IdAnexoClienteCC AND IdServicio$upper = cim.IdServicio$upper)
            WHERE cim.Activo = 1 AND car.Activo = 1
            GROUP BY cim.IdServicio$upper
            ORDER BY cim.IdServicio$upper;";
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $query = $catalogo->obtenerLista($consulta);
            return $query;
        } else {
            return null;
        }
    }

    public function getNoSerieUpdate($idkServicio) {
        $arrayNoSerie = Array();
        $consulta = "SELECT cie.NoSerie FROM `k_servicioim` AS kim
        INNER JOIN c_inventarioequipo AS cie ON (cie.IdKServicio = kim.IdKServicioIM OR (ISNULL(cie.IdKServicio) AND cie.IdAnexoClienteCC = kim.IdAnexoClienteCC)) 
        AND cie.ClaveEspKServicioFAIM = kim.IdServicioIM INNER JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
        WHERE IdKServicioIM = $idkServicio";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
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
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdKServicioIM() {
        return $this->IdKServicioIM;
    }

    public function setIdKServicioIM($IdKServicioIM) {
        $this->IdKServicioIM = $IdKServicioIM;
    }

    public function getIdServicioIM() {
        return $this->IdServicioIM;
    }

    public function setIdServicioIM($IdServicioIM) {
        $this->IdServicioIM = $IdServicioIM;
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

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}

?>
