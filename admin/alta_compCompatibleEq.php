<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/CompCompatiblesEq.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_componentesCompatiblesEq.php";
$noComponente = "";
$read = "";
$soportado = "";
$idComponente = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_compCompatiblesEq.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new CompCompatiblesEq();
            if (isset($_POST['idEqipo']) && isset($_POST['id2'])) {
                $idComponente = $_POST['id2'];
                $obj->getRegistroById($_POST['idEqipo'], $_POST['id2']);
                $read = "disabled";
                $noComponente = $obj->getNoParteComponente();
                $soportado = $obj->getSoportado();
                
            }
            ?>
            <fieldset style="width: 95%; ">
                <legend>Componentes compatibles</legend>
                <form id="formCompatible" name="formCompatible" action="/" method="POST">
                    <table id="componenteCompatible">
                        <tr>
                            <td><label for="componentesComp1">Componente</label></td>
                            <td>
                                <select id="componentesComp1" name="componentesComp1" style='width: 250px'>
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
                            <td><label for="soportado">Soportado</label></td>
                            <td><input type="text" name="soportado1" id="soportado1" value="<?php echo $soportado; ?>" style='width: 80px'/></td>
                            <td> <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='nuevoComponente();' style="float: right; cursor: pointer;" /></td>
                            <td> <img class="imagenMouse" src="resources/images/Erase.png" title="Nuevo" onclick='eliminarComponente();' style="float: right; cursor: pointer;" /></td>
                        </tr>
                    </table>
                   <input type="submit" class="boton" value="Guardar" />
                   <input type="submit" class="boton" value="Cancelar" onclick="regresarListaEq('<?php echo $pagina_lista; ?>','ComponentesCompatibles');
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