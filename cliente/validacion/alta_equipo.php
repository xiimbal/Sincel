<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if(!isset($_POST['idTicket'])){
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/Inventario.class.php");
include_once("../../WEB-INF/Classes/Equipo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$permisos_grid = new PermisosSubMenu();

$id = "";
$NoSerieEquipo = "";
$NoParteEquipo = "";
$Ubicacion = "";
$permiso = true;

if(isset($_GET['Serie'])){
    $NoSerieEquipoGET = $_GET['Serie'];
    $ModeloCodificado = $_GET['Modelo'];
    $ModeloEquipoGET = str_replace("__XX__", " ", $_GET['Modelo']);        
    $NoSerieEquipo = $NoSerieEquipoGET;
    $ModeloEquipo = $ModeloEquipoGET;                      
}

$inventario = new Inventario();
$equipo = new Equipo();

if(isset($_POST['id'])){
    if($inventario->getRegistroById($_POST['id'])){
        $id = $_POST['id'];
        $NoSerieEquipo = $inventario->getNoSerie();
        $NoParteEquipo = $inventario->getNoParteEquipo();
        $Ubicacion = $inventario->getUbicacion();
    }
}

if(!$inventario->getRegistroById($NoSerieEquipo) || !$equipo->getRegistroByModelo($ModeloEquipo)){
    $permiso = false;
}else{
    $NoParteEquipo = $equipo->getNoParte();
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_equipo.js"></script>
    </head>
    <body>        
        <fieldset>
            <legend>Equipo</legend>
            <form id="formEquipo" name="formEquipo" action="/" method="POST">
                <input type="hidden" name="no_parte2" id="no_parte2" value="<?php echo $NoParteEquipo;  ?>"/>
                <table style="width: 100%">                
                    <tr>
                        <td><label for="no_serie2">No. serie:</label></td>
                        <td><input type="text" id="no_serie2" name="no_serie2" class="complete" value="<?php echo $NoSerieEquipo; ?>" readonly="readonly"/></td>
                    </tr>                    
                    <tr>
                        <td><label for="ubicacion2">Ubicaci&oacute;n:</label></td>
                        <td><input type="text" id="ubicacion2" name="ubicacion2" class="complete" value="<?php echo $Ubicacion; ?>"/></td>
                    </tr>                
                </table>
                <input type="submit" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('equipo2', '../cliente/validacion/lista_equipo.php?NoSerie=<?php echo $NoSerieEquipoGET; ?>&Modelo=<?php echo $ModeloCodificado; ?>', '<?php echo $_POST['idTicket']; ?>', null);return false;"/>
                <?php
                    if(!$permiso){
                        echo "<b>El modelo y/o el No. de serie no están registrados.</b>";
                    }else{
                ?>                
                <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16) || empty($id)){  ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <?php
                    }
                ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>
                <input type="hidden" name="independiente" id="independiente" value="true"/>
                <br/><br/><br/><br/>
            </form>
        </fieldset>        
    </body>
</html>