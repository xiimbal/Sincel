<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Factura2.class.php");
$factura = new Factura();
$factura->setIdFactura($_GET['id']);
$factura->getRegistrobyID();
?>
<html lang="es">
    <head>
        <title>Env&iacute;o de factura <?php echo $factura->getFolio(); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/facturacion/enviar_factura_cfdi.js"></script>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>     
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
    </head>
    <body>
       <form id="formeniar" method="POST" action = "../WEB-INF/Controllers/facturacion/Controller_enviar_Factura.php">
            <table>
                <tr>
                    <td>
                        <label for="titulo">Titulo</label>
                    </td>
                    <td>
                        <input type="text" id="titulo" name="titulo" value="Pre-Factura electrónica <?php echo $factura->getFolio(); ?>" style="width: 400px"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="correos">Correos</label>
                    </td>
                    <td>
                        <?php 
                        $cat = new CatalogoFacturacion();
                        $result = $cat->obtenerLista("SELECT RFCReceptor FROM c_factura WHERE IdFactura='".$_GET['id']."'");
                        $cadena="";
                        if($rs2 =  mysql_fetch_array($result)){
                            $catalogo = new Catalogo();
                              
                            $result=$catalogo->obtenerLista("SELECT CorreoElectronicoEnvioFact1, CorreoElectronicoEnvioFact2, CorreoElectronicoEnvioFact3, CorreoElectronicoEnvioFact4 FROM `c_cliente` WHERE ClaveCliente = '".$rs2['RFCReceptor']."';");
                            while($rs = mysql_fetch_array($result)){
                                if(isset($rs['CorreoElectronicoEnvioFact1']) && $rs['CorreoElectronicoEnvioFact1']!=""){
                                    $cadena.=$rs['CorreoElectronicoEnvioFact1'].";";
                                }
                                if(isset($rs['CorreoElectronicoEnvioFact2']) && $rs['CorreoElectronicoEnvioFact2']!=""){
                                    $cadena.=$rs['CorreoElectronicoEnvioFact2'].";";
                                }
                                if(isset($rs['CorreoElectronicoEnvioFact3']) && $rs['CorreoElectronicoEnvioFact3']!=""){
                                    $cadena.=$rs['CorreoElectronicoEnvioFact3'].";";
                                }
                                if(isset($rs['CorreoElectronicoEnvioFact4']) && $rs['CorreoElectronicoEnvioFact4']!=""){
                                    $cadena.=$rs['CorreoElectronicoEnvioFact4'].";";
                                }
                            }
                            
                            /*$result=$catalogo->obtenerLista("SELECT con.CorreoElectronico FROM c_contacto AS con INNER JOIN c_cliente AS cli ON cli.ClaveCliente=con.ClaveEspecialContacto WHERE cli.RFC='".$rs2['RFCReceptor']."';");
                            while($rs = mysql_fetch_array($result)){
                                $cadena.=$rs['CorreoElectronico'].";";
                            } */
                        }
                        
                        ?>
                        <input type="text" id="correos" name="correos" value="<?php echo $cadena;?>" style="width: 600px"/>
                    </td>
                </tr>
                <tr>
                    <td>Comentario</td>
                    <td>
                        <textarea  cols="100" id="comentario" name="comentario" style="height: 100px; resize: none;">
                            <?php
                                $parametros = new Parametros();
                                if($parametros->getRegistroById(16)){
                                    echo $parametros->getDescripcion();
                                }
                            ?>
                        </textarea>
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