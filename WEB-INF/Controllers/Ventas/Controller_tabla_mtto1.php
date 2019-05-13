<?php
session_start();
include_once("../../Classes/PermisosSubMenu.class.php");
include_once("../../Classes/Catalogo.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_arbol.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
if ($permisos_grid->getAlta()) {
    $catalogo = new Catalogo();
?>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_mantenimiento.js"></script>
    </head>
    <body>
        <form id="formMantenimiento">
            <input type="hidden" id="no_serie" name="no_serie" value="<?php echo $_POST['id']; ?>"/>
            <table>
                <tr>
                    <td>Nuevo mantenimiento</td>
                    <td></td>
                </tr>
                <tr>
                    <td><label for="fechaMtto">Fecha de inicio</label></td>
                    <td><input type="text" class="fecha" id="fechaMtto" name="fechaMtto" class="fecha"/></td>
                    <td><label for="periocidad">Periodicidad</label></td>
                    <td><select id="numero" name="numero">
                            <option value="">Selecciona</option>
                            <?php 
                                for($i=1;$i<31;$i++){
                                    echo "<option value=\"".$i."\">".$i."</option>";
                                }
                            ?>
                        </select></td>
                    <td><select id="periocidad" name="periocidad">
                            <option value="">Selecciona</option>
                            <option value="1">D&iacute;as</option>
                            <option value="2">Semanas</option>
                            <option value="3">Meses</option>
                        </select></td>
                    <td><label for="Area de atencion">Area de atenci&oacute;n</label></td>
                    <td>
                        <select id="area" name="area" style="width: 200px;" >
                            <?php
                            /* Inicializamos la clase */
                            $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                            INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");                                
                            echo "<option value=''>Todos las áreas</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (isset($_POST['area']) && $_POST['area'] == $rs['IdEstado']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                            }
                            ?> 
                        </select>
                    </td>        
                    <td><input type="submit" class="boton"  value="Guardar" /></td>
                </tr>
            </table>            
        </form>
        <br/><br/>
        <div id="tablamtto2"></div>
    </body>
</html>
<?php }?>