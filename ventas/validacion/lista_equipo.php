<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if (!isset($_POST['idTicket'])) {
    header("Location: ../../index.php");
}

if (isset($_GET['NoSerie']) || $_GET['Modelo']) {
    $ModeloCodificado = $_GET['Modelo'];
    $ModeloEquipo = str_replace("__XX__", " ", $_GET['Modelo']);
    $NoSerieEquipo = $_GET['NoSerie'];
}else{
    $ModeloCodificado = "";
    $ModeloEquipo = "";
    $NoSerieEquipo = "";
}

$back = "";
include_once("../../WEB-INF/Classes/Equipo.class.php");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/alta_validacion.js"></script>        
    </head>
    <body>                
        <fieldset>
            <legend>Equipo</legend>
 
            <?php        
            $equipo = new Equipo();
            /* Obtenemos los posibles equipos asociados al ticket */
            $query = $equipo->getRegistroValidacion($ModeloEquipo, $NoSerieEquipo);
            //if(mysql_num_rows($query)){
            echo '<table class="filtro" style="min-width: 100%;">';
            echo '<thead>
                    <tr>                        
                        <td>No. de serie</td>
                        <td>Modelo</td>
                        <td>Ubicación</td>                        
                        <td></td>
                    </tr>
                </thead>';
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";                
                echo "<td align='center' scope='row'>" . $rs['NoSerie'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['Ubicacion'] . "</td>";                
                echo "<td align='center' scope='row'><a href='#' onclick='cambiarContenidoValidaciones(\"equipo2\",\"".$back."ventas/validacion/alta_equipo.php?Modelo=$ModeloCodificado&Serie=$NoSerieEquipo\", $(\"#idTicket\").val(),\"" . $rs['NoSerie'] . "\",false); return false;'>
                                            <img src=\"".$back."resources/images/Apply.png\"/></a></td>";
                echo "</tr>";
            }
            echo '';
            echo '</table>';
            // }
            ?>
            <br/><br/><br/>
        </fieldset>        
    </body>
</html>
