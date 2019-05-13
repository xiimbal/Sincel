<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Folio.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
$folio = new Folio();
if (isset($_GET['id']) && $_GET['id'] != "") {
    $folio->setId_folio($_GET['id']);
    $folio->getRegistrobyID();
}else if(isset($_GET['rfc']) && $_GET['rfc'] != ""){
    $folio->setRFCemisor($_GET['rfc']);
    $folio->getRegistrobyRFC();
}
//echo $_GET['rfc'];
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/Alta_folio.js"></script>
<form id="formfolio">
    <fieldset>
        <table style=" width:100%">            
            <tr>
                <td>RFC Emisor:</td>
                <td>
                    
                    <select name="RFCemisor" id="RFCemisor"  style="width:200px;">
                        <?php
                            $consulta = "SELECT RFC, RazonSocial FROM `c_datosfacturacionempresa` WHERE Activo = 1 ORDER BY RFC;";
                            $catalogo = new CatalogoFacturacion();
                            $result = $catalogo->obtenerLista($consulta);
                            while($rs = mysql_fetch_array($result)){
                                $s = "";
                                if($rs['RFC'] == $folio->getRFCemisor()){
                                    $s = "selected = 'selected'";
                                }else{
                                    continue;
                                }
                                echo "<option value='".$rs['RFC']."' $s>".$rs['RFC']." / ".$rs['RazonSocial']."</option>";
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Folio Inicial:</td>
                <td><input name="folioInicial" type="text" maxlength="150" id="folioInicial"  style="width:200px;" value="<?php echo $folio->getFolioInicial() ?>"/></td>
            </tr>
            <tr>
                <td>Folio Final:</td>
                <td><input name="folioFinal" type="text" maxlength="150" id="folioFinal"  style="width:200px;" value="<?php echo $folio->getFolioFinal() ?>"/></td>
            </tr>
            <tr>
                <td>Serie:</td>
                <td><input name="serie" type="text" maxlength="150" id="serie"  style="width:200px;" value="<?php echo $folio->getSerie() ?>"/></td>
            </tr>
            <tr>
                <td>No Aprobacion:</td>
                <td><input name="noAprobacion" type="text" maxlength="150" id="noAprobacion"  style="width:200px;" value="<?php echo $folio->getNoAprobacion() ?>"/></td>
            </tr>
            <tr>
                <td>Ano Aprobacion:</td>
                <td><input name="anioAprobacion" type="text" maxlength="150" id="anioAprobacion"  style="width:200px;" value="<?php echo $folio->getAnioAprobacion() ?>"/></td>
            </tr>
            <tr>
                <td>Ultimo Folio:</td>
                <td><input name="ultimoFolio" type="text" maxlength="150" id="ultimoFolio"  style="width:200px;" value="<?php echo $folio->getUltimoFolio() ?>"/></td>
            </tr>
            <tr>
                <td>Canceladas:</td>
                <td><input name="canceladas" type="text" maxlength="150" id="canceladas"  style="width:200px;" value="<?php echo $folio->getCanceladas() ?>" readonly="readonly"/></td>
            </tr>
            <tr>
                <td>Activo:</td>
                <td><input name="activo" type="checkbox" id="activo" value="1" <?php
                    if ($folio->getActivo() == 1) {
                        echo "checked";
                    }
                    ?>/></td>
            </tr>
        </table>
    </fieldset>
    <?php if ($folio->getId_folio() != NULL && $folio->getId_folio() != "") {
        ?>
        <input type="hidden" id="id" name="id" value="<?php echo $folio->getId_folio(); ?>"/>
<?php }
?>
    <br />
    <table style=" width:100%; text-align:center">
        <tr>
            <td>
                <input type="submit" class="boton" name="Guardar" value="Guardar"  id="Guardar" />
            </td>
            <td>
                <input type="button" onclick="cambiarContenidos('Multi/list_empresas.php', 'Folios');
                        return false;" class="boton" name="Cancelar" value="Cancelar" id="Cancelar" />
            </td>
        </tr>
    </table>
</form>