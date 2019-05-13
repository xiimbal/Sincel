<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/DomicilioUsuarioTurno.class.php");
include_once("../WEB-INF/Classes/DomicilioUsuarioTurnoDetalle.class.php");
$pagina_lista = "catalogos/lista_empleados_loyalty.php";
$irViajesE = "";
if (isset($_GET['ve']) && $_GET['ve'] != "") {
    $irViajesE = $_GET['ve']; //Contiene link a alta_autoriza_especial.php
}
$id = "";
$usuario = "";
$nombre = "";
$app = "";
$apm = "";
$telefono = "";
$correo = "";
$password = "";
$puesto = "";
$activo = "checked='checked'";
$mensajero = '';
$idAlmacen = "";
$idUsuarioMDB = "";
$negocios = array();

$domicilioTurno = new DomicilioUsuarioTurno();
$turno = "";
$campania = "";
$area = "";
$contacto = "";
$codigoB = "";
$calle = "";
$exterior = "";
$interior = "";
$colonia = "";
$ciudad = "";
$estado = "";
$delegacion = "";
$cp = "";
$localidad = "";
$latitud = "";
$longitud = "";
$costo_fijo = "";
$forma_pago = "";
$alta_turno = "catalogos/alta_turno.php";
$alta_campania = "catalogos/alta_campania.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_usuario_turno.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Usuario();
                if ($obj->getRegistroById($_POST['id'])) {
                    $id = $obj->getId();
                    $usuario = $obj->getUsuario();
                    $nombre = $obj->getNombre();
                    $app = $obj->getPaterno();
                    $apm = $obj->getMaterno();
                    $telefono = $obj->getTelefono();
                    $correo = $obj->getEmail();
                    $password = $obj->getPassword();
                    $puesto = $obj->getPuesto();
                    $costo_fijo = $obj->getCostoFijo();
                    $forma_pago = $obj->getIdFormaPago();
                    $idAlmacen = $obj->getIdAlmacen();
                    $idUsuarioMDB = $obj->getIdUsuarioMultiBD();
                    if ($obj->getActivo() == "0") {
                        $activo = "";
                    }
                    if ($obj->isMensajeroConductor()) {
                        $mensajero = "checked='checked'";
                    }
                    $negocios = $obj->obtenerNegociosDeUsuario();

                    $domicilioTurno->getRegistroById($_POST['id']);
                    $turno = $domicilioTurno->getTurno();
                    $campania = $domicilioTurno->getCampania();
                    $area = $domicilioTurno->getArea();
                    $contacto = $domicilioTurno->getContacto();
                    $codigoB = $domicilioTurno->getCodigoB();
                    $calle = $domicilioTurno->getCalle();
                    $exterior = $domicilioTurno->getExterior();
                    $interior = $domicilioTurno->getInterior();
                    $colonia = $domicilioTurno->getColonia();
                    $ciudad = $domicilioTurno->getCiudad();
                    $estado = $domicilioTurno->getEstado();
                    $delegacion = $domicilioTurno->getDelegacion();
                    $cp = $domicilioTurno->getCp();
                    $localidad = $domicilioTurno->getLocalidad();
                    $latitud = $domicilioTurno->getLatitud();
                    $longitud = $domicilioTurno->getLongitud();                    
                }
            }
            ?>

            <form id="formUsuario" name="formUsuario" action="/" method="POST">
                <table style="width: 95%;">
                    <tr>
                        <td><label for="usuario">Usuario</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="usuario" name="usuario" value="<?php echo $usuario; ?>"/></td>
                        <td><label for="nombre">Nombre(s)</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>
                        <td><label for="telefono">Teléfono</label></td>
                        <td><input type="text" id="telefono" name="telefono" value="<?php echo $telefono; ?>"/></td> 

<!--                        <td><label for="puesto">Puesto</label></td>
<td>
<select id="puesto" name="puesto" class="filtro">
                        <?php
                        /* $catalogo = new Catalogo();
                          $query = $catalogo->getListaAlta("c_puesto", "Nombre");
                          echo "<option value='0' >Selecciona una opción</option>";
                          while ($rs = mysql_fetch_array($query)) {
                          $s = "";
                          if ($puesto != "" && $puesto == $rs['IdPuesto']) {
                          $s = "selected";
                          }
                          if ($rs['IdPuesto'] != 14) {
                          echo "<option value=" . $rs['IdPuesto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                          }
                          } */
                        ?>
</select>
</td>-->
                    </tr>
                    <tr>
                        <td><label for="paterno">Apellido paterno</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="paterno" name="paterno" value="<?php echo $app; ?>"/></td>
                        <td><label for="materno">Apellido materno</label></td>
                        <td><input type="text" id="materno" name="materno" value="<?php echo $apm; ?>"/></td>
                        <td><label for="correo">Correo electr&oacute;nico</label>
                            <!--<span class="obligatorio"> *</span>-->
                        </td>
                        <td><input type="text" id="correo" name="correo" value="<?php echo $correo; ?>"/></td>
                    </tr>
                    <tr>                        
                        <td><label for="pass1">Contrase&ntilde;a</label><span class="obligatorio"> *</span></td>
                        <td><input type="password" id="pass1" name="pass1" value="<?php echo $password; ?>"/></td>
                        <td><label for="pass1">Repite la contrase&ntilde;a</label><span class="obligatorio"> *</span></td>
                        <td><input type="password" id="pass2" name="pass2" value="<?php echo $password; ?>"/></td>
                        <td></td>
                        <td>
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo                            
                            <input type="checkbox" id="mensajero" name="mensajero" value="Si" <?php echo $mensajero; ?>/>Mensajero
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if (isset($_POST['id'])) {
                                echo '<input type="checkbox" name="cambiar" id="cambiar" onchange="activarDesactivarPassword(\'cambiar\');"/>Cambiar contraseña';
                            }
                            ?>
                        </td>
                        <td><?php echo "<input type='hidden' id='puesto' name='puesto' value='100'/> "; ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><label for="codigoB">No. Codigo de Barras</label>
                            <!--<span class="obligatorio"> *</span>-->
                        </td>
                        <td><input type="text" id="codigoB" name="codigoB" value="<?php echo $codigoB; ?>"/></td>
                        <td>
                            Cuadrante de Empleado
                        </td>
                        <td>
                            <select id="area" name="area" class="filtro">
                                <?php
                                /* Inicializamos la clase */
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                    INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                                echo "<option value='0' >Selecciona Cuadrante</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (!empty($area) && $rs['IdEstado'] == $area) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                            <div id="error_area" style="font-size: 12px; color: red;"></div>
                        </td>
                        <td><label for="contacto">Forma de Contactar</label></td>
                        <td><select id="contacto" name="contacto" style="width: 180px;" class="filtro">
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
                        <td>Forma de pago</td>
                        <td>
                            <select id="forma_pago" name="forma_pago" class="filtro">
                                <option value="">Selecciona una forma de pago</option>
                                <?php
                                $result = $catalogo->getListaAlta("c_formapago", "Descripcion");
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if($forma_pago == $rs['IdFormaPago']){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Descripcion'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>Precio fijo</td>
                        <td>
                            <input type="text" id="costo_fijo" name="costo_fijo" value="<?php echo $costo_fijo; ?>" maxlength="9"/>
                            <br/><span style='font-size:8px;font-style: italic;color:grey;'>Este valor es utilizado en caso que este registro se ocupe como una ruta</span>
                        </td>
                    </tr>
                    <tr>
<!--                        <td><label for="almacen">Almacén</label></td>
                        <td><select id="almacen" name="almacen" style="width: 180px;">
                                <option value="0">Seleccione un almacén</option>
                        <?php
                        echo "<input type='hidden' id='almacen' name='almacen' value='0'/> ";
//                                $catalogo1 = new Catalogo();
//                                $query1 = $catalogo1->getListaAlta("c_almacen", "nombre_almacen");
//                                while ($rs = mysql_fetch_array($query1)) {
//                                    $s = "";
//                                    if ($idAlmacen != "" && $idAlmacen == $rs['id_almacen']) {
//                                        $s = "selected";
//                                    }
//                                    echo "<option value=" . $rs['id_almacen'] . " " . $s . ">" . $rs['nombre_almacen'] . "</option>";
//                                }
                        ?>
                            </select>
                        </td>-->
<!--                        <td><label for="negocios">Negocios</label></td>
                        <td>
                            <select id="negocios" name="negocios[]" class="multiselect" multiple="multiple" style="width: 180px;">                                
                        <?php
                        echo "<input type='hidden' id='negocios' name='negocios[]' value='0'/> ";
//                                $catalogo1 = new Catalogo();
//                                $query1 = $catalogo1->getListaAlta("c_cliente", "NombreRazonSocial");
//                                while ($rs = mysql_fetch_array($query1)) {
//                                    $s = "";
//                                    if (in_array($rs['ClaveCliente'], $negocios)) {
//                                        $s = "selected='selected'";
//                                    }
//                                    echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
//                                }
                        ?>
                            </select>
                        </td>-->
                    </tr>
                </table>
                <fieldset>
                    <legend>Campañas y Turnos</legend>
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 25%;"></td>
                            <td style="width: 25%;"></td>
                            <td style="width: 30%;"></td>
                            <td style="width: 10%;">

                                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta_campania; ?>");' style="float: left; cursor: pointer;" />  
                                Agregar Nueva Campaña
                            </td>
                            <td style="width: 15%;">

                                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta_turno; ?>");' style="float: left; cursor: pointer;" />  
                                Agregar Nuevo Turno
                            </td>
                        </tr>
                    </table>

                    <table style="width: 80%;" id="t_datos_addenda">
                        <tr>
                            <td style="width: 10%">Campaña</td>
                            <td style="width: 11.9%">Turno</td>
                        </tr>
                        <?php
                        if (!empty($id)) {
                            $detalle = new DomicilioUsuarioTurnoDetalle();
                            $result = $detalle->getRegistrosByUsuario($id);
                        } else {
                            $result = NULL;
                        }

                        $numero = 1;
                        if (empty($id) || mysql_num_rows($result) == 0) {

                            echo "<tr id='row_$numero'>";
                            echo "<td style='width: 30%'>";
                            echo "<select id='slcCampania_$numero' name='slcCampania_$numero' class='filtro'>";
                            echo "<option value='0'>Seleccione una campaña</option>";

                            $catalogo = new Catalogo();
                            $queryCampania = $catalogo->getListaAlta("c_area", "Descripcion");
                            while ($rs = mysql_fetch_array($queryCampania)) {
                                $s = "";
                                if ($campania != "" && $campania == $rs['IdArea'])
                                    $s = "selected";
                                if (($rs['ClaveCentroCosto']) != NULL || ($rs['ClaveCentroCosto']) != "") {
                                    echo "<option value='" . $rs['IdArea'] . "' $s>" . $rs['Descripcion'] . "</option>";
                                }
                            }
                            echo "</select>";

                            echo "</td>";
                            echo "<td style='width: 25%'>";
                            echo "<select id='slcTurno_$numero' name='slcTurno_$numero' class='filtro'>";
                            echo "<option value='0'>Seleccione un Turno</option>";
                            $catalogo = new Catalogo();
                            $queryTurno = $catalogo->getListaAlta("c_turno", "descripcion");
                            while ($rs = mysql_fetch_array($queryTurno)) {
                                $s = "";
                                if ($turno != "" && $turno == $rs['idTurno'])
                                    $s = "selected";
                                echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . "</option>";
                            }

                            echo "</select>";
                            echo "</td>";
                            echo "<td><input type='image' src='resources/images/add.png' title='Agregar otro concepto' onclick='agregarConcepto(); return false;' /></td>";
                            echo "</tr>";
                        }else {
                            while ($rs = mysql_fetch_array($result)) {
                                $campania = $rs['idCampania'];
                                $turno = $rs['idTurno'];
                                echo "<tr id='row_$numero'>";
                                echo "<td style='width: 30%'>";
                                echo "<select id='slcCampania_$numero' name='slcCampania_$numero' class='filtro'>";
                                echo "<option value='0'>Seleccione una campaña</option>";

                                $catalogo = new Catalogo();
                                $queryCampania = $catalogo->getListaAlta("c_area", "Descripcion");
                                while ($rsC = mysql_fetch_array($queryCampania)) {
                                    $s = "";
                                    if ($campania != "" && $campania == $rsC['IdArea'])
                                        $s = "selected";
                                    if (($rsC['ClaveCentroCosto']) != NULL || ($rsC['ClaveCentroCosto']) != "") {
                                        echo "<option value='" . $rsC['IdArea'] . "' $s>" . $rsC['Descripcion'] . "</option>";
                                    }
                                }
                                echo "</select>";
                                echo "</td>";

                                echo "<td style='width: 25%'>";
                                echo "<select id='slcTurno_$numero' name='slcTurno_$numero' class='filtro'>";
                                echo "<option value='0'>Seleccione un Turno</option>";
                                $catalogo = new Catalogo();
                                $queryTurno = $catalogo->getListaAlta("c_turno", "descripcion");
                                while ($rsT = mysql_fetch_array($queryTurno)) {
                                    $s = "";
                                    if ($turno != "" && $turno == $rsT['idTurno'])
                                        $s = "selected";
                                    echo "<option value='" . $rsT['idTurno'] . "' $s>" . $rsT['descripcion'] . "</option>";
                                }

                                echo "</select>";
                                echo "</td>";

                                echo '<td><input type="image" src="resources/images/add.png" title="Agregar otro concepto" onclick="agregarConcepto(); return false;" /></td>';
                                if ($numero > 1) {
                                    echo "<td><input type='image' src='resources/images/Erase.png' title='Eliminar este concepto' onclick='borrarConcepto(" . $numero . "); return false;'/></td>";
                                }
                                $numero++;
                                echo "</tr>";
                            }
                            $numero--;
                        }
                        ?>
                    </table>
                </fieldset>
                <br/>
                <fieldset>
                    <legend>Dirección</legend>
                    <table style="width:100%"> 
                        <tr>
                            <td>Calle</td><td><input type="text" id="txtCalle" name="txtCalle" value="<?php echo $calle ?>"></td>
                            <td>No. Exterior</td><td><input type="text" id="txtExterior" name="txtExterior" value="<?php echo $exterior ?>"></td>
                            <td>No. Interior</td><td><input type="text" id="txtInterior" name="txtInterior" value="<?php echo $interior ?>"></td>
                        </tr>
                        <tr>
                            <td>Colonia</td><td><input type="text" id="txtColonia" name="txtColonia" value="<?php echo $colonia ?>"></td>
                            <td>Ciudad</td><td><input type="text" id="txtCiudad" name="txtCiudad" value="<?php echo $ciudad ?>"></td>
                            <td>Estado</td>
                            <td>
                                <select id="slcEstado" name="slcEstado">
                                    <option value="0">Seleccione un estado</option>
                                    <?php
                                    $catalogo = new Catalogo();
                                    $queryEstado = $catalogo->getListaAlta("c_ciudades", "Ciudad");
                                    while ($rs = mysql_fetch_array($queryEstado)) {
                                        $s = "";
                                        if ($estado != "" && $estado == $rs['IdCiudad'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['IdCiudad'] . "' $s>" . $rs['Ciudad'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Delegación</td><td><input type="text" id="txtDelegacion" name="txtDelegacion" value="<?php echo $delegacion ?>"></td>
                            <td>C.P.</td><td><input type="text" id="txtcp" name="txtcp" value="<?php echo $cp ?>"></td>
                            <td>Localidad</td><td><input type="text" id="txtLocalidad" name="txtLocalidad" value="<?php echo $localidad ?>"></td>
                        </tr>
                        <tr>
                            <td>Latitud</td><td><input type="number" id="Latitud" name="Latitud" value="<?php echo $latitud ?>" step="any"></td>
                            <td>Longitud</td><td><input type="number" id="Longitud" name="Longitud" value="<?php echo $longitud ?>" step="any"></td>
                            <td></td><td></td>
                        </tr>
                    </table>
                </fieldset>
                <br/>
                <input type="submit" name="submit" class="boton" value="Guardar" />                
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>                            
                       <?php
                       echo "<input type='hidden' id='id' name='id' value='$id'/> ";
                       echo "<input type='hidden' id='idUsuarioMBD' name='idUsuarioMBD' value='$idUsuarioMDB'/> ";
                       echo "<input type='hidden' id='NoAlta' name='NoAlta' value='2'/> ";
                       echo "<input type='hidden' id='ve' name='ve' value='$irViajesE'/> ";
                       ?>
                <input type="hidden" id="numero_conceptos" name="numero_conceptos" value="<?php echo $numero; ?>"/>
            </form>
        </div>
    </body>
</html>
