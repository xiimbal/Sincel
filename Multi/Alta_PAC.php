<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/PAC.class.php");
$pac = new PAC();
if (isset($_GET['id']) && $_GET['id'] != "") {
    $pac->setId_pac($_GET['id']);
    $pac->getRegistrobyID();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/Alta_pac.js"></script>
<form id="formpac">
    <fieldset>
        <table style=" width:100%">
            <tr>
                <td >Nombre PAC:</td>
                <td><input name="nombre" type="text" maxlength="150" id="nombre"  style="width:200px;" value="<?php echo $pac->getNombre()?>"/></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td >Usuario:</td>
                <td><input name="usuario" type="text" maxlength="150" id="usuario"  style="width:200px;" value="<?php echo $pac->getUsuario()?>"/></td>
                <td >Password:</td>
                <td><input name="password" type="text" maxlength="150" id="password"  style="width:200px;" value="<?php echo $pac->getPassword()?>"/></td>
            </tr>
            <tr>
                <td >Dir Timbre:</td>
                <td><input name="dir_timbre" type="text" maxlength="150" id="dir_timbre" style="width:200px;" value="<?php echo $pac->getDireccion_timbrado()?>"/></td>
                <td >Dir Cancelación:</td>
                <td><input name="dir_cancelacion" type="text" maxlength="150" id="dir_cancelacion" style="width:200px;" value="<?php echo $pac->getDireccion_cancelacion()?>"/></td>
            </tr>
        </table>
    </fieldset>
    <?php if (isset($_GET['id']) && $_GET['id'] != "") {
        ?>
        <input type="hidden" id="id" name="id" value="<?php echo $_GET['id']; ?>"/>
        <?php }
    ?>
    <br />
    <table style=" width:100%; text-align:center">
        <tr>
            <td>
                <input type="submit" class="boton" name="Guardar" value="Guardar"  id="Guardar" />
            </td>
            <td>
                <input type="button" onclick="cambiarContenidos('Multi/lista_pac.php', 'PAC');
                        return false;" class="boton" name="Cancelar" value="Cancelar" id="Cancelar" />
            </td>
        </tr>
    </table>
</form>