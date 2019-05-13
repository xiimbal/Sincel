<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/ResurtidoToner.class.php");
$resurtido = new ResurtidoToner();
if (isset($_GET['almacen']) && $_GET['almacen'] != "") {
    $resurtido->setAlmacen($_GET['almacen']);
}
if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
    $resurtido->setFecha1($_GET['fecha1'] . " 00:00:00");
    if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
        $resurtido->setFecha2($_GET['fecha2'] . " 23:59:59");
    } else {
        $hoy = date("Y-n-j");
        $resurtido->setFecha2($hoy);
    }
}
if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
    $resurtido->setCliente($_GET['cliente']);
}
if (isset($_GET['localidad']) && $_GET['localidad'] != "") {
    $resurtido->setLocalidad($_GET['localidad']);
}
if (isset($_GET['equipo']) && $_GET['equipo'] != "") {
    $resurtido->setEquipo($_GET['equipo']);
}
$query = $resurtido->getTabla();
$resurtido->ponerNombre();
$varEncabezadoAlmacen = "";
$varEncabezadoCliente = "";
$varEncabezadoLocalidad = "";
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
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <div class="principal">            
            <img src="../resources/images/kyocera_reporte.png" style="float:right; margin: 0% 20% 5% 0%; height: 45px;"/>            
            <div class="titulo">Reporte de resurtido de toner</div>           
            <br/><br/>
            <table>
                <?php
                if ($resurtido->getAlmacenN() != "") {
                    echo "<tr><td class='mediano obscuro'>Almacén: </td><td style='width: 75%;'>" . $resurtido->getAlmacenN() . "</td></tr>";
                } else {
                    $varEncabezadoAlmacen = "Almacén";
                    echo "<tr><td class='mediano obscuro'>Almacén: </td><td style='width: 75%;'>Todos los almacenes</td></tr>";
                }
                if ($resurtido->getFecha1() != "") {
                    echo "<tr><td class='mediano obscuro'>Fecha: </td><td style='width: 75%;'>" . $resurtido->getFecha1() . " hasta " . $resurtido->getFecha2() . "</td></tr>";
                } else {
                    echo "<tr><td class='mediano obscuro'>Fecha: </td><td style='width: 75%;'>Todas las fechas</td></tr>";
                }if ($resurtido->getClienteN() != "") {
                    echo "<tr><td class='mediano obscuro'>Cliente: </td><td style='width: 75%;'>" . $resurtido->getClienteN() . "</td></tr>";
                } else {
                    $varEncabezadoCliente = "Cliente";
                    echo "<tr><td class='mediano obscuro'>Cliente: </td><td style='width: 75%;'>Todos los cliente</td></tr>";
                }if ($resurtido->getLocalidadN() != "") {
                    echo "<tr><td class='mediano obscuro'>Localidad: </td><td style='width: 75%;'>" . $resurtido->getLocalidadN() . "</td></tr>";
                } else {
                    $varEncabezadoLocalidad = "Localidad";
                    echo "<tr><td class='mediano obscuro'>Localidad: </td><td style='width: 75%;'>Todas las localidades</td></tr>";
                }if ($resurtido->getEquipo() != "") {
                    echo "<tr><td class='mediano obscuro'>Equipo: </td><td style='width: 75%;'>".$resurtido->getEquipo()."</td></tr>";
                }else {
                    echo "<tr><td class='mediano obscuro'>Cliente: </td><td style='width: 75%;'>Todos los equipo</td></tr>";
                }
                ?>
            </table>          
        </div>
        <br/>
        <table class="completeSize">
            <tr>
                <th class="borde centrado">Fecha</th>
                <th class="borde centrado" style="width: 20px;">Equipo</th>
                <th class="borde centrado" style="width: 20px;">Serie</th>               
                <th class="borde centrado">No Parte</th>
                <th class="borde centrado">Modelo</th>
                <th class="borde centrado">Descripción</th>
                <th class="borde centrado">Cantidad</th>
                <th class="borde centrado">No. Ticket</th>
                <th class="borde centrado">Contador</th>
                <?php
                if ($varEncabezadoAlmacen != "") {
                    echo "<td class='borde centrado'>" . $varEncabezadoAlmacen . "</td>";
                }
                if ($varEncabezadoCliente != "") {
                    echo "<td class='borde centrado'>" . $varEncabezadoCliente . "</td>";
                }
                if ($varEncabezadoLocalidad != "") {
                    echo "<td class='borde centrado'>" . $varEncabezadoLocalidad . "</td>";
                }
                ?>
            </tr>
            <?php
            $val = false;
            while ($resultSet = mysql_fetch_array($query)) {
                echo "<tr>";
                echo "<td class='borde centrado'>" . $resultSet['Fecha'] . "</td>";
                echo "<td class='borde centrado' style='width: 20px;'>" . $resultSet['Modelo'] . "</td>";
                echo "<td class='borde centrado' style='width: 20px;'>" . $resultSet['NoSerie'] . "</td>";
                echo "<td class='borde centrado'>" . $resultSet['NoParteT'] . "</td>";
                echo "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
                echo "<td class='borde centrado'>" . $resultSet['DescripcionT'] . "</td>";
                echo "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                echo "<td class='borde centrado'>" . $resultSet['IdTicket'] . "</td>";
                $pieces = explode(',', $resultSet['NoSerie']);
                echo "<td class='borde centrado'>";
                $txt1 = "";
                $color = false;
                foreach ($pieces as $value) {
                    $query2 = $resurtido->getLectura($value, $resultSet['Fecha']);
                    if ($rs = mysql_fetch_array($query2)) {
                        if ($rs['ContadorBN'] != "") {
                            $txt1.="<tr>";
                            $txt1.= "<td width='80'>" . $value . "</td>";
                            $txt1.= "<td width='80'>" . $rs['ContadorBN'] . "</td>";
                            if ($rs['ContadorCL'] != "") {
                                $txt1.= "<td width='80'>" . $rs['ContadorCL'] . "</td>";
                                $color = true;
                            }
                            $txt1.= "<td width='80'>" . $rs['Fecha'] . "</td>";
                            $txt1.= "</tr>";
                        } else {
                            $txt1.="<tr>";
                            $txt1.= "<td width='80'>" . $value . "</td>";
                            $txt1.= "<td width='80'> " . $rs['ContadorBN'] . "</td>";
                            if ($rs['ContadorCL'] != "") {
                                $txt1.= "<td width='80'>" . $rs['ContadorCL'] . "</td>";
                                $color = true;
                            }
                            $txt1.= "<td width='80'>Sin lecturas</td>";
                            $txt1.= "</tr>";
                        }
                    }
                }
                if ($color) {
                    $txt = "<table width='320' align='center'><t><td width='80'>NoSerie</td><td width='80'>BN</td><td width='80'>Color</td><td width='80'>Fecha</td></tr>";
                } else {
                    $txt = "<table width='320' align='center'><t><td width='80'>NoSerie</td><td width='80'>BN</td><td width='80'>Fecha</td></tr>";
                }
                $txt.=$txt1 . "</table>";
                if ($txt1 != "") {
                    echo $txt;
                }
                echo "</td>";
                if ($varEncabezadoAlmacen != "") {
                    echo "<td class='borde centrado'>" . $resultSet['almacen'] . "</td>";
                }
                if ($varEncabezadoCliente != "") {
                    echo "<td class='borde centrado'>" . $resultSet['cliente'] . "</td>";
                }
                if ($varEncabezadoLocalidad != "") {
                    echo "<td class='borde centrado'>" . $resultSet['localidad'] . "</td>";
                }
                echo "</tr>";
                $val = true;
            }
            if ($val == false) {
                echo "<tr>";
                echo "<td class='borde centrado' colspan='13'>No se encontraron datos que coincidieran con su búsqueda</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <br/><br/><br/>
        <div style="page-break-after: always;"></div>

    </body>
</html>

