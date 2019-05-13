<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/DomicilioAlmacen.class.php");
$domicilioAlmacen = new DomicilioAlmacen();
$pagina_lista = "admin/lista_almacen.php";
$idAlmacen = "";
$nombre = "";
$activo = "checked='checked'";
$read = "";
$tipoAlmacen = "";
$prioridad = 1;
$localidades = array();
$cliente = array();
$selectCliente = "";
$todoGrupo = "";
$porGrupo = ""; //"checked";
$showgrupo = "";
$grupo = "";
$tamañoCliente = "85%";
$calle = "";
$exterior = "";
$interior = "";
$colonia = "";
$ciudad = "";
$estado = "";
$delegacion = "";
$pais = "";
$cp = "";
$surtir = "";
$latitud = "";
$longitud = "";
$tamanoTabla = 1;
if (isset($_POST['cliente'])) {
    $tipoAlmacen = $_POST['tipoAlmacen'];
    $cliente = $_POST['cliente'];
    if ($tipoAlmacen == "1")
        $selectCliente = "selected";
    if ($tipoAlmacen == "2")
        $selectPropio = "selected";
    $idAlmacen = $_POST['idAlmacen'];
    $nombre = $_POST['nombre'];
    $showgrupo = $_POST['grupo'];
//    if (isset($_POST['todo']) && $_POST['todo'] == "seleccionado")
//        $todoGrupo = "checked='checked";
//    else
//        $todoGrupo = "";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_almacen.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div id="mensajes"></div>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Almacen();
                $obj->getRegistroById($_POST['id']);
                $domicilioAlmacen->getRegistroById($_POST['id']);
                $obj->getRegistroMiniAlmacenById($_POST['id']);
                $obj->getRegistroMiniAlmacenByIdLocalidad($_POST['id']);
                $localidades = $obj->getLocalidades();
                $cliente = $obj->getArrayCliente();
                $read = "readonly='readonly'";
                $idAlmacen = $obj->getIdAlmacen();
                $surtir = (int)$obj->getSurtir();
                $nombre = $obj->getNombre();
                $showgrupo = $obj->getGrupoCliente(); //grupo del cliente
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                $tipoAlmacen = $obj->getTipoAlmacen();
                $prioridad = $obj->getPrioridad();
                if ($tipoAlmacen == "0")
                    $selectCliente = "selected";
                if ($tipoAlmacen == "1")
                    $selectPropio = "selected";

                $grupo = $obj->getClienteGrupo();

                if ($grupo != "" || $grupo != null) {
                    $showgrupo = $obj->getClienteGrupo();
                    $porGrupo = "checked";
                }
                $calle = $domicilioAlmacen->getCalle();
                $exterior = $domicilioAlmacen->getExterior();
                $interior = $domicilioAlmacen->getInterior();
                $colonia = $domicilioAlmacen->getColonia();
                $ciudad = $domicilioAlmacen->getCiudad();
                $estado = $domicilioAlmacen->getEstado();
                $delegacion = $domicilioAlmacen->getDelegacion();
                $pais = $domicilioAlmacen->getPais();
                $cp = $domicilioAlmacen->getCp();
                $latitud = $domicilioAlmacen->getLatitud();
                $longitud = $domicilioAlmacen->getLongitud();
            }
            ?>
            <form id="formAlmacen" name="formAlmacen" action="/" method="POST">
                <table style="width: 50%;" >
                    <tr>
                        <td style="width: 30%"><label for="tipo">Tipo de almcen</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="tipo" name="tipo" style="width: 190px" onchange="mostrarFormatoCliente();">
                                <option value="0">Seleccione tipo de almacén</option>
                                <option value="1" <?php echo $selectCliente ?> >Cliente</option>
                                <option value="2" <?php echo $selectPropio ?> >Propio</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <div>
                    <table style="width: 50%;" id="datosObligatorios">
                        <tr>
                            <td style="width: 30%"><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                            <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>" style="width: 220px"/></td>
                            <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                        </tr>   
                        <tr>
                            <td><label for="prioridad">Prioridad</label><span class="obligatorio"> *</span></td>
                            <td><input type="number" id="prioridad" name="prioridad" value="<?php echo $prioridad; ?>" style="width: 220px"/></td>
                        </tr>
                    </table>
                    <fieldset>
                        <legend>Dirección del almacén</legend> 
                        <table style="width:100%">                            
                            <tr>
                                <td>Calle<span class="obligatorio"> *</span></td><td><input type="text" id="txtCalle" name="txtCalle" value="<?php echo $calle ?>"></td>
                                <td>No. Exterior<span class="obligatorio"> *</span></td><td><input type="text" id="txtExterior" name="txtExterior" value="<?php echo $exterior ?>"></td>
                                <td>No. Interior</td><td><input type="text" id="txtInterior" name="txtInterior" value="<?php echo $interior ?>"></td>
                            </tr>
                            <tr>
                                <td>Colonia<span class="obligatorio"> *</span></td><td><input type="text" id="txtColonia" name="txtColonia" value="<?php echo $colonia ?>"></td>
                                <td>Ciudad<span class="obligatorio"> *</span></td><td><input type="text" id="txtCiudad" name="txtCiudad" value="<?php echo $ciudad ?>"></td>
                                <td>Estado<span class="obligatorio"> *</span></td>
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
                                <td>Delegación<span class="obligatorio"> *</span></td><td><input type="text" id="txtDelegacion" name="txtDelegacion" value="<?php echo $delegacion ?>"></td>
                                <td>País<span class="obligatorio"> *</span></td><td><input type="text" id="txtPais" name="txtPais" value="<?php echo $pais ?>"></td>
                                <td>Código postal<span class="obligatorio"> *</span></td><td><input type="text" id="txtcp" name="txtcp" value="<?php echo $cp ?>"></td>
                            </tr>
                            <tr>
                                <td>Latitud<span class="obligatorio"> *</span></td><td><input type="number" id="Latitud" name="Latitud" value="<?php echo $latitud ?>" step="any"></td>
                                <td>Longitud<span class="obligatorio"> *</span></td><td><input type="number" id="Longitud" name="Longitud" value="<?php echo $longitud ?>" step="any"></td>
                                <td></td><td></td>
                            </tr>
                        </table>
                    </fieldset>
                </div>                
                <div id="divLocalidad">
                <?php 
                    $checked = "";
                    if($surtir == 1){
                        $checked = "checked";
                    }
                    echo "<input type = 'checkbox' name = 'surtir' id = 'surtir' $checked>Tratar como almacén propio";
                ?>
                    <table  id="TablaClienteFila" style="width:100%">                       
                        <tr>
                            <td style="width: 15%">Cliente<span class='obligatorio'> *</span></td>
                            <td>
                                <select class="cliente" id="cliente_<?php echo "0"; ?>" name="cliente_<?php echo "0"; ?>" style="width: 190px" onchange="mostraLocalidadAjaxCliente('<?php echo "0" ?>');" >
                                    <option value="0">Seleccione un cliente</option>
                                    <?php
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                    $array_clientes = array();
                                    while ($rs = mysql_fetch_array($query)) {
                                        $array_clientes[$rs['ClaveCliente']] = $rs['NombreRazonSocial'];
                                        $s = "";
                                        if ($grupo != "") {
                                            if ($grupo == $rs['ClaveCliente']) {
                                                $s = "selected";
                                            }
                                        } else {
                                            if ($cliente[0] == $rs['ClaveCliente']) {
                                                $s = "selected";
                                            }
                                        }
                                        echo "<option value='" . $rs['ClaveGrupo'] . "***" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <?php if ($showgrupo != "") { ?>
                                    <input type="checkbox" onclick="verificarCheckbox();" name="todoGrupo" id="todoGrupo" <?php echo $porGrupo; ?>/><label for="todoGrupo" id="lbltodoGrupo">Todo el grupo</label>
                                <?php } ?> 
                            </td>
                            <td>Localidad<span class='obligatorio'> *</span></td>
                            <td>
                                <select id="localidad0" name="localidad0[]" style="width: 190px" multiple="multiple" class="localidad">
                                    <?php
                                    if ($cliente[0] != "") {
                                        $catalogo1 = new Catalogo();
                                        $query1 = $catalogo1->obtenerLista("SELECT * FROM c_centrocosto cc WHERE cc.ClaveCliente='$cliente[0]'");
                                        while ($rs = mysql_fetch_array($query1)) {
                                            $s1 = "";
                                            $c1 = 0;
                                            while ($c1 < count($localidades)) {
                                                if ($localidades[$c1] == $rs['ClaveCentroCosto']) {
                                                    $s1 = "selected";
                                                }
                                                $c1++;
                                            }
                                            echo "<option value='" . $rs['ClaveCentroCosto'] . "' $s1>" . $rs['Nombre'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <br/>
                                <div id='mensajeError0'></div>
                            </td>
                            <td>
                                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='nuevoCliente();' style="float: right; cursor: pointer;" />
                            </td>
                        </tr>   
                        <?php
                        $ct = 1;
                        while ($ct < count($cliente)) {
                            $tamanoTabla++;
                            ?>
                            <tr id='filaCliente_<?php echo $ct; ?>'>
                                <td style="width: 15%">Cliente<span class='obligatorio'> *</span></td>
                                <td>
                                    <select class="cliente" id="cliente_<?php echo $ct; ?>" name="cliente_<?php echo $ct; ?>" style="width: 190px" onchange="mostraLocalidadAjaxCliente('<?php echo $ct ?>');" >
                                        <option value="0">Seleccione un cliente</option>
                                        <?php
                                        $catalogo = new Catalogo();
                                        $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");                                        
                                        while ($rs = mysql_fetch_array($query)) {                                            
                                            $s = "";
                                            if ($grupo != "") {
                                                if ($grupo == $rs['ClaveCliente']) {
                                                    $s = "selected";
                                                }
                                            } else {
                                                if ($cliente[$ct] == $rs['ClaveCliente']) {
                                                    $s = "selected";
                                                }
                                            }
                                            echo "<option value='" . $rs['ClaveGrupo'] . "***" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <?php if ($showgrupo != "") { ?>
                                        <input type="checkbox" onclick="verificarCheckbox();" name="todoGrupo" id="todoGrupo" <?php echo $porGrupo; ?>/><label for="todoGrupo" id="lbltodoGrupo">Todo el grupo</label>
                                    <?php } ?> 
                                </td>
                                <td>Localidad<span class='obligatorio'> *</span></td>
                                <td>
                                    <select id="localidad_<?php echo $ct; ?>" name="localidad_<?php echo $ct; ?>[]" style="width: 190px" multiple="multiple" class="localidad">
                                        <?php
                                        if ($cliente[$ct] != "") {
                                            $catalogo1 = new Catalogo();
                                            $query1 = $catalogo1->obtenerLista("SELECT * FROM c_centrocosto cc WHERE cc.ClaveCliente='$cliente[$ct]'");
                                            while ($rs = mysql_fetch_array($query1)) {
                                                $s1 = "";
                                                $c1 = 0;
                                                while ($c1 < count($localidades)) {
                                                    if ($localidades[$c1] == $rs['ClaveCentroCosto']) {
                                                        $s1 = "selected";
                                                    }
                                                    $c1++;
                                                }
                                                echo "<option value='" . $rs['ClaveCentroCosto'] . "' $s1>" . $rs['Nombre'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar fila' onclick='deleteRowCliente(<?php echo $ct ?>)' style='float: right; cursor: pointer;' />
                                </td>
                            </tr>  
                            <?php
                            $ct++;
                        }
                        ?>
                    </table>
                    <?php if($tipoAlmacen == "0"){ ?>
                        <input type="button" name="resurtir" id="resurtir" value="Crear ticket de resurtido" class="boton" onclick="generarTicketResurtido('<?php echo $idAlmacen; ?>');return false;"/>
                    <?php } ?>
                </div>  
                <!--<div id="div_clientes_propios" style="display: none;">
                    <table style="width: 60%;">
                        <tr>
                            <td><label for="clientes_propios">Clientes</label></td>
                            <td>
                                <select id="clientes_propios" name="clientes_propios[]" style="width: 190px" multiple="multiple" class="localidad">
                                    <?php
                                        /*foreach ($array_clientes as $key => $value) {
                                            echo "<option value='$key'>$value</option>";
                                        }*/
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>-->
                
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');return false;"/>
                <?php
                echo "<input type='hidden' id='idAlmacen' name='idAlmacen' value='" . $idAlmacen . "'/> ";
                echo "<input type='hidden' id='tamano' name='tamano' value='" . $tamanoTabla . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>
