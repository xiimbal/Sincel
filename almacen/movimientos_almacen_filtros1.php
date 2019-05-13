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
            <table style="width: 100%;">     
                <tr>
                    <td>No. Serie</td>
                    <td><input type="text" id="no_serie" name="no_serie" style="width: 230px;"/></td>
                    <td>Tipo</td>
                    <td>
                        <select id="tipo" name="tipo[]" class="filtromultiple" onchange="cambiarselectmodelo('tipo', 'modelo');" style="width: 230px;" multiple="multiple">                            
                            <option value="0">Equipo</option>
                            <?php
                            $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                            while ($rs = mysql_fetch_array($query2)) {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>Modelo</td>
                    <td>
                        <select id="modelo" name="modelo" class="filtro" style="width: 230px;">
                            <option value="">Selecciona el modelo</option>
                        </select>
                    </td>
                </tr>            
                <tr>
                    <td>Entrada/Salida</td>
                    <td>
                        <select id="tipo_es" name="tipo_es" class="filtro" style="min-width: 230px;">
                            <option value="0">Todos</option>
                            <option value="1">Entradas</option>
                            <option value="2">Salidas</option>
                        </select>
                    </td>
                    <td>Cliente</td>
                    <td>
                        <select id="cliente" name="cliente" class="filtro" onchange="cargarLocalidadByCliente('localidad','cliente');" style="max-width: 230px;">
                            <?php
                                echo $clientes;
                            ?>
                        </select>
                    </td>
                    <td>Localidad</td>
                    <td>
                        <select  id="localidad" name="localidad" class="filtro" style="width: 230px;">                                
                            <?php                                        
                                echo "<option value=''>Selecciona la localidad</option>";                                        
                            ?> 
                        </select>
                    </td>
                </tr>
                <tr>                    
                    <td>Fecha inicio</td>
                    <td><input id="fecha_inicio" name="fecha_inicio" class="fecha" style="width:230px" /></td>
                    <td>Fecha final</td>
                    <td><input id="fecha_fin" name="fecha_fin" class="fecha" style="width:230px" /></td>
                    <td>Almacén</td>
                    <td>
                        <select id="almacen" name="almacen" class="filtro" style="width: 230px;">
                            <?php
                                echo $almacenes;
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Ticket</td>
                    <td><input type="number" id="ticket" name="ticket" style="width:230px" /></td>
                    <td>Sólo compras</td>
                    <td><input type="checkbox" id="compras" name="compras" value="on"/></td>
                </tr>
            </table>
            <input type="submit" class="boton" id="boton_almacen" name="boton_almacen" value="Buscar" style="margin-left: 80%;"/>
            <input type="submit" class="boton" id="boton_excel" name="boton_almacen" value="Excel"/>
            <?php if(isset($_GET['regresar']) && $_GET['regresar'] != ""){?>
            <input type="button" class="boton" onclick="cambiarContenidos('<?php echo $_GET['regresar']?>','');" value="Regresar">
            <?php }?>
        </form>
    </body>
</html>