<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");

$catalogo = new Catalogo();
$usuario = new Usuario();
$cliente = new Cliente();
$usuario->getRegistroById($_SESSION['idUsuario']);

//$almacenes = "<option value='0'>Todos los almacenes</option>";
$almacenes = "";

if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], "24")){/*Si es un usuario de almacen*/
    $almacen = new Almacen();
    $array = $almacen->getAlmacenResponsable($_SESSION['idUsuario']);
    if($array == ""){//Si no es encargado de un almacen en especifico
        $consulta = "SELECT id_almacen,nombre_almacen,TipoAlmacen FROM `c_almacen` WHERE Activo = 1 ORDER BY nombre_almacen;";
    }else{
        $consulta = "SELECT id_almacen,nombre_almacen,TipoAlmacen FROM `c_almacen` WHERE Activo = 1 AND id_almacen IN($array) ORDER BY nombre_almacen;";
    }
}else{//Sino es usuario de almacen
    $consulta = "SELECT id_almacen,nombre_almacen,TipoAlmacen FROM `c_almacen` WHERE Activo = 1 ORDER BY nombre_almacen;";
}

$idAlmacen = $usuario->getIdAlmacen();

$result = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($result)){
    $s = "";
    if($idAlmacen == $rs['id_almacen']){
        $s = "selected = 'selected'";
    }
    $almacenes.= "<option value='".$rs['id_almacen']."' $s>".$rs['nombre_almacen']."</option>";
}

$result = $cliente->getTodosRegistros();
$clientes = "<option value='0'>Todos los clientes</option>";
while ($rs = mysql_fetch_array($result)) {
    $s = "";
    if(isset($_GET['cliente']) && $_GET['cliente'] == $rs['ClaveCliente']){
        $s = "selected";
    }
    $clientes .= "<option value='".$rs['ClaveCliente']."' $s>".$rs['NombreRazonSocial']."</option>";
}
?>
<!DOCTYPE>
<html lang="es">
    <head>
        <title>Movimientos de equipos</title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/movimiento_almacen.js"></script>
    </head>
    <body>
        <form id="form_almacen" name="form_almacen" action="almacen/movimiento_almacen.php" method="POST" target="_blank">
            <style>
                .ui-multiselect{
                    width: 100%!important;
                }
            </style>
            
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="no_serie">No. Serie</label>
                    <input class="form-control" type="text" id="no_serie" name="no_serie"/>
                </div>
                <div class="form-group col-md-3">
                    <label for="tipo_es">Entrada/Salida</label>
                    <select id="tipo_es" name="tipo_es" class="custom-select">
                            <option value="0">Todos</option>
                            <option value="1">Entradas</option>
                            <option value="2">Salidas</option>
                        </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="fecha_inicio">Fecha inicio</label>
                    <input  id="fecha_inicio" name="fecha_inicio" class="fecha form-control"/>
                </div>
                <div class="form-group col-md-3">
                    <label for="">Ticket</label>
                    <input type="number" id="ticket" name="ticket" class="form-control"/>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="tipo">Tipo</label>
                     <select id="tipo" name="tipo[]" class="filtromultiple" onchange="cambiarselectmodelo('tipo', 'modelo');"  multiple="multiple">                            
                            <option value="0">Equipo</option>
                            <?php
                            $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                            while ($rs = mysql_fetch_array($query2)) {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="cliente">Cliente</label>
                     <select id="cliente" name="cliente" class="filtro custom-select" onchange="cargarLocalidadByCliente('localidad','cliente');">
                            <?php
                                echo $clientes;
                            ?>
                        </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="fecha_fin"> Fecha final</label>
                    <input id="fecha_fin" name="fecha_fin" class="form-control fecha"/>
                </div>
                <div class="form-group col-md-3 p-4">
                    <input type="checkbox" id="compras" name="compras" value="on"/>
                    <label for="compras">Sólo compras</label>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="modelo">Modelo</label>
                    <select id="modelo" name="modelo" class="filtro">
                            <option value="">Selecciona el modelo</option>
                        </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="localidad">Localidad</label>
                    <select  id="localidad" name="localidad" class="filtro">                                
                            <?php                                        
                                echo "<option value=''>Selecciona la localidad</option>";                                        
                            ?> 
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="almacen">Almacén</label>
                    <select id="almacen" name="almacen" class="filtro">
                            <?php
                                echo $almacenes;
                            ?>
                    </select>
                </div>
                
                <div class="form-group col-md-2 p-3">
                     <input type="submit" class="btn btn-success btn-block" id="boton_almacen" name="boton_almacen" value="Buscar"/>
                </div>
                <div class="form-group col-md-1 p-3">
                    <input type="submit" class="btn btn-outline-success btn-block" id="boton_excel" name="boton_almacen" value="Excel"/>
            <?php if(isset($_GET['regresar']) && $_GET['regresar'] != ""){?>
            <input type="button" class="boton" onclick="cambiarContenidos('<?php echo $_GET['regresar']?>','');" value="Regresar">
            <?php }?>
                </div>
            </div>
            
                       
        </form>
    </body>
</html>