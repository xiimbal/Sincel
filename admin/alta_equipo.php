<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$pagina_lista = "admin/lista_equipos.php";

$urlImg = "";
$partes = "";
$modelo = "";
$idTipoEquipo = "";
$descripcion = "";
$precio = "";
$meses = "";
$impresiones = "";
$read = "";
$activo = "checked='checked'";
$prefijo = "";
$longitud_serie = "";

$idCaracteristica = "";
$color = "";
$fax = "";
$formatoEquipoA4 = "";
$formatoEquipoA3 = "";
$capacidadDuplex = "";
$velocidad = "";
$ciclo = "";
$capacidad = "";
$resolucion = "";
$peso = "";
$lenguajeImpresion = "";
$listaPart = "";
$guiaOpAv = "";
$espcTecnicas = "";
$guiaOp = "";
$manualServ = "";
$tipoServicio = "";
$bn = "";
$IncluyeToner = "checked='checked'";
?>
<!DOCTYPE html>
<html lang="es">
    <head>     
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_componentes.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_componenteCompatible.js"></script>

        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_equipoTipoComponentes.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_equipo.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
                OcultarCampos();
            });
        </script>
    </head>
    <body>
        <div class="principal">   
            <?php
            $obj = new Equipo();

            if (isset($_POST['id'])) {
                $tipo = new EquipoCaracteristicasFormatoServicio();
                $obj->getRegistroById($_POST['id']);
                if (isset($_POST['copiar']) && $_POST['copiar'] == TRUE)
                    $read = "";
                else {
                    $read = "readonly='readonly'";
                    $partes = $obj->getNoParte();
                }

                $tipo->getRegistroById($_POST['id']);
                $urlImg = "WEB-INF/Controllers/documentos/equipos/" . $obj->getImagen();
                $modelo = $obj->getModelo();
                $descripcion = $obj->getDescripcion();
                $idTipoEquipo = $obj->getIdTipoEquipo();
                $precio = $obj->getPrecio();
                $meses = $obj->getMeses();
                $impresiones = $obj->getImpresiones();
                $capacidadDuplex = $obj->getCapacidadDuplex();
                $lenguajeImpresion = $obj->getPld();
                $velocidad = $obj->getVeocidad();
                $ciclo = $obj->getCiclo();
                $resolucion = $obj->getResolucion();
                $capacidad = $obj->getCapacidadMemoria();
                $peso = $obj->getPesoPapel();
                $prefijo = $obj->getPrefijo();
                $longitud_serie = $obj->getLongitudSerie();
                $listaPart = "WEB-INF/Controllers/documentos/equipos/" . $obj->getListaPartes();
                $guiaOpAv = "WEB-INF/Controllers/documentos/equipos/" . $obj->getGiaOperacionesAvanzadas();
                $espcTecnicas = "WEB-INF/Controllers/documentos/equipos/" . $obj->getEspecificacionTecnica();
                $guiaOp = "WEB-INF/Controllers/documentos/equipos/" . $obj->getOperacion();
                $manualServ = "WEB-INF/Controllers/documentos/equipos/" . $obj->getManualServicion();
                $idCaracteristica = $tipo->getIdCaracteristica();
                if ($tipo->getFormatoEquipo() == "A4")
                    $formatoEquipoA4 = "checked";
                if ($tipo->getFormatoEquipo() == "A3")
                    $formatoEquipoA3 = "checked";


                if ($tipo->getServicioColor() == "Color")
                    $color = "checked='checked'";
                else if ($tipo->getBn() == "B/N")
                    $bn = "checked='checked'";



                if ($tipo->getServicioFax() == "Fax")
                    $fax = "checked='checked'";
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                if ($obj->getIncluyeToner() == "0") {
                    $IncluyeToner = "";
                }
            }
            ?>
            <div id="copiarComponente" name="copiarComponente" style="float: right; margin-right: 5%; ">
                <select id="componenteCopiar" name="componenteCopiar" style="max-width: 250px;">
                    <?php
                    
                    $query = $catalogo->getListaAlta("c_equipo", "Modelo");
                    echo "<option value='0' >Selecciona una opción</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value=\"" . $rs['NoParte'] . "\" >" . $rs['Modelo'] . " / " . $rs['NoParte'] ."</option>";
                    }
                    ?>
                </select>
                <input type="image" src="resources/images/copy.png" onclick="copiarDatos('admin/alta_equipo.php');
                return false;" name="image" width="16" height="16">
            </div><br/><br/>
            <form id="formEquipo" name="formEquipo" action="/" method="POST" enctype="multipart/form-data" class="formulario" >
                <table>
                    <tr>
                        <td>
                            <?php if ($urlImg != "") { ?> <img src="<?php echo $urlImg ?>" id="preview" name="preview" style="max-width: 200px; max-height: 150px;"/>
                            <?php } else { ?><div id="arch0"></div>
                            <?php } ?></td>
                        <td colspan="3">
                            <input type="file" id="imagen" name="imagen" onchange="readURL(this, 'preview');"/>
                        </td>

                    </tr>
                    <tr>
                        <td><label for="partes">* No. de parte</label></td>
                        <td><input type="text" id="partes" name="partes" value="<?php echo $partes ?>" <?php echo $read ?>/></td>
                        <td></td>
                        <td></td>   
                    </tr>
                    <tr>
                        <td><label for="modelo">* Modelo</label></td>
                        <td><input type="text" id="modelo" name="modelo" value="<?php echo $modelo ?>"/></td>
                        <td></td>
                        <td></td>   
                    </tr>
                    <tr>
                        <td><label for="IdTipoEquipo">* Tipo de equipo</label></td>
                        <td>
                            <select name="IdTipoEquipo" id="IdTipoEquipo">
                                <option value=''>Selecciona una opción</option>
                                <?php
                                    $result = $catalogo->getListaAlta("c_tipoequipo", "TipoEquipo");
                                    while($rs = mysql_fetch_array($result)){
                                        $s = ($idTipoEquipo == $rs['IdTipoEquipo'])? "selected" : "";
                                        echo "<option value='".$rs['IdTipoEquipo']."' $s>".$rs['TipoEquipo']."</option>";
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="descripcion">* Descripci&oacute;n</label></td>
                        <td><textarea id="descripcion" name="descripcion" cols="40"><?php echo $descripcion; ?></textarea></td>
                    </tr>
                    <tr>
                        <td><label for="precio">Precio en dólares: </label></td>
                        <td><input type="text" id="precio" name="precio" value="<?php echo $precio; ?>"/></td>
                        <td></td>
                        <td></td>   
                    </tr>
                    <tr>
                        <td><label for="periodoMeses">Periodo de garantia(meses): </label></td>
                        <td><input type="text" id="periodoMeses" name="periodoMeses" value="<?php echo $meses; ?>"/></td>
                        <td><label for="periodoImpresion">Periodo de garantia(impresiones): </label></td>
                        <td><input type="text" id="periodoImpresion" name="periodoImpresion" value="<?php echo $impresiones; ?>"/></td>   
                    </tr>  
                    <tr>
                        <td><label for="prefijo">Prefijo-Serie</label></td>
                        <td><input type="text" id="prefijo" name="prefijo" value="<?php echo $prefijo; ?>"/></td>
                        <td><label for="longitud_serie">Longitud serie</label></td>
                        <td><input type="number" id="longitud_serie" name="longitud_serie" value="<?php echo $longitud_serie; ?>"/></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>
                </table>

                <fieldset>
                    <legend>Característica del equipo</legend>
                    <table>
                        <tr>
                            <td>
                                <label for="caracteristica">Caracteristicas</label>
                                <select id="caracteristica" name="caracteristica"  onchange="OcultarCampos();">
                                    <?php                                    
                                    $query = $catalogo->getListaAlta("c_caracteristicaequipo", "Nombre");
                                    echo "<option value='' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($idCaracteristica != "" && $idCaracteristica == $rs['IdCaracteristicaEquipo']) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['Nombre'] . "' " . $s . ">" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <fieldset>
                                    <legend>* Tipo de servicio</legend>   
                                    <table>
                                        <tr><td>
                                                <input type="radio" name="tiposerv" id="tiposerv" value="1"<?php echo $color; ?>/>Color</td><td>
                                                <input type="radio" name="tiposerv" id="tiposerv" value="3" <?php echo $bn; ?>/>B/N </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div id="ocFax">
                                                    <input type="checkbox" name="fax" id="fax" <?php echo $fax; ?>/>Fax
                                                </div></td>
                                        </tr>
                                    </table>
                                </fieldset>
                            </td>
                            <td>
                                <div id="formato">
                                    <fieldset>
                                        <legend>* Formato equipo</legend>
                                        <input type = "radio" name = "formato1" id = "formato1" <?php echo $formatoEquipoA4; ?> value = "A4" />A4
                                        <br/>
                                        <input type = "radio" name = "formato1" id = "formato1" <?php echo $formatoEquipoA3; ?> value = "A3" />A3
                                    </fieldset>
                                </div>
                            </td>
                            <td rowspan="3">Capacidad duplex<br/><textarea id="duplex" name="duplex" cols="40"><?php echo $capacidadDuplex; ?></textarea></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="checkbox" name="incluyeToner" id="incluyeToner" <?php echo $IncluyeToner; ?>/>Incluye Toner</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><label for="velocidad">Velocidad (páginas por minuto)</label></td>
                            <td><input type="text" id="velocidad" name="velocidad" value="<?php echo $velocidad ?>"/></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><label for="ciclo">Ciclo máximo mensual</label></td>
                            <td><input type="text" id="ciclo" name="ciclo" value="<?php echo $ciclo ?>"/></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><label for="resolucion">Resolución</label></td>
                            <td><input type="text" id="resolucion" name="resolucion" value="<?php echo $resolucion ?>"/></td>
                            <td></td>
                            <td rowspan="3">Lenguaje de impresion(PDL)<textarea id="lenguajeImpr" name="lenguajeImpr" cols="40"><?php echo $lenguajeImpresion; ?></textarea></td>
                        </tr>
                        <tr>
                            <td><label for="capacidad">Capacidad máxima de memoria (MB): </label></td>
                            <td><input type="text" id="capacidad" name="capacidad" value="<?php echo $capacidad ?>"/></td>
                            <td></td>

                        </tr>
                        <tr>
                            <td><label for="peso">Peso max. papel (g/m2): </label></td>
                            <td><input type="text" id="peso" name="peso" value="<?php echo $peso ?>"/></td>
                            <td></td>
                        </tr>
                    </table>

                </fieldset>

                <fieldset>
                    <legend>Archivos PDF</legend>
                    <table>
                        <tr>
                            <td><label for="listaPartes">Lista de partes: </label></td><td colspan="2"><input type="file" id="listaPartes" name="listaPartes"/>
                                <?php if ($guiaOp != "") { ?><br/><a href="<?php echo $listaPart; ?>" target='_blank'><?php echo $rest = substr($listaPart, 39); ?></a>
                                <?php } else { ?>  <br/><label id="arch1"></label>
                                <?php } ?> </td>
                            <td><label for="giaOperacion">Guía de operación: </label></td><td colspan="2"><input type="file" id="giaOperacion" name="giaOperacion"/>
                                <?php if ($guiaOp != "") { ?> <br/><a href="<?php echo $guiaOp; ?>" target='_blank'><?php echo $rest = substr($guiaOp, 39); ?></a>
                                <?php } else { ?> <br/><label id="arch2"></label>
                                <?php } ?></td>

                        </tr>
                        <tr>
                            <td><label for="guiaOpAvanzada">Guía de operación avanzada: </label></td><td colspan="2"><input type="file" id="guiaOpAvanzada" name="guiaOpAvanzada"/>
                                <?php if ($guiaOpAv != "") { ?> <br/><a href="<?php echo $guiaOpAv; ?>" target='_blank'><?php echo $rest = substr($guiaOpAv, 39); ?></a>
                                <?php } else { ?> <br/><label id="arch3"></label>
                                <?php } ?></td>
                            <td><label for="manualServicio">Manual de servicio </label></td><td colspan="2"><input type="file" id="manualServicio" name="manualServicio"/>
                                <?php if ($manualServ != "") { ?><br/><a href="<?php echo $manualServ; ?>" target='_blank'><?php echo $rest = substr($manualServ, 39); ?></a>
                                <?php } else { ?> <br/><label id="arch4"></label>
                                <?php } ?> </td>

                        </tr>
                        <tr>
                            <td><label for="EspecificacionTec">Especificaciones técnicas: </label></td><td colspan="2"><input type="file" id="EspecificacionTec" name="EspecificacionTec"/>
                                <?php if ($espcTecnicas != "") { ?><br/><a href="<?php echo $espcTecnicas; ?>" target='_blank'><?php echo $rest = substr($espcTecnicas, 39); ?></a>
                                <?php } else { ?><br/><label id="arch5"></label>
                                <?php } ?>
                            </td>
                            <td></td><td colspan="2"></td>

                        </tr>
                    </table>                   
                </fieldset>
                <div id="mensajeEquipo">

                </div>
                <div id="editEquipo">                   
                    <div id="partesEquipo" style="width: 100%">

                    </div>
                    <div id="ComponentesCompatibles"  style="width: 100%">

                    </div>
                    <div id="ComponentesEquipo" style="width: 100%">

                    </div>
                    <div id="EquiposSimiliares" style="width: 100%">

                    </div>
                </div>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Terminar" onclick="TerminarEdicion('<?php echo $pagina_lista; ?>');
                return false;"/>
                       <?php
                       echo "<input type='hidden' id='id' name='id' value='$partes'/> ";
                       if (isset($_POST['copiar']) && $_POST['copiar'] == TRUE) {
                           echo "<input type='hidden' id='copiadocompo' name='copiadocompo' value='1'/> ";
                           echo "<input type='hidden' id='copiadoid' name='copiadoid' value='" . $_POST['id'] . "'/> ";
                       }
                       ?>
            </form>
        </div>
    </body>
</html>