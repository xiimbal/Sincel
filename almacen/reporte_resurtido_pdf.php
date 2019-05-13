<?php
session_start();
if (!isset($_POST['almacen']) || $_POST['almacen'] == "") {
    header("Location: ../index.php");
}

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

date_default_timezone_set('America/Mexico_City');
header('Content-Type: text/html; charset=UTF-8');
ini_set("memory_limit", "600M");
set_time_limit(0);

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");

$catalogo = new Catalogo();
$almacen = new Almacen();
if (!$almacen->getRegistroById($_POST['almacen'])) {
    echo "Error: el almacén solicitado no existe";
    exit;
}

$FechaInicio = "";
$FechaFin = "";
$Proveedor = "";
$Tipos = array();
$NoParte = "";
$Agrupar = false;
$arreglo = array();
$result;

if (isset($_POST['fecha_inicial']) && !empty($_POST['fecha_inicial'])) {
    $FechaInicio = $_POST['fecha_inicial'];
}

if (isset($_POST['fecha_final']) && !empty($_POST['fecha_final'])) {
    $FechaFin = $_POST['fecha_final'];
}

if (isset($_POST['slProveedor']) && !empty($_POST['slProveedor'])) {
    $Proveedor = $_POST['slProveedor'];
}

if (isset($_POST['tipo']) && !empty($_POST['tipo'])) {
    $Tipos = $_POST['tipo'];
}

if (isset($_POST['modelo']) && !empty($_POST['modelo'])) {
    $NoParte = $_POST['modelo'];
}

if (isset($_POST['agrupar']) && !empty($_POST['agrupar'])) {
    $Agrupar = true;
}

$cabeceras = array('Almacen' => "string", 'TipoComponente' => "string", 'Modelo' => "string", 'NoParte' => "string", 'Descripcion' => "string", 'Proveedor' => "string",
    'Existencia' => "int", 'CantidadMinima' => "int", 'CantidadMaxima' => "int", 'Salidas' => "int", 'Entradas' => "int", 'Cantidad_Propuesta_Compra' => "string");

if ($Agrupar) {
    $arreglo = $almacen->reporteResurtidoAlmacenDatos($FechaInicio, $FechaFin, $Proveedor, $Tipos, $NoParte, $Agrupar, $cabeceras);
} else {
    $result = $almacen->reporteResurtidoAlmacen($FechaInicio, $FechaFin, $Proveedor, $Tipos, $NoParte, $Agrupar);
}
?>
<!DOCTYPE>
<html lang="es" style="width: 100%;">
    <head>
        <title>Reporte de resurtido de almacén</title>
        <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        <style>
            table {
                border-collapse: collapse;
            }

            table, th, td {
                border: 1px solid black;
            }

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
    </head>
    <body>
        <div style="float: right;">
            <a href="reporte_resurtido_xml.php?almacen=<?php echo $almacen->getIdAlmacen() ?>&fecha_inicial=<?php echo $FechaInicio; ?>&fecha_final=<?php echo $FechaFin; ?>&slProveedor=<?php echo $Proveedor ?>&tipo=<?php echo implode(",", $Tipos) ?>&modelo=<?php echo $NoParte ?>&agrupar=<?php echo $Agrupar; ?>" 
               target="_blank">
                <img src="../resources/images/excel.png"/>
            </a>
        </div>
        <h1 style="text-align: center;">REPORTE DE INVENTARIO DE COMPONENTES: <?php echo $almacen->getNombre(); ?></h1>
        <h3 style="text-align: center;"><?php echo "Al " . $catalogo->formatoFechaReportes(date('Y-m-d')) . " a las " . date('H:m:s'); ?></h3>
        <table style="width: 100%;">
            <thead>
                <tr>
                    <?php
                    foreach ($cabeceras as $key => $value) {
                        echo "<th>$key</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!$Agrupar) {
                    while ($rs = mysql_fetch_array($result)) {
                        echo "<tr>";
                        foreach ($cabeceras as $key => $value) {
                            echo "<td>" . $rs[$key] . "</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    foreach ($arreglo AS $ke => $array) {
                        echo "<tr>";
                        if (($array['Entradas'] != "" && $array['Entradas'] != 0) || ($array['Salidas'] != "" && $array['Salidas'] != 0)) {
                            foreach ($cabeceras as $key => $value) {
                                echo "<td>" . $array[$key] . "</td>";
                            }
                        }
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </body>
</html>