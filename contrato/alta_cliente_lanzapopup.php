<?php
$ClaveCliente = "";
if(isset($_GET['ClaveCliente']) && $_GET['ClaveCliente'] != ""){
    $ClaveCliente = "?ClaveCliente=" . $_GET['ClaveCliente'];
}
?>
<html>
    <head>
        <title>Lanza alta cliente</title>
        <script>
            $(document).ready(function(){
                lanzarPopUp('Alta cliente', 'cliente/alta_cliente.php<?php echo $ClaveCliente?>');
                $(".boton").button();
            });
        </script>
    </head>
    <body>
        <a href="#" title="Alta cliente" onclick="lanzarPopUp('Alta cliente', 'cliente/alta_cliente.php<?php echo $ClaveCliente?>');
                            return false;"><img src="resources/images/client_icon.gif" width="28" height="28" style="margin-left: 48%;"/></a>
        <?php if(isset($_GET['regresar']) && $_GET['regresar'] != ""){ ?>
        <button type="button" class="boton" onclick="cambiarContenidos('<?php echo $_GET['regresar']?>','');">Regresar</button>
        <?php }?>
    </body>
</html>