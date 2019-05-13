<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$permisos_grid = new PermisosSubMenu();

$catalogo = new Catalogo();
$NoSerie = $_GET['NoSerie'];
$IdKServicio = $_GET['IdKServicio'];
$prefijo_menor =  $_GET['prefijo'];
$anexo = $_GET['Anexo'];
$prefijo_mayor = strtoupper($prefijo_menor);

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/altaEquipoServicio.js"></script>
        <style>
            .chico{width: 80px;}
        </style>
    </head>
    <body>        
        <fieldset>
            <legend>Equipos</legend>
            <form id="formEquipos" name="formEquipos" action="/" method="POST">                
                <input type="hidden" id="IdKServicio" name="IdKServicio" value="<?php echo $IdKServicio; ?>"/>
                <input type="hidden" id="prefijo" name="prefijo" value="<?php echo $prefijo_menor; ?>"/>
                <table style="width: 100%">
                    <tr>
                        <td><label for="tipo_servicioIM">Tipo:</label></td>
                        <td>
                            <?php
                            $consulta = "SELECT kacc.IdAnexoClienteCC, kacc.ClaveAnexoTecnico,
                            kim.IdKServicioIM, CONCAT(kim.IdKServicioIM,' - ',cim.Nombre) AS im, 
                            kfa.IdKServicioFA, CONCAT(kfa.IdKServicioFA,' - ',cfa.Nombre) AS fa, 
                            kgim.IdKServicioGIM, CONCAT(kgim.IdKServicioGIM,' - ',cgim.Nombre) AS gim, 
                            kgfa.IdKServicioGFA, CONCAT(kgfa.IdKServicioGFA,' - ',cgfa.Nombre) AS gfa 
                            FROM k_anexoclientecc AS kacc
                            LEFT JOIN k_servicioim AS kim ON kacc.IdAnexoClienteCC = kim.IdAnexoClienteCC
                            LEFT JOIN c_servicioim AS cim ON kim.IdServicioIM = cim.IdServicioIM
                            LEFT JOIN k_serviciofa AS kfa ON kacc.IdAnexoClienteCC = kfa.IdAnexoClienteCC
                            LEFT JOIN c_serviciofa AS cfa ON kfa.IdServicioFA = cfa.IdServicioFA
                            LEFT JOIN k_serviciogim AS kgim ON kacc.IdAnexoClienteCC = kgim.IdAnexoClienteCC
                            LEFT JOIN c_serviciogim AS cgim ON kgim.IdServicioGIM = cgim.IdServicioGIM
                            LEFT JOIN k_serviciogfa AS kgfa ON kacc.IdAnexoClienteCC = kgfa.IdAnexoClienteCC
                            LEFT JOIN c_serviciogfa AS cgfa ON kgfa.IdServicioGFA = cgfa.IdServicioGFA
                            WHERE kacc.ClaveAnexoTecnico = '$anexo';";
                            //echo $consulta;
                            ?>
                            <select id="tipo_servicio" name="tipo_servicio" style="max-width: 180px;" onchange="cambiarTipoServicio('tipo_servicioIM');">
                                <?php                                    
                                    $result = $catalogo->obtenerLista($consulta);
                                    while($rs = mysql_fetch_array($result)){
                                        if(isset($rs['im'])){
                                            $s = "";
                                            if($prefijo_menor == "im" && $IdKServicio == $rs['IdKServicioIM']){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='IM..".$rs['IdKServicioIM']."' $s>".$rs['im']."</option>";
                                        }
                                        if(isset($rs['fa'])){
                                            $s = "";
                                            if($prefijo_menor == "fa" && $IdKServicio == $rs['IdKServicioFA']){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='FA..".$rs['IdKServicioFA']."' $s>".$rs['fa']."</option>";                                        
                                        }
                                        if(isset($rs['gim'])){
                                            $s = "";
                                            if($prefijo_menor == "gim" && $IdKServicio == $rs['IdKServicioGIM']){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='GIM..".$rs['IdKServicioGIM']."' $s>".$rs['gim']."</option>";                                        
                                        }
                                        if(isset($rs['gfa'])){
                                            $s = "";
                                            if($prefijo_menor == "im" && $IdKServicio == $rs['IdKServicioIM']){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='GFA..".$rs['IdKServicioGFA']."' $s>".$rs['gfa']."</option>";                                        
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="NoSerie">No. Serie:</label></td>
                        <td><input type="text"  id="NoSerie" name="NoSerie" value="<?php echo $NoSerie; ?>" readonly="readonly"/></td>                        
                    </tr>
                </table>
                <input type="submit" id="cancelar_servicioim" class="boton" id="cancelar" value="Cancelar" style="float: right; margin-right: 5px;"
                onclick="cargarDependencia('equipos_p2','../cliente/validacion/lista_equiposServicio.php','<?php echo $IdKServicio ?>',null,'<?php echo $prefijo_menor; ?>');  
                return false;"/>
                <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16)){  ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <br/><br/><br/><br/>
            </form>
        </fieldset>
    </body>
</html>