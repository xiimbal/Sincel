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
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="almacen">Almacén <span class="obligatorio"> *</span></label>
                         <select class="custom-select" id='almacen' name='almacen' class='filtro' onchange="cargarComponentesDeAlmacen('almacen','parte');">
                                <?php echo $almacenes; ?>
                            </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="parte">Modelo <span class="obligatorio"> *</span></label>
                        <select  class="custom-select" id='parte' name='parte' class='filtro'>
                                <option value=''>Selecciona el componente</option>
                            </select>
                    </div>
                    <div class="form-group col-md-4"><label for="cantidadExis">Cantidad a ingresar<span class="obligatorio"> *</span></label>
                        
                        <input class="form-control" type='number' id='cantidadExis' name='cantidadExis' min="1"/>
                    </div>
                    
                </div>
                <div class="form-row">
                        <div class="form-group col-md-4">
                        Comentario<span class="obligatorio"> *</span>
                         <textarea id="comentario" class="form-control" name="comentario" rows="3"></textarea> 
                        </div>
                        <div class="form-group col-md-4">
                            <label for="ticket_devolucion">Ticket devolución</label>
                            <input id="ticket_devolucion" class="form-control" type="number" name="ticket_devolucion" />
                           
                        </div>
                        <div class="form-group col-md-4 p-3">
                              <p class="text-primary text-md-left"> * En caso de ser una devolvión de componente, favor de ingresar el ticket con el que se había atendido</p>
                        </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 offset-md-8">
                        <input type="submit" class="btn btn-success btn-block" value="Guardar" />
                    </div>
                </div>
                              
            </form>
        </div>
    </body>
</html>
