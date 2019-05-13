<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$noSolicitud = $_GET['idMov'];
//$noSolicitud = 1;
$fecha = "";
$cliente = "";
$usuarioCreacion = "";
$recibe = "&nbsp;";
$autoriza = "Claudia Moreno Carrillo";
$catalogo = new Catalogo();

$consulta = "SELECT 
meq.id_movimientos, meq.tipo_movimiento, meq.NoSerie, meq.pendiente, meq.Fecha AS Fecha, e.Modelo,
CONCAT(c1.ClaveCliente,' - ',c1.NombreRazonSocial) AS cliente_anterior, c1.RFC AS rfc_anterior, con1.Nombre AS contacto_anterior, con1.Telefono AS telefono_anterior,
CONCAT(cc1.ClaveCentroCosto, ' - ' ,cc1.Nombre) AS centro_anterior, c2.RFC AS rfc_nuevo, con2.Nombre AS contacto_nuevo, con2.Telefono AS telefono_nuevo,
CONCAT(c2.ClaveCliente,' - ',c2.NombreRazonSocial) AS cliente_nuevo,
CONCAT(cc2.ClaveCentroCosto, ' - ' ,cc2.Nombre) AS centro_nuevo,
CONCAT(dom1.Calle,' No. Ext. ',dom1.NoExterior, ' No. Int. ',dom1.NoInterior) AS calle_anterior, dom1.CodigoPostal AS CP_anterior,
dom1.Colonia AS colonia_anterior, dom1.Delegacion AS delegacion_anterior, dom1.Ciudad AS ciudad_anterior,
CONCAT(dom2.Calle,' No. Ext. ',dom2.NoExterior, ' No. Int. ',dom2.NoInterior) AS calle_nuevo, dom2.CodigoPostal AS CP_nuevo,
dom2.Colonia AS colonia_nuevo, dom2.Delegacion AS delegacion_nuevo, dom2.Ciudad AS ciudad_nuevo,
alm1.nombre_almacen AS almacen_anterior, alm2.nombre_almacen AS almacen_nuevo,
e.Descripcion AS Descripcion,
cl.ContadorBNML, cl.ContadorBNPaginas, cl.ContadorColorML, cl.ContadorColorPaginas, cl.NivelTonAmarillo, cl.NivelTonCian, cl.NivelTonMagenta, cl.NivelTonNegro,
reportes_movimientos.id_reportes AS NoReporte
FROM `movimientos_equipo` AS meq 
INNER JOIN reportes_movimientos ON reportes_movimientos.id_movimientos =meq.id_movimientos
LEFT JOIN c_cliente AS c1 ON c1.ClaveCliente = meq.clave_cliente_anterior
LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = meq.clave_cliente_nuevo
LEFT JOIN c_centrocosto AS cc1 ON cc1.ClaveCentroCosto = meq.clave_centro_costo_anterior
LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = meq.clave_centro_costo_nuevo
LEFT JOIN c_contacto AS con1 ON con1.ClaveEspecialContacto = cc1.ClaveCentroCosto
LEFT JOIN c_contacto AS con2 ON con2.ClaveEspecialContacto = cc2.ClaveCentroCosto
LEFT JOIN c_domicilio AS dom1 ON dom1.ClaveEspecialDomicilio = cc1.ClaveCentroCosto
LEFT JOIN c_domicilio AS dom2 ON dom2.ClaveEspecialDomicilio = cc2.ClaveCentroCosto
LEFT JOIN c_almacen AS alm1 ON alm1.id_almacen = meq.almacen_anterior
LEFT JOIN c_almacen AS alm2 ON alm2.id_almacen = meq.almacen_nuevo
LEFT JOIN c_lectura AS cl ON cl.IdLectura = meq.id_lectura
LEFT JOIN c_bitacora as b ON b.NoSerie = meq.NoSerie
LEFT JOIN c_equipo as e ON e.NoParte = b.NoParte
WHERE meq.id_movimientos = " . $_GET['idMov'] . ";";
$query = $catalogo->obtenerLista($consulta);
if ($rs = mysql_fetch_array($query)) {
    $mensaje_destino = "";
    $mensaje_origen = "";
    $nombre_destino = "";
    $nombre_origen = "";
    switch ($rs['tipo_movimiento']) {
        case "1":
            $mensaje_origen = "CLIENTE ANTERIOR";
            $mensaje_destino = "CLIENTE NUEVO";
            $nombre_origen = $rs['cliente_anterior'];
            $nombre_destino = $rs['cliente_nuevo'];
            break;
        case "2":
            $mensaje_origen = "ALMACÉN ANTERIOR";
            $mensaje_destino = "CLIENTE NUEVO";
            $nombre_origen = $rs['almacen_anterior'];
            $nombre_destino = $rs['cliente_nuevo'];
            break;
        case "3":
            $mensaje_origen = "CLIENTE ANTERIOR";
            $mensaje_destino = "ALMACÉN NUEVO";
            $nombre_origen = $rs['cliente_anterior'];
            $nombre_destino = $rs['almacen_nuevo'];
            break;
        case "4":
            $mensaje_origen = "ALMACÉN ANTERIOR";
            $mensaje_destino = "ALMACÉN NUEVO";
            $nombre_origen = $rs['almacen_anterior'];
            $nombre_destino = $rs['almacen_nuevo'];
            break;
        case "5":
            $mensaje_origen = "CLIENTE ANTERIOR";
            $mensaje_destino = "CLIENTE NUEVO";
            $nombre_origen = $rs['cliente_anterior'];
            $nombre_destino = $rs['cliente_nuevo'];
            break;
        default:
            break;
    }
    ?>
    <!DOCTYPE HTML>
    <html lang="es">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <style>
                body{font-family: Arial; font-size: 15px;}
                .titulo{font-weight: bold; font-size: 18px;}
                table{
                    border-collapse:collapse;
                }            
                .borde{border: 1px solid #000;}
                .mediano{width: 30%;}
                .gigantes{width: 600px;}
                /*.pagebreak { page-break-after: always; page-break-before: always; }*/
                .espacio{min-height: 100px;}
                /*.obscuro{background-color: #404040; color: white; text-align: center;  font-style: italic; -webkit-print-color-adjust:exact;}
                .gris{background-color: #C0C0C0; font-weight: bold;  -webkit-print-color-adjust:exact;}
                .color{background-color: #3333CC; color: white; -webkit-print-color-adjust:exact; }
                .bn{background-color: #000; color: white; }*/
                .obscuro{color: black; text-align: center;  font-style: italic;}
                .gris{font-weight: bold; }
                .color{color: black;}
                .bn{color: black; }

                .pie{font-size: 10px; color: #800000;}
                .centrado {text-align: center;}
                .completeSize{width: 97%;}
            </style>
            <title>Reporte</title>
        </head>
        <body>
            <a href=javascript:window.print(); style="margin: 93%;">Imprimir</a>
            <div class="principal">
                <img src="../../../resources/images/kyocera_reporte.png" style="float:right; margin: 0% 20% 5% 0%;"/>
                <div class="titulo">FORMATO DE MOVIMIENTOS DE EQUIPOS</div>            
                <div style="margin-left: 83%; font-weight: bold; font-size: 20px;">No. Movimiento: <?php echo $rs['NoReporte']; ?></div>
                <br/><br/>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano obscuro">SE FACTURA POR</td>
                        <td class="borde mediano">SERVICIOS CORPORATIVOS GENESIS, S.A DE C.V.</td>
                    </tr>
                </table>
                <br/>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano obscuro">TIPO DE MOVIMIENTO</td>
                        <td class="borde mediano">MOVIMIENTO DE EQUIPO</td>
                        <td style="min-width: 50px;"></td>
                        <td class="borde gris">FECHA</td>
                        <td class="borde"><?php echo $rs['Fecha']; ?></td>
                    </tr>
                </table>            
            </div>
            <br/>
            <table class="completeSize">
                <tr>
                    <td colspan="4" class="borde obscuro"><?php echo $mensaje_origen ?></td>
                </tr>
                <tr>
                    <td class="borde gris">NOMBRE ó RAZON SOCIAL</td>
                    <td colspan="3" class="borde"><?php echo $nombre_origen; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CONTACTO COMERCIAL</td>
                    <td class="borde"><?php echo $rs['contacto_anterior']; ?></td>
                    <td class="borde gris">RFC</td>
                    <td class="borde"><?php echo $rs['rfc_anterior']; ?></td>
                </tr>
                <tr>
                    <td class="borde obscuro" colspan="4"><?php echo $rs['centro_anterior']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CALLE Y NÚMERO</td>
                    <td class="borde" colspan="3"><?php echo $rs['calle_anterior']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">COLONIA</td>
                    <td class="borde" colspan="3"><?php echo $rs['colonia_anterior']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DELEGACION ó MUNICIPIO</td>
                    <td class="borde" colspan="3"><?php echo $rs['delegacion_anterior']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CIUDAD / ESTADO</td>
                    <td class="borde" colspan="3"><?php echo $rs['ciudad_anterior']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">TELEFONO Y EXTENSION</td>
                    <td class="borde"><?php echo $rs['telefono_anterior']; ?></td>
                    <td class="borde gris">C. POSTAL</td>
                    <td class="borde"><?php echo $rs['CP_anterior']; ?></td>
                </tr>


                <tr>
                    <td colspan="4" class="borde obscuro"><?php echo $mensaje_destino ?></td>
                </tr>
                <tr>
                    <td class="borde gris">NOMBRE ó RAZON SOCIAL</td>
                    <td colspan="3" class="borde"><?php echo $nombre_destino; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CONTACTO COMERCIAL</td>
                    <td class="borde"><?php echo $rs['contacto_nuevo']; ?></td>
                    <td class="borde gris">RFC</td>
                    <td class="borde"><?php echo $rs['rfc_nuevo']; ?></td>
                </tr>
                <tr>
                    <td class="borde obscuro" colspan="4"><?php echo $rs['centro_nuevo']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CALLE Y NÚMERO</td>
                    <td class="borde" colspan="3"><?php echo $rs['calle_nuevo']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">COLONIA</td>
                    <td class="borde" colspan="3"><?php echo $rs['colonia_nuevo']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DELEGACION ó MUNICIPIO</td>
                    <td class="borde" colspan="3"><?php echo $rs['delegacion_nuevo']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CIUDAD / ESTADO</td>
                    <td class="borde" colspan="3"><?php echo $rs['ciudad_nuevo']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">TELEFONO Y EXTENSION</td>
                    <td class="borde"><?php echo $rs['telefono_nuevo']; ?></td>
                    <td class="borde gris">C. POSTAL</td>
                    <td class="borde"><?php echo $rs['CP_nuevo']; ?></td>
                </tr>

                <tr>
                    <td class="borde mediano centrado gris" colspan="4">DESCRIPCION DE EQUIPO</td>
                </tr>

                <tr>
                    <td class='borde'>No Serie</td>
                    <td class='borde'>Modelo</td>
                    <td class='borde' colspan="2">Descripcion</td>
                </tr>

                <tr>
                    <td class='borde'><?php echo $rs['NoSerie']; ?></td>
                    <td class='borde'><?php echo $rs['Modelo']; ?></td>
                    <td class='borde' width="400" colspan="2"><?php echo $rs['Descripcion']; ?></td>
                </tr>

                <tr>
                    <td class="borde obscuro" colspan="4">OBSERVACIONES</td>
                </tr>
                <tr>
                    <td class="borde" colspan="4"><div class="espacio"></div></td>
                </tr>
            </table>  
            <table class="completeSize">
                <tr>
                    <td class="borde centrado" style="width: 33%;">CLIENTE</td>
                    <td class="borde centrado" style="width: 33%;">EJECUTIVO DE CUENTAS</td>
                    <td class="borde centrado" style="width: 33%;">AUTORIZACIÓN</td>
                </tr>
                <tr>
                    <td class="borde"><div class="espacio"></div></td>
                    <td class="borde centrado"><div class="espacio"></div></td>
                    <td class="borde centrado"><div class="espacio centrado"></div>LIC. CLAUDIA MORENO</td>
                </tr>
                <tr>
                    <td class="borde centrado">FIRMA</td>
                    <td class="borde centrado">FIRMA</td>
                    <td class="borde centrado">FIRMA</td>
                </tr>
            </table> 
            <div class="pie">
                SERVICIOS CORPORATIVOS GENESIS, S.A DE C.V. RIO CHURUBUSCO No. Ext. 267 No. Int. ,COL. PRADO CHURUBUSCO COYOACAN, MÉXICO DISTRITO FEDERAL C.P. 04230 TELS.56465850-53468358
            </div>    
            <br/><br/><br/>
            <div style="page-break-after: always;"></div>
        </body>
    </html>
<?php } ?>
