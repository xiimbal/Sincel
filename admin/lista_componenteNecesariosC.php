<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//include_once("../WEB-INF/Classes/PartesEquipo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_ComponentesNecesarioC.php";
$alta = "admin/alta_ComponentesNecesariosC.php";
$idComponente = $_POST['idEquipo'];
$detalle="admin/alta_componentes.php";
$same_page = "admin/lista_componenteNecesariosC.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_equipoComponenteInicial.js"></script>
    </head>
    <body>       
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend>Componentes necesarios</legend>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='altaDetalleComponente("<?php echo $alta; ?>", "ComponentesNecesarios", "<?php echo $idComponente; ?>","padre");' style="float: right; cursor: pointer;" />  
                <br/><br/><br/>
                <table id="tEquipoIni" class="tabla_datos"  style="width: 100%">
                    <thead>
                        <tr>
                            <td>Tipo</td><td>No. parte</td><td>Modelo</td><td>Descripcion</td><td></td><td></td>
                        </tr> 
                    </thead>
                    <tbody>
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("SELECT tc.Nombre,c.NoParte,c.Modelo,c.Descripcion,cn.NoParteComponentePadre,cn.NoParteComponente
                                                            FROM k_componentecomponentenecesario cn,c_componente c,c_tipocomponente tc
                                                            WHERE cn.NoParteComponente=c.NoParte
                                                            AND c.IdTipoComponente=tc.IdTipoComponente
                                                            AND cn.NoParteComponentePadre='".$idComponente."'");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                            ?>
                        <td align='center' scope='row'> <a href='#' onclick='dealleComponentes("<?php echo $detalle; ?>","ComponentesNecesarios", "<?php echo $rs['NoParteComponente']; ?>","<?php echo $idComponente?>","<?php echo $same_page ?>");
                            return false;' title='Editar Registro' ><img src="resources/images/Buscar.png" style="width: 24px; height: 24px;"/></a></td>
                        <td align='center' scope='row'> <a href='#' onclick='eliminraRegDetalle("<?php echo $controlador . "?id=" . $rs['NoParteComponentePadre'] . "&id2=" . $rs['NoParteComponente'] ?>","ComponentesNecesarios", "<?php echo $rs['NoParteComponentePadre']; ?>", "<?php echo $same_page; ?>");
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

