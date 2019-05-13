<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../../index.php");
}

if (!isset($_SESSION['idEmpresa']) || $_SESSION['idEmpresa'] == "") {
    echo "Debes de iniciar sesión para poder ver el reporte";
    return;
}

include_once("../../Classes/Catalogo.class.php");

$noSolicitud = $_GET['noSolicitud'];
$catalogo = new Catalogo();
$catalogo->setEmpresa($_SESSION['idEmpresa']);
$query = $catalogo->obtenerLista("SELECT reportes_historicos.NumReporte AS NumReporte,
	reportes_historicos.FechaCreacion AS Fecha
        FROM reportes_historicos WHERE reportes_historicos.NumReporte=" . $noSolicitud . ";");
$causa_movimiento = "";
if ($rsp = mysql_fetch_array($query)) {
    $count_mysql = 0;
    $consulta = "SELECT
        movimientos_equipo.NoSerie AS NoSerie,
        e.Modelo AS Modelo,
        e.Descripcion AS Descripcion,
        movimientos_equipo.causa_movimiento,
        movimientos_equipo.tipo_movimiento AS Tipo_Movimiento,
        movimientos_equipo.Fecha AS Fecha,
        movimientos_equipo.Fecha AS FechaSimple,
        movimientos_equipo.clave_cliente_anterior AS clave_cliente_anterior,
        movimientos_equipo.clave_centro_costo_anterior AS clave_centro_costo_anterior,
        movimientos_equipo.clave_cliente_nuevo AS clave_cliente_nuevo,
        movimientos_equipo.clave_centro_costo_nuevo AS clave_centro_costo_nuevo,
        movimientos_equipo.almacen_anterior AS almacen_anterior,
        movimientos_equipo.almacen_nuevo AS almacen_nuevo,
        movimientos_equipo.pendiente AS pendiente,
        c_tipomovimiento.Nombre AS TipoMovimiento,
        c_inventarioequipo.Ubicacion AS Ubicacion,
        b.NoParte AS NoParte,
        k_almacenequipo.Ubicacion AS UbicacionAlm,
        CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno) AS NombreUsuario,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.IdKServicioGIM WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.IdKServicioGFA WHEN !ISNULL(im.IdKServicioIM) THEN im.IdKServicioIM WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.IdKServicioGFA ELSE 0 END) AS IdKServicio,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.RentaMensual WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.RentaMensual WHEN !ISNULL(im.IdKServicioIM) THEN im.RentaMensual WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.RentaMensual ELSE 0 END) AS RentaMensual,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.PaginasIncluidasBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosBN WHEN !ISNULL(im.IdKServicioIM) THEN im.PaginasIncluidasBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosBN ELSE 0 END) AS IncluidosBN,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.PaginasIncluidasColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosColor WHEN !ISNULL(im.IdKServicioIM) THEN im.PaginasIncluidasColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.MLIncluidosColor ELSE 0 END) AS IncluidosColor,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginasExcedentesBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesBN WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginasExcedentesBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesBN ELSE 0 END) AS CostoExcedentesBN,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginasExcedentesColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesColor WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginasExcedentesColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLExcedentesColor ELSE 0 END) AS CostoExcedentesColor,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginaProcesadaBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosBN WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginaProcesadaBN WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosBN ELSE 0 END) AS CostoProcesadaBN,
        (CASE WHEN !ISNULL(gim.IdKServicioGIM) THEN gim.CostoPaginaProcesadaColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosColor WHEN !ISNULL(im.IdKServicioIM) THEN im.CostoPaginaProcesadaColor WHEN !ISNULL(gfa.IdKServicioGFA) THEN gfa.CostoMLProcesadosColor ELSE 0 END) AS CostoProcesadaColor
        FROM reportes_historicos
        INNER JOIN reportes_movimientos ON reportes_movimientos.id_reportes = reportes_historicos.NumReporte
        INNER JOIN movimientos_equipo ON movimientos_equipo.id_movimientos = reportes_movimientos.id_movimientos
        LEFT JOIN c_usuario on c_usuario.Loggin=movimientos_equipo.UsuarioCreacion
        LEFT JOIN c_tipomovimiento ON c_tipomovimiento.IdTipoMovimiento=movimientos_equipo.IdTipoMovimiento
        LEFT JOIN c_bitacora as b ON b.NoSerie = movimientos_equipo.NoSerie
        LEFT JOIN c_equipo as e ON e.NoParte = b.NoParte
        LEFT JOIN c_inventarioequipo ON c_inventarioequipo.NoSerie=movimientos_equipo.NoSerie
        LEFT JOIN k_almacenequipo ON k_almacenequipo.NoSerie=movimientos_equipo.NoSerie
        LEFT JOIN k_serviciogim AS gim ON gim.IdKServicioGIM = (SELECT MIN(IdKServicioGIM) FROM k_serviciogim WHERE (movimientos_equipo.IdKServicioAnterior = IdKServicioGIM OR (ISNULL(movimientos_equipo.IdKServicioAnterior) AND movimientos_equipo.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioGIM = movimientos_equipo.IdServicioAnterior)
        LEFT JOIN c_serviciogim AS cgim ON cgim.IdServicioGIM = gim.IdServicioGIM
        LEFT JOIN k_serviciogfa AS gfa ON gfa.IdKServicioGFA = (SELECT MIN(IdKServicioGFA) FROM k_serviciogfa WHERE (movimientos_equipo.IdKServicioAnterior = IdKServicioGFA OR (ISNULL(movimientos_equipo.IdKServicioAnterior) AND movimientos_equipo.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioGFA = movimientos_equipo.IdServicioAnterior)
        LEFT JOIN c_serviciogfa AS cgfa ON cgfa.IdServicioGFA = gfa.IdServicioGFA
        LEFT JOIN k_servicioim AS im ON im.IdKServicioIM = (SELECT MIN(IdKServicioIM) FROM k_servicioim WHERE (movimientos_equipo.IdKServicioAnterior = IdKServicioIM OR (ISNULL(movimientos_equipo.IdKServicioAnterior) AND movimientos_equipo.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioIM = movimientos_equipo.IdServicioAnterior)
        LEFT JOIN c_servicioim AS cim ON cim.IdServicioIM = im.IdServicioIM
        LEFT JOIN k_serviciofa AS fa ON fa.IdKServicioFA = (SELECT MIN(IdKServicioFA) FROM k_serviciofa WHERE (movimientos_equipo.IdKServicioAnterior = IdKServicioFA OR (ISNULL(movimientos_equipo.IdKServicioAnterior) AND movimientos_equipo.IdAnexoClienteCCAnterior = IdAnexoClienteCC)) AND IdServicioFA = movimientos_equipo.IdServicioAnterior)
        LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = fa.IdServicioFA
        WHERE reportes_historicos.NumReporte=" . $noSolicitud . " GROUP BY NoSerie ORDER BY clave_centro_costo_anterior,almacen_anterior"; 
    //echo $consulta;
    $queryprin = $catalogo->obtenerLista($consulta);
    $numero_filas = mysql_num_rows($queryprin);    
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
                    width: 900px;
                }            
                .borde{border: 1px solid #000;}
                .mediano{width: 30%;}
                .gigantes{width: 600px;}                
                .espacio{min-height: 100px;}                
                .obscuro{color: black; text-align: center;  font-style: italic;}
                .gris{font-weight: bold; }
                .color{color: black;}
                .bn{color: black; }

                .pie{font-size: 10px; color: #800000;}
                .centrado {text-align: center;}
                .completeSize{width: 97%;}
            </style>
            <title>Reporte</title>
            <link rel="shortcut icon" href="../../../resources/images/logos/ra4.png" type="image/x-icon"/>
        </head>
        <body>
            <a href=javascript:window.print(); style="margin: 93%;"><img src="../../../resources/images/icono_impresora.png" style="width: 24px; height: 24px;"/></a>
            <?php            
            $counter = 0;
            while ($rss = mysql_fetch_array($queryprin)) {
                $causa_movimiento = $rss['causa_movimiento'];
                $NombreUsuario = $rss['NombreUsuario'];                               
                ?>
                <div class="principal">
                    <img src="../../../LOGOS/3(1)_empresa_logo.png" style="float:right; margin: 0% 20% 5% 0%;"/>
                    <div class="titulo">FORMATO DE MOVIMIENTOS DE EQUIPOS</div>            
                    <div style="margin-left: 83%; font-weight: bold; font-size: 20px;">No. Movimiento: <?php echo $noSolicitud; ?></div>
                    <br/><br/>
                    <table class="completeSize">
                        <tr>
                            <td class="borde mediano obscuro">SE FACTURA POR</td>
                            <td class="borde mediano">GN SYS CORPORATIVO S.A. DE C.V.</td>
                        </tr>
                    </table>
                    <br/>
                    <table class="completeSize">
                        <tr>
                            <td class="borde mediano obscuro">TIPO DE MOVIMIENTO</td>
                            <td class="borde mediano"><?php echo $rss['TipoMovimiento']; ?></td>
                            <td style="min-width: 50px;"></td>
                            <td class="borde gris">FECHA</td>
                            <td class="borde"><?php echo $rss['Fecha']; ?></td>
                        </tr>
                    </table>            
                </div>
                <br/>
                <table class="completeSize">
                    <tr>
                        <td colspan="4" class="borde obscuro"><b>DESTINO</b></td>
                    </tr>
                    <?php
                    if ($rss['Tipo_Movimiento'] == 1 || $rss['Tipo_Movimiento'] == 2 || $rss['Tipo_Movimiento'] == 5) {
                        $query = $catalogo->obtenerLista("SELECT
                            c_cliente.NombreRazonSocial AS Nombre,
                            c_cliente.RFC AS RFC,
                            c_contacto.Nombre AS Contacto,
                            c_contacto.Telefono,
                            c_centrocosto.Nombre AS CentroCosto,
                            CONCAT(c_domicilio.Calle,\" #\",c_domicilio.NoExterior) AS Calle,
                            c_domicilio.Colonia AS Colonia,
                            c_domicilio.Delegacion AS Delegacion,
                            c_domicilio.Estado AS Estado,
                            c_domicilio.CodigoPostal AS CP
                            FROM
                                    c_cliente
                            INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                            LEFT JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                            LEFT JOIN c_contacto ON c_contacto.ClaveEspecialContacto=c_centrocosto.ClaveCentroCosto
                            WHERE c_centrocosto.ClaveCentroCosto='" . $rss['clave_centro_costo_nuevo'] . "'");
                        if ($resultSet = mysql_fetch_array($query)) {
                            ?>
                            <tr>
                                <td class="borde gris">NOMBRE ó RAZON SOCIAL</td>
                                <td colspan="3" class="borde"><?php echo $resultSet['Nombre']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">CONTACTO COMERCIAL</td>
                                <td class="borde"><?php echo $resultSet['Contacto']; ?></td>
                                <td class="borde" colspan="2"><b>RFC:</b> <?php echo $resultSet['RFC']; ?>
                            </tr>
                            <tr>
                                <td class="borde obscuro" colspan="4"><?php echo $resultSet['CentroCosto']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">CALLE Y NÚMERO</td>
                                <td class="borde" colspan="3"><?php echo $resultSet['Calle']; ?></td>
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
                                <td class="borde" colspan="3"><?php echo $resultSet['Estado']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">TELEFONO Y EXTENSION</td>
                                <td class="borde"><?php echo $resultSet['Telefono']; ?></td>
                                <td class="borde" colspan="2"><b>C. POSTAL</b>: <?php echo $resultSet['CP']; ?></td>
                            </tr>

                            <?php
                        }
                    } else {
                        $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre,CONCAT(c_domicilio_almacen.Calle,\" #\",c_domicilio_almacen.NoExterior) AS Calle,
                            c_domicilio_almacen.Colonia AS Colonia,
                            c_domicilio_almacen.Delegacion AS Delegacion,
                            c_domicilio_almacen.Estado AS Estado,
                            c_domicilio_almacen.CodigoPostal AS CP FROM c_almacen 
                            LEFT JOIN c_domicilio_almacen ON c_domicilio_almacen.IdAlmacen = c_almacen.id_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_nuevo'] . "'");
                        if ($resultSet = mysql_fetch_array($query)) {
                            ?>
                            <tr>
                                <td class="borde gris">ALMACÉN</td>
                                <td colspan="3" class="borde"><?php echo $resultSet['Nombre']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">CALLE Y NÚMERO</td>
                                <td class="borde" colspan="3"><?php echo $resultSet['Calle']; ?></td>
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
                                <td class="borde" colspan="3"><?php echo $resultSet['Estado']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">TELEFONO Y EXTENSION</td>
                                <td class="borde"></td>
                                <td class="borde" colspan="2"><b>C. POSTAL</b>: <?php echo $resultSet['CP']; ?></td>
                            </tr>
                            <?php
                        } else {
                            $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre FROM c_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_nuevo'] . "'");
                            if ($resultSet = mysql_fetch_array($query)) {
                                ?>
                                <tr>
                                    <td class="borde gris">ALMACÉN</td>
                                    <td colspan="3" class="borde"><?php echo $resultSet['Nombre']; ?></td>
                                </tr>
                                <?php
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td colspan="4" class="borde obscuro"><b>ORIGEN</b></td>
                    </tr>
                    <?php
                    $firmaEjecutivo = "";
                    if ($rss['Tipo_Movimiento'] == 1 || $rss['Tipo_Movimiento'] == 3 || $rss['Tipo_Movimiento'] == 5) {
                        $query = $catalogo->obtenerLista("SELECT
                            c_cliente.NombreRazonSocial AS Nombre,
                            c_cliente.RFC AS RFC,
                            c_contacto.Nombre AS Contacto,
                            c_contacto.Telefono,
                            c_centrocosto.Nombre AS CentroCosto,
                            CONCAT(c_domicilio.Calle,\" #\",c_domicilio.NoExterior) AS Calle,
                            c_domicilio.Colonia AS Colonia,
                            c_domicilio.Delegacion AS Delegacion,
                            c_domicilio.Estado AS Estado,
                            c_domicilio.CodigoPostal AS CP,
                            CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno) AS NombreUsuario
                            FROM
                                    c_cliente
                            INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta
                            INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                            INNER JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                            LEFT JOIN c_contacto ON c_contacto.ClaveEspecialContacto=c_centrocosto.ClaveCentroCosto
                            WHERE c_centrocosto.ClaveCentroCosto='" . $rss['clave_centro_costo_anterior'] . "'");
                        if ($resultSet = mysql_fetch_array($query)) {
                            $firmaEjecutivo = $resultSet['NombreUsuario'];
                            ?>
                            <tr>
                                <td class="borde gris">NOMBRE ó RAZON SOCIAL</td>
                                <td colspan="3" class="borde"><?php echo $resultSet['Nombre']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">CONTACTO COMERCIAL</td>
                                <td class="borde" width="650"><?php echo $resultSet['Contacto']; ?></td>
                                <td class="borde" colspan="2"><b>RFC:</b> <?php echo $resultSet['RFC']; ?>
                            </tr>
                            <tr>
                                <td class="borde obscuro" colspan="4"><?php echo $resultSet['CentroCosto']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">CALLE Y NÚMERO</td>
                                <td class="borde" colspan="3"><?php echo $resultSet['Calle']; ?></td>
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
                                <td class="borde" colspan="3"><?php echo $resultSet['Estado']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">TELEFONO Y EXTENSION</td>
                                <td class="borde"><?php echo $resultSet['Telefono']; ?></td>
                                <td class="borde" colspan="2"><b>C. POSTAL</b>: <?php echo $resultSet['CP']; ?></td>
                            </tr>

                            <?php
                        }
                    } else {
                        $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre,CONCAT(c_domicilio_almacen.Calle,\" #\",c_domicilio_almacen.NoExterior) AS Calle,
                            c_domicilio_almacen.Colonia AS Colonia,
                            c_domicilio_almacen.Delegacion AS Delegacion,
                            c_domicilio_almacen.Estado AS Estado,
                            c_domicilio_almacen.CodigoPostal AS CP,CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno) AS NombreUsuario FROM c_almacen LEFT JOIN k_responsablealmacen ON k_responsablealmacen.IdAlmacen=c_almacen.id_almacen LEFT JOIN c_usuario ON c_usuario.IdUsuario=k_responsablealmacen.IdUsuario LEFT JOIN c_domicilio_almacen ON c_domicilio_almacen.IdAlmacen = c_almacen.id_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_anterior'] . "'");
                        if ($query != null && $resultSet = mysql_fetch_array($query)) {
                            $firmaEjecutivo = $resultSet['NombreUsuario'];
                            ?>
                            <tr>
                                <td class="borde gris">ALMACÉN</td>
                                <td colspan="3" class="borde"><?php echo $resultSet['Nombre']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">CALLE Y NÚMERO</td>
                                <td class="borde" colspan="3"><?php echo $resultSet['Calle']; ?></td>
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
                                <td class="borde" colspan="3"><?php echo $resultSet['Estado']; ?></td>
                            </tr>
                            <tr>
                                <td class="borde gris">TELEFONO Y EXTENSION</td>
                                <td class="borde"></td>
                                <td class="borde" colspan="2"><b>C. POSTAL</b>: <?php echo $resultSet['CP']; ?></td>
                            </tr>
                            <?php
                        } else {
                            $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre FROM c_almacen WHERE c_almacen.id_almacen='" . $rss['almacen_anterior'] . "'");
                            if ($resultSet = mysql_fetch_array($query)) {
                                ?>
                                <tr>
                                    <td class="borde gris">ALMACÉN</td>
                                    <td colspan="3" class="borde"><?php echo $resultSet['Nombre']; ?></td>
                                </tr>
                                <?php
                            }
                        }
                    }
                    ?>
                </table>
                <table class="completeSize">
                    <tr>
                        <?php 
                        $columnas = 0;
                        $servicios = array("IdKServicio" => "Id","RentaMensual"=>"Renta Mensual ($)", "IncluidosBN"=>"Incluidos B/N", "IncluidosColor"=>"Incluidos Color", 
                            "CostoExcedentesBN"=>"Costo Excedentes B/N ($)", "CostoExcedentesColor"=>"Costo Excedentes Color ($)", 
                            "CostoProcesadaBN"=>"Costo Procesados B/N ($)", "CostoProcesadaColor"=>"Costo Procesados Color ($)");
                        foreach ($servicios as $key => $value) {
                            if(isset($rss[$key]) && !empty($rss[$key])){
                                echo "<td class='borde obscuro'>$value:</td>";
                                echo "<td class='borde obscuro'>".number_format($rss[$key],2)."</td>";
                                $columnas++;
                            }
                        }                                                
                        ?>                    
                    </tr>
                    <tr>
                        <td class="borde obscuro" colspan="<?php echo ($columnas * 2); ?>">DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES</td>
                    </tr>
                </table>
                <table class="completeSize">
                    
                    <tr>
                        <td class='borde' style="width: 15%;">No Serie</td>
                        <td class='borde' style="width: 15%;">Modelo</td>
                        <td class='borde' style="width: 45%;">Ubicación</td>
                        <td class='borde' style="width: 25%;">Contadores</td>
                    </tr>

                    <?php
                    mysql_data_seek($queryprin, $count_mysql);
                    $aux_ant = "";
                    while ($rss = mysql_fetch_array($queryprin)) {
                        echo "<tr>";
                        if ($aux_ant == "" || $aux_ant == $rss['clave_centro_costo_anterior']) {
                            $val = false;
                            $query4 = $catalogo->obtenerLista("SELECT ts.Nombre AS servicio, ts.IdTipoServicio AS ID FROM `k_equipocaracteristicaformatoservicio` AS ke
                                    INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '" . $rss['NoParte'] . "' AND ts.IdTipoServicio = ke.IdTipoServicio;");
                            while ($resultSet = mysql_fetch_array($query4)) {
                                if ($resultSet['ID'] == 1) {
                                    $val = true;
                                }
                            }
                            $query = $catalogo->obtenerLista("SELECT
                                            DATE(c_lectura.Fecha) AS Fecha,
                                            c_lectura.ContadorBNPaginas AS ContadorBN,
                                            c_lectura.ContadorColorPaginas AS ContadorCL,
                                            c_lectura.ContadorBNML AS ContadorBNML,
                                            c_lectura.ContadorColorML AS ContadorCLML,
                                            c_lectura.NivelTonNegro AS NivelTonNegro,
                                            c_lectura.NivelTonCian AS NivelTonCian,
                                            c_lectura.NivelTonMagenta AS NivelTonMagenta,
                                            c_lectura.NivelTonAmarillo AS NivelTonAmarillo
                                            FROM movimientos_equipo
                                            INNER JOIN c_lectura ON c_lectura.NoSerie = movimientos_equipo.NoSerie
                                            WHERE movimientos_equipo.NoSerie ='" . $rss['NoSerie'] . "' AND c_lectura.Fecha <='" . $rss['FechaSimple'] . "' ORDER BY Fecha DESC");
                            if ($rs = mysql_fetch_array($query)) {
                                if ($val)
                                    echo "<td class='borde'>" . $rss['NoSerie'] . "</td><td class='borde' width='400'>" . $rss['Modelo'] . "</td><td class='borde' width='300' >" . $rss['Ubicacion'] . "</td><td class='borde' width='300'>BN: " . $rs['ContadorBN'] . "<br/>Color: " . $rs['ContadorCL'] . "</td>";
                                else
                                    echo "<td class='borde'>" . $rss['NoSerie'] . "</td><td class='borde' width='400'>" . $rss['Modelo'] . "</td><td class='borde' width='300'>" . $rss['Ubicacion'] . "</td><td class='borde' width='300'>BN: " . $rs['ContadorBN'] . "</td>";
                            } else {
                                echo "<td class='borde'>" . $rss['NoSerie'] . "</td><td class='borde' width='400'>" . $rss['Modelo'] . "</td><td class='borde' width='300'>" . $rss['Ubicacion'] . "</td> <td class='borde' width='300'></td>";
                            }
                            $aux_ant = $rss['clave_centro_costo_anterior'];
                        } else {
                            break;
                        }

                        echo "</tr>";
                        ?>                            
                        <?php
                        $count_mysql++;
                        $counter++;
                    }//Cierre
                    if ($numero_filas > $count_mysql) {
                        mysql_data_seek($queryprin, $count_mysql);
                    }
                    ?>
                </table>
                <table class="completeSize">
                    <tr>
                        <td class="borde obscuro" colspan="4">OBSERVACIONES</td>
                    </tr>
                    <tr><td class="borde" colspan="4"><?php
                            if ($NombreUsuario != "") {
                                echo strtoupper("Solicitado por: " . $NombreUsuario);
                            }
                            echo "<br/>Causa Movimiento: ".$causa_movimiento;
                            ?><div class="espacio"></div></td></tr>
                </table> 
                <table class="completeSize">
                    <tr>
                        <td class="borde centrado" style="width: 33%;">CLIENTE</td>
                        <td class="borde centrado" style="width: 33%;">EJECUTIVO DE CUENTAS</td>
                        <td class="borde centrado" style="width: 33%;">AUTORIZACIÓN</td>
                    </tr>
                    <tr>
                        <td class="borde"><div class="espacio"></div></td>
                        <td class="borde centrado"><div class="espacio centrado"></div><?php echo $firmaEjecutivo ?></td>
                        <td class="borde centrado"><div class="espacio centrado"></div>LIC. CLAUDIA MORENO</td>
                    </tr>
                    <tr>
                        <td class="borde centrado">FIRMA</td>
                        <td class="borde centrado">FIRMA</td>
                        <td class="borde centrado">FIRMA</td>
                    </tr>
                </table> 
                <div class="pie">
                    SERVICIOS CORPORATIVOS G&Eacute;NESIS, S.A DE C.V. RIO CHURUBUSCO No. Ext. 267 No. Int. ,COL. PRADO CHURUBUSCO COYOACAN, M&Eacute;XICO DISTRITO FEDERAL C.P. 04230 TELS.56465850-53468358
                </div>    
                <br/><br/><br/><br/>
                <?php 
                    if($counter < $numero_filas){                        
                ?>
                    <div style="page-break-after: always;"></div>
                <?php }
            }
            //Cierra while que recorre las localidades}
            ?>
        </body>
    </html>
<?php } ?>