<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
$menu = new Menu();
if($menu->getSubmenuById(70)){
    $nombre_menu = $menu->getNom_sub();
}else{
    $nombre_menu = "Componentes";
}

$permisos_grid = new PermisosSubMenu();
$catalogo = new Catalogo();

$same_page = "almacen/lista_k_almacenComponente.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controler_AlmacenComponente.php";

$cabeceras = array("Almacén", $nombre_menu, "NoParte" , "Descripción", "Existencia", "Apartados", "Minimo", "Maximo", "Ubicacion", "Precio Dlls.", "", "", "");
$columnas = array("nombre_almacen", "Modelo", "NoParte", "Descripcion", "cantidad_existencia", "cantidad_apartados", "CantidadMinima", "CantidadMaxima", "Ubicacion",
    "PrecioDolares", "NoParte", "id_almacen");
$alta = "almacen/alta_k_almacenComponente.php";

/*Obtenemos los almacenes a los que tiene permiso el usuario actual*/
$idAlmacenes = array();
$consultaAlmacen = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us "
    . " WHERE ra.IdUsuario='" . $_SESSION['idUsuario'] . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario 
        ORDER BY a.nombre_almacen ASC";
$queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);

if(mysql_num_rows($queryAlmacen) == 0){
    $consultaAlmacen = "SELECT * FROM c_almacen a WHERE a.Activo=1 ORDER BY a.nombre_almacen ASC";
    $queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacenes[$rs['id_almacen']] = $rs['nombre_almacen'];
    }
    $id_almacenes = "";
}else{
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacenes[$rs['id_almacen']] = $rs['nombre_almacen'];        
    }
    $id_almacenes = implode(",", array_keys($idAlmacenes));//Id de los almacenes a los que tiene permiso el usuario actual
}

$consultaTipoComponente = "";
$tipoComponFiltro = "";
if (isset($_POST['tipoComponente']) && $_POST['tipoComponente'] > 0) {
    $tipoComponFiltro = $_POST['tipoComponente'];
    $consultaTipoComponente = "AND c.IdTipoComponente='" . $_POST['tipoComponente'] . "'";
}

$filtro_responsable_almacen = "";
$array_almacenes = array();
if(isset($_POST['almacenes']) && $_POST['almacenes']!=""){
    $filtro_responsable_almacen = " AND al.id_almacen IN(".$_POST['almacenes'].") ";
    $array_almacenes = explode(",", $_POST['almacenes']);    
}

$modelo = "";
$filtro_modelo = "";
if(isset($_POST['modelo']) && $_POST['modelo']!=""){
    $modelo = $_POST['modelo'];
    $filtro_modelo = " AND c.Modelo LIKE '%$modelo%' ";
}

if ($id_almacenes != "") {    
    $consulta = "SELECT * FROM k_almacencomponente ac,c_almacen al,c_componente c 
    WHERE ac.NoParte=c.NoParte AND ac.id_almacen=al.id_almacen
    AND al.id_almacen IN ($id_almacenes) $consultaTipoComponente $filtro_responsable_almacen $filtro_modelo
    ORDER BY ac.id_almacen,c.Modelo ASC;";
} else {
    $consulta = "SELECT * FROM k_almacencomponente ac,c_almacen al,c_componente c 
        WHERE ac.NoParte=c.NoParte AND ac.id_almacen=al.id_almacen $consultaTipoComponente $filtro_responsable_almacen $filtro_modelo 
        ORDER BY ac.id_almacen,c.Modelo ASC;";
}
//echo $consulta;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/exportar_excel.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_almacenComponente.js"></script>
        <script type="text/javascript" src="resources/js/funciones.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <style>
                .ui-multiselect{
                    width:100%!important ;
                }
            </style>
            <form action="reportes/ReporteInventarioComponentes.php" method="post" target="_blank" id="FormularioExportacion">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="tipoComponenteFiltro">Tipo de <?php echo $nombre_menu; ?></label>
                        <select id="tipoComponenteFiltro" name="tipoComponenteFiltro" class="custom-select">
                                <option value="0">Todos los <?php echo $nombre_menu; ?></option>
                                <?php
                                $obj1 = new Catalogo();
                                $query1 = $obj1->getListaAlta('c_tipocomponente', 'Nombre');
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($tipoComponFiltro != "" && $tipoComponFiltro == $rs['IdTipoComponente']){
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoComponente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="almacen">Almacén</label><br>
                        <select id="almacen" name="almacen[]" class="custom-select multiselect" multiple>
                                <?php
                                    //echo "<option value=''>Todos los almacenes</option>";
                                    foreach ($idAlmacenes as $key => $value) {
                                        $s = "";
                                        if(in_array($key, $array_almacenes)){
                                            $s = "selected = 'selected'";
                                        }
                                        echo "<option value='$key' $s>$value</option>";
                                    }
                                ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="">Modelo</label>
                        <input type="text" id="NoParte" name="NoParte" value="<?php echo $modelo; ?>" class="form-control"/>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Mostrar <?php echo $nombre_menu; ?>" class="btn btn-success btn-block" 
                                   onclick="mostrarComponentesAlmacen('<?php echo $same_page ?>','tipoComponenteFiltro','almacen','NoParte');"/>
                    </div>
                    <?php if (isset($_POST['mostrar']) && $_POST['mostrar'] == "true") { ?>
                        <div class="form-group col-md-3">
                            <input type="button" class="botonExcel btn btn-outline-success btn-block" title="Exportar a excel" id="excelSubmit" name="excelSubmit" value="Exportar a excel" onclick="submitform()"/>
                                <input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
                        </div>
                    <?php } ?>
                </div>
            </form>
            <br/><br/><br/>
            <?php if(isset($_POST['mostrar'])){  ?>
                <div class="table-responsive">
                    <style>
                    .fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-tl.ui-corner-tr.ui-helper-clearfix{
                        min-width: 996px;
                    }
                    .fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-bl.ui-corner-br.ui-helper-clearfix{
                        min-width: 996px;
                    }
                </style>
                    <table id="tAlmacen" class="tabla_datos" style="width: 100%!important;min-width: 996px;">
                        <thead>
                            <tr>
                                <?php
                                for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                }
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // if (isset($_POST['tipoComponente']) && $_POST['tipoComponente'] != "") {
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista($consulta);
                            while ($rs = mysql_fetch_array($query)) {
                                echo "<tr>";
                                for ($i = 0; $i < count($columnas) - 2; $i++) {
                                    $prefijo = "";
                                    if ($columnas[$i] == "PrecioDolares") {
                                        $prefijo = "$ ";
                                    }
                                    echo "<td align='center' scope='row'>$prefijo" . $rs[$columnas[$i]] . "</td>";
                                }
                                ?>
                            <td align='center' scope='row'> 
                                <?php if ($permisos_grid->getModificar()) { ?>
                                    <a class="h6 text-warning" href='#' onclick='editarComponentesAlmacen("<?php echo $alta; ?>", "<?php echo $rs['NoParte']; ?>", "<?php echo $rs['id_almacen']; ?>", "<?php echo $rs['TipoAlmacen']; ?>");
                                        return false;' title='Editar Registro' ><i class="fal fa-pencil"></i></a>
                                   <?php } ?>
                            </td>

                            <td align='center' scope='row'> 
                                <?php if ($permisos_grid->getBaja()) { ?>
                                    <a class="h6 text-danger" href='#' onclick='eliminarRegistroProv("<?php echo $controlador . "?id=" . $rs['NoParte'] . "&id2=" . $rs['id_almacen'] . "&modelo=" . $rs['Modelo'] ?>", "<?php echo $rs['NoParte']; ?>", "<?php echo $same_page; ?>");
                                        return false;'><i class="fal fa-trash"></i></a> </td>  
                                <?php } ?>
                                <?php
                                echo "</tr>";
                            }
                            //  }
                            ?>
                        </tbody>
                    </table>
                </div>
            
            <?php } ?>
        </div>
    </body>
</html>