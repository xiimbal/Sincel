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
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="tipoComponente">Tipo <?php echo $nombre_menu; ?> <span class="obligatorio"> *</span></label>
                        <select class="custom-select" id='tipoComponente' name='tipoComponente' <?php echo $read; ?> onchange="mostrarRefacciones();" >
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
                    </div>
                    <div class="form-group col-md-3">
                        <label for="noParte"><?php echo $nombre_menu; ?> <span class="obligatorio"> *</span></label>
                        <input id="noParte" class="form-control" name="noParte" type="text" value="<?php echo $modelo . $noParte ?>" <?php echo $read ?>/>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="almacen">Almacén <span class="obligatorio"> *</span></label>
                        <select class="custom-select" id='almacen' name='almacen' <?php echo $read; ?>>
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
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cantidad">Existencia <span class="obligatorio"> *</span></label>
                        <?php
                            $readCantidad = "readonly='readonly'";
                            if ($permisos->tienePermisoEspecial($_SESSION['idUsuario'], 22) || !isset($_POST['id'])) {
                                $readCantidad = "";
                            }
                            ?>
                        <input type='text' id='cantidad' name='cantidad' class="form-control" value='<?php echo $cantidad ?>' <?php echo $readCantidad; ?>/>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="apartados">Apartados <span class="obligatorio"> *</span></label>
                        <input type='text' id='apartados' name='apartados' value='<?php echo $apartado; ?>' readonly="readonly" class="form-control"/>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="minima">Cantidad mínima</label>
                        <input type="text" id="minima" name="minima" value="<?php echo $minimo; ?>" class="form-control"/>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="maxima">Cantidad máxima</label>
                        <input type="text" id="maxima" name="maxima" value="<?php echo $maximo; ?>" class="form-control"/>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="txtUbicacion">Ubicación</label>
                        <input class="form-control" type="text" id="txtUbicacion" name="txtUbicacion" value="<?php echo $ubicacion; ?>"/>
                    </div>
                    <?php if ($edicion == "1") { ?>
                        <div class="form-group col-md-4">
                        <label for="">Comentario</label>
                        <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                        <input style="width: 160px;" type='hidden' id='cantidadExis' name='cantidadExis' value='<?php echo $cantidad; ?>'/>
                        <input style="width: 160px;" type='hidden' id='apartadoExis' name='apartadoExis' value='<?php echo $apartado; ?>'/>
                        <input style="width: 160px;" type='hidden' id='minimoExis' name='minimoExis' value='<?php echo $minimo; ?>'/>
                        <input style="width: 160px;" type='hidden' id='maximoExis' name='maximoExis' value='<?php echo $maximo; ?>'/>
                        <input style="width: 160px;" type='hidden' id='ubicacionExis' name='ubicacionExis' value='<?php echo $ubicacion; ?>'/>
                        </div>
                    <?php } ?>
                </div>
                <div class="form-row">
                    <div class="col-md-3">
                        <input type="submit" class="btn btn-success btn-block" value="Guardar" />
                    </div>
                    <div class="col-md-3">
                        <input type="submit" class="btn btn-danger btn-block" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>
                    </div>
                </div>
                
                
                <input type="hidden" id="id" name="id" value="<?php echo $noParte ?>"/>
                <input type="hidden" id="id2" name="id2" value="<?php echo $idAlmacen ?>"/>
                <input type="hidden" id="tipoAlmacen" name="tipoAlmacen" value="<?php echo $tipoAlmacen1 ?>"/>
                <input type="hidden" id="modelo" name="modelo" value="<?php echo $modelo1 ?>"/>               
            </form>            
        </div>
    </body>
</html>
