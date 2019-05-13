<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
$id = "";
if (!isset($_POST['id'])) {
    if (isset($_GET['id']))
        $id = $_GET['id'];
    else
        header("Location: ../index.php");
}
else
    $id = $_POST['id'];



if (isset($_GET['tipo']) && strtoupper($_GET['tipo']) == "SUMINISTRO") {
    $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $id . "&Vista=Detalle&uguid=" . $_SESSION['user'];
} else {
    $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $id . "&Vista=Detalle&uguid=" . $_SESSION['user'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
    </head>
    <body>
        <iframe src="<?php echo $src; ?>" style="width: 100%; height: 680px;"></iframe>
        <?php
        if(!isset($_GET['id'])){
        ?>
        <input type = "submit" class = "button" style = "float: right;" value = "Cancelar" onclick = "cambiarContenidos('hardware/mis_tickets_hw.php');
                return false;"/>
        <?php }?>
        <br/><br/><br/>
    </body>
</html>