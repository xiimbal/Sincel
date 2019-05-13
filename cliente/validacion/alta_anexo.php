<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/Anexo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$permisos_grid = new PermisosSubMenu();
$id = "";
$fecha = "";
$NoContrato = "";
$diaCorte = "";
$disabled= "";

if(isset($_POST['id']) && !is_null($_POST['id'])){    
    $equipo = new Anexo();
    $equipo->getRegistroById($_POST['id']);
    $id = $_POST['id'];
    $diaCorte = $equipo->getDiaCorte();    
    $fecha = $equipo->getFechaElaboracion();
    $NoContrato = $equipo->getNoContrato();   
    $disabled= "readonly='readonly'";
}

if(isset($_GET['Nuevo'])){
    $disabled= "";
}

if (isset($_GET['id'])) {
    $ClaveGET = $_GET['id'];
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_anexo.js"></script>
    </head>
    <body>
        <fieldset>
            <legend>Anexo</legend>
            <form id="formAnexo" name="formAnexo" action="/" method="POST">
                <input type="hidden" id="anexo_contrato" name="anexo_contrato" value="<?php echo $_GET['id']; ?>"/>
                <input type="hidden" id="clave_cc_contrato" name="clave_cc_contrato" />
                <table style="width: 100%">
                    <tr>
                        <td><label for="fecha_anexo2">Fecha</label></td>
                        <td><input type="text" id="fecha_anexo2" name="fecha_anexo2" class="complete fecha" value="<?php echo $fecha; ?>"/></td>
                        <td><label for="dia_corte">Día de corte</label></td>
                        <td><input type="text" id="dia_corte" name="dia_corte" class="complete" value="<?php echo $diaCorte; ?>"/></td>
                    </tr>                    
                </table>
                <input type="submit" id="cancelar_anexo" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('anexo2', '../cliente/validacion/lista_anexo.php?idCliente=<?php echo $_GET['idCliente'] ?>',  <?php  if(isset($_POST['idTicket'])){ echo "'".$_POST['idTicket']."'";} else{ echo "null"; } ?>, '<?php echo $ClaveGET; ?>' , null);return false;"/>
                <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16) || empty($id)){  ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>        
                <input type="hidden" name="independiente" id="independiente" value="true"/>
                <br/><br/><br/><br/>                                
            </form>
            <a href="#" id="liga_parametros" name="liga_parametros" onclick="mostrarParametros(); return false;">Mostrar par&aacute;metros de lecturas</a>
            <form id="FormLectura" name="FormLectura">
                <div id="paramatros_lecturas" style="display: none;"></div>
            </form>
        </fieldset>        
    </body>
</html>