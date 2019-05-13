<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PartesEquipo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$noParteComponente = "";
$read = "";
$soportadoMax = "";
$idComponente = "";
$pagina_lista = "admin/lista_partesEquipo.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_PartesEquipo.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new PartesEquipo();
            if (isset($_POST['idEqipo']) && isset($_POST['id2'])) {
                $idComponente = $_POST['id2'];
                $obj->getRegistroById($_POST['idEqipo'], $_POST['id2']);
                $read = "disabled";
                $noParteComponente = $obj->getNoParteComponente();
                $soportadoMax = $obj->getSoportadoMax();
            }
            ?>
            <fieldset style="width: 95%; ">
                <legend>Partes del equipo</legend>
                <form id="formParteEq" name="formParteEq" action="/" method="POST">
                    <table>
                        <tr>
                            <td><label for="componenteInst">Componente</label></td>
                            <td>
                                <select id="componenteInst" name="componenteInst" style="width: 250px;">
                                    <?php
                                    $catalogo = new Catalogo();

                                    $query = $catalogo->getListaAlta("c_componente", "Modelo");
                                    echo "<option value='' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($noParteComponente != "" && $noParteComponente == $rs['NoParte']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['NoParte'] . ">" . $rs['NoParte'] . " - " . $rs['Modelo'] . " - ".$rs['Descripcion']."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><label for="soportado">Soportado Maximo</label></td>
                            <td><input type="text" name="soportado" id="soportado" value="<?php echo $soportadoMax; ?>"/></td>                       
                        </tr>
                    </table>
                    <input type="submit" class="boton" value="Guardar" />
                    <input type="submit" class="boton" value="Cancelar" onclick="regresarListaEq('<?php echo $pagina_lista; ?>', 'partesEquipo');
                return false;"/>
                           <?php
                           echo "<input type='hidden' id='idE' name='idE' value='" . $_POST['idEqipo'] . "'/> ";
                           echo "<input type='hidden' id='idC' name='idC' value='" . $idComponente . "'/> ";
                           ?>
                </form>
            </fieldset>
        </div>        
    </body>
</html>