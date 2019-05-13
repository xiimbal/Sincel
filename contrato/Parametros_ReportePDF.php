<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

if (!isset($_GET['anexo']) || $_GET['anexo'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/ParametroLectura.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

if(isset($_GET['anexo'])){
    $anexo = $_GET['anexo'];
    $contrato = $_GET['contrato'];
    $cliente = $_GET['cliente'];
    $fecha = $_GET['fecha'];
    //Cambiamos el formato de la fecha para que sea igual al que se manda desde Facturas Lecturas
    $fecha = substr($fecha, 5, 2)."-".substr($fecha, 0, 4);
    $parametro = new ParametroLectura();
    $catalogo = new Catalogo();
    $Ver_equipo = 1;
    $Mostrar_area = "";
    $Numero_proveedor = "";
    $Numero_orden = "";
    $Observaciones_dentro_xml = "";
    $Observaciones_fuera_xml = "";
    $Resaltar_periodo = "";
    $Rentas_lecturas = "";
    $Factura_renta_adelantada = "";
    $Dir_reporte = "";
    $MostrarLecturas = "1";
    $MostrarImporteCero = "1";
    $MostrarEncabezadoServicio = "";
    $Agrupar_Color = "";
    $Dividir_Color = "";
    $dividir_factura = "0";
    $agrupar_factura = "0";
    $Mostrar_Serie = "";
    $MostrarModelo = "";
    $MostrarLocalidad = "";
    $HistoricoFacturacion = "0";
    $FechaInstalacion = "0";
    $Agrupar_Renta = "1";
    $MostrarPeriodo = "";
    $otro = "";
    $IdProductoSATRenta = 50951;
    $IdProductoSATImpresion = 51334;
    
    if ($parametro->getRegistroById($_GET['anexo'])) {
        $Ver_equipo = $parametro->getVer_equipo();
        $Mostrar_area = $parametro->getMostrar_area();
        $Numero_proveedor = $parametro->getNumero_proveedor();
        $Numero_orden = $parametro->getNumero_orden();
        $Observaciones_dentro_xml = $parametro->getObservaciones_dentro_xml();
        $Observaciones_fuera_xml = $parametro->getObservaciones_fuera_xml();
        $Resaltar_periodo = $parametro->getResaltar_periodo();
        $Rentas_lecturas = $parametro->getRentas_lecturas();
        $Factura_renta_adelantada = $parametro->getFactura_renta_adelantada();
        $Dir_reporte = $parametro->getDir_reporte();
        $MostrarImporteCero = $parametro->getMostrarImporteCero();
        $MostrarEncabezadoServicio = $parametro->getMostrarEncabezadoServicio();
        $Agrupar_Color = $parametro->getAgrupar_Color();
        $Dividir_Color = $parametro->getDividir_Color();
        $dividir_factura = $parametro->getDividir_factura();
        $agrupar_factura = $parametro->getAgrupar_factura();
        $Agrupar_Renta = $parametro->getAgrupar_Renta();
        $Mostrar_Serie = $parametro->getMostrar_Serie();
        $MostrarModelo = $parametro->getMostrarModelo();
        $MostrarLecturas = $parametro->getMostrar_Lecturas();
        $MostrarPeriodo = $parametro->getMostrarPeriodo();
        $MostrarLocalidad = $parametro->getMostrarLocalidad();
        $HistoricoFacturacion = $parametro->getHistoricoFacturacion();
        $FechaInstalacion = $parametro->getFechaInstalacion();
        $IdProductoSATRenta = $parametro->getIdProductoSATRenta();
        $IdProductoSATImpresion = $parametro->getIdProductoSATImpresion();
        $otro = "<input type='hidden' name='param_lect' id='param_lect' value'" . $parametro->getId_parametro() . "'/>";
    }
}
?>
<script type="text/javascript" src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="../resources/js/paginas/Parametros_ReportePDF.js"></script>
<form id="formParametrosReporte" name="formParametrosReporte" action="../reportes/ReporteLecturaPDF.php" method="POST">
    <input type="hidden" id="rentaSAT" name="rentaSAT" value="<?php echo $IdProductoSATRenta; ?>"/>
    <input type="hidden" id="impresionesSAT" name="impresionesSAT" value="<?php echo $IdProductoSATImpresion; ?>"/>
    <input type="hidden" id="dividir_factura" name="dividir_factura" value="<?php echo $dividir_factura; ?>" />
    <input type="hidden" id="agrupar_factura" name="agrupar_factura" value="<?php echo $agrupar_factura; ?>" />
    <input type="hidden" id="periodo_factura" name="periodo_factura" value="<?php echo $MostrarPeriodo; ?>" />
    <input type="hidden" id="rentas_lecturas" name="rentas_lecturas" value="<?php echo $Rentas_lecturas; ?>" />
    <input type="hidden" id="Dividir_Color" name="Dividir_Color" value="<?php echo $Dividir_Color; ?>" />
    <input type="hidden" id="Agrupar_Renta" name="Agrupar_Renta" value="<?php echo $Agrupar_Renta; ?>" />
    <input type="hidden" id="mostrar_area" name="mostrar_area" value="<?php echo $Mostrar_area; ?>" />
    <input type="hidden" id="Mostrar_Modelo" name="Mostrar_Modelo" value="<?php echo $MostrarModelo; ?>" />
    <input type="hidden" id="Mostrar_Serie" name="Mostrar_Serie" value="<?php echo $Mostrar_Serie; ?>" />
    <input type="hidden" id="MostrarLecturas" name="MostrarLecturas" value="<?php echo $MostrarLecturas; ?>" />
    <input type="hidden" id="MostrarLocalidad" name="MostrarLocalidad" value="<?php echo $MostrarLocalidad; ?>" />
    <input type="hidden" id="HistoricoFacturacion" name="HistoricoFacturacion" value="<?php echo $HistoricoFacturacion; ?>" />
    <input type="hidden" id="FechaInstalacion" name="FechaInstalacion" value="<?php echo $FechaInstalacion; ?>" />
    <input type="hidden" id="MostrarEncabezadoServicio" name="MostrarEncabezadoServicio" value="<?php echo $MostrarEncabezadoServicio; ?>" />
    <input type="hidden" id="MostrarImporteCero" name="MostrarImporteCero" value="<?php echo $MostrarImporteCero; ?>" />
    <input type="hidden" id="dir_rep" name="dir_rep" value="<?php echo $Dir_reporte; ?>" />
    <input type="hidden" id="fact_adel" name="fact_adel" value="<?php echo $Factura_renta_adelantada; ?>" />
    <input type="hidden" id="resal_perio" name="resal_perio" value="<?php echo $Resaltar_periodo; ?>" />
    <input type="hidden" id="num_orden" name="num_orden" value="<?php echo $Numero_orden; ?>" />
    <input type="hidden" id="num_prov" name="num_prov" value="<?php echo $Numero_proveedor; ?>" />
    <input type="hidden" id="obs_in_xml" name="obs_in_xml" value="<?php echo $Observaciones_dentro_xml; ?>" />
    <input type="hidden" id="obs_out_xml" name="obs_out_xml" value="<?php echo $Observaciones_fuera_xml; ?>" />
    <input type="hidden" id="fecha" name="fecha" value="<?php echo $fecha; ?>" />
    <?php
        /*************************** Encabezados de servicios ****************************/
        $query = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre FROM c_servicioim AS c WHERE c.Activo=1;");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
            FROM c_servicioim AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioIM
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $nombre = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $nombre = $rsp['NombreKP'];
                    $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                    $rsp = mysql_fetch_array($query2);
                }
            }
            if ($nombre == "") {
                $nombre = $rs['Nombre'];
            }
            echo "<input type='hidden' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
        }
    
        $query = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogim AS c WHERE c.Activo=1;");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
            FROM c_serviciogim AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGIM
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $nombre = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                    $nombre = $rsp['NombreKP'];
                    $rsp = mysql_fetch_array($query2);
                }
            }
            if ($nombre == "") {
                $nombre = $rs['Nombre'];
            }
            echo "<input type='hidden' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
        }
        
        $query = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciofa AS c WHERE c.Activo=1;");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
            FROM c_serviciofa AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioFA
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $nombre = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $nombre = $rsp['NombreKP'];
                    $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                    $rsp = mysql_fetch_array($query2);
                }
            }
            if ($nombre == "") {
                $nombre = $rs['Nombre'];
            }
            echo "<input type='hidden' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
        }
        
        $query = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogfa AS c WHERE c.Activo=1");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
            FROM c_serviciogfa AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGFA
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $nombre = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $nombre = $rsp['NombreKP'];
                    $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                    $rsp = mysql_fetch_array($query2);
                }
            }
            if ($nombre == "") {
                $nombre = $rs['Nombre'];
            }
            echo "<input type='hidden' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
        }
        
        /*************************** Unidades de servicio ****************************/
        $query = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre FROM c_servicioim AS c WHERE c.Activo=1");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
            FROM c_servicioim AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioIM
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $renta = "";
            $impresiones = "";
            $excedente = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $renta = $rsp['Renta'];
                    $impresiones = $rsp['Impresiones'];
                    $excedente = $rsp['Excedente'];
                    $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                    $rsp = mysql_fetch_array($query2);
                }
            }
            echo "<input type='hidden' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
            echo "<input type='hidden' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
            echo "<input type='hidden' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
        }
        
        $query = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogim AS c WHERE c.Activo=1 ;");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
            FROM c_serviciogim AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGIM
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $renta = "";
            $impresiones = "";
            $excedente = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $renta = $rsp['Renta'];
                    $impresiones = $rsp['Impresiones'];
                    $excedente = $rsp['Excedente'];
                    $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                    $rsp = mysql_fetch_array($query2);
                }
            }
            echo "<input type='hidden' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
            echo "<input type='hidden' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
            echo "<input type='hidden' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
        }
        
        $query = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciofa AS c WHERE c.Activo=1;");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
            FROM c_serviciofa AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioFA
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $renta = "";
            $impresiones = "";
            $excedente = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $renta = $rsp['Renta'];
                    $impresiones = $rsp['Impresiones'];
                    $excedente = $rsp['Excedente'];
                    $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                    $rsp = mysql_fetch_array($query2);
                }
            }
            echo "<input type='hidden' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
            echo "<input type='hidden' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
            echo "<input type='hidden' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
        }
        
        $query = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogfa AS c WHERE c.Activo=1");
        $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
            FROM c_serviciogfa AS c
            LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGFA
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
        $rsp = mysql_fetch_array($query2);
        while ($rs = mysql_fetch_array($query)) {
            $dis = "disabled='disabled'";
            $renta = "";
            $impresiones = "";
            $excedente = "";
            if ($rsp != "") {
                if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                    $dis = "";
                    $renta = $rsp['Renta'];
                    $impresiones = $rsp['Impresiones'];
                    $excedente = $rsp['Excedente'];
                    $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                    $rsp = mysql_fetch_array($query2);
                }
            }
            echo "<input type='hidden' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
            echo "<input type='hidden' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
            echo "<input type='hidden' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
        }
        
        /*************************** Conceptos adicionales ****************************/
        $filas = 0;
        $query2 = $catalogo->obtenerLista("SELECT kp.Descripcion AS Descripcion,kp.IdProductoSAT,
            kp.Cantidad AS Cantidad,kp.PrecioUnitario AS PrecioUnitario,kp.Nivel_facturacion AS Nivel_facturacion
            FROM k_parametro_concepto AS kp
            LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
            WHERE cp.ClaveAnexoTecnico='$anexo';");
        while ($rs = mysql_fetch_array($query2)) {
            $query22 = $catalogo->obtenerLista("SELECT * FROM c_nivel_facturacion ORDER BY Nombre");
            while ($rspl = mysql_fetch_array($query22)) {
                if ($rspl['Id_nivel_facturacion'] == $rs['Nivel_facturacion']) {
                    echo "<input type='hidden' id='select_con_adic_" . $filas . "' name='select_con_adic_" . $filas . "' value='" . $rspl['Id_nivel_facturacion'] . "' />";
                    break;
                }
            }
            echo "<input type='hidden' name='text_con_adic_" . $filas . "' id='text_con_adic_" . $filas . "' value='" . $rs['Descripcion'] . "' /></td>";
            echo "<input type='hidden' name='cantidad_con_adic_" . $filas . "' id='cantidad_con_adic_" . $filas . "' value='" . $rs['Cantidad'] . "' onchange='calcularImporte($filas)' style='width: 50px'/></td>";
            echo "<input type='hidden' name='preciounitario_con_adic_" . $filas . "' id='preciounitario_con_adic_" . $filas . "' value='" . $rs['PrecioUnitario'] . "' onchange='calcularImporte($filas)' style='width: 50px'/></td>";
            echo "<input type='hidden' name='importe_con_adic_" . $filas . "' id='importe_con_adic_" . $filas . "' value='$" . number_format($rs['PrecioUnitario'] * $rs['Cantidad'],2) . "' readonly='readonly' style='width: 100px'/></td>";
            echo "<input type='hidden' name='producto_adic_" . $filas . "' id='producto_adic_" . $filas . "' value='" . $rs['IdProductoSAT'] . "' /></td></tr>";
            $filas++;
        }
        echo "<input type='hidden' name='filas_conceptos' id='filas_conceptos' value='$filas'/>";
        echo $otro;
        echo "<input type='hidden' name='contrato' id='contrato' value='$contrato'/>";
        echo "<input type='hidden' name='cliente' id='cliente' value='$cliente'/>";
        echo "<input type='hidden' name='anexo' id='anexo' value='$anexo'/>";
        echo "<input type='hidden' name='localidad' id='localidad' value=''/>";
        echo "<input type='hidden' name='activarBoton' id='activarBoton' value='1'/>";
    ?>
    
</form>