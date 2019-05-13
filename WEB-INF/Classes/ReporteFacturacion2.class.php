<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ReporteFacturacion2 {

    private $FechaInicial;
    private $FechaFinal;
    private $RFC = "";
    private $rfccliente;
    private $status;
    private $docto;
    private $folio;
    private $cliente = "";
    private $conexionn;
    private $GrupoEmpresa;
    private $Empresa;

    public function getTabla($prefactura) {
        $tiene_filtro = false;
        $where = "WHERE";

        if (isset($this->RFC) && $this->RFC != "") {
            $tiene_filtro = true;
            $where .= " e.RFC='" . $this->RFC . "'";
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

        if (isset($this->rfccliente) && $this->rfccliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.RFC = '" . $this->rfccliente . "'";
            } else {
                $where .= " c.RFC = '" . $this->rfccliente . "'";
            }
        }

        if (isset($this->cliente) && $this->cliente != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND c.RFC = '" . $this->cliente . "'";
            } else {
                $where .= " c.RFC = '" . $this->cliente . "'";
            }
        }

        if (isset($this->status) && $this->status != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                if ($this->status == 3) {
                    $where .= " AND EstatusFactura = " . $this->status . " AND EstadoFactura!=0 ";
                } elseif ($this->status == 4) {
                    $where .= " AND FacturaPagada = 1 AND EstadoFactura!=0 AND EstatusFactura!=3";
                } elseif ($this->status == 1) {
                    $where .= " AND FacturaPagada = 0 AND EstadoFactura!=0 AND EstatusFactura!=3 ";
                } else {
                    $where .= " AND EstadoFactura = " . $this->status . "";
                }
            } else {
                if ($this->status == 3) {
                    $where .= " EstatusFactura = " . $this->status . " AND EstadoFactura!=0";
                } elseif ($this->status == 4) {
                    $where .= " FacturaPagada = 1 AND EstadoFactura!=0 AND EstatusFactura!=3";
                } elseif ($this->status == 1) {
                    $where .= " FacturaPagada = 0 AND EstadoFactura!=0 AND EstatusFactura!=3 ";
                } else {
                    $where .= " EstadoFactura = " . $this->status . "";
                }
            }
        }

        if (isset($this->folio) && $this->folio != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND f.Folio LIKE '%" . $this->folio . "%'";
            } else {
                $where .= " f.Folio LIKE '%" . $this->folio . "%'";
            }
        }

        if (isset($this->docto) && $this->docto != "") {
            $tiene_filtro = true;
            if ($where != "WHERE") {
                $where .= " AND TipoComprobante = '" . $this->docto . "'";
            } else {
                $where .= " TipoComprobante = '" . $this->docto . "'";
            }
        }

        if ($where == "WHERE") {
            $where .= " Invisible=0";
        } else {
            $where .= " AND Invisible=0";
        }

        $consulta = "SELECT DISTINCT f.Folio AS Folio, 
           f.IdFactura AS IdFactura,
           IF((SUM(pp.importe))=(SUM(DISTINCT con.PrecioUnitario*con.Cantidad)*1.16),'Si','No') AS pagado,
           f.FechaFacturacion AS FechaFacturacion,
           IF(ISNULL(f.FacturaXML),'Prefactura','Facturada') AS TipoFactura,
           tp.Nombre AS TipoFacturaNombre,
           e.RazonSocial AS NombreEmisor,
           c.NombreRazonSocial AS NombreReceptor,
           e.RFC AS RFCEmisor,
           f.Generada AS Generada,
           (SELECT CASE WHEN EstadoFactura = 0 THEN 'NP' ELSE (SELECT CASE WHEN EstadoFactura = 2 THEN 'C' ELSE (SELECT CASE WHEN FacturaPagada = 0 THEN 'NP' ELSE 'P' END) END) END) AS EstadoFactura,
           c.RFC AS RFCReceptor,
           f.PathPDF AS PDF,
           f.PathXML AS XML, f.CFDI33,
           (SELECT CASE WHEN TipoComprobante = 'ingreso' THEN 'F' ELSE 'NDC' END) AS TipoComprobante,
           (SELECT CASE WHEN FacturaEnviada = 1 THEN 'Si' ELSE 'No' END) AS Enviado, 
           IF((SUM(pp.importe))=(SUM(DISTINCT con.PrecioUnitario*con.Cantidad)*1.16),'Si','No') AS PagadoSiNo,            
           CanceladaSAT as CanceladaSAT,           
           (CASE WHEN !ISNULL(con.idConcepto) THEN SUM(con.PrecioUnitario*con.Cantidad) ELSE f.Total/1.16 END) AS Subtotal,
            (CASE 
                            WHEN ISNULL(f.Descuentos) THEN 0
                            WHEN !ISNULL(con.idConcepto) 
                            THEN SUM( (con.PrecioUnitario*con.Cantidad) * (f.Descuentos / 100) )	
                            ELSE (f.Total/1.16) * (f.Descuentos / 100)
            END) AS Descuento,

            (
            (CASE WHEN !ISNULL(con.idConcepto) THEN SUM(con.PrecioUnitario*con.Cantidad) ELSE f.Total/1.16 END) -
            (CASE 
                            WHEN ISNULL(f.Descuentos) THEN 0
                            WHEN !ISNULL(con.idConcepto) 
                            THEN SUM( (con.PrecioUnitario*con.Cantidad) * (f.Descuentos / 100) )	
                            ELSE (f.Total/1.16) * (f.Descuentos / 100)
            END)
            ) * 0.16 AS Importe,         

            (
            (CASE WHEN !ISNULL(con.idConcepto) THEN SUM(con.PrecioUnitario*con.Cantidad) ELSE f.Total/1.16 END) -
            (CASE 
                            WHEN ISNULL(f.Descuentos) THEN 0
                            WHEN !ISNULL(con.idConcepto) 
                            THEN SUM( (con.PrecioUnitario*con.Cantidad) * (f.Descuentos / 100) )	
                            ELSE (f.Total/1.16) * (f.Descuentos / 100)
            END)
            ) * 1.16 AS Total    
           
            FROM c_factura AS f 
            INNER JOIN c_datosfacturacionempresa AS e ON e.IdDatosFacturacionEmpresa = f.RFCEmisor
            INNER JOIN c_cliente AS c ON c.ClaveCliente=f.RFCReceptor
            LEFT JOIN c_conceptos AS con ON con.idFactura=f.IdFactura
            LEFT JOIN c_pagosparciales AS pp ON pp.id_factura=f.IdFactura
            INNER JOIN c_tipofactura AS tp ON tp.Id_TipoFactura=f.Id_TipoFactura
             " . $where;
        $consulta.=" GROUP BY IdFactura ORDER BY FechaFacturacion DESC LIMIT 0,500";
        //echo $consulta;
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function CambiarPagado($tipo) {
        $consulta = ("UPDATE c_factura SET EstadoFactura=" . $tipo . " WHERE IdFactura='" . $this->folio . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function GenerarFactura($xml) {
        if (!empty($xml)) {
            $xml = str_replace("'", "´", $xml);
        }
        $consulta = ("UPDATE c_factura SET FacturaXML='" . $xml . "' WHERE IdFactura=" . $this->folio . ";");

        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function obtenerXML() {
        $consulta = ("SELECT FacturaXML FROM c_factura WHERE IdFactura='" . $this->folio . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function obtenerXMLCancelada() {
        $consulta = ("SELECT CanceladaSAT FROM c_factura WHERE Folio='" . $this->folio . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
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

    public function getGrupoEmpresa() {
        return $this->GrupoEmpresa;
    }

    public function getEmpresa() {
        return $this->Empresa;
    }

    public function setGrupoEmpresa($GrupoEmpresa) {
        $this->GrupoEmpresa = $GrupoEmpresa;
    }

    public function setEmpresa($Empresa) {
        $this->Empresa = $Empresa;
    }

}

?>
