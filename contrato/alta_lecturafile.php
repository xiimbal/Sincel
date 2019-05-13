<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
header('Content-Type: text/html; charset=utf-8');
include_once("../WEB-INF/Classes/Catalogo.class.php");

include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");


$catalogo = new Catalogo();

$meses = array(1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio",
    7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Carga de lecturas</title>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.9.1.js"></script>
        <script src="../resources/js/jquery/jquery-ui-1.10.3.custom.min.js"></script>        
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
        <script type="text/javascript" src="../resources/js/funciones.js"></script>                   
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/lista.js"></script>

        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">
        <!--    Lecturas    -->
        <script src="../resources/js/paginas/lecturas/alta_lecturafile.js"></script>
    </head>
    <body>
        <h2>Carga las lecturas a partir de un archivo csv</h2>
        <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
            <img src="../resources/images/cargando.gif"/>                          
        </div>
        <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%;"></div>
        
        <form id = "formCargaLista" name="formCargaLista" ENCTYPE="multipart/form-data">
            <table style="width: 95%;">
                <tr>
                    <td>Cliente</td>
                    <td>
                        <select id="cliente" name="cliente" class="select" style="width: 200px;">
                            <option value="">Selecciona el cliente</option>
                            <?php
                            $result = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<option value='" . $rs['ClaveCliente'] . "'>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>                    
                    <td>Mes</td>
                    <td>
                        <select id="mes_lectura" name="mes_lectura" class="select">
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
                        <select id="anio_lectura" name="anio_lectura" class="select">
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
                </tr>
                <tr>
                    <td>Importar archivo: </td>                    
                    <td colspan="4"><input type='file' name='file' id='file' class="boton"></td>                    
                </tr>
                
                <tr>      <!-- *JT -->                                                    
                    <td>Tipo de archivo: </td>
                     <td>
                         <select id="tipo_archivo" name="tipo_archivo" onchange="modificarValidacion();" class="select">
                            <option value="1">Normal</option>
                            <option value="2">Print Fleet</option>
                        </select>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><input id="upload" name ="upload" type="button" value="Cargar" class="boton" /></td>
                </tr>   <!-- *JT -->
            </table>
        </form>
        <br/>
        <div id="contenidos_invisibles" style="display: none;"></div>
        <div id="mensajeLista"></div>
        <br/>
        <div id="div1"></div>
        
   <?           //*JT
$cabeceras = array("Folio", "Registros OK", "Registros Error","Fecha Registro", "Excel", "Borrar");
$columnas = array("nombre_almacen", "Activo", "id_almacen");
$tabla = "c_almacen";
$order_by = "nombre_almacen";
$alta = "admin/alta_almacen.php";
   ?>                 
        
    <div class="segunda">
            <table id="tAlmacen" class="tabla_datos">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <b>Registros Cargas Masivas</b>
                <tbody>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT * FROM c_lecturaMasiva ORDER BY IdLecturasMasivas ASC;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='col'>" . $rs['IdLecturasMasivas'] . "</td>";
                        echo "<td align='center' scope='col'>" . $rs['RegistrosOK'] . "</td>";
                        echo "<td align='center' scope='col'>" . $rs['RegistrosError'] . "</td>";
                        echo "<td align='center' scope='col'>" . $rs['FechaRegistro'] . "</td>";
                        ?>
                   <td align='center' scope='row'>
                        <a class="liga_imprimir" href="ReporteCargasMasivasExcelLight.php?txt_fecha_fin=<?php echo $where ?>&TipoR=<?php echo $banderaTipoR ?>">
                            <img src="../resources/img/excelicon.png" title="Exportar a Excel" style="width: 30px; height: 30px"/>
                        </a>
                   </td>
                   <td align='center' scope='row'> 
                        <?php if ($permisos->getBaja()==1) { ?>
                            <a href='#' onclick='eliminarRegistro1Id("<?php  echo $controlador;?>", "<?php  echo $same_pag; ?>","<?php  echo $rs['IdRegistrosTarimas'];?>");
                                    return false;' title='Borrar Registro' >
                                <i class="fa fa-trash-o" aria-hidden="true" style="font-size: 2em;"></i>
                            </a>
                        <?php  } ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        
    </body>
</html>