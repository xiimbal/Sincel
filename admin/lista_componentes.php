<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
$menu = new Menu();
if($menu->getSubmenuById(70)){
    $nombre_menu = $menu->getNom_sub();
}else{
    $nombre_menu = "Componentes";
}

$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_componentes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$controlador = $_SESSION['ruta_controler'] . "Controler_Componente.php";

$cabeceras = array("Modelo", "No Parte","Tipo", "Descripcion", "Precio", "Estatus","", "", "");
$columnas = array("Modelo", "NoParte", "Tipo" ,"Descripcion", "PrecioDolares", "NoParte");
$alta = "admin/alta_componentes.php";

$where = "";

$tipoComponFiltro = "";
if (isset($_POST['tipoComponente']) && $_POST['tipoComponente'] > 0) {
    $tipoComponFiltro = $_POST['tipoComponente'];    
    $where = " WHERE c.IdTipoComponente='$tipoComponFiltro'";
}

$modelo = "";
if(isset($_POST['modelo']) && $_POST['modelo']!=""){
    $modelo = $_POST['modelo'];
    if($where == ""){
        $where .= " WHERE c.Modelo LIKE '%$modelo%' ";
    }else{
        $where .= " AND c.Modelo LIKE '%$modelo%' ";
    }
}

$no_parte = "";
if(isset($_POST['parte']) && $_POST['parte']!=""){
    $no_parte = $_POST['parte'];
    if($where == ""){
        $where .= " WHERE c.NoParte LIKE '%$no_parte%' ";
    }else{
        $where .= " AND c.NoParte LIKE '%$no_parte%' ";
    }    
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table style="width: 80%;">
                <tr>
                    <td>Tipo de <?php echo $nombre_menu; ?></td>
                    <td>
                        <select id="tipoComponenteFiltro" name="tipoComponenteFiltro">
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
                    </td>                    
                    <td>Modelo</td>
                    <td>
                        <input type="text" id="Modelo" name="Modelo" value="<?php echo $modelo; ?>"/>
                    </td>
                    <td>No. Parte</td>
                    <td>
                        <input type="text" id="NoParte" name="NoParte" value="<?php echo $no_parte; ?>"/>
                    </td>
                    <td>
                        <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Mostrar <?php echo $nombre_menu; ?>" class="button" 
                               onclick="mostrarComponentes('<?php echo $same_page ?>','Modelo','NoParte','tipoComponenteFiltro');"/>
                    </td>
                </tr>
            </table>
            <br/><br/><br/>
            <?php if(isset($_POST['mostrar'])){  ?>
            <table id="tAlmacen" class="tabla_datos">
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
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT c.NoParte, c.Modelo, c.Descripcion, c.PrecioDolares, c.Activo, tc.Nombre AS Tipo 
                        FROM c_componente c 
                        LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente 
                        $where 
                        ORDER BY c.Modelo ASC");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs["Modelo"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["NoParte"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["Tipo"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["Descripcion"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["PrecioDolares"] . "</td>";
                        if ($rs['Activo'] == 1){
                            echo "<td align='center' scope='row'>Activo</td>";
                        }else{
                            echo "<td align='center' scope='row'>Inactivo</td>";
                        }
                        ?>
                    <td align='center' scope='row'>       
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='editarRegistroComponentes("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
                                return false;' title='Editar Registro' >
                                <img src="resources/images/Modify.png"/>
                            </a>
                        <?php } ?>
                    </td>
                    <td align='center' scope='row'>       
                        <?php if ($permisos_grid->getBaja()) { ?>
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs[$columnas[count($columnas) - 1]]; ?>", "<?php echo $same_page; ?>"); return false;' 
                               title='Eliminar Registro' ><img src="resources/images/Erase.png"/>
                            </a>
                        <?php } ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </body>
</html>