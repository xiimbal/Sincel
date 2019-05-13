<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/DomicilioUsuarioTurno.class.php");
$pagina_lista = "catalogos/lista_usuario_turno.php";

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
                $obj->getRegistroById($_POST['id']);
                $id = $obj->getId();
                $usuario = $obj->getUsuario();
                $nombre = $obj->getNombre();
                $app = $obj->getPaterno();
                $apm = $obj->getMaterno();
                $telefono = $obj->getTelefono();
                $correo = $obj->getEmail();
                $password = $obj->getPassword();
                $puesto = $obj->getPuesto();
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
            ?>

            <form id="formUsuario" name="formUsuario" action="/" method="POST">
                <table style="width: 95%;">
                    <tr>
                        <td><label for="usuario">Usuario</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="usuario" name="usuario" value="<?php echo $usuario; ?>"/></td>
                        <td><label for="nombre">Nombre(s)</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>                        
                        <td><label for="puesto">Puesto</label></td>
                        <td>
                            <select id="puesto" name="puesto" class="filtro">
                                <?php
                                $catalogo = new Catalogo();
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
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="paterno">Apellido paterno</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="paterno" name="paterno" value="<?php echo $app; ?>"/></td>
                        <td><label for="materno">Apellido materno</label></td>
                        <td><input type="text" id="materno" name="materno" value="<?php echo $apm; ?>"/></td>
                        <td><label for="telefono">Teléfono</label></td>
                        <td><input type="text" id="telefono" name="telefono" value="<?php echo $telefono; ?>"/></td> 
                    </tr>
                    <tr>                        
                        <td><label for="pass1">Contrase&ntilde;a</label><span class="obligatorio"> *</span></td>
                        <td><input type="password" id="pass1" name="pass1" value="<?php echo $password; ?>"/></td>
                        <td><label for="pass1">Repite la contrase&ntilde;a</label><span class="obligatorio"> *</span></td>
                        <td><input type="password" id="pass2" name="pass2" value="<?php echo $password; ?>"/></td>
                        <td><label for="correo">Correo electr&oacute;nico</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="correo" name="correo" value="<?php echo $correo; ?>"/></td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if (isset($_POST['id'])) {
                                echo '<input type="checkbox" name="cambiar" id="cambiar" onchange="activarDesactivarPassword(\'cambiar\');"/>Cambiar contraseña';
                            }
                            ?>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><label for="almacen">Almacén</label></td>
                        <td><select id="almacen" name="almacen" style="width: 180px;">
                                <option value="0">Seleccione un almacén</option>
                                <?php
                                $catalogo1 = new Catalogo();
                                $query1 = $catalogo1->getListaAlta("c_almacen", "nombre_almacen");
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($idAlmacen != "" && $idAlmacen == $rs['id_almacen']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['id_almacen'] . " " . $s . ">" . $rs['nombre_almacen'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="negocios">Negocios</label></td>
                        <td>
                            <select id="negocios" name="negocios[]" class="multiselect" multiple="multiple" style="width: 180px;">                                
                                <?php
                                $catalogo1 = new Catalogo();
                                $query1 = $catalogo1->getListaAlta("c_cliente", "NombreRazonSocial");
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if (in_array($rs['ClaveCliente'], $negocios)) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td></td>
                        <td>
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo                            
                            <input type="checkbox" id="mensajero" name="mensajero" value="Si" <?php echo $mensajero; ?>/>Mensajero
                        </td>
                    </tr>
                </table>
                <br/>
                <fieldset>
                    <legend>Dirección</legend> 
                    <table style="width: 95%;">
                        <tr>
                            <td style="width: 10%">Campaña</td>
                            <td style="width: 15%">
                                <select id="slcCampania" name="slcCampania" class="filtro">
                                    <option value="0">Seleccione una campaña</option>
                                    <?php
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
                                    ?>
                                </select>
                            </td>
                            <td style="width: 5%">

                                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta_campania; ?>");' style="float: left; cursor: pointer;" />  

                            </td>

                            <td style="width: 11.9%">Turno</td>
                            <td style="width: 15%">
                                <select id="slcTurno" name="slcTurno" class="filtro">
                                    <option value="0">Seleccione un Turno</option>
                                    <?php
                                    $catalogo = new Catalogo();
                                    $queryTurno = $catalogo->getListaAlta("c_turno", "descripcion");
                                    while ($rs = mysql_fetch_array($queryTurno)) {
                                        $s = "";
                                        if ($turno != "" && $turno == $rs['idTurno'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>

                                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta_turno; ?>");' style="float: left; cursor: pointer;" />  

                            </td>
                            <td>
                                Cuadrante
                            </td>
                            <td>
                                <select id="area" name="area" class="filtro">
                                    <?php
                                    /* Inicializamos la clase */
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
                        </tr>

                    </table>
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
                       echo "<input type='hidden' id='NoAlta' name='NoAlta' value='1'/> ";
                       ?>
            </form>
        </div>
    </body>
</html>