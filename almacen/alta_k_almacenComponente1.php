<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/AlmacenConmponente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$pagina_lista = "almacen/lista_k_almacenComponente.php";
include_once("../WEB-INF/Classes/Menu.class.php");
$menu = new Menu();
if($menu->getSubmenuById(70)){
    $nombre_menu = $menu->getNom_sub();
}else{
    $nombre_menu = "Componentes";
}

$idAlmacen = "";
$noParte = "";
$apartado = "0";
$cantidad = "";
$read = "";
$idTipoComp = "";
$minimo = "";
$maximo = "";
$tipoAlmacen1 = "";
$read2 = "";
$modelo = "";
$modelo1 = "";
//almacen filtro
$catalogo = new Catalogo();
$idUsuario = "";
$userAlmacen = "";
$almacenPredeterminado = "";
$ubicacion = "";
$almacen1 = $catalogo->obtenerLista("SELECT IdPuesto,IdUsuario,IdAlmacen  FROM c_usuario WHERE IdUsuario='" . $_SESSION['idUsuario'] . "'");
if ($rs = mysql_fetch_array($almacen1)) {
    $idUsuario = $rs['IdPuesto'];
    $userAlmacen = $rs['IdUsuario'];
    $almacenPredeterminado = $rs['IdAlmacen'];
}


$consulta = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us WHERE ra.IdUsuario='" . $userAlmacen . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
$resultAlmacen = $catalogo->obtenerLista($consulta);
if (mysql_num_rows($resultAlmacen) == 0) {//Sino hay almacen predeterminado
    $consulta = "SELECT * FROM c_almacen a WHERE a.Activo=1 ORDER BY a.nombre_almacen ASC";
}

$consultaComponentes = "";

if (isset($_POST['tipoRefaccion'])) {
    $tipoComponente = $_POST['tipoRefaccion'];
    $consultaComponentes = "SELECT NoParte, IdTipoComponente, PathImagen, Modelo, REPLACE(REPLACE(c.Descripcion, '\r', ''), '\n', ' ') AS Descripcion, 
        PrecioDolares, Activo, NoParteAnterior, Rendimiento,IdColor 
        FROM c_componente AS c WHERE c.IdTipoComponente='" . $tipoComponente . "' AND c.Activo = 1";
    $idAlmacen = $_POST['almacen'];
    $cantidad = $_POST['existencia'];
    $apartado = $_POST['apartados'];
}
$edicion = "0";
$permisos = new PermisosSubMenu();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_almacenComponente.js"></script>        
        <?php
        if ($consultaComponentes != "") {
            ?>
            <script>
                $(function () {
                    var availableTags = [
                        <?php
                        $obj = new AlmacenComponente();
                        $obj->serchNoSerie($consultaComponentes);
                        $lista = $obj->getArreglo_php();
                        $lis = $obj->getArreglo_php2();
                        $descr = $obj->getArreglo_php3();
                        for ($x = 0; $x < count($lista); $x++) {
                            echo "'" . $lista[$x] . " / " . $lis[$x] . " / " . $descr[$x] . "',";
                        }
                        ?>
                    ];
                    $("#noParte").autocomplete({
                        source: availableTags,
                        minLength: 2
                    });
                });
            </script>
        <?php } ?>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id']) && isset($_POST['id2'])) {
                $obj = new AlmacenComponente();
                $obj->getRegistroById($_POST['id'], $_POST['id2']);
                $read = "disabled='disabled'";
                $idAlmacen = $obj->getIdAlmacen();
                $noParte = $obj->getNoParte();
                $cantidad = $obj->getExistencia();
                $apartado = $obj->getApartados();
                $minimo = $obj->getMinimo();
                $maximo = $obj->getMaximo();
                $tipoAlmacen1 = $_POST['id3'];
                $obj->GetModeloComponente($noParte);
                $modelo = $obj->getModeloComp() . " / ";
                $modelo1 = $obj->getModeloComp();
                $read2 = "readonly";
                $edicion = "1";
                $tipoComponente = $obj->getTipoComponente();
                $ubicacion = $obj->getUbicacion();                
            }
            ?>
            <form id="formAlamcenComponente" name="formAlamcenComponente" action="/" method="POST">
                <table style="min-width: 80%">
                    <tr>
                        <td><label for="tipoComponente">Tipo <?php echo $nombre_menu; ?></label><span class="obligatorio"> *</span></td>
                        <td>
                            <select style="width: 160px;" id='tipoComponente' name='tipoComponente' <?php echo $read; ?> onchange="mostrarRefacciones();" >
                                <option value='0'>Todos los tipos de <?php echo $nombre_menu; ?></option>
                                <?php
                                $obj1 = new Catalogo();
                                $query1 = $obj1->getListaAlta('c_tipocomponente', 'Nombre');
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($tipoComponente != "" && $tipoComponente == $rs['IdTipoComponente'])
                                        $s = "selected";
                                    echo "<option value=" . $rs['IdTipoComponente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><label for="noParte"><?php echo $nombre_menu; ?></label><span class="obligatorio"> *</span></td>
                        <td>
                            <input id="noParte" name="noParte" type="text" value="<?php echo $modelo . $noParte ?>" <?php echo $read ?>/>
                        </td>
                        <td><label for="almacen">Almacén</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select style="width: 160px;" id='almacen' name='almacen' <?php echo $read; ?>>
                                <option value='0'>Selecciona una opción</option>
                                <?php
                                $obj1 = new Catalogo();
                                $query1 = $obj1->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($idAlmacen != "" && $idAlmacen == $rs['id_almacen'] || ($idAlmacen == "" && $almacenPredeterminado != "" && $almacenPredeterminado == $rs['id_almacen'])){
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['id_almacen'] . " " . $s . ">" . $rs['nombre_almacen'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>    
                    </tr>
                    <tr>
                        <td><label for="cantidad">Existencia</label><span class="obligatorio"> *</span></td>
                        <td>
                            <?php
                            $readCantidad = "readonly='readonly'";
                            if ($permisos->tienePermisoEspecial($_SESSION['idUsuario'], 22) || !isset($_POST['id'])) {
                                $readCantidad = "";
                            }
                            ?>
                            <input type='text' id='cantidad' name='cantidad' value='<?php echo $cantidad ?>' <?php echo $readCantidad; ?>/>                             
                        </td>
                        <td><label for="apartados">Apartados</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type='text' id='apartados' name='apartados' value='<?php echo $apartado; ?>' readonly="readonly"/>                            
                        </td>
                    </tr>
                    <tr>
                        <td>Cantidad mínima</td><td><input type="text" id="minima" name="minima" value="<?php echo $minimo; ?>"/></td>
                        <td>Cantidad máxima</td><td><input type="text" id="maxima" name="maxima" value="<?php echo $maximo; ?>"/></td>
                    </tr>
                    <tr>
                        <td>Ubicación</td><td colspan="3"><input style="width: 100%;" type="text" id="txtUbicacion" name="txtUbicacion" value="<?php echo $ubicacion; ?>"/></td>
                    </tr>                    
                    <?php if ($edicion == "1") { ?>
                        <tr>
                            <td>Comentario</td>
                            <td colspan="3"><textarea style="width: 450px;height: 50px" id="comentario" name="comentario"></textarea></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input style="width: 160px;" type='hidden' id='cantidadExis' name='cantidadExis' value='<?php echo $cantidad; ?>'/>
                                <input style="width: 160px;" type='hidden' id='apartadoExis' name='apartadoExis' value='<?php echo $apartado; ?>'/>
                                <input style="width: 160px;" type='hidden' id='minimoExis' name='minimoExis' value='<?php echo $minimo; ?>'/>
                                <input style="width: 160px;" type='hidden' id='maximoExis' name='maximoExis' value='<?php echo $maximo; ?>'/>
                                <input style="width: 160px;" type='hidden' id='ubicacionExis' name='ubicacionExis' value='<?php echo $ubicacion; ?>'/>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>
                <input type="hidden" id="id" name="id" value="<?php echo $noParte ?>"/>
                <input type="hidden" id="id2" name="id2" value="<?php echo $idAlmacen ?>"/>
                <input type="hidden" id="tipoAlmacen" name="tipoAlmacen" value="<?php echo $tipoAlmacen1 ?>"/>
                <input type="hidden" id="modelo" name="modelo" value="<?php echo $modelo1 ?>"/>               
            </form>            
        </div>
    </body>
</html>
