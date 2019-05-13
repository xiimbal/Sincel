<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Factura.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");    

$permisos_grid = new PermisosSubMenu();

$factura = new Factura_NET();
$factura->getRegistrobyID($_GET['id']);
$ver_contactos_fac = $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 43);

$cat = new CatalogoFacturacion();
$catalogo = new Catalogo();
$empresa = new DatosFacturacionEmpresa();
if (!$empresa->getRegistroByRFC($factura->getRFCEmisor())) {
    echo "Error: no se encuentra registrado el emisor " . $factura->getRFCEmisor();
    return;
}

$tiene_contacto = false;
$consulta = "SELECT cc.ClaveCliente, cc.ClaveCentroCosto, cc.Nombre AS Localidad, fp.Folio, fp.FolioTimbrado,
    ctt.Nombre AS Contacto, ctt.IdContacto, ctt.CorreoElectronico,
    (CASE WHEN ctt.EnvioFactura = 1 AND ctt.IdTipoContacto = 13 THEN 1 ELSE 0 END) AS EnvioFactura
    FROM c_folio_prefactura AS fp
    LEFT JOIN c_factura AS f ON f.Folio = fp.Folio AND f.RFCEmisor = fp.IdEmisor
    LEFT JOIN c_facturadetalle AS fd ON fd.IdFactura = f.IdFactura
    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = fd.ClaveCentroCosto
    LEFT JOIN c_contacto AS ctt ON ctt.ClaveEspecialContacto = cc.ClaveCentroCosto 
    WHERE fp.FolioTimbrado = '" . $factura->getSerie() . $factura->getFolio() . "' AND fp.IdEmisor = " . $empresa->getIdDatosFacturacionEmpresa() . "
    AND !ISNULL(ctt.IdContacto) AND ctt.Activo = 1 AND (ctt.IdTipoContacto = 13 OR (ctt.IdTipoContacto = 15 AND ctt.EnvioFactura = 1)) 
    GROUP BY ctt.IdContacto;";


$parametros = new Parametros();
if ($parametros->getRegistroById(16)) {
    
}
?>
<html lang="es">
    <head>
        <title>Env&iacute;o de factura <?php echo $factura->getFolio(); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>       
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

        <script type="text/javascript" language="javascript" src="../resources/js/paginas/facturacion/enviar_factura_cfdi.js"></script>
    </head>
    <body>
        <form id="formeniar" method="POST" action = "../WEB-INF/Controllers/facturacion/Controller_enviar_Factura_cfdi.php">
            <table>
                <tr>
                    <td>
                        <label for="titulo">Titulo</label>
                    </td>
                    <td>
                        <input type="text" id="titulo" name="titulo" value="Factura electrónica <?php echo $factura->getFolio(); ?>" style="width: 400px"/>
                    </td>
                </tr>
                <tr>
                    <td>Contactos de envío (facturación)</td>
                    <td>
                        <select id="contactos" name="contactos[]" class="multiselect" multiple="multiple">
                            <?php
                            $result = $catalogo->obtenerLista($consulta);
                            if (mysql_num_rows($result) > 0) {
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($rs['EnvioFactura'] == "1") {
                                        $s = "selected = 'selected'";
                                        //$tiene_contacto = true;
                                    }
                                    echo "<option value='" . $rs['CorreoElectronico'] . "' $s>" . $rs['Contacto'] . " (" . $rs['CorreoElectronico'] . ")</option>";
                                }
                            } else {
                                $consulta2 = "SELECT ctt.Nombre, ctt.CorreoElectronico, ctc.Descripcion
                                            FROM c_contacto ctt
                                            LEFT JOIN c_tipocontacto ctc ON ctt.IdTipoContacto = ctc.IdTipoContacto
                                            WHERE (ClaveEspecialContacto IN 
                                            (SELECT cc.ClaveCliente FROM c_folio_prefactura AS fp 
                                                    LEFT JOIN c_factura AS f ON f.Folio = fp.Folio AND f.RFCEmisor = fp.IdEmisor
                                                    LEFT JOIN c_facturadetalle AS fd ON fd.IdFactura = f.IdFactura
                                                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = fd.ClaveCentroCosto
                                                    WHERE fp.Foliotimbrado = " . $factura->getSerie(). $factura->getFolio() . "
                                            ) OR ClaveEspecialContacto IN
                                            (SELECT cc.ClaveCentroCosto FROM c_folio_prefactura AS fp 
                                                    LEFT JOIN c_factura AS f ON f.Folio = fp.Folio AND f.RFCEmisor = fp.IdEmisor
                                                    LEFT JOIN c_facturadetalle AS fd ON fd.IdFactura = f.IdFactura
                                                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = fd.ClaveCentroCosto
                                                    WHERE fp.Foliotimbrado = " . $factura->getSerie(). $factura->getFolio() . "
                                            )) AND (ctt.IdTipoContacto = 13 OR (ctt.IdTipoContacto = 15 AND ctt.EnvioFactura = 1)) GROUP BY ctt.IdContacto;";
                                $result = $catalogo->obtenerLista($consulta2);
                                while ($rs = mysql_fetch_array($result)) {
                                    echo "<option value='" . $rs['CorreoElectronico'] . "' $s>" . $rs['Nombre'] . " " . $rs['CorreoElectronico'] . "(" . $rs['Descripcion'] . ")</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="correos">Otros Correos</label>
                    </td>
                    <td>
                        <?php
                        $cadena = "";
                        if (!$tiene_contacto) {
                            $correos = array();
                            $result = $cat->obtenerLista("SELECT RFCReceptor FROM c_factura WHERE IdFactura='" . $_GET['id'] . "';");
                            while ($rs2 = mysql_fetch_array($result)) {
                                $consulta = "SELECT CorreoElectronicoEnvioFact1, CorreoElectronicoEnvioFact2, CorreoElectronicoEnvioFact3, CorreoElectronicoEnvioFact4 FROM `c_cliente` WHERE RFC = '" . $rs2['RFCReceptor'] . "';";
                                $result = $catalogo->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($result)) {
                                    if (isset($rs['CorreoElectronicoEnvioFact1']) && $rs['CorreoElectronicoEnvioFact1'] != "") {
                                        $cadena.=$rs['CorreoElectronicoEnvioFact1'] . ";";
                                        array_push($correos, $rs['CorreoElectronicoEnvioFact1']);
                                    }
                                    if (isset($rs['CorreoElectronicoEnvioFact2']) && $rs['CorreoElectronicoEnvioFact2'] != "" && !in_array($rs['CorreoElectronicoEnvioFact2'], $correos)) {
                                        $cadena.=$rs['CorreoElectronicoEnvioFact2'] . ";";
                                        array_push($correos, $rs['CorreoElectronicoEnvioFact2']);
                                    }
                                    if (isset($rs['CorreoElectronicoEnvioFact3']) && $rs['CorreoElectronicoEnvioFact3'] != "" && !in_array($rs['CorreoElectronicoEnvioFact3'], $correos)) {
                                        $cadena.=$rs['CorreoElectronicoEnvioFact3'] . ";";
                                        array_push($correos, $rs['CorreoElectronicoEnvioFact3']);
                                    }
                                    if (isset($rs['CorreoElectronicoEnvioFact4']) && $rs['CorreoElectronicoEnvioFact4'] != "" && !in_array($rs['CorreoElectronicoEnvioFact4'], $correos)) {
                                        $cadena.=$rs['CorreoElectronicoEnvioFact4'] . ";";
                                        array_push($correos, $rs['CorreoElectronicoEnvioFact4']);
                                    }
                                }

                                //$result=$catalogo->obtenerLista("SELECT con.CorreoElectronico FROM c_contacto AS con INNER JOIN c_cliente AS cli ON cli.ClaveCliente=con.ClaveEspecialContacto WHERE cli.RFC='".$rs2['RFCReceptor']."';");
                                $result = $catalogo->obtenerLista("SELECT DISTINCT(con.CorreoElectronico) AS CorreoElectronico
                                    FROM c_contacto AS con 
                                    INNER JOIN c_cliente AS cli ON cli.ClaveCliente=con.ClaveEspecialContacto 
                                    WHERE cli.RFC='" . $rs2['RFCReceptor'] . "' AND con.IdTipoContacto = 13 AND con.Activo = 1;");
                                while ($rs = mysql_fetch_array($result)) {
                                    if (!in_array($rs['CorreoElectronico'], $correos)) {
                                        $cadena.=$rs['CorreoElectronico'] . ";";
                                        array_push($correos, $rs['CorreoElectronico']);
                                    }
                                }
                            }
                        }
                        ?>                        
                        <span style='font-size:10px;font-style: italic;color:grey;'>En caso de un correo extra, ingrese aquí. Para agregarlo como destino frecuente, hay que agregar como contacto de facturación.</span><br/>
                        <input type="text" id="correos" name="correos" value="<?php echo $cadena; ?>" style="width: 600px"/>
                    </td>
                </tr>
                <tr>
                    <td>Comentario</td>
                    <td>
                        <textarea  cols="100" id="comentario" name="comentario" style="height: 100px; resize: none;"><?php echo $parametros->getDescripcion(); ?></textarea>
                    </td>
                    <td></td>
                </tr>
                <input type="hidden" name="id" value="<?php echo $_GET['id'] ?>" id="id"/>
                <tr>
                    <td style=" text-align:right;" colspan="2">
                        <input type="button" name="Cancelar" class="boton" value="Cancelar" id="Cancelar" onclick="cambiarContenidos('facturacion/ReporteFacturacion_net.php', 'Facturas CFDI')"/>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="submit" name="GuardarPrefactura" class="boton" value="Enviar" id="GuardarPrefactura" />
                    </td>
                </tr>
            </table>
        </form> 
    </body>
</html>