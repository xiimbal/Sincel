<?php
session_start();
include_once("WEB-INF/Classes/Catalogo.class.php");
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT
	u.Nombre AS Nombre,
	u.ApellidoPaterno AS Appat,
	u.ApellidoMaterno AS Apmat,
	u.correo AS Correo,
	u.Loggin AS Username
FROM
	c_usuario AS u
WHERE u.IdUsuario=" . $_SESSION['idUsuario']);
if ($rs = mysql_fetch_array($query)) {
    ?>
<script type="text/javascript" src="resources/js/datosUsuario.js"></script>
    <br/><br/>
    <form id="formDatosUsuario">
        <table style="width: 100%;">
            <tr>
                <td>Nombre<span class="obligatorio"> *</span></td>
                <td>
                    <input type="text" id="Nombre" name="Nombre" value="<?php echo $rs['Nombre']?>"/>
                </td>
                <td>Apellido Paterno<span class="obligatorio"> *</span></td>
                <td>
                    <input type="text" id="Appat" name="Appat" value="<?php echo $rs['Appat']?>"/>
                </td>
                <td>Apellido Materno<span class="obligatorio"> *</span></td>
                <td>
                    <input type="text" id="Apmat" name="Apmat" value="<?php echo $rs['Apmat']?>"/>
                </td>
            </tr>
            <tr>
                <td>Correo<span class="obligatorio"> *</span></td>
                <td>
                    <input type="text" id="Correo" name="Correo" value="<?php echo $rs['Correo']?>"/>
                </td>
                <td>Nombre de usuario<span class="obligatorio"> *</span></td>
                <td>
                    <input type="text" id="Username" name="Username" value="<?php echo $rs['Username']?>" readonly="readonly"/>
                </td>
                <td>Desea cambiar la contraseña</td>
                <td>
                    <input type="checkbox" id="Checkc" name="Checkc" value="1"/>
                </td>
            </tr>
            <tr>
                <td>Contraseña</td>
                <td>
                    <input type="password" id="Contra" name="Contra" disabled="disabled"/>
                </td>
                <td>Confirme contraseña</td>
                <td>
                    <input type="password" id="Contras" name="Contras" disabled="disabled"/>
                </td>
                <td></td><td></td>
            </tr>
        </table>
        <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
        <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar"/>
    </form>
<?php } ?>