<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_formatoAmplio.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "admin/lista_arrendamiento.php");

$controlador = $_SESSION['ruta_controler'] . "Controler_Arrendamiento.php";
$alta = "admin/alta_formatoAmplio.php";
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
                                    WHERE m.Pagina='admin/lista_formatoAmplio.php';");
while ($rs = mysql_fetch_array($query)) {
    $idModalidad = $rs['IdModalidad'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script> 
    </head>
    <body>
        <div class="principal">
            <?php if($permisos_grid->getAlta()){ ?>
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidosArrend("<?php echo $alta; ?>", "<?php echo $idModalidad; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos" style="width: 100%">
                <thead>
                    <tr style="font-size: 10px">
                        <td align='center' scope='row'>Nombre</td>
                        <td align='center' scope='row'>Arrendamiento</td>
                        <td align='center' scope='row'>Renta mensual</td>
                        <td align='center' scope='row'>ML Incluidas B/N</td>
                        <td align='center' scope='row'>ML Incluidas color</td>
                        <td align='center' scope='row'>ML Excedentes B/N </td>
                        <td align='center' scope='row'>ML Excedentes color</td>
                        <td align='center' scope='row'>Costo ML página B/N</td>
                        <td align='center' scope='row'>Costo ML página color</td>
                         <td align='center' scope='row'>Estatus</td>
                        <td></td><td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT a.IdArrendamiento,a.IdModalidad,a.Nombre,a.Tipo,a.rentaMensual,a.IncluidoBN,a.IncluidoColor,a.ExcedentesBN,a.ExcedentesColor,a.Activo,
                                                        a.CostoProcesadaBN,a.CostoProcesadaColor
                                                        FROM c_arrendamiento a
                                                        WHERE a.IdModalidad=(SELECT IdModalidad FROM c_modalidad WHERE Nombre='Formato amplio');");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
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

                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Tipo'] . "</td>";
                        echo "<td align='center' scope='row'>" . $renta . "</td>";
                        echo "<td align='center' scope='row'>" . $incluidoBN . "</td>";
                        echo "<td align='center' scope='row'>" . $incluidoColor . "</td>";
                        echo "<td align='center' scope='row'>" . $excedenteBN . "</td>";
                        echo "<td align='center' scope='row'>" . $excedenteColor . "</td>";
                        echo "<td align='center' scope='row'>" . $costoBN . "</td>";
                        echo "<td align='center' scope='row'>" . $costoColor . "</td>";
                        if ($rs['Activo'] == 1)
                           echo "<td align='center' scope='row'>Activo</td>";
                        else
                           echo "<td align='center' scope='row'>Inactivo</td>";
                        ?>
                    <td align='center' scope='row'> 
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='editarRegistroArrendamiento("<?php echo $alta; ?>", "<?php echo $rs['IdArrendamiento']; ?>", "<?php echo $rs['IdModalidad']; ?>");
                            return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a>
                           <?php } ?>
                    </td>

                    <td align='center' scope='row'> 
                        <?php if ($permisos_grid->getBaja()) { ?>
                            <a href='#' onclick='eliminarRegistroArrendamiento("<?php echo $controlador . "?id=" . $rs['IdArrendamiento'] . "&id2=" . $rs['IdModalidad'] ?>", "<?php echo $rs['IdArrendamiento']; ?>", "<?php echo $same_page; ?>");
                            return false;'><img src='resources/images/Erase.png'/></a> 
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

