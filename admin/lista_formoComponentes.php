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
$same_page = "admin/lista_formoComponentes.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_formaParteC.js"></script> 
    </head>
    <body>
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend>Formo parte del(los) componente(s)</legend>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='altaDetalleComponente("<?php echo $alta; ?>", "formatoComponentes", "<?php echo $idComponente; ?>","padre");' style="float: right; cursor: pointer;" />  
                <br/><br/><br/>
                <table id="tformParte" class="tabla_datos" style="width: 100%">
                    <thead>
                        <tr>
                            <td>Tipo</td><td>No. Partes</td><td>Modelo</td><td>Descripción</td><td></td><td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("SELECT tc.Nombre,c.NoParte,c.Modelo,c.Descripcion,cci.NoParteComponentePadre,cci.NoParteComponente
                                                                FROM c_componente c,c_tipocomponente tc,k_componentecomponenteinicial cci
                                                                WHERE cci.NoParteComponentePadre=c.NoParte
                                                                AND c.IdTipoComponente=tc.IdTipoComponente
                                                                AND cci.NoParteComponente='" . $idComponente . "'");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                            ?>
                        <td align='center' scope='row'> <a href='#' onclick='dealleComponentes("<?php echo $detalle; ?>","formatoComponentes", "<?php echo $rs['NoParteComponentePadre']; ?>","<?php echo $idComponente;?>","<?php echo $same_page ?>");
                            return false;' title='Editar Registro' >Buscar</a></td>
                         <td align='center' scope='row'> <a href='#' onclick='eliminraRegDetalle("<?php echo $controlador . "?id=" . $rs['NoParteComponentePadre'] . "&id2=" . $rs['NoParteComponente'] ?>","formatoComponentes", "<?php echo $rs['NoParteComponente']; ?>", "<?php echo $same_page; ?>");
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

