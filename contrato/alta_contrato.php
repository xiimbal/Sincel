<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
    $src = $_SESSION['liga']."/Operacion/Operacion/Contratos/ConsultaContrato.aspx?uguid=".$_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
    </head>
    <body>
        <iframe src="<?php echo $src; ?>" style="width: 100%; height: 680px;"></iframe>
        <input type="submit" class="button" style="float: right;" value="Cancelar" onclick="cambiarContenidos('ventas/mis_clientes.php');return false;"/>
        <br/><br/><br/>
    </body>
</html>