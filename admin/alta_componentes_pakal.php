<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Componente.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$menu = new Menu();
if($menu->getSubmenuById(70)){
    $nombre_menu = $menu->getNom_sub();
}else{
    $nombre_menu = "Componentes";
}
$permisos_grid = new PermisosSubMenu();
$nombre_modelo = $permisos_grid->getModeloSistema();
$NoParte = $permisos_grid->getNoParteSistema();
$Tipo = $permisos_grid->getTipoSistema();

$pagina_lista = "admin/lista_componentes_pakal.php";
$pagina_ComponenteDetalle = ""; //"admin/lista_componentesCompatiblesEq.php";

$numero = "";
$read = "";
$imagen = "";
$nombre = "";
$descripcion = "";
$precio = "";
$tipo = "";
$rendimiento = "";
$activo = "checked='checked'";
$oculto = "";
$onlyRead = "";
$visible = "style='display:none;'";
$idEquipo = "";
$parteAnterior = "";
$color = "";
$stiloDiv = "style = 'display:none;'";
?>
<!DOCTYPE html>
<html lang="es">
    <head>                              
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_componentes_pakal.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_equipo_pakal.js"></script>
    </head>
    <body>
        <div class="principal"> 
            <?php
            if (isset($_POST['id'])) {
                $obj = new Componente();

                $obj->getRegistroById($_POST['id']);
                if (isset($_POST['copiar']) && $_POST['copiar'] == TRUE)
                    $read = "";
                else {
                    $read = "readonly='readonly'";
                    $numero = $obj->getNumero();
                }
                $imagen = $obj->getImagen();
                if (isset($imagen) && $imagen != "") {
                    $imagen = "WEB-INF/Controllers/" . $imagen;
                }
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                $nombre = $obj->getModelo();
                $descripcion = $obj->getDescripcion();
                $precio = $obj->getPrecio();
                $tipo = $obj->getTipo();
                $rendimiento = $obj->getRendimiento();
                $parteAnterior = $obj->getParteAnterior();
                $color = $obj->getColor();
                if ($color != "")
                    $stiloDiv = "";
            }
            if (isset($_POST['detalleC'])) {
                $oculto = "style = 'display:none;'";
                $onlyRead = 'disabled="disabled"';
                $visible = "";
                $idEquipo = $_POST['idEquipo'];
                $pagina_ComponenteDetalle = $_POST['lista'];
            }
            ?>
            <div id="copiarComponente"  class="form-row mt">
                <div class="form-group col-12 text-center font-weight-bold mb-5">
                    <h3>Carga de <?php echo $nombre_menu; ?> </h3>
                </div>
                <div class="form-group col-md-3">
                    <select id="componenteCopiar" name="componenteCopiar" class="custom-select">
                            <?php
                            $equipos = new Catalogo();
                            $query = $equipos->getListaAlta("c_componente", "Modelo");
                            echo "<option value = '0' >Selecciona  $nombre_menu a copiar</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                echo "<option value = " . $rs['NoParte'] . ">" . $rs['NoParte'] . " - " . $rs['Modelo'] . " - " . $rs['Descripcion'] . "</option>";
                            }
                            ?>
                    </select>
                </div>
                <div class="form-group col-md-3 text-center pl-2">
                    <h5>Copiar <?php echo $nombre_menu; ?>:</h5>
                </div>
                <div class="form-group col-md-3">
                    <button name="copiar" class="btn btn-primary" title="Copiar datos" onclick="copiarDatos('admin/alta_componentes_pakal.php');
                        return false;
                       "><i class="fal fa-copy"></i></button>
                </div>
            </div>
            <form id="formComponente" name="formComponente" action="/" method="POST">
                <div class="form-row">
                    <div class="form-group col-md-4 p-2">
                        <img src="<?php echo $imagen; ?>" id="preview" name="preview" style="max-width: 200px; max-height: 150px;"/>
                        <input class="form-control" <?php echo $oculto ?> type="file" id="imagen" name="imagen" onchange="readURL(this, 'preview');"/>
                     </div>
                    <div class="form-group col-md-4">
                        <label for="parte"><?php echo $NoParte ?><span style="color:red">*</span></label>
                        <input class="form-control" type="text" id="parte" name="parte" <?php echo $onlyRead ?> value="<?php echo $numero; ?>" <?php echo $read; ?>/>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="nombre"><?php echo $nombre_modelo ?><span style="color:red">*</span></label>
                        <input class="form-control" type="text" id="nombre" name="nombre" <?php echo $onlyRead ?> value="<?php echo $nombre; ?>"/>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="descripcion">Descripción<span style="color:red">*</span></label>
                        <textarea class="form-control" id="descripcion" name="descripcion" cols="40" <?php echo $onlyRead ?> ><?php echo $descripcion; ?></textarea>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="tipo"><?php echo $Tipo ?><span style="color:red">*</span></label>
                        <select  class="custom-select" id="tipo" name="tipo" onchange="MostrarColorTonerComponente()">
                            <option value="">Selecciona una opción</option>
                            <?php
                            $catalogo = new Catalogo();
                            $query = $catalogo->getListaAlta("c_tipocomponente", "Nombre");

                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if ($tipo != "" && $tipo == $rs['IdTipoComponente']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['IdTipoComponente'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2 p-3">
                        <input type="checkbox" name="activo" id="activo" <?php echo $onlyRead ?> <?php echo $activo; ?>/>
                        <label for="activo">Activo</label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-goup col-md-3">
                        <input type="submit" class="btn btn-success btn-block" value="Guardar" <?php echo $oculto; ?> />
                    </div>
                    <div class="form-goup col-md-3">
                         <input type="submit" class="btn btn-warning btn-block" value="Terminar" <?php echo $oculto; ?> onclick="TerminarEdicion('<?php echo $pagina_lista; ?>');
                        return false;"/>
                    </div>
                    <div class="form-goup col-md-3">
                           <input type="submit" class="boton" value="Cancelar" <?php echo $visible; ?> onclick="regresarListaEq('<?php echo $pagina_ComponenteDetalle; ?>', '<?php echo $_POST['div'] ?>');
                           return false;"/>
                           <?php
                           echo "<input type='hidden' id='id' name='id' value='$numero'/> ";
                           echo "<input type='hidden' id='idE' name='idE' value='$idEquipo'/> ";
                           if (isset($_POST['copiar']) && $_POST['copiar'] == TRUE) {
                           echo "<input type='hidden' id='copiadocompo' name='copiadocompo' value='1'/> ";
                           echo "<input type='hidden' id='copiadoid' name='copiadoid' value='" . $_POST['id'] . "'/> ";
                           }
                           ?>
                    </div>
                </div>                
            </form>
        </div>
    </body>
</html> 
