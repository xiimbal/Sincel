<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Financial.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$permisos_grid = new PermisosSubMenu();
$pagina_lista = "catalogos/lista_financial.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $pagina_lista);

$id = "";
$Fecha = date('Y')."-".date('m')."-".date('d');
$IdOperador = '';
$Comentario = '';
$IdEstatus = '1';
$IdTipoRetencion = '';
$PorcentajeInteres = '';
$activo = "checked='checked'";

$catalogo = new Catalogo();
$conceptos = array();
$result_c = $catalogo->getListaAlta("c_conceptofinancial", "Concepto");
while ($rs = mysql_fetch_array($result_c)) {
    $conceptos[$rs['IdConcepto']] = $rs['Concepto'];
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_financial.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Financial();
                if ($obj->getRegistroById($_POST['id'])) {
                    $id = $_POST['id'];
                    $Fecha = $obj->getFecha();
                    $IdOperador = $obj->getIdOperador();
                    $Comentario = $obj->getComentario();                    
                    $IdEstatus = $obj->getIdEstatus();
                    $IdTipoRetencion = $obj->getIdTipoRetencion();
                    $PorcentajeInteres = $obj->getPorcentajeInteres();
                    if ($obj->getActivo() == 0) {
                        $activo = "";
                    }
                }
            }
            ?>
            <form id="formFinancial" name="formFinancial" action="/" method="POST">
                <div class="ui-state-highlight ui-corner-all aviso">
                    <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
                        <strong></strong> Campos requeridos <span>*</span></p>
                </div>
                <table style="min-width: 90%;">
                    <tr>
                        <td><label for="Fecha">Fecha del préstamo</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="Fecha" name="Fecha" value="<?php echo $Fecha; ?>" class="fecha" required="required"/></td>
                        <td><label for="IdOperador">Chofer</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="IdOperador" name="IdOperador" required="required" style="width: 250px;">
                                <option value="">Selecciona el chofer</option>
                                <?php
                                    $consulta = ("SELECT usu.IdUsuario, Loggin, CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',ApellidoMaterno) AS nombre_completo,
                                    correo, per.Nombre AS puesto,(SELECT CASE WHEN usu.Activo = 1 THEN 'Activo' ELSE 'Inactivo' END) AS Activo,
                                    CONCAT (cv.Placas,' - ',cv.Modelo) AS Vehiculo
                                    FROM c_usuario AS usu 
                                    LEFT JOIN c_puesto AS per ON per.IdPuesto = usu.IdPuesto 
                                    LEFT JOIN c_domicilio_usturno AS cd ON cd.IdUsuario=usu.IdUsuario 
                                    LEFT JOIN c_vehiculo AS cv ON cd.IdVehiculo=cv.IdVehiculo 
                                    WHERE usu.IdPuesto=101 OR usu.IdPuesto=108 OR usu.IdPuesto=109  AND usu.Activo = 1
                                    ORDER BY nombre_completo ASC;");
                                    $result = $catalogo->obtenerLista($consulta);
                                    while($rs = mysql_fetch_array($result)){
                                        $s = "";
                                        if($rs['IdUsuario'] == $IdOperador){
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='".$rs['IdUsuario']."' $s>".$rs['nombre_completo']."</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td><label for="PorcentajeInteres">Porcentaje de interés</label><span class="obligatorio"> *</span></td>
                        <td><input type="number" id="PorcentajeInteres" name="PorcentajeInteres" value="<?php echo $PorcentajeInteres; ?>" required="required" onblur="calcularInteresInicial();"/></td>
                    </tr>    
                    <tr>
                        <td><label for="IdEstatus">Estatus</label></td>
                        <td>
                            <select id="IdEstatus1" name="IdEstatus1" required="required" disabled="disabled">
                                <?php
                                    $result = $catalogo->getListaAlta("c_estatusfinancial", "Estatus");
                                    while($rs = mysql_fetch_array($result)){
                                        $s = "";
                                        if($rs['IdEstatus'] == $IdEstatus || empty($IdEstatus)){
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='".$rs['IdEstatus']."' $s>".$rs['Estatus']."</option>";
                                    }
                                    if(empty($IdEstatus)){
                                        $IdEstatus = 1;
                                    }
                                    echo "<input type='hidden' id='IdEstatus' name='IdEstatus' value='$IdEstatus' />";
                                ?>                                
                            </select>
                        </td>
                        <td><label for="IdTipoRetencion">Tipo de retención</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="IdTipoRetencion" name="IdTipoRetencion" required="required">
                                <option value="">Selecciona el tipo</option>
                                <?php
                                    $result = $catalogo->getListaAlta("c_tiporetencion", "TipoRetencion");
                                    while($rs = mysql_fetch_array($result)){
                                        $s = "";
                                        if($rs['IdTipoRetencion'] == $IdTipoRetencion){
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='".$rs['IdTipoRetencion']."' $s>".$rs['TipoRetencion']."</option>";
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <textarea style='resize: none; width: 500px;' rows='4' id="Comentario" name="Comentario"><?php echo $Comentario; ?></textarea>
                        </td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>
                </table>
                
                <fieldset>
                    <legend>Conceptos</legend>
                    <?php                        
                        $monto_inicial = "";
                        $fecha_inicial = date('Y')."-".date('m')."-".date('d');
                        $comentario_inicial = "";
                        $id_detalle_inicial = "";
                        if(!empty($id)){
                            $consulta = "SELECT * FROM k_financial WHERE IdFinancial = $id AND IdConcepto = 1;";
                            $result = $catalogo->obtenerLista($consulta);
                            while($rs = mysql_fetch_array($result)){
                                $monto_inicial = $rs['Importe'];
                                $fecha_inicial = $rs['Fecha'];
                                $comentario_inicial = $rs['Comentario'];
                                $id_detalle_inicial = $rs['IdDetalleFinancial'];
                            }
                        }
                    ?>
                    <table id="tabla_detalles" style="width: 80%;">
                        <tr id='fila_detalle_0'>
                            <td>
                                <table>
                                    <tr>
                                        <td>Concepto<span class="obligatorio"> *</span></td>
                                        <td>
                                            <select id="concepto_0" name="concepto_0">
                                                <?php
                                                    $consulta = "SELECT IdConcepto, Concepto FROM `c_conceptofinancial` WHERE IdConcepto = 1;";
                                                    $result = $catalogo->obtenerLista($consulta);
                                                    while($rs = mysql_fetch_array($result)){
                                                        echo "<option value='".$rs['IdConcepto']."'>".$rs['Concepto']."</option>";
                                                    }
                                                ?>
                                            </select>
                                        </td>
                                        <td>Monto<span class="obligatorio"> *</span></td>
                                        <td><input type="number" id="monto_0" name="monto_0" step="any" value="<?php echo $monto_inicial; ?>" onblur="calcularInteresInicial();"/></td>
                                        <td>Fecha<span class="obligatorio"> *</span></td>
                                        <td><input type="text" id="fecha_0" name="fecha_0" class="fecha" value="<?php echo $fecha_inicial; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6">
                                            <textarea id="comentario_0" name="comentario_0" style="resize: none; width: 500px;" rows="4"><?php echo $comentario_inicial; ?></textarea>
                                            <input type="hidden" id="id_0" name="id_0" value="<?php echo $id_detalle_inicial; ?>"/>
                                        </td>
                                    </tr>
                                </table> 
                            </td>
                        </tr>
                        <?php
                        $result = null;
                        $contador = 1;
                        if (!empty($id)) {
                            $result = $catalogo->obtenerLista("SELECT * FROM k_financial WHERE IdFinancial = $id AND IdConcepto <> 1;");
                        }
                        if ($result != null && mysql_num_rows($result) > 0) {
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr id='fila_detalle_" . $contador . "'>"
                                    . "<td>"
                                        . "<table>
                                            <tr>
                                                <td>Concepto<span class='obligatorio'> *</span></td>
                                                <td>
                                                    <select id='concepto_$contador' name='concepto_$contador'>";  
                                                    foreach ($conceptos as $key => $value) {
                                                        $s = "";
                                                        if($key == $rs['IdConcepto']){
                                                            $s = "selected='selected'";
                                                        }
                                                        echo "<option value='$key' $s>$value</option>";
                                                    }
                                                    echo "</select>
                                                </td>
                                                <td>Monto<span class='obligatorio'> *</span></td>
                                                <td><input type='number' id='monto_$contador' name='monto_$contador' step='any' value='".$rs['Importe']."'/></td>
                                                <td>Fecha<span class='obligatorio'> *</span></td>
                                                <td><input type='text' id='fecha_$contador' name='fecha_$contador' class='fecha' value='".$rs['Fecha']."'/></td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'>
                                                    <textarea id='comentario_$contador' name='comentario_$contador' style='resize: none; width: 500px;' rows='4'>".$rs['Comentario']."</textarea>
                                                    <input type='hidden' id='id_$contador' name='id_$contador' value='".$rs['IdDetalleFinancial']."'/>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                    <a href='#' id='add_" . $contador . "' onclick='agregarDetalle(); return false;' title='Agrega otro detalle'><img src='resources/images/add.png' title='Nuevo'/></a>";
                                    if ($contador > 1) {
                                        echo "</td><td><a href='#' id='delete_" . $contador . "' onclick='eliminarDetalle(" . $contador . "); return false;' title='Elimina este detalle'><img src='resources/images/Erase.png'/></a>";
                                    }
                                    echo "</td>"
                                . "</tr>";
                                $contador++;
                            }
                        } else {
                            echo "<tr id='fila_detalle_" . $contador . "'>"
                                    . "<td>"
                                        . "<table>
                                            <tr>
                                                <td>Concepto<span class='obligatorio'> *</span></td>
                                                <td>
                                                    <select id='concepto_$contador' name='concepto_$contador'>";  
                                                    foreach ($conceptos as $key => $value) {  
                                                        $s = "";
                                                        if($key == "2"){
                                                            $s = "selected='selected'";
                                                        }
                                                        echo "<option value='$key' $s>$value</option>";
                                                    }
                                                    echo "</select>
                                                </td>
                                                <td>Monto<span class='obligatorio'> *</span></td>
                                                <td><input type='number' id='monto_$contador' name='monto_$contador' step='any' /></td>
                                                <td>Fecha<span class='obligatorio'> *</span></td>
                                                <td><input type='text' id='fecha_$contador' name='fecha_$contador' class='fecha' /></td>
                                            </tr>
                                            <tr>
                                                <td colspan='6'>
                                                    <textarea id='comentario_$contador' name='comentario_$contador' style='resize: none; width: 500px;' rows='4'></textarea>
                                                    <input type='hidden' id='id_$contador' name='id_$contador' value=''/>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                    <a href='#' id='add_" . $contador . "' onclick='agregarDetalle(); return false;' title='Agrega otro detalle'><img src='resources/images/add.png' title='Nuevo'/></a>";
                                    if ($contador > 1) {
                                        echo "</td><td><a href='#' id='delete_" . $contador . "' onclick='eliminarDetalle(" . $contador . "); return false;' title='Elimina este detalle'><img src='resources/images/Erase.png'/></a>";
                                    }
                                    echo "</td>"
                                . "</tr>";
                                $contador++;
                        }
                        ?>
                    </table>
                    <input type="hidden" id="TotalDetalles" name="TotalDetalles" value="<?php echo $contador; ?>"/>
                </fieldset>
                
                <br/>
                <input type="submit" class="boton" id="guardar_financial" value="Guardar" />
                <input type="submit" class="boton" value="Regresar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');return false;"/>
                <?php
                echo "<input type='hidden' id='id' name='id' value='" . $id . "'/> ";                       
                ?>
            </form>
        </div>
    </body>
</html>
