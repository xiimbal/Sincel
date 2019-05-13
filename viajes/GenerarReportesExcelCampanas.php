<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "viajes/reportesCampanas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$urlReporteServicios = "";
$urlReporteMiPlantilla = "";

if (isset($_POST['form'])) {
    $parametros = "";
    
    if (isset($_POST['form'])) {
        parse_str($_POST['form'], $parametros);
    }
    
    if (isset($parametros['Campana']) && $parametros['Campana'] != "" && $parametros['Campana'] != 0) {
        $idC = "";
        foreach ($parametros['Campana'] as $value) {
            $idC.= "$value,";
        }
        if($idC != ""){
            $idC = substr($idC, 0, strlen($idC)-1);
        }
        $urlReporteServicios.="?Campana=$idC";
    }
    
    if (isset($parametros['nombreE']) && $parametros['nombreE'] != "" && $parametros['nombreE'] != 0) {
        if ($urlReporteServicios == "") {
            $idNE = "";
            foreach ($parametros['nombreE'] as $value) {
                $idNE .= "$value,";
            }
            if($idNE != ""){
                $idNE = substr($idNE, 0, strlen($idNE)-1);
            }
            $urlReporteServicios.="?nombreE=$idNE";
        } else {
            $idNE = "";
            foreach ($parametros['nombreE'] as $value) {
                $idNE .= "$value,";
            }
            if($idNE != ""){
                $idNE = substr($idNE, 0, strlen($idNE)-1);
            }            
            $urlReporteServicios.="&nombreE=$idNE";
        }
        $urlReporteMiPlantilla = $urlReporteServicios;
        $urlReporteMiPlantilla.=",".$_SESSION['user'];
    }else
    {
        $urlReporteMiPlantilla = $urlReporteServicios;
        if($urlReporteMiPlantilla == ""){
            $urlReporteMiPlantilla = "?nombreE=".$_SESSION['user'];
        }else{
            $urlReporteMiPlantilla .= "&nombreE=".$_SESSION['user'];
        }
    }
    
    if (isset($parametros['turno']) && $parametros['turno'] != "" && $parametros['turno'] != 0) {
        if ($urlReporteServicios == "") {
            $idT = "";
            foreach ($parametros['turno'] as $value) {
                $idT .= "$value,";
            }
            if($idT != ""){
                $idT = substr($idT, 0, strlen($idT)-1);
            }
            $urlReporteServicios.="?turno=$idT";
        } else {
            $idT = "";
            foreach ($parametros['turno'] as $value) {
                $idT .= "$value,";
            }
            if($idT != ""){
                $idT = substr($idT, 0, strlen($idT)-1);
            }            
            $urlReporteServicios.="&turno=$idT";
        }
    }
    
    if (isset($parametros['fecha_inicio']) && $parametros['fecha_inicio'] != ""){
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?fechaI=" . $parametros['fecha_inicio'];
        } else {
            $urlReporteServicios.="&fechaI=" . $parametros['fecha_inicio'];
        }
    }
    
    if (isset($parametros['fecha_fin']) && $parametros['fecha_fin'] != ""){
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?fechaF=" . $parametros['fecha_fin'];
        } else {
            $urlReporteServicios.="&fechaF=" . $parametros['fecha_fin'];
        }
    }
    
    if (isset($parametros['operador']) && $parametros['operador'] != "" && $parametros['operador'] != 0) {
        if ($urlReporteServicios == "") {
            $idO = "";
            foreach ($parametros['operador'] as $value) {
                $idO .= "$value,";
            }
            if($idO != ""){
                $idO = substr($idO, 0, strlen($idO)-1);
            }
            $urlReporteServicios.="?operador=$idO";
        } else {
            $idO = "";
            foreach ($parametros['operador'] as $value) {
                $idO .= "$value,";
            }
            if($idO != ""){
                $idO = substr($idO, 0, strlen($idO)-1);
            }            
            $urlReporteServicios.="&operador=$idO";
        }
    }
    
}else{
    echo "No se recibieron datos";
}

?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/viajes/GenerarReporteExcelCampanas.js"></script>
<br/><br/>
<table style="float: left;">
    <tr>
        <td>
            <a href="viajes/ReporteServicios.php<?php echo $urlReporteServicios ?>" target="_blank" class="boton" style='text-decoration:none;color:white;'>Reporte de servicios</a>
        </td>
        <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 32)){   ?>
        <td>
            <a href="viajes/ReporteAdministracion.php<?php echo $urlReporteServicios ?>" target="_blank" class="boton" style='text-decoration:none;color:white;'>Reporte de administración</a>
        </td>
        <? } ?>
        <td>
            <a href="viajes/ReporteAdministracion.php<?php echo $urlReporteMiPlantilla ?>" target="_blank" class="boton" style='text-decoration:none;color:white;'>Mi plantilla</a>
        </td>
        <td>
            <a href="viajes/ReporteMovimientos.php<?php echo $urlReporteServicios ?>" target="_blank" class="boton" style='text-decoration:none;color:white;'>Reporte de movimientos</a>
        </td>
        <td>
            <a href="viajes/ReporteViajesPorCampana.php<?php echo $urlReporteServicios ?>" target="_blank" class="boton" style='text-decoration:none;color:white;'>Reporte de viajes</a>
        </td>
    </tr>
</table>

