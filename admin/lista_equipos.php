<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$controlador = $_SESSION['ruta_controler'] . "Controler_Equipo.php";

$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_equipos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("No parte", "Modelo", "Precio", "Garantia (Meses)", "Tipo", "Color - B/N", "Estatus", "", "", "");
$columnas = array("NoParte", "Modelo", "PrecioDolares", "PeriodoGarantiaMeses", "Tipo" ,"Nombre", "NoParte");
$tabla = "c_equipo";
$order_by = "Modelo";
$alta = "admin/alta_equipo.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" src="resources/js/funciones.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cargarPyS("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
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
                    $query = $catalogo->obtenerLista("SELECT e.NoParte,e.Modelo,e.PrecioDolares,e.PeriodoGarantiaMeses,e.Activo,te.TipoEquipo AS Tipo,
                        GROUP_CONCAT((SELECT ts.Nombre FROM c_tiposervicio ts WHERE eqc.IdTipoServicio=ts.IdTipoServicio AND (ts.IdTipoServicio=1 OR ts.IdTipoServicio=3)) SEPARATOR  ',') AS tipo
                        FROM c_equipo e
                        LEFT JOIN k_equipocaracteristicaformatoservicio eqc ON e.NoParte=eqc.NoParte 
                        LEFT JOIN c_tipoequipo AS te ON te.IdTipoEquipo = e.IdTipoEquipo
                        GROUP BY e.NoParte ORDER BY e.NoParte ASC;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['PrecioDolares'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['PeriodoGarantiaMeses'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Tipo'] . "</td>";
                        $datosExplode = explode(",", $rs['tipo']);
                        if (isset($datosExplode[1]))
                            echo "<td align='center' scope='row'>Color</td>";
                        else
                            echo "<td align='center' scope='row'>" . $datosExplode[0] . "</td>";
                        if ($rs['Activo'] == 1){
                            echo "<td align='center' scope='row'>Activo</td>";
                        }else{
                            echo "<td align='center' scope='row'>Inactivo</td>";
                        }
                        ?>
                    <td align='center' scope='row'>                        
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='editarRegistroEquipo("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
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
        </div>
    </body>
</html>