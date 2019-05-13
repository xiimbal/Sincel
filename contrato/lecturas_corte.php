<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Lectura.class.php");

$meses = array(1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio",
    7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");

$cliente_objeto = new Cliente();

if (isset($_GET['id'])) {
    $cc = $_GET['id'];
    $cc_objeto = new CentroCosto();
    $cc_objeto->getRegistroById($cc);
    $cliente_objeto->getRegistroById($cc_objeto->getClaveCliente());
} else if ($_GET['cliente']) {
    $cc = "";
    $cliente_objeto->getRegistroById($_GET['cliente']);
}
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_arbol.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
if ($permisos_grid->getAlta()) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
        <head>                       
            <title>Toma de lecturas de corte</title>
            <!-- JS -->
            <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
            <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
			<script src="../resources/js/jquery/jquery-ui.min.js"></script>      
            <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
            <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
            <script type="text/javascript" src="../resources/js/funciones.js"></script>         
            <script type="text/javascript" src="../resources/js/paginas/lecturas/lectura_corte.js"></script>
        </head>
        <body>
            <table style="min-width: 70%;">
                <tr>
                    <td><b>Cliente:</b> </td>
                    <td><?php echo $cliente_objeto->getNombreRazonSocial();
    if ($cliente_objeto->getModalidad() == "3") {
        echo "<span style='color:red;'> (Cliente de venta directa)</span>";
    } ?></td>                
                </tr>
                <tr>
                    <td><b>Localidad:</b> </td>
                    <td>
                        <?php
                        if (isset($_GET['id'])) {
                            echo $cc_objeto->getNombre();
                        } else {
                            echo "Todas las localidades";
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <br/>
            <table style="width: 40%;">                        
                <tr>
                    <td>Mes</td>
                    <td>
                        <select id="mes_lectura" name="mes_lectura" onchange="cargarEquipos('<?php echo $cliente_objeto->getClaveCliente(); ?>', '<?php echo $cc; ?>', 'mes_lectura', 'anio_lectura', '', 'sugerir_lecturas');">
                            <?php
                            $mes_actual = intval(date("n"));
                            foreach ($meses as $key => $value) {
                                $s = "";
                                if ($mes_actual == $key) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='$key' $s>$value</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>Año</td>
                    <td>
                        <select id="anio_lectura" name="anio_lectura" onchange="cargarEquipos('<?php echo $cliente_objeto->getClaveCliente(); ?>', '<?php echo $cc; ?>', 'mes_lectura', 'anio_lectura', '', 'sugerir_lecturas');">
                            <?php
                            $anio_actual = intval(date("Y"));
                            $intervalo = 3;
                            for ($i = ($anio_actual - $intervalo); $i <= $anio_actual; $i++) {
                                $s = "";
                                if ($i == $anio_actual) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='$i' $s>$i</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" id="sugerir_lecturas" name="sugerir_lecturas" checked="checked" style="margin-left: 10%;" 
                            onclick="cargarEquipos('<?php echo $cliente_objeto->getClaveCliente(); ?>', '<?php echo $cc; ?>', 'mes_lectura', 'anio_lectura', '', 'sugerir_lecturas');
                            "/>Sugerir Lecturas
                        <br/>
                        <input type="button" class="boton" onclick="cargarEquipos('<?php echo $cliente_objeto->getClaveCliente(); ?>', '<?php echo $cc; ?>', 'mes_lectura', 'anio_lectura', '', 'sugerir_lecturas');
                            return false;" id="inicia_captura" value="Iniciar captura" style="margin-left: 15%;"/>
                    </td>
                </tr>
            </table>
            <br/><br/>
            <div id="cargando_lectura" style="width:80%; margin-left: 50%; display: none; ">
                <img src="../resources/images/cargando.gif"/>                          
            </div>
            <div id="mensaje_lecturas"></div>
            <div id="div_lectura"></div>
        </body>
    </html>
<?php } ?>