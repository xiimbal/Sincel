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
$order = "ORDER BY p.Fecha DESC";
$asc = "DESC";
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
                    $where .= " (ti.NoSerieEquipo = '".$value."'";
                }else{
                    $where.= " AND (ti.NoSerieEquipo = '".$value."'";
                }
            }else{
                $where .= " OR ti.NoSerieEquipo = '".$value."'";
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
                    $where .= " (t.idTurno = ".$value;
                }else{
                    $where.= " AND (t.idTurno = ".$value;
                }
            }else{
                $where .= " OR t.idTurno = ".$value;
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
            $where = "WHERE p.Fecha >= '".$parametros['fecha_inicio']."'";
        }else{
            $where.= " AND p.Fecha >= '".$parametros['fecha_inicio']."'";
        }
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?fechaI='" . $parametros['fecha_inicio']."'";
        } else {
            $urlReporteServicios.="&fechaI='" . $parametros['fecha_inicio']."'";
        }
    }
    
    if (isset($parametros['fecha_fin']) && $parametros['fecha_fin'] != ""){
        if($where == ""){
            $where = "WHERE p.Fecha >= '".$parametros['fecha_fin']."'";
        }else{
            $where.= " AND p.Fecha >= '".$parametros['fecha_fin']."'";
        }
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?fechaF='" . $parametros['fecha_fin']."'";
        } else {
            $urlReporteServicios.="&fechaF='" . $parametros['fecha_fin']."'";
        }
    }
    
    if(isset($parametros['ascendente'])){
        $asc = "ASC";
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?ascendente='" . $parametros['ascendente']."'";
        } else {
            $urlReporteServicios.="&ascendente='" . $parametros['ascendente']."'";
        }
    }
    
    if (isset($parametros['ordenar']) && $parametros['ordenar'] != ""){ //Si no entra aquí solo se ordenara por fecha
        $orden = $parametros['ordenar'] + 0;
        if($orden == 1){    //Campaña
            $order = "ORDER BY a.IdArea $asc, p.Fecha DESC";
        }else if($orden == 2){  //Nombre empleado
            $order = "ORDER BY ti.NoSerieEquipo $asc, p.Fecha DESC";
        }else if($orden == 3){  //Operador
            $order = "ORDER BY ktt.IdUsuario $asc, p.Fecha DESC";
        }else{  //Turno
            $order = "ORDER BY t.idTurno $asc, p.Fecha DESC";
        }
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?ordenar=" . $parametros['ordenar'];
        } else {
            $urlReporteServicios.="&ordenar=" . $parametros['ordenar'];
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
                    $where .= " (ktt.IdUsuario = ".$value;
                }else{
                    $where.= " AND (ktt.IdUsuario = ".$value;
                }
            }else{
                $where .= " OR ktt.IdUsuario = ".$value;
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

$query = "SELECT a.Descripcion, 
        p.Fecha, p.Hora, lt.ContadorBN AS servicio, CONCAT_WS(' ',u2.Nombre,u2.ApellidoPaterno, u2.ApellidoMaterno) AS Loggin, 
        CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno, u.ApellidoMaterno) AS NombreOperador
        FROM c_plantilla p
        LEFT JOIN c_area AS a ON p.idCampania = a.IdArea 
        LEFT JOIN k_plantilla AS kp ON kp.idPlantilla = p.idPlantilla
        LEFT JOIN k_plantilla_asistencia AS kpa ON kpa.idK_Plantilla = kp.idK_Plantilla
        LEFT JOIN c_ticket AS ti ON ti.IdTicket = kpa.IdTicket
        LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = kpa.IdTicket 
        LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario 
        LEFT JOIN c_turno AS t ON t.idTurno = p.idTurno 
        LEFT JOIN c_lecturasticket AS lt ON lt.fk_idticket = p.IdTicket
        LEFT JOIN c_usuario AS u2 ON u2.Loggin = ti.NoSerieEquipo
        $where $order";

$result = $catalogo->obtenerLista($query);

?>
<!DOCTYPE html>
<html lang="es">
    <head>     
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Reporte Servicios</title>
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
        <a href="viajes/ReporteServicios.php<?php echo $urlReporteServicios ?>" style="margin-left: 5%;"><img height="30" width="30" src="resources/images/excel.png"></a>
        <br/><br/>
        <table style="width: 100%">
            <tr>                
                <td style="width: 50%"><img height="80" width="120" src="<?php echo $nombreLogo; ?>"/></td>
            </tr>
        </table>
    <center><h2>Reporte de servicios</h2></center>
    <br/><br/>
    <fieldset>
        <legend><b>Datos del reporte</b></legend>
        <table style="width: 100%;height: 40px;" class="BorderTabla">
        <thead style="background-color: grey;">
            <tr></tr>
            <tr>
                <th align="center" style="width: 10%;">Campaña</th>
                <th align="center" style="width: 40%;">Nombre empleado</th>
                <th align="center" style="width: 10%;">Fecha de plantilla</th>
                <th align="center" style="width: 10%;">Servicio</th>
                <th align="center" style="width: 10%;">Operador de la unidad</th>
            </tr>
        <tbody style="background-color: #D3D6FF;">
            <?php
                while ($rs = mysql_fetch_array($result)) {
                    if(isset($rs['Loggin']) && $rs['Loggin'] != "") {
            ?>
            <tr>
                <td align="center"><?php echo $rs['Descripcion']; ?></td>
                <td align="center"><?php echo $rs['Loggin']; ?></td>
                <td align="center"><?php echo $rs['Fecha']." ".$rs['Hora'] ?></td>
                <td align="center"><?php echo $rs['servicio'] ?></td>
                <td align="center"><?php echo $rs['NombreOperador'] ?></td>
            </tr>
            <?php  
                    }
                } ?>
        </tbody>
        </thead>
    </fieldset>
    </body>
</html>