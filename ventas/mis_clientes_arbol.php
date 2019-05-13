<?php
    session_start();

    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") header("Location: ../index.php");

    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

    $permiso = new PermisosSubMenu();
    $contactoPermiso = 0;

    $permisos_grid = new PermisosSubMenu();
    $same_page = "ventas/mis_clientes_arbol.php";
    $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

    if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 34)) $contactoPermiso = 1;

    $usuario = new Usuario();
    $usuario->setId($_SESSION['idUsuario']);

    $clientes_permitidos = $usuario->obtenerNegociosDeUsuario();
    $array_clientes = implode("','", $clientes_permitidos);
    if (!empty($array_clientes)) $array_clientes = "'$array_clientes'";
    
?>
<!DOCTYPE html>
<html lang="es">
    <head>       
        <style>
            .tree {
                margin: 0;
                padding: 0;
                white-space: nowrap;
                overflow-x: auto;
            }
            .tree ul {
                list-style: none;
            }
            .tree-node {
                padding: 5px 8px;
            }
            .tree-node-hover {
                padding: 5px;
                background-color: #007bff;
                color: white;
            }
            .tree-expanded::before,
            .tree-collapsed::before,
            .tree-folder::before,
            .tree-file::before,
            .tree-checkbox::before,
            .tree-indent::before,
            .tree-hit::before,
            .tree-icon::before {
                display: inline-block;
                text-rendering: auto;
                -webkit-font-smoothing: antialiased;
                font-style: normal;
                font-variant: normal;
                font-family: "Font Awesome 5 Free";
            }
            .tree-expanded::before {
                content: "\f150";
            }
            .tree-collapsed::before {
                content: "\f152";
            }
            .tree-checkbox0::before {
                content: "\f0c8";
            }
            .tree-checkbox1::before, .tree-checkbox2::before {
                content: "\f14a";
            }

            .icon-impresora::before {
                content: "\f15b";
                color: #ffc107;
            }
            .icon-person::before {
                content: "\f007";
                color: #17a2b8;
            }
            .icon-edificio::before {
                content: "\f1ad";
            }
        </style>
    </head>    
    <body>
        <div class="principal">
            <!--Permisos especiales-->
            <?php if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 7)): ?>
                <a href="#" title="Alta cliente" onclick="lanzarPopUp('Alta cliente', 'cliente/alta_cliente.php'); return false;">
                    <i class="far fa-user" style="font-size:1.3rem;"></i> Mis Clientes
                </a>
            <?php endif; ?>
            <!-- Permisos especiales -->
            <?php if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 33)): ?>            
                <a href="#" title="Alta lecturas" onclick="lanzarPopUp('Alta Lecturas', 'contrato/alta_lecturafile.php'); return false;">
                    <i class="far fa-list-alt" style="font-size:1.3rem;"></i> Añadir Lecturas
                </a>
            <?php endif; ?>
            <div id="divinfoup"></div>
            <form>
                <div class="container-fluid p-4 bg-light rounded">
                    <div class="form-row">
                        <?php 
                            $catalogo = new Catalogo();
                            if (!empty($array_clientes)):
                        ?>
                            <div class="form-group col-md-6 col-12">
                                <label for="cliente" class="m-0">Cliente:</label>
                                <select class="custom-select" id="cliente" name="cliente">
                                    <?php
                                        $query = $catalogo->obtenerLista("SELECT c_cliente.NombreRazonSocial AS NombreCliente, c_cliente.ClaveCliente AS ClaveCliente FROM c_usuario INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario WHERE c_cliente.ClaveCliente IN($array_clientes) AND c_cliente.Activo=1 ORDER BY NombreCliente ASC;");
                                        //displaying options after query
                                        while ($rs = mysql_fetch_array($query)) {
                                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        <?php elseif (!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21) && !$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)): ?>
                            <div class="form-group col-md-6 col-12">
                                <label for="vendedor" class="m-0">Vendedor:</label>
                                <select class="custom-select" id="vendedor" name="vendedor" onchange="cargarclientes('vendedor', 'cliente');">
                                    <option value="">Selecciona el vendedor</option>
                                    <?php
                                        $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario, CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_puesto.IdPuesto=11 AND c_usuario.Activo = 1 ORDER BY Nombre");
                                        while ($rs = mysql_fetch_array($query)) {
                                            echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                        }
                                    ?>
                                </select>    
                            </div>
                            <div class="form-group col-md-6 col-12">
                                <label for="cliente" class="m-0">Cliente:</label>
                                <select class="custom-select" id="cliente" name="cliente">
                                    <option value="">Selecciona el cliente</option>
                                    <?php
                                        $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE Activo=1 AND (Modalidad = 1 OR IdTipoCliente = 7) ORDER BY NombreRazonSocial");
                                        while ($rs = mysql_fetch_array($query)) {
                                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <div class="form-group col-md-6 col-12">
                                <label for="cliente" class="m-0">Cliente:</label>
                                <select class="custom-select" id="cliente" name="cliente">
                                    <option value="">Selecciona el cliente</option>
                                    <?php
                                        $query = ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21))
                                            ? "SELECT c_cliente.NombreRazonSocial AS NombreCliente, c_cliente.ClaveCliente AS ClaveCliente FROM c_usuario INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario INNER JOIN c_cliente ON c_cliente.ClaveCliente = k_tfscliente.ClaveCliente WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1 ORDER BY NombreCliente ASC"
                                            : "SELECT c_cliente.NombreRazonSocial AS NombreCliente, c_cliente.ClaveCliente AS ClaveCliente FROM c_usuario INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1 ORDER BY NombreCliente ASC;";
                                        
                                        $consulta = $catalogo->obtenerLista($query);

                                        while ($rs = mysql_fetch_array($consulta)) echo "<option value='" . $rs['ClaveCliente'] . "'>" . $rs['NombreCliente'] . "</option>";
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <!--Lecturas-->
                        <div class="form-group col-md-3 col-12">
                            <input type="radio" id="tipo" name="tipo" value="1" checked/>
                            <label for="tipo">Lecturas</label>
                        </div>
                        <!-- Tickets -->
                        <div class="form-group col-md-3 col-12">
                            <input type="radio" id="tipo1" name="tipo" value="2"/>
                            <label for="tipo1">Tickets</label>
                        </div>
                        <!-- Si no hay clientes permitidos -->
                        <?php if (empty($clientes_permitidos)): ?>
                            <!-- Movimiento de equipo -->
                            <div class="form-group col-md-3 col-12">    
                                <input  type="radio" id="tipo2" name="tipo" value="3"/>
                                <label for="tipo2">Movimiento de equipo</label>
                            </div>
                            <!-- Mtto preventivo -->
                            <div class="form-group col-md-3 col-12">
                                <input  type="radio" id="tipo3" name="tipo" value="4"/>
                                <label for="tipo3">Mtto preventivo</label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3 offset-md-9 col-sm-6 offset-sm-6">
                            <input id="ss" class="easyui-searchbox" style="width:200px"/>
                            <div id="mm" style="width:120px">
                                <div data-options="name:'1'">N&uacute;mero de serie</div>
                                <div data-options="name:'2'">Nombre de cliente</div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="contactoPermiso" name="contactoPermiso" value="<?php echo $contactoPermiso; ?>"/>
               </div>
            </form>
            <br/>
            <table id="tg" style="width:100%;overflow-x:scroll;">
            </table>
            <input type="button" id="botonvarios" value="Cambiar" class="btn btn-secondary"/>
        </div>
        <div id="divinfo">         
        </div>
        <script type="text/javascript" src="resources/js/jquery.easyui.min.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas_mis_clientes.js"></script>
    </body>
</html>