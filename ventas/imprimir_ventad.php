<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$id = $_GET['id'];
$catalogo = new Catalogo();

$consulta = "SELECT
	c_ventadirecta.Fecha AS Fecha,
        c_ventadirecta.IdVentaDirecta AS Num,
	CASE c_ventadirecta.Estatus
                WHEN 1 THEN 'Registrada'
                WHEN 2 THEN 'Facturada'
		WHEN 3 THEN 'Cancelada'
		WHEN 4 THEN 'Cerrada'
	END  AS Status,
        c_ventadirecta.Clave_Localidad AS ClaveCentroCosto,
	k_ventadirectadet.Cantidad AS Cantidad,
	k_ventadirectadet.TipoProducto AS Tipo,
	k_ventadirectadet.IdProduto AS IdProduto,
	k_ventadirectadet.Costo AS Costo,
	c_cliente.NombreRazonSocial AS NombreRazonSocial,
	c_cliente.RFC AS RFC,
	c_datosfacturacionempresa.RazonSocial AS Razon,
        c_datosfacturacionempresa.ImagenPHP,
	CONCAT(c_datosfacturacionempresa.RazonSocial,' ',c_datosfacturacionempresa.Calle,' No. Ext. ',c_datosfacturacionempresa.NoExterior,' No. Int. ',c_datosfacturacionempresa.NoInterior,' ,COL. ',c_datosfacturacionempresa.Colonia,' ',c_datosfacturacionempresa.Delegacion,', ',c_datosfacturacionempresa.Pais,' ',c_datosfacturacionempresa.Estado,' C.P. ',c_datosfacturacionempresa.CP,' TELS.',c_datosfacturacionempresa.Telefono) AS facturacion,
	(CASE WHEN !ISNULL(ctt.IdContacto) THEN ctt.Nombre ELSE CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoMaterno,' ',c_usuario.ApellidoPaterno) END) AS Contacto,
	(CASE 
        WHEN k_ventadirectadet.TipoProducto = 0 THEN (SELECT CONCAT(MAX(e.Modelo),' / ',e.NoParte) FROM `c_equipo` AS e WHERE e.NoParte = k_ventadirectadet.IdProduto) 
        ELSE (SELECT CONCAT(MAX(com.Modelo),' / ',com.Modelo,' / ',com.Descripcion) FROM `c_componente` AS com WHERE com.NoParte = k_ventadirectadet.IdProduto) END) AS Modelo,
        c_bitacora.NoSerie AS Serie,
        c_solicitud.comentario
        FROM c_ventadirecta
        INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta = c_ventadirecta.IdVentaDirecta
        INNER JOIN c_cliente ON c_cliente.ClaveCliente = c_ventadirecta.ClaveCliente
        INNER JOIN c_usuario ON c_usuario.IdUsuario= c_cliente.EjecutivoCuenta
        LEFT JOIN c_datosfacturacionempresa ON c_datosfacturacionempresa.IdDatosFacturacionEmpresa = c_cliente.IdDatosFacturacionEmpresa
        LEFT JOIN c_solicitud ON c_solicitud.id_solicitud=c_ventadirecta.id_solicitud 
        LEFT JOIN c_contacto AS ctt ON ctt.IdContacto = c_solicitud.IdContacto 
        LEFT JOIN c_bitacora ON c_bitacora.id_solicitud=c_ventadirecta.id_solicitud AND c_bitacora.NoParte = k_ventadirectadet.IdProduto
        WHERE c_ventadirecta.IdVentaDirecta =" . $id;
//echo $consulta;
$query_solicitud = $catalogo->obtenerLista($consulta);
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
        <a href=javascript:window.print(); style="margin: 93%;"><img src="../resources/images/icono_impresora.png" style="width: 24px; height: 24px;"/></a>
        <?php
        $resultSet = mysql_fetch_array($query_solicitud);
        if(isset($resultSet['comentario'])){
            $comentario = $resultSet['comentario'];
        }else{
            $comentario = "";
        }
        ?>
        <div class="principal">
            <img src="../<?php echo $resultSet['ImagenPHP']; ?>" style="float:right; margin: 0% 20% 5% 0%;"/>
            <div class="titulo">FORMATO DE VENTAS DIRECTAS</div>       
            <div style="margin-left: 83%; font-weight: bold; font-size: 20px;">No. Venta Directa: <?php echo $id; ?></div>
            <br/><br/>
            <table class="completeSize">
                <tr>
                    <td class="borde mediano obscuro">SE FACTURA POR</td>
                    <td class="borde mediano"><?php echo $resultSet['Razon']; ?></td>
                </tr>
            </table>
            <br/>
            <table class="completeSize">
                <tr>
                    <td class="borde mediano obscuro">TIPO DE MOVIMIENTO</td>
                    <td class="borde mediano">VENTA DIRECTA</td>
                    <td style="min-width: 50px;"></td>
                    <td class="borde gris">FECHA</td>
                    <td class="borde"><?php echo $resultSet['Fecha']; ?></td>
                </tr>
            </table>            
        </div>
        <br/>
        <table class="completeSize">
            <tr>
                <td colspan="4" class="borde obscuro">DATOS GENERALES</td>
            </tr>
        </table>
        <table class="completeSize">
            <?php
            $query = $catalogo->obtenerLista("SELECT
                            c_cliente.NombreRazonSocial AS Nombre,
                            c_cliente.RFC AS RFC,
                            CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoMaterno,\" \",c_usuario.ApellidoPaterno) AS Contacto,
                            c_centrocosto.Nombre AS CentroCosto,
                            CONCAT(c_domicilio.Calle,\" No. Ext: \",c_domicilio.NoExterior,\" No. Int: \",c_domicilio.NoInterior) AS Calle,
                            c_domicilio.Colonia AS Colonia,
                            c_domicilio.Delegacion AS Delegacion,
                            c_domicilio.Estado AS Estado,
                            c_domicilio.CodigoPostal AS CP
                            FROM c_cliente
                            INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                            INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta
                            LEFT JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                            WHERE c_centrocosto.ClaveCentroCosto='" . $resultSet['ClaveCentroCosto'] . "'");
            if ($rss = mysql_fetch_array($query)) {
                ?>
                <tr>
                    <td class="borde gris" width="300">NOMBRE ó RAZON SOCIAL</td>
                    <td colspan="3" class="borde"><?php echo $rss['Nombre']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CONTACTO COMERCIAL</td>
                    <td class="borde"><?php echo $resultSet['Contacto']; ?></td>
                    <td class="borde" colspan="2"><b>RFC:</b> <?php echo $rss['RFC']; ?>
                </tr>
                <tr>
                    <td class="borde obscuro" colspan="4"><?php echo $rss['CentroCosto']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CALLE Y NÚMERO</td>
                    <td class="borde" colspan="3"><?php echo $rss['Calle']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">COLONIA</td>
                    <td class="borde" colspan="3"><?php echo $rss['Colonia']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DELEGACION ó MUNICIPIO</td>
                    <td class="borde" colspan="3"><?php echo $rss['Delegacion']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CIUDAD / ESTADO</td>
                    <td class="borde" colspan="3"><?php echo $rss['Estado']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">TELEFONO Y EXTENSION</td>
                    <td class="borde"></td>
                    <td class="borde" colspan="2"><b>C. POSTAL</b>: <?php echo $rss['CP']; ?></td>
                </tr>
            </table>
        <?php } ?>
        <table class="completeSize">
            <tr>
                <td class="borde obscuro" colspan="5">DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES</td>
            </tr>
            <tr>
                <td class='borde centrado obscuro'>CANTIDAD</td>
                <td class='borde centrado obscuro'>MODELO</td>
                <td class='borde centrado obscuro'>SERIE</td>
                <td class='borde centrado obscuro'>COSTO</td>
                <td class='borde centrado obscuro'>TOTAL</td>
            </tr>
            <?php
            echo "<tr>";
            if ($resultSet['Serie'] == "") {
                echo "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                echo "<td class='borde centrado'>" . $resultSet['Modelo'] . "</td><td class='borde centrado'></td><td class='borde centrado'>$" . number_format($resultSet['Costo'], 2) . "</td>
                            <td class='borde centrado'>$" . number_format($resultSet['Cantidad'] * $resultSet['Costo'], 2) . "</td>";
            } else {
                echo "<td class='borde centrado'>1</td>";
                echo "<td class='borde centrado'>" . $resultSet['Modelo'] . "</td><td class='borde centrado'>" . $resultSet['Serie'] . "</td><td class='borde centrado'>$" . number_format($resultSet['Costo'], 2) . "</td>
                            <td class='borde centrado'>$" . number_format($resultSet['Costo'], 2) . "</td>";
            }
            echo "</tr>";            
            while ($resultSet = mysql_fetch_array($query_solicitud)) {
                echo "<tr>";
                if ($resultSet['Serie'] == "") {
                    echo "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                    echo "<td class='borde centrado'>" . $resultSet['Modelo'] . "</td><td class='borde centrado'></td><td class='borde centrado'>$" . number_format($resultSet['Costo'], 2) . "</td>
                            <td class='borde centrado'>$" . number_format($resultSet['Cantidad'] * $resultSet['Costo'], 2) . "</td>";
                } else {
                    echo "<td class='borde centrado'>1</td>";
                    echo "<td class='borde centrado'>" . $resultSet['Modelo'] . "</td><td class='borde centrado'>" . $resultSet['Serie'] . "</td><td class='borde centrado'>$" . number_format($resultSet['Costo'], 2) . "</td>
                            <td class='borde centrado'>$" . number_format($resultSet['Costo'], 2) . "</td>";
                }
                echo "</tr>";
                
            }
            ?>  
            <tr>
                <td class="borde obscuro" colspan="5">OBSERVACIONES</td>
            </tr>
            <tr>
                <td class="borde" colspan="5"><div class="espacio"><?php echo $comentario; ?></div></td>
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
            <?php echo $resultSet['facturacion']; ?>
        </div>    
        <br/><br/><br/>        
    </body>
</html>
