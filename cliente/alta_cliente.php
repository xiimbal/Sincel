<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$NombreCliente = "";
$RFC = "";
$RazonSocial = "";
$id = "";
$NombreCentro = "";
$ClaveCentro = "";

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $cliente = new Cliente();
    if ($cliente->getRegistroById($id)) {
        $NombreCliente = $cliente->getNombreRazonSocial();
        $RFC = $cliente->getRFC();
        $RazonSocial = $cliente->getIdDatosFacturacionEmpresa();
    }
} else if (isset($_GET['ClaveCliente'])) {
    $id = $_GET['ClaveCliente'];
    $cliente = new Cliente();
    if ($cliente->getRegistroById($id)) {
        $NombreCliente = $cliente->getNombreRazonSocial();
        $RFC = $cliente->getRFC();
        $RazonSocial = $cliente->getIdDatosFacturacionEmpresa();
    }
}

$NombreClienteAux = str_replace(" ", "__XX__", $NombreCliente);
$NombreCentroAux = str_replace(" ", "__XX__", $NombreCentro);
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
        <!-- <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css"> -->
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script> 
        <!-- <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css"> -->
        
        <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>                
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_cliente.js"></script>

        <link href="../resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container-fluid">
            <div id="cargando">
                <img src="../resources/images/cargando.gif"/>                          
            </div>

            <div id="loading_text"></div>
            <div id="mensaje_cliente2"></div>
            <div id="cliente2">

            </div>
            <div id="mensaje_localidad2"></div>
            <div id="localidad2">

            </div>
            <div id="mensaje_domicilio2"></div>
            <div id="domicilio2">

            </div>
            <div id="mensaje_contacto2"></div>
            <div id="contacto2">

            </div>
            <div id="mensaje_contrato2"></div>
            <div id="contrato2">

            </div>
            <div id="mensaje_anexo2"></div>
            <div id="anexo2">

            </div>
            <div id="mensaje_serviciosg2"></div>
            <div id="servicios_g2">

            </div>
            <div id="mensaje_serviciosp2"></div>
            <div id="servicios_p2">

            </div>
            <div id="mensaje_equipos"></div>
            <div id="equipos_p2">

            </div>    
            <?php
            if (isset($_GET['action']) && $_GET['action'] == "alta") {
                echo '<script type="text/javascript">cambiarContenidoValidaciones("cliente2", "../cliente/validacion/alta_cliente.php?Nuevo=true", null, null, false);</script>';
            } else {
                echo '<script type="text/javascript">cambiarContenidoValidaciones("cliente2", "../cliente/validacion/lista_cliente.php?Nombre=' . $NombreClienteAux . '&Clave=' . $id . '&NombreCentro=' . $NombreCentroAux . '&ClaveCentro=' . $ClaveCentro . '", null, null, false);</script>';
            }
            ?>
            <input type='hidden' id='contenidos_invisibles' name='contenidos_invisibles' class="complete"/>
            <input type='hidden' id='clave_localidad1' name='clave_localidad1' class="complete"/>
            <input type='hidden' id='clave_cliente1' name='clave_cliente1' class="complete"/>
        </div>
    </body>
</html>