<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class ParametroLectura {

    private $Id_parametro;
    private $ClaveAnexoTecnico;
    private $Ver_equipo;
    private $Mostrar_area;
    private $Numero_proveedor;
    private $Numero_orden;
    private $Observaciones_dentro_xml;
    private $Observaciones_fuera_xml;
    private $Resaltar_periodo;
    private $Rentas_lecturas;
    private $Factura_renta_adelantada;
    private $Dir_reporte;
    private $MostrarImporteCero;
    private $MostrarEncabezadoServicio;
    private $Agrupar_Color;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $Dividir_factura;
    private $Agrupar_factura;
    private $Dividir_Color;
    private $Mostrar_Serie;
    private $Mostrar_Lecturas;
    private $MostrarModelo;
    private $Agrupar_Renta;
    private $MostrarPeriodo;
    private $MostrarLocalidad;
    private $HistoricoFacturacion;
    private $FechaInstalacion;
    private $IdProductoSATRenta;
    private $IdProductoSATImpresion;

    public function getRegistroById($clave) {
        $consulta = ("SELECT * FROM c_parametro_lectura WHERE ClaveAnexoTecnico='$clave';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->Id_parametro = $rs['Id_parametro'];
            $this->ClaveAnexoTecnico = $rs['ClaveAnexoTecnico'];
            $this->Ver_equipo = $rs['Ver_equipo'];
            $this->Mostrar_area = $rs['Mostrar_area'];
            $this->Numero_proveedor = $rs['Numero_proveedor'];
            $this->Numero_orden = $rs['Numero_orden'];
            $this->Observaciones_dentro_xml = $rs['Observaciones_dentro_xml'];
            $this->Observaciones_fuera_xml = $rs['Observaciones_fuera_xml'];
            $this->Resaltar_periodo = $rs['Resaltar_periodo'];
            $this->Rentas_lecturas = $rs['Rentas_lecturas'];
            $this->Factura_renta_adelantada = $rs['Factura_renta_adelantada'];
            $this->Dir_reporte = $rs['Dir_reporte'];
            $this->Agrupar_todos_cc = $rs['Agrupar_todos_cc'];
            $this->MostrarImporteCero = $rs['MostrarImporteCero'];
            $this->MostrarEncabezadoServicio = $rs['MostrarEncabezadoServicio'];
            $this->Agrupar_Color = $rs['Agrupar_Color'];
            $this->Dividir_Color = $rs['Dividir_Color'];
            $this->Dividir_factura = $rs['Dividir_factura'];
            $this->Agrupar_factura = $rs['Agrupar_factura'];
            $this->Mostrar_Serie = $rs['Mostrar_Serie'];
            $this->MostrarModelo = $rs['Mostrar_Modelo'];
            $this->Mostrar_Lecturas = $rs['Mostrar_Lecturas'];
            $this->Agrupar_Renta = $rs['Agrupar_Renta'];
            $this->MostrarPeriodo = $rs['MostrarPeriodo'];
            $this->MostrarLocalidad = $rs['MostrarLocalidad'];
            $this->HistoricoFacturacion = $rs['HistoricoFacturacion'];
            $this->FechaInstalacion = $rs['FechaInstalacion'];
            $this->IdProductoSATRenta = $rs['IdProductoSATRenta'];
            $this->IdProductoSATImpresion = $rs['IdProductoSATImpresion'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function getIDByClave($clave) {
        $consulta = ("SELECT * FROM c_parametro_lectura WHERE ClaveAnexoTecnico='$clave';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->Id_parametro = $rs['Id_parametro'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = "INSERT INTO c_parametro_lectura(ClaveAnexoTecnico, Ver_equipo,Mostrar_area, Numero_proveedor,Numero_orden,Observaciones_dentro_xml,
                Observaciones_fuera_xml,Resaltar_periodo,Rentas_lecturas,Factura_renta_adelantada,Dir_reporte,UsuarioCreacion,
                FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Agrupar_todos_cc,MostrarImporteCero,
                MostrarEncabezadoServicio,Agrupar_Color,Dividir_factura,Dividir_Color,Agrupar_factura,Mostrar_Serie,Mostrar_Modelo,
                Mostrar_Lecturas,Agrupar_Renta,MostrarPeriodo,MostrarLocalidad,HistoricoFacturacion,FechaInstalacion,IdProductoSATRenta,IdProductoSATImpresion) 
                VALUES('$this->ClaveAnexoTecnico','$this->Ver_equipo','$this->Mostrar_area','$this->Numero_proveedor','$this->Numero_orden','$this->Observaciones_dentro_xml',"
                . "'$this->Observaciones_fuera_xml','$this->Resaltar_periodo','$this->Rentas_lecturas','$this->Factura_renta_adelantada','$this->Dir_reporte',
                '$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla','$this->Agrupar_todos_cc','$this->MostrarImporteCero',
                '$this->MostrarEncabezadoServicio','$this->Agrupar_Color','$this->Dividir_factura','$this->Dividir_Color','$this->Agrupar_factura','$this->Mostrar_Serie','$this->MostrarModelo',
                $this->Mostrar_Lecturas,'$this->Agrupar_Renta',$this->MostrarPeriodo,$this->MostrarLocalidad,$this->HistoricoFacturacion,$this->FechaInstalacion,$this->IdProductoSATRenta,$this->IdProductoSATImpresion);";
        //echo $consulta;
        $catalogo = new Catalogo();
        $this->Id_parametro = $catalogo->insertarRegistro($consulta);
        if ($this->Id_parametro != NULL && $this->Id_parametro != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_parametro_lectura SET Ver_equipo = '$this->Ver_equipo', Mostrar_area = '$this->Mostrar_area',Numero_proveedor = '$this->Numero_proveedor', Numero_orden = '$this->Numero_orden',Observaciones_dentro_xml = '$this->Observaciones_dentro_xml', Observaciones_fuera_xml = '$this->Observaciones_fuera_xml',
           Resaltar_periodo = '$this->Resaltar_periodo', Rentas_lecturas = '$this->Rentas_lecturas', Factura_renta_adelantada = '$this->Factura_renta_adelantada',Dir_reporte = '$this->Dir_reporte',
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla',MostrarImporteCero='$this->MostrarImporteCero',MostrarEncabezadoServicio='$this->MostrarEncabezadoServicio',Agrupar_Color='$this->Agrupar_Color',
            Dividir_factura='$this->Dividir_factura',Dividir_Color='$this->Dividir_Color',Agrupar_factura='$this->Agrupar_factura',
            Mostrar_Serie='$this->Mostrar_Serie',Agrupar_Renta='$this->Agrupar_Renta', Mostrar_Modelo='$this->MostrarModelo',
            Mostrar_Lecturas = '$this->Mostrar_Lecturas', MostrarPeriodo = $this->MostrarPeriodo, MostrarLocalidad = $this->MostrarLocalidad,
            HistoricoFacturacion = $this->HistoricoFacturacion , FechaInstalacion = $this->FechaInstalacion, IdProductoSATRenta = $this->IdProductoSATRenta, IdProductoSATImpresion = $this->IdProductoSATImpresion 
           WHERE Id_parametro = '$this->Id_parametro';");
        //echo $consulta;
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId_parametro() {
        return $this->Id_parametro;
    }

    public function getClaveAnexoTecnico() {
        return $this->ClaveAnexoTecnico;
    }

    public function getVer_equipo() {
        return $this->Ver_equipo;
    }

    public function getMostrar_area() {
        return $this->Mostrar_area;
    }

    public function getNumero_proveedor() {
        return $this->Numero_proveedor;
    }

    public function getNumero_orden() {
        return $this->Numero_orden;
    }

    public function getObservaciones_dentro_xml() {
        return $this->Observaciones_dentro_xml;
    }

    public function getObservaciones_fuera_xml() {
        return $this->Observaciones_fuera_xml;
    }

    public function getResaltar_periodo() {
        return $this->Resaltar_periodo;
    }

    public function getRentas_lecturas() {
        return $this->Rentas_lecturas;
    }

    public function getFactura_renta_adelantada() {
        return $this->Factura_renta_adelantada;
    }

    public function getDir_reporte() {
        return $this->Dir_reporte;
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

    public function setId_parametro($Id_parametro) {
        $this->Id_parametro = $Id_parametro;
    }

    public function setClaveAnexoTecnico($ClaveAnexoTecnico) {
        $this->ClaveAnexoTecnico = $ClaveAnexoTecnico;
    }

    public function setVer_equipo($Ver_equipo) {
        $this->Ver_equipo = $Ver_equipo;
    }

    public function setMostrar_area($Mostrar_area) {
        $this->Mostrar_area = $Mostrar_area;
    }

    public function setNumero_proveedor($Numero_proveedor) {
        $this->Numero_proveedor = $Numero_proveedor;
    }

    public function setNumero_orden($Numero_orden) {
        $this->Numero_orden = $Numero_orden;
    }

    public function setObservaciones_dentro_xml($Observaciones_dentro_xml) {
        $this->Observaciones_dentro_xml = $Observaciones_dentro_xml;
    }

    public function setObservaciones_fuera_xml($Observaciones_fuera_xml) {
        $this->Observaciones_fuera_xml = $Observaciones_fuera_xml;
    }

    public function setResaltar_periodo($Resaltar_periodo) {
        $this->Resaltar_periodo = $Resaltar_periodo;
    }

    public function setRentas_lecturas($Rentas_lecturas) {
        $this->Rentas_lecturas = $Rentas_lecturas;
    }

    public function setFactura_renta_adelantada($Factura_renta_adelantada) {
        $this->Factura_renta_adelantada = $Factura_renta_adelantada;
    }

    public function setDir_reporte($Dir_reporte) {
        $this->Dir_reporte = $Dir_reporte;
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

    public function getAgrupar_todos_cc() {
        return $this->Agrupar_todos_cc;
    }

    public function getMostrarImporteCero() {
        return $this->MostrarImporteCero;
    }

    public function setMostrarImporteCero($MostrarImporteCero) {
        $this->MostrarImporteCero = $MostrarImporteCero;
    }

    public function getMostrarEncabezadoServicio() {
        return $this->MostrarEncabezadoServicio;
    }

    public function setMostrarEncabezadoServicio($MostrarEncabezadoServicio) {
        $this->MostrarEncabezadoServicio = $MostrarEncabezadoServicio;
    }

    public function getAgrupar_Color() {
        return $this->Agrupar_Color;
    }

    public function setAgrupar_Color($Agrupar_Color) {
        $this->Agrupar_Color = $Agrupar_Color;
    }

    public function getDividir_factura() {
        return $this->Dividir_factura;
    }

    public function getAgrupar_factura() {
        return $this->Agrupar_factura;
    }

    public function getDividir_Color() {
        return $this->Dividir_Color;
    }

    public function setDividir_factura($Dividir_factura) {
        $this->Dividir_factura = $Dividir_factura;
    }

    public function setAgrupar_factura($Agrupar_factura) {
        $this->Agrupar_factura = $Agrupar_factura;
    }

    public function setDividir_Color($Dividir_Color) {
        $this->Dividir_Color = $Dividir_Color;
    }

    public function getMostrar_Serie() {
        return $this->Mostrar_Serie;
    }

    public function setMostrar_Serie($Mostrar_Serie) {
        $this->Mostrar_Serie = $Mostrar_Serie;
    }

    public function getAgrupar_Renta() {
        return $this->Agrupar_Renta;
    }

    public function setAgrupar_Renta($Agrupar_Renta) {
        $this->Agrupar_Renta = $Agrupar_Renta;
    }

    public function getMostrarModelo() {
        return $this->MostrarModelo;
    }

    public function setMostrarModelo($MostrarModelo) {
        $this->MostrarModelo = $MostrarModelo;
    }

    function getMostrar_Lecturas() {
        return $this->Mostrar_Lecturas;
    }

    function setMostrar_Lecturas($Mostrar_Lecturas) {
        $this->Mostrar_Lecturas = $Mostrar_Lecturas;
    }

    function getMostrarPeriodo() {
        return $this->MostrarPeriodo;
    }

    function setMostrarPeriodo($MostrarPeriodo) {
        $this->MostrarPeriodo = $MostrarPeriodo;
    }

    function getMostrarLocalidad() {
        return $this->MostrarLocalidad;
    }

    function setMostrarLocalidad($MostrarLocalidad) {
        $this->MostrarLocalidad = $MostrarLocalidad;
    }

    function getHistoricoFacturacion() {
        return $this->HistoricoFacturacion;
    }

    function setHistoricoFacturacion($HistoricoFacturacion) {
        $this->HistoricoFacturacion = $HistoricoFacturacion;
    }
    
    function getFechaInstalacion() {
        return $this->FechaInstalacion;
    }

    function setFechaInstalacion($FechaInstalacion) {
        $this->FechaInstalacion = $FechaInstalacion;
    }
    
    function getIdProductoSATRenta() {
        return $this->IdProductoSATRenta;
    }

    function getIdProductoSATImpresion() {
        return $this->IdProductoSATImpresion;
    }

    function setIdProductoSATRenta($IdProductoSATRenta) {
        $this->IdProductoSATRenta = $IdProductoSATRenta;
    }

    function setIdProductoSATImpresion($IdProductoSATImpresion) {
        $this->IdProductoSATImpresion = $IdProductoSATImpresion;
    }
}
