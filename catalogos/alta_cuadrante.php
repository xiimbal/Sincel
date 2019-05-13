<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
//include_once("../WEB-INF/Classes/TFSCliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Cuadrante.class.php");
$pagina_lista = "catalogos/lista_cuadrante.php";

$idCuadrante = "";
$descripcion = "";
$latitud = "";
$longitud = "";
$activo = "checked='checked'";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_cuadrante.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Cuadrante();
                $obj->getRegistroById($_POST['id']);
                $idCuadrante = $obj->getIdCuadrante();
                $descripcion = $obj->getDescripcion();
                $latitud= $obj->getLatitud();
                $longitud= $obj->getLongitud();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
            <form id="formCuadrante" name="formCuadrante" action="/" method="POST">
                <table style="width: 95%;">
                    <tr>
                        
                        <td><label for="descripcion">Descripci&oacute;n</label><span class='obligatorio'> *</span></td>
                        <td><input type="text" id="descripcion" name="descripcion" cols="60" value="<?php echo $descripcion; ?>"></td>
                        <td>Latitud</td><td><input type="number" id="Latitud" name="Latitud" value="<?php echo $latitud ?>" step="any"></td>
                        <td>Longitud</td><td><input type="number" id="Longitud" name="Longitud" value="<?php echo $longitud ?>" step="any"></td>
                        <td>
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo
                        </td>
                    </tr>
                </table>
                <br/><br/>
                <input type="submit" name="submit" class="boton" value="Guardar" />                
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>                            
                       <?php
                       echo "<input type='hidden' id='idCuadrante' name='idCuadrante' value='" . $idCuadrante . "'/> ";
                       ?>

            </form>
        </div>
    </body>
</html>