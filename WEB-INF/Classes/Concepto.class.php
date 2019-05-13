<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once ("ClaveProdServ.class.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Concepto {

    private $idConcepto;
    private $Cantidad;
    private $Unidad;
    private $Descripcion;
    private $PrecioUnitario;
    private $Descuento;    
    private $Porcentaje;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $idFactura;
    private $conceptos_array;
    private $Tipo;
    private $id_articulo;
    private $encabezado;
    private $obs_in = "";
    private $empresa;
    private $IdEmpresaProductoSAT;

    public function nuevoRegistro() {
        $encabezado = 0;
        if (isset($this->Unidad) && !empty($this->Unidad)) {
            $this->Unidad = "'$this->Unidad'";
        }else{
            $this->Unidad = "NULL";
        }
        if (isset($this->encabezado) && $this->encabezado != "") {
            $encabezado = $this->encabezado;
        }
        if(!isset($this->IdEmpresaProductoSAT) || empty($this->IdEmpresaProductoSAT)){
            $this->IdEmpresaProductoSAT = "NULL";
        }
        if(!isset($this->Descuento) || empty($this->Descuento)){
            $this->Descuento = "NULL";
        }
        if(!isset($this->Porcentaje) || empty($this->Porcentaje)){
            $this->Porcentaje = "0";
        }
        $consulta = "INSERT INTO c_conceptos(idFactura,Cantidad,Unidad,Descripcion,PrecioUnitario,Descuento,Porcentaje,Encabezado,UsuarioCreacion,
            FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Tipo,id_articulo,IdEmpresaProductoSAT)
            VALUES('" . $this->idFactura . "','" . $this->Cantidad . "',$this->Unidad,'" . $this->Descripcion . "','" 
            . $this->PrecioUnitario . "',$this->Descuento,$this->Porcentaje, $encabezado,'" . $this->UsuarioCreacion . "',
            NOW(),'" . $this->UsuarioUltimaModificacion . 
            "',NOW(),'" . $this->Pantalla . "'," . $this->Tipo . ",'" . $this->id_articulo . "',$this->IdEmpresaProductoSAT);";
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $this->idConcepto = $catalogo->insertarRegistro($consulta);
        if ($this->idConcepto != NULL && $this->idConcepto != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updateRegistro() {
        $encabezado = 0;
        if (isset($this->encabezado) && $this->encabezado != "") {
            $encabezado = $this->encabezado;
        }
        if (isset($this->Unidad) && !empty($this->Unidad)) {
            $this->Unidad = "$this->Unidad'";
        }else{
            $this->Unidad = "NULL";
        }
        if(!isset($this->IdEmpresaProductoSAT) || empty($this->IdEmpresaProductoSAT)){
            $this->IdEmpresaProductoSAT = "NULL";
        }
        if(!isset($this->Descuento) || empty($this->Descuento)){
            $this->Descuento = "NULL";
        }
        if(!isset($this->Porcentaje) || empty($this->Porcentaje)){
            $this->Porcentaje = "0";
        }
        $consulta = ("UPDATE c_conceptos SET Cantidad='" . $this->Cantidad . "',Unidad=$this->Unidad,Encabezado = $encabezado,
            IdEmpresaProductoSAT = $this->IdEmpresaProductoSAT,Descuento=$this->Descuento, Porcentaje = $this->Porcentaje,
            Descripcion='" . $this->Descripcion . "',PrecioUnitario='" . $this->PrecioUnitario . "',UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Tipo='" . $this->Tipo . "',id_articulo='" . $this->id_articulo . "',Pantalla='" . $this->Pantalla . "'
            WHERE idConcepto=" . $this->idConcepto);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_conceptos WHERE idConcepto=" . $this->idConcepto);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->idConcepto = $catalogo->insertarRegistro($consulta);
        if ($this->idConcepto != NULL && $this->idConcepto != 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getRegistrobyID() {
        $consulta = ("SELECT * FROM c_conceptos WHERE idConcepto=" . $this->idConcepto);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->Cantidad = $rs['Cantidad'];
            $this->Descripcion = $rs['Descripcion'];
            $this->PrecioUnitario = $rs['PrecioUnitario'];
            $this->Unidad = $rs['Unidad'];
            $this->idFactura = $rs['idFactura'];
            $this->id_articulo = $rs['id_articulo'];
            $this->Tipo = $rs['Tipo'];
            $this->encabezado = $rs['Encabezado'];
            $this->IdEmpresaProductoSAT = $rs['IdEmpresaProductoSAT'];
            $this->Descuento = $rs['Descuento'];
            $this->Porcentaje = $rs['Porcentaje'];
            return true;
        }
        return false;
    }

    /**
     * Regresa un resultset con todos los conceptos de la factura.
     * @param type $id
     * @return type
     */
    public function getConceptosByFactura($id) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "SELECT * FROM c_conceptos WHERE idFactura = $id;";
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function subtotalbyFactura($factura) {
        $consulta = ("SELECT * FROM c_conceptos WHERE idFactura=" . $factura);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $subtotal = 0;
        $this->conceptos_array = Array();
        while ($rs = mysql_fetch_array($query)) {
            $array = Array($rs['Cantidad'], $rs['Unidad'], str_replace('–', '-', trim($rs['Descripcion'])), round($rs['PrecioUnitario'], 2), round($rs['Cantidad'] * $rs['PrecioUnitario'], 2), $rs['Encabezado']);
            $subtotal = $subtotal + ($rs['Cantidad'] * $rs['PrecioUnitario']);
            array_push($this->conceptos_array, $array);
        }
        return round($subtotal, 2);
    }

    public function subtotalbyFacturaObs($factura) {
        $consulta = ("SELECT * FROM c_conceptos WHERE idFactura=" . $factura);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $subtotal = 0;
        $this->conceptos_array = Array();
        $count = mysql_num_rows($query);
        $i = 0;
        while ($rs = mysql_fetch_array($query)) {
            $floatVal = floatval($rs['Cantidad']);
            if($floatVal && intval($floatVal) != $floatVal){
                $cantidad = (number_format((float)$floatVal,4));        
            }else{
                $cantidad = (number_format((int)$floatVal));        
            }
            if ($i == ($count - 1)) {
                $array = Array($cantidad, $rs['Unidad'], str_replace('–', '-', trim($rs['Descripcion'])) . " " . $this->obs_in, round($rs['PrecioUnitario'], 2), round($rs['Cantidad'] * $rs['PrecioUnitario'], 2), $rs['Encabezado']);
            } else {
                $array = Array($cantidad, $rs['Unidad'], str_replace('–', '-', trim($rs['Descripcion'])), round($rs['PrecioUnitario'], 2), round($rs['Cantidad'] * $rs['PrecioUnitario'], 2), $rs['Encabezado']);
            }$i++;
            $subtotal = $subtotal + ($rs['Cantidad'] * $rs['PrecioUnitario']);
            array_push($this->conceptos_array, $array);
        }
        return round($subtotal, 2);
    }

    public function subtotalbyFacturaObs33($factura) {
        $consulta = ("SELECT c.*,cps.ClaveProdServ,um.ClaveUnidad,um.UnidadMedida 
            FROM c_conceptos c
            LEFT JOIN k_empresaproductosat AS eps ON eps.IdEmpresaProductoSAT = c.IdEmpresaProductoSAT
            LEFT JOIN c_claveprodserv cps ON cps.IdProdServ = eps.IdClaveProdServ
            LEFT JOIN c_unidadmedidaSAT AS um ON um.IdUnidadMedida = eps.IdUnidadMedida
            WHERE idFactura=" . $factura);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $subtotal = 0;
        $this->conceptos_array = Array();
        $count = mysql_num_rows($query);
        $i = 0;
        while ($rs = mysql_fetch_array($query)) {
            $floatVal = floatval($rs['Cantidad']);
            if($floatVal && intval($floatVal) != $floatVal){
                $cantidad = (number_format((float)$floatVal,4));        
            }else{
                $cantidad = (number_format((int)$floatVal));        
            }
            
            if ($i == ($count - 1)) {
                $array = Array($cantidad, $rs['ClaveProdServ'] ,$rs['ClaveUnidad'],$rs['UnidadMedida'], str_replace('–', '-', trim(preg_replace('/\s+/', ' ',$rs['Descripcion']))) . " " . $this->obs_in, $rs['PrecioUnitario'], $rs['Cantidad'] * $rs['PrecioUnitario'], $rs['Encabezado'], $rs['Descuento'], $rs['Porcentaje']);
            } else {
                $array = Array($cantidad, $rs['ClaveProdServ'] ,$rs['ClaveUnidad'],$rs['UnidadMedida'], str_replace('–', '-', trim(preg_replace('/\s+/', ' ',$rs['Descripcion']))), $rs['PrecioUnitario'], $rs['Cantidad'] * $rs['PrecioUnitario'], $rs['Encabezado'], $rs['Descuento'], $rs['Porcentaje']);
            }
            //print_r($array);
            $i++;
            $subtotal = $subtotal + ($rs['Cantidad'] * $rs['PrecioUnitario']);
            array_push($this->conceptos_array, $array);
        }
        return round($subtotal, 2);
    }
    
    public function subtotalbyFacturaObsPDF($factura) {
        $consulta = ("SELECT * FROM c_conceptos WHERE idFactura=" . $factura);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $subtotal = 0;
        $this->conceptos_array = Array();
        $count = mysql_num_rows($query);
        $i = 0;
        while ($rs = mysql_fetch_array($query)) {
            $floatVal = floatval($rs['Cantidad']);
            if($floatVal && intval($floatVal) != $floatVal){
                $cantidad = (number_format((float)$floatVal,4));        
            }else{
                $cantidad = (number_format((int)$floatVal));        
            }
            if ($i == ($count - 1)) {
                $cadena = trim($rs['Descripcion']);
                $cadena2 = $this->agregarSaltos($cadena);
                $array = Array($cantidad, $rs['Unidad'], str_replace('–', '-', $cadena2) . " " . $this->obs_in, round($rs['PrecioUnitario'], 2), round($rs['Cantidad'] * $rs['PrecioUnitario'], 2), $rs['Encabezado']);
            } else {
                $cadena = trim($rs['Descripcion']);
                $cadena2 = $this->agregarSaltos($cadena);
                $array = Array($cantidad, $rs['Unidad'], str_replace('–', '-', $cadena2) , round($rs['PrecioUnitario'], 2), round($rs['Cantidad'] * $rs['PrecioUnitario'], 2), $rs['Encabezado']);
            }$i++;
            $subtotal = $subtotal + ($rs['Cantidad'] * $rs['PrecioUnitario']);
            array_push($this->conceptos_array, $array);
        }
        return round($subtotal, 2);
    }
    
    public function agregarSaltos($cadena)
    {   
        $separada = explode("MODELO:", $cadena);
        $final = $separada[0];
        
        for($i = 1; $i < count($separada); $i++){
            $final .= "\nMODELO: " . $separada[$i];
        }
        return $final;
    }
    
    public function getObs_in() {
        return $this->obs_in;
    }

    public function setObs_in($obs_in) {
        $this->obs_in = $obs_in;
    }

    public function getTipo() {
        return $this->Tipo;
    }

    public function getId_articulo() {
        return $this->id_articulo;
    }

    public function setTipo($Tipo) {
        $this->Tipo = $Tipo;
    }

    public function setId_articulo($id_articulo) {
        $this->id_articulo = $id_articulo;
    }

    public function getConceptos_array() {
        return $this->conceptos_array;
    }

    public function getIdConcepto() {
        return $this->idConcepto;
    }

    public function getCantidad() {
        return $this->Cantidad;
    }

    public function getUnidad() {
        return $this->Unidad;
    }

    public function getDescripcion() {
        return $this->Descripcion;
    }

    public function getPrecioUnitario() {
        return $this->PrecioUnitario;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function getIdFactura() {
        return $this->idFactura;
    }

    public function setIdConcepto($idConcepto) {
        $this->idConcepto = $idConcepto;
    }

    public function setCantidad($Cantidad) {
        $this->Cantidad = $Cantidad;
    }

    public function setUnidad($Unidad) {
        $this->Unidad = $Unidad;
    }

    public function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    public function setPrecioUnitario($PrecioUnitario) {
        $this->PrecioUnitario = $PrecioUnitario;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function setIdFactura($idFactura) {
        $this->idFactura = $idFactura;
    }

    public function getEncabezado() {
        return $this->encabezado;
    }

    public function setEncabezado($encabezado) {
        $this->encabezado = $encabezado;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    function getIdEmpresaProductoSAT() {
        return $this->IdEmpresaProductoSAT;
    }

    function setIdEmpresaProductoSAT($IdEmpresaProductoSAT) {
        $this->IdEmpresaProductoSAT = $IdEmpresaProductoSAT;
    }

    function getDescuento() {
        return $this->Descuento;
    }

    function getPorcentaje() {
        return $this->Porcentaje;
    }

    function setDescuento($Descuento) {
        $this->Descuento = $Descuento;
    }

    function setPorcentaje($Porcentaje) {
        $this->Porcentaje = $Porcentaje;
    }
}

?>