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

        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">

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
              <div class="container-fluid">

                <!--Nombre de esquema-->
                    <div class="form-row">
                        <div class="form-group col-md-4">
                        <label  for="nombre">Nombre de esquema</label>
                        <input class="form-control" type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/>
                        </div>

                    <!--Tipo-->
                       <div class="form-group  col-md-4">
                            <label for="tipo">Tipo</label>
                            <input class="form-control" type="text" id="tipo" name="tipo" value="<?php echo $tipo; ?>"/>
                       </div>    
                    
                    <!--Servicios que ocupan este arrendamiento-->
                    <div class="form-group  col-md-4">
                        <label for="servicios">Servicios que ocupan este arrendamiento</label>                
                            <select class="form-control"class="multiple" id="servicios" name="servicios[]" multiple="multiple">
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
                    </div>

                <!--Botones de Selección-->
               <div class="col-md-4">
                    <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input"  type="checkbox" name="rentaMensual" id="rentaMensual" 
                        <?php echo $renta; ?>>
                        <label class="custom-control-label"  for="rentaMensual">Renta mensual</label><br>
                  </div>

                      <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input" type="checkbox" name="incluidaBN" id="incluidaBN" <?php echo $incluidoBN; ?>
                        >
                        <label class="custom-control-label" for="incluidaBN">Páginas incluidas B/N</label><br>
                    </div>
               
                    <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input"  type="checkbox" name="incluidaColor" id="incluidaColor" <?php echo $incluidoColor; ?>
                        >
                        <label class="custom-control-label" for="incluidaColor">Páginas incluidas color</label><br>
                    </div>
                  
                    <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input" type="checkbox" name="excedenteBN" id="excedenteBN" <?php echo $excedenteBN; ?>
                        >
                        <lavel class="custom-control-label" for="excedenteBN">Página excedente  B/N</lavel><br>
                    </div>
                       
                    
                    <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input" type="checkbox" name="excedenteColor" id="excedenteColor" <?php echo $excedenteColor; ?>
                        >
                        <label class="custom-control-label" for="excedenteColor">Página excedente color</label><br>
                    </div>    
                   

                    <div class="custom-control custom-checkbox mt-2">
                        <input  class="custom-control-input" type="checkbox" name="costoBN" id="costoBN" <?php echo $costoBN; ?>
                        >
                        <label class="custom-control-label" for="costoBN">Costo por página procesada B/N</label><br>
                    </div>

                    <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input" type="checkbox" name="costoColor" id="costoColor" <?php echo $costoColor; ?>
                        >
                        <label class="custom-control-label" for="costoColor">Costo por página procesada color</label><br>  
                    </div>

                    <div class="custom-control custom-checkbox mt-2">
                        <input class="custom-control-input" type="checkbox" name="activo" id="activo" <?php echo $activo; ?>>
                        <label class="custom-control-label" for="activo">Activo</label><br>
                    </div>
                </div>
                
         
            <!--Botones-->
           
                <input type="submit" class="button btn btn-lang btn-block btn-outline-success mt-3 mb-3" value="Guardar" />
                <input type="submit" class="button btn btn-lang btn-block btn-outline-danger mt-3 mb-3" value="Cancelar" onclick="cambiarContenidosArrend('<?php echo $pagina_lista; ?>', '<?php echo $_POST['id2']; ?>');
                return false;"/>
                       <?php
                       echo "<input type='hidden' id='idM' name='idM' value='" . $_POST['id2'] . "'/> ";
                       if (isset($_POST['id']))
                           $id2 = $_POST['id'];
                       echo "<input type='hidden' id='idA' name='idA' value='" . $id2 . "'/> ";
                       ?>
                   

               </div> 
           </div>
            </form>
        </div>
</body>
</html>
