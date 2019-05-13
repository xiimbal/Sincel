<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }    
    $src = $_SESSION['liga']."/cfdi/consultafactura.aspx?uguid=".$_SESSION['user'];
    //$src = $_SESSION['liga']."/facturacion/ConsultaFactura.aspx?uguid=".$_SESSION['user'];;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
    </head>
    <body>
        <iframe src="<?php echo $src; ?>" style="width: 100%; height: 680px;"/>
    </body>
</html>