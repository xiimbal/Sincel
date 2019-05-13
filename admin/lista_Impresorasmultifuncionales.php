<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_Impresorasmultifuncionales.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "admin/lista_arrendamiento.php");

$controlador = $_SESSION['ruta_controler'] . "Controler_Arrendamiento.php";
$alta = "admin/alta_impresoraMultifuncional.php";
$idModalidad = "";
$renta = "";
$incluidoBN = "";
$incluidoColor = "";
$excedenteBN = "";
$excedenteColor = "";
$costoBN = "";
$costoColor = "";

$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT *
                                    FROM c_modalidad m
                                    WHERE m.Pagina='admin/lista_Impresorasmultifuncionales.php';");
while ($rs = mysql_fetch_array($query)) {
    $idModalidad = $rs['IdModalidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
<body>
    <div class="principal">

        <?php if($permisos_grid->getAlta()){ ?>
            <a href="#" class="btn btn-success" onclick='cambiarContenidosArrend("<?php echo $alta; ?>", "<?php echo $idModalidad; ?>");'>
                <i class="far fa-plus-circle" style="color: white; font-size: 1.2rem;"></i>
            </a>
        <?php } ?>

        <div class="table-responsive">
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Arrendamiento</th>
                        <th>Renta mensual</th>
                        <th>Incluidas B/N</th>
                        <th>Incluidas color</th>
                        <th>Excedentes B/N </th>
                        <th>Excedentes color</th>
                        <th>Costo página B/N</th>
                        <td>Costo página color</th>
                        <th>Estatus</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT a.IdArrendamiento,a.IdModalidad,a.Nombre,a.Tipo,a.rentaMensual,a.IncluidoBN,a.IncluidoColor,a.ExcedentesBN,a.ExcedentesColor,a.Activo,
                                                        a.CostoProcesadaBN,a.CostoProcesadaColor FROM c_arrendamiento a
                                                        WHERE a.IdModalidad=(SELECT IdModalidad FROM c_modalidad WHERE Nombre='Impresoras y Multifuncionales');");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr class='table__line'>";
                        if ($rs['IncluidoBN'] == 1)
                            $incluidoBN = "x";
                        else
                            $incluidoBN = "";
                        if ($rs['IncluidoColor'] == 1)
                            $incluidoColor = "x";
                        else
                            $incluidoColor = "";
                        if ($rs['ExcedentesBN'] == 1)
                            $excedenteBN = "x";
                        else
                            $excedenteBN = "";
                        if ($rs['ExcedentesColor'] == 1)
                            $excedenteColor = "x";
                        else
                            $excedenteColor = "";
                        if ($rs['CostoProcesadaBN'] == 1)
                            $costoBN = "x";
                        else
                            $costoBN = "";
                        if ($rs['CostoProcesadaColor'] == 1)
                            $costoColor = "x";
                        else
                            $costoColor = "";
                        if ($rs['rentaMensual'] == 1)
                            $renta = "x";
                        else
                            $renta = "";

                        echo "<td>" . $rs['Nombre'] . "</td>";
                        echo "<td>" . $rs['Tipo'] . "</td>";
                        echo "<td>" . $renta . "</td>";
                        echo "<td>" . $incluidoBN . "</td>";
                        echo "<td>" . $incluidoColor . "</td>";
                        echo "<td>" . $excedenteBN . "</td>";
                        echo "<td>" . $excedenteColor . "</td>";
                        echo "<td>" . $costoBN . "</td>";
                        echo "<td>" . $costoColor . "</td>";
                        if ($rs['Activo'] == 1)
                        echo "<td>Activo</td>";
                        else
                        echo "<td>Inactivo</td>";
                    ?>
                    <td> 
                        <?php if($permisos_grid->getModificar()){ ?>
                            <a href='#' onclick='editarRegistroArrendamiento("<?php echo $alta; ?>", "<?php echo $rs['IdArrendamiento']; ?>", "<?php echo $rs['IdModalidad']; ?>");return false;' title='Editar Registro' >
                                <i class="far fa-edit"></i>
                            </a>
                        <?php } ?>
                    </td>

                    <td> 
                        <?php if($permisos_grid->getBaja()){ ?>
                            <a href='#' onclick='eliminarRegistroArrendamiento("<?php echo $controlador . "?id=" . $rs['IdArrendamiento'] . "&id2=" . $rs['IdModalidad'] ?>", "<?php echo $rs['IdArrendamiento']; ?>", "<?php echo $same_page; ?>");return false;'>
                                <i class="far fa-trash-alt"></i>
                            </a> 
                        <?php } ?>
                    </td> 
                    <?php
                        echo "</tr>";
                        }
                    ?>
            </table>
        </div>
    </div>
    <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
</body>
</html>

