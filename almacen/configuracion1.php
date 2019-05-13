<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");

$pagina_anterior = "almacen/lista_configuracion.php";
if (isset($_GET['regresar'])) {
    $pagina_anterior = $_GET['regresar'];
}

$catalogo = new Catalogo();
$usuario = new Usuario();
$permiso = new PermisosSubMenu();
$id = "";
$id_solicitud = '';
$boolDemo = false;
$NoParte = '';
$NoSerie = '';
$IP = '';
$idAlmacen = '';
$Mac = '';
$ClaveCentroCosto = '';
$tipoServicio = '1'; /* Por default se maneja particular */
$IdAnexoClienteCC = '';
$IdServicio = '';
$ClaveCliente = '';
$NoSerieGenesis = '';
$tipo = '';
$checkedCliente = "checked='checked'";
$name_select_parte = 'id="no_parte" name="no_parte"';
$disabled = "";
$checkedAlmacen = "";
$Ubicacion = "";
$Demo = "";

$obj = new Configuracion();
if (isset($_POST['id']) && $obj->getRegistroById($_POST['id'])) {
    $id = $obj->getId_bitacora();
    $id_solicitud = $obj->getId_solicitud();
    $NoParte = $obj->getNoParte();
    $NoSerie = $obj->getNoSerie();
    $NoSerieGenesis = $obj->getNoGenesis();
    $IP = $obj->getIP();
    $Mac = $obj->getMac();
    $tipo = $obj->getIdTipoInventario();
    $componentes = $obj->getComponentesK();
    if (!$permiso->tienePermisoEspecial($_SESSION['idUsuario'], 5)) {
        $name_select_parte = "";
        $disabled = "disabled= 'disabled'";
    }
}

/* Encontramos su ubicacion real */
$almacen = false;
$consulta = "SELECT a.nombre_almacen, a.id_almacen, kae.Ubicacion FROM k_almacenequipo AS kae 
        INNER JOIN c_almacen AS a ON kae.NoSerie = '$NoSerie' AND kae.id_almacen = a.id_almacen;";
$query = $catalogo->obtenerLista($consulta);
while ($rs = mysql_fetch_array($query)) {
    $checkedAlmacen = "checked='checked'";
    $idAlmacen = $rs['id_almacen'];
    $Ubicacion = $rs['Ubicacion'];
    $almacen = true;
}

$idKServicio = "";
if (!$almacen) {
    $consulta = "SELECT (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN '0' ELSE '1' END) AS tipo, cinv.IdKServicio,
        cinv.IdAnexoClienteCC, cinv.ClaveEspKServicioFAIM, cinv.Ubicacion, ks.IdKserviciogimgfa, cinv.Demo
        FROM `c_inventarioequipo` AS cinv
        LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
        WHERE cinv.NoSerie = '$NoSerie';";
    //echo $consulta;
    $query = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($query)) {
        $checkedCliente = "checked='checked'";
        if ($rs['Demo'] == "1") {
            $boolDemo = true;
        }
        $Demo = "checked='checked'";
        $ClaveCentroCosto = $rs['ClaveCentroCosto'];
        $tipoServicio = $rs['tipo'];
        $Ubicacion = $rs['Ubicacion'];
        $IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
        $IdServicio = $rs['ClaveEspKServicioFAIM'];
        $idKServicio = $rs['IdKServicio'];
        $CC = new CentroCosto();
        if ($CC->getRegistroById($ClaveCentroCosto)) {
            $ClaveCliente = $CC->getClaveCliente();
        }
    }
}

if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 22)) {/* Si es de almacen, solo puede poner el numero de serie */
    $soloSerie = "true";
} else {
    $soloSerie = "false";
}

$selects = Array("SELECT c_serviciofa.IdServicioFA AS ID,
				c_serviciofa.Nombre AS Nombre
    FROM c_serviciofa;", "SELECT c_serviciogfa.IdServicioGFA AS ID,
                           c_serviciogfa.Nombre
    FROM c_serviciogfa;", "SELECT c_serviciogim.IdServicioGIM AS ID,
                           c_serviciogim.Nombre AS Nombre
    FROM c_serviciogim;", "SELECT c_servicioim.IdServicioIM AS ID,
                           c_servicioim.Nombre
    FROM c_servicioim;");

$servicio = "<option value=''>Selecciona el servicio</option>";
foreach ($selects as $select) {
    $query = $catalogo->obtenerLista($select);
    while ($rs = mysql_fetch_array($query)) {
        $s = "";
        if ($IdServicio != "" && $IdServicio == $rs['ID']) {
            $s = "selected='selected'";
        }
        $servicio = $servicio . "<option value=\"" . $rs['ID'] . "\" $s>" . $rs['Nombre'] . "</option>";
    }
}

$query = $catalogo->getListaAlta("c_componente", "NoParte");

$tipos = "";
$query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
while ($rs = mysql_fetch_array($query2)) {
    $tipos.= "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/configuracion.js"></script>
        <style>
            .area_trabajo {margin: 5% 5% 0 5%;}
            .entrada {width: 200px;}
            .cliente_div {display: none;}
            .almacen_div{display: none;}
        </style>
    </head>
    <body>
        <div id="info_config" style="display: none;"></div>
        <div class="area_trabajo">
            <form id="formConfiguracion" name="formConfiguracion">
                <fieldset>
                    <legend>Equipo</legend>
                    <table style="width: 100%;">
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>
                                <input type="radio" id="radio_cliente" name="radio_ubicacion" onchange="mostrarOcultarDiv('radio_cliente', 'cliente_div', 'almacen_div');" value="cliente" <?php echo $checkedCliente; ?>/>Cliente
                                <input type="radio" id="radio_almacen" name="radio_ubicacion" onchange="mostrarOcultarDiv('radio_almacen', 'almacen_div', 'cliente_div');" value="almacen" <?php echo $checkedAlmacen; ?>/>Almac&eacute;n
                            </td>
                        </tr>
                        <tr>
                            <td><label for="sol_equipo">Solicitud de equipo</label></td>
                            <td>
                                <input type="text" id="sol_equipo" name="sol_equipo" value="<?php echo $id_solicitud; ?>" style="width: 200px;" readonly="readonly" />                                
                            </td>
                            <td><label for="cliente" class="cliente_div">Cliente</label></td>
                            <td>
                                <select class="entrada cliente_div" id="cliente" name="cliente" onchange="cargarLocalidadByCliente('localidad', 'cliente');
                                        limpiarAnexo('anexo');">
                                    <?php
                                    /* Inicializamos la clase */
                                    $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                    echo "<option value=''>Selecciona el cliente</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value='" . $rs['ClaveCliente'] . "'>" . $rs['NombreRazonSocial'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="no_parte">No. Parte</label></td>
                            <td>
                                <select class="filtro" <?php echo $name_select_parte; ?> onchange="cargarModeloByParte('modelo', 'no_parte');" style="width: 160px;" <?php echo $disabled; ?>>
                                    <?php
                                    /* Inicializamos la clase */
                                    $query = $catalogo->getListaAlta("c_equipo", "NoParte");
                                    echo "<option value=''>Selecciona el número de parte</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($NoParte != "" && $rs['NoParte'] == $NoParte) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='" . $rs['NoParte'] . "' $s>" . $rs['NoParte'] . " / " . $rs['Modelo'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            </td>
                            <td><label for="localidad" class="cliente_div">Localidad</label></td>
                            <td>
                                <select class="entrada cliente_div" id="localidad" name="localidad" onchange="cargaranexo('localidad', 'anexo');">                                
<?php
echo "<option value=''>Selecciona la localidad</option>";
?> 
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="modelo">Modelo</label></td>
                            <td><input type="text" class="entrada" id="modelo" name="modelo" readonly="readonly" /></td>
                            <td><label for="anexo" class="cliente_div">Anexo</label></td>
                            <td>
                                <select class="entrada cliente_div" id="anexo" name="anexo" onchange="cargarServicios('anexo', 'servicio');"> 
                                <?php
                                echo "<option value=''>Selecciona el anexo/option>";
                                ?> 
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="serie">No. Serie</label></td>
                            <td>
                                <input type="text" class="entrada" id="serie" name="serie" onkeyup="quitarblancos('serie');" value="<?php echo $NoSerie ?>"/>
                                <input type="hidden" class="entrada" id="serie_original" name="serie_original" value="<?php echo $NoSerie ?>"/>
                            </td>
                            <td><label for="servicio" class="cliente_div">Servicio</label></td>
                            <td>
                                <select class="entrada cliente_div" id="servicio" name="servicio">                                

                                </select>
                                <input type="hidden" id="IdKServicio" name="IdKServicio" value="<?php echo $idKServicio; ?>"/>
                                <input type="hidden" id="IdServicioInv" name="IdServicioInv" value="<?php echo $IdServicio; ?>"/>

                            </td>
                        </tr>
                        <tr>
                            <td><label for="serie_genesis">No. Serie Génesis</label></td>
                            <td><input type="text" class="entrada" id="serie_genesis" name="serie_genesis" value="<?php echo $NoSerieGenesis ?>"/></td>
                            <td><div class="almacen_div">Almac&eacute;n</div></td>
                            <td>
                                <select class="entrada almacen_div" id="almacen_equipo" name="almacen_equipo">
                                    <?php
                                    /* Inicializamos la clase */
                                    $query = $catalogo->getListaAlta("c_almacen", "nombre_almacen");
                                    echo "<option value=''>Selecciona el almacén</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($idAlmacen != "" && $idAlmacen == $rs['id_almacen']) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='" . $rs['id_almacen'] . "' $s>" . $rs['nombre_almacen'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="ip">IP</label></td>
                            <td><input type="text" class="entrada" id="ip" name="ip" value="<?php echo $IP ?>"/></td>
                            <td><label for="ubicacion">Ubicación</label></td>
                            <td><input type="text" class="entrada" id="ubicacion" name="ubicacion" value="<?php echo $Ubicacion ?>"/></td>
                        </tr>
                        <tr>
                            <td><label for="mac">MAC Adress</label></td>
                            <td><input type="text" class="entrada" id="mac" name="mac" value="<?php echo $Mac ?>"/></td> 
                            <td><label for="tipo_inventario">Tipo inventario</label></td>
                            <td>                              
                                <select class="entrada" id="tipo_inventario" name="tipo_inventario">                                
                                    <?php
                                    /* Inicializamos la clase */
                                    $query = $catalogo->getListaAlta("c_tipoinventario", "Nombre");
                                    echo "<option value='null'>Selecciona el tipo de inventario</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($tipo != "" && $rs['idTipo'] == $tipo) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='" . $rs['idTipo'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            </td>
                        </tr>
<?php
if ($boolDemo) {
    ?>        
                            <tr>
                                <td><label for="equipo_demo">Equipo en demo</label></td>
                                <td><input type="checkbox" id="equipo_demo" name="equipo_demo" value="1" <?php echo $Demo; ?>/></td>
                                <td><label for="cobrar_hojas">Cobrar hojas en demo</label></td>
                                <td><input type="checkbox" id="cobrar_hojas" name="cobrar_hojas" value="1"/></td>
                            </tr>
                            <tr>
                                <td><label for="contadorBN">Contador B/N</label></td>
                                <td><input type="number" value = "" maxlength="8" name="contadorBN" id="contadorBN"/></td>
                                <?php
                                $equipoCaracteristica = new EquipoCaracteristicasFormatoServicio();
                                //Verificamos si permite impresiones a color para mostrar la siguiente fila
                                if ($equipoCaracteristica->isColor($NoParte)) {
                                    ?>
                                    <td><label for="contadorColor">Contador Color</label></td>
                                    <td><input type="number" value = "" maxlength="8" name="contadorColor" id="contadorColor"/></td>
                                    <?php
                                }
                                ?>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Componentes</legend>                    
                    <table id="table_componentes">
                        <tr>
                            <td>Tipo</td>
                            <td>No. Parte</td>                            
                            <td>Fecha</td>
                        </tr>
                        <?php
                        $cantidad_componente = 1;
                        if (!isset($_POST['id'])) {/* Si no se esta editando la bitacora */
                            echo "<tr>";
                            echo "<td>
                                    <select id=\"tipo1\" name=\"tipo1\" class=\"size filtro\" onchange=\"cambiarSelectModeloCompatible('tipo1', 'c_no_parte_1','$NoParte');\">
                                    <option value=''>Selecciona el tipo</option>
                                    $tipos
                                    </select>
                                    </td>";
                            echo "<td><select class='entrada filtro' id='c_no_parte_1' name='c_no_parte_1'></select></td>";
                            echo "<td><input type='text' class='fecha entrada' id='c_fecha_1' name='c_fecha_1'/></td>";
                            echo "</tr>";
                        } else {
                            if (isset($componentes[$cantidad_componente])) {
                                $c = $componentes[$cantidad_componente];
                                while ($c['tipo'] == "0") {
                                    echo "<tr>";
                                    echo "<td>
                                        <select id=\"tipo$cantidad_componente\" name=\"tipo$cantidad_componente\" class=\"size filtro\" 
                                            onchange=\"cambiarSelectModeloCompatible('tipo$cantidad_componente', 'c_no_parte_$cantidad_componente','$NoParte');\">
                                        <option value=''>Selecciona el tipo</option>
                                        $tipos
                                        </select>
                                        </td>";
                                    echo "<td><select class='entrada filtro' id='c_no_parte_$cantidad_componente' name='c_no_parte_$cantidad_componente'>";
                                    /* Obtenemos componentes con el tipo de componente actual y compatibles con el no de parte del equipo */
                                    $query = $catalogo->obtenerLista("SELECT c.NoParte, c.IdTipoComponente, c.Modelo, c.Descripcion, cc.NoParteEquipo, cc.Soportado 
                                        FROM c_componente AS c
                                        LEFT JOIN k_equipocomponentecompatible AS cc ON c.NoParte = cc.NoParteComponente
                                        WHERE c.IdTipoComponente = " . $c['IdTipoComponente'] . " AND (cc.NoParteEquipo = '$NoParte' OR ISNULL(NoParteEquipo));");
                                    echo "<option value=''>Selecciona el No. de parte</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($c['NoParte'] == $rs['NoParte']) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value=\"" . $rs['NoParte'] . "\" $s>" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                    }
                                    echo "</select></td>";
                                    echo "<td><input type='text' class='fecha entrada' id='c_fecha_$cantidad_componente' name='c_fecha_$cantidad_componente' value='" . $c['fecha'] . "'/></td>";
                                    echo "</tr>";
                                    /* Seleccionamos el tipo de componente actual */
                                    echo "<script type='text/javascript'>$('#tipo$cantidad_componente').val('" . $c['IdTipoComponente'] . "');</script>";
                                    if (isset($componentes[++$cantidad_componente])) {
                                        $c = $componentes[$cantidad_componente];
                                    } else {
                                        break;
                                    }
                                }
                                $cantidad_componente--;
                            } else {
                                echo "<tr>";
                                echo "<td>
                                        <select id=\"tipo1\" name=\"tipo1\" class=\"size filtro\" onchange=\"cambiarSelectModeloCompatible('tipo1', 'c_no_parte_1','$NoParte');\">
                                        <option value=''>Selecciona el tipo</option>
                                        $tipos
                                        </select>
                                        </td>";
                                echo "<td><select class='entrada filtro' id='c_no_parte_1' name='c_no_parte_1'></select></td>";
                                echo "<td><input type='text' class='fecha entrada' id='c_fecha_1' name='c_fecha_1'/></td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </table>
                    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick="nuevoComponente();" style="float: right; cursor: pointer;" />  
                </fieldset>
                <fieldset>
                    <legend>Suministros</legend>                    
                    <table id="table_suministro">
                        <tr>
                            <td>Tipo</td>
                            <td>No. Parte</td>                            
                            <td>Fecha</td>
                        </tr>
                        <?php
                        $cantidad_suministro = 1;
                        if (!isset($_POST['id'])) {/* Si no se esta editando la bitacora */
                            echo "<tr>";
                            echo "<td>
                                    <select id=\"stipo1\" name=\"stipo1\" class=\"size filtro\" onchange=\"cambiarSelectModeloCompatible('stipo1', 's_no_parte_1','$NoParte');\">
                                    <option value=''>Selecciona el tipo</option>
                                    $tipos
                                    </select>
                                    </td>";
                            echo "<td><select class='entrada filtro' id='s_no_parte_1' name='s_no_parte_1'></select></td>";
                            echo "<td><input type='text' class='fecha entrada' id='s_fecha_1' name='s_fecha_1'/></td>";
                            echo "</tr>";
                        } else {
                            $aux_componentes = $cantidad_componente;
                            if (isset($componentes[++$aux_componentes])) {
                                $c = $componentes[$aux_componentes];
                                while ($c['tipo'] == "1") {
                                    echo "<tr>";
                                    echo "<td>
                                        <select id=\"stipo$cantidad_suministro\" name=\"stipo$cantidad_suministro\" class=\"size filtro\" 
                                            onchange=\"cambiarSelectModeloCompatible('stipo$cantidad_suministro', 's_no_parte_$cantidad_suministro','$NoParte');\">
                                        <option value=''>Selecciona el tipo</option>
                                        $tipos
                                        </select>
                                        </td>";
                                    echo "<td><select class='entrada filtro' id='s_no_parte_$cantidad_suministro' name='s_no_parte_$cantidad_suministro'>";
                                    /* Obtenemos componentes con el tipo de componente actual y compatibles con el no de parte del equipo */
                                    $query = $catalogo->obtenerLista("SELECT c.NoParte, c.IdTipoComponente, c.Modelo, c.Descripcion, cc.NoParteEquipo, cc.Soportado 
                                        FROM c_componente AS c
                                        LEFT JOIN k_equipocomponentecompatible AS cc ON c.NoParte = cc.NoParteComponente
                                        WHERE c.IdTipoComponente = " . $c['IdTipoComponente'] . " AND (cc.NoParteEquipo = '$NoParte' OR ISNULL(NoParteEquipo));");
                                    echo "<option value=''>Selecciona el No. de parte</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($c['NoParte'] == $rs['NoParte']) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value=\"" . $rs['NoParte'] . "\" $s>" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                    }
                                    echo "</select></td>";
                                    echo "<td><input type='text' class='fecha entrada' id='s_fecha_$cantidad_suministro' name='s_fecha_$cantidad_suministro' value='" . $c['fecha'] . "'/></td>";
                                    echo "</tr>";
                                    /* Seleccionamos el tipo de componente actual */
                                    echo "<script type='text/javascript'>$('#stipo$cantidad_suministro').val('" . $c['IdTipoComponente'] . "');</script>";
                                    $aux_componentes++;
                                    if (isset($componentes[$aux_componentes])) {
                                        $c = $componentes[$aux_componentes];
                                    } else {
                                        break;
                                    }
                                    $cantidad_suministro++;
                                }
                            } else {
                                echo "<tr>";
                                echo "<td>
                                        <select id=\"stipo1\" name=\"stipo1\" class=\"size filtro\" onchange=\"cambiarSelectModeloCompatible('stipo1', 's_no_parte_1','$NoParte');\">
                                        <option value=''>Selecciona el tipo</option>
                                        $tipos
                                        </select>
                                        </td>";
                                echo "<td><select class='entrada filtro' id='s_no_parte_1' name='s_no_parte_1'></select></td>";
                                echo "<td><input type='text' class='fecha entrada' id='s_fecha_1' name='s_fecha_1'/></td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </table>
                    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick="nuevoSuministro();" style="float: right; cursor: pointer;" />  
                </fieldset>
                <input type="submit" class="button" value="Cancelar" style="float: right;" onclick="cambiarContenidos('<?php echo $pagina_anterior; ?>', 'Configuración de equipo');
                        return false;"/>
                <input type="submit" class="button" value="Guardar" style="float: right;"/>
                <input type="hidden" id="cantidad_componentes" name="cantidad_componentes" value="<?php echo $cantidad_componente; ?>"/>
                <input type="hidden" id="cantidad_suministros" name="cantidad_suministros" value="<?php echo $cantidad_suministro; ?>"/>
                <input type="hidden" id="solo_serie" name="solo_serie" value="<?php echo $soloSerie; ?>"/>
                <input type="hidden" id="clave_cliente" name="clave_cliente" value="<?php echo $ClaveCliente; ?>"/>
                <input type="hidden" id="clave_cc" name="clave_cc" value="<?php echo $ClaveCentroCosto; ?>"/>
                <input type="hidden" id="tipo_servicio" name="tipo_servicio" value="<?php echo $tipoServicio; ?>"/>
                <input type="hidden" id="id_anexo" name="id_anexo" value="<?php echo $IdAnexoClienteCC; ?>"/>
                <input type="hidden" id="id_bitacora" name="id_bitacora" value="<?php echo $id; ?>"/>
                <input type="hidden" id="no_parte_confi" name="no_parte_confi" value="<?php echo $id; ?>"/>
                <input type="hidden" id="pagina_anterior" name="pagina_anterior" value="<?php echo $pagina_anterior; ?>"/>
                <?php
                if ($name_select_parte == "") {/* Si se esta editando el equipo */
                    echo "<input type='hidden' id='no_parte' name='no_parte' value='$NoParte'/>";
                }
                ?>
                <br/><br/>
            </form>             
        </div>
    </body>
</html>