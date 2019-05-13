<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");

$permisos_grid = new PermisosSubMenu();
$lecturaTicket = new LecturaTicket();
$lecturaTicket2 = new LecturaTicket();
$same_page = "tfs/ReportarCambioToner.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
date_default_timezone_get();
$fechaHoraActual = ""; //= date("d-m-Y H:i:s");
$catalogo = new Catalogo();
$cliente = "";
$localidad = "";
$noParteEquipo = "";
$tipoUsuario = "";
$tfs = "";
$almacen = "";
$noSerie = "";
$consultaCliente = "";
$consultaLocalidad = "";
$consultaSeries = "";
$consultaToner = "";
$almacenAnterior = "";
$fechaHoraLectura = "";
$contadorNegro = "";
$contadorColor = "";
$nivelNegro = "";
$nivelCia = "";
$nivelMagenta = "";
$nivelAmarillo = "";
$read = "";
$formatoEquipo = "";
$nombreCliente = "";
$nombreCentroCosto = "";
$cantidadSolicitada = 1;
$modeloEquipo = "";
$noSerieSelect = "";
$colorToner = "";
$corteBN = 0;
$corteColor = 0;

// echo ("<h1>".$_SESSION['idUsuario']."</h1>");

if (isset($_POST['usuarioTFS']) && $_POST['usuarioTFS'] != "") {
    $tfs = $_POST['usuarioTFS'];
    $consultaCliente = "SELECT c.ClaveCliente,c.NombreRazonSocial,tfsc.IdUsuario 
        FROM c_cliente c,k_tfscliente tfsc,c_centrocosto cc ,k_minialmacenlocalidad ml WHERE c.ClaveCliente=tfsc.ClaveCliente 
        AND tfsc.IdUsuario='$tfs' AND cc.ClaveCliente=c.ClaveCliente AND cc.ClaveCentroCosto=ml.ClaveCentroCosto  GROUP BY c.ClaveCliente ORDER BY c.NombreRazonSocial ASC  ";
}

$queryTipoUsuario = $catalogo->obtenerLista("SELECT u.IdPuesto AS usuario FROM c_usuario u WHERE u.IdUsuario='" . $_SESSION['idUsuario'] . "'");
while ($rs = mysql_fetch_array($queryTipoUsuario)) {
    $tipoUsuario = $rs['usuario'];
}

if (isset($_POST['claveCliente']) && $_POST['claveCliente'] != "") {
    $cliente = $_POST['claveCliente'];
    // $cliente = '10126';
    $consultaLocalidad = "SELECT cc.ClaveCentroCosto,cc.Nombre
                            FROM c_centrocosto cc,k_minialmacenlocalidad ml
                            WHERE cc.ClaveCliente='$cliente' AND ml.ClaveCentroCosto=cc.ClaveCentroCosto GROUP BY cc.ClaveCentroCosto";
}

if (isset($_POST['claveLocalidad']) && $_POST['claveLocalidad'] != "") {
    $localidad = $_POST['claveLocalidad'];    
    $consultaSeries = "SELECT ie.NoSerie,ie.NoParteEquipo,e.Modelo FROM k_anexoclientecc ax,c_inventarioequipo ie,c_equipo e 
                        WHERE ax.IdAnexoClienteCC=ie.IdAnexoClienteCC AND ax.CveEspClienteCC='$localidad' AND e.NoParte=ie.NoParteEquipo";
}

if (isset($_POST['noParteEquipo']) && $_POST['noParteEquipo'] != "") {
    $noParteEquipo = $_POST['noParteEquipo'];    
    $consultaToner = "SELECT c.NoParte,c.Modelo,c.Descripcion,ca.id_almacen,c.IdColor,(SELECT cfs2.IdTipoServicio FROM k_equipocaracteristicaformatoservicio cfs2 WHERE cfs2.NoParte=e.NoParte AND cfs2.IdTipoServicio<>2 ORDER BY cfs2.IdTipoServicio ASC LIMIT 1) AS formato 
                        FROM c_equipo e ,k_equipocomponentecompatible ecc,c_componente c,k_almacencomponente ca,k_minialmacenlocalidad ml,c_centrocosto cc 
                        WHERE ecc.NoParteEquipo=e.NoParte AND ecc.NoParteComponente=c.NoParte AND c.NoParte=ca.NoParte AND ca.id_almacen=ml.IdAlmacen AND cc.ClaveCentroCosto=ml.ClaveCentroCosto AND cc.ClaveCentroCosto='$localidad' AND e.NoParte='$noParteEquipo' AND ca.cantidad_existencia>0 AND c.IdTipoComponente=2";
}


if (isset($_POST['noSerieEquipos']) && $_POST['noSerieEquipos'] != "") {
    $noSerieSelect = $_POST['noSerieEquipos'];
    $lecturaTicket->setNoSerie($noSerieSelect);
    $lecturaTicket->getLecturaTonerByNoSerie();
    $lecturaTicket2->setNoSerie($noSerieSelect);
    $lecturaTicket2->getUltimaLecturaCorte();
    $corteBN = $lecturaTicket2->getContadorBNA();
    $corteColor = $lecturaTicket2->getContadorColorA();
}

$permisoEspecialRendimiento = "";
if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 12)) {
    $permisoEspecialRendimiento = "1";
} else {
    $permisoEspecialRendimiento = "0";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/reportarCambioToner.js"></script>
        <script>
            $(document).ready(function () {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div>        


            <form id="frmReporteCambioToner" name="frmReporteCambioToner" action="/" method="POST">
                <table style="width: 70%">
                    <tr>
                        <td>Buscar Serie</td>
                        <td>
                            <input type="text" id="buscar_serie" name="buscar_serie" value='<?php echo $noSerieSelect; ?>' style="width: 220px; margin-top: 15px;"/>&nbsp;&nbsp;
                            <input type="button" id="btn_buscar_serie" name="btn_buscar_serie" value="Buscar Serie" 
                                   class="boton" style="margin-bottom: 20px;" onclick="buscarSerie('buscar_serie');"/>
                        </td>
                        <td><div style="display: none; color: red;" id="error_serie"></div></td>
                    </tr>
                    <?php

                    if ($tipoUsuario == "21") {
                        $consultaCliente = " SELECT c.ClaveCliente,c.NombreRazonSocial,tfsc.IdUsuario FROM c_cliente c,k_tfscliente tfsc, c_centrocosto cc ,k_minialmacenlocalidad ml WHERE c.ClaveCliente=tfsc.ClaveCliente 
                                        AND tfsc.IdUsuario='" . $_SESSION['idUsuario'] . "' AND cc.ClaveCliente=c.ClaveCliente AND cc.ClaveCentroCosto=ml.ClaveCentroCosto  GROUP BY c.ClaveCliente ORDER BY c.NombreRazonSocial ASC";
                    } else {
                        echo "<tr>";
                        echo "<td>Tfs</td>";
                        echo "<td>";
                        ?>
                        <select id="tfS" name="tfS" onchange="MostrarClientesTFS(this.value);" style="width: 200px" class="filtro">
                            <option value="0">Seleccione un TFS</option>
                            <?php
                            $queryTFS = $catalogo->obtenerLista("SELECT u.IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS tfs FROM c_usuario u,k_tfscliente tf WHERE u.IdUsuario=tf.IdUsuario GROUP BY u.IdUsuario  ORDER BY tfs ASC ");
                            while ($rs = mysql_fetch_array($queryTFS)) {
                                $s = "";
                                if ($tfs != "" && $tfs == $rs['IdUsuario']) {
                                    $s = "selected";
                                }
                                echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['tfs'] . "</option>";
                            }
                            ?>
                        </select>
                        <?php
                        echo "</td>";
                        echo "<td></td>";
                        echo "</tr>";
                    }
                    ?>
                    <tr>
                        <td>Cliente</td>
                        <td>
                            <select id="cliente" name="cliente" onchange="MostrarLocalidadCliente(this.value);" style="width: 200px" class="filtro">
                                <!--<option value="0">Seleccione un cliente</option>-->
                                <?php
                                if ($consultaCliente != "") {
                                    $queryCliente = $catalogo->obtenerLista($consultaCliente);
                                    if (mysql_num_rows($queryCliente) == 1) {
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $cliente = $rs['ClaveCliente'];
                                            // $cliente = '10126';
                                            $nombreCliente = $rs['NombreRazonSocial'];
                                            $s = "selected";
                                            echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                        $consultaLocalidad = "SELECT cc.ClaveCentroCosto,cc.Nombre
                                                            FROM c_centrocosto cc,k_minialmacenlocalidad ml
                                                            WHERE cc.ClaveCliente='$cliente' AND ml.ClaveCentroCosto=cc.ClaveCentroCosto GROUP BY cc.ClaveCentroCosto";
                                    } else {
                                        echo " <option value='0'>Seleccione un cliente</option>";
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $s = "";
                                            if ($cliente != "" && $cliente == $rs['ClaveCliente']) {
                                                $nombreCliente = $rs['NombreRazonSocial'];
                                                $s = "selected";
                                            }
                                            echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Localidad</td>
                        <td>
                            <select id="Localidad" name="Localidad"  onchange="MostrarEquiposLocalidad(this.value);" style="width: 200px" class="filtro">
                                <?php
                                if ($cliente != "" && $consultaLocalidad != "") {
                                    $queryCliente = $catalogo->obtenerLista($consultaLocalidad);
                                    if (mysql_num_rows($queryCliente) == 1) {
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $localidad = $rs['ClaveCentroCosto'];
                                            $nombreCentroCosto = $rs['Nombre'];
                                            $s = "selected";
                                            echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                        }
                                        $consultaSeries = "SELECT ie.NoSerie,ie.NoParteEquipo,e.Modelo FROM k_anexoclientecc ax,c_inventarioequipo ie,c_equipo e 
                                        WHERE ax.IdAnexoClienteCC=ie.IdAnexoClienteCC AND ax.CveEspClienteCC='$localidad' AND e.NoParte=ie.NoParteEquipo";
                                    } else {
                                        echo "<option value = '0'>Seleccione una localidad</option>";
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $s = "";
                                            if ($localidad != "" && $localidad == $rs['ClaveCentroCosto']) {
                                                $nombreCentroCosto = $rs['Nombre'];
                                                $s = "selected";
                                            }
                                            echo "<option value = " . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Serie</td>
                        <td>
                            <select id="NoSerie" name="NoSerie"  onchange="MostrarComponentesCompatible(this.value);" style="width: 200px" class="filtro">
                                <?php
                                $hay_opciones = false;
                                if ($localidad != "" && $consultaSeries != "") {
                                    $queryCliente = $catalogo->obtenerLista($consultaSeries);
                                    if (mysql_num_rows($queryCliente) == 1) {
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $noParteEquipo = $rs['NoParteEquipo'];
                                            $modeloEquipo = $rs['Modelo'];
                                            $noSerie = $rs['NoSerie'];
                                            $s = "selected";                                                                            
                                            echo "<option value = " . $rs['NoParteEquipo'] . " " . $s . ">" . $rs['NoSerie'] . " / " . $rs['Modelo'] . "</option>";
                                            $hay_opciones = true;
                                        }
                                        $consultaToner = "SELECT c.NoParte,c.Modelo,c.Descripcion,ca.id_almacen,c.IdColor,(SELECT cfs2.IdTipoServicio FROM k_equipocaracteristicaformatoservicio cfs2 WHERE cfs2.NoParte=e.NoParte AND cfs2.IdTipoServicio<>2 ORDER BY cfs2.IdTipoServicio ASC LIMIT 1) AS formato 
                                                                FROM c_equipo e ,k_equipocomponentecompatible ecc,c_componente c,k_almacencomponente ca,k_minialmacenlocalidad ml,c_centrocosto cc 
                                                                WHERE ecc.NoParteEquipo=e.NoParte AND ecc.NoParteComponente=c.NoParte AND c.NoParte=ca.NoParte AND ca.id_almacen=ml.IdAlmacen AND cc.ClaveCentroCosto=ml.ClaveCentroCosto AND cc.ClaveCentroCosto='$localidad' AND e.NoParte='$noParteEquipo'  AND ca.cantidad_existencia>0 AND c.IdTipoComponente=2 ";
                                    } else {
                                        echo "<option value = '0'>Seleccione número de serie</option>";
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $s = "";
                                            if ($noSerieSelect != "" && $noSerieSelect == $rs['NoSerie']) {
                                                $modeloEquipo = $rs['Modelo'];
                                                $noSerie = $rs['NoSerie'];
                                                $s = "selected";
                                            }
                                            echo "<option value = '" . $rs['NoParteEquipo'] . " / " . $rs['NoSerie'] . "' $s>" . $rs['NoSerie'] . " / " . $rs['Modelo'] . "</option>";
                                            $hay_opciones = true;
                                        }
                                    }
                                }
                                if (!$hay_opciones && $noSerieSelect != "") {
                                    $configuracion = new Configuracion();
                                    if ($configuracion->getRegistroByNoSerie($noSerieSelect)) {
                                        $equipo = new Equipo();
                                        $equipo->getRegistroById($configuracion->getNoParte());

                                        $noParteEquipo = $equipo->getNoParte();
                                        $modeloEquipo = $equipo->getModelo();                                        
                                        $noSerie = $configuracion->getNoSerie();
                                        echo "<option value = '" . $configuracion->getNoParte() . " / " . $configuracion->getNoSerie() . "' selected='selected'>
                                                " . $configuracion->getNoSerie() . " / " . $equipo->getModelo() . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td></td>
                    </tr>                   
                    <tr>
                        <td>Toner </td>
                        <td>
                            <select id="toner" name="toner" style="width: 200px;" class="filtro" onchange="mostraContadoresToner(this.value);">                                
                                <?php
                                if ($noParteEquipo != "" && $consultaToner != "") {
                                    $queryCliente = $catalogo->obtenerLista($consultaToner);
                                    if (mysql_num_rows($queryCliente) == 1) {
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $almacenAnterior = $rs['id_almacen'];
                                            $formatoEquipo = $rs['formato'];
                                            $colorToner = $rs['IdColor'];
                                            $s = "selected";
                                            echo "<option value = '" . $rs['NoParte'] . " // " . $rs['IdColor'] . "' $s>" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                        }
                                    } else {
                                        echo "<option value='0'>Seleccione toner</option>";
                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                            $almacenAnterior = $rs['id_almacen'];
                                            $formatoEquipo = $rs['formato'];
                                            $s = "";
                                            if ($toner != "" && $toner == $rs['NoParte']) {
                                                $s = "selected";
                                            }
                                            echo "<option value = '" . $rs['NoParte'] . " // " . $rs['IdColor'] . "' $s>" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </td>
                        <td><input type="hidden" name="cantidadToner" id="cantidadToner" value="<?php echo $cantidadSolicitada; ?>" /></td>                        
                    </tr>                    
                </table>  
                <div id="divContadores" style="width: 100%;" >
                    <?php
                    if ($noSerie != "") {
                        $fechaHoraActual = date("d-m-Y H:i:s");
                    }
                    if ($noSerie != "" && $colorToner != "") {
                        $read = "readonly";
                        $consultaNiveles = "SELECT (CASE WHEN !ISNULL(lt.Fecha) THEN lt.Fecha ELSE t.FechaHora END) AS Fecha,lt.ContadorBN AS ContadorBN,lt.ContadorCL AS ContadorCL,lt.ContadorBNA AS ContadorBNML,
                                            lt.ContadorCLA AS ContadorCLML,lt.NivelTonNegro AS NivelTonNegro,lt.NivelTonCian AS NivelTonCian,lt.NivelTonMagenta AS NivelTonMagenta,
                                            lt.NivelTonAmarillo AS NivelTonAmarillo
                                            FROM c_lecturasticket lt INNER JOIN c_ticket t ON t.IdTicket =(SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 LEFT JOIN c_notaticket AS nt  ON nt.IdNotaTicket = 
                                            (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket) WHERE lt.fk_idticket = t2.IdTicket AND t2.TipoReporte = 15 AND t2.EstadoDeTicket <> 4 
                                            AND (nt.IdEstatusAtencion <> 59 OR ISNULL(nt.IdEstatusAtencion))) INNER JOIN c_notaticket nt3 ON nt3.IdTicket=t.IdTicket 
                                            INNER JOIN k_nota_refaccion nr ON nt3.IdNotaTicket=nr.IdNotaTicket INNER JOIN c_componente c ON c.NoParte=nr.NoParteComponente aND c.IdColor=$colorToner
                                            WHERE lt.ClvEsp_Equipo='$noSerie' ORDER BY t.IdTicket DESC LIMIT 0,1";
                        $query = $catalogo->obtenerLista($consultaNiveles);
                        if ($rs = mysql_fetch_array($query)) {
                            list($fecha, $hora) = explode(" ", $fechaContador = $rs['Fecha']);
                            list($anio, $mes, $dia) = explode("-", $fecha);
                            $fechaHoraLectura = $dia . "-" . $mes . "-" . $anio . " " . $hora;
                            $contadorNegro = $rs['ContadorBN'];
                            $contadorColor = $rs['ContadorCL'];
                            $nivelNegro = $rs['NivelTonNegro'];
                            $nivelCia = $rs['NivelTonCian'];
                            $nivelMagenta = $rs['NivelTonMagenta'];
                            $nivelAmarillo = $rs['NivelTonAmarillo'];
                        }
                    }
                    ?>
                    <div>
                        <fieldset>
                            <legend>Contadores y niveles de toner</legend> 
                            <table style="width: 100%">
                                <tr>
                                    <td style="width: 50%">
                                        <fieldset>
                                            <legend>Captura contador y niveles actuales</legend> 
                                            <table style="width: 100%">
                                                <tr>
                                                    <td>Fecha:</td><td><input type="text" id="txtFechaContadorNuevo" name="txtFechaContadorNuevo" value="<?php echo $fechaHoraActual; ?>" <?php echo $read; ?>/></td>
                                                </tr>  
                                                <?php if ($formatoEquipo == "1" || $formatoEquipo == "") { ?>
                                                    <tr>
                                                        <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNNuevo" name="txtContadorBNNuevo" /></td>
                                                    </tr>

                                                    <tr>
                                                        <td>Contador color(páginas):</td><td><input type="text" id="txtContadorColorNuevo" name="txtContadorColorNuevo"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroNuevo" name="txtNivelNegroNuevo"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner cian(%):</td><td><input type="text" id="txtNivelCainNuevo" name="txtNivelCainNuevo"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner magenta(%):</td><td><input type="text" id="txtNivelMagentaNuevo" name="txtNivelMagentaNuevo"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner amarillo(%):</td><td><input type="text" id="txtNivelAmarilloNuevo" name="txtNivelAmarilloNuevo"/></td>
                                                    </tr>     
                                                <?php } else if ($formatoEquipo == "3") { ?>
                                                    <tr>
                                                        <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNNuevo" name="txtContadorBNNuevo" /></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroNuevo" name="txtNivelNegroNuevo"/></td>
                                                    </tr>
                                                <?php } ?>
                                            </table>
                                        </fieldset>
                                    </td>
                                    <td style="width: 50%">
                                        <fieldset>
                                            <legend>Contador anterior y niveles</legend> 
                                            <table style="width: 100%">
                                                <tr>
                                                    <td>Fecha:</td><td><input type="text" id="txtFechaContadorAnterior" name="txtFechaContadorAnterior" <?php echo $read; ?> value="<?php echo $fechaHoraLectura; ?>"/></td>
                                                </tr>
                                                <?php if ($formatoEquipo == "1" || $formatoEquipo == "") { ?>
                                                    <tr>
                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroAnterior" name="txtNivelNegroAnterior" <?php echo $read; ?> value="<?php echo $nivelNegro; ?>"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner cian(%):</td><td><input type="text" id="txtNivelCainAnterior" name="txtNivelCainAnterior" <?php echo $read; ?> value="<?php echo $nivelCia; ?>"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner magenta(%):</td><td><input type="text" id="txtNivelMagentaAnterior" name="txtNivelMagentaAnterior"<?php echo $read; ?> value="<?php echo $nivelMagenta; ?>"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Nivel de toner amarillo(%):</td><td><input type="text" id="txtNivelAmarilloAnterior" name="txtNivelAmarilloAnterior" <?php echo $read; ?> value="<?php echo $nivelAmarillo; ?>"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Última lectura cambio tóner B/N</b></td>
                                                        <td><b><?php //echo $lecturaTicket->getContadorBNA(); ?></b>
                                                            <input type="text" id="txtContadorBNAnterior" name="txtContadorBNAnterior" readonly="readonly" value="<?php echo $lecturaTicket->getContadorBNA(); ?>"/>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Última lectura cambio tóner color</b></td>
                                                        <td>
                                                            <b><?php //echo $lecturaTicket->getContadorColorA(); ?></b>
                                                            <input type="text" id="txtContadorColorAnterior" name="txtContadorColorAnterior" <?php echo $read; ?> value="<?php echo $lecturaTicket->getContadorColorA(); ?>"/>                                                            
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Última lectura corte B/N</b></td><td><b><?php echo $lecturaTicket2->getContadorBNA(); ?></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Última lectura corte color</b></td><td><b><?php echo $lecturaTicket2->getContadorColorA(); ?></b></td>
                                                    </tr>
                                                <?php } else if ($formatoEquipo == "3") { ?>
                                                    <tr>
                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroAnterior" name="txtNivelNegroAnterior" <?php echo $read; ?> value="<?php echo $nivelNegro; ?>"/></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Última lectura cambio tóner B/N</b></td>
                                                        <td>
                                                            <b><?php echo $lecturaTicket->getContadorBNA(); ?></b>
                                                            <input type="text" id="txtContadorBNAnterior" name="txtContadorBNAnterior" readonly="readonly" value="<?php echo $lecturaTicket->getContadorBNA(); ?>"/>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Última lectura corte B/N</b></td><td><b><?php echo $lecturaTicket2->getContadorBNA(); ?></b></td>
                                                    </tr>
                                                <?php } ?>
                                            </table>                                            
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </div>
                </div>
                <?php if(!isset($corteBN)) {$corteBN = 0; $corteColor = 0; } ?>
                <input type="hidden" name="lecturaCorteBN" id="lecturaCorteBN" value="<?php echo $corteBN; ?>"/>
                <input type="hidden" name="lecturaCorteColor" id="lecturaCorteColor" value="<?php echo $corteColor; ?>"/>
                <input type="hidden" name="txtPermisoRendimiento" id="txtPermisoRendimiento" value="<?php echo $permisoEspecialRendimiento; ?>" />
                <input type="hidden" name="almacen" id="almacen"  value="<?php echo $almacenAnterior; ?>"/>
                <input type="hidden" name="noSerie2" id="noSerie2"  value="<?php echo $noSerie; ?>"/>
                <input type="hidden" name="ModeloEquipo" id="modeloEquipo"  value="<?php echo $modeloEquipo; ?>"/>
                <input type="hidden" name="modeloComponente" id="modeloComponente"  value="<?php echo $modelocomponente; ?>"/>
                <input type="hidden" name="nombreCliente" id="nombreCliente"  value="<?php echo $nombreCliente; ?>"/>
                <input type="hidden" name="nombreCentroCosto" id="nombreCentroCosto"  value="<?php echo $nombreCentroCosto; ?>"/>
                <?php if ($permisos_grid->getModificar()) { ?>
                    <input type="submit" class="boton" value="Guardar" />
                <?php } ?>
            </form>
        </div>
        <div id="dialog"></div>
    </body>    
</html>