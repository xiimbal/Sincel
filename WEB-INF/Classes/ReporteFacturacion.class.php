<?php

include_once("ConexionFacturacion.class.php");
include_once("Conexion.class.php");
include_once("ParametroGlobal.class.php");
include_once("Catalogo.class.php");
include_once("CatalogoFacturacion.class.php");

class ReporteFacturacion {

    private $FechaInicial;
    private $FechaFinal;
    private $RFC = "";
    private $rfccliente;
    private $status;
    private $docto;
    private $folio;
    private $cliente = "";
    private $ejecutivo;
    private $empresa;
    private $conexionn;

    public function getTabla($prefactura) {
        /* Obtenemos el vendedor que se pone por default segun los parametros globales */
        $parametro = new ParametroGlobal();
        if (isset($this->empresa)) {
            $parametro->setEmpresa($this->empresa);
        }

        if ($parametro->getRegistroById("1")) {
            $vendedor = $parametro->getValor();
        } else {
            $vendedor = "";
        }

        $tiene_filtro = false;
        $mostrar_ndc = true;
        $where = "WHERE";

        if (isset($this->RFC) && $this->RFC != "") {
            $tiene_filtro = true;
            $where .= " f.RFCEmisor='" . $this->RFC . "'";
        }

        if (isset($this->FechaInicial) && $this->FechaInicial != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            } else {
                $where .= " f.FechaFacturacion >= '" . $this->FechaInicial . " 00:00:00'";
            }
        }

        if (isset($this->FechaFinal) && $this->FechaFinal != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            } else {
                $where .= " f.FechaFacturacion <= '" . $this->FechaFinal . " 23:59:59'";
            }
        }

        if (isset($this->ejecutivo) && $this->ejecutivo != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.EjecutivoCuenta = $this->ejecutivo ";
            } else {
                $where .= " c.EjecutivoCuenta = $this->ejecutivo ";
            }
        }

        if (isset($this->rfccliente) && $this->rfccliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.RFCReceptor = '" . $this->rfccliente . "'";
            } else {
                $where .= " f.RFCReceptor = '" . $this->rfccliente . "'";
            }
        }

        if (isset($this->cliente) && $this->cliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.RFCReceptor = '" . $this->cliente . "'";
            } else {
                $where .= " f.RFCReceptor = '" . $this->cliente . "'";
            }
        }

        if (isset($this->status)) {
            $tiene_filtro = true;
            $mostrar_ndc = false;
            if ($where != "WHERE") {
                $where .= " AND( ";
                foreach ($this->status as $value) {
                    if ($value == 5) {
                        $where .= " (f.PendienteCancelar = 1 AND f.EstadoFactura!=0) OR ";
                    } else if ($value == 3) {
                        $where .= " (f.EstatusFactura = $value AND f.EstadoFactura!=0 AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else {
                        $where .= " (f.EstadoFactura = $value) OR ";
                    }
                }
                if ($where != "") {
                    $where = substr($where, 0, strlen($where) - 3);
                }
                $where .= ")";
            } else {
                $where .= " ( ";
                foreach ($this->status as $value) {
                    if ($value == 5) {
                        $where .= " (f.PendienteCancelar = 1 AND f.EstadoFactura!=0) OR ";
                    } else if ($value == 3) {
                        $where .= " (f.EstatusFactura = $value AND f.EstadoFactura!=0 AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 4) {
                        $where .= " (f.FacturaPagada = 1 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } elseif ($value == 1) {
                        $where .= " (f.FacturaPagada = 0 AND f.EstadoFactura!=0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura!=3) AND f.PendienteCancelar = 0) OR ";
                    } else {
                        $where .= " (f.EstadoFactura = $value) OR ";
                    }
                }
                if ($where != "") {
                    $where = substr($where, 0, strlen($where) - 3);
                }
                $where .= ")";
            }
        }

        if (isset($this->folio) && $this->folio != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.Folio = '" . $this->folio . "'";
            } else {
                $where .= " f.Folio = '" . $this->folio . "'";
            }
        }

        if (isset($this->docto) && $this->docto != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.TipoComprobante = '" . $this->docto . "'";
            } else {
                $where .= " f.TipoComprobante = '" . $this->docto . "'";
            }
        }

        if ($where == "WHERE") {
            $where = "";
            if (!$prefactura) {
                $where = " WHERE f.Serie <> 'PREF'";
            }
        } else {
            if (!$prefactura) {
                $where .= " AND f.Serie <> 'PREF' ";
            }
        }

        /* Agregamos un having para que no muestre las notas de credito en caso de que sea necesario */
        $having = "";
        if (!$mostrar_ndc) {
            $having = " HAVING TipoComprobante <> 'NDC'";
        }


        $consulta = "SELECT f.Folio,";
        if (!$prefactura) {
            $consulta.="(SELECT CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) ELSE '$vendedor' END) AS ejecutivo,";
        }
        $consulta.="(SELECT SUM(ImportePagado) AS suma FROM c_pagosparciales AS pp WHERE pp.IdFactura = f.IdFactura) AS pagado,
            DATE(f.FechaFacturacion) AS FechaFacturacion, DATE(f.PeriodoFacturacion) AS PeriodoFacturacion,
            f.NombreReceptor,
            f.NombreEmisor,
            f.Serie,
            c.ClaveCliente,
            SUBSTRING(f.RFCEmisor,1,3) AS RFCEmisor,
            FORMAT((f.Total/1.16),2) AS subtotal,
            FORMAT(((f.Total/1.16)*.16),2) AS importe,
            FORMAT(f.Total,2) as Total,            
            f.RFCReceptor,
            f.IdFactura AS IdFactura,
            f.PathPDF AS PDF,
            (GROUP_CONCAT(fndc.Folio SEPARATOR ', ')) AS PagadoNDC,
            (SELECT CASE WHEN f.TipoComprobante = 'ingreso' THEN 'F' ELSE 'NDC' END) AS TipoComprobante,
            (SELECT CASE WHEN f.EstadoFactura = 0 THEN 'C' WHEN f.PendienteCancelar = 1 THEN 'Pendiente Cancelar' WHEN f.TipoComprobante <> 'ingreso' THEN 'NDC' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'INC' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
            (SELECT CASE WHEN f.FacturaEnviada = 1 THEN 'Si' ELSE 'No' END) AS Enviado, 
            (SELECT CASE WHEN f.TipoComprobante <> 'ingreso' THEN 'NA' WHEN f.EstadoFactura = 0 THEN 'No' ELSE (SELECT CASE WHEN f.EstatusFactura = 3 THEN 'No' ELSE (SELECT CASE WHEN f.FacturaPagada = 0 THEN 'No' ELSE 'Si' END) END) END) AS PagadoSiNo,            
            f.CanceladaSAT as CanceladaSAT,
            (CASE WHEN !ISNULL((SELECT MAX(FechaPago)FROM c_pagosparciales WHERE IdFactura = f.IdFactura)) 
            THEN (SELECT MAX(FechaPago)FROM c_pagosparciales WHERE IdFactura = f.IdFactura)
            ELSE f.FechaPago END) AS FechaPago
            FROM c_factura AS f ";
        //if(!$prefactura){
        $consulta.=" LEFT JOIN c_cliente AS c ON c.Activo = 1 AND TRIM(f.RFCReceptor) = TRIM(c.RFC) AND c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1) LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta ";
        //}
        $consulta.=" LEFT JOIN c_factura AS fndc ON fndc.IdFacturaRelacion = f.IdFactura AND fndc.TipoComprobante = 'egreso' ";
        $consulta.= $where;
        if (!$tiene_filtro && $prefactura) {/* Si no tiene filtro y se permiten pre-facturas(no es el excel) */
            $consulta.=" GROUP BY IdFactura $having ORDER BY FechaFacturacion DESC LIMIT 0,500";
        } else {
            $consulta.=" GROUP BY IdFactura ";
            $consulta.=$having;
        }
        
        $catalogo = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function CambiarPagado($tipo) {
        $consulta = ("UPDATE c_factura SET FacturaPagada=" . $tipo . " WHERE Folio='" . $this->folio . "'");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function obtenerXML() {
        $consulta = ("SELECT cfdiTimbrado AS FacturaXML FROM c_factura WHERE Folio='" . $this->folio . "'");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function obtenerXMLCancelada() {
        $consulta = ("SELECT CanceladaSAT FROM c_factura WHERE Folio='" . $this->folio . "'");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Convierte en un array tridimensional el resultSet del cron de facturacion consolidado.
     * @param type $result array con index IdDatosFacturacionEmpresa, Mes, EstadoFactura
     * @return type
     */
    public function convertirRSIntoArrayConsolidado($result) {
        $resultado = array();
        while ($rs = mysql_fetch_array($result)) {
            if (isset($rs['Cuenta'])) {
                $resultado[$rs['IdDatosFacturacionEmpresa']][$rs['Mes']][$rs['EstadoFacturaPer']] = $rs['Cuenta'];
            } else if (isset($rs['Cuenta1']) && isset($rs['Cuenta2'])) {
                $resultado[$rs['IdDatosFacturacionEmpresa']][$rs['Mes']][$rs['EstadoFacturaPer']] = (float) $rs['Cuenta1'] + (float) $rs['Cuenta2'];
            }
        }
        return $resultado;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
    }

    public function getDocto() {
        return $this->docto;
    }

    public function setDocto($docto) {
        $this->docto = $docto;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getRfccliente() {
        return $this->rfccliente;
    }

    public function setRfccliente($rfccliente) {
        $this->rfccliente = $rfccliente;
    }

    public function getVendedor() {
        return $this->vendedor;
    }

    public function setVendedor($vendedor) {
        $this->vendedor = $vendedor;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function getFechaInicial() {
        return $this->FechaInicial;
    }

    public function setFechaInicial($FechaInicial) {
        $this->FechaInicial = $FechaInicial;
    }

    public function getFechaFinal() {
        return $this->FechaFinal;
    }

    public function setFechaFinal($FechaFinal) {
        $this->FechaFinal = $FechaFinal;
    }

    public function getRFC() {
        return $this->RFC;
    }

    public function setRFC($RFC) {
        $this->RFC = $RFC;
    }

    public function getEjecutivo() {
        return $this->ejecutivo;
    }

    public function setEjecutivo($ejecutivo) {
        $this->ejecutivo = $ejecutivo;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getConexionn() {
        return $this->conexionn;
    }

    public function setConexionn($conexionn) {
        $this->conexionn = $conexionn;
    }

}

?>
