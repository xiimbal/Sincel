<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }
    include_once("../WEB-INF/Classes/Parametros.class.php");
    $parametros = new Parametros();
    $user = "e.s.sanchez@gmail.com";
    $password = "1234567";
    if($parametros->getRegistroById("19")){
        $user = $parametros->getDescripcion();
    }
    if($parametros->getRegistroById("20")){
        $password = $parametros->getDescripcion();
    }
    
    $src = "http://greenfact.mx/nomina/index.php?r=site/login&banner=no";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
    </head>
    <body>
        Usuario: <?php echo $user; ?>
        <br/>Password: <?php echo $password; ?>
        <iframe src="<?php echo $src;?>" style="width: 100%; height: 700px;"></iframe>
    </body>
</html>