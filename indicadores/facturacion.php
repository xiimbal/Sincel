<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    echo "2";
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Indicadores_Facturacion.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");

$liga_facturas = "../principal.php?mnu=facturacion&action=ReporteFacturacion_net";
$tiene_get = true;

$emisor = "";
$ejecutivo = "";
$cliente = "";
$fechaInicio = "";
$fechaFinal = "";
/* Filtros de emisor */
if (isset($_GET['Emisor']) && $_GET['Emisor'] != "") {
    $emisor = $_GET['Emisor'];
    $datosEmisor = new DatosFacturacionEmpresa();
    if ($datosEmisor->getRegistroById($emisor)) {
        if (!$tiene_get) {
            $liga_facturas .= "?";
        } else {
            $liga_facturas .= "&";
        }
        $liga_facturas .= ("param1=" . $datosEmisor->getRFC());
        $tiene_get = true;
    }
} else if (isset($_POST['razon_social']) && $_POST['razon_social'] != "") {
    $emisor = $_POST['razon_social'];
    $datosEmisor = new DatosFacturacionEmpresa();
    if ($datosEmisor->getRegistroById($emisor)) {
        if (!$tiene_get) {
            $liga_facturas .= "?";
        } else {
            $liga_facturas .= "&";
        }
        $liga_facturas .= ("param1=" . $datosEmisor->getRFC());
        $tiene_get = true;
    }
} else {
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param1=0");
    $tiene_get = true;
}

/* Filtros de ejecutivo */
if (isset($_GET['Ejecutivo']) && $_GET['Ejecutivo'] != "") {
    $ejecutivo = $_GET['Ejecutivo'];
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param11=" . $ejecutivo);
    $tiene_get = true;
} else if (isset($_POST['ejecutivo']) && $_POST['ejecutivo'] != "") {
    $ejecutivo = $_POST['ejecutivo'];
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param11=" . $ejecutivo);
    $tiene_get = true;
} else {
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param11=0");
    $tiene_get = true;
}

/* Filtros de fecha de inicio */
if (isset($_GET['FechaInicio']) && $_GET['FechaInicio'] != "") {
    $fechaInicio = $_GET['FechaInicio'];
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param2=" . $fechaInicio);
    $tiene_get = true;
    /* Obtenemos el anio anterior */
    $anio_inicial = (int) substr($fechaInicio, 0, 4);
    $anio_anterior = $anio_inicial - 1;
    $fechaInicialLecturas = $fechaInicio; //Esta fecha se ocupa para el limite de las lecturas
} else if (isset($_POST['fecha_inicio']) && $_POST['fecha_inicio'] != "") {
    $fechaInicio = $_POST['fecha_inicio'];
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param2=" . $fechaInicio);
    $tiene_get = true;
    /* Obtenemos el anio anterior */
    $anio_inicial = (int) substr($fechaInicio, 0, 4);
    $anio_anterior = $anio_inicial - 1;
    $fechaInicialLecturas = $fechaInicio; //Esta fecha se ocupa para el limite de las lecturas
} else {
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param2=0");
    $tiene_get = true;
    /* Obtenemos el anio anterior */
    $anio_inicial = (int) date("Y");
    $anio_anterior = $anio_inicial - 1;
    $fechaInicialLecturas = ($anio_inicial . "-" . date("m") . "-01"); //Esta fecha se ocupa para el limite de las lecturas
}
/* Filtros de fecha final */
if (isset($_GET['fechaFinal']) && $_GET['fechaFinal'] != "") {
    $fechaFinal = $_GET['fechaFinal'];
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param3=" . $fechaFinal);
    $tiene_get = true;
    $fechaFinalLecturas = $fechaFinal; //Esta fecha se ocupa para el limite de las lecturas
} else if (isset($_POST['fecha_fin']) && $_POST['fecha_fin'] != "") {
    $fechaFinal = $_POST['fecha_fin'];
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param3=" . $fechaFinal);
    $tiene_get = true;
    $fechaFinalLecturas = $fechaFinal; //Esta fecha se ocupa para el limite de las lecturas
} else {
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param3=0");
    $tiene_get = true;
    $fechaFinalLecturas = (date("Y") . "-" . date("m") . "-" . date("d")); //Esta fecha se ocupa para el limite de las lecturas;
}

/* Filtros de cliente */
if (isset($_GET['Cliente']) && $_GET['Cliente'] != "") {
    $cliente = $_GET['Cliente'];
    $obj = new Cliente();
    if ($obj->getRegistroById($cliente)) {
        if (!$tiene_get) {
            $liga_facturas .= "?";
        } else {
            $liga_facturas .= "&";
        }
        $liga_facturas .= ("param4=" . $obj->getRFC());
        $tiene_get = true;
    }
    $liga = "";
} else if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $cliente = $_POST['cliente'];
    $obj = new Cliente();
    if ($obj->getRegistroById($cliente)) {
        if (!$tiene_get) {
            $liga_facturas .= "?";
        } else {
            $liga_facturas .= "&";
        }
        $liga_facturas .= ("param4=" . $obj->getRFC());
        $tiene_get = true;
    }
    $liga = "indicadores/facturacion.php?Emisor=$emisor&Ejecutivo=$ejecutivo&Cliente=$cliente&FechaInicio=$fechaInicio&fechaFinal=$fechaFinal";
} else {
    if (!$tiene_get) {
        $liga_facturas .= "?";
    } else {
        $liga_facturas .= "&";
    }
    $liga_facturas .= ("param4=0");
    $tiene_get = true;
    $liga = "";
}

if ($tiene_get) {
    $liga_facturas .= "&";
} else {
    $liga_facturas .= "?";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Equipos</title>
        <meta charset="utf-8"/>        
    </head>
    <body>        
<?php if (!empty($liga)) { ?>
            <a class="highslide" href="<?php echo $liga; ?>" 
               onclick="return hs.htmlExpand(this, {objectType: 'iframe', width: 1020, headingText: 'Equipos', targetX: 'posicion 1px', targetY: null});">
<?php } ?>
            <table style="width: 75%;">
                <thead>
                    <tr style="background-color: #E6B8B7;">
                        <th>Facturaci&oacute;n</th>                    
                        <th></th>
                    </tr>
                </thead>            
                <tbody style="background-color: #F2DCDB;">
                    <?php
                    $indicadores = new Indicadores_Facturacion();
                    /* Monto facturado */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param5=1&param6=1&param7=3&param8=4&param9=5&param10=0&param12=0&param13=1&param14=0&param15=1' target='_blank'>
                            Monto Facturado</a>", $indicadores->obtenerMontoFacturas(false, false, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, false, false, 2));
                    /* Notas de credito */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param5=1&param6=1&param7=3&param8=4&param9=5&param10=1&param12=0&param13=1&param14=0&param15=1' target='_blank'>
                            Notas de crédito</a>", $indicadores->obtenerMontoFacturas(true, false, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, false, false, false));
                    /* Monto canceladas */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param5=0&param6=0&param7=0&param8=0&param9=0&param10=0&param12=0&param13=1&param14=0&param15=1' target='_blank'>
                            Canceladas</a>", $indicadores->obtenerMontoFacturas(false, true, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, false, false, false));
                    /* Facturas pendientes */
                    //$indicadores->imprimirConceptoCosto("Facturas Pendientes", "0");
                    /* Pendientes de cancelar */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param5=1&param6=0&param7=0&param8=0&param9=5&param10=0&param12=0&param13=1&param14=0&param15=1' target='_blank'>
                            Pendientes de cancelar</a>", $indicadores->obtenerMontoFacturas(false, false, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, true, false, false));
                    /* No Pagadas */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param5=1&param6=1&param7=0&param8=0&param9=0&param10=0&param12=0&param13=1&param14=0&param15=1' target='_blank'>
                            Facturas no pagadas</a>", $indicadores->obtenerMontoFacturas(false, false, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, false, true, false));
                    /* No Pagadas del año anterior */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param2=$anio_anterior-01-01&param3=$anio_anterior-12-31&param5=1&param6=1&param7=0&param8=0&param9=0&param10=0&param12=0&param13=1&param14=0&param15=1' target='_blank'>
                            Facturas no pagadas del años anterior</a>", $indicadores->obtenerMontoFacturas(false, false, $emisor, $ejecutivo, $cliente, "$anio_anterior-01-01", "$anio_anterior-12-31", false, true, false));
                    /* Facturas de ventas */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param5=1&param6=1&param7=3&param8=4&param9=5&param10=0&param12=1&param13=0&param14=2&param15=1' target='_blank'>
                            Facturas de ventas</a>", $indicadores->obtenerMontoFacturas(false, false, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, false, false, true));
                    /* Facturas no pagadas de ventas */
                    $indicadores->imprimirFila("<a href='" . $liga_facturas .
                            "param5=1&param6=1&param7=0&param8=0&param9=0&param10=0&param12=1&param13=0&param14=2&param15=1' target='_blank'>
                            Facturas no pagadas de ventas</a>", $indicadores->obtenerMontoFacturas(false, false, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, false, true, true));
                    /* Lecturas pendientes */
                    //$indicadores->imprimirConceptoCantidad("Lecturas pendientes", 200);
                    /* Lecturas tomadas */
                    //$indicadores->imprimirConceptoCantidad("Lecturas tomadas", 2);
                    ?>
                </tbody>
                <!--<tfoot>
                    <tr style="background-color: #E6B8B7;">
                        <td>Total</td>                    
                        <td style='text-align: right;'>  <?php //echo "$ ".number_format($indicadores->getCostoToal(), 2);  ?> </td>
                    </tr>
                </tfoot>-->
            </table>   
<?php if (!empty($liga)) { ?>
            </a>
<?php } ?>
    </body>
</html>