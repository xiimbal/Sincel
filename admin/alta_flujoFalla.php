<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Estado.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$id = "";
$estado = "";
$mostrarClientes = "";
$mostrarContactos = "";
$orden = "";
$area = "";
$idFlujo = "";
$activo = "checked='checked'";
$idEstado = "";
$idEstadoTicket = "";
$pagina_lista = "admin/lista_flujoFalla.php";
$flujos = array();
$numeroEscalamientos = 0;
$FlValidacion = "";
$FlCobrar = "";
$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_flujoFalla.js"></script>        
    </head>
    <body>
        <div class="principal" id="main">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Estado();
                $obj->getRegistroById($_POST['id']);
                $id = $obj->getIdEstado();
                $estado = $obj->getNombre();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                $mostrarClientes = $obj->getMostrarClientes() + 0;
                $mostrarContactos = $obj->getMostrarContactos() + 0;
                if($obj->getFlagValidacion() == "1"){
                    $FlValidacion = "checked";
                }if($obj->getFlagCobrar() == "1"){
                    $FlCobrar = "checked";
                }
                $area = $obj->getArea();
                $idEstado = $obj->getIdEstado();
                $orden = $obj->getOrdenFlujo();
                $idEstadoTicket = $obj->getIdEstadoTicket();
                $flujos = $obj->getFlujos();
                //Obtenemos los escalamientos de el estado
                $obtenerEscalamientos = "SELECT * from c_escalamientoEstado WHERE idEstado = $id";
                $result = $catalogo->obtenerLista($obtenerEscalamientos);
                $numeroEscalamientos = mysql_num_rows($result);
            }
            ?>

            <form id="formFlujoFalla" name="formFlujoFalla" action="/" method="POST">
                <table style="width: 70%">
                    <tr>
                        <td><label for="estado">Estado</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="estado" name="estado" value="<?php echo $estado; ?>"/>                            
                        </td>
                        <td><label for="orden"></label>Orden<span class="obligatorio"> *</span></td>
                        <td><input type="text" id="orden" name="orden" value="<?php echo $orden; ?>"/></td> 
                        <td>
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo
                        </td>
                    </tr>
                    <tr>
                        <td><label for="area">Área</label></td>
                        <td>
                            <select id="area" name="area">
                                <?php
                                $query = $catalogo->getListaAlta("c_area", "Descripcion");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($area != "" && $area == $rs['IdArea']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdArea'] . " " . $s . ">" . $rs['Descripcion'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>Pantallas</td>
                        <td>
                            <select id="flujos_estado" name="flujos_estado[]" multiple="multiple" class="multiselect">
                                <?php
                                    $query = $catalogo->getListaAltaTodo("c_flujo", "Nombre");
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if(in_array($rs['IdFlujo'], $flujos)){
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value=" . $rs['IdFlujo'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td><label for="estadoTicket">Asociar a estado ticket</label></td>
                        <td>
                            <select id="estadoTicket" name="estadoTicket">
                                <?php
                                $queryET = $catalogo->obtenerLista("SELECT et.IdEstadoTicket, et.Nombre FROM c_estadoticket et 
                                        WHERE et.IdEstadoTicket 
                                        NOT IN(Select (CASE WHEN e.IdEstadoTicket != 'NULL' THEN e.IdEstadoTicket ELSE '' END) FROM c_estado e)");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rsET = mysql_fetch_array($queryET)) {
                                    echo "<option value=" . $rsET['IdEstadoTicket'] . " >" . $rsET['Nombre'] . "</option>";
                                }
                                if(isset($idEstadoTicket) && $idEstadoTicket != ""){
                                    $resultEst = $catalogo->obtenerLista("SELECT Nombre FROM c_estadoticket WHERE IdEstadoTicket = $idEstadoTicket");
                                    if($rsEst = mysql_fetch_array($resultEst)){
                                        echo "<option value = ".$idEstadoTicket." selected >".$rsEst['Nombre']."</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Mostrar correos de: </td>
                        <td>
                            <?php 
                                $checked = "";
                                if($mostrarClientes){
                                    $checked = "checked";
                                }
                            ?>
                            <input type="checkbox" name="clientes" id="clientes" value="Clientes" <?php echo $checked; ?>>Clientes
                        </td>
                            <?php
                                $checked = "";
                                if($mostrarContactos){
                                    $checked = "checked";
                                }
                            ?>
                        <td>
                            <input type="checkbox" name="contactos" id="contactos" value="Contactos" <?php echo $checked; ?>>Contactos
                        </td>
                        <td>
                            <input type="checkbox" name="flagValidacion" id="flagValidacion" <?php echo $FlValidacion?>>Validar
                            <input type="checkbox" name="flagCobrar" id="flagCobrar" <?php echo $FlCobrar?>>Cobrar
                        </td>
                        <td></td>
                    </tr>
                </table>
                <?php if($numeroEscalamientos == 0){ ?>
                <br/><br/>
                <h2 class="titulos">Escalamiento</h2>
                <div>
                    <table style="width: 70%" id="tescalamiento">
                    <tr id="escalamiento_0">
                    <td><table>
                        <tr>
                            <td>Tiempo de envio<span class="obligatorio"> *</span></td>
                            <td><input type="text" name="tiempoEnvio_0" id="tiempoEnvio_0"></td>
                            <td><label for="color">Color<span class="obligatorio"> *</span></label></td>
                            <td><input type="text" name="color_0" id="color_0"></td>
                            <td><img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='agregarEscalamiento();return false;' style="float: right; cursor: pointer;" /></td>
                        </tr>
                        <tr>
                            <td>Prioridad <span class="obligatorio"> *</span></td>
                            <td>
                                <select id="prioridad_0" name="prioridad_0">
                                    <option value=''>Seleccione una prioridad</option>
                                <?php
                                    $resultPrioridad = $catalogo->getListaAlta("c_prioridadticket", "Prioridad");
                                    while($rsPrioridad = mysql_fetch_array($resultPrioridad)){
                                        echo "<option value = '".$rsPrioridad['Prioridad']."'>".$rsPrioridad['Prioridad']."</option>";
                                    }
                                ?>
                                </select>
                            </td>
                            <td>Correos <span class="obligatorio"> *</span></td>
                            <td>
                                <select id="correos_0" name="correos_0[]" multiple="multiple" class="multiselect" style="width: 150px">
                                    <?php
                                        $queryUsuario = $catalogo->getListaAlta("c_usuario", "Nombre");
                                        while ($rsUsuario = mysql_fetch_array($queryUsuario)) {
                                            if((!empty($rsUsuario['correo'])))
                                            {    
                                                $s = "";
                                                echo "<option value= 'us" . $rsUsuario['IdUsuario'] . "' " . $s . ">" .$rsUsuario['Loggin']."-". $rsUsuario['correo'] . "</option>";
                                            }
                                        }
                                        echo "<option value = 'tfs'>Enviar a usuarios TFS </option>";
                                        if($mostrarClientes){
                                            echo "<option value= 'cl' >Correos envío facturacion cliente</option>";
                                        }
                                        if($mostrarContactos){
                                            $query = $catalogo->getListaAlta("c_tipocontacto", "Nombre");
                                            while ($rs = mysql_fetch_array($query)) {
                                                $s = "";
                                                echo "<option value= 'co" . $rs['IdTipoContacto'] . "' " . $s . ">" . $rs['Nombre'] . "</option>";
                                            }
                                        }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Mensaje</td>
                            <td>
                                <textarea id="mensaje_0" name="mensaje_0" rows="10" cols="50"></textarea>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>
                    </table>
                </div>
                <?php }else{
                        $c = 0;
                        while($rsEs = mysql_fetch_array($result)){ ?>
                <br/><br/>
                <h2 class="titulos">Escalamiento</h2>
                <div>
                    <table style="width: 70%" id="tescalamiento">
                    <?php echo "<tr id='escalamiento_$c'>"; ?>
                    <td><table>
                        <tr>
                            <td>Tiempo de envio <span class="obligatorio"> *</span></td>
                            <td>
                            <?php echo "<input type='text' name='tiempoEnvio_$c' id='tiempoEnvio_$c' value=".$rsEs['tiempoEnvio'].">"; ?>
                            </td>
                            <td><label for="color">Color <span class="obligatorio"> *</span></label></td>
                            <td>
                            <?php echo "<input type='text' name='color_$c' id='color_$c' value=".$rsEs['color'].">"; ?>
                            </td>
                            <td><img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='agregarEscalamiento();return false;' style="float: right; cursor: pointer;" /></td>
                            <td><img class="imagenMouse" src="resources/images/Erase.png" title="Borrar" onclick='eliminarEscalamiento(<?php echo $c; ?>);return false;' style="float: right; cursor: pointer;" /></td>
                        </tr>
                        <tr>
                            <td>Prioridad <span class="obligatorio"> *</span></td>
                            <td>
                                <?php
                                    echo "<select id='prioridad_$c' name='prioridad_$c' required='required'>";
                                    echo "<option value=''>Seleccione una prioridad</option>";
                                    $resultPrioridad = $catalogo->getListaAlta("c_prioridadticket","Prioridad");
                                    while($rsPrioridad = mysql_fetch_array($resultPrioridad)){
                                        $s = "";
                                        if($rsEs['prioridad'] == $rsPrioridad['Prioridad']){
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value = '".$rsPrioridad['Prioridad']."' $s>".$rsPrioridad['Prioridad']."</option>";
                                    }
                                    echo "</select>";
                                ?>
                            </td>
                            <td>Correos <span class="obligatorio"> *</span></td>
                            <td>
                                <?php
                                $queryCorreos = "SELECT correo FROM c_escalamientoCorreo WHERE idEscalamiento = ".$rsEs['idEscalamiento'];
                                $resultCorreo = $catalogo->obtenerLista($queryCorreos);
                                $correosArray = array();
                                while($rsCorreo = mysql_fetch_array($resultCorreo)){
                                    array_push($correosArray, $rsCorreo['correo']);
                                }
                                echo "<select id='correos_$c' name='correos_$c"."[]' multiple='multiple' class='multiselect' style='width: 150px'>";
                                    $queryUsuario = $catalogo->getListaAlta("c_usuario", "Nombre");
                                    while ($rsUsuario = mysql_fetch_array($queryUsuario)) {
                                        if((!empty($rsUsuario['correo'])))
                                        {    
                                            $s = "";
                                            if(in_array("us".$rsUsuario['IdUsuario'], $correosArray)){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value= 'us" . $rsUsuario['IdUsuario'] . "' " . $s . ">" .$rsUsuario['Loggin']."-". $rsUsuario['correo'] . "</option>";
                                        }
                                    }
                                    $s = "";
                                    if(in_array("tfs", $correosArray)){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value = 'tfs' $s>Enviar a usuarios TFS </option>";
                                    if($mostrarClientes){
                                        $s = "";
                                        if(in_array("cl", $correosArray)){
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value= 'cl' $s >Correos envío facturacion cliente</option>";
                                    }
                                    if($mostrarContactos){
                                        $query = $catalogo->getListaAlta("c_tipocontacto", "Nombre");
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if(in_array("co".$rs['IdTipoContacto'] , $correosArray)){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value= 'co" . $rs['IdTipoContacto'] . "' " . $s . ">" . $rs['Nombre'] . "</option>";
                                        }
                                    }
                                ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Mensaje</td>
                            <td>
                                <?php echo"<textarea id='mensaje_$c' name='mensaje_$c' rows='10' cols='50'>".$rsEs['mensaje']."</textarea>"; ?>
                            </td>
                        </tr>
                        </table>
                    </td>
                    </tr>
                    </table>
                </div>
                <?php $c++; }
                }   ?>
                <br/><br/>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                       <?php
                       if($numeroEscalamientos > 0) $numeroEscalamientos--;
                       echo "<input type='hidden' id='id' name='id' value='" . $idEstado . "'/> ";                       
                       echo "<input type='hidden' id='flujo' name='flujo' value='" . $idFlujo . "'/> ";
                       echo "<input type='hidden' id='tipo' name='tipo' value='" . $_GET['tipo'] . "'/> ";
                       echo "<input type='hidden' id='numeroEscalamientos' name = 'numeroEscalamientos' value = '$numeroEscalamientos' />";
                       ?>
            </form>
        </div>
    </body>
</html>