<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PrecioABC.class.php");
include_once("../WEB-INF/Classes/Componente.class.php");

$catalogo = new Catalogo();
$precio = new PrecioABC();
$tipo_componente = "";
if(!$precio->getRegistroById($_GET['id'])){
    echo "<br/>Error: no existe el precio que estás intenando consultar";
    return false;    
}


?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/EditarPrecioABC.js"></script>
<style>
    .ui-multiselect {
        max-width: 170px;
        font-size: 10px;
    }
    .ui-multiselect-checkboxes {
        font-size: 12px;
    }
</style>
<div class="p-4">
    <form id="formprecioabc">
        <div class="form-row">
            <div class="form-group col-md-4 col-12">
                <label for="tipo" class="m-0">Tipo</label>
                <select id="tipo" name="tipo" class="custom-select" disabled="disabled">
                    <option value="">Selecciona el tipo</option>
                    <?php
                        if ($precio->getNoParteEquipo() != NULL && $precio->getNoParteEquipo() != "") {
                            $tipo_componente = 0;
                            echo "<option value=\"0\" selected>Equipo</option>";
                        } else {
                            $componente = new Componente();                        
                            if($precio->getNoParteComponente() != NULL && $precio->getNoParteComponente() != "" && $componente->getRegistroById($precio->getNoParteComponente())){
                                $tipo_componente = $componente->getTipo();
                            }
                            $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre " . "FROM c_tipocomponente ORDER BY Nombre;");
                            while ($rs = mysql_fetch_array($query2)) {
                                echo ( $tipo_componente == $rs['ID'] 
                                    ? "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>" 
                                    : "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>" );
                            }
                        }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4 col-12">
                <label for="modelo" class="m-0">Modelo</label>
                <select id="modelo" name="modelo" class="custom-select" disabled="disabled">
                    <?php
                        if ($tipo_componente == "0") {
                            $query3 = $catalogo->obtenerLista("SELECT DISTINCT c_equipo.Modelo AS Modelo, c_equipo.NoParte AS Parte FROM c_equipo WHERE c_equipo.NoParte='".$precio->getNoParteEquipo()."'");
                            while ($rsp = mysql_fetch_array($query3)) {
                                echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                            }
                        } else {
                            $query3 = $catalogo->obtenerLista("SELECT DISTINCT c_componente.Modelo AS Modelo, c_componente.NoParte AS Parte, c_componente.Descripcion AS Descripcion FROM c_componente
                                INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente WHERE c_componente.NoParte='".$precio->getNoParteComponente()."'");
                            while ($rsp = mysql_fetch_array($query3)) {
                                echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . " / " . $rsp['Descripcion'] . "</option>";
                            }
                        }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4 col-12">
                <label for="almacen" class="m-0">Almacen</label>
                <select id="almacen" name="almacen" class="custom-select" >
                    <option value="">Selecciona el almacén</option>                    
                    <?php
                        $query2 = $catalogo->getListaAlta("c_almacen", "nombre_almacen");
                        while ($rs = mysql_fetch_array($query2)) {   
                            $s = "";
                            if($rs['id_almacen'] == $precio->getIdAlmacen()){
                                $s = "selected='selected'";
                            }
                            echo "<option value=\"" . $rs['id_almacen'] . "\" $s>" . $rs['nombre_almacen'] . "</option>";
                        }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4 col-12">
                <label for="precioa" class="m-0">Precio A</label>
                <input type="text" id="precioa" name="precioa" value="<?php echo $precio->getPrecio_A()?>" class="form-control">
            </div>
            <div class="form-group col-md-4 col-12">
                <label for="preciob" class="m-0">Precio B</label>
                <input type="text" id="preciob" name="preciob" value="<?php echo $precio->getPrecio_B()?>" class="form-control">
            </div>
            <div class="form-group col-md-4 col-12">
                <label for="precioc" class="m-0">Precio C</label>
                <input type="text" id="precioc" name="precioc" value="<?php echo $precio->getPrecio_C()?>" class="form-control">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4 col-12">
                <input type="submit" id="aceptar" class="btn btn-secondary" name="aceptar" value="Guardar"/>
                <input type="button" id="cancelar" class="btn btn-secondary" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/lista_precios_abc.php', 'Precios ABC');"/>
            </div>
        </div>
    </form>
</div>
