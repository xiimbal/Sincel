<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/MovimientoBancario.class.php");

$mov = new MovimientoBancario();
if (isset($_GET['id']) && $_GET['id']) {
    $mov->getRegistroById($_GET['id']);
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/banco/editar_movimiento.js"></script>
<form id="formMovimientoBancario">
    <table style="width: 70%;">
        <tr>
            <td>Factura</td>
            <td><input type="text" name="factura" id="factura" value="<?php echo $mov->getFactura(); ?>"/></td>
            <td>Tipo</td>
            <td><input type="text" name="tipo" id="tipo" value="<?php echo $mov->getTipo(); ?>"/></td>
            <td>Total</td>
            <td><input type="text" name="total" id="total" value="<?php echo $mov->getTotal(); ?>"/></td>
        </tr>
        <tr>
            <td>No. Cuenta</td>
            <td>
                <div id="noCuenta" name="noCuenta">
                <select id="noCuenta" name="noCuenta" style="max-width: 250px;">
                    <?php
                    $equipos = new Catalogo();
                    $query = $equipos->getListaAlta("c_cuentaBancaria", "noCuenta");
                    echo "<option value='0' >Selecciona una opción</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        if(strcmp(trim($rs['idCuentaBancaria']),trim($mov->getNoCuenta())) == 0)
                        {
                            echo "<option value=\"" . $rs['idCuentaBancaria'] . "\" selected=\"selected\" >" . $rs['noCuenta'] ."</option>";
                        }else
                        {
                            echo "<option value=\"" . $rs['idCuentaBancaria'] . "\" >" . $rs['noCuenta'] ."</option>";                     
                        }
                    }
                    ?>
                </select></div>
            </td>
            <td>Banco</td>
            <td>
                <div id="copiarComponente" name="copiarComponente">
                <select id="componenteCopiar" name="componenteCopiar" style="max-width: 250px;">
                    <?php
                    $equipos = new Catalogo();
                    $query = $equipos->getListaAlta("c_banco", "Nombre");
                    echo "<option value='0' >Selecciona una opción</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        if(strcmp(trim($rs['Nombre']),trim($mov->getIdBanco())) == 0)
                        {
                            echo "<option value=\"" . $rs['IdBanco'] . "\" selected=\"selected\" >" . $rs['Nombre'] ."</option>";
                        }else
                        {
                            echo "<option value=\"" . $rs['IdBanco'] . "\" >" . $rs['Nombre'] ."</option>";                     
                        }
                    }
                    ?>
                </select></div>
            </td>
            <td>Fecha</td>
            <td><input type="text" name="fecha" id="fecha" class="fecha" value="<?php echo $mov->getFecha(); ?>"/></td>
        </tr>
        <tr>   
            <td>Referencia</td>
            <td><input type="text" name="referencia" id="referencia" value="<?php echo $mov->getReferencia(); ?>"/></td>
            <td>Comentario</td>
            <td><input type="text" name="comentario" id="comentario" value="<?php echo $mov->getComentario(); ?>"/></td>
        </tr>
    </table>
    <?php
    if (isset($_GET['id']) && $_GET['id']) {
        ?>
        <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>"/>
    <?php } ?>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('Bancos/lista_movimientos.php', 'Movimientos Bancarios');"/>
</form>

