<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$idNotaTicket = $_GET['idNota'];
$catalogo = new Catalogo();
//echo $idNotaTicket . "<br/>";
$consulta = "SELECT mc.IdMovimiento,nt.IdNotaTicket,t.IdTicket,t.FechaHora,c.NombreRazonSocial,t.DescripcionReporte,nt.DiagnosticoSol,
nt.FechaHora AS fechaNota,mc.CantidadMovimiento,cc.Modelo,a.nombre_almacen,t.NombreCentroCosto,c.RFC,d.Calle,d.Colonia,d.Delegacion,d.Estado,d.CodigoPostal,
f.RazonSocial,CONCAT(f.Telefono) AS telefono,ct.Nombre AS contacto,cc.Descripcion,
CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono)AS domicilioFiscal,nt.UsuarioSolicitud 
FROM c_notaticket nt,c_ticket t,movimiento_componente mc, c_cliente c,c_componente cc,c_domicilio d,c_contacto ct,c_datosfacturacionempresa f,c_almacen a
WHERE nt.IdTicket=t.IdTicket
AND nt.IdNotaTicket=mc.IdNotaTicket
AND t.ClaveCliente=c.ClaveCliente
AND d.ClaveEspecialDomicilio=t.ClaveCliente
AND c.ClaveCliente=ct.ClaveEspecialContacto
AND c.IdDatosFacturacionEmpresa=f.IdDatosFacturacionEmpresa
AND cc.NoParte=mc.NoParteComponente
AND mc.IdAlmacenAnterior=a.id_almacen
AND nt.IdNotaTicket IN ($idNotaTicket)
GROUP BY mc.IdMovimiento ";
$query = $catalogo->obtenerLista($consulta);
$solicito = "";
$domicilioFiscal = "";

//echo $consulta;
//$noSolicitud = $_GET['noSolicitud'];
////$noSolicitud = 2;
//
//
//
//$consulta = "SELECT cc.ClaveCentroCosto, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,
//cli.NombreRazonSocial, cli.RFC, co.Nombre AS contacto, co.Telefono, bi.NoSerie,  d.*, cc.Nombre As centro,
//(CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
//f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion,
//CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS solicito
//FROM c_solicitud AS c
//INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
//INNER JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto
//INNER JOIN c_cliente AS cli ON cli.ClaveCliente = cc.ClaveCliente
//LEFT JOIN c_bitacora AS bi ON bi.id_solicitud = c.id_solicitud AND bi.NoParte = ks.Modelo
//LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = ks.ClaveCentroCosto
//LEFT JOIN c_contacto AS co ON co.ClaveEspecialContacto = cc.ClaveCentroCosto
//LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa
//LEFT JOIN c_usuario AS u ON c.id_crea = u.IdUsuario
//GROUP BY cc.ClaveCentroCosto ORDER BY cc.ClaveCentroCosto;"; /* Obtenemos las localidades de la solicitud */
////echo $consulta;
//$query_solicitud = $catalogo->obtenerLista($consulta);
//$solicito = "";
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
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <?php
        if ($rs = mysql_fetch_array($query)) {
            ?>
            <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>    
            <div class="principal">            
                <img src="../resources/images/kyocera_reporte.png" style="float:right; margin: 0% 20% 5% 0%; height: 45px;"/>            
                <div class="titulo">FORMATO DE SOLICITUD DE REFACCIONES</div>                        
                <div style="margin-left: 83%; font-weight: bold; font-size: 20px;">No. Solicitud: <?php echo $rs['IdMovimiento'] ?></div>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano obscuro centrado">SE FACTURA POR</td>
                        <td class="borde centrado" style="width: 75%;"><?php echo $rs['RazonSocial'] ?></td>
                    </tr>
                </table>
                <br/>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano centrado obscuro">TIPO DE MOVIMIENTO</td>
                        <td class="borde mediano centrado">Solicitud de refacción</td>
    <!--                        <td class="borde mediano centrado"><?php echo "SALIDA DE ALMACÉN:" . $rs['nombre_almacen'] . ""; ?></td>-->
                        <td style="min-width: 50px;"></td>
                        <td class="borde mediano gris centrado">FECHA</td>
                        <td class="borde mediano centrado"><?php echo $rs['fechaNota'] ?></td>
                    </tr>
                </table>            
            </div>
            <br/>
            <table class="completeSize">
                <tr>
                    <td colspan="4" class="borde obscuro">DATOS GENERALES</td>
                </tr>
                <tr>
                    <td class="borde gris">NOMBRE ó RAZON SOCIAL</td>
                    <td colspan="3" class="borde"><?php echo $rs['NombreRazonSocial'] ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CONTACTO COMERCIAL</td>
                    <td class="borde"><?php echo $rs['contacto'] ?></td>
                    <td class="borde gris" style="text-align: right;">RFC</td>
                    <td class="borde"><?php echo $rs['RFC'] ?></td>
                </tr>
                <tr>
                    <td class="borde obscuro" colspan="4"><?php echo $rs['NombreCentroCosto'] ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CALLE Y NÚMERO</td>
                    <td class="borde" colspan="3"><?php
                        echo $rs['Calle']
//                        echo $resultSet['Calle'];
//                        if ($resultSet['NoExterior'] != null) {
//                            echo " No. Ext: " . $resultSet['NoExterior'];
//                        }
//                        if ($resultSet['NoInterior'] != null) {
//                            echo " No. Int: " . $resultSet['NoInterior'];
////                        }
//                        echo "calle y numero";
                        ?></td>
                </tr>
                <tr>
                    <td class="borde gris">COLONIA</td>
                    <td class="borde" colspan="3"><?php echo $rs['Colonia'] ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DELEGACION ó MUNICIPIO</td>
                    <td class="borde" colspan="3"><?php echo $rs['Delegacion'] ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CIUDAD / ESTADO</td>
                    <td class="borde" colspan="3"><?php echo $rs['Estado'] ?></td>
                </tr>
                <tr>
                    <td class="borde gris">TELEFONO Y EXTENSION</td>
                    <td class="borde" style="min-width: 20%;"><?php echo $rs['telefono'] ?></td>
                    <td class="borde gris" style="text-align: right;">C. POSTAL</td>
                    <td class="borde"><?php echo $rs['CodigoPostal'] ?></td>
                </tr>         
                <tr>
                    <td class="borde obscuro" colspan="4">DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES</td>
                </tr>
                <?php
                $solicito = $rs['UsuarioSolicitud'];
                $domicilioFiscal = $rs['domicilioFiscal'];
            }
            ?>
            <tr>
                <td class='borde'>CANTIDAD</td>
                <td class='borde'>MODELO</td>
                <td class='borde'>DESCRIPCIÓN</td>
                <td class='borde'>No SERIE</td>
            </tr>
            <?php
            mysql_data_seek($query, 0);
            while ($rs = mysql_fetch_array($query)) {
                ?>
                <tr>
                    <td class='borde'><?php echo $rs['CantidadMovimiento'] ?></td>
                    <td class='borde'><?php echo $rs['Modelo'] ?></td>
                    <td class='borde'><?php echo $rs['Descripcion'] ?></td>
                    <td class='borde'></td>
                </tr>
<?php } ?>
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
                <td class="borde centrado"><div class="espacio"></div><?php echo $solicito; ?></td>
                <td class="borde centrado"><div class="espacio centrado"></div>LIC. CLAUDIA MORENO</td>
            </tr>
            <tr>
                <td class="borde centrado">FIRMA</td>
                <td class="borde centrado">FIRMA</td>
                <td class="borde centrado">FIRMA</td>
            </tr>
        </table>  
        <div class="pie">
<?php echo $domicilioFiscal ?>
        </div>    
        <br/><br/><br/>
        <div style="page-break-after: always;"></div>

    </body>
</html>
