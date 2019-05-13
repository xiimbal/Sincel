<?php

include_once("Catalogo.class.php");

class ReporteFacturacionProveedor {

    private $RFCProveedor;
    private $fechaInicio;
    private $fechaFin;
    private $proveedor;
    private $estado;
    private $folio;
    
    public function getTabla() {
        $tiene_filtro = false;
        $where = "";
        $having = null;
        if (isset($this->RFCProveedor) && $this->RFCProveedor != "") {
            $tiene_filtro = true;
            $where .= "WHERE p.RFC='" . $this->RFCProveedor . "'";
        }
        if (isset($this->fechaInicio) && $this->fechaInicio != "") {
            $tiene_filtro = true;
            if ($where != "") {
                $where .= " AND f.Fecha >= '" . $this->fechaInicio."'";
            } else {
                $where .= "WHERE f.Fecha >= '" . $this->fechaInicio."'";
            }
        }
        if (isset($this->fechaFin) && $this->fechaFin != "") {
            $tiene_filtro = true;
            if ($where != "") {
                $where .= " AND f.Fecha <= '" . $this->fechaFin."'";
            } else {
                $where .= "WHERE f.Fecha <= '" . $this->fechaFin."'";
            }
        }
        if (isset($this->estado) && $this->estado != "") {
            $having = " HAVING";
            foreach ($this->estado as $value)
            {
                if(strcmp($having, " HAVING") == 0)
                {
                    if($value == 1){
                        $having .= " pagadoSN = 'No'";
                    }
                    else{
                        $having .= " pagadoSN = 'Si'";
                    }
                }else{
                    if($value == 1){
                        $having .= " OR pagadoSN = 'No'";
                    }
                    else{
                        $having .= " OR pagadoSN = 'Si'";
                    }
                }       
            }
        }
        if (isset($this->folio) && $this->folio != "") {
            $tiene_filtro = true;
            if ($where != "") {
                $where .= " AND f.Folio = '" . $this->folio . "'";
            } else {
                $where .= "WHERE f.Folio = '" . $this->folio . "'";
            }
        }
        
        $consulta = "SELECT f.Folio,
            (SELECT SUM(pp.importe) AS suma) AS pagado, 
            (GROUP_CONCAT(CONCAT('',CONCAT('$', FORMAT(pp.importe, 2)),' [',CAST(DATE(pp.FechaPago) AS CHAR),']  (', pp.Observaciones, ') (',pp.Referencia,') / ',pp.UsuarioCreacion) SEPARATOR ', <br/>')) AS pagos, 
            DATE(f.Fecha) AS FechaFacturacion,
            p.RFC as RFCProveedor,
            FORMAT((f.Total/1.16),2) AS subtotal,
            FORMAT(((f.Total/1.16)*.16),2) AS importe,
            FORMAT(f.Total,2) as Total,
            IF(SUM(pp.importe) = f.Total, 'Si', 'No') as pagadoSN,
            f.Total AS TotalSinFormato,
            p.NombreComercial as NombreProveedor,
            f.IdFacturaProveedor AS IdFactura,
            f.PathFactura AS PDF,
            f.PathXml AS XML,
            (CASE WHEN !ISNULL(pp.id_pago) THEN (SELECT MAX(FechaPago)FROM c_pagosparciales_proveedor WHERE id_factura = f.IdFacturaProveedor) ELSE f.Fecha END) AS FechaPago,
            f.IdOrdenCompra
            FROM c_factura_proveedor AS f  
            LEFT JOIN c_proveedor AS p ON p.ClaveProveedor = (SELECT MIN(ClaveProveedor) FROM c_proveedor WHERE ClaveProveedor = f.IdEmisor AND Activo = 1)           
            LEFT JOIN c_pagosparciales_proveedor AS pp ON pp.id_factura = f.IdFacturaProveedor 
            $where GROUP BY f.IdFacturaProveedor $having";
        if(!$tiene_filtro){/*Si no tiene filtro y se permiten pre-facturas(no es el excel)*/
            $consulta.=" ORDER BY FechaFacturacion DESC LIMIT 0,500";
        }
        //echo $consulta;
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);        
        return $query;
    }
    
    function getRFCProveedor() {
        return $this->RFCProveedor;
    }

    function getFechaInicio() {
        return $this->fechaInicio;
    }

    function getFechaFin() {
        return $this->fechaFin;
    }

    function getProveedor() {
        return $this->proveedor;
    }

    function getEstado() {
        return $this->estado;
    }

    function getFolio() {
        return $this->folio;
    }

    function setRFCProveedor($RFCProveedor) {
        $this->RFCProveedor = $RFCProveedor;
    }

    function setFechaInicio($fechaInicio) {
        $this->fechaInicio = $fechaInicio;
    }

    function setFechaFin($fechaFin) {
        $this->fechaFin = $fechaFin;
    }

    function setProveedor($proveedor) {
        $this->proveedor = $proveedor;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setFolio($folio) {
        $this->folio = $folio;
    }
    
}
