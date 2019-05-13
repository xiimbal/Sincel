<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Puesto.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$permisos = new PermisosSubMenu();
$catalogo = new Catalogo();

$pagina_lista = "admin/lista_puesto.php";
$id="";
$idPuesto="";
$nombre="";
$descripcion="";
$activo="checked='checked'";
$read = "";
$reabrir = "";
$areas = array();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_puesto.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $id=$_POST['id'];
                $obj = new Puesto();
                $obj->getRegistroById($_POST['id']);
                $read = "readonly='readonly'";
                $idPuesto = $obj->getIdPuesto();
                $nombre = $obj->getNombre();
                $descripcion = $obj->getDescripcion();
                if($obj->getActivo()=="0"){
                    $activo = "";
                }
                if($obj->getReAbrirTicket()=="1"){
                    $reabrir = "checked='checked'";
                }
                $areas = $obj->obtenerAreasPuesto();
            }
            ?>
            <form id="formPuesto" name="formPuesto" action="/" method="POST">
                <table style="min-width: 90%;">
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td><td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>
                        <td><label for="descripcion">Descripción</label></td><td><input type="text" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>"/></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>                    
                </table><br/>
                <fieldset>
                    <legend>Áreas de atención tickets</legend>
                    <table>
                        <tr>
                            <td><label for="areas">Áreas</label></td>
                            <td>
                                <select id="areas" name="areas[]" class="multiselect" multiple="multiple">
                                    <?php
                                    /* Inicializamos la clase */
                                    $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                    INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");                                    
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if (!empty($areas) && in_array($rs['IdEstado'], $areas)) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Permisos especiales</legend>
                    <?php $result = $permisos->getPermisosEspecialesSubMenuByPuesto($id); ?>
                    <table>
                        <tr>
                            <td><input type="checkbox" name="reabrir" id="reabrir" <?php echo $reabrir; ?>/>Reabrir tickets</td>
                            <?php
                                if(mysql_num_rows ($result) > 0){                                    
                                    $contador = 0;
                                    while($rs = mysql_fetch_array($result)){
                                        if($contador > 0 && $contador % 10 == 0){
                                            echo "</tr><tr>";
                                        }
                                        $s = "";
                                        if(intval($rs['agregado']) > 0){
                                            $s = "checked='checked'";
                                        }
                                        echo "<td><input type=\"checkbox\" name=\"especial_$contador\" id=\"especial_$contador\" 
                                            value='".$rs['IdPermisoEspecial']."' $s/>".$rs['NombrePermiso']."</td>"; 
                                        $contador++;
                                    }                                    
                                }
                            ?>
                        </tr>
                    </table>
                </fieldset>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                echo "<input type='hidden' id='id' name='id' value='" . $id . "'/> ";
                echo "<input type='hidden' id='numero_permisos' name='numero_permisos' value='" . $contador . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>
