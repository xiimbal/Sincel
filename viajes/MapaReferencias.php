<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");

$pagina_lista = "mesa/lista_ticket.php";
$latitud_or = "";
$latitud_des = "";
$longitud_or = "";
$longitud_des = "";
$bloq = "";

$destino = "DESTINO TICKET";
//print_r($_GET);
if (isset($_GET['id'])) {
    $idTicket = $_GET['id'];
}
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT * FROM k_plantilla_asistencia WHERE IdTicket = " . $idTicket . ";");
if (mysql_num_rows($query) > 0) {
    $rs = mysql_fetch_array($query);
    $query1 = $catalogo->obtenerLista("SELECT cp.TipoEvento, cd.Latitud AS LatitudU, cd.Longitud AS LongitudU, cdo.Latitud AS LatitudC, cdo.Longitud AS LongitudC 
                                          FROM c_usuario cu, k_plantilla kp, c_area ca, c_domicilio_usturno cd, c_plantilla cp, c_domicilio cdo, c_ticket ct WHERE  ct.IdTicket=$idTicket AND cdo.ClaveEspecialDomicilio=ct.ClaveCentroCosto AND ca.IdArea=cd.IdCampania AND cd.IdUsuario=kp.idUsuario 
                                          AND cu.IdUsuario=kp.idUsuario AND kp.idPlantilla=cp.idPlantilla AND idK_PLantilla = " . $rs['idK_Plantilla'] . ";");
    $rs1 = mysql_fetch_array($query1);
    if ($rs1['TipoEvento'] == 1) {
        $latitud_or = $rs1['LatitudU'];
        $latitud_des = $rs1['LatitudC'];
        $longitud_or = $rs1['LongitudU'];
        $longitud_des = $rs1['LongitudC'];
    } else {
        $latitud_or = $rs1['LatitudC'];
        $latitud_des = $rs1['LatitudU'];
        $longitud_or = $rs1['LongitudC'];
        $longitud_des = $rs1['LongitudU'];
    }
} else {
    $query = $catalogo->obtenerLista("SELECT * FROM c_especial ce WHERE  ce.idTicket = " . $idTicket . ";");
    $rs = mysql_fetch_array($query);
    $latitud_or = $rs1['Latitud_or'];
    $latitud_des = $rs1['Latitud_des'];
    $longitud_or = $rs1['Longitud_or'];
    $longitud_des = $rs1['Longitud_des'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/viajes/alta_autoriza_especial.js"></script> 
    </head>
    <body>
        <div class="principal">
            <form id="formEspecial" name="formEspecial" action="/" method="POST">
                <table>
                    <tr>
                        <td>
                            <fieldset>
                                <legend><img src="resources/images/origen.png" title="Origen" style="float: left;" />Origen</legend>
                                <table>
                                    <tr>
                                        <td>Latitud</td><td><input type="number" id="Latitud_or" name="Latitud_or" value="<?php echo $latitud_or ?>" step="any" <?php echo $bloq; ?>></td>
                                        <td>Longitud</td><td><input type="number" id="Longitud_or" name="Longitud_or" value="<?php echo $longitud_or ?>" step="any" <?php echo $bloq; ?>></td>
                                    </tr>
                                </table>
                                <table>
                                    <tr>
                                        <td rowspan="2"> 
                                            <input align="center" type="button" value="Buscar Ubicación" class="boton" title="Buscar Dimicilio de acuerdo con las coordenadas" onclick="getLatLngText();" />                             
                                        </td>
                                        <td>
                                            <div id="fotocargandoPI" style="width:100%; display: none; ">
                                                <img src="resources/img/loading.gif"/>                             
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                        <td></td>
                        <td>
                            <fieldset>
                                <legend><img src="resources/images/destino.png" title="Origen" style="float: left;" />Destino</legend> 
                                <table>
                                    <tr>
                                        <td>Latitud</td><td><input type="number" id="Latitud_des" name="Latitud_des" value="<?php echo $latitud_des ?>" step="any" <?php echo $bloq; ?>></td>
                                        <td>Longitud</td><td><input type="number" id="Longitud_des" name="Longitud_des" value="<?php echo $longitud_des ?>" step="any" <?php echo $bloq; ?>></td>
                                    </tr>
                                </table>
                                <table>
                                    <tr>
                                        <td rowspan="2"> 
                                            <input align="center" type="button" value="Buscar Ubicación" class="boton" title="Buscar Dimicilio de acuerdo con las coordenadas" onclick="getLatLngText2();" />                             
                                        </td>
                                        <td>
                                            <div id="fotocargandoPI2" style="width:100%; display: none; ">
                                                <img src="resources/img/loading.gif"/>                             
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr></table>

                <table style="width: 95%;">
                    <tr>
                        <td style="vertical-align: text-top; width: 60%;"><!--Aqui se pone el mapa-->                        
                            <div id="map_canvas" style="width: 100%; height: 400px; left: 40px; position: relative" ></div>
                        </td>
                    </tr>
                </table>
                <br/>                    
            </form>
        </div>
    </body>
</html>
