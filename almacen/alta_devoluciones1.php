<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/AlmacenConmponente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");

$catalogo = new Catalogo();
$usuario = new Usuario();

//almacen filtro
$almacenPredeterminado = "";
$consulta = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us WHERE ra.IdUsuario='" . $usuario->getId() . "' 
    AND (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
$result = $catalogo->obtenerLista($consulta);

if (mysql_num_rows($result) == 0) {//Si no tiene almacen predeterminado
    $consulta = "SELECT * FROM c_almacen a WHERE (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 ORDER BY a.nombre_almacen ASC";
    $result = $catalogo->obtenerLista($consulta);
}

$almacenes = "<option value=''>Selecciona el almacén</option>";
while ($rs = mysql_fetch_array($result)) {
    $s = "";
    if ($almacenPredeterminado != "" && $almacenPredeterminado = $rs['id_almacen']) {
        $s = "selected='selected'";
    }
    $almacenes .= "<option value='" . $rs['id_almacen'] . "' $s>" . $rs['nombre_almacen'] . "</option>";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_devoluciones.js"></script>                
    </head>
    <body>
        <div class="principal">                        
            <form id="formDevoluciones" name="formDevoluciones" action="/" method="POST">
                <table style="min-width: 80%">                    
                    <tr>                        
                        <td><label for="almacen">Almacén</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select style="max-width: 250px;" id='almacen' name='almacen' class='filtro' onchange="cargarComponentesDeAlmacen('almacen','parte');">
                                <?php echo $almacenes; ?>
                            </select>
                        </td>    
                    </tr>
                    <tr>
                        <td><label for="parte">Modelo</label><span class="obligatorio"> *</span></td>
                        <td>                            
                            <select id='parte' name='parte' class='filtro' style='width: 250px; max-width: 250px;'>
                                <option value=''>Selecciona el componente</option>
                            </select>
                        </td>                        
                    </tr>                    
                    <tr>
                        <td>Cantidad a ingresar<span class="obligatorio"> *</span></td>
                        <td><input style="width: 160px;" type='number' id='cantidadExis' name='cantidadExis' min="1"/></td>
                    </tr>
                    <tr>
                        <td>Comentario<span class="obligatorio"> *</span></td>
                        <td colspan="3"><textarea style="width: 450px;height: 50px" id="comentario" name="comentario"></textarea></td>
                    </tr> 
                    <tr>
                        <td colspan="2">
                            <div style="color: blue; font-size: 12px;"> * En caso de ser una devolvión de componente, favor de ingresar el ticket con el que se había atendido</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            
                            <br/><label for="ticket_devolucion">Ticket devolución</label></td>
                        <td><input type="number" id="ticket_devolucion" name="ticket_devolucion" /></td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />               
            </form>
        </div>
    </body>
</html>
