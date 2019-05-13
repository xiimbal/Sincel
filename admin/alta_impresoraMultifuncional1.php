<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Arrendamiento.class.php");
include_once("../WEB-INF/Classes/ServicioIM.class.php");

$pagina_lista = "admin/lista_Impresorasmultifuncionales.php";
$idArrendamiento = "";
$idModalidad = "";
$id2 = "";
$nombre = "";
$tipo = "";
$renta = "checked='checked'";
$incluidoBN = "checked='checked'";
$incluidoColor = "checked='checked'";
$excedenteBN = "checked='checked'";
$excedenteColor = "checked='checked'";
$costoBN = "checked='checked'";
$costoColor = "checked='checked'";
$activo = "checked='checked'";
$read = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_ImpresoraMultifuncionales.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new Arrendamiento();
            if (isset($_POST['id']) && isset($_POST['id2'])) {
                $obj->setIdArrendamiento($_POST['id']);
                $obj->getRegistroById($_POST['id'], $_POST['id2']);
                $read = "readonly='readonly'";
                $idArrendamiento = $obj->getIdArrendamiento();
                $idModalidad = $obj->getIdModalidad();
                $nombre = $obj->getNombre();
                $tipo = $obj->getTipo();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                if ($obj->getIncluidoBN() == "0") {
                    $incluidoBN = "";
                }
                if ($obj->getIncluidoColor() == "0") {
                    $incluidoColor = "";
                }
                if ($obj->getExcedenteBN() == "0") {
                    $excedenteBN = "";
                }
                if ($obj->getExcedenteColor() == "0") {
                    $excedenteColor = "";
                }
                if ($obj->getCostoBN() == "0") {
                    $costoBN = "";
                }
                if ($obj->getCostoColor() == "0") {
                    $costoColor = "";
                }
                if ($obj->getRenta() == "0") {
                    $renta = "";
                }
                /*Obtenemos los servicios que ya estan asociados a este arrendamiento*/
                $servicios = $obj->getServiciosByArrendamiento();
            }
            ?>
            <form id="formImprMult" name="formImprMult" action="/" method="POST">
                <table>
                    <tr>
                        <td><label for="nombre">Nombre de esquema</label></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>
                        <td colspan="2">
                            <label for="tipo">Tipo</label>&nbsp;<input type="text" id="tipo" name="tipo" value="<?php echo $tipo; ?>" style="width: 350px;"/>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="servicios">Servicios que ocupan este arrendamiento</label></td>
                        <td colspan="3">                            
                            <select class="multiple" id="servicios" name="servicios[]" multiple="multiple">
                                <?php
                                    $servicio = new ServicioIM();
                                    $result = $servicio->getServiciosImpresion();
                                    while($rs = mysql_fetch_array($result)){
                                        $s = "";
                                        if(in_array($rs['IdServico'], $servicios)){
                                            $s = "selected = 'selected'";
                                        }
                                        echo "<option value='".$rs['IdServico']."' $s>".$rs['Nombre']." (".$rs['tipo'].") </option>";
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="rentaMensual" id="rentaMensual" <?php echo $renta; ?>/>Renta mensual</td>
                        <td><input type="checkbox" name="incluidaBN" id="incluidaBN" <?php echo $incluidoBN; ?>/>Páginas incluidas B/N</td>
                        <td><input type="checkbox" name="incluidaColor" id="incluidaColor" <?php echo $incluidoColor; ?>/>Páginas incluidas color</td>
                        <td><input type="checkbox" name="excedenteBN" id="excedenteBN" <?php echo $excedenteBN; ?>/>Página excedente  B/N </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="excedenteColor" id="excedenteColor" <?php echo $excedenteColor; ?>/>Página excedente color</td>
                        <td><input type="checkbox" name="costoBN" id="costoBN" <?php echo $costoBN; ?>/>Costo por página procesada B/N</td>
                        <td><input type="checkbox" name="costoColor" id="costoColor" <?php echo $costoColor; ?>/>Costo por página procesada color</td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidosArrend('<?php echo $pagina_lista; ?>', '<?php echo $_POST['id2']; ?>');
                return false;"/>
                       <?php
                       echo "<input type='hidden' id='idM' name='idM' value='" . $_POST['id2'] . "'/> ";
                       if (isset($_POST['id']))
                           $id2 = $_POST['id'];
                       echo "<input type='hidden' id='idA' name='idA' value='" . $id2 . "'/> ";
                       ?>
            </form>
        </div>
    </body>
</html>
