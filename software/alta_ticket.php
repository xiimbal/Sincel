<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
        
    $miUsuario=$_SESSION['user'];
    
    $src = $_SESSION['liga']."/Operacion/MesaServicio/AltaTicket.aspx?Vista=Agregar&Operacion=2&uguid=".$miUsuario;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
    </head>
    <body>
        <iframe src="<?php echo $src;?>" style="width: 100%; height: 680px;"></iframe>
    </body>
</html>