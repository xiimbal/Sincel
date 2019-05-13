<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");
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

    public function nuevoRegistro() {
        $this->conn = new Conexion();
        $encabezado = 0;
        if (isset($this->encabezado) && $this->encabezado != "") {
            $encabezado = $this->encabezado;
        }
        $consulta = "INSERT INTO c_conceptos(idFactura,Cantidad,Unidad,Descripcion,PrecioUnitario,Encabezado,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Tipo,id_articulo)
            VALUES('" . $this->idFactura . "','" . $this->Cantidad . "','" . $this->Unidad . "','" . $this->Descripcion . "','" . $this->PrecioUnitario . "',
                $encabezado,'" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "'," . $this->Tipo . ",'" . $this->id_articulo . "');";
        //echo $consulta;
        $query = mysql_query($consulta);
        $this->idConcepto = mysql_insert_id();
        $this->conn->Desconectar();
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function updateRegistro() {
        $this->conn = new Conexion();
        $encabezado = 0;
        if (isset($this->encabezado) && $this->encabezado != "") {
            $encabezado = $this->encabezado;
        }
        $query = mysql_query("UPDATE c_conceptos SET Cantidad='" . $this->Cantidad . "',Unidad='" . $this->Unidad . "',Encabezado = $encabezado,
            Descripcion='" . $this->Descripcion . "',PrecioUnitario='" . $this->PrecioUnitario . "',UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Tipo='" . $this->Tipo . "',id_articulo='" . $this->id_articulo . "',Pantalla='" . $this->Pantalla . "'
            WHERE idConcepto=" . $this->idConcepto);
        $this->conn->Desconectar();
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteRegistro() {
        $this->conn = new Conexion();
        $query = mysql_query("DELETE FROM c_conceptos WHERE idConcepto=" . $this->idConcepto);
        $this->idConcepto = mysql_insert_id();
        $this->conn->Desconectar();
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getRegistrobyID() {
        $this->conn = new Conexion();
        $query = mysql_query("SELECT * FROM c_conceptos WHERE idConcepto=" . $this->idConcepto);
        $this->conn->Desconectar();
        if ($rs = mysql_fetch_array($query)) {
            $this->Cantidad = $rs['Cantidad'];
            $this->Descripcion = $rs['Descripcion'];
            $this->PrecioUnitario = $rs['PrecioUnitario'];
            $this->Unidad = $rs['Unidad'];
            $this->id_articulo = $rs['id_articulo'];
            $this->Tipo = $rs['Tipo'];
            $this->encabezado = $rs['Encabezado'];
        } else {
            return false;
        }
    }
         
    /**
     * Regresa un resultset con todos los conceptos de la factura.
     * @param type $id
     * @return type
     */
    public function getConceptosByFactura($id){
        $catalogo = new Catalogo();
        $consulta = "SELECT * FROM c_conceptos WHERE idFactura = $id;";
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function subtotalbyFactura($factura) {
        $this->conn = new Conexion();
        $query = mysql_query("SELECT * FROM c_conceptos WHERE idFactura=" . $factura);
        $this->conn->Desconectar();
        $subtotal = 0;
        $this->conceptos_array = Array();
        while ($rs = mysql_fetch_array($query)) {
            $array = Array($rs['Cantidad'], $rs['Unidad'], str_replace('SERIE','\n SERIE',str_replace('–','-',trim($rs['Descripcion']))), round($rs['PrecioUnitario'],2), round($rs['Cantidad'] * $rs['PrecioUnitario'],2), $rs['Encabezado']);
            $subtotal = $subtotal + ($rs['Cantidad'] * $rs['PrecioUnitario']);
            array_push($this->conceptos_array, $array);
        }
        return round($subtotal,2);
    }

    public function subtotalbyFacturaObs($factura) {
        $this->conn = new Conexion();
        $query = mysql_query("SELECT * FROM c_conceptos WHERE idFactura=" . $factura);
        $this->conn->Desconectar();
        $subtotal = 0;
        $this->conceptos_array = Array();
        $count = mysql_num_rows($query);
        $i = 0;
        while ($rs = mysql_fetch_array($query)) {
            if ($i == ($count - 1)) {
                $array = Array($rs['Cantidad'], $rs['Unidad'], str_replace('SERIE','\n SERIE',str_replace('–','-',trim($rs['Descripcion']))) . " " . $this->obs_in, round($rs['PrecioUnitario'],2), round($rs['Cantidad'] * $rs['PrecioUnitario'],2), $rs['Encabezado']);
            } else {
                $array = Array($rs['Cantidad'], $rs['Unidad'], str_replace('SERIE','\n SERIE',str_replace('–','-',trim($rs['Descripcion']))), round($rs['PrecioUnitario'],2), round($rs['Cantidad'] * $rs['PrecioUnitario'],2), $rs['Encabezado']);
            }$i++;
            $subtotal = $subtotal + ($rs['Cantidad'] * $rs['PrecioUnitario']);
            array_push($this->conceptos_array, $array);
        }
        return round($subtotal,2);
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

}

?>