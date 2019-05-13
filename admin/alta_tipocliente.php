<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/TipoCliente.class.php");
$pagina_lista = "admin/lista_tipocliente.php";

$IdTipoCliente = '';
$Nombre = '';
$Descripcion = '';
$Orden = '';
$IdVersion = '';
$Activo = "checked='checked'";
$Radio = "";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_tipocliente.js"></script>        
    </head>
    <body>
        <div class="principal bg-white">
            <?php
            if (isset($_POST['id'])) {                
                $obj = new TipoCliente();
                if($obj->getRegistroById($_POST['id'])){
                    $IdTipoCliente = $obj->getIdTipoCliente();
                    $Nombre = $obj->getNombre();
                    $Descripcion = $obj->getDescripcion();
                    $Orden = $obj->getOrden();
                    $IdVersion = $obj->getIdVersion();
                    $Radio = $obj->getRadio();
                    if ($obj->getActivo() == "0") {
                        $Activo = "";
                    }
                }                                
            }
            ?>
            <form id="formTipoCliente" name="formTipoCliente" action="/" method="POST">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="nombre">Nombre</label>
                        <span class="obligatorio"> *</span>
                        <input class="form-control" type="text" id="nombre" name="nombre" value="<?php echo $Nombre; ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="descripcion">Descripci&oacute;n</label>
                        <span class="obligatorio"> *</span>
                        <input class="form-control" type="text" id="descripcion" name="descripcion" value="<?php echo $Descripcion; ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="radio">Radio de búsqueda</label>
                        <span class="obligatorio"> *</span>
                        <input class="form-control" type="number" id="radio" name="radio" value="<?php echo $Radio; ?>" step="any"/>
                    </div>
                    <div class="form-group col-md-3 p-3">
                        <label for="activo">Activo</label>
                        <input type="checkbox" name="activo" id="activo" <?php echo $Activo; ?>/>
                    </div>
                    <div class="form-group col-md-12">
                        <input type="submit" class="btn btn-success" value="Guardar" />
                        <input type="submit" class="btn btn-danger" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>
                        <?php
                        echo "<input type='hidden' id='id' name='id' value='" . $IdTipoCliente . "'/> ";
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>
