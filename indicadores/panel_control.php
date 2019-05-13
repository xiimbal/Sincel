<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$usuario = new Usuario();
$usuario->setId($_SESSION['idUsuario']);
$catalogo = new Catalogo();

$clientes_permitidos = $usuario->obtenerNegociosDeUsuario();
$array_clientes = implode("','", $clientes_permitidos);
if (!empty($array_clientes)) {
    $array_clientes = "'$array_clientes'";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>JSP Page</title>

        <!-- 1. Add these JavaScript inclusions in the head of your page -->        
        <!--<script type="text/javascript" src="resources/scripts/jquery-1.7.2.min.js"></script>-->
        <!--<script type="text/javascript" src="resources/js/links/highcharts.js"></script>-->
        <!--[if IE]>
                <script type="text/javascript" src="../js/excanvas.compiled.js"></script>
        <![endif]-->

        <!-- Highslide code -->
        <script type="text/javascript" src="resources/js/highslide/highslide-full.packed.js"></script>
        <script type="text/javascript" src="resources/js/highslide/highslide.config.js" charset="utf-8"></script>
        <link rel="stylesheet" type="text/css" href="resources/css/highslide/highslide.css" />
        <!--[if lt IE 7]>
        <link rel="stylesheet" type="text/css" href="/highslide/highslide-ie6.css" />
        <![endif]-->
        <!-- End Highslide code -->

        <!--    jqwidgets      -->
        <link rel="stylesheet" href="resources/css/highslide/jqwidgets/styles/jqx.base.css" type="text/css" />
        <link rel="stylesheet" href="resources/css/highslide/jqwidgets/styles/docking.css" type="text/css" />               
        <script type="text/javascript" src="resources/css/highslide/jqwidgets/jqxcore.js"></script>
        <script type="text/javascript" src="resources/css/highslide/jqwidgets/jqxwindow.js"></script>        
        <script type="text/javascript" src="resources/css/highslide/jqwidgets/jqxtabs.js"></script>        
        <script type="text/javascript" src="resources/css/highslide/jqwidgets/jqxdocking.js"></script>        
        <script type="text/javascript" src="resources/css/highslide/jqwidgets/jqxscrollbar.js"></script>       
        <!-- Docking -->        
        <script type="text/javascript" src="resources/js/paginas/panel_control.js" charset="utf-8"></script>            
    </head>
    <body class='default' style="width: 100%;">
        <br/>
        <table style="width: 100%;">
            <tr>
                <td><label for="cliente">Cliente: </label></td>
                <td>
                    <?php
                    if (!empty($array_clientes)) {
                        ?>                    
                        <select id="cliente" name="cliente" class="filtro" style="max-width: 200px;">
                            <?php
                            $query = $catalogo->obtenerLista("SELECT
                                c_cliente.NombreRazonSocial AS NombreCliente,
                                c_cliente.ClaveCliente AS ClaveCliente
                                FROM c_usuario
                                INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                WHERE c_cliente.ClaveCliente IN($array_clientes) AND c_cliente.Activo=1
                                ORDER BY NombreCliente ASC;");

                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if(isset($_POST['cliente']) && $_POST['cliente'] == $rs['ClaveCliente']){
                                    $s = "selected='selected'";
                                }
                                echo "<option value=\"" . $rs['ClaveCliente'] . "\" $s>" . $rs['NombreCliente'] . "</option>";
                            }
                            ?>
                        </select>
                        <?php
                    } else if (!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21) && !$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
                        ?>
                        
                        <select id="cliente" name="cliente"class="filtro" style="max-width: 200px;">                            
                            <?php
                            $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE Activo=1 ORDER BY NombreRazonSocial");
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if(isset($_POST['cliente']) && $_POST['cliente'] == $rs['ClaveCliente']){
                                    $s = "selected='selected'";
                                }
                                echo "<option value=\"" . $rs['ClaveCliente'] . "\" $s>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?>
                            ?>
                        </select>
                        <?php
                    } else {
                        ?>                        
                        <select id="cliente" name="cliente" class="filtro" style="max-width: 200px;">                            
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
                                $s = "";
                                if(isset($_POST['cliente']) && $_POST['cliente'] == $rs['ClaveCliente']){
                                    $s = "selected='selected'";
                                }
                                echo "<option value=\"" . $rs['ClaveCliente'] . "\" $s>" . $rs['NombreCliente'] . "</option>";
                            }
                            ?>
                        </select>
                    <?php } ?>
                </td>
                <td>Fecha inicio</td>
                <td><input id="fecha_inicio" name="fecha_inicio" class="fecha" style="width:100px;" value="<?php
                    if (isset($_POST['fechaInicio'])) {
                        echo $_POST['fechaInicio'];
                    }
                    ?>"/></td>
                <td>Fecha final</td>
                <td><input id="fecha_fin" name="fecha_fin" class="fecha" style="width:100px;" value="<?php
                    if (isset($_POST['fechaFinal'])) {
                        echo $_POST['fechaFinal'];
                    }
                    ?>"/></td>
                <td><label for="razon_social">Emisor: </label></td>
                <td>
                    <select id="razon_social" class="filtro" style="max-width: 200px;">
                        <option value="">Todos los emisores</option>
                        <?php
                        $datosFacturacion = new DatosFacturacionEmpresa();
                        $result = $datosFacturacion->getEmpresasFacturacion();
                        while ($rs = mysql_fetch_array($result)) {
                            $s = "";
                            if (isset($_POST['razonSocial']) && $_POST['razonSocial'] == $rs['IdDatosFacturacionEmpresa']) {
                                $s = "selected='selected'";
                            }
                            echo "<option value='" . $rs['IdDatosFacturacionEmpresa'] . "' $s>" . $rs['RazonSocial'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><label for="ejecutivo">Ejecutivo: </label></td>
                <td>
                    <select id="ejecutivo" class="filtro" style="max-width: 200px;">
                        <option value="">Todos los ejecutivos</option>
                        <?php
                        $result = $usuario->getUsuariosByPuesto("11");
                        while ($rs = mysql_fetch_array($result)) {
                            $s = "";
                            if (isset($_POST['ejecutivo']) && $_POST['ejecutivo'] == $rs['IdUsuario']) {
                                $s = "selected='selected'";
                            }
                            echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                        }
                        ?>
                    </select>
                </td>                                                   
                <td><input type="button" class="button" value="Obtener indicadores" 
                           onclick="cargarDiv('contenidos', 'indicadores/panel_control.php', 'fecha_inicio', 'fecha_fin', 'cliente', 'ejecutivo', 'razon_social');"/></td>            
            </tr>
        </table><br/>
        <?php if (isset($_POST['razonSocial'])) { ?>
            <div id='jqxWidget'>
                <div id="docking">
                    <div style="width: 37%;">
                        <div id="window1" style="height: 320px;">
                            <div>Equipos</div>
                            <div style="overflow: hidden;" id="uno" ></div>
                        </div>
                        <div id="window4" style="height: 320px">
                            <div>Solicitudes</div>
                            <div style="overflow: hidden;" id="cuatro">                                
                            </div>
                        </div>                        
                    </div>
                    <div style="width: 33%;"> 
                        <div id="window2" style="height: 320px;">
                            <div>Facturaci&oacute;n</div>
                            <div style="overflow: hidden;" id="dos">                                
                            </div>
                        </div>
                        <div id="window5" style="height: 320px;">
                            <div>Lecturas</div>
                            <div style="overflow: hidden;" id="cinco">                                
                            </div>
                        </div>
                    </div>
                    <div style="width: 33%;">
                        <div id="window3" style="height: 320px;">
                            <div>Tickets</div>
                            <div style="overflow: hidden;" id="tres">                                
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        <?php } ?>
    </body>
</html>