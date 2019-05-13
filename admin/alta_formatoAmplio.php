<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Arrendamiento.class.php");
include_once("../WEB-INF/Classes/ServicioFA.class.php");
$pagina_lista = "admin/lista_formatoAmplio.php";
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
    <body>
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
        <form id="formFormatoAmpl" name="formFormatoAmpl" action="/" method="POST">
            <div class="container-fluid">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label  for="nombre" class="m-0">Nombre de esquema</label>
                        <input class="form-control" type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/>
                    </div>
                    <div class="form-group  col-md-4">
                        <label  for="tipo" class="m-0">Tipo</label>
                        <input class="form-control" type="text" id="tipo" name="tipo" value="<?php echo $tipo; ?>"/></label>
                    </div>
                    <div class="form-group  col-md-4">
                        <label  for="servicios" class="m-0">Servicios que ocupan este arrendamiento</label>                      
                        <select class="form-control" class="multiple" id="servicios" name="servicios[]" multiple="multiple">
                            <?php
                                $servicio = new ServicioFA();
                                $result = $servicio->getServiciosFA();
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if(in_array($rs['IdServico'], $servicios)){
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='".$rs['IdServico']."' $s>".$rs['Nombre']." (".$rs['tipo'].") </option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="rentaMensual" id="rentaMensual" <?php echo $renta; ?>>
                            <label class="custom-control-label" for="rentaMensual">jlp</label><br>
                        </div>

                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="incluidaBN" id="incluidaBN" <?php echo $incluidoBN; ?>>
                            <label class="custom-control-label" for="incluidaBN">ML Páginas incluidas B/N</label><br>
                        </div>

                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="incluidaColor" id="incluidaColor" <?php echo $incluidoColor; ?>
                            >
                            <label class="custom-control-label" for="incluidaColor">ML Páginas incluidas color</label><br>
                        </div>

                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="excedenteBN" id="excedenteBN" <?php echo $excedenteBN; ?>
                            >
                            <label class="custom-control-label" for="excedenteBN">ML Página excedente  B/N </label><br>
                        </div>

                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="excedenteColor" id="excedenteColor" <?php echo $excedenteColor; ?>
                            >
                            <label class="custom-control-label" for="excedenteColor">ML Página excedente color</label><br>
                        </div>
                        
                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="costoBN" id="costoBN" <?php echo $costoBN; ?>
                            >
                            <label class="custom-control-label" for="costoBN">Costo por ML página procesada B/N</label><br>
                        </div>

                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="costoColor" id="costoColor" <?php echo $costoColor; ?>
                            >
                            <label class="custom-control-label" for="costoColor"> Costo por ML página procesada color</label><br>
                        </div>

                        <div class="custom-control custom-checkbox mt-2">
                            <input class="custom-control-input" type="checkbox" name="activo" id="activo" <?php echo $activo; ?>
                            >
                            <label class="custom-control-label" for="activo">Activo</label><br>
                        </div>
                    </div>    
                </div>
                <input type="submit" class="button btn btn-lang btn-block btn-success mt-3 mb-3" value="Guardar" />
                <input type="submit" class="button btn btn-lang btn-block btn-danger mt-3 mb-3" value="Cancelar" onclick="cambiarContenidosArrend('<?php echo $pagina_lista; ?>', '<?php echo $_POST['id2']; ?>');return false;"/>
                <?php
                    echo "<input type='hidden' id='idM' name='idM' value='" . $_POST['id2'] . "'/> ";
                    if (isset($_POST['id'])) $id2 = $_POST['id'];
                    echo "<input type='hidden' id='idA' name='idA' value='" . $id2 . "'/> ";
                ?>
            </div>
        </form>
        <script type="text/javascript" src="resources/js/paginas/resumen.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_formatoAmplio.js"></script>  
    </body>
</html>