<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$lista_alta = "admin/lista_cliente.php";

$NombreCliente = "";
$RFC = "";
$RazonSocial = "";
$id = "";
$NombreCentro = "";
$ClaveCentro = "";

if(isset($_POST['id'])){
    $id = $_POST['id'];
    $cliente = new Cliente();
    if($cliente->getRegistroById($id)){
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
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_validacion.js"></script>                
        <script type="text/javascript" language="javascript" src="resources/js/paginas/validacion/alta_cliente.js"></script>        
    </head>
    <body>
        <div class="principal">
            <div id="mensaje_cliente2" style="font-size: 15px;"></div>
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
            <br/><br/>
            <input type="submit" class="button" value="Regresar" style="float: right; margin-right: 5px;" onclick="cambiarContenidos('<?php echo $lista_alta; ?>','Clientes'); return false;"/>
            <?php
                echo '<script type="text/javascript">cambiarContenidoValidaciones("cliente2", "ventas/validacion/lista_cliente.php?Nombre='.$NombreClienteAux.'&Clave='.$id.'&NombreCentro='.$NombreCentroAux.'&ClaveCentro='.$ClaveCentro.'", null, null, false);</script>';
            ?>
            <br/><br/>
        </div>
    </body>
</html>