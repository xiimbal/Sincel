<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$catalogo = new Catalogo();
$id = "";

if(isset($_GET['id']) && $_GET['id'] != ""){
    $id = $_GET['id'];
}else{
    echo "Error: La acción no se pudo realizar. Inténtelo de nuevo.";
    return;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="/resources/js/paginas/facturacion/form_validar_viaticos.js"></script>   
    </head>
    <body>
        <div class="principal">
            <form id="frmValidarViaticos" name="frmValidarViaticos" action="/" method="POST" enctype="multipart/form-data">
                <table style="width: 85%">
                    <tr>
                        <th width="15%" align="center" scope="col">Concepto</th>
                        <th width="30%" align="center" scope="col">Descripción</th>
                        <th width="15%" align="center" scope="col">Cantidad original</th>
                        <th width="15%" align="center" scope="col">Cantidad</th>
                        <th width="15%" align="center" scope="col">Comprobante</th>
                        <th width="10%" align="center" scope="col">Validar</th>
                    </tr>
                    <?php
                    $query = "SELECT ve.IdPartida,s.NombreServicio, nt.DiagnosticoSol, ve.cantidad, nt.PathImagen, ve.Validado, ve.CantidadOriginal, nt.IdNotaTicket FROM c_notaticket nt INNER JOIN
                    c_estado e ON e.IdEstado = nt.IdEstatusAtencion INNER JOIN k_serviciove ve ON ve.IdNotaTicket = nt.IdNotaTicket INNER JOIN 
                    c_serviciosve s ON ve.IdServicioVE = s.IdServicioVE WHERE e.FlagValidacion = 1 AND nt.IdTicket = $id";
                    $contador = 0;
                    $result = $catalogo->obtenerLista($query);
                    while($rs = mysql_fetch_array($result)){
                        $disabled = "";
                        $nochange = "";
                        $marcado = "";
                        $imagen = "";
                        $cantidad = "";
                        $cantidadOriginal = "";
                        if($rs['Validado']){
                            $disabled = "readonly";
                            $nochange = "onclick='return false;'";
                            $marcado = "checked";
                        }
                        if (!empty($rs['PathImagen'])) {
                            $imagen = "<a href='" . $rs['PathImagen'] . "' target='_blank'><img src='resources/images/pdf_descarga.png' style='width: 20px'/></a>";
                        }
                        if(!is_float($rs['CantidadOriginal'])){
                            $cantidadOriginal = number_format($rs['CantidadOriginal'], 0);
                        }if(!is_float($rs['cantidad'])){
                            $cantidad = number_format($rs['cantidad']);
                        }
                        echo "<tr>";
                        echo "<td align=\"center\" scope=\"col\"><input type='hidden' id='viatico$contador' name='viatico$contador' value='" . $rs['IdPartida'] . "'>" . $rs['NombreServicio'] . "</td>";
                        echo "<td align=\"center\" scope=\"col\"><input type='hidden' id='nota$contador' name='nota$contador' value='" . $rs['IdNotaTicket'] . "'>" . $rs['DiagnosticoSol'] . "</td>";                        
                        echo "<td align=\"center\" scope=\"col\">$cantidadOriginal</td>";                        
                        echo "<td align=\"center\" scope=\"col\"><input type='text' id='cantidad$contador' name='cantidad$contador' value='$cantidad' $disabled></td>";
                        echo "<td align=\"center\" scope=\"col\"><input type='file' id='archivo$contador' name='archivo$contador'>$imagen</td>";
                        echo "<td align=\"center\" scope=\"col\"><input type='checkbox' id='validacion$contador' name='validacion$contador' $marcado $nochange></td>";
                        echo "</tr>";
                        $contador += 1;
                    }
                    ?>
                </table>
                <input type="hidden" id="ticket" name="ticket" value="<?php echo $id?>">
                <input type="hidden" id="contador" name="contador" value="<?php echo $contador?>">
                <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
                <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('facturacion/validar_viaticos.php', 'Validar viáticos');"/>
            </form>   
        </div>
    </body>
</html>
