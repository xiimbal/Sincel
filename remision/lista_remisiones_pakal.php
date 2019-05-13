<?php
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: index.php");
    }
    include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");

    $catalogoF = new CatalogoFacturacion();
    $catalogo = new Catalogo();
    $permisos_grid = new PermisosSubMenu();
    $usuario = new Usuario();

    $folio = "";
    if (isset($_GET['id'])) {
        $folio = $_GET['id'];
        $activar_submit = true;
    }
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/remision/lista_remisiones_pakal.js"></script>

<div class="p-4 bg-light rounded"> <!-- fondo del contenido -->
    <form id="rfactura">
        <div class="form-row">
            <div class="form-group col-12 col-md-4"> <!-- Estado -->      
                <label class="m-0" for="TipoDomicilioF">Estado: </label>                            
                <select id="status" name="status[]" class="custom-select" multiple="multiple">
                    <?php
                    if (!$cxc) {//Si no estamos en la venta de cxc                        
                        if (isset($_GET['param6']) && $_GET['param6'] == "1") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }

                        echo "<option value='1' $s>No Pagada</option>";
                        if (isset($_GET['param8']) && $_GET['param8'] == "4") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        echo "<option value='4' $s>Pagadas</option>";
                    } else {
                        echo "<option value='1' selected='selected'>No Pagada</option>";
                        echo "<option value='4'>Pagadas</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-12 col-md-4"> <!-- Fecha de Inicio -->                  
                <label class="m-0" for="TipoDomicilioF">Fecha de Inicio: </label>  
                <input type="date" class="form-control" id="fecha1" name="fecha1" value="<?php
                    if (isset($_GET['param2']) && $_GET['param2'] != "0" && $_GET['param2'] != "") {
                        echo $_GET['param2'];
                        $activar_submit = true;
                    } else if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
                        $llamar = true;
                        echo $_GET['fecha1'];
                    }
                ?>"/>
            </div>
            <div class="form-group col-12 col-md-4"> <!-- Fecha fin -->                  
                <label class="m-0" for="TipoDomicilioF">Fecha fin: </label>   
                <input type="date" id="fecha2" class="form-control" name="fecha2" value="<?php
                    if (isset($_GET['param3']) && $_GET['param3'] != "0" && $_GET['param3'] != "") {
                        echo $_GET['param3'];
                        $activar_submit = true;
                    } else if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
                        $llamar = true;
                        echo $_GET['fecha2'];
                    }
                ?>"/>
            </div>
        </div>
        <div class="form-row">          
            <div class="form-group col-md-4"> <!-- Ejecutivo -->      
                <label class="m-0" for="TipoDomicilioF">Ejecutivo: </label>   
                <select id="ejecutivo" name="ejecutivo" class="custom-select">
                    <option value="">Todos los ejecutivos</option>
                    <?php
                    $result = $usuario->getUsuariosByPuesto("11");
                    while ($rs = mysql_fetch_array($result)) {
                        $s = "";
                        if (isset($_POST['ejecutivo']) && $_POST['ejecutivo'] == $rs['IdUsuario']) {
                            $s = "selected='selected'";
                        } else if (isset($_GET['param11']) && $_GET['param11'] != "0" && $_GET['param11'] == $rs['IdUsuario']) {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        }
                        echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4"> <!-- Cliente -->                  
                <label class="m-0" for="TipoDomicilioF">Cliente: </label>   
                <select id="cliente" name="cliente" class="custom-select">
                    <?php
                    echo "<option value=''>Todos los clientes</option>";
                    if (empty($array_clientes)) {
                        $query = $catalogo->obtenerLista("SELECT DISTINCT(RFC) AS RFC, NombreRazonSocial AS Nombre FROM c_cliente WHERE Activo = 1 AND RFC<>\"\" ORDER BY Nombre");
                    } else {
                        $query = $catalogo->obtenerLista("SELECT DISTINCT(RFC) AS RFC, NombreRazonSocial AS Nombre FROM c_cliente WHERE ClaveCliente IN($array_clientes) AND Activo = 1 ORDER BY Nombre");
                    }
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['RFC'] . "' >" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4"> <!-- Folio -->                  
                <label class="m-0" for="TipoDomicilioF">Folio: </label>   
                <input class="form-control" type="text" id="folio" name="folio" value="<?php echo $folio; ?>"/>
            </div>
        </div>
        <div class="form-row">  
            <div class="form-group col-md-4"> <!-- Tipo facturas -->      
                <label class="m-0" for="TipoDomicilioF">Tipo facturas: </label>  
                <select id="tipo_facturas" name="tipo_facturas[]" class="custom-select" multiple="multiple">
                    <?php
                        $consulta = "SELECT IdTipoFactura, TipoFactura FROM `c_tipofacturaexp` where IdTipoFactura = 2;";
                        $result = $catalogoF->obtenerLista($consulta);
                        if (isset($_GET['param12']) && $_GET['param12'] == "0") {
                            $s = "selected = 'selected'";
                            $activar_submit = true;
                        } else {
                            $s = "";
                        }
                        //echo "<option value='0' $s>Sin identificar</option>";
                        while ($rs = mysql_fetch_array($result)) {
                            $s = "";
                            if (isset($_GET['param13']) && $_GET['param13'] == "1" && $rs['IdTipoFactura'] == 1) {
                                $s = "selected = 'selected'";
                                $activar_submit = true;
                            }
                            if (isset($_GET['param14']) && $_GET['param14'] == "2" && $rs['IdTipoFactura'] >= 2) {
                                $s = "selected = 'selected'";
                                $activar_submit = true;
                            }
                            echo "<option value='" . $rs['IdTipoFactura'] . "' $s>" . $rs['TipoFactura'] . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4"> <!-- RFC Facturas -->                  
                <label class="m-0" for="TipoDomicilioF">RFC Facturas: </label>  
                <input class="form-control" type="text" id="rfc_facturas" name="rfc_facturas"/>
            </div>
        </div>
        <div class="clearfix">
            <button id="enviar" type="submit" class="btn btn-info float-right">Mostrar </button>         
        </div>
    </form>
</div>
<div id="tablamensajeinfo"></div>
<div id="tablainfo"></div>