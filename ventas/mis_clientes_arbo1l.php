<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permiso = new PermisosSubMenu();
$contactoPermiso = 0;

$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_arbol.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 34)){
    $contactoPermiso = 1;
}

$usuario = new Usuario();
$usuario->setId($_SESSION['idUsuario']);

$clientes_permitidos = $usuario->obtenerNegociosDeUsuario();
$array_clientes = implode("','", $clientes_permitidos);
if (!empty($array_clientes)) {
    $array_clientes = "'$array_clientes'";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>       
        <!--easyui-->
        <script type="text/javascript" src="resources/js/jquery.easyui.min.js"></script>
        <link rel="stylesheet" type="text/css" href="resources/css/arbol/easyui.css">
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas_mis_clientes.js"></script>
    </head>    
    <body>
        <div class="principal">
            <?php if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 7)) {//Si tiene el permiso especial de alta cliente ?>
                <a href="#" title="Alta cliente" onclick="lanzarPopUp('Alta cliente', 'cliente/alta_cliente.php');
                            return false;"><img src="resources/images/client_icon.gif" width="28" height="28"/></a>                                        
                   <?php
               }
               if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 33)) {
                   ?>            
                <a href="#" title="Alta lecturas" onclick="lanzarPopUp('Alta Lecturas', 'contrato/alta_lecturafile.php');
                                return false;"><img src="resources/images/contadores.png" width="28" height="28"/></a>
                   <?php
               }
               ?>
            <div id="divinfoup">         
            </div>
            <br/>
            <form>

                <?php
                $catalogo = new Catalogo();
                //echo "Clientes: ".print_r($array_clientes);
                if (!empty($array_clientes)) {
                    ?>
                    <label for="cliente">Cliente:</label>
                    <select id="cliente" name="cliente" width="100" style="width: 100px" >

                        <?php
                        $query = $catalogo->obtenerLista("SELECT
                                c_cliente.NombreRazonSocial AS NombreCliente,
                                c_cliente.ClaveCliente AS ClaveCliente
                                FROM c_usuario
                                INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                WHERE c_cliente.ClaveCliente IN($array_clientes) AND c_cliente.Activo=1
                                ORDER BY NombreCliente ASC;");

                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                        }
                        ?>
                    </select>
                    <?php
                } else if (!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21) && !$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
                    ?>
                    <label for="vendedor">Vendedor:</label>
                    <select id="vendedor" name="vendedor" width="100" style="width: 100px" onchange="cargarclientes('vendedor', 'cliente');">
                        <option value="">Selecciona el vendedor</option>
                        <?php
                        $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario,
                            CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre 
                            FROM `c_usuario` 
                            INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_puesto.IdPuesto=11 AND c_usuario.Activo = 1 ORDER BY Nombre");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                    <label for="cliente">Cliente:</label>
                    <select id="cliente" name="cliente" width="100" style="width: 100px" >
                        <option value="">Selecciona el cliente</option>
                        <?php
                        $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE Activo=1 AND (Modalidad = 1 OR IdTipoCliente = 7) ORDER BY NombreRazonSocial");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . "</option>";
                        }
                        ?>
                        ?>
                    </select>
                    <?php
                } else {
                    ?>
                    <label for="cliente">Cliente:</label>
                    <select id="cliente" name="cliente" width="100" style="width: 100px" >
                        <option value="">Selecciona el cliente</option>
                        <?php
                        if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)) {
                            $query = $catalogo->obtenerLista("SELECT
                                c_cliente.NombreRazonSocial AS NombreCliente,
                                c_cliente.ClaveCliente AS ClaveCliente
                                FROM c_usuario
                                INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario
                                INNER JOIN c_cliente ON c_cliente.ClaveCliente = k_tfscliente.ClaveCliente
                                WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                ORDER BY NombreCliente ASC");
                        } else {
                            $query = $catalogo->obtenerLista("SELECT
                                c_cliente.NombreRazonSocial AS NombreCliente,
                                c_cliente.ClaveCliente AS ClaveCliente
                                FROM c_usuario
                                INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                ORDER BY NombreCliente ASC;");
                        }
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                        }
                        ?>
                    </select>
                <?php } ?>
                <br/><br/>
                <input type="radio" id="tipo" name="tipo" value="1" checked/>Lecturas
                <input type="radio" id="tipo1" name="tipo" value="2"/>Tickets
                <?php
                if (empty($clientes_permitidos)) {
                    echo '<input type="radio" id="tipo2" name="tipo" value="3"/>Movimiento de equipo
                              <input type="radio" id="tipo3" name="tipo" value="4"/>Mtto preventivo';
                }
                ?>                
                <div style="float: right">
                    <input id="ss" class="easyui-searchbox" style="width:200px"/>
                    <div id="mm" style="width:120px">
                        <div data-options="name:'1'">N&uacute;mero de serie</div>
                        <div data-options="name:'2'">Nombre de cliente</div>
                    </div>
                </div>
                <input type="hidden" id="contactoPermiso" name="contactoPermiso" value="<?php echo $contactoPermiso; ?>"/>
            </form>
            <br/>
            <table id="tg" style="width:900px;height:250px">
            </table>
            <input type="button" id="botonvarios" value="Cambiar" class="boton"/>
        </div>
        <br/><br/>
        <div id="divinfo">         
        </div>
    </body>
</html>