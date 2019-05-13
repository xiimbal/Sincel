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
$nombre_modelo = $permisos_grid->getModeloSistema();
$NoParte = $permisos_grid->getNoParteSistema();
$Tipo = $permisos_grid->getTipoSistema();

$same_page = "admin/lista_componentes_pakal.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$controlador = $_SESSION['ruta_controler'] . "Controler_Componente.php";

$cabeceras = array($nombre_modelo, $NoParte,$Tipo, "Descripcion", "Estatus","", "", "");
$columnas = array("Modelo", "NoParte", "Tipo" ,"Descripcion", "NoParte");
$alta = "admin/alta_componentes_pakal.php";

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
        <style>
            /*#tAlmacen{
                width: 100% !important;
            }*/

            .dataTables_paginate{
                margin: 10px auto !important;
            }
        </style>
        <div class="principal bg-white p-4">
                
                        <?php if ($permisos_grid->getAlta()) { ?>
                            <button class="btn btn-success" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");'><i class="fal fa-plus-circle"></i></button>
                        <?php } ?>
                <div class="form-row m-2" >       
                        <div class="form-group col-md-4">
                            <label class="text-dark"  for="tipoComponenteFiltro">Tipo de <?php echo $Tipo; ?></label>
                            <select id="tipoComponenteFiltro" name="tipoComponenteFiltro" class="form-control">
                                <option value="0">Todos los <?php echo $Tipo; ?></option>
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
                            <label class="text-dark"  for="Modelo"><?php echo $nombre_modelo;  ?></label>
                            <input class="form-control" type="text" id="Modelo" name="Modelo" value="<?php echo $modelo; ?>"/>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="text-dark"  for="NoParte"><?php echo $NoParte;  ?></label>
                            <input class="form-control" type="text" id="NoParte" name="NoParte" value="<?php echo $no_parte; ?>"/>
                        </div>   
                        
                </div>
                <!--div class="form-group col-md-2"-->
                                <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Mostrar Productos" class="btn btn-success" onclick="mostrarComponentes('<?php echo $same_page ?>','Modelo','NoParte','tipoComponenteFiltro');"/>
                <!--/div-->
            <?php if(isset($_POST['mostrar'])){  ?>
            <div id="tAlmacen_wrapper" class="dataTables_wrapper" role="grid">
                <table id="tAlmacen" class="tabla_datos dataTable table">
                <thead class="thead-dark">
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
                        //echo "<td align='center' scope='row'>" . $rs["PrecioDolares"] . "</td>";
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
                               <i class="fal fa-pencil" style="font-size: 1.5rem;color: peru;"></i>
                            </a>
                        <?php } ?>
                    </td>
                    <td align='center' scope='row'>       
                        <?php if ($permisos_grid->getBaja()) { ?>
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs[$columnas[count($columnas) - 1]]; ?>", "<?php echo $same_page; ?>"); return false;' 
                               title='Eliminar Registro' ><i class="fal fa-trash" style="font-size: 1.5rem;color: red;"></i>
                            </a>
                        <?php } ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>
                </tbody>
                </table>
            </div>
            <?php } ?>

        </div>
    </body>
</html>