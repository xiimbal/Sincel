<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "catalogos/lista_equiposComputo.php";

$urlImg = "";
$partes = "";
$modelo = "";
$descripcion = "";
$precio = "";
$meses = "";
$activo = "checked='checked'";
  
$procesador = "";
$RAM = "";
$HD = "";
$sistemaOperativo = "";
$resolucionPantalla = "";
$tamanoPulgadas = "";
$HDMI = "";
$DVD = "";
$USB = "";
$WIFI = "";
$idiomaSO = "";

?>
<!DOCTYPE html>
<html lang="es">
    <head>     
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_componentes.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_componenteCompatible.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_equipoTipoComponentes.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_equipoComputo.js"></script>
    </head>
    <body>
        <div class="principal">   
        <?php
            $obj = new Equipo();

            if (isset($_POST['id'])) {
                $obj->getRegistroById($_POST['id']);
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                $partes = $obj->getNoParte();
                $modelo = $obj->getModelo();
                $descripcion = $obj->getDescripcion();
                $precio = $obj->getPrecio();
                $meses = $obj->getMeses();
                $urlImg = "WEB-INF/Controllers/documentos/equipos/" . $obj->getImagen();
                
                $procesador = $obj->getProcesador();
                $RAM = $obj->getRAM();
                $HD = $obj->getHD();
                $sistemaOperativo = $obj->getSistemaOperativo();
                $resolucionPantalla = $obj->getResolucionPantalla();
                $tamanoPulgadas = $obj->getTamanoPulgadas();
                $HDMI = $obj->getHDMI();
                $DVD = $obj->getDVD();
                $USB = $obj->getUSB();
                $WIFI = $obj->getWIFI();
                $idiomaSO = $obj->getIdiomaSO();
            }
        ?>
        <form id="formEquipo" name="formEquipo" action="/" method="POST" enctype="multipart/form-data" class="formulario" >
        <table>
            <tr>
                <td><label for="partes">No. de parte <div class="obligatorio">*</div> </label></td>
                <td><input type="text" id="partes" name="partes" value="<?php echo $partes ?>" <?php echo $read ?>/></td>
                <td></td>
                <td></td>   
            </tr>
            <tr>
                <td><label for="modelo">Modelo <div class="obligatorio">*</div></label></td>
                <td><input type="text" id="modelo" name="modelo" value="<?php echo $modelo ?>"/></td>
                <td></td>
                <td></td>   
            </tr>
            <tr>
                <td><label for="descripcion">Descripci&oacute;n<div class="obligatorio">*</div></label></td>
                <td><textarea id="descripcion" name="descripcion" cols="40"><?php echo $descripcion; ?></textarea></td>
            </tr>
            <tr>
                <td><label for="precio">Precio en dólares: </label></td>
                <td><input type="text" id="precio" name="precio" value="<?php echo $precio; ?>"/></td>
                <td></td>
                <td></td>   
            </tr>
            <tr>
                <td><label for="periodoMeses">Periodo de garantia(meses): <div class="obligatorio">*</div></label></td>
                <td><input type="text" id="periodoMeses" name="periodoMeses" value="<?php echo $meses; ?>"/></td>
                <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
            </tr>
        </table>
        <fieldset>
            <legend>Característica del equipo</legend>
            <table>
                <tr>
                    <td>Procesador: <div class="obligatorio">*</div></td>
                    <td><input type="text" name="procesador" id="procesador" value="<?php echo $procesador; ?>"/></td>
                    <td>Capacidad Disco duro (GB) <div class="obligatorio">*</div></td>
                    <td><input type="number" name="hd" id="hd" value="<?php echo $HD; ?>" step="any"/></td>
                    <td>Idioma Sistema Operativo</td>
                    <td><input type="text" name="idiomaSO" id="idiomaSO" value="<?php echo $idiomaSO; ?>"/></td>
                </tr>
                <tr>
                    <td>Memoria RAM: (GB) <div class="obligatorio">*</div></td>
                    <td><input type="numer" name="ram" id="ram" value="<?php echo $RAM; ?>"/></td>
                    <td>Sistema Operativo: </td>
                    <td><input type="text" name="sistemaOperativo" id="sistemaOperativo" value="<?php echo $sistemaOperativo; ?>"/></td>
                </tr>
                <tr>
                    <td>Resolución Pantalla: </td>
                    <td><input type="text" name="resolucion" id="resolucin" value="<?php echo $resolucionPantalla; ?>"/></td>
                    <td>Tamaño de pantalla (pulgadas): </td>
                    <td><input type="number" name="pulgadas" id="pulgadas" value="<?php echo $tamanoPulgadas; ?>"/></td>
                </tr>
                <tr>
                    <?php
                        $sHDMI = "";
                        $sDVD = "";
                        $sUSB = "";
                        $sWIFI = "";
                        if(isset($HDMI) && (int)$HDMI == 1){
                            $sHDMI = "checked";
                        }
                        if(isset($DVD) && (int)$DVD == 1){
                            $sDVD = "checked";
                        }
                        if(isset($USB) && (int)$USB == 1){
                            $sUSB = "checked";
                        }
                        if(isset($WIFI) && (int)$WIFI == 1){
                            $sWIFI = "checked";
                        }
                    ?>
                    <td><input type="checkbox" name="HDMI" id="HDMI" <?php echo $sHDMI; ?>>HDMI</td>
                    <td><input type="checkbox" name="DVD" id="DVD" <?php echo $sDVD; ?>>Lector DVD</td>
                    <td><input type="checkbox" name="USB" id="USB" <?php echo $sUSB; ?>>USB 3.0</td>
                    <td><input type="checkbox" name="WIFI" id="WIFI" <?php echo $sWIFI; ?>>WI-FI</td>
                </tr>
            </table>
        </fieldset>
        <input type="submit" class="boton" value="Guardar" />
        <input type="submit" class="boton" value="Terminar" onclick="TerminarEdicion('<?php echo $pagina_lista; ?>');
        return false;"/>
        <input type='hidden' id='id' name='id' value='<?php echo $partes; ?>'/>
    </form>
    </body>
</html>

