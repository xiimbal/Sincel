<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Productos_Genesis.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$nombre = "";
$id = "";
$precio = "";
if(isset($_POST['id'])){   
    $id = $_POST['id'];
    $Productos_Genesis = new Productos_Genesis();
    $Productos_Genesis->setId($id);
    $Productos_Genesis->getRegistro();
    $nombre=$Productos_Genesis->getNombre();
    $precio=$Productos_Genesis->getId_precio();
}
$pagina_lista="admin/lista_Productos_Genesis.php";
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/alta_Producto_Genesis.js"></script>
<form id="formProdGenesis" name="formProdGenesis" action="/" method="POST">
    <table style="width: 50%;">
        <tr>
            <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
            <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>
            <td><label for="precio">Precio</label><span class="obligatorio"> *</span></td>
            <td><select id="precio" name="precio">
                    <option value="">Selecciona el precio</option>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT c_prod_pre_genesis.Nombre AS Nombre ,c_prod_pre_genesis.Id_Precio AS ID FROM c_prod_pre_genesis ORDER BY Nombre");
                    if ($precio != "") {
                        while ($rs = mysql_fetch_array($query)) {
                            if ($rs['ID'] == $precio) {
                                echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                            } else {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                            }
                        }
                    } else {
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                        }
                    }
                    ?>
                </select></td>
        </tr>                      
    </table>
    <input type="submit" class="boton" value="Guardar" />
    <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
        return false;"/>
           <?php
           if ($id != "") {
               echo "<input type='hidden' id='id' name='id' value='" . $id . "'/> ";
           }
           ?>
</form>