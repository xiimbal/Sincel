<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/CentroCostoReal.class.php");
$cc = new CentroCostoReal();
if (isset($_GET['clave']) && $_GET['clave'] != "") {
    $cc->setId_cc($_GET['clave']);
    $cc->getRegistrobyID();
}
?>
<script type="text/javascript" src="../resources/js/paginas/ventas/alta_cc.js"></script>
<form id="form_cc" name="form_cc" >
    <table>
        <tr>
            <td><label for="nombre">Nombre</label></td>
            <td><input type="text" id="nombre" name="nombre" value="<?php
                if (isset($_GET['clave']) && $_GET['clave'] != "") {
                    echo $cc->getNombre();
                }
                ?>"/></td>
        </tr>
        <tr>
            <td><label for="localidades">Localidades</label></td>
            <td>
                <select id="localidades" name="localidades[]" class="filtro" multiple="multiple">
                    <?php
                    $catalogo = new Catalogo();
                    if (isset($_GET ['clave']) && $_GET['clave'] != "") {
                        $result = $catalogo->obtenerLista("SELECT c.ClaveCentroCosto AS ID, c.Nombre AS Nombre,IF(ISNULL(c.id_cr),'0','1') AS checked FROM c_centrocosto AS c
                        WHERE (ISNULL(c.id_cr) OR c.id_cr='" . $_GET['clave'] . "') AND c.ClaveCliente='" . $_GET['id'] . "' ORDER BY checked DESC, c.Nombre");
                        while ($rs = mysql_fetch_array($result)) {
                            $checked = "";
                            if ($rs['checked'] == 1) {
                                $checked = "selected";
                            }
                            echo "<option value='" . $rs['ID'] . "' " . $checked . ">" . $rs['Nombre'] . "</option>";
                        }
                    } else {
                        $result = $catalogo->obtenerLista("SELECT c.ClaveCentroCosto AS ID, c.Nombre AS Nombre FROM c_centrocosto AS c
                        WHERE ISNULL(c.id_cr) AND c.ClaveCliente='" . $_GET['id'] . "' ORDER BY c.Nombre");
                        while ($rs = mysql_fetch_array($result)) {
                            echo "<option value='" . $rs['ID'] . "'>" . $rs['Nombre'] . "</option>";
                        }
                    }
                    ?>
                </select>                        
            </td>
        </tr>
        <tr>
            <td><label for="Moroso">Moroso:</label></td>
            <td>           
                <?php
                $s = "";
                if ($cc->getMoroso() == "1") {
                    $s = "checked";
                }
                echo "<input type=\"checkbox\" value=\"1\" name=\"Moroso\" id=\"Moroso\" " . $s . "/>";
                ?>
            </td>
        </tr>
    </table>
    <?php
    if (isset($_GET ['clave']) && $_GET['clave'] != "") {
        echo "<input type=\"hidden\" id=\"clave\" name=\"clave\" value=\"" . $_GET['clave'] . "\"/>";
    }
    ?>
    <input type="hidden" id="cliente" name="cliente" value="<?php echo $_GET['id']; ?>"/>
    <input type="submit" id="submit_equipo" name="submit_equipo" class="boton" value="Guardar" style="margin-left: 30%;"/>
    <input type="button" id="cancelar" name="cancelar" class="boton" value="Cancelar" style="margin-left: 30%;" onclick="cambiarContenidos('tabla_cc.php?id=<?php echo $_GET['id']; ?>', 'Centro de costo');"/>
</form>