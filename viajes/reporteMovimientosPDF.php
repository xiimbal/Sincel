<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

$urlReporteServicios = "";

$catalogo = new Catalogo();

$nombreLogo = "";
$queryFacturacion = $catalogo->obtenerLista("SELECT df.Telefono,df.ImagenPHP,df.IdDatosFacturacionEmpresa,df.Calle,df.NoExterior,df.Colonia,df.Delegacion,df.Estado,df.CP FROM c_datosfacturacionempresa df");
if ($rs = mysql_fetch_array($queryFacturacion)) {
    $nombreLogo = $rs['ImagenPHP'];
}

$where = "";
$parametrosMostrados = "";

if (isset($_POST['form'])) {
    $parametros = "";
    
    if (isset($_POST['form'])) {
        parse_str($_POST['form'], $parametros);
    }
    
    if (isset($parametros['Campana']) && $parametros['Campana'] != "" && $parametros['Campana'] != 0) {
        $idC = "";
        foreach ($parametros['Campana'] as $value) {
            $idC.= "$value,";
            if($where == ""){
                $where = "WHERE (a.IdArea = ".$value;
            }else{
                $where.= " OR a.IdArea = ".$value;
            }
        }
        if($idC != ""){
            $idC = substr($idC, 0, strlen($idC)-1);
        }
        $urlReporteServicios.="?Campana=$idC";
        $where .= ")";
    }
    
    if (isset($parametros['nombreE']) && $parametros['nombreE'] != "" && $parametros['nombreE'] != 0) {
        if($where == ""){
            $where = "WHERE";
        }
        $num = 0;
        foreach ($parametros['nombreE'] as $value) {
            if($num == 0){
                if(strcmp($where, "WHERE") == 0){
                    $where .= " (u.Loggin = '".$value."'";
                }else{
                    $where.= " AND (u.Loggin = '".$value."'";
                }
            }else{
                $where .= " OR u.Loggin = '".$value."'";
            }
            $num++;
        }
        $where .= ")";
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
    }
    
    if (isset($parametros['turno']) && $parametros['turno'] != "" && $parametros['turno'] != 0) {
        if($where == ""){
            $where = "WHERE";
        }
        $num = 0;
        foreach ($parametros['turno'] as $value) {
            if($num == 0){
                if(strcmp($where, "WHERE") == 0){
                    $where .= " (cdu.IdTurno = ".$value;
                }else{
                    $where.= " AND (cdu.IdTurno = ".$value;
                }
            }else{
                $where .= " OR cdu.IdTurno = ".$value;
            }
            $num++;
        }
        $where .= ")";
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
        if($where == ""){
            $where = "WHERE ccc.FechaCreacion >= '".$parametros['fecha_inicio']."'";
        }else{
            $where.= " AND ccc.FechaCreacion >= '".$parametros['fecha_inicio']."'";
        }
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?fechaI='" . $parametros['fecha_inicio']."'";
        } else {
            $urlReporteServicios.="&fechaI='" . $parametros['fecha_inicio']."'";
        }
    }
    
    if (isset($parametros['fecha_fin']) && $parametros['fecha_fin'] != ""){
        if($where == ""){
            $where = "WHERE ccc.FechaCreacion >= '".$parametros['fecha_fin']."'";
        }else{
            $where.= " AND ccc.FechaCreacion >= '".$parametros['fecha_fin']."'";
        }
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?fechaF='" . $parametros['fecha_fin']."'";
        } else {
            $urlReporteServicios.="&fechaF='" . $parametros['fecha_fin']."'";
        }
    }
    
    if (isset($parametros['operador']) && $parametros['operador'] != "" && $parametros['operador'] != 0) {
        if($where == ""){
            $where = "WHERE";
        }
        $num = 0;
        foreach ($parametros['operador'] as $value) {
            if($num == 0){
                if(strcmp($where, "WHERE") == 0){
                    $where .= " (ccc.IdUsuario = ".$value;
                }else{
                    $where.= " AND (ccc.IdUsuario = ".$value;
                }
            }else{
                $where .= " OR ccc.IdUsuario = ".$value;
            }
            $num++;
        }
        $where .= ")";
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

$query = "SELECT a.Descripcion AS Descr	, ccc.IdCambioCampania,
        CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS nombre, 
        (CASE WHEN (ISNULL(ccc2.IdUsuario)) THEN 
        ((5 * (DATEDIFF(ccc.FechaCreacion, cdu.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(cdu.FechaCreacion) + WEEKDAY(ccc.FechaCreacion) + 1, 1))) 
        WHEN (!ISNULL(ccc2.IdUsuario)) THEN 
        ((5 * (DATEDIFF(ccc.FechaCreacion, ccc2.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(ccc2.FechaCreacion) + WEEKDAY(ccc.FechaCreacion) + 1, 1))) 
        END)AS Salida 
        FROM c_cambio_campania ccc 
        LEFT JOIN c_area AS a ON ccc.IdCampania_antes = a.IdArea 
        LEFT JOIN c_domicilio_usturno AS cdu ON ccc.IdUsuario = cdu.IdUsuario
        LEFT JOIN c_usuario AS u ON ccc.IdUsuario = u.IdUsuario 
        LEFT JOIN c_cambio_campania AS ccc2 ON (ccc2.IdCambioCampania = (SELECT IdCambioCampania FROM c_cambio_campania WHERE IdUsuario = ccc.IdUsuario AND IdCambioCampania < ccc.IdCambioCampania ORDER BY IdCambioCampania DESC LIMIT 1))
        $where";

$result = $catalogo->obtenerLista($query);

$queryEntrada = "SELECT a.Descripcion AS Descr,
        CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS nombre, 
        (CASE WHEN (ISNULL(ccc3.IdUsuario)) THEN 
        ((5 * (DATEDIFF(NOW(), ccc.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(ccc.FechaCreacion) + WEEKDAY(NOW()) + 1, 1)))
        WHEN (!ISNULL(ccc3.IdUsuario)) THEN 
        ((5 * (DATEDIFF(ccc3.FechaCreacion, ccc.FechaCreacion) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(ccc.FechaCreacion) + WEEKDAY(ccc3.FechaCreacion) + 1, 1)))
        END)AS Entrada 
        FROM c_cambio_campania ccc 
        LEFT JOIN c_area AS a ON ccc.IdCampania_despues = a.IdArea 
        LEFT JOIN c_domicilio_usturno AS cdu ON (cdu.IdCampania = ccc.IdCampania_despues AND cdu.IdUsuario = ccc.IdUsuario) 
        LEFT JOIN c_usuario AS u ON ccc.IdUsuario = u.IdUsuario 
        LEFT JOIN c_cambio_campania AS ccc3 ON (ccc3.IdCambioCampania = (SELECT IdCambioCampania FROM c_cambio_campania WHERE IdUsuario = ccc.IdUsuario AND IdCambioCampania > ccc.IdCambioCampania ORDER BY IdCambioCampania ASC LIMIT 1))
        $where";
//echo $query;

$result2 = $catalogo->obtenerLista($queryEntrada);
?>
<!DOCTYPE html>
<html lang="es">
    <head>     
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Reporte Campaña</title>
        <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        <style>
            .BorderTabla td,th {
                border: solid black;
                border-width:1px;
                border-spacing: 0px;
                border-collapse: collapse
            }
            img.imagen{width:150px; height:70px;}
            img.imagens{width:150px; height:70px;}
            @media print {
                * { margin: 0 !important; padding: 0 !important; }
                #controls, .footer, .footerarea{ display: none; }
                html, body {
                    /*changing width to 100% causes huge overflow and wrap*/
                    height:80%; 
                    background: #FFF; 
                    font-size: 9.5pt;
                }
                img.imagen{width:75px; height:30px;}
                img.imagens{width:75px; height:30px;}
                template { width: auto; left:0; top:0; }
                li { margin: 0 0 10px 20px !important;}
            }
        </style>
    </head>
    <body style="font-size:12px;font-family:Arial;height:50%;">
        <link rel="stylesheet" href="resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="resources/js/jquery/jquery-1.11.3.min.js"></script>
		<script src="resources/js/jquery/jquery-ui.min.js"></script>    
        <a href=javascript:window.print(); style="margin-left: 85%;"><img src="resources/images/icono_impresora.png" height="30" width="30"></a>
        <a href="viajes/ReporteMovimientos.php<?php echo $urlReporteServicios ?>" style="margin-left: 5%;"><img height="30" width="30" src="resources/images/excel.png"></a>
        <br/><br/>
        <table style="width: 100%">
            <tr>                
                <td style="width: 50%"><img height="80" width="120" src="<?php echo $nombreLogo; ?>"/></td>
            </tr>
        </table>
    <center><h2>Reporte de campaña</h2></center>
    <br/><br/>
    <fieldset>
        <legend><b>Datos del reporte</b></legend>
        <table style="width: 100%;height: 40px;" class="BorderTabla">
        <thead style="background-color: grey;">
            <tr></tr>
            <tr>
                <th align="center" style="width: 10%;">Campaña</th>
                <th align="center" style="width: 40%;">Movimiento</th>
                <th align="center" style="width: 10%;">Nombre</th>
                <th align="center" style="width: 10%;">Duración (en días hábiles)</th>
            </tr>
        <tbody style="background-color: #D3D6FF;">
            <?php
                while ($rs = mysql_fetch_array($result)) {
            ?>
            <tr>
                <td align="center"><?php echo $rs['Descr']; ?></td>
                <td align="center"><?php echo "Salida"; ?></td>
                <td align="center"><?php echo $rs['nombre']; ?></td>
                <td align="center"><?php echo $rs['Salida']; ?></td>
            </tr>
            <?php  
                }
                
                while ($rs = mysql_fetch_array($result2)){
            ?>
            <tr>
                <td align="center"><?php echo $rs['Descr']; ?></td>
                <td align="center"><?php echo "Entrada"; ?></td>
                <td align="center"><?php echo $rs['nombre']; ?></td>
                <td align="center"><?php echo $rs['Entrada']; ?></td>
            </tr>
            <?php  
                }
            ?>
        </tbody>
        </thead>
    </fieldset>
    </body>
</html>
