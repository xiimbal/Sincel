<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//include_once("../WEB-INF/Classes/PartesDelComponenteC.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_FormaParteC.php";
$alta = "admin/alta_formaParte.php";
$detalle="admin/alta_componentes.php";
$idComponente = $_POST['idEquipo'];
$same_page = "admin/lista_formaParteC.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_componentesNecesariosC.js"></script> 
    </head>
    <body>
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend>Componentes necesarios</legend>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='altaDetalle("<?php echo $alta; ?>","ComponentesEquipo", "<?php echo $idEquipo; ?>");' style="float: right; cursor: pointer;" />  
                <br/><br/><br/>
                <table id="tCompNecesarios" class="tabla_datos" style="width: 100%">
                    <thead>
                        <tr>
                            <td>Tipo</td><td>No. Partes</td><td>Modelo</td><td>Descripción</td><td></td><td></td><td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                          $catalogo= new Catalogo();  
                           $query = $catalogo->obtenerLista("SELECT tc.Nombre,ccn.NoParteComponente,c.Modelo,c.Descripcion,ccn.NoParteComponentePadre
                                                                FROM k_componentecomponentenecesario ccn,c_componente c,c_tipocomponente tc
                                                                WHERE ccn.NoParteComponente=c.NoParte
                                                                AND c.IdTipoComponente=tc.IdTipoComponente
                                                                AND ccn.NoParteComponentePadre='".$idComponente."'");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['NoParteComponente'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                            ?>
                        <td align='center' scope='row'> <a href='#' onclick='editarDetalle("<?php echo $alta; ?>","partesDelComponente", "<?php echo $rs['NoParteComponente']; ?>", "<?php echo $rs['NoParteComponente']; ?>");
                            return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>
                        <td align='center' scope='row'> <a href='#' onclick='dealleComponentes("<?php echo $detalle; ?>","partesDelComponente", "<?php echo $rs['NoParteComponente']; ?>","<?php echo $idEquipo?>","<?php echo $same_page ?>");
                            return false;' title='Editar Registro' ><img src="resources/images/Buscar.png" style="width: 24px; height: 24px;"/></a></td>
                        <td align='center' scope='row'> <a href='#' onclick='eliminraRegDetalle("<?php echo $controlador . "?id=" . $rs['NoParteEquipo'] . "&id2=" .$idComponete ?>","partesDelComponente", "<?php echo $rs['NoParteComponente']; ?>", "<?php echo $same_page; ?>");
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

