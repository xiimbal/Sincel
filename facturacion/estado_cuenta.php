<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

header('Content-Type: text/html; charset=UTF-8');
ini_set("memory_limit","600M");

include_once("../WEB-INF/Classes/EstadoCuenta.class.php");

$estado = new EstadoCuenta();

$mostrar_pagado = false;
if(isset($_POST['mostrar_pagado']) && $_POST['mostrar_pagado'] == "1"){
    $mostrar_pagado = true;    
}

?>
<!DOCTYPE>
<html lang="es" style="width: 100%;">
    <head>
        <title>Reporte de facturación</title>
        <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style>
            body{
                font-size: 11px;
            }
            
            @media all {
                    .page-break	{ display: none; }
            }

            @media print {
                    .page-break	{ display: block; page-break-before: always; }
            }
        </style>
    </head>
    <body> 
        <a href=javascript:window.print(); style="margin: 85%;"><img src="../resources/images/icono_impresora2.jpg" width="25px" height="25px"/></a>
        <?php
            $estado->generarEstadoCuenta($_POST['cliente'], $_POST['fecha_inicio'], $_POST['fecha_fin'], $mostrar_pagado, false);            
        ?>        
    </body>
</html>

