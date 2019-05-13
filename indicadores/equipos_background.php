<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {    
    header("Location: index.php");
}
$liga = "indicadores/equipos.php?Emisor=".$_POST['razonSocial']."&Ejecutivo=".$_POST['ejecutivo']."&Cliente=".$_POST['cliente'];
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Equipos</title>
        <meta charset="utf-8"/>
    </head>
    <body>
        <div id="posicion">
            <a class="highslide" href="<?php echo $liga; ?>" 
            onclick="return hs.htmlExpand(this, { objectType: 'iframe',width: 1020, headingText: 'Equipos', targetX: 'posicion 1px', targetY: null});">
                <img src="resources/images/institucion.png" width="100%" height="275px"/></a>
        </div>
    </body>
</html>