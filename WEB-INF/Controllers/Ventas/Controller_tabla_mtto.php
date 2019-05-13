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
            <div class="container p-3 bg-light rounded">
                <h5>Nuevo mantenimiento</h5>
                <div class="form-row">
                    <div class="form-group col-md-6 col-12">
                        <label for="fechaMtto" class="m-0">Fecha de inicio</label>
                        <input type="text" id="fechaMtto" name="fechaMtto" class="fecha form-control"/>
                    </div>
                    <div class="form-group col-md-6 col-12">
                        <label for="periocidad" class="m-0">Periodicidad</label>
                        <select id="periocidad" name="periocidad" class="custom-select">
                            <option value="">Selecciona</option>
                            <option value="1">D&iacute;as</option>
                            <option value="2">Semanas</option>
                            <option value="3">Meses</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6 col-12">
                        <label for="numero" class="m-0">Numero</label>
                        <select id="numero" name="numero" class="custom-select">
                            <option value="">Selecciona</option>
                            <?php 
                                for($i=1;$i<31;$i++){
                                    echo "<option value=\"".$i."\">".$i."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6 col-12">
                        <label for="area" class="m-0">Area de atenci&oacute;n</label>
                        <select id="area" name="area"  class="custom-select">
                            <option value=''>Todos las &aacute;reas</option>
                            <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (isset($_POST['area']) && $_POST['area'] == $rs['IdEstado']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                            ?> 
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6 col-12">
                        <input type="submit" class="btn btn-secondary"  value="Guardar" />
                    </div>
                </div>
            </div>
        </form>
        <br/><br/>
        <div id="tablamtto2"></div>
    </body>
</html>
<?php }?>