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
<form id="formprecioabc">
    <table style="width: 100%;">
        <tr>
            <td>Tipo</td>
            <td>
                <select id="tipo" name="tipo" class="filtro" style="width: 200px;" disabled="disabled">
                    <option value="">Selecciona el tipo</option>
                    <?php
                    if ($precio->getNoParteEquipo() != NULL && $precio->getNoParteEquipo() != "") {
                        $tipo_componente = 0;
                        echo "<option value=\"0\" selected>Equipo</option>";
                    } else {
                        $componente = new Componente();                        
                        if($precio->getNoParteComponente() != NULL && $precio->getNoParteComponente() != "" 
                                && $componente->getRegistroById($precio->getNoParteComponente())){
                            $tipo_componente = $componente->getTipo();
                        }
                        $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre "
                                . "FROM c_tipocomponente ORDER BY Nombre;");
                        while ($rs = mysql_fetch_array($query2)) {
                            if ($tipo_componente == $rs['ID']) {
                                echo "<option value=\"" . $rs['ID'] . "\" selected>" . $rs['Nombre'] . "</option>";
                            } else {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </td>            
            <td>Modelo</td>
            <td><select id="modelo" name="modelo" class="filtro" style="width: 200px;" disabled="disabled">
                    <?php
                    if ($tipo_componente == "0") {
                        $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                            c_equipo.Modelo AS Modelo,
                            c_equipo.NoParte AS Parte 
                            FROM
                            c_equipo
                            WHERE c_equipo.NoParte='".$precio->getNoParteEquipo()."'");
                        while ($rsp = mysql_fetch_array($query3)) {
                            echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
                        }
                    } else {
                        $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                            c_componente.Modelo AS Modelo,
                            c_componente.NoParte AS Parte,
                            c_componente.Descripcion AS Descripcion
                            FROM
                            c_componente
                            INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
                            WHERE c_componente.NoParte='".$precio->getNoParteComponente()."'");
                        while ($rsp = mysql_fetch_array($query3)) {
                            echo "<option value=\"" . $rsp['Parte'] . "\" selected>" . $rsp['Modelo'] . " / " . $rsp['Parte'] . " / " . $rsp['Descripcion'] . "</option>";
                        }
                    }
                    ?>
                </select></td>
                <td>Almacén</td>
                <td>
                    <select id="almacen" name="almacen" class="filtro" style="width: 200px;">
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
                </td>
        </tr>        
        <tr>
            <td>Precio A</td>
            <td><input type="text" id="precioa" name="precioa" style="width: 200px;" value="<?php echo $precio->getPrecio_A()?>"></td>
            <td>Precio B</td>
            <td><input type="text" id="preciob" name="preciob" style="width: 200px;" value="<?php echo $precio->getPrecio_B()?>" ></td>
            <td>Precio C</td>
            <td><input type="text" id="precioc" name="precioc" style="width: 200px;" value="<?php echo $precio->getPrecio_C()?>"></td>
        </tr>
        <input type="hidden" name="idabc" id="idabc" value="<?php echo $precio->getId_precio_abc()?>"/>
    </table>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/lista_precios_abc.php', 'Precios ABC');"/>
</form>