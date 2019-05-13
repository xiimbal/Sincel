<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/DomicilioAlmacen.class.php");
$noSolicitud = $_GET['noSolicitud'];


$catalogo = new Catalogo();
$equipo = new EquipoCaracteristicasFormatoServicio();
$parametros = new Parametros();

if($parametros->getRegistroById(25)){
    $contadorUsado = $parametros->getValor();
}else{
    $contadorUsado = 50;
}

$consulta = "SELECT cc.ClaveCentroCosto, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,DATE(c.fecha_solicitud) AS fecha_sol,cts.Nombre AS tipoSolicitud,c.id_almacen,
cli.NombreRazonSocial, cli.RFC, (CASE WHEN !ISNULL(ctt.IdContacto) THEN ctt.Nombre ELSE co.Nombre END) AS contacto, 
(CASE WHEN !ISNULL(ctt.Telefono) THEN ctt.Telefono ELSE co.Telefono END) AS Telefono, bi.NoSerie,  d.*, cc.Nombre As centro,
(CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion,
f.ImagenPHP,
CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS solicito,c.comentario AS Comentario,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.IdKServicioGIM WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.IdKServicioGFA WHEN !ISNULL(im.IdKServicioIM) THEN im.IdKServicioIM WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.IdKServicioGFA ELSE 0 END) AS IdKServicio,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.RentaMensual WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.RentaMensual WHEN !ISNULL(im.IdKServicioIM) THEN im.RentaMensual WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.RentaMensual ELSE 0 END) AS RentaMensual,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.PaginasIncluidasBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosBN WHEN !ISNULL(im.IdKServicioIM) THEN im.PaginasIncluidasBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosBN ELSE 0 END) AS IncluidosBN,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.PaginasIncluidasColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosColor WHEN !ISNULL(im.IdKServicioIM) THEN im.PaginasIncluidasColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosColor ELSE 0 END) AS IncluidosColor,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginasExcedentesBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesBN WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginasExcedentesBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesBN ELSE 0 END) AS CostoExcedentesBN,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginasExcedentesColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesColor WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginasExcedentesColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesColor ELSE 0 END) AS CostoExcedentesColor,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginaProcesadaBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosBN WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginaProcesadaBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosBN ELSE 0 END) AS CostoProcesadaBN,
(CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginaProcesadaColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosColor WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginaProcesadaColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosColor ELSE 0 END) AS CostoProcesadaColor,
cat.ClaveAnexoTecnico, ctt2.NoContrato
FROM c_solicitud AS c
INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
INNER JOIN c_tiposolicitud AS cts ON cts.IdTipoMovimiento = c.id_tiposolicitud
LEFT JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto
INNER JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente
LEFT JOIN k_solicitudbitacora AS kb ON kb.id_solicitud = c.id_solicitud AND kb.NoParte = ks.Modelo
LEFT JOIN c_bitacora AS bi ON bi.id_bitacora = kb.id_bitacora
LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = ks.ClaveCentroCosto
LEFT JOIN c_contacto AS co ON co.ClaveEspecialContacto = cc.ClaveCentroCosto
LEFT JOIN c_contacto AS ctt ON ctt.IdContacto = c.IdContacto
LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa
LEFT JOIN c_usuario AS u ON c.id_crea = u.IdUsuario
LEFT JOIN k_serviciogim AS gim ON gim.IdKServicioGIM = (SELECT MIN(IdKServicioGIM) FROM k_serviciogim WHERE (ks.IdKServicio = IdKServicioGIM OR (ISNULL(ks.IdKServicio) AND ks.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGIM = ks.IdServicio)
LEFT JOIN c_serviciogim AS cgim ON cgim.IdServicioGIM = gim.IdServicioGIM
LEFT JOIN k_serviciogfa AS gfa ON gfa.IdKServicioGFA = (SELECT MIN(IdKServicioGFA) FROM k_serviciogfa WHERE (ks.IdKServicio = IdKServicioGFA OR (ISNULL(ks.IdKServicio) AND ks.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGFA = ks.IdServicio)
LEFT JOIN c_serviciogfa AS cgfa ON cgfa.IdServicioGFA = gfa.IdServicioGFA
LEFT JOIN k_servicioim AS im ON im.IdKServicioIM = (SELECT MIN(IdKServicioIM) FROM k_servicioim WHERE (ks.IdKServicio = IdKServicioIM OR (ISNULL(ks.IdKServicio) AND ks.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioIM = ks.IdServicio)
LEFT JOIN c_servicioim AS cim ON cim.IdServicioIM = im.IdServicioIM
LEFT JOIN k_serviciofa AS fa ON fa.IdKServicioFA = (SELECT MIN(IdKServicioFA) FROM k_serviciofa WHERE (ks.IdKServicio = IdKServicioFA OR (ISNULL(ks.IdKServicio) AND ks.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioFA = ks.IdServicio)
LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = fa.IdServicioFA
LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = ks.IdAnexoClienteCC
LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
LEFT JOIN c_contrato AS ctt2 ON ctt2.NoContrato = cat.NoContrato
GROUP BY cc.ClaveCentroCosto ORDER BY cc.ClaveCentroCosto;"; /* Obtenemos las localidades de la solicitud */

$query_solicitud = $catalogo->obtenerLista($consulta);
$numResults = mysql_num_rows($query_solicitud);
$counter = 0;
$solicito = "";
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
            @media print {
                * { margin: 0 !important; padding: 0 !important; }
                #controls, .footer, .footerarea{ display: none; }
                html, body {
                    /*changing width to 100% causes huge overflow and wrap*/
                    height:80%; 
                    background: #FFF; 
                    font-size: 9.5pt;
                }
                img.imagen{width:75px; height:30px;}
                img.imagens{width:75px; height:30px;}
                template { width: auto; left:0; top:0; }
                li { margin: 0 0 10px 20px !important;}
            }
        </style>
        <title>Reporte</title>
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;"><img src="../resources/images/icono_impresora.png" style="width: 24px; height: 24px;"/></a>
        <?php
        while ($resultSet = mysql_fetch_array($query_solicitud)) {//while que recorre todas las localidades de la solicitud
            $counter++;
            $solicito = $resultSet['solicito'];
            ?>
            <div class="principal">            
                <img src="../<?php echo $resultSet['ImagenPHP']; ?>" style="float:right; margin: 0% 10% 8% 0%;"/>            
                <div class="titulo">FORMATO DE SOLICITUD DE EQUIPOS</div>                        
                <div style="margin-left: 83%; font-weight: bold; font-size: 20px;">No. Solicitud: <?php echo $noSolicitud; ?></div>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano obscuro centrado">SE FACTURA POR</td>
                        <td class="borde centrado" style="width: 75%;"><?php echo $resultSet['razon']; ?></td>
                    </tr>
                </table>
                <br/>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano centrado obscuro">TIPO DE MOVIMIENTO</td>
                        <td class="borde mediano centrado"><?php echo $resultSet['tipoSolicitud']; ?></td>
                        <td style="min-width: 50px;"></td>
                        <td class="borde mediano gris centrado">PERÍODO EN ARRENDAMIENTO</td>
                        <td class="borde mediano centrado">
                            <?php
                                echo substr($catalogo->formatoFechaReportes($resultSet['fecha_sol']),5)." /<br/>";
                                echo $resultSet['fecha']; 
                            ?>
                        </td>
                    </tr>
                </table>            
            </div>
            <br/>
            <?php if(!isset($resultSet['id_almacen']) || $resultSet['id_almacen']==""){ ?>
            <table class="completeSize">
                <tr>
                    <td colspan="4" class="borde obscuro">DATOS GENERALES</td>
                </tr>
                <tr>
                    <td class="borde gris">NOMBRE ó RAZON SOCIAL</td>
                    <td colspan="3" class="borde"><?php echo $resultSet['NombreRazonSocial']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CONTACTO COMERCIAL</td>
                    <td class="borde"><?php echo $resultSet['contacto']; ?></td>
                    <td class="borde gris" style="text-align: right;">RFC</td>
                    <td class="borde"><?php echo $resultSet['RFC']; ?></td>
                </tr>
                <?php
                    if(isset($resultSet['centro'])){/*Si existe la localidad*/
                ?>
                <tr>
                    <td class="borde obscuro" colspan="4"><?php echo $resultSet['centro']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CALLE Y NÚMERO</td>
                    <td class="borde" colspan="3"><?php
                        echo $resultSet['Calle'];
                        if ($resultSet['NoExterior'] != null) {
                            echo " No. Ext: " . $resultSet['NoExterior'];
                        }
                        if ($resultSet['NoInterior'] != null) {
                            echo " No. Int: " . $resultSet['NoInterior'];
                        }
                        ?></td>
                </tr>
                <tr>
                    <td class="borde gris">COLONIA</td>
                    <td class="borde" colspan="3"><?php echo $resultSet['Colonia']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DELEGACION ó MUNICIPIO</td>
                    <td class="borde" colspan="3"><?php echo $resultSet['Delegacion']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CIUDAD / ESTADO</td>
                    <td class="borde" colspan="3"><?php echo $resultSet['Ciudad']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">TELEFONO Y EXTENSION</td>
                    <td class="borde" style="min-width: 20%;"><?php echo $resultSet['Telefono']; ?></td>
                    <td class="borde gris" style="text-align: right;">C. POSTAL</td>
                    <td class="borde"><?php echo $resultSet['CodigoPostal']; ?></td>
                </tr>
                <?php
                    }else{
                ?>
                <tr>
                    <td class="borde obscuro" colspan="4">Sin localidad</td>
                </tr>
                <?php
                    }
                ?>                    
            </table>
            <table class="completeSize">
                <tr>
                    <?php 
                    /*$columnas = 0;
                    $servicios = array("IdKServicio" => "Id","RentaMensual"=>"Renta Mensual ($)", "IncluidosBN"=>"Incluidos B/N", "IncluidosColor"=>"Incluidos Color", 
                        "CostoExcedentesBN"=>"Costo Excedentes B/N ($)", "CostoExcedentesColor"=>"Costo Excedentes Color ($)", 
                        "CostoProcesadaBN"=>"Costo Procesados B/N ($)", "CostoProcesadaColor"=>"Costo Procesados Color ($)");
                    foreach ($servicios as $key => $value) {
                        if(isset($resultSet[$key]) && !empty($resultSet[$key])){
                            echo "<td class='borde obscuro'>$value:</td>";
                            echo "<td class='borde obscuro'>".number_format($resultSet[$key],2)."</td>";
                            $columnas++;
                        }
                    }  */
                    $columas = 0;     
                    $servicios = array("IdKServicio" => "Id Servicio","ClaveAnexoTecnico" => "Anexo", "NoContrato" => "Contrato");
                    foreach ($servicios as $key => $value) {
                        if(isset($resultSet[$key]) && !empty($resultSet[$key])){
                            echo "<td class='borde obscuro'>$value:</td>";
                            echo "<td class='borde obscuro'>".$resultSet[$key]."</td>";
                            $columnas++;
                        }
                    }
                    ?>           
                    
                </tr>
                <tr>
                    <td class="borde obscuro" colspan="<?php echo ($columnas * 2); ?>">DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES</td>
                </tr>
            </table>
            <?php }else{//Si el destino es un almacén 
                $almacen_objeto = new Almacen();
                $domicilioAlmacen = new DomicilioAlmacen();
                $almacen_objeto->getRegistroById($resultSet['id_almacen']);
                $domicilioAlmacen->getRegistroById($almacen_objeto->getIdAlmacen());
            ?>
            <table class="completeSize">
                <tr>
                    <td colspan="4" class="borde obscuro">DATOS GENERALES</td>
                </tr>
                <tr>
                    <td class="borde gris" style="max-width: 10%;">NOMBRE ALMACÉN</td>
                    <td colspan="3" class="borde"><?php echo $almacen_objeto->getNombre(); ?></td>
                </tr>                
                <tr>
                    <td class="borde gris">CALLE Y NÚMERO</td>
                    <td class="borde" colspan="3"><?php
                        $domicilioAlmacen->getCalle();
                        if ($domicilioAlmacen->getExterior() != null) {
                            echo " No. Ext: " . $domicilioAlmacen->getExterior();
                        }
                        if ($domicilioAlmacen->getInterior() != null) {
                            echo " No. Int: " . $domicilioAlmacen->getInterior();
                        }
                        ?></td>
                </tr>
                <tr>
                    <td class="borde gris">COLONIA</td>
                    <td class="borde" colspan="3"><?php echo $domicilioAlmacen->getColonia(); ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DELEGACION ó MUNICIPIO</td>
                    <td class="borde" colspan="3"><?php echo $domicilioAlmacen->getDelegacion(); ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CIUDAD / ESTADO</td>
                    <td class="borde" colspan="3"><?php echo $domicilioAlmacen->getCiudad(); ?></td>
                </tr>
                <tr>
                    <td class="borde gris">C. POSTAL</td>
                    <td class="borde" style="min-width: 20%;" colspan="3"><?php echo $domicilioAlmacen->getCp(); ?></td>                    
                </tr>                                
                <tr>
                    <td class="borde obscuro" colspan="4">DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES</td>
                </tr>
            </table>
            <?php } ?>
            <table class="completeSize">
                <tr>
                    <td class='borde' style="width: 5%;">CANTIDAD</td>
                    <td class='borde' style="width: 20%;">MODELO</td>
                    <td class='borde' style="width: 20%;">No SERIE</td>
                    <td class='borde' style="width: 10%;">CONTADORES</td>
                    <td class='borde' style="width: 45%;">UBICACIÓN</td>                    
                </tr>
                <?php
                $consulta = "SELECT SUM(cantidad_autorizada) AS autorizada FROM k_solicitud WHERE id_solicitud = $noSolicitud AND tipo = 0;";
                $query = $catalogo->obtenerLista($consulta);
                $cantidad_autorizada = 0;
                while ($rs = mysql_fetch_array($query)) {
                    $cantidad_autorizada = intval($rs['autorizada']);
                }

                $consulta = "SELECT count(id_bitacora) AS asignadas FROM k_solicitudbitacora WHERE id_solicitud = $noSolicitud;";
                $query = $catalogo->obtenerLista($consulta);
                $cantidad_asignada = 0;
                while ($rs = mysql_fetch_array($query)) {
                    $cantidad_asignada = intval($rs['asignadas']);
                }
                
                if ($cantidad_asignada > 0 && $cantidad_asignada == $cantidad_autorizada) {
                    if(isset($resultSet['ClaveCentroCosto'])){
                        $consulta = "SELECT DISTINCT(bi.NoSerie) AS NoSerie, 
                            (CASE WHEN !ISNULL(bi.NoSerie) THEN bi.NoSerie ELSE UUID() END) NoSerieAgrupar,
                            cinv.Ubicacion AS Ubicacion,ks.NoSurtir,
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            (CASE WHEN !ISNULL(ks.cantidad_autorizada) THEN ks.cantidad_autorizada ELSE ks.cantidad END) AS autorizada,
                            ks.cantidad_surtida,
                            ks.tipo, ks.NoSerie AS SerieAsociada, ks.Modelo AS NoParte, c.comentario, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,
                            ti.Nombre AS estadoEquipo,l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            c.estatus,cli.NombreRazonSocial, cli.RFC, co.Nombre AS contacto, d.*, cc.Nombre As centro, ks.cantidad_autorizada,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT CONCAT(MAX(e.Modelo),' / ',e.NoParte) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT CONCAT(MAX(com.Modelo),' / ',com.NoParte,' / ',com.Descripcion) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto AND ks.ClaveCentroCosto = '" . $resultSet['ClaveCentroCosto'] . "'
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = cc.ClaveCliente                                                        
                            LEFT JOIN k_solicitudbitacora AS kb ON kb.id_solicitud = c.id_solicitud AND kb.NoParte = ks.Modelo AND ks.ClaveCentroCosto = kb.ClaveCentroCosto
                            LEFT JOIN c_bitacora AS bi ON bi.id_bitacora = kb.id_bitacora
                            LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MAX(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = ks.ClaveCentroCosto)
                            LEFT JOIN c_contacto AS co ON co.IdContacto = (SELECT MAX(IdContacto) FROM c_contacto WHERE ClaveEspecialContacto = cc.ClaveCentroCosto)
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa 
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = 
                            (
                                SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = bi.NoSerie 
                                AND (ContadorBNPaginas > $contadorUsado OR ContadorColorPaginas > $contadorUsado
                                OR ContadorBNML > $contadorUsado OR ContadorColorML > $contadorUsado)                                 
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            ) 
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = 
                            (
                                SELECT MAX(id_lecturaticket) FROM c_lecturasticket 
                                WHERE ClvEsp_Equipo = bi.NoSerie AND (ContadorBN > $contadorUsado OR ContadorCL > $contadorUsado)
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            )
                            LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie=bi.NoSerie
                            GROUP BY NoSerieAgrupar
                            ORDER BY tipo,NoParte;";                        
                    }else{
                        $consulta = "SELECT DISTINCT(bi.NoSerie) AS NoSerie, 
                            (CASE WHEN !ISNULL(bi.NoSerie) THEN bi.NoSerie ELSE UUID() END) NoSerieAgrupar,
                            cinv.Ubicacion AS Ubicacion,ks.NoSurtir,
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            (CASE WHEN !ISNULL(ks.cantidad_autorizada) THEN ks.cantidad_autorizada ELSE ks.cantidad END) AS autorizada,
                            ks.cantidad_surtida,
                            ks.tipo, ks.NoSerie AS SerieAsociada,  ks.Modelo AS NoParte, c.comentario, c.fecha_solicitud AS fecha,
                            ti.Nombre AS estadoEquipo,l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            c.estatus,cli.NombreRazonSocial, cli.RFC, ks.cantidad_autorizada,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT CONCAT(MAX(e.Modelo),' / ',e.NoParte) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT CONCAT(MAX(com.Modelo),' / ',com.NoParte,' / ',com.Descripcion) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente                                                        
                            LEFT JOIN k_solicitudbitacora AS kb ON kb.id_solicitud = c.id_solicitud AND kb.NoParte = ks.Modelo                            
                            LEFT JOIN c_bitacora AS bi ON bi.id_bitacora = kb.id_bitacora
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = 
                            (
                                SELECT MAX(IdLectura) 
                                FROM c_lectura WHERE NoSerie = bi.NoSerie 
                                AND (ContadorBNPaginas > $contadorUsado OR ContadorColorPaginas > $contadorUsado 
                                OR ContadorBNML > $contadorUsado OR ContadorColorML > $contadorUsado) 
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            ) 
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = 
                            (
                                SELECT MAX(id_lecturaticket) 
                                FROM c_lecturasticket 
                                WHERE ClvEsp_Equipo = bi.NoSerie AND (ContadorBN > $contadorUsado OR ContadorCL > $contadorUsado)
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            )
                            LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie=bi.NoSerie
                            WHERE ISNULL(ks.ClaveCentroCosto)                 
                            GROUP BY NoSerieAgrupar
                            ORDER BY tipo,NoParte;";
                    }
                } else {
                    if(isset($resultSet['ClaveCentroCosto'])){
                        $consulta = "SELECT DISTINCT(bi.NoSerie) AS NoSerie, 
                            (CASE WHEN !ISNULL(bi.NoSerie) THEN bi.NoSerie ELSE UUID() END) NoSerieAgrupar,
                            cinv.Ubicacion AS Ubicacion,ks.NoSurtir,
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            (CASE WHEN !ISNULL(ks.cantidad_autorizada) THEN ks.cantidad_autorizada ELSE ks.cantidad END) AS autorizada,
                            ks.cantidad_surtida,
                            ks.tipo, ks.NoSerie AS SerieAsociada,  ks.Modelo AS NoParte, c.comentario, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,
                            ti.Nombre AS estadoEquipo,l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            c.estatus,cli.NombreRazonSocial, cli.RFC, co.Nombre AS contacto, d.*, cc.Nombre As centro, ks.cantidad_autorizada,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT CONCAT(MAX(e.Modelo),' / ',e.NoParte) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT CONCAT(MAX(com.Modelo),' / ',com.NoParte,' / ',com.Descripcion) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto AND ks.ClaveCentroCosto = '" . $resultSet['ClaveCentroCosto'] . "'
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = cc.ClaveCliente                                                        
                            LEFT JOIN k_solicitudbitacora AS kb ON kb.id_solicitud = c.id_solicitud AND kb.NoParte = ks.Modelo AND ks.ClaveCentroCosto = kb.ClaveCentroCosto
                            LEFT JOIN c_bitacora AS bi ON bi.id_bitacora = kb.id_bitacora
                            LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MAX(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = ks.ClaveCentroCosto)
                            LEFT JOIN c_contacto AS co ON co.IdContacto = (SELECT MAX(IdContacto) FROM c_contacto WHERE ClaveEspecialContacto = cc.ClaveCentroCosto)
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa                         
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = 
                            (
                                SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = bi.NoSerie 
                                AND (ContadorBNPaginas > $contadorUsado OR ContadorColorPaginas > $contadorUsado
                                OR ContadorBNML > $contadorUsado OR ContadorColorML > $contadorUsado) 
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            ) 
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = 
                            (
                                SELECT MAX(id_lecturaticket) FROM c_lecturasticket 
                                WHERE ClvEsp_Equipo = bi.NoSerie AND (ContadorBN > $contadorUsado OR ContadorCL > $contadorUsado)
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            )
                            LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie=bi.NoSerie
                            GROUP BY NoSerieAgrupar
                            ORDER BY tipo,NoParte;";
                    }else{
                        $consulta = "SELECT DISTINCT(bi.NoSerie) AS NoSerie, 
                            (CASE WHEN !ISNULL(bi.NoSerie) THEN bi.NoSerie ELSE UUID() END) NoSerieAgrupar,
                            cinv.Ubicacion AS Ubicacion,ks.NoSurtir,
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            (CASE WHEN !ISNULL(ks.cantidad_autorizada) THEN ks.cantidad_autorizada ELSE ks.cantidad END) AS autorizada, 
                            ks.tipo, ks.NoSerie AS SerieAsociada,  ks.Modelo AS NoParte, c.comentario, c.fecha_solicitud AS fecha,
                            ks.cantidad_surtida,
                            ti.Nombre AS estadoEquipo,c.estatus,cli.NombreRazonSocial, cli.RFC, ks.cantidad_autorizada,
                            l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT CONCAT(MAX(e.Modelo),' / ',e.NoParte) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT CONCAT(MAX(com.Modelo),' / ',com.NoParte,' / ',com.Descripcion) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente                            
                            LEFT JOIN k_solicitudbitacora AS kb ON kb.id_solicitud = c.id_solicitud AND kb.NoParte = ks.Modelo 
                            LEFT JOIN c_bitacora AS bi ON bi.id_bitacora = kb.id_bitacora
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa 
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = 
                            (
                                SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = bi.NoSerie 
                                AND (ContadorBNPaginas > $contadorUsado OR ContadorColorPaginas > $contadorUsado
                                OR ContadorBNML > $contadorUsado OR ContadorColorML > $contadorUsado) 
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            ) 
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = 
                            (
                                SELECT MAX(id_lecturaticket) FROM c_lecturasticket 
                                WHERE ClvEsp_Equipo = bi.NoSerie AND (ContadorBN > $contadorUsado OR ContadorCL > $contadorUsado)
                                AND DATE(FechaCreacion) <= DATE(c.fecha_solicitud)
                            )
                            LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie=bi.NoSerie
                            WHERE ISNULL(ks.ClaveCentroCosto)                             
                            GROUP BY NoSerieAgrupar
                            ORDER BY tipo,NoParte;";
                    }
                }
                //echo $consulta;
                $modelo_anterior = ""; //Para identifcar un cambio, y asi poder poner los modelos que no tienen numero de serie, en el caso de solicitudes a medias                    
                $num_equipos = 0;
                $cantidad_autorizada = 0;
                $tipo_anterior = "";
                $comentario = "";
                $estatus = "0";
                $serie = false;
                $query = $catalogo->obtenerLista($consulta);
                while ($rs = mysql_fetch_array($query)) {
                    $estatus = $rs['estatus'];
                    $comentario = $rs['Ubicacion']; 
                                        
                    /* Esta condicion es para solicitudes a medias */
                    if ($serie && $tipo_anterior == "0" && $modelo_anterior != "" && $modelo_anterior != $rs['Modelo']) {
                        for (; $num_equipos < $cantidad_autorizada;) {
                            echo "<tr>";
                            echo "<td class='borde'>1</td><td class='borde'>" . $modelo_anterior . "</td><td class='borde'></td><td class='borde'>" . $comentario . "</td>
                                    <td class='borde'></td>";
                            echo "</tr>";
                            $num_equipos++;
                        }                        
                        $serie = false;                        
                    }
                    
                    if($modelo_anterior != $rs['Modelo']){                        
                        $num_equipos = 0;
                    }
                    
                    if ($rs['tipo'] == "0") {                        
                        $num_equipos++;                        
                        if ($rs['NoSerie'] != null && $rs['NoSerie'] != "") {                            
                            $serie = true;
                        }
                    }
                    
                    $contadores = "";
                    $estadoEquipo = "";  
                    
                    if(/*$rs['tipo']=="1" &&*/ $rs['NoSurtir'] == "1" && ($rs['cantidad_surtida'] == "0" || $rs['cantidad_surtida']=="")){
                        continue;
                    }
                                                            
                    if ($rs['NoSerie'] == null || $rs['NoSerie'] == "") {                        
                        $cantidad = $rs['autorizada'];
                        if($rs['tipo']=="0"){
                            $NoSerie = "";
                        }else if(isset ($rs['SerieAsociada']) && $rs['SerieAsociada']!=""){
                            $NoSerie = $rs['SerieAsociada']." (Con cliente)";
                            if(isset($rs['cantidad_surtida']) && $rs['cantidad_surtida']!="0" && $rs['cantidad_surtida'] != $rs['autorizada']){
                                $cantidad = $rs['cantidad_surtida']." *";
                            }
                        }else if($rs['tipo']=="0" && $rs['NoSurtir'] == "1" && $rs['cantidad_surtida'] != "0"){
                            $cantidad = $rs['cantidad_surtida'];
                        }else{
                            $NoSerie = "";
                            if(isset($rs['cantidad_surtida']) && $rs['cantidad_surtida']!="0" && $rs['cantidad_surtida'] != $rs['autorizada']){
                                $cantidad = $rs['cantidad_surtida']." *";
                            }
                        }                        
                    } else {
                        $NoSerie = $rs['NoSerie'];
                        $cantidad = 1;
                        $fa = false;                        
                         /* Verificamos las caracteristicas del equipo */
                        $equipo = new EquipoCaracteristicasFormatoServicio();
                        $result2 = $equipo->getCaracteristicasByParte($rs['Modelo']);
                        while($rs2 = mysql_fetch_array($result2)){                            
                            if($rs2['IdFormatoEquipo'] == "3"){
                                $fa = true;
                            }
                        }
                        
                        if(!$fa){
                            if(isset($rs['ContadorBNPaginas'])){
                                $contadores.="Contador BN: ".$rs['ContadorBNPaginas'];
                            }
                            if(isset($rs['ContadorColorPaginas'])){
                                $contadores.="<br/>Contador color: ".$rs['ContadorColorPaginas'];
                            }
                        }else{
                            if(isset($rs['ContadorBNML'])){
                                $contadores.="Contador BN: ".$rs['ContadorBNML'];
                            }
                            if(isset($rs['ContadorColorML'])){
                                $contadores.="<br/>Contador color: ".$rs['ContadorColorML'];
                            }
                        }
                        if(isset($rs['NivelTonNegro'])){
                            $contadores.="<br/>Toner negro: ".$rs['NivelTonNegro']."%";
                        }
                        if(isset($rs['NivelTonMagenta'])){
                            $contadores.="<br/>Toner magenta: ".$rs['NivelTonMagenta']."%";
                        }
                        if(isset($rs['NivelTonCian'])){
                            $contadores.="<br/>Toner cian: ".$rs['NivelTonCian']."%";
                        }
                        if(isset($rs['NivelTonAmarillo'])){
                            $contadores.="<br/>Toner amarillo: ".$rs['NivelTonAmarillo']."%";
                        }
                        if(isset($rs['EstadoEquipoByLectura'])){
                            $estadoEquipo = "(".$rs['EstadoEquipoByLectura'].")";
                        }
                    }                                                            
                    echo "<tr>";
                    echo "<td class='borde'>$cantidad</td><td class='borde'>" . $rs['Modelo'] . "</td><td class='borde'>$NoSerie $estadoEquipo</td>
                            <td class='borde'>$contadores</td><td class='borde'>" . $comentario . "</td>";
                    echo "</tr>";

                    $modelo_anterior = $rs['Modelo'];
                    $cantidad_autorizada = intval($rs['cantidad_autorizada']);
                    $tipo_anterior = $rs['tipo'];
                }//Cierre
                
                /* Esta condicion es para solicitudes a medias */
                if ($serie && $tipo_anterior == "0" && $modelo_anterior != "") {                    
                    for (; $num_equipos < $cantidad_autorizada;) {
                        echo "<tr>";
                        echo "<td class='borde'>1</td><td class='borde'>" . $modelo_anterior . "</td><td class='borde'></td><td class='borde'>$comentario</td>
                                <td class='borde'></td>";
                        echo "</tr>";
                        $num_equipos++;
                    }
                    $num_equipos = 0;
                }
                ?>
                <tr>
                    <td class="borde obscuro" colspan="5">COMENTARIO</td>
                </tr>
                <tr>
                    <td class="borde" colspan="5"><div class="espacio"><?php echo $resultSet['Comentario']?></div></td>
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
            <?php echo $resultSet['facturacion']; ?>
            </div>    
            <br/><br/><br/>
            <?php if($counter < $numResults){ ?>
                <div style="page-break-after: always;"></div>
            <?php } ?>
    <?php
}//Cierra while que recorre las localidades
?>
    </body>
</html>
