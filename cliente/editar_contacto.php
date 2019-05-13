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

        <div class="table-responsive">
            <table id="tContacto" class="table">
                <thead class="thead-dark">
                    <tr>
                        <?php foreach ($cabeceras as $cabecera) echo "<th>" . $cabecer . "</th>"; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        while ($rs = mysql_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>" . $rs['localidad'] . "</td>";
                            echo "<td>" . $rs['Nombre'] . "</td>";
                            echo "<td>" . $rs['CorreoElectronico'] . "</td>";
                            echo "<td>" . $rs['Celular'] . "</td>";
                            echo "<td>" . $rs['TipoContacto'] . "</td>";
                            ?> 
                                <td>
                                    <a href="#" onclick='cambiarContenidosContacto("../cliente/validacion/alta_contacto.php?id=<?php echo $rs['IdContacto'];?>&Cliente=<?php echo $ClaveCliente; ?>", "Nuevo Contacto");'>
                                        <i class="fa fal-pencil-alt text-danger"></i>
                                    </a>
                                </td>
                                
                            <?php 
                            echo "<td><a href='#' onclick=\"eliminarContacto('".$rs['IdContacto']."','$ClaveCliente');
                                    return false;\" title='Eliminar'><img src=\"../resources/images/Erase.png\" width=\"24\" height=\"24\"/></a></td>";
                            echo "</tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <div id="contenidos" class="container-fluid">
            
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


