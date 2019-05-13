<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/CFDI.class.php");
$cfdi = new CFDI();
if (isset($_GET['id']) && $_GET['id'] != "") {
    $cfdi->setId_Cfdi($_GET['id']);
    $cfdi->getRegistrobyID();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/Alta_csd.js"></script>
<form id="formcsd">
    <table style=" width:100%">
        <tr>
            <td>
                <table style=" width:100%">
                    <tr>
                        <td style=" width:140px"> Nombre:</td>
                        <td><input name="nombre" type="text" maxlength="150" id="nombre" value="<?php echo $cfdi->getNombre() ?>" style="width:200px;" /></td>
                    </tr>
                </table>
            </td>
            <td>
                &nbsp;</td>
            <td style=" vertical-align:top;">
            </td>
        </tr>
    </table>
    <br />
    <fieldset>
        <legend>Archivos<br /></legend>
        <table >
            <?php if (!isset($_GET['id'])) { ?>
            <tr>
                <td>
                    CSD:<br />
                </td>
                <td>
                    <input id="csd" type="file" name="csd" />
                </td>
            </tr>
            <tr>
                <td >
                    Archivo Key:<br />
                </td>
                <td>
                    <input id="key" type="file" name="key" />
                </td>
            </tr>
            <tr>
                <td >
                    Archivo Key PEM:<br />
                </td>
                <td>
                    <input id="pem" type="file" name="pem" />
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td style=" width:140px"> Contraseña:</td>
                <td><input name="pass" type="text" maxlength="150" id="pass" value="<?php echo $cfdi->getCsd_password() ?>" style="width:200px;" /></td>
            </tr>
            <tr>
                <td style=" width:140px"> No Certificado:</td>
                <td><input name="certificado" type="text" maxlength="150" id="certificado" value="<?php echo $cfdi->getNoCertificado() ?>" style="width:200px;" /></td>
            </tr>
            <tr>
                <td style=" width:140px"> No SAT:</td>
                <td><input name="nosat" type="text" maxlength="150" id="nosat" value="<?php echo $cfdi->getNoSAT() ?>" style="width:200px;" /></td>
            </tr>
        </table>
        <?php if (isset($_GET['id']) && $_GET['id']) { ?>
            <input type="hidden" id="id" name="id" value="<?php echo $_GET['id'] ?>">
        <?php } ?>
    </fieldset>
    <table style=" width:100%; text-align:center">
        <tr>
            <td>
                <input type="submit" class="boton" name="Guardar" value="Guardar"  id="Guardar" />
            </td>
            <td>
                <input type="button" onclick="cambiarContenidos('Multi/lista_cfdi_archivos.php', 'CFDI');
                        return false;" class="boton" name="Cancelar" value="Cancelar" id="Cancelar" />
            </td>
        </tr>
    </table>
</form>