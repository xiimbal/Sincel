<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Contacto.class.php");

if(isset($_GET['ClaveCliente'])){
    $ClaveCliente = $_GET['ClaveCliente'];
    $contacto = new Contacto();
    
    //Obtenemos todos los contactos asociados al cliente y a sus localidades.
    $result = $contacto->getTodosContactosCliente($ClaveCliente);
    
    $cabeceras = array("Localidad", "Nombre", "Correo electrónico","Celular","Tipo Contacto"," "," ");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/ventas/alta_contacto.js"></script>
    </head>
    <body>
        <br/>
        <div id="mensaje_contacto2"></div>
        <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo" onclick='cambiarContenidosContacto("../cliente/validacion/alta_contacto.php?Cliente=<?php echo $ClaveCliente; ?>", "Nuevo Contacto");' style="float: right; cursor: pointer;" /> 
        <br/><br/>
        <table id="tContacto">
        <thead><tr>
        <?php
            for ($i = 0; $i < (count($cabeceras)); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
        ?>
        </tr></thead>
        <tbody>
        <?php
            while ($rs = mysql_fetch_array($result)) {
                echo "<tr>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['localidad'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Nombre'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['CorreoElectronico'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Celular'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['TipoContacto'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">"
                ?> <img class="imagenMouse" src="../resources/images/Modify.png" title="Nuevo" onclick='cambiarContenidosContacto("../cliente/validacion/alta_contacto.php?id=<?php echo $rs['IdContacto'];?>&Cliente=<?php echo $ClaveCliente; ?>", "Nuevo Contacto");' style="cursor: pointer;" /></td> 
                <?php 
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"eliminarContacto('".$rs['IdContacto']."','$ClaveCliente');
                        return false;\" title='Eliminar'><img src=\"../resources/images/Erase.png\" width=\"24\" height=\"24\"/></a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
        </table>
        <br/><br/>
        <div id="contenidos" style="max-width: 97%;">
            
        </div>
        <div id="contenidos_invisibles">           
        </div>
        <div id="loading_text">          
        </div>
    </body>
</html>
<?php
}else{
    echo "<b>Se ha producido un error</b> La clave del cliente no se pudo obtener";
}
?>


