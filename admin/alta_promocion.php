<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Promocion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_promocion.php";

$IdPromocion = '';
$ClaveCliente = '';
$Descripcion = '';
$Vigencia = '';
$Telefono = '';
$CodigoPromocion = '';
$IdGiro = '';
$Activo = "";
$Titulo = '';
$Localidad = '';
$IdUsuario = '';
$Vigencia_Fin = '';
$ManejaCupon = "checked='checked'";
$NumeroCupones = '';
$CuponesUsados = 0;
$Imagen = '';
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_promocion.js"></script>       
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Promocion();
                if ($obj->getRegistroById($_POST['id'])) {
                    $IdPromocion = $obj->getIdPromocion();
                    $ClaveCliente = $obj->getClaveCliente();
                    $Descripcion = $obj->getDescripcion();
                    $Vigencia = $obj->getVigencia();
                    $Telefono = $obj->getTelefono();
                    $CodigoPromocion = $obj->getCodigoPromocion();
                    $IdGiro = $obj->getIdGiro();
                    $Titulo = $obj->getTitulo();
                    $Localidad = $obj->getLocalidad();
                    $IdUsuario = $obj->getIdUsuario();
                    $Vigencia_Fin = $obj->getVigencia_Fin();
                    if($obj->getManejaCupon() == "0"){
                        $ManejaCupon = "";
                        $NumeroCupones = 0;
                        $CuponesUsados = 0;
                    }else{
                        $NumeroCupones = $obj->getNumeroCupones();
                        $CuponesUsados = $obj->getCuponesUsados();
                    }                    
                    $Imagen = $obj->getImagen();
                }
            }
            ?>
            <form id="formPromocion" name="formPromocion" action="/" method="POST">
                <table style="min-width: 50%">
                    <tr>
                        <td>Logo</td>
                        <td>
                            <?php                                
                                if($Imagen != ""){
                                    echo "<img src='resources/images/promociones/".$Imagen."' onclick='return false;' style='width: 100px; height:100px;'/>";
                                }
                            ?>
                            <br/>
                            <input type="file" name="logo" id="logo" class="invalid maxSize" data-max-size='17kb' data-type='image'/>
                            <span class="error_file" style="color: red; background: #FDD9DB;"></span>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="titulo">T&iacute;tulo</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="titulo" name="titulo" value="<?php echo $Titulo; ?>"/>                                
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="localidad">Localidad</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="localidad" name="localidad" value="<?php echo $Localidad; ?>"/>                                
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="contacto">Contacto</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="contacto" name="contacto" style="width:200px" class="uniselect" onchange="cargarNegociosDeUsuario('contacto', 'cliente');"> 
                            <?php
                            $catalogo = new Catalogo();
                            $consulta = "SELECT u.IdUsuario, u.Loggin, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Nombre
                                FROM `k_usuarionegocio` AS kun
                                LEFT JOIN c_usuario AS u ON u.IdUsuario = kun.IdUsuario
                                LEFT JOIN c_cliente AS c ON kun.ClaveCliente = c.ClaveCliente
                                WHERE u.Activo = 1 AND c.Activo = 1
                                GROUP BY u.IdUsuario;";
                            $result = $catalogo->obtenerLista($consulta);
                            echo "<option value=''>Selecciona un contacto</option>";
                            while ($rs = mysql_fetch_array($result)) {
                                $s = "";
                                if($rs['IdUsuario'] == $IdUsuario){
                                    $s = "selected = 'selected'";
                                }
                                echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Loggin'] . " (" . $rs['Nombre'] . ")</option>";
                            }
                            ?>
                            </select>
                        </td>                        
                    </tr>   
                    <tr>
                        <td><label for="cliente">Negocio</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="cliente" name="cliente" style="width:200px" class="uniselect">      
                                <?php
                                    if($IdUsuario != ""){
                                        $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial
                                            FROM `k_usuarionegocio` AS kun
                                            LEFT JOIN c_usuario AS u ON u.IdUsuario = kun.IdUsuario
                                            LEFT JOIN c_cliente AS c ON kun.ClaveCliente = c.ClaveCliente
                                            WHERE c.Activo = 1 AND u.IdUsuario = $IdUsuario
                                            GROUP BY c.ClaveCliente;";
                                        $result = $catalogo->obtenerLista($consulta);
                                        while($rs = mysql_fetch_array($result)){
                                            $s = "";
                                            if($rs['ClaveCliente'] == $ClaveCliente){
                                                $s = "selected = 'selected'";
                                            }
                                            echo "<option value=\"" . $rs['ClaveCliente'] . "\" $s>" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                    }
                                ?>
                            </select>                            
                        </td>                        
                    </tr>   
                    <tr>
                        <td><label for="descripcion">Descripci&oacute;n</label></td>
                        <td>
                            <textarea id="descripcion" name="descripcion" style="width: 300px; resize: none;">
                                <?php echo $Descripcion; ?>
                            </textarea>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="vigencia_inicio">Vigencia Inicio</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="vigencia_inicio" name="vigencia_inicio" class="fecha" value="<?php echo $Vigencia; ?>" style="width:200px"/>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="vigencia_fin">Vigencia Fin</label><span class="obligatorio"> *</span></td>
                        <td>
                            <input type="text" id="vigencia_fin" name="vigencia_fin" class="fecha" value="<?php echo $Vigencia_Fin; ?>" style="width:200px"/>
                        </td>                        
                    </tr>                                                            
                    <tr>
                        <td><label for="codigo_promocion">Código Promoción</label></td>
                        <td>
                            <input type="text" id="codigo_promocion" name="codigo_promocion" value="<?php echo $CodigoPromocion; ?>" style="width:200px"/>
                        </td>                        
                    </tr>
                    <tr>
                        <td><input type="checkbox" name="cupon" id="cupon" <?php echo $ManejaCupon; ?>/>Maneja cup&oacute;n</td>
                    </tr>
                    <tr>
                        <td><label for="numero_cupones">Número cupones</label></td>
                        <td>
                            <input type="number" id="numero_cupones" name="numero_cupones" value="<?php echo $NumeroCupones; ?>" style="width:200px"/>
                        </td>                        
                    </tr>
                    <tr>
                        <td><label for="numero_cupones_usados">Cupones usados</label></td>
                        <td>
                            <input type="number" id="numero_cupones_usados" name="numero_cupones_usados" value="<?php echo $CuponesUsados; ?>" style="width:200px" readonly="readonly"/>
                        </td>                        
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>
<?php
echo "<input type='hidden' id='id' name='id' value='" . $IdPromocion . "'/> ";
?>
            </form>
        </div>
    </body>
</html>