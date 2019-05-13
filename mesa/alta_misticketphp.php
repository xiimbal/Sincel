<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");

$catalogo = new Catalogo();
$estadoTicket = "3";
$noSerie = "";
$lecturaTicket = new LecturaTicket();
$fechaContadorAnterior = "";
$contadorNegroAnterior = "";
$contadorColorAnterior = "";
$claveLocalidad = null;
$tipoServicio = null;
$UbicaiconNoDomicilio = null;

if (isset($_POST['claveLocalidad']) && $_POST['claveLocalidad'] != "") {
    $claveLocalidad = $_POST['claveLocalidad'];
}

$nombreContacto = "";
$telefono = "";
$celular = "";
$correoE = "";
$idContacto = "";
$IdTipoContacto = "";
$claveEspecialContacto = "";
$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/alta_misticketphp.php";
$nombreTabla = "tAlmacen";
if (isset($_POST['regresar']) && $_POST['regresar']) {
    $pagina_listaRegresar = $_POST['regresar'];
} else {
    $pagina_listaRegresar = "mesa/lista_ticket.php";
}

if (isset($_POST["noSerie"]) && $_POST["noSerie"] != "") {
    $noSerie = $_POST["noSerie"];
}
if (isset($_POST["area"]) && $_POST["area"] != "") {
    $tipoReporte = $_POST["area"];
}

if ($noSerie != "") {//datos del equipo
    $consulta = "SELECT ie.NoSerie,(SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
             THEN (SELECT cc.ClaveCentroCosto FROM k_anexoclientecc an,c_centrocosto cc WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC)
             ELSE (SELECT cc.ClaveCentroCosto FROM c_centrocosto cc,k_serviciogimgfa sg WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto)END )AS Localidad,
             (SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
             THEN (SELECT cc.ClaveCliente FROM k_anexoclientecc an,c_centrocosto cc WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC)
             ELSE (SELECT cc.ClaveCliente FROM c_centrocosto cc,k_serviciogimgfa sg WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto)END )AS Cliente,fs.IdTipoServicio AS tipoServicio
             FROM c_inventarioequipo ie,k_equipocaracteristicaformatoservicio fs WHERE ie.NoSerie='$noSerie' AND fs.NoParte=ie.NoParteEquipo AND fs.IdTipoServicio<>2 ORDER BY fs.IdFormatoEquipo ASC LIMIT 1";
    $queryEquipoSerie = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($queryEquipoSerie)) {
        $claveLocalidad = $rs['Localidad'];
        $claveCliente = $rs['Cliente'];
        $tipoServicio = $rs['tipoServicio'];
    }
    $lecturaTicket->setNoSerie($noSerie);
    $lecturaTicket->getLecturaBYNoSerie();
    $fechaContadorAnterior = $lecturaTicket->getFechaA();
    $contadorNegroAnterior = $lecturaTicket->getContadorBNA();
    $contadorColorAnterior = $lecturaTicket->getContadorColorA();
    $queryUbucaion = $catalogo->obtenerLista("SELECT ie.Ubicacion FROM c_inventarioequipo ie WHERE ie.NoSerie='$noSerie'");
    while ($rs = mysql_fetch_array($queryUbucaion)) {
        $UbicaiconNoDomicilio = $rs['Ubicacion'];
    }
}
   
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
if(isset($claveLocalidad))
{
    $consultaCliente = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.IdTipoCliente, cc.ClaveCentroCosto as ClaveEspecial, "
        . "(SELECT ct.Nombre FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS nombreContacto, "
        . "(SELECT ct.Telefono FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Telefono,
        (SELECT ct.Celular FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Celular,
        (SELECT ct.CorreoElectronico FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS CorreoElectronico,
        (SELECT ct.IdContacto FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS IdContacto 
        from c_cliente c, c_centrocosto cc "
        . "LEFT JOIN k_usuarionegocio AS un ON un.ClaveCliente = cc.ClaveCliente "
        . "WHERE cc.ClaveCliente=c.ClaveCliente AND cc.ClaveCentroCosto='$claveLocalidad' AND un.IdUsuario = ".$_SESSION['idUsuario'];
}else{
    $consultaCliente = "SELECT c.ClaveCliente, c.NombreRazonSocial from c_cliente c"
        . " LEFT JOIN k_usuarionegocio AS un ON un.ClaveCliente = c.ClaveCliente "
        . "WHERE un.IdUsuario = ".$_SESSION['idUsuario'];
}

   ?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/nuevoTicketCliente.js"></script>
        <script>
            $(function() {
                $("#tabs").tabs();
            });
        </script>
        <script>
            $(function() {

                var availableTags = [
            <?php
            $arrayNoSerie = array();
            $c = 0;
            $query1 = $catalogo->obtenerLista("SELECT ie.NoSerie,an.CveEspClienteCC FROM c_inventarioequipo ie,k_anexoclientecc an WHERE ie.IdAnexoClienteCC=an.IdAnexoClienteCC");
            while ($rs = mysql_fetch_array($query1)) {
                $arrayNoSerie[$c] = $rs['NoSerie'];
                $c++;
            }
            for ($x = 0; $x < count($arrayNoSerie); $x++) {
                echo "'" . $arrayNoSerie[$x] . "',";
            }
            ?>
                ];
                $("#txtNoSrieFallaBuscar").autocomplete({
                    source: availableTags,
                    minLength: 2
                });
            });</script>
        <script>
            $(document).ready(function() {
                $('.boton').button();
            });
        </script>
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">

    </head>

    <body>
        <div class="principal"> 
            <form id="frmAltaTicket" name="frmAltaTicket" action="/" method="POST">
                <div>
                    <div class="container-fluid">   
                     <div class="form-row">
                       <div class="form-group col-md-6">
                      	<label >Tipo de reporte:</label>
                                <select class='form-control' id="sltTipoReporte" name="sltTipoReporte" onchange="MostrarTipoReporte(this.value);" <?php echo $descativarTipoReporte; ?>>
                                    <option value="1" selected>Fallo</option>>
                                </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Estado del ticket:</label> 
                                <select class='form-control' id="sltEstadoTicket" name="sltEstadoTicket"  <?php echo $drawList; ?> disabled>
                                    <option value="1">Seleccione el estado del ticket</option>  
                                    <?php
                                    $consultaEstadoTicket = "SELECT * FROM c_estadoticket et WHERE et.Activo=1 ORDER BY et.Nombre ASC";
                                    $queryEstado = $catalogo->obtenerLista($consultaEstadoTicket);
                                    while ($rs = mysql_fetch_array($queryEstado)) {
                                        $s = "";
                                        if ($estadoTicket == $rs['IdEstadoTicket'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['IdEstadoTicket'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            
                      </div>
                    </div>
                </div> 
                <br/><br/>
                <div>    
                    <div id="tabs">
                        <ul>
                            <li><a href="#tabs-1">Pedido</a></li>
                        </ul>
                    <div id = "tabs-1" style = "background-color: #A4A4A4">
                        <div class="form-row">
                            <div  id="busquedaSerie" class="form-group col-md-4">
                                    <Label>No serie:</Label>
                                    <input class="form-control" type="text" id='txtNoSrieFallaBuscar' name='txtNoSrieFallaBuscar' value="<?php echo $noSerie ?>"/>
                                    <input  class="button btn btn-lg btn-block btn-outline-defaul mt-3 mb-3" type='button' id="botonBuscar" name="botonBuscar" value="Buscar" onclick="bucarVentaDirecta()"  <?php echo $botonBuscar; ?>/>  

                                    <input type="hidden" id='slcCliente' name='slcCliente' value="<?php echo $claveCliente ?>"/>
                                    <input type="hidden" id='slcLocalidad' name='slcLocalidad' value="<?php echo $claveLocalidad ?>"/>
                            </div>

                        <div id="busquedaCliente" class="form-group col-md-4">
                                   <Label >Cliente:</label>
                                        <?php
                                            $queryCliente = $catalogo->obtenerLista($consultaCliente);
                                        ?>
                                        <select multiple  class="form-control" class="form-control" id="slcCliente" name="slcCliente"  onchange="incidenciaClienteSuspendidoFalla(this.value)"  class="filtro" <?php echo $descativarClienteLocalidad ?>>
                                            <?php
                                                if(mysql_num_rows($queryCliente) > 1){                                                
                                            ?>
                                            <option value="0">Seleccione un cliente</option>
                                            <?php
                                                }
                                            while ($rs = mysql_fetch_array($queryCliente)) {
                                                $s = "";
                                                if ($claveCliente != "" && $claveCliente == $rs['ClaveCliente']) {
                                                    $nombreCliente = $rs['NombreRazonSocial'];
                                                    $s = "selected";
                                                }
                                                if(mysql_num_rows($queryCliente) == 1){
                                                    $claveCliente = $rs['ClaveCliente'];
                                                }
                                                $nombreContacto = $rs['nombreContacto'];
                                                $telefono = $rs['Telefono'];
                                                $celular = $rs['Celular'];
                                                $correoE = $rs['CorreoElectronico'];
                                                $idContacto = $rs['IdContacto'];
                                                $IdTipoContacto = $rs['IdTipoCliente'];
                                                $claveEspecialContacto = $rs['ClaveEspecial'];
                                                echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                </div>
                     <div class="form-group col-md-4">
                                    <?php
                                    $permisoEspecial = "";
                                    if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 8)) {
                                        $permisoEspecial = "1";
                                    } else {
                                        $permisoEspecial = "0";
                                    }
                                    ?>
                                    <Label>Localidad:</label>                                         
                                        <select class= "form-control" id="slcLocalidad" name="slcLocalidad"  multiple  class="form-control" onchange="CambioLocalidadTicket(this.value);" <?php echo $descativarClienteLocalidad ?>>
                                            <?php
                                            if ($claveCliente != "") {
                                                $queryCliente = $catalogo->obtenerLista("SELECT cc.ClaveCentroCosto,cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCliente='$claveCliente' AND cc.Activo=1 ORDER BY cc.Nombre ASC;");
                                                echo " <option value='0'>Seleccione una localidad</option>";
                                                while ($rs = mysql_fetch_array($queryCliente)) {
                                                    $s = "";
                                                    if ($claveLocalidad != "" && $claveLocalidad == $rs['ClaveCentroCosto']) {
                                                        $nombreLocalidad = $rs['Nombre'];
                                                        $s = "selected";
                                                    }
                                                    echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
//                                                            }
                                                }
                                            }
                                            ?>
                                        </select>
                         </div>
                        </div>
                    </div>





                <?php if ($claveLocalidad != "") { ?>
                            <br/><br/>                                        
                            <table id="<?php echo $nombreTabla; ?>" class="tabla_datos" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; min-width:10%">No Serie</th>
                                        <th style="text-align: center; min-width: 20%">Modelo equipo</th>
                                        <th style="text-align: center; min-width: 15%">Contador B/N</th>
                                        <th style="text-align: center; min-width: 15%">Contador color</th>
                                        <th style="text-align: center; min-width: 15%">Comentario</th> 
                                        <th style="text-align: center; min-width: 15%">Reportar</th>   
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $onclickRadio = "";
                                    $whereSerie = "";
                                    if ($noSerie != "") {
                                        $whereSerie = "AND cie.NoSerie='$noSerie'";
                                    }
                                    $query = $catalogo->obtenerLista("SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,
                                        (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                        FROM k_anexoclientecc AS kacc LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                        LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo WHERE !ISNULL(cie.NoSerie) AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) $whereSerie ORDER BY NoSerie DESC");
                                    $contador = 0;
                                    while ($rs = mysql_fetch_array($query)) {

                                        $seleccionar = "";
                                        if ($noSerie == $rs['NoSerie']) {
                                            $seleccionar = "checked";
                                            if ($idTicket != "") {
                                                $queryLecturas = $catalogo->obtenerLista("SELECT lt.ContadorBN,lt.ContadorCL,lt.NivelTonNegro,
                                                                                        lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,
                                                                                        lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA ,lt.FechaA,lt.Comentario
                                                                                        FROM c_lecturasticket lt WHERE lt.ClvEsp_Equipo='" . $rs["NoSerie"] . "'  AND lt.fk_idticket='$idTicket'");
                                                while ($rs3 = mysql_fetch_array($queryLecturas)) {
                                                    $contadorNegroAnterior = $rs3['ContadorBNA'];
                                                    $contadorColorAnterior = $rs3['ContadorCLA'];
                                                    $nivelNegroAnterior = $rs3['NivelTonNegroA'];
                                                    $nivelCianAnterior = $rs3['NivelTonCianA'];
                                                    $nivelMagentaAnterior = $rs3['NivelTonMagentaA'];
                                                    $nivelAmarilloAnterior = $rs3['NivelTonAmarilloA'];
                                                    $fechaContadorAnterior = $rs3['FechaA'];
                                                    $contadorNegro = $rs3['ContadorBN'];
                                                    $contadorColor = $rs3['ContadorCL'];
                                                    $nivelNegro = $rs3['NivelTonNegro'];
                                                    $nivelCian = $rs3['NivelTonCian'];
                                                    $nivelMagenta = $rs3['NivelTonMagenta'];
                                                    $nivelAmarillo = $rs3['NivelTonAmarillo'];
                                                    $comentario_lectura = $rs3['Comentario'];
                                                }
                                            }
                                        }
                                        echo "<tr>";
                                        echo "<input type='hidden' name='tipoFormatoEquipo' id='tipoFormatoEquipo' value='" . $rs['tipoFormato'] . "'/>";
                                        echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['NoSerie'] . ""
                                        . "<input type='hidden' name='txtNoSerieE_$contador' id='txtNoSerieE_$contador' style='width: 80px;' value='" . $rs['NoSerie'] . "'/></td>";
                                        echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['Modelo'] . ""
                                        . "<input type='hidden' name='txtModeloE_$contador' id='txtModeloE_$contador' style='width: 80px;' value='" . $rs['Modelo'] . "'/>"
                                        . "<input type='hidden' name='txtfechaAnterior_$contador' id='txtfechaAnterior_$contador' style='width: 80px;' readonly/>"
                                        . "</td>";
                                        $anterior = "0";
                                        if ($contadorNegroAnterior != "") {
                                            $anterior = $contadorNegroAnterior;
                                            $nuevo = $anterior + 100000;
                                            $rango.="$('#txtContadorNegro_$contador').rules('add', {
                                            range:[$anterior,$nuevo],
                                              messages: {
                                                        range: 'El contador actual no debe superar en 100,000 al anterior'
                                                    }
                                            });";
                                        }

                                        echo "<td align='center' scope='row' style='font-size:11px'>"
                                        . "<br/>Anterior<input type='text' name='txtContadorNegroAnterior_$contador' id='txtContadorNegroAnterior_$contador' style='width: 80px;' value='$contadorNegroAnterior' readonly/>"
                                        . "<br/>actual<input type='text' name='txtContadorNegro_$contador' id='txtContadorNegro_$contador' style='width: 80px;' value='$contadorNegro'/></td>";
                                        if ($rs['tipoFormato'] != "1") {
                                            echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                        } else {
                                            $anterior = "0";
                                            if ($contadorColorAnterior != "") {
                                                $anterior = $contadorColorAnterior;
                                                $nuevo = $anterior + 100000;
                                                $rango.="$('#txtContadorColor_$contador').rules('add', {
                                                range:[$anterior,$nuevo],
                                                  messages: {
                                                            range: 'El contador actual no debe superar en 100,000 al anterior'
                                                        }
                                                });";
                                            }

                                            echo "<td align='center' scope='row' style='font-size:11px'>"
                                            . "<br/><input type='text' name='txtContadorColorAnterior_$contador' id='txtContadorColorAnterior_$contador' style='width: 80px;' value='$contadorColorAnterior' readonly/>"
                                            . "<br/><input type='text' name='txtContadorColor_$contador' id='txtContadorColor_$contador' style='width: 80px; ' value='$contadorColor'/></td>";
                                        }
                                        echo "<td align='center' scope='row' style='font-size:11px'><textarea id='comentario_$contador' name='comentario_$contador'>$comentario_lectura</textarea></td>";
                                        if ($detalle == "1") {
                                            echo "<td align='center' scope='row' style='font-size:11px'><input type='radio' name='rdEquipoFalla' id='rdEquipoFalla' value='" . $contador . " / " . $rs['NoSerie'] . " / " . $rs['Modelo'] . "'  $seleccionar  $desactivarRadioPedido/></td>";
                                        } else {
                                            echo "<td align='center' scope='row' style='font-size:11px'><input type='radio' name='rdEquipoFalla' onclick='incidenciaByTicketFallaCliente(\"" . $rs['NoSerie'] . "\")' id='rdEquipoFalla' value='" . $contador . " / " . $rs['NoSerie'] . " / " . $rs['Modelo'] . "'  $seleccionar  $desactivarRadioPedido/></td>";
                                        }
                                        echo "</tr>";
                                        $contador++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php } ?>
                            </div>
                        </div>
                </div>
                
                <div>
                    <fieldset>
                        <legend>Datos del ticket</legend>   
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label >No. ticket cliente:</label>
                            <input class="form-control" type="text" id="txtNoTicketClienteGral" name="txtNoTicketClienteGral" value='<?php echo $ticketCliente ?>' <?php echo $read; ?>/>
                        </div>
                        <div class="form-group col-md-3">
                            <label >No. ticket distribuidor:</label>
                            <input class="form-control" type="text" id="txtNoTicketDistribucionGral" name="txtNoTicketDistribucionGral" value='<?php echo $ticketDistribucion; ?>' <?php echo $read; ?>/>  
                        </div>   
                        <div class="form-group  col-md-3">
                            <label >Descripción del reporte:</label>
                            	<textarea class="form-control" style="width: 99%; height: 130px;" id='descripcion' name='descripcion' <?php echo $read; ?>><?php echo $descripcion; ?></textarea>                          
                         </div> 
                         <div class="form-group  col-md-3">
                            <label >Observaciones adicionales:</label>
                            	<textarea class="form-control" style="width: 99%;height: 130px;" id='observacion' name='observacion' <?php echo $read; ?>><?php echo $observacion; ?></textarea>
                        </div>   
                        <div class="form-group col-md-3">
                           <label >Área de atención<span class="obligatorio"> *</span>:</label>
                                <select class="form-control"  id="areaAtencionGral" name="areaAtencionGral"  <?php echo $drawList; ?>>

                                    <?php
                                    if ($tipoReporte == "15") {
                                        $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=3";
                                    } else if ($tipoReporte != "15" && $tipoReporte != "") {
                                        echo "<option value='0'>Seleccione el area de atención</option>";
                                        $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=2";
                                    } else {
                                        echo "<option value='0'>Seleccione el area de atención</option>";
                                    }
                                    $query = $catalogo->obtenerLista($queryArea);
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($areaAtencion != "" && $areaAtencion == $rs['IdEstado'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        
                    </div>
                </fieldset>
            </div>
            <?php if ($detalle != "1" && $permisos_grid->getModificar()) { ?>              
            <input type = "submit" id = "botonGuardar" name = "botonGuardar" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" value = "Guardar"/>
                   <?php if ($botonCancelar != "1") { ?>  
                <input type = "submit" class="button btn btn-lg btn-block btn-outline-danger mt-3 mb-3" value = "Cancelar" onclick = "cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                    return false;"/>
                       <?php
                   }
               }
               ?>
            <input type = "hidden" name = "idTicket" id = "idTicket" value = "<?php echo $idTicket; ?>" />
            <input type = "hidden" name = "txtPermisoRendimiento" id = "txtPermisoRendimiento" value = "<?php echo $permisoEspecialRendimiento; ?>" />
            <input type = "hidden" name = "nombreCC" id = "idTicket" value = "<?php echo $nombreLocalidad; ?>" />
            <input type = "hidden" name = "nombreCliente" id = "idTicket" value = "<?php echo $nombreCliente; ?>" />
            <input type = "hidden" name = "filaSeleccionada" id = "filaSeleccionada" value = ""/>
            <input type = "hidden" name = "tipoUsuario" id = "tipoUsuario" value = "<?php echo $idPuesto; ?>"/>
            <input type = "hidden" name = "rdContacto" id = "rdContacto" value ="-1" />
            <input type = "hidden" name = "txtNombre" id = "txtNombre" value = "<?php echo $nombreContacto . " / " . $telefono . " / " . $celular . " / " . $correoE . " / " . $idContacto . " / " . "8" . " / " . $claveEspecialContacto; ?>" />
            <input type = "hidden" name = "txtTelefono1" id = "txtTelefono1" value = "<?php echo $telefono; ?>"/>
            <input type = "hidden" name = "txtCelular" id = "txtCelular" value = "<?php echo $celular; ?>"/>
            <input type = "hidden" name = "correoElectronico" id = "correoElectronico" value = "<?php echo $correoE; ?>"/>
            <input type = "hidden" name = "lstHR" id = "rdContacto" value ="9" />
            <input type = "hidden" name = "lstMR" id = "rdContacto" value ="00" />
            <input type = "hidden" name = "lstTA" id = "rdContacto" value ="am" />
            <input type = "hidden" name = "lstFinHR" id = "rdContacto" value ="6" />
            <input type = "hidden" name = "lstFinMR" id = "rdContacto" value ="00" />
            <input type = "hidden" name = "lstFinTR" id = "rdContacto" value ="pm" />
            <?php
                if($permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "mesa/lista_ticket_new.php")){
                    echo '<input type = "hidden" name = "permiso_tickets2" id = "permiso_tickets2" value ="1" />';
                }
            ?>
            </form>
        </div>
<div id = "dialog" ></div>         
        </body>
        <div class="p-4 bg-light rounded">
</html>