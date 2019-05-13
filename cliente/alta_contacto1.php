<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Contacto.class.php");

if(isset($_GET['ClaveCliente'])){
    $ClaveCliente = $_GET['ClaveCliente'];
    $contacto = new Contacto();
    
    //Obtenemos todos los contactos asociados al cliente y a sus localidades.
    $result = $contacto->getTodosContactosCliente($ClaveCliente);
    
    $cabeceras = array("Localidad", "Nombre", "Correo electrónico","Celular","Tipo Contacto"," "," ");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.9.1.js"></script>
        <script src="../resources/js/jquery/jquery-ui-1.10.3.custom.min.js"></script>        
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
        <script type="text/javascript" src="../resources/js/funciones.js"></script>                   

        <!-- Tables -->
        <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
        <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">  
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/ventas/alta_contacto.js"></script>
    </head>
    <body onload='cambiarContenidosContacto("../cliente/editar_contacto.php?ClaveCliente=<?php echo $ClaveCliente; ?>", "Nuevo Contacto");'>
        <br/>
        <div id="mensaje_contacto2"></div>
        <div id="contenidos" style="max-width: 97%;">
            
        </div>
        <div id="contenidos_invisibles">           
        </div>
        <div id="loading_text">          
        </div>
    </body>
</html>
<?php
}else{
    echo "<b>Se ha producido un error</b> La clave del cliente no se pudo obtener";
}
?>

