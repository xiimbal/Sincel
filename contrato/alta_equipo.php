<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Anexo.class.php");
if (isset($_GET['cliente']) && $_GET['cliente'] != "" && (!isset($_GET['id']) || $_GET['id'] == "")) {
    $Cliente = new Cliente();
    $Cliente->getRegistroById($_GET['cliente']);
} else {
    $ClaveCentro = $_GET['id'];
    $CentroCosto = new CentroCosto();
    $CentroCosto->getRegistroById($ClaveCentro);
    $Cliente = new Cliente();
    $Cliente->getRegistroById($CentroCosto->getClaveCliente());
}

$back = "../";
if(isset($_GET['noback'])){
    $back = "";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Alta de equipos</title>
        <!-- JS -->
        <?php if (!isset($_GET['ind'])) { ?>
            <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
            <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
			<script src="../resources/js/jquery/jquery-ui.min.js"></script>     
            <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
            <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
            <!-- multiselect -->
            <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
            <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
            <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
            <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

            <script type="text/javascript" src="../resources/js/funciones.js"></script>
        <?php } ?>
        <script type="text/javascript" src="<?php echo $back; ?>resources/js/paginas/alta_serie.js"></script>
    </head>
    <body>        
        <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
            <img src="<?php echo $back; ?>resources/images/cargando.gif"/>                          
        </div>
        <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%;"></div>
        <h2>Insertar equipo en <b><?php echo $Cliente->getNombreRazonSocial();
        if (isset($CentroCosto)) {
            echo " - " . $CentroCosto->getNombre();
        } ?></b></h2>
        <div id="mensajes" style="font-size: 12px;"></div>
        <form id="form_equipo" name="form_equipo" action="/" method="POST">
            <table>
                <?php
                if (!isset($ClaveCentro)) {
                    echo "<tr>";
                    echo '<td><label for="cc">Localidad</label></td>';
                    echo '<td>';
                    echo '<select id="cc" name="cc" class="filtroselect" style="width: 250px;" 
                        onchange="actualizarAnexo(\'contrato\',\'anexo_tecnico\',\'servicio\',\'cc\',\'anexo\');">';
                    $cc = new CentroCosto();
                    $result = $cc->getRegistroValidacion($Cliente->getClaveCliente());
                    while ($rs = mysql_fetch_array($result)) {
                        echo "<option value='" . $rs['ClaveCentroCosto'] . "' selected='selected'>" . $rs['Nombre'] . "</option>";
                        $ClaveCentro = $rs['ClaveCentroCosto'];
                    }
                    echo '</select>';
                    echo '</td>';
                    echo "<tr>";
                } else {
                    echo '<input type="hidden" id="cc" name="cc" value="' . $ClaveCentro . '"/>';
                }
                ?>
                <tr>
                    <td><label for="serie">Serie</label></td>
                    <td><input type="text" id="serie" name="serie" onkeyup="quitarblancos('serie');" style="width: 250px;"/></td>
                </tr>
                <tr>
                    <td><label for="modelo">Modelo</label></td>
                    <td>
                        <select id="modelo" name="modelo" class="filtroselect" style="width: 250px;">
                            <option value="">Selecciona un modelo</option>
                            <?php
                            $catalogo = new Catalogo();
                            $result = $catalogo->obtenerLista("SELECT NoParte, Modelo FROM `c_equipo` WHERE Activo = 1 ORDER BY Modelo;");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<option value='" . $rs['NoParte'] . "'>" . $rs['Modelo'] . " - " . $rs['NoParte'] . "</option>";
                            }
                            ?>
                        </select>                        
                    </td>
                </tr>
                <tr>
                    <td><label for="ubicacion">Ubicación del equipo</label></td>
                    <td>
                        <textarea id="ubicacion" name="ubicacion" style="width: 350px; resize: none; height: 100px;"></textarea>
                    </td>
                </tr>
                            <?php if (!isset($_GET['ind'])) { ?>
                    <tr>
                        <td><label for="contrato">Contrato</label></td>
                        <td>
                            <select id="contrato" name="contrato" style="width: 250px;" onchange="cargarAnexos('contrato', 'anexo', '<?php echo $ClaveCentro; ?>', 'servicio');">
                                <?php
                                $query = $catalogo->obtenerLista("SELECT NoContrato, date(FechaInicio) as FechaInicio, date(FechaTermino) as FechaTermino FROM `c_contrato` WHERE ClaveCliente = '" . $Cliente->getClaveCliente() . "' AND Activo = 1;");
                                echo "<option value=\"\">Selecciona el contrato</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    echo "<option value=\"" . $rs['NoContrato'] . "\">" . $rs['NoContrato'] . " / " . $rs['FechaInicio'] . " / " . $rs['FechaTermino'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="anexo">Anexo</label></td>
                        <td>
                            <select id="anexo" name="anexo" style="width: 250px;" onchange="cargarServicios('anexo', 'servicio');">
                                <option value=''>Selecciona el anexo</option>;
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="servicio">Servicio</label></td>
                        <td>
                            <select id="servicio" style="width: 250px;" name="servicio">
                                <option value=''>Selecciona el servicio</option>;
                            </select>
                        </td>
                    </tr>
                <?php
                } else {
                    //echo "<script type='text/javascript'>$(\"#contenidos_invisibles\").load(dir, {'cc':$ClaveCentro , 'crear':'true'});</script>";
                    $prefijo_menor = strtolower($_GET['prefijo']);
                    $prefijo_mayor = strtoupper($_GET['prefijo']);
                    $consulta = "SELECT s.IdKServicio$prefijo_mayor, s.IdServicio$prefijo_mayor, kacc2.IdAnexoClienteCC, kacc.ClaveAnexoTecnico, cat.NoContrato 
                        FROM `k_servicio$prefijo_menor` AS s
                        INNER JOIN k_anexoclientecc AS kacc ON s.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                        LEFT JOIN k_anexoclientecc AS kacc2 ON kacc2.IdAnexoClienteCC = 
                        (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = kacc.ClaveAnexoTecnico AND CveEspClienteCC = '$ClaveCentro')
                        INNER JOIN c_anexotecnico AS cat ON kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico
                        WHERE s.IdKServicio$prefijo_mayor = " . $_GET['servicio'] . ";";
                    $result = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($result)) {
                        if (!isset($rs['IdAnexoClienteCC'])) {
                            $anexo = new Anexo();
                            $anexo->setClaveAnexoTecnico($rs['ClaveAnexoTecnico']);
                            $anexo->setClaveCC($ClaveCentro);
                            $anexo->setUsuarioCreacion($_SESSION['user']);
                            $anexo->setUsuarioUltimaModificacion($_SESSION['user']);
                            $anexo->setPantalla("Alta equipo plantilla");
                            if ($anexo->newK_anexoClienteCC()) {
                                $IdAnexoClienteCC = $anexo->getIdAnexoClienteCC();
                            }
                        } else {
                            $IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
                        }
                        echo '<input type="hidden" id="contrato" name="contrato" value="' . $rs['NoContrato'] . '"/>';
                        echo '<input type="hidden" id="anexo" name="anexo" value="' . $IdAnexoClienteCC . '"/>';
                        echo '<input type="hidden" id="anexo_tecnico" name="anexo_tecnico" value="' . $rs['ClaveAnexoTecnico'] . '"/>';
                        echo '<input type="hidden" id="servicio" name="servicio" value="' . $rs['IdServicio' . $prefijo_mayor] . '-' . $rs['IdKServicio' . $prefijo_mayor] . '"/>';
                    }
                    if ($prefijo_mayor == "GIM" || $prefijo_mayor == "GFA") {
                        echo '<input type="hidden" id="global" name="global" value="true"/>';
                    }
                }
                ?>
            </table>            
            <input type="hidden" id="cliente" name="cliente" value="<?php echo $Cliente->getClaveCliente(); ?>"/>
            <div id="contenidos_invisibles" style="display: none;"></div>
            <input type="submit" id="submit_equipo" name="submit_equipo" class="boton" value="Guardar" style="margin-left: 30%;"/>
            <?php
            if (isset($_GET['ind'])) {
                $servicio = $_GET['servicio'];
                $prefijo = $_GET['prefijo'];
                echo '<input type="hidden" id="independiente" name="independiente" value="true"/>';
                if(isset($_GET['noback'])){
                    echo '<input type="button" class="boton" value="Regresar" id="boton_regresar"
                    onclick="cargarDependencia(\'equipos_p2\',\'ventas/validacion/lista_equiposServicio.php\',\'' . $servicio . '\',null,\'' . $prefijo . '\'); return false;"/>';                    
                }else{
                    echo '<input type="button" class="boton" value="Regresar" id="boton_regresar"
                    onclick="cargarDependencia(\'equipos_p2\',\'../cliente/validacion/lista_equiposServicio.php\',\'' . $servicio . '\',null,\'' . $prefijo . '\'); return false;"/>';
                    echo "<input type='hidden' id='back_folder' name='back_folder' value='1'/>";
                }
            }
            ?>
        </form>
    </body>
</html>