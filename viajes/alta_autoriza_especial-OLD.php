<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
date_default_timezone_set('America/Mexico_City');
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/AutorizarEspecial.class.php");
$pagina_lista = "viajes/lista_especial.php";

$auto_especial = new AutorizarEspecial();
$idEspecial = "";
$idRuta = "";
$nombreRuta = "";
$empleado = "";
$contacto = "";
$datoContacto = "";
$tipoSer = "";
$campania = "";
$turno = "";
$costo = "";
$info = "";
$fecha = date('Y') . "-" . date('m') . "-" . date('d');
$h = date('H');
$minutos = round((int) (date('i')) / 5) * 5;
if ($minutos <= 15) {
    $minutos = "15";
} else {
    if ($minutos > 15 && $minutos <= 30) {
        $minutos = "30";
    } else {
        if ($minutos > 30 && $minutos <= 45) {
            $minutos = "45";
        } else {
            if ($minutos > 45 && $minutos <= 59) {
                $minutos = "00";
                $h = $h + 1;
            }
        }
    }
}
//if($minutos<10){$minutos="0".$minutos;}
$hora = $h . ":" . $minutos;
$origen = "";
$destino = "";

$calle_or = "";
$exterior_or = "";
$interior_or = "";
$colonia_or = "";
$ciudad_or = "";
$delegacion_or = "";
$cp_or = "";
$localidad_or = "";
$estado_or = "";
$latitud_or = "";
$longitud_or = "";
$comentarios_or = "";
$cuadrante = "";

$calle_des = "";
$exterior_des = "";
$interior_des = "";
$colonia_des = "";
$ciudad_des = "";
$delegacion_des = "";
$cp_des = "";
$localidad_des = "";
$estado_des = "";
$latitud_des = "";
$longitud_des = "";
$comentarios_des = "";

$EstadoTicket = 0;
$notas = 0;
$bloq = "";

$origenR = "";
$destinoR = "";
$operador = "";
$edit = FALSE;
$resultEscalas = null;

$catalogo = new Catalogo();
$queryEstado = $catalogo->getListaAlta("c_ciudades", "Ciudad");
$ciudades = array();
while ($rsEstado = mysql_fetch_array($queryEstado)) {
    $ciudades[$rsEstado['IdCiudad']] = $rsEstado['Ciudad'];
}

if (isset($_POST['FiltroEmpleado'])) {
    $empleado = $_POST['FiltroEmpleado'];
}

$rutas = array();
$consulta = "SELECT * FROM c_rutaespecial WHERE Activo = 1;";
$resultRuta = $catalogo->obtenerLista($consulta);
while ($rsRuta = mysql_fetch_array($resultRuta)) {
    $rutas[$rsRuta['idEspecial']] = $rsRuta['Nombre_ruta'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/viajes/alta_autoriza_especial.js"></script> 
    </head>
    <body>
        <div class="principal">
            <?php
            if (
                    (isset($_GET['id']) && !empty($_GET['id']) ) ||
                    (isset($_POST['id']) && !empty($_POST['id']) ) ||
                    (isset($_POST['idTicket']) && !empty($_POST['idTicket']))
            ) {
                if (isset($_POST['idTicket']) && !empty($_POST['idTicket'])) {
                    if (!$auto_especial->getRegistroByIdTicket($_POST['idTicket'])) {
                        echo "<h3>El ticket " . $_POST['idTicket'] . " no tienen ningún viaje asociado</h3>";
                    }
                    $edit = TRUE;
                } else if (isset($_GET['ruta']) && $_GET['ruta'] == "1") {
                    $auto_especial->getRegistroRutaById($_POST['id']);
                    $idRuta = $_POST['id'];
                    $nombreRuta = $auto_especial->getNombre_ruta();
                } else {
                    if (isset($_GET['id']) && $_GET['id'] != "") {
                        $auto_especial->getRegistroById($_GET['id']);
                    } else {
                        $auto_especial->getRegistroById($_POST['id']);
                        $edit = TRUE;
                    }
                }

                $idEspecial = $auto_especial->getIdEspecial();
                if (empty($idRuta)) {
                    $contacto = $auto_especial->getContacto();
                    $datoContacto = $auto_especial->getDatoContacto();
                    $turno = $auto_especial->getIdTurno();
                    $fecha = $auto_especial->getFecha();
                    $hora = $auto_especial->getHora();
                    $campania = $auto_especial->getIdCampania();
                }

                $empleado = $auto_especial->getIdEmpleado();
                $tipoSer = $auto_especial->getTipoServicio();
                $origen = $auto_especial->getOrigen();
                $destino = $auto_especial->getDestino();
                $calle_or = $auto_especial->getCalle_or();
                $exterior_or = $auto_especial->getExterior_or();
                $interior_or = $auto_especial->getInterior_or();
                $colonia_or = $auto_especial->getColonia_or();
                $ciudad_or = $auto_especial->getCiudad_or();
                $delegacion_or = $auto_especial->getDelegacion_or();
                $cp_or = $auto_especial->getCp_or();
                $localidad_or = $auto_especial->getLocalidad_or();
                $estado_or = $auto_especial->getEstado_or();
                $latitud_or = $auto_especial->getLatitud_or();
                $longitud_or = $auto_especial->getLongitud_or();
                $comentarios_or = $auto_especial->getComentario_or();
                $cuadrante = $auto_especial->getCuadrante();
                $info = $auto_especial->getInformacion();

                $calle_des = $auto_especial->getCalle_des();
                $exterior_des = $auto_especial->getExterior_des();
                $interior_des = $auto_especial->getInterior_des();
                $colonia_des = $auto_especial->getColonia_des();
                $ciudad_des = $auto_especial->getCiudad_des();
                $delegacion_des = $auto_especial->getDelegacion_des();
                $cp_des = $auto_especial->getCp_des();
                $localidad_des = $auto_especial->getLocalidad_des();
                $estado_des = $auto_especial->getEstado_des();
                $latitud_des = $auto_especial->getLatitud_des();
                $longitud_des = $auto_especial->getLongitud_des();
                $comentarios_des = $auto_especial->getComentario_des();
                $ticket_exis = $auto_especial->getIdTicket();
                $costo = $auto_especial->getPrecioParticular();

                if ($ticket_exis != "") {
                    echo "Servicio: <b>" . $ticket_exis . "</b>";

                    $queryEstadoT = $catalogo->obtenerLista("SELECT ct.EstadoDeTicket, kt.IdUsuario FROM c_ticket AS ct LEFT JOIN k_tecnicoticket AS kt ON kt.IdTicket=ct.IdTicket WHERE ct.IdTicket=" . $ticket_exis . ";");
                    $rsET = mysql_fetch_array($queryEstadoT);
                    $EstadoTicket = $rsET['EstadoDeTicket'];
                    if ($rsET['IdUsuario'] != "") {
                        $operador = $rsET['IdUsuario'];
                    }

                    $catalogo2 = new Catalogo();
                    $result = $catalogo2->obtenerLista("SELECT IdNotaTicket FROM c_notaticket WHERE IdTicket = " . $ticket_exis . " AND IdEstatusAtencion = 51;");
                    if (mysql_num_rows($result) > 0) {
                        $notas = 1;
                    }
                }
                if (!$edit && $EstadoTicket == 3) {
                    $bloq = 'disabled';
                }

                if (empty($idRuta)) {
                    $resultEscalas = $auto_especial->obtenerEscalasDeViaje();
                } else {
                    $resultEscalas = $auto_especial->obtenerEscalasDeRuta();
                }
            }
            ?>
            <form id="formEspecial" name="formEspecial" action="/" method="POST">
                <br/>
                <table>
                    <tr><td>
                            <table id="tabla_origen_destino">
                                <tr>
                                    <td>
                                        <table>
                                            <tr>
                                                <td style="width: 10%">Empleado<span class='obligatorio'> *</span></td>
                                                <td style="width: 15%">
                                                    <select id="slcEmpleado" name="slcEmpleado" class="filtro" onchange="verReferencia('viajes/alta_autoriza_especial.php');" <?php echo $bloq; ?>>
                                                        <option value="0">Seleccione un Empleado</option>
                                                        <?php
                                                        $queryEmpleado = $catalogo->getListaAlta("c_usuario", "Nombre");
                                                        while ($rs = mysql_fetch_array($queryEmpleado)) {
                                                            $s = "";
                                                            if ($empleado != "" && $empleado == $rs['IdUsuario']) {
                                                                $s = "selected";
                                                            }
                                                            if ($rs['IdPuesto'] == 100) {
                                                                echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['IdUsuario'] . " " . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label for="fecha">Fecha</label><span class="obligatorio"> *</span></td>
                                                <td><input type="text" id="fecha" name="fecha" value="<?php echo $fecha; ?>" required="required"/></td>
                                            </tr>
                                            <tr>
                                                <td><label for="orden"></label>Hora<span class="obligatorio"> *</span></td>
                                                <td><input type="text" id="hora" name="hora" value="<?php echo $hora; ?>" required="required"/></td>
                                            </tr>
                                            <tr>
                                                <td><label for="tiposer">Tipo de Servicio</label></td>
                                                <td><select id="tiposer" name="tiposer" style="width: 180px;" <?php echo $bloq; ?>>
                                                        <?php
                                                        if ($tipoSer == "" || $tipoSer == 0) {
                                                            $s = "selected";
                                                            echo "<option value='0' selected>Reservado</option>";
                                                            echo "<option value='1'>Al momento</option>";
                                                        } else {
                                                            echo "<option value='0'>Reservado</option>";
                                                            echo "<option value='1' selected>Al momento</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%">Origen Recurrente<span class='obligatorio'> *</span></td>
                                                <td style="width: 15%">
                                                    <select id="origenR" name="origenR" onchange="verDomicilio(1);" <?php echo $bloq; ?>>
                                                        <option value="0">Seleccionar Origen</option>
                                                        <?php
                                                        $queryOrigen = $catalogo->obtenerLista("SELECT CONCAT('1-',cu.IdUbicacion) AS idEspecial, cu.Descripcion AS Origen FROM c_ubicaciones cu WHERE cu.Activo=1 UNION 
                                                                                                SELECT CONCAT('2-',MAX(idEspecial)) AS IdEspecial,  Origen FROM c_especial ce  WHERE ce.idUsuario=" . $empleado . " GROUP BY Origen ;");
                                                        while ($rs = mysql_fetch_array($queryOrigen)) {
                                                            $s = "";
                                                            if ($origenR != "" && $origenR == $rs['Origen']) {
                                                                $s = "selected";
                                                            }
                                                            if (trim($origen) == trim($rs['Origen'])) {
                                                                $s = "selected";
                                                            }
                                                            echo "<option value='" . $rs['idEspecial'] . "' $s>" . $rs['Origen'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Origen<span class='obligatorio'> *</span></td><td><input  style="width: 60%" type="text" id="txtOrigen" name="txtOrigen" value="<?php echo $origen ?>" <?php echo $bloq; ?>></td>
                                            </tr>
                                            <tr>
                                                <td style="width: 10%">Destino Recurrente<span class='obligatorio'> *</span></td>
                                                <td style="width: 15%">
                                                    <select id="destinoR" name="destinoR" onchange="verDomicilio(2);" <?php echo $bloq; ?>>
                                                        <option value="0">Seleccionar Destino</option>
                                                        <?php
                                                        $queryDestino = $catalogo->obtenerLista("SELECT CONCAT('1-',cu.IdUbicacion) AS idEspecial, cu.Descripcion AS Destino FROM c_ubicaciones cu WHERE cu.Activo=1 UNION 
                                                                                                 SELECT CONCAT('2-',MAX(idEspecial)) AS IdEspecial,  Destino FROM c_especial ce  WHERE ce.idUsuario=" . $empleado . " GROUP BY Destino ;");

                                                        while ($rs = mysql_fetch_array($queryDestino)) {
                                                            $s = "";
                                                            if ($destinoR != "" && $destinoR == $rs['idEspecial']) {
                                                                $s = "selected";
                                                            }

                                                            if (trim($destino) == trim($rs['Destino'])) {
                                                                $s = "selected";
                                                            }
                                                            echo "<option value='" . $rs['idEspecial'] . "' $s>" . $rs['Destino'] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Destino<span class='obligatorio'> *</span></td><td><input style="width: 100%" type="text" id="txtDestino" name="txtDestino" value="<?php echo $destino ?>" <?php echo $bloq; ?>></td>
                                            </tr>
                                            <tr>
                                                <td>Información</td>
                                                <td>
                                                    <input style="width: 100%" type="text" id="txtInformacion" name="txtInformacion" value="<?php echo $info; ?>" <?php echo $bloq; ?>>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td>
                                        <fieldset>
                                            <legend>Domicilio Origen</legend> 
                                            <table style="width:100%"> 
                                                <tr>
                                                    <td>Calle <span class='obligatorio'> *</span></td><td><input type="text" id="txtCalle_or" name="txtCalle_or" value="<?php echo $calle_or ?>" <?php echo $bloq; ?>></td>
                                                    <td>No. Exterior <span class='obligatorio'> *</span></td><td><input type="text" id="txtExterior_or" name="txtExterior_or" value="<?php echo $exterior_or ?>" <?php echo $bloq; ?>></td>
                                                </tr>
                                                <tr>
                                                    <!--<td>No. Interior</td><td><input type="text" id="txtInterior_or" name="txtInterior_or" value="<?php //echo $interior_or                             ?>" <?php //echo $bloq;                             ?>></td>-->
                                                <input type='hidden' id='txtInterior_or' name='txtInterior_or' value="<?php echo $interior_or ?>"/>
                                                <td>Colonia <span class='obligatorio'> *</span></td><td><input type="text" id="txtColonia_or" name="txtColonia_or" value="<?php echo $colonia_or ?>" <?php echo $bloq; ?>></td>
                                                </tr>
                                                <tr>
                                                    <!--<td>Ciudad</td><td><input type="text" id="txtCiudad_or" name="txtCiudad_or" value="<?php //echo $ciudad_or                            ?>" <?php //echo $bloq;                            ?>></td>-->
                                                <input type="hidden" id="txtCiudad_or" name="txtCiudad_or" value="<?php echo $ciudad_or ?>" />
                                                <td>Delegación <span class='obligatorio'> *</span></td><td><input type="text" id="txtDelegacion_or" name="txtDelegacion_or" value="<?php echo $delegacion_or ?>" <?php echo $bloq; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td>C.P. <span class='obligatorio'> *</span></td>
                                                    <td><input type="text" id="txtcp_or" name="txtcp_or" value="<?php echo $cp_or ?>" <?php echo $bloq; ?> maxlength="6"></td>
                                                    <!--<td>Localidad</td><td><input type="text" id="txtLocalidad_or" name="txtLocalidad_or" value="<?php //echo $localidad_or                            ?>" <?php //echo $bloq;                            ?>></td>-->
                                                <input type="hidden" id="txtLocalidad_or" name="txtLocalidad_or" value="<?php echo $localidad_or ?>" <?php //echo $bloq;                            ?> />
                                                </tr>
                                                <tr>
                                                    <td>Estado <span class='obligatorio'> *</span></td>
                                                    <td>
                                                        <select id="slcEstado_or" name="slcEstado_or" <?php echo $bloq; ?>>
                                                            <option value="0">Seleccione un estado</option>
                                                            <?php
                                                            foreach ($ciudades as $key => $value) {
                                                                $s = "";
                                                                if ($estado_or != "" && $estado_or == $key)
                                                                    $s = "selected";
                                                                echo "<option value='$key' $s>$value</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>Comentario</td><td><input type="text" id="Comentario_or" name="Comentario_or" value="<?php echo $comentarios_or ?>" <?php echo $bloq; ?>></td>
                                                </tr>
                                                <tr>
                                                    <td><label for="area">Cuadrante </label><span class='obligatorio'> *</span></td>
                                                    <td>
                                                        <select id="area" name="area" class="filtro" <?php echo $bloq; ?>>
                                                            <?php
                                                            /* Inicializamos la clase */
                                                            if ($cuadrante == "") {
                                                                $cuadrante = 280;
                                                            }
                                                            $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                                                                  INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) WHERE Activo = 1 ORDER BY Nombre;");
                                                            echo "<option value='0' >Selecciona una opción</option>";
                                                            while ($rs = mysql_fetch_array($query)) {
                                                                $s = "";
                                                                if (!empty($cuadrante) && $rs['IdEstado'] == $cuadrante) {
                                                                    $s = "selected='selected'";
                                                                }
                                                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                            }
                                                            ?> 
                                                        </select>
                                                        <div id="error_area" style="font-size: 12px; color: red;"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Latitud</td><td><input type="number" id="Latitud_or" name="Latitud_or" value="<?php echo $latitud_or ?>" step="any" <?php echo $bloq; ?>></td>
                                                    <td>Longitud</td><td><input type="number" id="Longitud_or" name="Longitud_or" value="<?php echo $longitud_or ?>" step="any" <?php echo $bloq; ?>></td>
                                                </tr>
                                            </table>
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td rowspan="2"> 
                                                        <input align="center" type="button" value="Buscar Ubicación" class="boton" title="Buscar Domicilio según coordenadas" onclick="getLatLngText();" />                             
                                                    </td>
                                                    <td> 
                                                        <input align='center' type='button' value='Buscar Coordenada' class='boton' title='Buscar coordenadas de acuerdo con la dirección' onclick='geocodificarDireccionOrigen();' />   
                                                        <br/><span style='font-size:8px;font-style: italic;color:grey;'>Servicio bajo las condiciones de Google Maps Geocoding API</span>
                                                    </td>
                                                    <td>
                                                        <div id="fotocargandoPI" style="width:100%; display: none; ">
                                                            <img src="resources/img/loading.gif"/>                             
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </td>
                                </tr>
                                <?php
                                $contadorEscalas = 0;
                                if ($resultEscalas == NULL || mysql_num_rows($resultEscalas) == 0) {
                                    echo "<tr id='fila_detalle_$contadorEscalas'>
                                        <td>
                                            <fieldset>
                                                <legend>Domicilio Destino</legend>                                             
                                                <table style='width:100%'> 
                                                    <tr>
                                                        <td>Calle <span class='obligatorio'> *</span></td>
                                                        <td>
                                                            <input type='text' id='txtCalle_des$contadorEscalas' name='txtCalle_des$contadorEscalas' value='' $bloq>";
                                    if (empty($idRuta)) {
                                        echo "<input type='hidden' id='idDetalle$contadorEscalas' name='idDetalle$contadorEscalas' value=''/>";
                                    } else {
                                        echo "<input type='hidden' id='idDetalleRuta$contadorEscalas' name='idDetalleRuta$contadorEscalas' value=''/>";
                                    }
                                    echo "</td>
                                                        <td>No. Exterior <span class='obligatorio'> *</span></td><td><input type='text' id='txtExterior_des$contadorEscalas' name='txtExterior_des$contadorEscalas' value='' $bloq></td>
                                                    </tr>
                                                    <tr>                                                        
                                                    <input type='hidden' id='txtInterior_des$contadorEscalas' name='txtInterior_des$contadorEscalas' value='' />
                                                    <td>Colonia <span class='obligatorio'> *</span></td><td><input type='text' id='txtColonia_des$contadorEscalas' name='txtColonia_des$contadorEscalas' value='' $bloq></td>
                                                    </tr>
                                                    <tr>                                                        
                                                    <input type='hidden' id='txtCiudad_des$contadorEscalas' name='txtCiudad_des$contadorEscalas' value=''/>
                                                    <td>Delegación <span class='obligatorio'> *</span></td><td><input type='text' id='txtDelegacion_des$contadorEscalas' name='txtDelegacion_des$contadorEscalas' value='' $bloq></td>
                                                    </tr>
                                                    <tr>
                                                        <td>C.P. <span class='obligatorio'> *</span></td>
                                                        <td><input type='text' id='txtcp_des$contadorEscalas' name='txtcp_des$contadorEscalas' value='' $bloq maxlength='6'></td>                                                        
                                                        <input type='hidden' id='txtLocalidad_des$contadorEscalas' name='txtLocalidad_des$contadorEscalas' value='' />
                                                    </tr>
                                                    <tr>
                                                        <td>Estado <span class='obligatorio'> *</span></td>
                                                        <td>
                                                            <select id='slcEstado_des$contadorEscalas' name='slcEstado_des$contadorEscalas' $bloq>
                                                                <option value='0'>Seleccione un estado</option>";
                                    foreach ($ciudades as $key => $value) {
                                        $s = '';
                                        if ($estado_des != '' && $estado_des == $key) {
                                            $s = 'selected';
                                        }
                                        echo '<option value=' . $key . '>' . $value . '</option>';
                                    }

                                    echo "</select>
                                                        </td>
                                                        <td>Comentario</td><td><input type='text' id='Comentario_des$contadorEscalas' name='Comentario_des$contadorEscalas' value='' $bloq></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Latitud</td><td><input type='number' id='Latitud_des$contadorEscalas' name='Latitud_des$contadorEscalas' value='' step='any' $bloq></td>
                                                        <td>Longitud</td><td><input type='number' id='Longitud_des$contadorEscalas' name='Longitud_des$contadorEscalas' value='' step='any' $bloq></td>
                                                    </tr>                                                    
                                                </table>
                                                <table style='width: 100%;'>
                                                    <tr>
                                                        <td> 
                                                            <input align='center' type='button' value='Buscar Ubicación' class='boton' title='Buscar Domicilio de acuerdo con las coordenadas' onclick='getLatLngText2($contadorEscalas);' />                             
                                                        </td>
                                                        <td> 
                                                            <input align='center' type='button' value='Buscar Coordenada' class='boton' title='Buscar coordenadas de acuerdo con la dirección' onclick='geocodificarDireccion($contadorEscalas);' />                             
                                                            <br/><span style='font-size:8px;font-style: italic;color:grey;'>Servicio bajo las condiciones de Google Maps Geocoding API</span>
                                                        </td>
                                                        <td>
                                                            <div id='fotocargandoPI2$contadorEscalas' style='width:100%; display: none; '>
                                                                <img src='resources/img/loading.gif'/>                             
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href='#' onclick='agregarDestinoViaje(); return false;' title='Agregar una escala'>
                                                                <img src='resources/images/add.png'/>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </fieldset>    
                                        </td>
                                    </tr>";
                                    $contadorEscalas++;
                                } else {
                                    while ($rs = mysql_fetch_array($resultEscalas)) {
                                        echo "<tr id='fila_detalle_$contadorEscalas'>
                                            <td>
                                                <fieldset>
                                                    <legend>Domicilio Destino</legend>                                             
                                                    <table style='width:100%'> 
                                                        <tr>
                                                            <td>Calle <span class='obligatorio'> *</span></td>
                                                            <td>
                                                                <input type='text' id='txtCalle_des$contadorEscalas' name='txtCalle_des$contadorEscalas' value='" . $rs['Calle_des'] . "' $bloq>";
                                        if (empty($idRuta)) {
                                            echo "<input type='hidden' id='idDetalle$contadorEscalas' name='idDetalle$contadorEscalas' value='" . $rs['idkEspecial'] . "' />";
                                        } else {
                                            echo "<input type='hidden' id='idDetalleRuta$contadorEscalas' name='idDetalleRuta$contadorEscalas' value='" . $rs['idkEspecial'] . "' />";
                                        }
                                        echo "</td>
                                                            <td>No. Exterior <span class='obligatorio'> *</span></td><td><input type='text' id='txtExterior_des$contadorEscalas' name='txtExterior_des$contadorEscalas' value='" . $rs['NoExterior_des'] . "' $bloq></td>
                                                        </tr>
                                                        <tr>                                                        
                                                        <input type='hidden' id='txtInterior_des$contadorEscalas' name='txtInterior_des$contadorEscalas' value='" . $rs['NoInterior_des'] . "' />
                                                        <td>Colonia <span class='obligatorio'> *</span></td><td><input type='text' id='txtColonia_des$contadorEscalas' name='txtColonia_des$contadorEscalas' value='" . $rs['Colonia_des'] . "' $bloq></td>
                                                        </tr>
                                                        <tr>                                                        
                                                        <input type='hidden' id='txtCiudad_des$contadorEscalas' name='txtCiudad_des$contadorEscalas' value='" . $rs['Ciudad_des'] . "'/>
                                                        <td>Delegación <span class='obligatorio'> *</span></td><td><input type='text' id='txtDelegacion_des$contadorEscalas' name='txtDelegacion_des$contadorEscalas' value='" . $rs['Delegacion_des'] . "' $bloq></td>
                                                        </tr>
                                                        <tr>
                                                            <td>C.P. <span class='obligatorio'> *</span></td>
                                                            <td><input type='text' id='txtcp_des$contadorEscalas' name='txtcp_des$contadorEscalas' value='" . $rs['CodigoPostal_des'] . "' $bloq maxlength='6'></td>                                                        
                                                            <input type='hidden' id='txtLocalidad_des$contadorEscalas' name='txtLocalidad_des$contadorEscalas' value='" . $rs['Localidad_des'] . "' />
                                                        </tr>
                                                        <tr>
                                                            <td>Estado <span class='obligatorio'> *</span></td>
                                                            <td>
                                                                <select id='slcEstado_des$contadorEscalas' name='slcEstado_des$contadorEscalas' $bloq>
                                                                    <option value='0'>Seleccione un estado</option>";
                                        foreach ($ciudades as $key => $value) {
                                            $s = '';
                                            if ($rs['Estado_des'] != '' && $rs['Estado_des'] == $key) {
                                                $s = 'selected';
                                            }
                                            echo '<option value=' . $key . ' ' . $s . '>' . $value . '</option>';
                                        }

                                        echo "</select>
                                                            </td>
                                                            <td>Comentario</td><td><input type='text' id='Comentario_des$contadorEscalas' name='Comentario_des$contadorEscalas' value='" . $rs['Comentario_des'] . "' $bloq></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Latitud</td><td><input type='number' id='Latitud_des$contadorEscalas' name='Latitud_des$contadorEscalas' value='" . $rs['Latitud_des'] . "' step='any' $bloq></td>
                                                            <td>Longitud</td><td><input type='number' id='Longitud_des$contadorEscalas' name='Longitud_des$contadorEscalas' value='" . $rs['Longitud_des'] . "' step='any' $bloq></td>
                                                        </tr>
                                                        <tr>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    </table>
                                                    <table style='width: 100%;'>
                                                        <tr>
                                                            <td> 
                                                                <input align='center' type='button' value='Buscar Ubicación' class='boton' title='Buscar Domicilio de acuerdo con las coordenadas' onclick='getLatLngText2($contadorEscalas);' />                             
                                                            </td>
                                                            <td> 
                                                                <input align='center' type='button' value='Buscar Coordenada' class='boton' title='Buscar coordenadas de acuerdo con la dirección' onclick='geocodificarDireccion($contadorEscalas);' />                             
                                                                <br/><span style='font-size:8px;font-style: italic;color:grey;'>Servicio bajo las condiciones de Google Maps Geocoding API</span>
                                                            </td>
                                                            <td>
                                                                <div id='fotocargandoPI2$contadorEscalas' style='width:100%; display: none; '>
                                                                    <img src='resources/img/loading.gif'/>                             
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <a href='#' onclick='agregarDestinoViaje(); return false;' title='Agregar una escala'>
                                                                    <img src='resources/images/add.png'/>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </fieldset>    
                                            </td>
                                        </tr>";
                                        $contadorEscalas++;
                                    }
                                }
                                ?>                                                    
                            </table>
                            <input type="hidden" id="TotalEscalas" name="TotalEscalas" value="<?php echo $contadorEscalas; ?>"/>
                        </td>
                        <td style="vertical-align: text-top; width: 60%;">
                            <table>
                                <tr>
                                    <td>
                                        <input type="button" value ="Nuevo Empleado" onclick="cambiarContenidos('catalogos/alta_empleados_loyalty.php?ve=viajes/alta_autoriza_especial.php', 'Agregar Nuevo Empleado')" style="float: center; cursor: pointer;" class="boton"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="contacto">Forma de Contactar</label></td>
                                    <?php
                                    if ($empleado != "") {
                                        if ($campania == "" && $turno == "") {
                                            $queryD = $catalogo->obtenerLista("SELECT idCampania, idTurno FROM k_uscamtur WHERE idUsCamTur=(SELECT MIN(idUsCamTur) FROM k_uscamtur WHERE idUsuario= " . $empleado . ") ;");
                                            if (mysql_num_rows($queryD) > 0) {
                                                $rs = mysql_fetch_array($queryD);
                                                $campania = $rs['idCampania'];
                                                $turno = $rs['idTurno'];
                                            } else {
                                                echo "<font color=\"silver\">Al usuario no se le han asignado camapaña(s) o  turno(s)</font>";
                                            }
                                        }
                                        if ($contacto == "") {
                                            $queryD = $catalogo->obtenerLista("SELECT cd.IdFormaContacto, cu.Telefono, cu.correo FROM c_domicilio_usturno  AS cd LEFT JOIN c_usuario AS cu ON cu.IdUsuario=cd.IdUsuario WHERE cd.IdUsuario= " . $empleado . " ;");
                                            $rs = mysql_fetch_array($queryD);
                                            $contacto = $rs['IdFormaContacto'];
                                            if (($contacto == 1 || $contacto == 2) && $datoContacto == "") {
                                                $datoContacto = $rs['Telefono'];
                                            }
                                            if ($contacto == 3 && $datoContacto == "") {
                                                $datoContacto = $rs['correo'];
                                            }
                                        }
                                    }
                                    ?>
                                    <td><select id="contacto" name="contacto" style="width: 180px;" <?php echo $bloq; ?>>
                                            <option value="0">Seleccione su Forma de Contactar</option>
                                            <?php
                                            $catalogo1 = new Catalogo();
                                            $query1 = $catalogo1->getListaAlta("c_formacontacto", "Nombre");
                                            while ($rs = mysql_fetch_array($query1)) {
                                                $s = "";
                                                if ($contacto != "" && $contacto == $rs['IdFormaContacto']) {
                                                    $s = "selected";
                                                }
                                                echo "<option value=" . $rs['IdFormaContacto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Número/Correo</td><td><input style="width: 100%" type="text" id="txtDatoContacto" name="txtDatoContacto" value="<?php echo $datoContacto ?>" <?php echo $bloq; ?>></td>
                                </tr>
                                <tr>
                                    <td style="width: 10%">Campaña<span class='obligatorio'> *</span></td>
                                    <td style="width: 15%">
                                        <select id="slcCampania" name="slcCampania" <?php echo $bloq; ?>>
                                            <option value="0">Seleccione una campaña</option>
                                            <?php
                                            $queryCampania = $catalogo->obtenerLista("SELECT distinct idCampania, Descripcion FROM k_uscamtur LEFT JOIN c_area ON IdArea=idCampania WHERE idUsuario= " . $empleado . " ORDER BY idUsCamTur;");
                                            while ($rs = mysql_fetch_array($queryCampania)) {
                                                $s = "";
                                                if ($campania != "" && $campania == $rs['idCampania'])
                                                    $s = "selected";
                                                echo "<option value='" . $rs['idCampania'] . "' $s>" . $rs['Descripcion'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 11.9%">Turno<span class='obligatorio'> *</span></td>
                                    <td style="width: 15%">
                                        <select id="slcTurno" name="slcTurno" <?php echo $bloq; ?>>
                                            <option value="0">Seleccione un Turno</option>
                                            <?php
                                            $queryTurno = $catalogo->obtenerLista("SELECT distinct ku.idTurno, ct.descripcion FROM k_uscamtur AS ku LEFT JOIN c_turno AS ct ON ct.idTurno=ku.idTurno WHERE ku.idUsuario= " . $empleado . " ORDER BY ku.idUsCamTur;");
                                            while ($rs = mysql_fetch_array($queryTurno)) {
                                                $s = "";
                                                if ($turno != "" && $turno == $rs['idTurno'])
                                                    $s = "selected";
                                                echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 11.9%">Ruta</td>
                                    <td style="width: 15%">
                                        <select id="ruta_precargada" name="ruta_precargada" onchange="precargarRuta(); return false;">
                                            <option value="">Selecciona una ruta a pre-cargar</option>
                                            <?php
                                            foreach ($rutas as $key => $value) {
                                                $s = "";
                                                if ($idRuta == $key) {
                                                    $s = "selected='selected'";
                                                }
                                                echo "<option value='$key' $s>$value</option>";
                                            }
                                            ?>
                                        </select>
                                        <div style='font-size:8px;font-style: italic;color:grey;'>En caso de querer utilizar una ruta pre-determinada</div>
                                    </td>
                                </tr>
                            </table>
                            <div id="map_canvas" style="height: 600px; left: 20px; top: 30px; position: relative" ></div>                            
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td>Operador:</td>
                        <td>
                            <select id="operador" name="operador" class="select" <?php echo $bloq; ?>>
                                <option value="" >Selecciona un operador</option>
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(cu.Loggin,'-',cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Usuario FROM c_usuario AS cu
                                                                                                    WHERE cu.Activo = 1 AND (IdPuesto= 101)
                                                                                                    GROUP BY cu.IdUsuario
                                                                                                    ORDER BY Nombre;");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (!empty($operador) && $rs['IdUsuario'] == $operador) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Usuario'] . "</option>";
                                }
                                ?> 
                            </select>
                            <div id="sin_operador" style="width:100%; color: red;">Selecciona un operador</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Costo del viaje en particular<br/>                            
                        </td>
                        <td>
                            <input type="number" id="costo_servicio" name="costo_servicio" value="<?php echo $costo; ?>" step="any"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><span style='font-size:8px;font-style: italic;color:grey;'>En caso de no llenarse este campo, el costo se calcula en automático con la tarifa del cliente</span></td>
                    </tr>
                </table>
                <div style="display: inline; width: 50%;">
                    <?php
                    if ($notas == 1) {
                        echo "";
                    } else {
                        if (isset($_GET['id']) && $_GET['id'] != "") {
                            if ($EstadoTicket == 0) {
                                echo "<input type=\"submit\" name=\"submit\" class=\"boton\" value=\"Autorizar\" position=\"right\"/>";
                                echo "<input type='hidden' id='autorizar' name='autorizar' value='1'/> ";
                                echo "<input type=\"button\" value =\"Autorizar y Asignar\" onclick=\"guardarActualizar('AA4')\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>";
                            } else {
                                if ($EstadoTicket == 3) {
                                    echo "<input type=\"submit\" name=\"submit\" class=\"boton\" value=\"Desautorizar\" position=\"right\"/>";
                                    echo "<input type='hidden' id='autorizar' name='autorizar' value='2'/> ";
                                    echo "<input type='hidden' id='ticket' name='ticket' value='$ticket_exis'/> ";

                                    echo "<input type='hidden' id='slcEmpleado' name='slcEmpleado' value='" . $empleado . "'/> ";
                                    echo "<input type='hidden' id='contacto' name='contacto' value='" . $contacto . "'/> ";
                                    echo "<input type='hidden' id='txtDatoContacto' name='txtDatoContacto' value='" . $datoContacto . "'/> ";
                                    echo "<input type='hidden' id='tiposer' name='tiposer' value='" . $tipoSer . "'/> ";
                                    echo "<input type='hidden' id='slcCampania' name='slcCampania' value='" . $campania . "'/> ";
                                    echo "<input type='hidden' id='slcTurno' name='slcTurno' value='" . $turno . "'/> ";
                                    echo "<input type='hidden' id='hora' name='hora' value='" . $hora . "'/> ";
                                    echo "<input type='hidden' id='fecha' name='fecha' value='" . $fecha . "'/> ";
                                    echo "<input type='hidden' id='txtOrigen' name='txtOrigen' value='" . $origen . "'/> ";
                                    echo "<input type='hidden' id='txtDestino' name='txtDestino' value='" . $destino . "'/> ";
                                    echo "<input type='hidden' id='txtCalle_or' name='txtCalle_or' value='" . $calle_or . "'/> ";
                                    echo "<input type='hidden' id='txtExterior_or' name='txtExterior_or' value='" . $exterior_or . "'/> ";
                                    echo "<input type='hidden' id='txtInterior_or' name='txtInterior_or' value='" . $interior_or . "'/> ";
                                    echo "<input type='hidden' id='txtColonia_or' name='txtColonia_or' value='" . $colonia_or . "'/> ";
                                    echo "<input type='hidden' id='txtCiudad_or' name='txtCiudad_or' value='" . $ciudad_or . "'/> ";
                                    echo "<input type='hidden' id='txtDelegacion_or' name='txtDelegacion_or' value='" . $delegacion_or . "'/> ";
                                    echo "<input type='hidden' id='txtcp_or' name='txtcp_or' value='" . $cp_or . "'/> ";
                                    echo "<input type='hidden' id='txtLocalidad_or' name='txtLocalidad_or' value='" . $localidad_or . "'/> ";
                                    echo "<input type='hidden' id='slcEstado_or' name='slcEstado_or' value='" . $estado_or . "'/> ";
                                    echo "<input type='hidden' id='Latitud_or' name='Latitud_or' value=" . $latitud_or . "/> ";
                                    echo "<input type='hidden' id='Longitud_or' name='Longitud_or' value=" . $longitud_or . "/> ";
                                    echo "<input type='hidden' id='Comentario_or' name='Comentario_or' value='" . $comentarios_or . "'/> ";
                                    echo "<input type='hidden' id='area' name='area' value='" . $cuadrante . "'/> ";
                                    echo "<input type='hidden' id='txtCalle_des0' name='txtCalle_des0' value='" . $calle_des . "'/> ";
                                    echo "<input type='hidden' id='txtExterior_des0' name='txtExterior_des0' value='" . $exterior_des . "'/> ";
                                    echo "<input type='hidden' id='txtInterior_des0' name='txtInterior_des0' value='" . $interior_des . "'/> ";
                                    echo "<input type='hidden' id='txtColonia_des0' name='txtColonia_des0' value='" . $colonia_des . "'/> ";
                                    echo "<input type='hidden' id='txtCiudad_des0' name='txtCiudad_des0' value='" . $ciudad_des . "'/> ";
                                    echo "<input type='hidden' id='txtDelegacion_des0' name='txtDelegacion_des0' value='" . $delegacion_des . "'/> ";
                                    echo "<input type='hidden' id='txtcp_des0' name='txtcp_des0' value='" . $cp_des . "'/> ";
                                    echo "<input type='hidden' id='txtLocalidad_des0' name='txtLocalidad_des0' value='" . $localidad_des . "'/> ";
                                    echo "<input type='hidden' id='slcEstado_des0' name='slcEstado_des0' value='" . $estado_des . "'/> ";
                                    echo "<input type='hidden' id='Latitud_des0' name='Latitud_des0' value=" . $latitud_des . "/> ";
                                    echo "<input type='hidden' id='Longitud_des0' name='Longitud_des0' value=" . $longitud_des . "/> ";
                                    echo "<input type='hidden' id='Comentario_des0' name='Comentario_des0' value='" . $comentarios_des . "'/> ";
                                } else {
                                    if ($EstadoTicket == 4) {
                                        echo "<input type=\"submit\" name=\"submit\" class=\"boton\" value=\"Autorizar\" position=\"right\"/>";
                                        echo "<input type=\"button\" value =\"Autorizar y Asignar\" onclick=\"guardarActualizar('AA4')\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>";
                                        echo "<input type='hidden' id='autorizar' name='autorizar' value='3'/> ";
                                        echo "<input type='hidden' id='ticket' name='ticket' value='$ticket_exis' /> ";
                                    } else {
                                        echo "";
                                        //echo "<input type=\"submit\" name=\"submit\" class=\"boton\" value=\"Detalle\" position=\"right\"/>"; 
                                    }
                                }
                            }
                        } else {
                            echo "<input type=\"submit\" name=\"submit\" class=\"boton\" value=\"Guardar\" position=\"right\"/>";
                            if (!$edit) {
                                ?>
                                <input type="button" value ="Guardar y Autorizar" onclick="guardarActualizar('GA')" style="float: center; cursor: pointer;" class="boton"/>
                                <input type="button" value ="Guardar, Autorizar y Asignar" onclick="guardarActualizar('GAA')" style="float: center; cursor: pointer;" class="boton"/>
                                <?php
                            } else {
                                echo "<input type='hidden' id='edit' name='edit' value=$edit/> ";
                                if ($ticket_exis != "" && $ticket_exis > 0) {
                                    echo "<input type='hidden' id='ticket' name='ticket' value='$ticket_exis'/> ";
                                }
                            }
                        }
                    }
                    ?>                
                    <input type="submit" class="boton" value="Cancelar" onclick="cancelar(); return false;" style="display: inline;"/>                            
                    <div id="sin_origen" style="width:100%; color: red;">Selecciona o agrega origen</div>
                    <div id="sin_detino" style="width:100%; color: red;">Selecciona o agrega destino</div>
                    <?php
                    if (!isset($_GET['ruta'])) {
                        echo "<input type='hidden' id='id' name='id' value='$idEspecial'/> ";
                    } else {
                        echo "<input type='hidden' id='id' name='id' value=''/> ";
                    }

                    if (isset($_POST['regresar']) && !empty($_POST['regresar'])) {
                        echo "<input type='hidden' id='regresar' name='regresar' value='" . $_POST['regresar'] . "'/>";
                    }
                    ?>
                </div>                
                <div style="display: inline; width: 40%;">
                    <table style="display: inline; margin-left: 2%; width: 40%;">
                        <tr>
                            <td colspan="3"><h2>Guardado de ruta</h2></td>
                        </tr>
                        <tr>
                            <td>
                                Guarda ruta como:&nbsp;&nbsp;  
                                <select id="ruta" name="ruta" class="filtro" onchange="cargarNombreRuta();">
                                    <option value=''>Nueva ruta</option>
                                    <?php
                                    foreach ($rutas as $key => $value) {
                                        $s = "";
                                        if ($idRuta == $key) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='$key' $s>$value</option>";
                                    }
                                    ?>
                                </select> &nbsp;&nbsp;  
                            </td>
                            <td>
                                <input type="text" id='nombre_ruta' name='nombre_ruta' maxlength="60" value="<?php echo $nombreRuta; ?>" style="max-width: 60%;"/>
                            </td>
                            <td>
                                <input type="button" name="submitRuta" id="submitRuta" class="boton" value="Guardar Ruta" position="right"/>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><div id="error_ruta" style="color: red; display: none;">* El nombre de ruta es obligatorio</div></td>
                        </tr>
                    </table>

                </div>
            </form>
        </div>
    </body>
</html>