<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Componente.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
$menu = new Menu();
if($menu->getSubmenuById(70)){
    $nombre_menu = $menu->getNom_sub();
}else{
    $nombre_menu = "Componentes";
}

$pagina_lista = "admin/lista_componentes.php";
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
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_componentes.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_equipo.js"></script>
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
            Carga de <?php echo $nombre_menu; ?> Kyocera
            <div id="copiarComponente" name="copiarComponente" style="float: right;
                 margin-right: 5%;
                 ">
                <select id="componenteCopiar" name="componenteCopiar" style="width: 250px;
                        ">
                            <?php
                            $equipos = new Catalogo();
                            $query = $equipos->getListaAlta("c_componente", "Modelo");
                            echo "<option value = '0' >Selecciona un $nombre_menu a copiar</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                echo "<option value = " . $rs['NoParte'] . ">" . $rs['NoParte'] . " - " . $rs['Modelo'] . " - " . $rs['Descripcion'] . "</option>";
                            }
                            ?>
                </select>
                Copiar <?php echo $nombre_menu; ?>:
                <input type="image" src="resources/images/copy.png" title="Copiar datos" onclick="copiarDatos('admin/alta_componentes.php');
                        return false;
                       " name="image" width="20" height="20">
            </div><br/><br/>
            <form id="formComponente" name="formComponente" action="/" method="POST">
                <table>
                    <tr>
                        <td><img src="<?php echo $imagen; ?>" id="preview" name="preview" style="max-width: 200px; max-height: 150px;"/></td>
                        <td><input <?php echo $oculto ?> type="file" id="imagen" name="imagen" onchange="readURL(this, 'preview');"/></td>
                        <td></td>
                        <td></td>                        
                    </tr>
                    <tr>
                        <td><label for="parte">No. de parte<span style="color:red">*</span></label></td>
                        <td><input type="text" id="parte" name="parte" <?php echo $onlyRead ?> value="<?php echo $numero; ?>" <?php echo $read; ?>/></td>
                        <td><label for="parte_anterior">No. de parte anterior</label></td>
                        <td><input type="text" id="parte_anterior" <?php echo $onlyRead ?> name="parte_anterior" value="<?php echo $parteAnterior ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="nombre">Modelo<span style="color:red">*</span></label></td>
                        <td><input type="text" id="nombre" name="nombre" <?php echo $onlyRead ?> value="<?php echo $nombre; ?>"/></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><label for="descripcion">Descripción<span style="color:red">*</span></label></td>
                        <td><textarea id="descripcion" name="descripcion" cols="40" <?php echo $onlyRead ?> ><?php echo $descripcion; ?></textarea></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><label for="precio">Precio de lista en dólares<span style="color:red">*</span></label></td>
                        <td><input type="text" name="precio" id="precio" <?php echo $onlyRead ?> value="<?php echo $precio; ?>"/></td>                        
                        <td><label for="rendimiento">Rendimiento</label></td>
                        <td><input type="text" id="rendimiento" name="rendimiento" <?php echo $onlyRead ?> value="<?php echo $rendimiento; ?>"/></td>
                    </tr>
                    <tr>
                        <td>Tipo de <?php echo $nombre_menu; ?><span style="color:red">*</span></td>
                        <td>
                            <select id="tipo" name="tipo" onchange="MostrarColorTonerComponente()">
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
                        </td>
                        <td></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $onlyRead ?> <?php echo $activo; ?>/>Activo</td>
                    </tr>
                </table>
                <div id='colorComponente' <?php echo $stiloDiv ?>>
                    <table>
                        <tr>                   
                            <td style="width: 105px">Color tóner</td>
                            <td>
                                <select id="color" name="color">
                                    <?php
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->getListaAlta("c_colortoner", "Descripcion");
                                    echo "<option value='' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($color != "" && $color == $rs['IdColor']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['IdColor'] . " " . $s . ">" . $rs['Descripcion'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <div id="mensajeComponente"></div>
                <div id="editComponentes">   

                    <div id="formatoComponentes" style="width: 100%">

                    </div>
                    <div id="equipoCompatible">

                    </div>
                    <div id="ComponentesNecesarios" style="width: 100%">

                    </div>                   

                    <div id="partesDelComponente" style="width: 100%">

                    </div> 

                </div>
                <input type="submit" class="boton" value="Guardar" <?php echo $oculto; ?> />
                <input type="submit" class="boton" value="Terminar" <?php echo $oculto; ?> onclick="TerminarEdicion('<?php echo $pagina_lista; ?>');
                        return false;"/>
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
            </form>
        </div>
    </body>
</html> 
