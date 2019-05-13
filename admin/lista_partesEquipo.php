<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PartesEquipo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_PartesEquipo.php";
$alta = "admin/alta_PartesEquipo.php";
$idEquipo = $_POST['idEquipo'];
$same_page = "admin/lista_partesEquipo.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_equipoComponenteInicial.js"></script>
    </head>
    <body>       
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend>Partes del equipo</legend>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='altaDetalle("<?php echo $alta; ?>","partesEquipo", "<?php echo $idEquipo; ?>");' style="float: right; cursor: pointer;" />  
                <br/><br/><br/>
                <table id="tEquipoIni" class="tabla_datos"  style="width: 100%">
                    <thead>
                        <tr>
                            <td>Modelo</td><td>Tipo</td><td>Soporte Máximo</td><td></td><td></td>
                        </tr> 
                    </thead>
                    <tbody>
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("SELECT pe.NoParteEquipo,pe.NoParteComponente,tc.Nombre,pe.SoportadoMaximo,c.Modelo
                                                            FROM k_parteequipo pe,c_componente c,c_equipo e,c_tipocomponente tc
                                                            WHERE pe.NoParteEquipo=e.NoParte
                                                            AND pe.NoParteComponente=c.NoParte
                                                            AND c.IdTipoComponente=tc.IdTipoComponente
                                                            AND pe.NoParteEquipo='".$idEquipo."';");                       
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['SoportadoMaximo'] . "</td>";
                            ?>
                        <td align='center' scope='row'> <a href='#' onclick='editarDetalle("<?php echo $alta; ?>","partesEquipo", "<?php echo $rs['NoParteEquipo']; ?>", "<?php echo $rs['NoParteComponente']; ?>");
                            return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>
                        <td align='center' scope='row'> <a href='#' onclick='eliminraRegDetalle("<?php echo $controlador . "?id=" . $rs['NoParteEquipo'] . "&id2=" . $rs['NoParteComponente'] ?>","partesEquipo", "<?php echo $rs['NoParteEquipo']; ?>", "<?php echo $same_page; ?>");
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
