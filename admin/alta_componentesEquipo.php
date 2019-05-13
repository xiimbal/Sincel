<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ComponentesEquipo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_componetesEquipo.php";
$noComponente = "";
$read = "";
$instalado = "";
$idComponente = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_componentesEquipo.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new ComponentesEquipo();
            if (isset($_POST['idEqipo']) && isset($_POST['id2'])) {
                $idComponente = $_POST['id2'];
                $obj->getRegistroById($_POST['idEqipo'], $_POST['id2']);
                $read = "disabled";
                $noComponente = $obj->getNoPartesComponentes();
                $instalado=$obj->getInstalado();
            }
            ?>
            <fieldset style="width: 95%; ">
                <legend>Componentes del equipo</legend>
                <form id="formCompoEq" name="formCompoEq" action="/" method="POST">
                    <table>
                        <tr>
                            <td><label for="componentes">Componente</label></td>
                            <td>
                                <select id="componentes" name="componentes" style="width: 250px;">
                                    <?php
                                    $catalogo = new Catalogo();

                                    $query = $catalogo->getListaAlta("c_componente", "Modelo");
                                    echo "<option value='0' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($noComponente != "" && $noComponente == $rs['NoParte']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['NoParte'] . ">" . $rs['NoParte'] . " - " . $rs['Modelo'] . " - ".$rs['Descripcion']."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><label for="instalado">Instalado</label></td>
                            <td><input type="text" name="instalado" id="instalado" value="<?php echo $instalado; ?>"/></td>
                           </tr>
                    </table>
                   <input type="submit" class="boton" value="Guardar" />
                   <input type="submit" class="boton" value="Cancelar" onclick="regresarListaEq('<?php echo $pagina_lista; ?>','ComponentesEquipo');
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