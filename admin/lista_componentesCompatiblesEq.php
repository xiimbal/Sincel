<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/CompCompatiblesEq.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_CompCompatiblesEq.php";
$alta = "admin/alta_compCompatibleEq.php";
$detalle="admin/alta_componentes.php";
$idEquipo = $_POST['idEquipo'];
$same_page = "admin/lista_componentesCompatiblesEq.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_compCompatiblesEq.js"></script>
    </head>
    <body>
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend>Componentes Compatibles</legend>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='altaDetalle("<?php echo $alta; ?>","ComponentesCompatibles", "<?php echo $idEquipo; ?>");' style="float: right; cursor: pointer;" />  
                <br/><br/><br/>
                <table id="tEquipoCompat" class="tabla_datos" style="width: 100%">
                    <thead>
                        <tr>
                            <td>Tipo</td><td>No. Parte</td><td>Modelo</td><td>Descripción</td><td>Soportado</td><td></td><td></td><td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                          $catalogo= new Catalogo();  
                           $query = $catalogo->obtenerLista("SELECT ecc.NoParteComponente,ecc.NoParteEquipo,tc.Nombre,c.Modelo,c.Descripcion,ecc.Soportado
                                                                FROM k_equipocomponentecompatible ecc,c_equipo e,c_componente c,c_tipocomponente tc
                                                                WHERE ecc.NoParteComponente=c.NoParte
                                                                AND ecc.NoParteEquipo=e.NoParte
                                                                AND c.IdTipoComponente=tc.IdTipoComponente
                                                                AND ecc.NoParteEquipo='".$idEquipo."'");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['NoParteComponente'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['Soportado'] . "</td>";
                            ?>
                        <td align='center' scope='row'> <a href='#' onclick='editarDetalle("<?php echo $alta; ?>","ComponentesCompatibles", "<?php echo $rs['NoParteEquipo']; ?>", "<?php echo $rs['NoParteComponente']; ?>");
                            return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>
                        <td align='center' scope='row'> <a href='#' onclick='dealleComponentes("<?php echo $detalle; ?>","ComponentesCompatibles", "<?php echo $rs['NoParteComponente']; ?>","<?php echo $idEquipo?>","<?php echo $same_page ?>");
                            return false;' title='Ver componente' ><img src="resources/images/Buscar.png" style="width: 24px; height: 24px;"/></a></a></td>
                        <td align='center' scope='row'> <a href='#' onclick='eliminraRegDetalle("<?php echo $controlador . "?id=" . $rs['NoParteEquipo'] . "&id2=" . $rs['NoParteComponente'] ?>","ComponentesCompatibles", "<?php echo $rs['NoParteEquipo']; ?>", "<?php echo $same_page; ?>");
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
