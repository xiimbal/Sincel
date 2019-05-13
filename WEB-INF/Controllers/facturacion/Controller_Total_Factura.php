<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/EnLetras.class.php");
$catalogo = new Catalogo();
$result = $catalogo->obtenerLista("SELECT c.idConcepto,c.Cantidad,c.Descripcion,c.Unidad,c.PrecioUnitario,c.Descuento,c.Porcentaje,f.Descuentos
FROM c_factura AS f
INNER JOIN c_conceptos AS c ON c.idFactura=f.IdFactura
WHERE f.IdFactura=" . $_POST['id']);
$total = 0;
$descuento = 0;
$costo_descuento_partida = 0;
while ($rs = mysql_fetch_array($result)) {
    $total = $total + ($rs['Cantidad'] * $rs['PrecioUnitario']);
    $descuento = $rs['Descuentos'];
    if (isset($rs['Descuento']) && !empty($rs['Descuento'])) {        
        if ($rs['Porcentaje'] == "1") {//Si se maneja porcentaje
            $costo_descuento = number_format(($rs['Cantidad'] * $rs['PrecioUnitario']) * ($rs['Descuento'] / 100), 2, ".", "");
        } else {
            $costo_descuento = number_format($rs['Descuento'], 2, ".", "");
        }
        $costo_descuento_partida += $costo_descuento;
    }
}
?>
<table style=" width:100%">
    <?php    
    $total_descuento = number_format((($descuento / 100) * $total) + $costo_descuento_partida, 2, ".", "");
    $Suma_iva = ($total - $total_descuento) * 0.16;
    if (!empty($descuento) || !empty($costo_descuento_partida)) {
        echo '<tr>
                <td class="style2"  style="text-align:left;">

                </td>
                <td class="style3">Descuento:</td>
                <td class="style3">$</td>
                <td class="style3" style="text-align:right;
                    ">
                    <span id="MainContent_lblIVA">' . number_format($total_descuento, 2) . '</span>
                </td>
            </tr>';
    }
    ?>
    <tr>
        <td class="Etiquetas"  style="width: 750px;text-align:left;">
            <span id="MainContent_lblTotalLetra">(<?php
                $total = $total - $total_descuento;
                $letras = new EnLetras();
                echo $letras->ValorEnLetras($total + $Suma_iva, "PESOS ")
                ?>  )</span>
        </td>
        <td class="Etiquetas">Subtotal:</td>
        <td class="Etiquetas">$</td>
        <td class="Etiquetas" style=" width:100px;text-align:right;">
            <span id="MainContent_lblSubtotal"><?php echo number_format($total, 2) ?></span>
        </td>
    </tr>                                    
    <tr>
        <td class="style2"  style="text-align:left;">

        </td>
        <td class="style3">IVA 16%:</td>
        <td class="style3">$</td>
        <td class="style3" style="text-align:right;
            ">
            <span id="MainContent_lblIVA"><?php echo number_format($Suma_iva, 2) ?></span>
        </td>
    </tr>
    <tr>
        <td class="Etiquetas" style="width: 700px;text-align:left;">
        </td>
        <td class="Etiquetas">Total:</td>
        <td class="Etiquetas">$</td>
        <td class="Etiquetas" style="text-align:right;">
            <span id="MainContent_lblTotal"><?php echo number_format($total + $Suma_iva, 2) ?></span>
        </td>
    </tr>
</table>