<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//include_once("../WEB-INF/Classes/PartesDelComponenteC.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_EquipoCompatible.php";
$alta = "admin/alta_equipoCompatible.php";
$detalle = "admin/alta_componentes.php";
$idComponente = $_POST['idEquipo'];
$same_page = "admin/lista_equipoCompatible.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_equipoCompatible.js"></script> 
    </head>
    <body>
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend>Equipos Compatibles</legend>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='altaDetalleComponente("<?php echo $alta; ?>", "equipoCompatible", "<?php echo $idComponente; ?>","padre");' style="float: right; cursor: pointer;" />  
                <br/><br/><br/>
                <table id="tCompatible" class="tabla_datos" style="width: 100%">
                    <thead>
                        <tr>
                            <td>Equipo</td><td>Soportado</td><td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->obtenerLista("SELECT ec.NoParteComponente,ec.NoParteEquipo,c.Modelo AS componente,e.Modelo AS equipo,ec.Soportado
                                                            FROM c_componente c,k_equipocomponentecompatible ec,c_equipo e
                                                            WHERE ec.NoParteComponente=c.NoParte AND ec.NoParteEquipo=e.NoParte AND ec.NoParteComponente='".$idComponente."'");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['equipo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Soportado'] . "</td>";
                            ?>
                            <td align='center' scope='row'> <a href='#' onclick='eliminraRegDetalle("<?php echo $controlador . "?id=" . $rs['NoParteComponente'] . "&id2=" . $rs['NoParteEquipo'] ?>","equipoCompatible", "<?php echo $rs['NoParteComponente']; ?>", "<?php echo $same_page; ?>");
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

