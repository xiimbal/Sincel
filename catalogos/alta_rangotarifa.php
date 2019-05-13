<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/RangoTarifa.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$permisos = new PermisosSubMenu();
$catalogo = new Catalogo();

$pagina_lista = "catalogos/lista_rangotarifa.php";
$id = "";
$Tarifa = "";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_rangotarifa.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $id=$_POST['id'];
                $obj = new RangoTarifa();
                if($obj->getRegistroById($_POST['id'])){
                    $id = $obj->getIdTarifa();
                    $Tarifa = $obj->getTarifa();
                }                
            }
            ?>
            <form id="formTarifa" name="formTarifa" action="/" method="POST">
                <table style="min-width: 95%;">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $Tarifa; ?>" maxlength="100"/></td>                        
                    </tr>                    
                </table><br/>
                <fieldset>
                    <legend>Rangos</legend>
                    <table id="tabla_detalles">
                        <?php
                            $result = null;
                            $contador = 0;
                            if(!empty($id)){
                                $result = $catalogo->obtenerLista("SELECT IdDetalleTarifa,RangoInicial,RangoFinal,Costo FROM `k_tarifarango` WHERE IdTarifa = $id;");
                            }
                            if($result != null && mysql_num_rows($result) > 0){
                                while($rs = mysql_fetch_array($result)){
                                    echo "<tr id='fila_detalle_" . $contador . "'>"
                                        . "<td>Rango Inicial:</td>"
                                        . "<td><input type='number' step='any' name='r_inicial_".$contador."' id='r_inicial_".$contador."' value='".$rs['RangoInicial']."'></td>"
                                        . "<td>Rango Final:</td>"
                                        . "<td><input type='number' step='any' name='r_final_".$contador."' id='r_final_".$contador."' value='".$rs['RangoFinal']."'></td>"
                                        . "<td>Costo:</td>"
                                        . "<td><input type='number' step='any' name='costo_".$contador."' id='costo_".$contador."' value='".$rs['Costo']."'></td>"
                                        . "<td>"
                                        . "<input type='hidden' id='id_$contador' name='id_$contador' value='".$rs['IdDetalleTarifa']."'/>"
                                        . "<a href='#' id='add_" . $contador . "' onclick='agregarDetalle(); return false;' title='Agrega otro detalle'><img src='resources/images/add.png' /></a>";
                                        if($contador > 0){
                                            echo "<a href='#' id='delete_".$contador."' onclick='eliminarDetalle(".$contador."); return false;' title='Elimina este detalle'><img src='resources/images/Erase.png' /></a>";
                                        }
                                        echo "</td>"
                                        . "</tr>";
                                        $contador++;
                                }
                            }else{
                                echo "<tr id='fila_detalle_" . $contador . "'>"
                                        . "<td>Rango Inicial:</td>"
                                        . "<td><input type='number' step='any' name='r_inicial_".$contador."' id='r_inicial_".$contador."' value=''></td>"
                                        . "<td>Rango Final:</td>"
                                        . "<td><input type='number' step='any' name='r_final_".$contador."' id='r_final_".$contador."' value=''></td>"
                                        . "<td>Costo:</td>"
                                        . "<td><input type='number' step='any' name='costo_".$contador."' id='costo_".$contador."' value=''></td>"
                                        . "<td>"
                                        . "<input type='hidden' id='id_$contador' name='id_$contador' value=''/>"
                                        . "<a href='#' id='add_" . $contador . "' onclick='agregarDetalle(); return false;' title='Agrega otro detalle'><img src='resources/images/add.png' /></a>"
                                        . "</td>"
                                        . "</tr>";
                                        $contador++;
                            }
                        ?>
                    </table>
                    <input type="hidden" id="TotalDetalles" name="TotalDetalles" value="<?php echo $contador; ?>"/>
                </fieldset>                
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                echo "<input type='hidden' id='id' name='id' value='$id'/> ";
                echo "<input type='hidden' id='numero_permisos' name='numero_permisos' value='" . $contador . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>
