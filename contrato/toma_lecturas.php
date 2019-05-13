<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$max_dia = array(31,28,31,30,31,30,31,31,30,31,30,31);/*Maximo de dias por cada mes*/

include_once("../WEB-INF/Classes/Lectura.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");

$lectura = new Lectura();
$catalogo = new Catalogo();
$localidad = new CentroCosto();
$menu = new Menu();

if(isset($_POST['ClaveCC'])){
    $cc = $_POST['ClaveCC'];
    $cliente = "";
}else{
    $cc = "";
    $cliente = $_POST['ClaveCliente'];
}

if(isset($_POST['sugerir']) && $_POST['sugerir'] == "1"){
    $sugerir = true;
}else{
    $sugerir = false;
}

$year = $_POST['anio'];
$month = $_POST['mes'];
$anio_post = $year;
$mes_anterior = "";

if(isset($_POST['fecha_lectura']) && $_POST['fecha_lectura']!=""){
    $fecha = $_POST['fecha_lectura'];
    $anio_post = substr($fecha, 0, 4);    
}else{
    if(isset($cc)){//Si hay localidad
        $localidad->getRegistroById($cc);
        $dia = $lectura->getDiaDeCorteByCliente($localidad->getClaveCliente());
    }else{//Si solo hay cliente
        $dia = $lectura->getDiaDeCorteByCliente($cliente);
    }
    
    if(intval($dia) > $max_dia[intval($month) - 1]){
        $dia = $max_dia[intval($month)-1];
    }
    $fecha = "$year-$month-$dia";
}

/*Obtenemos el mes anterior*/
$result = $catalogo->obtenerLista("SELECT DATE_SUB('$year-$month-01',INTERVAL 1 MONTH) AS anterior;");
while($rs = mysql_fetch_array($result)){
    $mes_anterior = $rs['anterior'];
}

$aux = explode("-", $mes_anterior);
$mes_anterior = $meses[intval($aux[1])-1]." ".$aux[0];
/*Obtenemos el mes actual*/
$mes_actual = $meses[intval($month)-1]." ".$anio_post;

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <style>
            .sizeMedium {width: 50px;}
            .borde {border: 1px solid #000; text-align:center; vertical-align:middle;}
            table {border-collapse:collapse;}
            label.error {
                color: red;
            }
        </style>
        <script type="text/javascript" src="../resources/js/paginas/lecturas/lectura_corte.js"></script>         
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <form id="form_lectura" name="form_lectura" method="POST" action="toma_lectura_xls.php" target="_blank">
            Fecha de lectura: <input type="text" class="fecha" id="fecha_captura" name="fecha_captura" value="<?php echo $fecha; ?>" style="max-width: 150px;"/>
            <?php
                $result = $lectura->getLecturasByCC($cliente,$cc,null, $year, $month, $fecha);
                echo "<table style='width: 100%;'>";
                echo "<tr>
                    <td rowspan='3' class='borde'>No. Serie</td><td rowspan='3' class='borde'>Modelo</td>";
                if(!isset($cc) || $cc==""){
                    echo "<td rowspan='3' class='borde'>Localidad</td>";
                }
                echo "<td rowspan='3' class='borde'>Guardar contadores <span style='color:blue; font-size:11px;'> (* Sólo los contadores con la casilla seleccionada se guardarán)</span>
                    <br/><a href='#' onclick='seleccionarTodo(true); return false;'>Seleccionar todo</a>
                    <br/><a href='#' onclick='seleccionarTodo(false); return false;'>Deseleccionar todo</a>
                    </td>";
                echo "<td rowspan='3' class='borde'>Comentarios</td>";
                
                echo "<td colspan='8' class='borde'>Contador</td>
                    </tr>
                    <tr><td colspan='4' class='borde'>B/N</td><td colspan='4' class='borde'>Color</td>
                    </tr>
                    <tr><td class='borde'>$mes_anterior</td><td class='borde'>Usuario</td><td class='borde'>Fecha</td><td class='borde'>$mes_actual</td>"
                     . "<td class='borde'>$mes_anterior</td><td class='borde'>Usuario</td><td class='borde'>Fecha</td><td class='borde'>$mes_actual</td>
                    </tr>";  
                
                $numero_series = array();
                while($rs = mysql_fetch_array($result)){
                    array_push($numero_series, "'".$rs['NoSerie']."'");
                }
                
                $contadores_propuestos = array();
                if($sugerir && !empty($numero_series)){
                    $contadores_propuestos = $lectura->getUltimasLecturasPorSeries($numero_series);
                }
                
                if(mysql_num_rows($result)>0 && !mysql_data_seek($result, 0)){
                    $result = $lectura->getLecturasByCC($cliente,$cc,null, $year, $month, $fecha);
                }
                
                $contador = 0;
                while($rs = mysql_fetch_array($result)){
                    $contador++;
                    /*Vemos las caracteristicas del equipo actual*/
                    $color = false;
                    $fa = false;
                    /*Es FA?*/
                    $aux = explode(",", $rs['caracteristicas']);
                    if(in_array("2", $aux)){
                        $fa = true;
                    }
                    /*Es color?*/
                    $aux = explode(",", $rs['servicio']);
                    if(in_array("1", $aux)){
                        $color = true;                    
                    }
                    $serie = $rs['NoSerie'];
                    echo "<tr>"; 
                    echo "<td class='borde'>$serie</td>"; 
                    echo "<td class='borde'>".$rs['Modelo']."</td>"; 
                    if(!isset($cc) || $cc==""){
                        echo "<td class='borde'>".$rs['NombreCentroCosto']."</td>";
                    }
                    
                    if(!$fa){
                        $contadorbn = $rs['ContadorBNPaginas'];
                        $contadorcolor = $rs['ContadorColorPaginas'];
                        $contadorbnA = $rs['ContadorBNPaginasA'];
                        $contadorcolorA = $rs['ContadorColorPaginasA'];                        
                    }else{
                        $contadorbn = (int)$rs['ContadorBNML'];
                        $contadorcolor = (int)$rs['ContadorColorML'];
                        $contadorbnA = (int)$rs['ContadorBNMLA'];
                        $contadorcolorA = (int)$rs['ContadorColorMLA'];
                    }
                    
                    $propuesto = false;
                    if($contadorbn == "" && isset($contadores_propuestos[$serie]['bn']) && $contadores_propuestos[$serie]['bn'] != ""){
                        $contadorbn = $contadores_propuestos[$serie]['bn'];
                        $propuesto = true;
                    }
                    
                    echo "<td class='borde'>"; 
                    if($contadorbn != ""){
                        $checked = "checked='checked'";
                    }else{
                        $checked = "";
                    }
                    echo "<input type='checkbox' id='check_bn_$contador' name='check_bn_$contador' value='ON' $checked/>";
                    echo "</td>"; 
                    
                    echo "<td class='borde'><textarea id='comentario_$contador' name='comentario_$contador' style='resize:none;'>".$rs['Comentario']."</textarea></td>"; 
                    
                    if(isset($rs['IdLectura'])){
                        $existe = $rs['IdLectura'];
                    }else{
                        $existe = 0;
                    }
                    echo "<input type='hidden' id='fa_$contador' name='fa_$contador' value='$fa' />";
                    echo "<input type='hidden' id='existe_$contador' name='existe_$contador' value='$existe' />";
                    echo "<input type='hidden' id='serie_$contador' name='serie_$contador' value='".$rs['NoSerie']."' />";
                    echo "<td class='borde'>$contadorbnA
                        <input type='hidden' id='contador_bnMaximo_$contador' name='contador_bnMaximo_$contador' value='".$rs['MaxContadorBN']."'/>
                        <input type='hidden' id='contador_bnA_$contador' name='contador_bnA_$contador' value='$contadorbnA'/>
                        </td>";
                    echo "<td class='borde'>".$rs['UsuarioUltimaModificacion']."</td>";
                    echo "<td class='borde'>".$rs['FechaCreacion']."</td>";                                                                           
                    echo "<td class='borde'>";
                    echo "<input type='text' class='sizeMedium' id='contador_bn_$contador' name='contador_bn_$contador' value='$contadorbn' MaxLength='8'/>";
                    
                    
                    if($propuesto){
                        echo "<div style='font-size:12px; color:blue;'>Lectura capturada por el usuario <b>".$contadores_propuestos[$serie]['usuario']."</b> 
                            en fecha <b>".$catalogo->formatoFechaReportes(substr($contadores_propuestos[$serie]['fecha'], 0, 10))."</b>";
                        if(isset($contadores_propuestos[$serie]['ticket']) && $contadores_propuestos[$serie]['ticket'] != ""){
                            if($menu->tieneSubmenu($_SESSION['idUsuario'], 13)){
                                echo " para el ticket <a href='../principal.php?mnu=mesa&action=lista_ticket&id=".$contadores_propuestos[$serie]['ticket']."' target='_blank' title='Ver ticket ".$contadores_propuestos[$serie]['ticket']."'>".$contadores_propuestos[$serie]['ticket']."</a>";
                            }else{
                                echo " para el ticket ".$contadores_propuestos[$serie]['ticket'];
                            }
                        }
                        echo "</div>";
                    }
                    echo "</td>";
                    if($color){
                        echo "<td class='borde'>$contadorcolorA
                              <input type='hidden' id='contador_colorMaximo_$contador' name='contador_colorMaximo_$contador' value='".$rs['MaxContadorCL']."'/>
                              <input type='hidden' id='contador_colorA_$contador' name='contador_colorA_$contador' value='$contadorcolorA'/>    
                              </td>";
                        echo "<td class='borde'>".$rs['UsuarioUltimaModificacion']."</td>";
                        echo "<td class='borde'>".$rs['FechaCreacion']."</td>";
                        echo "<td class='borde'>"; 
                        
                        $propuesto = false;
                        if($contadorcolor == "" && isset($contadores_propuestos[$serie]['color']) && $contadores_propuestos[$serie]['color'] != ""){
                            $contadorcolor = $contadores_propuestos[$serie]['color'];
                            $propuesto = true;
                        }
                        
                        echo "<input type='text' class='sizeMedium' id='contador_color_$contador' name='contador_color_$contador' value='$contadorcolor' MaxLength='8'/>"; 
                        if($contadorcolor != ""){
                            $checked = "checked='checked'";
                        }else{
                            $checked = "";
                        }
                                                
                        if($propuesto){
                            echo "<div style='font-size:12px; color:blue;'>Lectura capturada por el usuario <b>".$contadores_propuestos[$serie]['usuario']."</b> 
                                en fecha <b>".$catalogo->formatoFechaReportes(substr($contadores_propuestos[$serie]['fecha'], 0, 10))."</b>";
                            if(isset($contadores_propuestos[$serie]['ticket']) && $contadores_propuestos[$serie]['ticket'] != ""){
                                if($menu->tieneSubmenu($_SESSION['idUsuario'], 13)){
                                    echo " para el ticket <a href='../principal.php?mnu=mesa&action=lista_ticket&id=".$contadores_propuestos[$serie]['ticket']."' target='_blank' title='Ver ticket ".$contadores_propuestos[$serie]['ticket']."'>".$contadores_propuestos[$serie]['ticket']."</a>";
                                }else{
                                    echo " para el ticket ".$contadores_propuestos[$serie]['ticket'];
                                }
                            }
                            echo "</div>";
                        }
                        echo "</td>";
                    }else{
                        echo "<td class='borde'></td><td class='borde'></td>";
                        echo "<td class='borde'></td><td class='borde'></td>";
                    }                   
                    echo "</tr>";
                }
                echo "</table>";
            ?>
            <br/>
            <input type="hidden" id="numero_equipos" name="numero_equipos" value="<?php echo $contador; ?>"/>
            <input type="hidden" id="year" name="year" value="<?php echo $year; ?>"/>
            <input type="hidden" id="month" name="month" value="<?php echo $month; ?>"/>
            <input type="hidden" id="cc" name="cc" value="<?php echo $cc; ?>"/>
            <input type="hidden" id="cliente" name="cliente" value="<?php echo $cliente; ?>"/>
            <input type="hidden" id="date" name="date" value="<?php echo date('Y')."-".date('m')."-".date('d'); ?>"/>
            <div id="contenidos_invisibles" style="display: none;"></div>
            <input type="button" class="boton" onclick="guardarLecturas(); return false;" id="guardar_lectura" name="guardar_lectura" value="Guardar" style="margin-left: 76%;"/>
            <input type="submit" class="boton" id="excel" name="excel" value="Exportar excel" style="margin-left: 1%;"/>
        </form>
    </body>
</html>