<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_evento.php";

$IdEvento = '';
$ClaveCliente = '';
$Nombre = '';
$Descripcion = '';
$FechaInicio = '';
$FechaFin = '';
$Imagen = '';
$Calle = '';
$NoExterior = '';
$NoInterior = '';
$Colonia = '';
$Ciudad = '';
$Estado = '';
$Delegacion = '';
$Pais = '';
$CodigoPostal = '';
$Latitud = '';
$Longitud = '';
$Activo = "checked='checked'";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_evento.js"></script>       
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Evento();
                if ($obj->getRegistroById($_POST['id'])) {
                    $IdEvento = $obj->getIdEvento();
                    $ClaveCliente = $obj->getClaveCliente();
                    $Nombre = $obj->getNombre();
                    $Descripcion = $obj->getDescripcion();
                    $FechaInicio = $obj->getFechaInicio();
                    $FechaFin = $obj->getFechaFin();
                    $Imagen = $obj->getImagen();
                    $Calle = $obj->getCalle();
                    $NoExterior = $obj->getNoExterior();
                    $NoInterior = $obj->getNoInterior();
                    $Colonia = $obj->getColonia();
                    $Ciudad = $obj->getCiudad();
                    $Estado = $obj->getEstado();
                    $Delegacion = $obj->getDelegacion();
                    $Pais = $obj->getPais();
                    $CodigoPostal = $obj->getCodigoPostal();
                    $Latitud = $obj->getLatitud();
                    $Longitud = $obj->getLongitud();
                    if ($obj->getActivo() == "0") {
                        $Activo = "";
                    }
                }
            }
            ?>
            <form id="formEvento" name="formEvento" action="/" method="POST">
                <table style="min-width: 50%">
                    <tr>
                        <td>Imagen</td>
                        <td>
                            <?php
                            if ($Imagen != "") {
                                echo "<img src='" . $Imagen . "' onclick='return false;' style='width: 100px; height:100px;'/>";
                            }
                            ?>
                            <br/>
                            <input type="file" name="logo" id="logo" class="invalid maxSize" data-max-size='200kb' data-type='image'/>
                            <span class="error_file" style="color: red; background: #FDD9DB;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="nombre" name="nombre" value="<?php echo $Nombre; ?>"/>                                
                        </td>                        
                    </tr>                    
                    <tr>
                        <td><label for="cliente">Negocio</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="cliente" name="cliente" style="width:200px" class="uniselect"> 
                                <?php
                                $catalogo = new Catalogo();
                                $result = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                echo "<option value=''>Selecciona el cliente</option>";
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($rs['ClaveCliente'] == $ClaveCliente) {
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . " (" . $rs['RFC'] . ")</option>";
                                }
                                ?>
                            </select>
                        </td>                        
                    </tr>                          
                    <tr>
                        <td><label for="descripcion">Descripci&oacute;n</label><span class="obligatorio"> *</span></td>
                        <td>
                            <textarea id="descripcion" name="descripcion" style="width: 300px; resize: none;">
                                <?php echo $Descripcion; ?>
                            </textarea>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="fecha_inicio">Fecha Inicio</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="fecha_inicio" name="fecha_inicio" class="fecha" value="<?php echo $FechaInicio; ?>" style="width:200px"/>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="fecha_fin">Fecha Fin</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="fecha_fin" name="fecha_fin" class="fecha" value="<?php echo $FechaFin; ?>" style="width:200px"/>
                        </td>                        
                    </tr>                                                                                
                    <tr>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $Activo; ?>/>Activo</td>
                    </tr>                    
                </table>
                <fieldset>
                    <legend>Dirección Evento</legend>
                    <table>
                        <tr>
                            <td>Calle<span class="obligatorio"> *</span></td>
                            <td><input type="text" id="calle" name="calle" value="<?php echo $Calle; ?>"/></td>
                            <td>No. Exterior<span class="obligatorio"> *</span></td>
                            <td><input type="text" id="no_exterior" name="no_exterior" value="<?php echo $NoExterior; ?>"/></td>
                            <td>No. Interior</td>
                            <td><input type="text" id="no_interior" name="no_interior" value="<?php echo $NoInterior; ?>"/></td>
                        </tr>
                        <tr>
                            <td>Colonia</td>
                            <td><input type="text" id="colonia" name="colonia" value="<?php echo $Colonia; ?>"/></td>
                            <td>Ciudad<span class="obligatorio"> *</span></td>
                            <td><input type="text" id="ciudad" name="ciudad" value="<?php echo $Ciudad; ?>"/></td>
                            <td>Estado<span class="obligatorio"> *</span></td>
                            <td>
                                <select class="uniselect" id="estado" name="estado">
                                    <?php
                                    $result = $catalogo->getListaAlta("c_ciudades", "Ciudad");
                                    while ($rs = mysql_fetch_array($result)) {
                                        $s = "";
                                        if ($rs['Ciudad'] == $Estado) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value='" . $rs['Ciudad'] . "' $s>" . $rs['Ciudad'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Delegación
                            </td>
                            <td>
                                <input name="delegacion" type="text" maxlength="50" id="delegacion" value="<?php echo $Delegacion; ?>" style="width:200px;" />                                
                            </td>
                            <td >
                                C.P: <span class="obligatorio"> *</span>
                            </td>
                            <td>
                                <input name="codigo_postal" type="text" maxlength="30" id="codigo_postal" value="<?php echo $CodigoPostal; ?>" />                                
                            </td>                            
                        </tr>
                        <tr>
                            <td><label for='latitud'>Latitud</label></td>
                            <td>
                                <input type="text" id='latitud' name='latitud' value='<?php  echo $Latitud; ?>'/>
                            </td>
                            <td><label for='longitud'>Longitud</label></td>
                            <td>
                                <input type="text" id='longitud' name='longitud' value='<?php echo $Longitud; ?>'/>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>'); return false;"/>
                <?php
                echo "<input type='hidden' id='id' name='id' value='" . $IdEvento . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>