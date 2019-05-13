<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet">
        <!--<script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_entrada_orden_compra.js"></script>-->
       
       
    </head>
    <body>
        <div class="principal">
            <a href="reportes/reporte_demo.php"><img class="imagenMouse" src="resources/images/excel.png" title="Exportar" style="float: right; cursor: pointer;" /></a>

            <table class="table-responsive" id="tAlmacen">
                <thead>
                    
                    <tr>
                    	<th align='center' scope='row' style="width: 5%">Id Solicitud</th>
                    	<th align='center' scope='row' style="width: 10%">Fecha Solicitud</th>
                    	<th align='center' scope='row' style="width: 5%">Fecha Regreso</th>
                        <th align='center' scope='row' style="width: 12%">No Parte </th>                        
                        <th align='center' scope='row' style="width: 20%">Modelo</th>
                        <th align='center' scope='row' style="width: 4%">No Serie</th>
                        <th align='center' scope='row' style="width: 4%">Nombre Cliente </th>
                        <th align='center' scope='row' style="width: 8%">Centro Costo Nombre</th>
                        <th align='center' scope='row' style="width: 2%">Clave Centro Costo</th>
                        <th align='center' scope='row' style="width: 5%">Clave Centro Costo2</th>
                        <!--<th align='center' scope='row' style="width: 5%">Moneda</th>
                        <th align='center' scope='row' style="width: 3%">Tipo Cambio</th>-->
                        <th align='center' scope='row' style="width: 7%">Clave Cliente</th>

                        
                    </tr>
                </thead>
                <tbody>
                    <?php                    
                        $consulta = "select `s`.`id_solicitud` AS `id_solicitud`,`s`.`fecha_solicitud` AS `fecha_solicitud`,`s`.`fecha_regreso` AS `fecha_regreso`,`b`.`NoParte` AS `NoParte`,`e`.`Modelo` AS `Modelo`,`b`.`NoSerie` AS `NoSerie`,(case when (`ks`.`ClaveCentroCosto` is not null) then (select `c_cliente`.`NombreRazonSocial` from (`c_centrocosto` join `c_cliente` on((`c_cliente`.`ClaveCliente` = `c_centrocosto`.`ClaveCliente`))) where (`c_centrocosto`.`ClaveCentroCosto` = `ks`.`ClaveCentroCosto`)) else `c`.`NombreRazonSocial` end) AS `NombreCliente`,(case when (`ks`.`ClaveCentroCosto` is not null) then (select `c_centrocosto`.`Nombre` from `c_centrocosto` where (`c_centrocosto`.`ClaveCentroCosto` = `ks`.`ClaveCentroCosto`)) else `cc`.`Nombre` end) AS `CentroCostoNombre`,`ksd`.`ClaveCentroCosto` AS `ClaveCentroCosto`,(case when (`ks`.`ClaveCentroCosto` is not null) then `ks`.`ClaveCentroCosto` else `cc`.`ClaveCentroCosto` end) AS `ClaveCentroCosto2`,(case when (`ks`.`ClaveCentroCosto` is not null) then (select `c_cliente`.`ClaveCliente` from (`c_centrocosto` join `c_cliente` on((`c_cliente`.`ClaveCliente` = `c_centrocosto`.`ClaveCliente`))) where (`c_centrocosto`.`ClaveCentroCosto` = `ks`.`ClaveCentroCosto`)) else `c`.`ClaveCliente` end) AS `ClaveCliente` from (((`c_centrocosto` `cc` left join (`k_anexoclientecc` `kacc` left join ((`c_inventarioequipo` `cinv` left join ((`c_solicitud` `s` left join `k_solicitud` `ksd` on((`ksd`.`id_solicitud` = `s`.`id_solicitud`))) left join `c_bitacora` `b` on(((`b`.`id_solicitud` = `s`.`id_solicitud`) and (`b`.`NoParte` = `ksd`.`Modelo`) and (`b`.`ClaveCentroCosto` = `ksd`.`ClaveCentroCosto`)))) on((`cinv`.`NoSerie` = `b`.`NoSerie`))) left join `k_serviciogimgfa` `ks` on((`ks`.`IdKserviciogimgfa` = `cinv`.`IdKserviciogimgfa`))) on((`kacc`.`IdAnexoClienteCC` = `cinv`.`IdAnexoClienteCC`))) on((`cc`.`ClaveCentroCosto` = `kacc`.`CveEspClienteCC`))) left join `c_cliente` `c` on((`c`.`ClaveCliente` = `cc`.`ClaveCliente`))) left join `c_equipo` `e` on((`e`.`NoParte` = `b`.`NoParte`))) where ((`cinv`.`Demo` = 1) and (`s`.`id_tiposolicitud` = 4) and (`ksd`.`tipo` = 0)) group by `b`.`NoSerie` having (`ksd`.`ClaveCentroCosto` = `ClaveCentroCosto2`) order by `s`.`id_solicitud`";
                    


                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['id_solicitud'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['fecha_solicitud'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['fecha_regreso'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                        //echo "<td align='center' scope='row'>" . $rs['NoParteAnterior'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NoSerie'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['CentroCostoNombre'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['ClaveCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['ClaveCentroCosto2'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['ClaveCliente'] . "</td>";
                        //echo "<td align='center' scope='row'>" . $rs['isDolar'] . "</td>";
                        //echo "<td align='center' scope='row'>" . $rs['TipoCambio'] . "</td>";
                        //echo "<td align='center' scope='row'>" . $rs['FechaOrdenCompra'] . "</td>";
                        //echo "<td align='center' scope='row'>" . $rs['FechaFactura'] . "</td>";
                        echo "</tr>";
                }
                ?>
                </tbody>
            </table>               
        </div>
    </body>
</html>
