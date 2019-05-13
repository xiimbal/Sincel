<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

if (!isset($_POST['idTicket'])) {
    header("Location: ../../index.php");
}

$ClienteCodificado = "";
$NombreCliente = "";   
$ClaveCliente = "";
$NombreCentro = "";
$ClaveCentro = "";

if (isset($_GET['Clave'])) {
    if(isset($_GET['Nombre'])){
        $ClienteCodificado = $_GET['Nombre'];
        $NombreCliente = str_replace("__XX__", " ",  $_GET['Nombre']);   
    }
    $ClaveCliente = $_GET['Clave'];
    $NombreCentro = $_GET['NombreCentro'];
    $ClaveCentro = $_GET['ClaveCentro'];
}

$liga = "../cliente/validacion/alta_cliente.php?Nombre=$ClienteCodificado&Clave=$ClaveCliente&NombreCentro=".$_GET['NombreCentro']."&ClaveCentro=".$_GET['ClaveCentro'];

include_once("../../WEB-INF/Classes/Cliente.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){
    $permiso = true;
}else{
    $permiso = false;
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>        
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/lista_cliente.js"></script>        
    </head>
    <body>        
        <div class="container-fluid p-3 bg-light rounded">
        <h5>Cliente</h5>
            <div id="nuevo_cliente"></div>
            <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 15)){ ?>
                <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo cliente" onclick='cambiarContenidoValidaciones("cliente2", "../cliente/validacion/alta_cliente.php?Nuevo=true&Nombre=<?php echo $ClienteCodificado; ?>&Clave=<?php echo $ClaveCliente; ?>&NombreCentro=<?php echo $_GET['NombreCentro']; ?>&ClaveCentro=<?php echo $_GET['ClaveCentro']; ?>", $("#idTicket").val(), null, true);
                return false;' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <div class="table-responsive">
                <?php        
                    $cliente = new Cliente();
                    /* Obtenemos los posibles equipos asociados al ticket */
                    $query = $cliente->getRegistrosByClave($ClaveCliente);
                    
                    //if(mysql_num_rows($query)){
                    echo '<table class="table">
                            <thead class="thead-dark">
                                <tr>
                                    <td></td>
                                    <td>RFC</td>
                                    <td>Nombre</td>
                                    <td>Tipo</td>                                                
                                    <td>Clave</td>                        
                                    <td></td>                                                
                                </tr>
                            </thead>';
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td><input type='radio' id='check_cliente_".$rs['ClaveCliente']."' name='check_cliente' 
                            onclick=' ";
                        if(isset($_POST['idTicket']) && $_POST['idTicket']!=null){
                                echo "actualizarTicket(\"".$_POST['idTicket']."\",\"".$rs['ClaveCliente']."\",\"cliente\");";
                        }
                        echo "$(\"#clave_cliente1\").val(\"".$rs['ClaveCliente']."\"); $(\"#clave_localidad1\").val();";
                        echo "$(\"#anexo2\").empty(); $(\"#servicios_p2\").empty(); $(\"#servicios_g2\").empty(); $(\"#equipos_p2\").empty();"; 
                        echo "cargarDependencia(\"localidad2\",\"../cliente/validacion/lista_localidad.php?Nombre=".$NombreCentro."&Clave=".$ClaveCentro."\",\"".$rs['ClaveCliente']."\",\"check_cliente_".$rs['ClaveCliente']."\",\"".$_POST["idTicket"]."\");
                            cargarDependencia(\"contrato2\",\"../cliente/validacion/lista_contrato.php\",\"".$rs['ClaveCliente']."\",\"check_cliente_".$rs['ClaveCliente']."\",\"".$_POST["idTicket"]."\");'/>
                        </td>";           
                        echo "<td>" . $rs['RFC'] . "</td>";
                        echo "<td>" . $rs['NombreRazonSocial'] . "</td>";
                        echo "<td>" . $rs['tipoCliente'] . "</td>";                
                        echo "<td>" . $rs['ClaveCliente'] . "</td>";                
                        echo "<td>"; 
                        echo "<a href='#' onclick='cambiarContenidoValidaciones(\"cliente2\",\"$liga\", $(\"#idTicket\").val(),\"" . $rs['ClaveCliente'] . "\",false); return false;'>";
                        if($permiso){                            
                            echo "<i class='fal fa-pencil'></i>"; 
                        }else{
                            echo "<i class='far fa-eye'></i>"; 
                        }
                        echo "</a></td>";
                        echo "</tr>";
                    }            
                    echo '</table>';
                    //}
                ?>
            </div>
        </div>       
    </body>
</html>