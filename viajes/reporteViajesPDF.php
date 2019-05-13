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
                    $where .= " (t.NoSerieEquipo = '".$value."'";
                }else{
                    $where.= " AND (t.NoSerieEquipo = '".$value."'";
                }
            }else{
                $where .= " OR t.NoSerieEquipo = '".$value."'";
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
                    $where .= " (dut.IdTurno = ".$value;
                }else{
                    $where.= " AND (dut.IdTurno = ".$value;
                }
            }else{
                $where .= " OR dut.IdTurno = ".$value;
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
            $where = "WHERE t.FechHoraInicRep >= '".$parametros['fecha_inicio']."'";
        }else{
            $where.= " AND t.FechHoraInicRep >= '".$parametros['fecha_inicio']."'";
        }
        if ($urlReporteServicios == "") {
            $urlReporteServicios.="?fechaI='" . $parametros['fecha_inicio']."'";
        } else {
            $urlReporteServicios.="&fechaI='" . $parametros['fecha_inicio']."'";
        }
    }
    
    if (isset($parametros['fecha_fin']) && $parametros['fecha_fin'] != ""){
        if($where == ""){
            $where = "WHERE t.FechHoraInicRep >= '".$parametros['fecha_fin']."'";
        }else{
            $where.= " AND t.FechHoraInicRep >= '".$parametros['fecha_fin']."'";
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
    if($where == ""){
        $where = "WHERE t.TipoReporte = 1";
    }else{
        $where.= "AND t.TipoReporte = 1";
    }
}else{
    echo "No se recibieron datos";
}

$query = "SELECT a.Descripcion, CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno, u.ApellidoMaterno) AS nombreEmpleado, ccc.Nombre,
        (CASE WHEN p.TipoEvento = 1 THEN 'Aforo' WHEN p.TipoEvento = 2 THEN 'Desaforo' END) AS tipo,
        CONCAT_WS(' ','C.',dut.Calle,'No.E',dut.NoExterior,'No.I',dut.NoInterior, 'Col.', dut.Colonia,'C.P.',dut.CodigoPostal, dut.Estado, dut.Delegacion) AS domicilioUsuario,
        CONCAT_WS(' ','C.',d.Calle,'No.E',d.NoExterior,'No.I',d.NoInterior, 'Col.', d.Colonia,'C.P.',d.CodigoPostal, d.Estado, d.Delegacion) AS centroCosto
        FROM c_ticket t
        LEFT JOIN k_plantilla_asistencia AS kpa ON kpa.IdTicket = t.IdTicket
        LEFT JOIN k_plantilla AS kp ON kp.idK_Plantilla = kpa.idK_Plantilla
        LEFT JOIN c_plantilla AS p ON kp.idPlantilla = p.idPlantilla
        LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = kpa.IdTicket 
        LEFT JOIN c_area AS a ON a.IdArea = p.idCampania
        LEFT JOIN c_usuario as u ON t.NoSerieEquipo = u.Loggin
        LEFT JOIN c_domicilio_usturno as dut ON dut.IdUsuario = u.IdUsuario
        LEFT JOIN c_centrocosto AS ccc ON a.ClaveCentroCosto = ccc.ClaveCentroCosto
        LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = a.ClaveCentroCosto
        $where";

$result = $catalogo->obtenerLista($query);

$queryViajesEspeciales = "SELECT a.Descripcion, e.Origen, e.Destino, CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno, u.ApellidoMaterno) AS Loggin,
        CONCAT_WS(' ','C.',e.Calle_or,'No.E',e.NoExterior_or,'No.I',e.NoInterior_or, 'Col.', e.Colonia_or,'C.P.',e.CodigoPostal_or, e.Estado_or, e.Delegacion_or) AS domicilioOrigen,
        CONCAT_WS(' ','C.',e.Calle_des,'No.E',e.NoExterior_des,'No.I',e.NoInterior_des, 'Col.', e.Colonia_des,'C.P.',e.CodigoPostal_des, e.Estado_des, e.Delegacion_des) AS domicilioDestino
        FROM c_especial e
        LEFT JOIN c_usuario AS u ON u.IdUsuario = e.idUsuario
        LEFT JOIN c_area AS a ON e.idCampania = a.IdArea
        $where2";

$result2 = $catalogo->obtenerLista($queryViajesEspeciales);
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
        <a href="viajes/ReporteViajesPorCampana.php<?php echo $urlReporteServicios ?>" style="margin-left: 5%;"><img height="30" width="30" src="resources/images/excel.png"></a>
        <br/><br/>
        <table style="width: 100%">
            <tr>                
                <td style="width: 50%"><img height="80" width="120" src="<?php echo $nombreLogo; ?>"/></td>
            </tr>
        </table>
    <center><h2>Reporte de viajes por campaña</h2></center>
    <br/><br/>
    <fieldset>
        <legend><b>Datos del reporte</b></legend>
        <table style="width: 100%;height: 40px;" class="BorderTabla">
        <thead style="background-color: grey;">
            <tr></tr>
            <tr>
                <th align="center" style="width: 10%;">Campaña</th>
                <th align="center" style="width: 40%;">Nombre de empleado </th>
                <th align="center" style="width: 10%;">Tipo</th>
                <th align="center" style="width: 10%;">Origen</th>
                <th align="center" style="width: 10%;">Destino</th>
            </tr>
        <tbody style="background-color: #D3D6FF;">
            <?php
                while ($rs = mysql_fetch_array($result)) {
                    if(isset($rs['nombreEmpleado']) && $rs['nombreEmpleado'] != ""){
            ?>
            <tr>
                <td align="center"><?php echo $rs['Descripcion']; ?></td>
                <td align="center"><?php echo $rs['nombreEmpleado']; ?></td>
                <td align="center"><?php echo $rs['tipo']; ?></td>
                <?php if(strcmp($rs['tipo'], "Aforo") == 0){ ?>
                    <td align="center"><?php echo $rs['domicilioUsuario']; ?></td>
                    <td align="center"><?php echo $rs['Nombre']." Domicilio. ".$rs['centroCosto']; ?></td>
                <?php }else{ ?>
                    <td align="center"><?php echo $rs['Nombre']." Domicilio. ".$rs['centroCosto']; ?></td>
                    <td align="center"><?php echo $rs['domicilioUsuario']; ?></td>
                <?php } ?>
            </tr>
            <?php  
                    }           
                }
                
                while ($rs = mysql_fetch_array($result2)){
            ?>
            <tr>
                <td align="center"><?php echo $rs['Descripcion']; ?></td>
                <td align="center"><?php echo $rs['Loggin']; ?></td>
                <td align="center"><?php echo "Viaje especial"; ?></td>
                <td align="center"><?php echo $rs['Origen']." Domicilio. ".$rs['domicilioOrigen']; ?></td>
                <td align="center"><?php echo $rs['Destino']." Domicilio. ".$rs['domicilioDestino']; ?></td>
            </tr>
            <?php  
                }
            ?>
        </tbody>
        </thead>
    </fieldset>
    </body>
</html>
