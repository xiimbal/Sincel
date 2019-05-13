<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//include_once("../WEB-INF/Classes/PartesDelComponenteC.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_DetalleComponente.php";
$alta = "admin/alta_detalleComponentes.php";
$detalle = "admin/alta_componentes.php";
$idComponente = $_POST['idEquipo'];
$same_page = "admin/lista_partesDelComponente.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_partesDelComponente.js"></script> 
    </head>
    <body>
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend>Partes del componente</legend>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='altaDetalleComponente("<?php echo $alta; ?>", "partesDelComponente", "<?php echo $idComponente; ?>", "hijo");' style="float: right; cursor: pointer;" />  
                <br/><br/><br/>
                <table id="tPartesDelEq" class="tabla_datos" style="width: 100%">
                    <thead>
                        <tr>
                            <td>Tipo</td><td>No. Partes</td><td>Modelo</td><td>Descripción</td><td></td><td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("SELECT tc.Nombre,cci.NoParteComponente,c.Modelo,c.Descripcion,cci.NoParteComponentePadre,cci.NoParteComponente
                                                                FROM k_componentecomponenteinicial cci,c_componente c,c_tipocomponente tc
                                                                WHERE cci.NoParteComponente=c.NoParte
                                                                AND c.IdTipoComponente=tc.IdTipoComponente
                                                                AND cci.NoParteComponentePadre='" . $idComponente . "'");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['NoParteComponente'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                            ?>
                        <td align='center' scope='row'> <a href='#' onclick='dealleComponentes("<?php echo $detalle; ?>", "partesDelComponente", "<?php echo $rs['NoParteComponente']; ?>", "<?php echo $idComponente ?>", "<?php echo $same_page ?>");
                            return false;' title='Editar Registro' ><img src="resources/images/Buscar.png" style="width: 24px; height: 24px;"/></a></td>
                        <td align='center' scope='row'> <a href='#' onclick='eliminraRegDetalle("<?php echo $controlador . "?id=" . $rs['NoParteComponentePadre'] . "&id2=" . $rs['NoParteComponente'] ?>", "partesDelComponente", "<?php echo $rs['NoParteComponentePadre']; ?>", "<?php echo $same_page; ?>");
                            return false;'><img src="resources/images/Erase.png"/></a> </td> 
                            <?php
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </fieldset>
        </div>
    </body>
</html>

