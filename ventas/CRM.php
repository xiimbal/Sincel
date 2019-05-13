<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
        
    $miUsuario=$_SESSION['user'];
    
    $src = "http://crm.gnsys.mx/";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
    </head>
    <body>
        Usuario: genesis
        <br/>Password: svFmmf
        <iframe src="<?php echo $src;?>" style="width: 100%; height: 700px;"></iframe>
    </body>
</html>