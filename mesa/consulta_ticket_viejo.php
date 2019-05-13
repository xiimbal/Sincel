<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$id_ticket = "";
$tipoReporte = "";
$estadoTicket = "";
$fechaTicket = "";
$nombreUsuario = "";
//
$ticketCliente = "";
$ticketDistribuidor = "";
$descripcionTicket = "";
$observaciones = "";
$areaAtencion = "";
$nombreResponsable = "";
$telefono1Responsable = "";
$telefono2Responsable = "";
$extencion1Responsable = "";
$extencion2Responsable = "";
$celularResponsable = "";
$correoResponsable = "";
$horaInicioResponsable = "";
$horaFinResponsable = "";
//
$nombreAtencion = "";
$telefono1Atencion = "";
$telefono2Atencion = "";
$extencion1Atencion = "";
$extencion2Atencion = "";
$celularAtencion = "";
$correoAtencion = "";
$horaInicioAtencion = "";
$horaFinAtencion = "";
$auxHoraInicioRes = "";
$auxHorafinRes = "";
$auxHoraInicioAt = "";
$auxHorafinAt = "";
$noSerieEquipo = "";
$modeloEquipo = "";
//
$claveCliente = "";
$nombreCliente = "";
$tipoCliente = "";
$nombreLocalidad = "";
$estatusCobranza = "";
$tipoDomicilio = "";
$calle = "";
$colonia = "";
$delegacion = "";
$idZona = "";
$nExterior = "";
$nInterior = "";
$ciudad = "";
$cp = "";
$estadoLocal = "";
$nombreContacto = "";
$telefono = "";
$celular = "";
$correoE = "";
$Ubicaion = "";
$tfs = "";
$fecha_anterior;
$contador_negro_anterior;
$contador_color_anterior;
$contador_negro;
$contador_color;
$id_estado = "2";
if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
    $id_ticket = $_POST['idTicket'];
}
if ($id_ticket != "") {
    $consultaTicket = "SELECT t.IdTicket AS id_ticket,t.FechaHora AS fecha_ticket,t.TipoReporte AS tipo_ticket,t.Usuario AS usuario_ticket,t.NoTicketCliente,
                        t.NoTicketDistribuidor,t.DescripcionReporte,t.ObservacionAdicional,t.AreaAtencion,cl.ClaveCliente,t.NombreResp,t.Telefono1Resp,t.Extension1Resp,
                        t.Telefono2Resp,t.Extension2Resp,t.CelularResp,t.CorreoEResp, t.HorarioAtenInicResp,t.HorarioAtenFinResp,t.NombreAtenc,t.Telefono1Atenc,t.Telefono2Atenc,
                        t.Extension1Atenc,t.Extension2Atenc,t.CelularAtenc,t.CorreoEAtenc, t.HorarioAtenInicAtenc,t.HorarioAtenFinAtenc,t.NoSerieEquipo AS serie_equipo,e.Modelo AS modeloEquipo,
                        t.NombreCliente,tc.Nombre AS tipo_cliente,ec.Nombre AS estatus_cobranza,t.NombreCentroCosto AS localidad,GROUP_CONCAT(CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS tfs,
                        IF(ISNULL(dm.IdTicket),'Domicilio centro de costo','Domicilio del ticket') AS tipo_domicilio,IF(ISNULL(dm.IdTicket),d.Calle,dm.Calle) AS calle,
                        IF(ISNULL(dm.IdTicket),d.NoExterior,dm.NoExterior) AS num_exterior,IF(ISNULL(dm.IdTicket),d.NoInterior,dm.NoInterior) AS num_interior,
                        IF(ISNULL(dm.IdTicket),d.Colonia,dm.Colonia) AS colonia,IF(ISNULL(dm.IdTicket),d.Ciudad,dm.Ciudad) AS ciudad,IF(ISNULL(dm.IdTicket),d.Estado,d.Estado) AS estado_dom,
                        IF(ISNULL(dm.IdTicket),d.Delegacion,dm.Delegacion) AS delegacion,IF(ISNULL(dm.IdTicket),d.CodigoPostal,dm.CodigoPostal) AS cp,IF(ISNULL(dm.IdTicket),zd.NombreZona,zdm.NombreZona) AS zona,
                        ct.Nombre AS nomContacto,ct.Telefono AS telContacto,ct.Celular AS celContacto,ct.CorreoElectronico AS emailContacot FROM c_ticket t LEFT JOIN c_bitacora b ON t.NoSerieEquipo=b.NoSerie
                        LEFT JOIN c_equipo e ON b.NoParte=e.NoParte LEFT JOIN c_cliente cl ON t.ClaveCliente=cl.ClaveCliente LEFT JOIN c_tipocliente tc On cl.IdTipoCliente=tc.IdTipoCliente
                        LEFT JOIN c_estatuscobranza ec On cl.IdEstatusCobranza=ec.IdEstatusCobranza LEFT JOIN k_tfscliente tfc On cl.ClaveCliente=tfc.ClaveCliente LEFT JOIN c_usuario u ON tfc.IdUsuario=u.IdUsuario
                        LEFT JOIN c_domicilioticket dm ON t.IdTicket=dm.IdTicket LEFT JOIN c_centrocosto cc ON cc.ClaveCliente=t.ClaveCentroCosto LEFT JOIN c_domicilio d ON cl.ClaveCliente=d.ClaveEspecialDomicilio
                        LEFT JOIN c_contacto ct ON t.ClaveCentroCosto=ct.ClaveEspecialContacto LEFT JOIN c_zona zdm ON dm.ClaveZona=zdm.ClaveZona LEFT JOIN c_zona zd ON d.ClaveZona=zd.ClaveZona WHERE t.IdTicket=$id_ticket";
    $query_ticket = $catalogo->obtenerLista($consultaTicket);
    while ($rs = mysql_fetch_array($query_ticket)) {
        $tipoReporte = $rs['tipo_ticket'];
        $estadoTicket = $rs['id_ticket'];
        $fechaTicket = $rs['fecha_ticket'];
        $nombreUsuario = $rs['usuario_ticket'];
        $ticketCliente = $rs['NoTicketCliente'];
        $ticketDistribuidor = $rs['NoTicketDistribuidor'];
        $descripcionTicket = $rs['DescripcionReporte'];
        $observaciones = $rs['ObservacionAdicional'];
        $areaAtencion = $rs['AreaAtencion'];
        //
        $nombreResponsable = $rs['NombreResp'];
        $telefono1Responsable = $rs['Telefono1Resp'];
        $telefono2Responsable = $rs['Telefono2Resp'];
        $extencion1Responsable = $rs['Extension1Resp'];
        $extencion2Responsable = $rs['Extension2Resp'];
        $celularResponsable = $rs['CelularResp'];
        $correoResponsable = $rs['CorreoEResp'];
        $horaInicioResponsable = $rs['HorarioAtenInicResp'];
        $horaFinResponsable = $rs['HorarioAtenFinResp'];
        $auxHoraInicioRes = explode(",", $horaInicioResponsable);
        $auxHorafinRes = explode(",", $horaFinResponsable);
        //
        $nombreAtencion = $rs['NombreAtenc'];
        $telefono1Atencion = $rs['Telefono1Atenc'];
        $telefono2Atencion = $rs['Telefono2Atenc'];
        $extencion1Atencion = $rs['Extension1Atenc'];
        $extencion2Atencion = $rs['Extension2Atenc'];
        $celularAtencion = $rs['CelularAtenc'];
        $correoAtencion = $rs['CorreoEAtenc'];
        $horaInicioAtencion = $rs['HorarioAtenInicAtenc'];
        $horaFinAtencion = $rs['HorarioAtenFinAtenc'];
        $auxHoraInicioAt = explode(",", $horaInicioAtencion);
        $auxHorafinAt = explode(",", $horaFinAtencion);
        $noSerieEquipo = $rs['serie_equipo'];
        $modeloEquipo = $rs['modeloEquipo'];
        //
        $claveCliente = $rs['ClaveCliente'];
        $nombreCliente = $rs['NombreCliente'];
        $tipoCliente = $rs['tipo_cliente'];
        $nombreLocalidad = $rs['localidad'];
        $estatusCobranza = $rs['estatus_cobranza'];
        $tipoDomicilio = $rs['tipo_domicilio'];
        $calle = $rs['calle'];
        $colonia = $rs['colonia'];
        $delegacion = $rs['delegacion'];
        $idZona = $rs['zona'];
        $nExterior = $rs['num_exterior'];
        $nInterior = $rs['num_interior'];
        $ciudad = $rs['ciudad'];
        $cp = $rs['cp'];
        $estadoLocal = $rs['estado_dom'];
        $nombreContacto = $rs['nomContacto'];
        $telefono = $rs['telContacto'];
        $celular = $rs['celContacto'];
        $correoE = $rs['emailContacot'];
        $Ubicaion = $rs[''];
        $tfs = $rs['tfs'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
        if (isset($_GET['frame']) && $_GET['frame'] == "1") {
            $path_previo = "../";            
            echo '<link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
                <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
				<script src="../resources/js/jquery/jquery-ui.min.js"></script>
                <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
                <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
                <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
                <script type="text/javascript" src="../resources/js/funciones.js"></script>                   

                <!-- Tables -->
                <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
                <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
                <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">  

                <!-- multiselect -->
                <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
                <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
                <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">';
            echo '<script type="text/javascript" language="javascript" src="../resources/js/paginas/nuevoTicket.js"></script>';
        } else {
            $path_previo = "";
            echo '<script type="text/javascript" language="javascript" src="resources/js/paginas/nuevoTicket.js"></script>';
        }
        ?>
        
        <script>
            $(function() {
                $("#tabs").tabs();
            });
        </script>
        <style>
            th {border: 1px black solid;text-align:center;font-size: 10px;background-color: #A4A4A4}
            .celda {border: 1px black solid;text-align:center;font-size: 10px;}
        </style>
        <script>
            function ver_detalle(fila) {
                $("#txt_serie").val($("#txt_serie" + fila).val());
                $("#txt_modelo").val($("#txt_modelo" + fila).val());
                $("#txt_contador_bn").val($("#txt_contador_bn" + fila).val());
                $("#txt_contador_color").val($("#txt_contador_color" + fila).val());
                $("#txt_nivel_negro").val($("#txt_nivel_negro" + fila).val());
                $("#txt_nivel_cian").val($("#txt_nivel_cian" + fila).val());
                $("#txt_nivel_magenta").val($("#txt_nivel_magenta" + fila).val());
                $("#txt_nivel_amarillo").val($("#txt_nivel_amarillo" + fila).val());
                $("#txt_contador_bn_ant").val($("#txt_contador_bn_ant" + fila).val());
                $("#txt_contador_color_ant").val($("#txt_contador_color_ant" + fila).val());
                $("#txt_nivel_negro_ant").val($("#txt_nivel_negro_ant" + fila).val());
                $("#txt_nivel_cian_ant").val($("#txt_nivel_cian_ant" + fila).val());
                $("#txt_nivel_magenta_ant").val($("#txt_nivel_magenta_ant" + fila).val());
                $("#txt_nivel_amarillo_ant").val($("#txt_nivel_amarillo_ant" + fila).val());
                if ($("#txt_color" + fila).val() == "NO") {
                    $("#tr_colo").hide();
                    $("#tr_cian").hide();
                    $("#tr_mg").hide();
                    $("#tr_am").hide();
                    $("#tr_colo_a").hide();
                    $("#tr_cian_a").hide();
                    $("#tr_mg_a").hide();
                    $("#tr_am_a").hide();
                }
                var active = $("#tabs").tabs("option", "active");
                $("#tabs").tabs("option", "active", active - 2);
            }
        </script>
    </head>
    <body>
        <table>
            <tr>
                <td>No. de ticket</td><td><input type="text" value="<?php echo $id_ticket ?>" readonly/></td>
                <td>Tipo de reporte</td>
                <td>
                    <select id="sltTipoReporte" name="sltTipoReporte" disabled>
                        <option value="0">Seleccione tipo de reporte</option>
                        <?php
                        $consultaTipo = "SELECT * FROM c_estado e,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND fe.IdFlujo=1";
                        $tipoReporteConsulta = $catalogo->obtenerLista($consultaTipo);
                        while ($rs = mysql_fetch_array($tipoReporteConsulta)) {
                            $s = "";
                            if ($tipoReporte == $rs['IdEstado']) {
                                $s = "selected";
                            }
                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td>Esatdo del ticket</td>
                <td>
                    <select id="sltEstadoTicket" name="sltEstadoTicket" disabled>
                        <option value="0">Seleccione el estado del ticket</option>  
                        <?php
                        $consultaEstadoTicket = "SELECT * FROM c_estadoticket et WHERE et.Activo=1 ORDER BY et.Nombre ASC";
                        $queryEstado = $catalogo->obtenerLista($consultaEstadoTicket);
                        while ($rs = mysql_fetch_array($queryEstado)) {
                            $s = "";
                            if ($id_estado == $rs['IdEstadoTicket']) {
                                $s = "selected";
                            }
                            echo "<option value='" . $rs['IdEstadoTicket'] . "' $s>" . $rs['Nombre'] . "</option>";
                        }
                        ?> 
                    </select>
                </td>
            </tr> <tr>
                <td>Fecha y hora ticket</td><td><input type="text" value="<?php echo $fechaTicket ?>" readonly/></td>
                <td>Nombre del usuario</td><td><input type="text" value="<?php echo $nombreUsuario ?>" readonly/></td>
                <td></td><td></td>
            </tr>
        </table>
        <br/><br/>
        <table style="border: 1px solid black;border-collapse: collapse;width:100%;">
            <tr>
                <th class="celda" style="width:10%">Fecha y hora</th><th class="celda" style="width:60%">Diagnostico</th><th class="celda" style="width:15%">Estatus de atención</th>
                <th class="celda" style="width:15%">Tipo solución</th><th class="celda">Técnico</th><th></th>
            </tr>
            <?php
            $consulta_nota = "SELECT nt.IdNotaTicket,nt.FechaHora,nt.DiagnosticoSol,nt.IdEstatusAtencion,e.Nombre AS estatus,nt.UsuarioUltimaModificacion FROM c_notaticket nt LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado WHERE nt.IdTicket='$id_ticket' AND nt.Activo=1 ORDER BY nt.FechaHora DESC";
            $query_nota = $catalogo->obtenerLista($consulta_nota);
            $contador = 0;
            while ($rs = mysql_fetch_array($query_nota)) {
                if ($contador % 2 == 0) {
                    $color = "style='background-color: #ffff99'";
                } else {
                    $color = "style='background-color: #ffffcc'";
                }
                echo "<tr $color>";
                echo "<td align='center' scope='row' style='font-size:11px' class='celda'>" . $rs['FechaHora'] . "</td>";
                echo "<td align='center' scope='row' style='font-size:11px' class='celda'>" . $rs['DiagnosticoSol'] . "</td>";
                echo "<td align='center' scope='row' style='font-size:11px' class='celda'>" . $rs['estatus'] . "</td>";
                echo "<td align='center' scope='row' style='font-size:11px' class='celda'></td>";
                echo "<td align='center' scope='row' style='font-size:11px' class='celda'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                echo "<td class='celda'>";
                ?>
                <a href='#' onclick='mostrarDetalleNota("<?php echo $path_previo; ?>mesa/detalleNota.php?frame=1", "<?php echo $rs['IdNotaTicket'] ?>", "<?php echo $tipoReporte ?>");
                            return false;' title='Detalle' > <img src='resources/images/Textpreview.png'/> </a>
                   <?php
                   echo "</td>";
                   echo "</tr>";
                   $contador++;
               }
               ?>
        </table>

        <br/><br/>
        <div>
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-1">Pedido</a></li>
                    <li><a href = "#tabs-2">Cliente</a></li>
                    <?php
                    if ($tipoReporte == "15") {
                        $noSerieEquipo = "";
                        $modeloEquipo = "";
                        echo "<li><a href = '#tabs-3'>Pedido</a></li>";
                    } else {
                        $queryLecturas = $catalogo->obtenerLista("SELECT lt.ContadorBN,lt.ContadorCL,lt.NivelTonNegro,lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,
                                                                    lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA ,lt.FechaA,lt.Comentario FROM c_lecturasticket lt WHERE lt.ClvEsp_Equipo='" . $noSerieEquipo . "'  AND lt.fk_idticket='$id_ticket'");
                        while ($rs3 = mysql_fetch_array($queryLecturas)) {
                            $fecha_anterior = $rs3['FechaA'];
                            $contador_negro_anterior = $rs3['ContadorBNA'];
                            $contador_color_anterior = $rs3['ContadorCLA'];
                            $contador_negro = $rs3['ContadorBN'];
                            $contador_color = $rs3['ContadorCL'];
                        }
                    }
                    ?>

                </ul>                
                <div id = "tabs-1" style = "background-color: #A4A4A4">
                    <fieldset><legend>Equipo</legend>
                        <table>
                            <tr><td>Número de serie del equipo</td><td><input type="text" id="txt_serie" disabled value="<?php echo $noSerieEquipo ?>"/></td></tr>
                            <tr><td>Modelo</td><td><input type="text" id="txt_modelo" disabled value="<?php echo $modeloEquipo ?>"/></td></tr>
                        </table>
                    </fieldset>                           

                    <fieldset>
                        <legend>Contadores y niveles de toner</legend>
                        <table style="width:100%">
                            <tr>
                                <td style="width:50%">
                                    <fieldset>
                                        <legend>Nuevos</legend>
                                        <table>
                                            <tr><td>Contador B/N</td><td><input type="text" id='txt_contador_bn' value="<?php echo $contador_negro ?>" readonly/></td></tr>
                                            <tr id="tr_colo"><td>Contador Color</td><td><input type="text" id='txt_contador_color' value="<?php echo $contador_color ?>" readonly/></td></tr>
                                            <?php if ($tipoReporte == "15") { ?>
                                                <tr><td>Nivel negro%</td><td><input type="text" id='txt_nivel_negro' readonly/></td></tr>
                                                <tr id="tr_cian"><td>Nivel cian%</td><td><input type="text" id='txt_nivel_cian' readonly/></td></tr>
                                                <tr id="tr_mg"><td>Nivel magenta%</td><td><input type="text" id='txt_nivel_magenta' readonly/></td></tr>
                                                <tr id="tr_am"><td>Nivel amarillo%</td><td><input type="text" id='txt_nivel_amarillo' readonly/></td></tr>
                                            <?php } ?>
                                        </table>
                                    </fieldset>
                                </td>
                                <td style="width:50%">
                                    <fieldset>
                                        <legend>Anteriores</legend>
                                        <table>
                                            <tr><td>Contador B/N</td><td><input type="text" id='txt_contador_bn_ant' value="<?php echo $contador_negro_anterior ?>" readonly/></td></tr>
                                            <tr id="tr_colo_a"><td>Contador Color</td><td><input type="text" id='txt_contador_color_ant' value="<?php echo $contador_color_anterior ?>" readonly/></td></tr>
                                            <?php if ($tipoReporte == "15") { ?>
                                                <tr><td>Nivel negro%</td><td><input type="text" id='txt_nivel_negro_ant' readonly/></td></tr>
                                                <tr id="tr_cian_a"><td>Nivel cian%</td><td><input type="text" id='txt_nivel_cian_ant' readonly/></td></tr>
                                                <tr id="tr_mg_a"><td>Nivel magenta%</td><td><input type="text" id='txt_nivel_magenta_ant' readonly/></td></tr>
                                                <tr id="tr_am_a"><td>Nivel amarillo%</td><td><input type="text" id='txt_nivel_amarillo_ant' readonly/></td></tr>
                                            <?php } ?>
                                        </table>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                    </fieldset>


                </div>
                <div id = "tabs-2" style = "background-color: #A4A4A4">
                    <fieldset><legend>Cliente</legend>
                        <table style="width: 100%">
                            <tr>
                                <td>Cliente:</td><td><input type="text"  value="<?php echo $claveCliente; ?>" style='width: 250px' readonly/></td>
                                <td>Nombre Empresa:</td><td><input type="text" value="<?php echo $nombreCliente; ?>" style='width: 250px' readonly/></td>
                            </tr>
                            <tr>
                                <td>Tipo de cliente</td><td><input type="text" value="<?php echo $tipoCliente; ?>" style='width: 250px' readonly/></td>                             
                                <td>Estatus de cobranza:</td><td><input type="text" value="<?php echo $estatusCobranza; ?>" style='width: 250px' readonly/></td>
                            </tr>
                            <tr>
                                <td>Localidad:</td><td><input type="text" style='width: 250px' value="<?php echo $nombreLocalidad ?>" readonly/></td>
                                <td>Nombre TFS:</td><td><input type="text" style='width: 250px' value="<?php echo $tfs ?>" readonly/></td>
                            </tr>
                        </table>
                        <fieldset>
                            <legend>Domicilio</legend> 
                            <table style="width: 100%">
                                <tr>
                                    <td>Tipo de domicilio:</td><td><input type="text" value="<?php echo $tipoDomicilio; ?>" style='width: 180px'  readonly/></td>
                                    <td>Zona:</td><td><input type="text" value="<?php echo $idZona ?>" style='width: 180px'  readonly/></td>
                                    <td></td><td></td>
                                </tr>
                                <tr>
                                    <td>Calle:</td><td><input type="text" value="<?php echo $calle; ?>" style='width: 180px'  readonly/></td>
                                    <td>No.exterior:</td><td><input type="text" value="<?php echo $nExterior; ?>" style='width: 180px'  readonly/></td>
                                    <td>No. interior:</td><td><input type="text" value="<?php echo $nInterior; ?>" style='width: 180px'  readonly/></td>
                                </tr>
                                <tr>
                                    <td>Colonia:</td><td><input type="text" value="<?php echo $colonia; ?>" style='width: 180px'  readonly/></td>
                                    <td>Ciudad:</td><td><input type="text" value="<?php echo $ciudad; ?>" style='width: 180px'  readonly/></td>
                                    <td>Estado:</td><td><input type="text" value="<?php echo $estadoLocal; ?>" style='width: 180px'  readonly/></td>
                                </tr>
                                <tr>
                                    <td>Delegación:</td><td><input type="text" value="<?php echo $delegacion; ?>" style='width: 180px' readonly /></td>
                                    <td>C.P:</td><td><input type="text" value="<?php echo $cp; ?>" style='width: 180px' readonly /></td>
                                    <td></td><td></td>
                                </tr>
                            </table>
                        </fieldset>
                        Contacto de la localidad
                        <table style="width: 100%">                                       
                            <tr>
                                <td>Nombre:</td><td><input type="text" id="txtNombreCFalla" name="txtNombreCToner" value="<?php echo $nombreContacto; ?>" style='width: 250px' <?php echo $readCliente; ?>/></td>
                                <td>Teléfono:</td><td><input type="text" id="txtTelefonoCFalla" name="txtTelefonoCToner" value="<?php echo $telefono; ?>" style='width: 250px' <?php echo $readCliente; ?> /></td>
                            </tr>
                            <tr>
                                <td>Celular:</td><td><input type="text" id="txtCelularCFalla" name="txtCelularCToner" value="<?php echo $celular; ?>" style='width: 250px' <?php echo $readCliente; ?> /></td>
                                <td>Correo electrónico:</td><td><input type="text" id="txtCorreoCFalla" name="txtCorreoCToner" value="<?php echo $correoE; ?>" style='width: 250px' <?php echo $readCliente; ?> /></td>
                            </tr>
                        </table>
                    </fieldset>
                </div>
                <?php if ($tipoReporte == "15") { ?>
                    <div id = "tabs-3" style = "background-color: #A4A4A4">
                        <table style="border: 1px solid black;border-collapse: collapse;width:100%;">
                            <tr>
                                <th>No. de Serie</th><th>Modelo</th><th>Ubicación</th><th>Color</th><th>Toner negro</th>
                                <th>Toner cian</th><th>Toner magenta</th><th>Toner amarillo</th><th>Estado</th><th>Detalle</th>
                            </tr>
                            <?php
                            $consulta_pedido = "SELECT p.IdPedido,p.IdTicket,p.ClaveEspEquipo,p.Modelo,p.TonerNegro,p.TonerCian,p.TonerMagenta,p.TonerAmarillo,p.Estado,lt.ContadorBN,lt.ContadorCL,
                                                    lt.NivelTonNegro,lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA,
                                                    IF(SUM(IF(fs.IdTipoServicio=1,1,0))>0,'SI','NO') AS color FROM c_pedido p LEFT JOIN c_lecturasticket lt ON p.IdLecturaTicket=lt.id_lecturaticket 
                                                     LEFT JOIN c_ticket t ON p.IdTicket=t.IdTicket LEFT JOIN c_equipo e ON e.Modelo=t.ModeloEquipo LEFT JOIN k_equipocaracteristicaformatoservicio fs ON e.NoParte=fs.NoParte
                                                    WHERE p.IdTicket=$id_ticket";
                            $query_pedido = $catalogo->obtenerLista($consulta_pedido);
                            $contador = 0;
                            while ($rs = mysql_fetch_array($query_pedido)) {
                                if ($contador % 2 == 0) {
                                    $color = "style='background-color: #ffff99'";
                                } else {
                                    $color = "style='background-color: #ffffcc'";
                                }
                                echo "<tr $color>";
                                echo "<td class='celda'>" . $rs['ClaveEspEquipo'] . "</td>";
                                echo "<td class='celda'>" . $rs['Modelo'] . "</td>";
                                echo "<td class='celda'></td>";
                                echo "<td class='celda'>" . $rs['color'] . "</td>";
                                echo "<td class='celda'>" . $rs['TonerNegro'] . "</td>";
                                echo "<td class='celda'>" . $rs['TonerCian'] . "</td>";
                                echo "<td class='celda'>" . $rs['TonerMagenta'] . "</td>";
                                echo "<td class='celda'>" . $rs['TonerAmarillo'] . "</td>";
                                echo "<td class='celda'>" . $rs['Estado'] . "</td>";
                                echo "<td class='celda'>";
                                ?>
                                <input type="hidden" id="txt_serie<?php echo $contador ?>" value="<?php echo $rs['ClaveEspEquipo']; ?>"/>
                                <input type="hidden" id="txt_modelo<?php echo $contador ?>" value="<?php echo $rs['Modelo']; ?>"/>
                                <input type="hidden" id='txt_contador_bn<?php echo $contador ?>'  value="<?php echo $rs['ContadorBN']; ?>" readonly/>
                                <input type="hidden" id='txt_contador_color<?php echo $contador ?>' value="<?php echo $rs['ContadorCL']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_negro<?php echo $contador ?>' value="<?php echo $rs['NivelTonNegro']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_cian<?php echo $contador ?>' value="<?php echo $rs['NivelTonCian']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_magenta<?php echo $contador ?>' value="<?php echo $rs['NivelTonMagenta']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_amarillo<?php echo $contador ?>' value="<?php echo $rs['NivelTonAmarillo']; ?>" readonly/>
                                <input type="hidden" id='txt_contador_bn_ant<?php echo $contador ?>' value="<?php echo $rs['ContadorBNA']; ?>" readonly/>
                                <input type="hidden" id='txt_contador_color_ant<?php echo $contador ?>' value="<?php echo $rs['ContadorCLA']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_negro_ant<?php echo $contador ?>' value="<?php echo $rs['NivelTonNegroA']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_cian_ant<?php echo $contador ?>' value="<?php echo $rs['NivelTonCianA']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_magenta_ant<?php echo $contador ?>' value="<?php echo $rs['NivelTonMagentaA']; ?>" readonly/>
                                <input type="hidden" id='txt_nivel_amarillo_ant<?php echo $contador ?>' value="<?php echo $rs['NivelTonAmarilloA']; ?>" readonly/>
                                <input type="hidden" id='txt_color<?php echo $contador ?>' value="<?php echo $rs['color']; ?>" readonly/>
                                <a href='#' onclick='ver_detalle(<?php echo $contador ?>);
                                                return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                   <?php
                                   echo "</td>";
                                   echo "</tr>";
                                   $contador++;
                               }
                               ?>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>
        <fieldset>
            <legend>Datos del ticket</legend>
            <fieldset>
                <legend>Contacto responsable del ticket</legend>
                <table style="width: 100%">
                    <tr>
                        <td>Nombre</td><td colspan="3"><input type="text"  value="<?php echo $nombreResponsable; ?>" style='width: 680px;' readonly/></td>
                    </tr>
                    <tr>
                        <td>Telefono 1:</td><td><input type="text"  value="<?php echo $telefono1Responsable; ?>" style='width: 250px'  /></td>
                        <td>Extención 1:</td><td><input type="text" value="<?php echo $extencion1Responsable; ?>" style='width: 250px'  readonly/></td>
                    </tr>
                    <tr>
                        <td>Telefono 2:</td><td><input type="text" value="<?php echo $telefono2Responsable; ?>" style='width: 250px'  readonly/></td>
                        <td>Extención 2:</td> <td><input type="text" value="<?php echo $extencion2Responsable; ?>" style='width: 250px' readonly/></td>
                    </tr>
                    <tr>
                        <td>Celular</td><td><input type="text" value="<?php echo $celularResponsable; ?>" style='width: 250px' /></td> 
                        <td>Correo electrónico</td><td><input type="text"  value="<?php echo $correoResponsable; ?>" style='width: 250px' /></td> 
                    </tr>  
                    <tr>
                        <td><b>Horario de atención:</b></td>
                        <td><b>Inicio</b> Hora <input type="text" value="<?php echo $auxHoraInicioRes[0] ?>" readonly style="width:30px"/>:<input type="text" value="<?php echo $auxHoraInicioRes[1] ?>" readonly style="width:30px"/><input type="text" value="<?php echo $auxHoraInicioRes[2] ?>" readonly style="width:30px"/></td>
                        <td><b>Fin</b> Hora</td>
                        <td><input type="text" value="<?php echo $auxHorafinRes[0] ?>" readonly style="width:30px"/>:<input type="text" value="<?php echo $auxHorafinRes[1] ?>" readonly style="width:30px"/>:<input type="text" value="<?php echo $auxHorafinRes[2] ?>" readonly style="width:30px"/></td>
                    </tr>
                </table>                
            </fieldset>
            <fieldset>
                <legend>Contacto de atención del ticket</legend>
                <table style="width: 100%">
                    <tr>
                        <td>Nombre</td><td colspan="3"><input type="text" value="<?php echo $nombreAtencion; ?>" style='width: 680px;' readonly/></td>
                    </tr>
                    <tr>
                        <td>Telefono 1:</td><td><input type="text"  value="<?php echo $telefono1Atencion; ?>" style='width: 250px'  /></td>
                        <td>Extención 1:</td><td><input type="text" value="<?php echo $extencion1Atencion; ?>" style='width: 250px'  readonly/></td>
                    </tr>
                    <tr>
                        <td>Telefono 2:</td><td><input type="text" value="<?php echo $telefono2Atencion; ?>" style='width: 250px'  readonly/></td>
                        <td>Extención 2:</td><td><input type="text" value="<?php echo $extencion2Atencion; ?>" style='width: 250px' readonly/></td>
                    </tr>
                    <tr>
                        <td>Celular</td><td><input type="text" value="<?php echo $celularAtencion; ?>" style='width: 250px' /></td> 
                        <td>Correo electrónico</td><td><input type="text"  value="<?php echo $correoAtencion; ?>" style='width: 250px' /></td> 
                    </tr>  
                    <tr>
                        <td><b>Horario de atención:</b></td>
                        <td><b>Inicio</b> Hora <input type="text" value="<?php echo $auxHoraInicioAt[0] ?>" readonly style="width:30px"/>:<input type="text" value="<?php echo $auxHoraInicioAt[1] ?>" readonly style="width:30px"/><input type="text" value="<?php echo $auxHoraInicioAt[2] ?>" readonly style="width:30px"/></td>
                        <td><b>Fin</b> Hora</td>
                        <td><input type="text" value="<?php echo $auxHorafinAt[0] ?>" readonly style="width:30px"/>:<input type="text" value="<?php echo $auxHorafinAt[1] ?>" readonly style="width:30px"/>:<input type="text" value="<?php echo $auxHorafinAt[2] ?>" readonly style="width:30px"/></td>
                    </tr>
                </table>              
            </fieldset>
            <table style="width:  100%">
                <tr>
                    <td>No. ticket cliente:</td><td><input type="text" id="txtNoTicketClienteGral" name="txtNoTicketClienteGral" readonly value='<?php echo $ticketCliente ?>'/></td>
                    <td>No. ticket distribuidor:</td><td><input type="text" id="txtNoTicketDistribucionGral" name="txtNoTicketDistribucionGral" readonly value='<?php echo $ticketDistribuidor; ?>'/></td>
                </tr>   
                <tr>
                    <td>Descripción del reporte:</td>
                    <td style="width: 85%" colspan="3"><textarea style="width: 100%; height: 150px;" readonly><?php echo $descripcionTicket; ?></textarea></td>
                </tr>
                <tr>
                    <td>Observaciones adicionales:</td>
                    <td style="width: 85%" colspan="3"><textarea style="width: 100%;height: 150px;" id='observacion' name='observacion' readonly><?php echo $observaciones; ?></textarea></td>
                </tr>   
                <tr>
                    <td>Área de atención:</td>
                    <td>
                        <select id="areaAtencionGral" name="areaAtencionGral" style="width: 300px" disabled="">

                            <?php
                            $consulta_area = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e  INNER JOIN k_flujoestado fe ON fe.IdEstado=e.IdEstado INNER JOIN c_flujo f ON fe.IdFlujo=f.IdFlujo WHERE  f.IdFlujo IN (2,3)";
                            $query_area = $catalogo->obtenerLista($consulta_area);
                            while ($rs = mysql_fetch_array($query_area)) {
                                $s = "";
                                if ($areaAtencion != "" && $areaAtencion == $rs['IdEstado']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>   
            </table>
        </fieldset>
        <div id="detalleNota">
            
        </div>
    </body>
</html>