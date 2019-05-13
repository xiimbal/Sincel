<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['id']) || !isset($_POST['id2']) || !isset($_POST['id3'])) {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PeriodoSinFacturar.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
$pagina_lista = "facturacion/lista_periodosinFacturar.php";

$periodo = $_POST['id'];
$bitacora = $_POST['id2'];
$cliente = $_POST['id3'];

$catalogo = new Catalogo();
$configuracion = new Configuracion();
$cliente_obj = new Cliente();
$periodo_obj = new PeriodoSinFacturar();

$periodo_obj->getRegistroById($_POST['id']);
$configuracion->getRegistroById($_POST['id2']);
$cliente_obj->getRegistroById($_POST['id3']);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_psf.js"></script>        
    </head>
    <body>
        <div class="principal">            
            <form id="formPSF" name="formPSF" action="/" method="POST">
                <table>
                    <tr>
                        <td>No. Serie</td>
                        <td><?php echo $configuracion->getNoSerie(); ?></td>
                        <td>Periodo</td>
                        <td><?php echo substr($catalogo->formatoFechaReportes($periodo_obj->getPeriodo()),5); ?></td>
                        <td>Cliente</td>
                        <td><?php echo $cliente_obj->getNombreRazonSocial() ?></td>
                    </tr>
                </table>
                <table style="min-width: 70%">
                    <tr>
                        <td><label for="comentario">Comentario</label><span class="obligatorio"> *</span></td>
                        <td>
                            <textarea id="comentario" name="comentario"></textarea>
                        </td>                         
                </table>
                <input type="submit" class="boton" value="Marcar como facturado" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                echo "<input type='hidden' id='periodo' name='periodo' value='" . $periodo . "'/> ";
                echo "<input type='hidden' id='bitacora' name='bitacora' value='" . $bitacora . "'/> ";
                echo "<input type='hidden' id='cliente' name='cliente' value='" . $cliente . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>