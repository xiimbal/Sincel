<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_SimilaresEquipos.php";
$noParteSimilar = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_similaresEquipos.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php           
            
            if (isset($_POST['idEqipo']) && isset($_POST['id2'])) {
                $noParteSimilar = $_POST['id2'];                
            }
            ?>
            <fieldset style="width: 95%; ">
                <legend>Equipos Similares</legend>
                <form id="formSimilar" name="formSimilar" action="/" method="POST">
                    <table>
                        <tr>
                            <td><label for="equipoSimilar">Equipo</label></td>
                            <td>
                                <select id="equipoSimilar" name="equipoSimilar" style='width: 250px;'>
                                    <?php
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->getListaAlta("c_equipo", "Modelo");
                                    echo "<option value='' >Selecciona un equipo</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        if($_POST['idEqipo'] == $rs['NoParte']){
                                            continue;
                                        }
                                        $s = "";
                                        if ($noParteSimilar != "" && $noParteSimilar == $rs['NoParte']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['NoParte'] . " $s>" . $rs['Modelo'] . " - " . $rs['NoParte'] ."</option>";
                                    }
                                    ?>
                                </select>
                            </td>                            
                        </tr>
                    </table>
                   <input type="submit" class="boton" value="Guardar" />
                   <input type="submit" class="boton" value="Cancelar" onclick="regresarListaEq('<?php echo $pagina_lista; ?>','EquiposSimiliares');
                return false;"/>
                   <?php
                    echo "<input type='hidden' id='idE' name='idE' value='" . $_POST['idEqipo'] . "'/> ";
                    echo "<input type='hidden' id='idC' name='idES' value='" . $noParteSimilar . "'/> ";
                    ?>
                </form>
            </fieldset>

        </div>
    </body>
</html>