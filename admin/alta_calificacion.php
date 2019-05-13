<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Cliente.class.php");
$pagina_lista = "admin/lista_calificacion.php";

$Id_calificacion = '';
$ClaveCliente = '';
$Calificacion = '';
$Titulo = '';
$Mensaje = '';
$Foto = '';
$IdUsuario = '';



?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_calificacion.js"></script>       
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {                
                $obj = new Cliente();
                if($obj->getCalificacionById($_POST['id'])){
                    $Id_calificacion = $obj->getId_calificacion();
                    $ClaveCliente = $obj->getClaveCliente();
                    $Calificacion = $obj->getCalificacion();
                    $Titulo = $obj->getTitulo();
                    $Mensaje = $obj->getMensaje();
                    $Foto = $obj->getFoto();
                    $IdUsuario = $obj->getIdUsuario();
                }                                
            }
            ?>
            <form id="formCalificacion" name="formCalificacion" action="/" method="POST" enctype="multipart/form-data" class="formulario" >
                <table style="min-width: 50%">
                    <tr>
                        <td><label for="cliente">Negocio</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="cliente" name="cliente" style="width:200px">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT NombreRazonSocial, ClaveCliente FROM c_cliente WHERE Activo = 1 ORDER BY NombreRazonSocial;");
                                echo "<option value='' >Selecciona un negocio</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($ClaveCliente == $rs['ClaveCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>                        
                    </tr>                       
                    <tr>
                        <td><label for="calificacion">Calificación</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="number" id="calificacion" name="calificacion" value="<?php echo $Calificacion; ?>" style="width:200px" min="0" max="10"/>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="titulo">Título</label></td>
                        <td>
                            <input type="text" id="titulo" name="titulo" value="<?php echo $Titulo; ?>" style="width:200px"/>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="mensaje">Mensaje</label></td>
                        <td>
                            <textarea id="mensaje" name="mensaje" style="width: 300px; resize: none;">
                                <?php echo $Mensaje; ?>
                            </textarea>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="foto">Foto</label></td>
                        <td>
                            <input type="file" id="foto" name="foto" />
                            <?php 
                                if (isset($_POST['id']) && $Foto != "") {  
                                    echo "<div id='preview'>";
                                        echo "<input type='image' src='$Foto' style='width:200px; height:200px;' onclick='return false;'/>";
                                    echo "</div>";
                                }
                            ?>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="usuario">Usuario</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="usuario" name="usuario" style="width:200px;">
                                <?php                                
                                $query = $catalogo->obtenerLista("SELECT IdUsuario, Loggin, 
                                    CONCAT(Nombre,' ',ApellidoPaterno, ApellidoMaterno) AS Usuario FROM `c_usuario` WHERE Activo = 1 
                                    ORDER BY Loggin;");
                                echo "<option value='' >Selecciona el usuario</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($IdUsuario == $rs['IdUsuario']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['Loggin'] . " (".$rs['Usuario'].")</option>";
                                }
                                ?>
                            </select>
                        </td>                        
                    </tr>                    
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                    echo "<input type='hidden' id='id' name='id' value='" . $Id_calificacion . "'/> ";
                    echo "<input type='hidden' id='empresa' name='empresa' value='" . $_SESSION['idEmpresa'] . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>