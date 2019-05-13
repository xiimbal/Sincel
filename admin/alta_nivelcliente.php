<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/NivelCliente.class.php");
$pagina_lista = "admin/lista_nivelcliente.php";

$IdNivelCliente = '';
$NivelCliente = '';
$Activo = "checked='checked'";


?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_nivelcliente.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {                
                $obj = new NivelCliente();
                if($obj->getRegistroById($_POST['id'])){
                    $IdNivelCliente = $obj->getIdNivelCliente();
                    $NivelCliente = $obj->getNivelCliente();                    
                    if ($obj->getActivo() == "0") {
                        $Activo = "";
                    }
                }                                
            }
            ?>
            <form id="formNivelCliente" name="formNivelCliente" action="/" method="POST">
                <table style="min-width: 70%">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $NivelCliente; ?>"/></td>                        
                    </tr>    
                    <tr>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $Activo; ?>/>Activo</td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                    echo "<input type='hidden' id='id' name='id' value='" . $IdNivelCliente . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>