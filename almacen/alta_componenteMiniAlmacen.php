<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ComponentesMiniAlmacen.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "almacen/lista_componentesMiniAlmacen.php";
$idminiAlmacen = $_POST["minialmacen"];
$idCliente = $_POST["cliente"];
$noParte = "";
$existencia = "";
$cantidadMinima = "";
$cantidadMaxima = "";
$tipoComponente = "";
$read = "";
$id = "";
if (isset($_POST['tipo']) && $_POST['tipo'] != "")
    $tipoComponente = $_POST['tipo'];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_ComponentesMinialmacen.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['idComponente'])) {

                $obj = new ComponentesMiniAlmacen();
                $obj->getRegistroById($_POST['minialmacen'], $_POST['idComponente']);
                $read = "disabled";
                $idminiAlmacen = $obj->getMinialmacen();
                $noParte = $obj->getNoParte();
                $existencia = $obj->getCantidadExistente();
                $cantidadMinima = $obj->getCantidadMinima();
                $cantidadMaxima = $obj->getCantidadMaxima();
                $id = "1";
                $catalogo1 = new Catalogo();
                $query1 = $catalogo1->obtenerLista("SELECT * FROM c_componente c WHERE c.NoParte='".$_POST['idComponente']."' ");
                if ($rs = mysql_fetch_array($query1)) {
                  $tipoComponente =$rs['IdTipoComponente'];
                }
            }
            ?>
            <form id="formCompMiniAlmacen" name="formCompMiniAlmacen" action="/" method="POST">
                <table style="width: 60%;">
                    <tr><td>Tipo de componente</td>
                        <td>
                            <select id='tipoComponente' name='tipoComponente' style='width: 180px;' onchange='verComponentesMiniAlmacen();' <?php echo $read?>>
                                <option value="0">Seleccione una opción</option>
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT * FROM c_tipocomponente tc WHERE tc.Activo=1 ORDER BY tc.Nombre ASC");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($tipoComponente != "" && $tipoComponente == $rs['IdTipoComponente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoComponente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <br/><br/>
                <table id='tablaComponente'>
                    <tr>
                        <td>Componente:<span class="obligatorio"> *</span></td>
                        <td>
                            <select name='componente1' id='componente1' style='width: 180px;' <?php echo $read?>>
                                <option value="0">Seleccione una opción</option>
                                <?php
                                if ($tipoComponente != "") {
                                    $catalogo1 = new Catalogo();
                                    $query1 = $catalogo1->obtenerLista("SELECT * FROM c_componente c WHERE c.IdTipoComponente=" . $tipoComponente . " AND c.Activo=1 ORDER BY c.Modelo ASC");
                                    while ($rs = mysql_fetch_array($query1)) {
                                        $s = "";
                                        if ($noParte != "" && $noParte == $rs['NoParte']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['NoParte'] . " " . $s . ">" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>

                        <td>Cantidad Existente: <span class="obligatorio"> *</span></td>
                        <td><input id='existente1' name='existente1' style='width: 50px' value='<?php echo $existencia ?>'/></td>                    
                        <td>Cantidad mínima:<span class="obligatorio"> *</span></td>
                        <td><input id='minima1' name='minima1' style='width: 50px' value='<?php echo $cantidadMinima ?>'/></td>                
                        <td>Cantidad máxima:<span class="obligatorio"> *</span></td>
                        <td><input id='maxima1' name='maxima1' style='width: 50px' value='<?php echo $cantidadMaxima ?>'/></td>
                        <td><img class="imagenMouse" src="resources/images/add.png" title="Otro Componente" onclick='otroComponente();' style="float: right; cursor: pointer;" />  </td>
                        <td><img class="imagenMouse" src="resources/images/Erase.png" title="Eliminar componente" onclick='deleteComponente()' style="float: right; cursor: pointer;" />  </td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="button" class="boton" value="Cancelar" onclick="regresarListaMinialmacen('<?php echo $pagina_lista; ?>', '<?php echo $idminiAlmacen ?>');"/>
                <input type='hidden' id='idminiAlmacen' name='idminiAlmacen' value='<?php echo $idminiAlmacen ?>'/>
                <input type='hidden' id='cliente' name='cliente' value='<?php echo $idCliente ?>'/>
                <input type='hidden' id='id' name='id' value='<?php echo $noParte ?>'/>
            </form>
        </div>
    </body>
</html>