<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
include_once("FacturaProveedor.class.php");

class PagoParcialProveedor {
    private $id_pago;
    private $id_factura;
    private $referencia;
    private $observaciones;
    private $importe;
    private $fechapago;
    private $cuentaBancaria;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;

    public function getTabla() {
        $consulta = "SELECT f.IdFacturaProveedor,f.Folio,pp.id_pago,DATE(pp.fechapago) AS fechapago,
            CONCAT('$', FORMAT(pp.importe, 2)) AS importeFormato,pp.importe, pp.observaciones,pp.referencia,p.NombreComercial
            FROM c_factura_proveedor AS f 
            INNER JOIN c_pagosparciales_proveedor AS pp ON pp.id_factura=f.IdFacturaProveedor 
            LEFT JOIN c_proveedor as p ON p.ClaveProveedor=f.IdEmisor 
            WHERE f.IdFacturaProveedor='$this->id_factura';";        
        $catalogo = new Catalogo();     
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getDatosbyFactura() {
        $consulta = ("SELECT f.IdFacturaProveedor,f.Folio,pp.id_pago,DATE(pp.fechapago) AS fechapago,pp.importe,pp.observaciones,
            pp.referencia,p.NombreComercial,e.RazonSocial,
            f.Total AS Total,
            f.Total-SUM(pp.importe) AS Pagado
            FROM c_factura_proveedor AS f
            LEFT JOIN c_pagosparciales_proveedor AS pp ON pp.id_factura=f.IdFacturaProveedor 
            LEFT JOIN c_proveedor as p ON p.ClaveProveedor=f.IdEmisor 
            LEFT JOIN c_datosfacturacionempresa AS e ON e.IdDatosFacturacionEmpresa=f.IdReceptor 
            WHERE f.IdFacturaProveedor='" . $this->id_factura . "'
            GROUP BY f.IdFacturaProveedor;");
        $catalogo = new Catalogo();         
        
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getRegistrobyID() {
        $consulta = "SELECT id_factura ,referencia,observaciones, importe,DATE(fechapago) AS fechapago,
            'Ninguna' AS Pantalla,NOW() AS FechaUltimaModificacion,'' AS UsuarioUltimaModificacion,NOW() AS FechaCreacion,'' AS UsuarioCreacion 
            FROM c_pagosparciales_proveedor WHERE id_pago='$this->id_pago';";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_factura = $rs['id_factura'];
            $this->referencia = $rs['referencia'];
            $this->observaciones = $rs['observaciones'];
            $this->importe = $rs['importe'];
            $this->fechapago = $rs['fechapago'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }        
        return false;
    }
    
    public function updateRegistro() {
        $factura = new FacturaProveedor();
        $ImportePorPagar = "null";
        if($factura->getRegistroById($this->id_factura)){
            $ImportePorPagar = (float)$factura->getTotal() - (float)  $this->importe;
        }
        if(!isset($this->importe) || $this->importe==""){
            $this->importe = "null";
        }
        if(!isset($this->cuentaBancaria) || $this->cuentaBancaria==""){
                $this->cuentaBancaria = "null";
            }
        $consulta = "UPDATE c_pagosparciales_proveedor SET id_factura='" . $this->id_factura . "',referencia='" . $this->referencia . "',
            observaciones='" . $this->observaciones . "',ImportePorPagar=$ImportePorPagar,
            importe='" . $this->importe . "',fechapago='" . $this->fechapago . "',idCuentaBancaria = $this->cuentaBancaria  WHERE id_pago='" . $this->id_pago . "'";            
        $catalogo = new Catalogo(); 
        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function nuevoRegistro() {
        $factura = new FacturaProveedor();
        if(!empty($this->empresa)){
            $factura->setEmpresa($this->empresa);
        }
        $ImportePorPagar = "null";
        if($factura->getRegistroById($this->id_factura)){
            $ImportePorPagar = (float)$factura->getTotal() - (float)  $this->importe;
        }
        if(!isset($this->importe) || $this->importe==""){
            $this->importe = "null";
        }

        if($this->fechapago != "NOW()"){
            $this->fechapago = "'$this->fechapago'";
        }
        if(!isset($this->cuentaBancaria) || $this->cuentaBancaria==""){
                $this->cuentaBancaria = "null";
        }
        $consulta = "INSERT INTO c_pagosparciales_proveedor(id_factura,referencia,observaciones,importe,ImportePorPagar,fechapago,idCuentaBancaria,UsuarioCreacion,FechaCreacion)
            VALUES('" . $this->id_factura . "','" . $this->referencia . "','" . $this->observaciones . "','" . $this->importe . "',$ImportePorPagar," . $this->fechapago . "," .$this->cuentaBancaria. ",'" . $this->UsuarioCreacion . "',NOW());";
        $catalogo = new Catalogo();   
        if(!empty($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        //echo $consulta;
        $query = $catalogo->obtenerLista($consulta);   
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_pagosparciales_proveedor WHERE id_pago = '" . $this->id_pago . "'");
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }
    
    public function verificaPagoMayor($pago, $idFactura, $idPago){
        if($idPago != "0"){
            $restarPago = " - (SELECT importe FROM c_pagosparciales_proveedor WHERE id_pago = $idPago) ";
        }else{
            $restarPago = "";
        }
        $consulta = "SELECT 
            (CASE WHEN ISNULL(pp.id_factura) AND $pago > f.Total THEN 1
            WHEN !ISNULL(pp.id_factura) AND (SUM(pp.importe) $restarPago + $pago) > f.Total THEN 1
            ELSE 0 END) isMayor            
            FROM c_factura_proveedor AS f
            LEFT JOIN c_pagosparciales_proveedor AS pp ON pp.id_factura = f.IdFacturaProveedor
            WHERE f.IdFacturaProveedor = $idFactura GROUP BY f.IdFacturaProveedor;";
        $catalogo = new Catalogo();
        if(!empty($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        //echo $consulta;
        while($rs = mysql_fetch_array($result)){
            if($rs['isMayor'] == "1"){
                return true;
            }
        }
        return false;
    }
    
    function getCuentaBancaria() {
        return $this->cuentaBancaria;
    }

    function setCuentaBancaria($cuentaBancaria) {
        $this->cuentaBancaria = $cuentaBancaria;
    }
    
    function getId_pago() {
        return $this->id_pago;
    }

    function getId_factura() {
        return $this->id_factura;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function getImporte() {
        return $this->importe;
    }

    function getFechapago() {
        return $this->fechapago;
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

    function setId_pago($id_pago) {
        $this->id_pago = $id_pago;
    }

    function setId_factura($id_factura) {
        $this->id_factura = $id_factura;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setFechapago($fechapago) {
        $this->fechapago = $fechapago;
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
    
    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}
