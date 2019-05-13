<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
    
    if(!isset($_POST['id'])){
        header("Location: ../index.php");
    }
    
    if(isset($_GET['tipo']) && strtoupper($_GET['tipo']) == "SUMINISTRO"){
        $src = $_SESSION['liga']."/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=".$_POST['id']."&Vista=Detalle&uguid=".$_SESSION['user'];
    }else{
        $src = $_SESSION['liga']."/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=".$_POST['id']."&Vista=Detalle&uguid=".$_SESSION['user'];
    }
    
    $filter = str_replace(" ", "_XX__XX_", $_POST['filter']);
    
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
    </head>
    <body>
        <iframe src="<?php echo $src; ?>" style="width: 100%; height: 680px;"></iframe>
        <input type="submit" class="button" style="float: right;" value="Cancelar" onclick="cambiarContenidos('facturacion/pendientes.php?page=<?php echo $_POST['page'] ?>&filter=<?php echo $filter; ?>');return false;"/>
        <br/><br/><br/>
    </body>
</html>