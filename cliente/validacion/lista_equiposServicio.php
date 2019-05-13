<?php
session_start();

include_once("../../WEB-INF/Classes/ServicioIM.class.php");
include_once("../../WEB-INF/Classes/ServicioFA.class.php");
include_once("../../WEB-INF/Classes/ServicioGIM.class.php");
include_once("../../WEB-INF/Classes/ServicioGFA.class.php");

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

$id = $_POST['id'];
$prefijo_menor = $_POST['idTicket'];/*Aprovechamos el parametro post idticket para recibir el prefijo*/
$prefijo_mayor = strtoupper($prefijo_menor);

if($prefijo_menor == "im"){
    $servicio = new ServicioIM();
}else if($prefijo_menor == "fa"){
    $servicio = new ServicioFA();
}else if($prefijo_menor == "gim"){
    $servicio = new KServicioGIM();
}else if($prefijo_menor == "gfa"){
    $servicio = new ServicioGFA();
}

include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/lista_servicio_im.js"></script>
        <style>
            .entrada {max-width: 55px;}
        </style>
    </head>
    <body>        
        <?php
            if(isset($_POST['filtro']) && isset($_POST['cc']) && $_POST['cc']!=""){
                $result = $servicio->getEquiposByIdKAnexoFiltroCC($id, $_POST['cc']);                
            }else{
                $result = $servicio->getEquiposByIdKAnexo($id);
            }
        ?>
        <fieldset>
            <legend>Equipos</legend>  
            <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 15)){ ?>
            <img class="imagenMouse" src="../resources/images/add.png" title="Nuevo equipo" 
                 onclick='cambiarContenidoValidaciones("equipos_p2", "../contrato/alta_equipo.php?ind=true&prefijo=<?php echo $prefijo_menor; ?>&servicio=<?php echo $id; ?>&cliente="+$("#clave_cliente1").val()+"&id="+$("#clave_localidad1").val(),null, null, true);
                return false;' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <?php if(mysql_num_rows($result) > 0){ ?>
                <table class="filtro" style="min-width: 100%;">
                    <thead>
                        <tr>                    
                            <td>NoSerie</td>
                            <td>Modelo</td>
                            <td>Cliente - Localidad</td>
                            <td>Servicio</td>                    
                            <td></td>
                        </tr>
                    </thead>
                    <?php
                        while($rs = mysql_fetch_array($result)){
                            echo "<tr>";
                            echo "<td>".$rs['NoSerie']."</td>";
                            echo "<td>".$rs['Modelo']."</td>";
                            echo "<td>".$rs['Cliente']."</td>";
                            echo "<td>$id</td>";
                            echo "<td align='center' scope='row'>";                             
                            echo "<a href='#' 
                                    onclick='cambiarContenidoServicio(\"equipos_p2\",\"cliente/validacion/altaEquipoServicio.php?Anexo=".$rs['ClaveAnexoTecnico']."&NoSerie=".$rs['NoSerie']."&IdKServicio=$id&prefijo=$prefijo_menor\",
                                    true,\"$id;\",\"".$rs['NoSerie']."\"); return false;'>";
                            if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){                            
                                echo "<img src=\"../resources/images/Modify.png\"/>"; 
                            }else{
                                echo "<img src=\"../resources/images/Textpreview.png\"/>"; 
                            }
                            echo "</a></td>";
                            echo "</tr>";
                        }
                    ?>
                </table>
            <?php }else{
                echo "<br/><br/>No se encontraron equipos";
            } ?>
        </fieldset>
    </body>
</html>