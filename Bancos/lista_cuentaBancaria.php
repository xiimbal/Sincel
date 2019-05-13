<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "Bancos/lista_cuentaBancaria.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("Banco", "No. Cuenta", "Tipo de Cuenta", "RFC", "Clave", "Sucursal", "Ejecutivo de cuenta", "Tel. ejecutivo", "Correo ejecutivo", "Corte" , "Activo" ,"Descripcion", "", "");
$alta = "Bancos/alta_cuentaBancaria.php";
?>
<!DOCTYPE html>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/lista_cuentaBancaria.js"></script>
<link rel="stylesheet" href="css/fontawesome/all.min.css">

<div class="table-responsive-lg table-responsive-md table-responsive-sm" style="overflow: auto;">
    <?php if ($permisos_grid->getAlta()) { ?>
    <button class="btn btn-success" title="Nuevo" onclick='cambiarContenidos("Bancos/alta_cuentaBancaria.php", "Nueva Cuenta");' style="float: right; cursor: pointer;"><i class="fal fa-plus-circle"></i></button>
        <!--img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Bancos/alta_cuentaBancaria.php", "Nueva Cuenta");' style="float: right; cursor: pointer;" /-->  
    <?php } ?>
    <!--script>
        let tableElement = document.getElementById('tCuentaBancaria');
        let tableHeader = document.querySelector('.fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-tl.ui-corner-tr.ui-helper-clearfix');

        window.addEventListener('resize', e => {
            let tableSize = tableElement.getBoundingClientRect();
            tableHeader.style.width = tableSize.width;
        });
    </script-->
    <div class="dataTables_wrapper">
        <table  id="tCuentaBancaria" class="table-responsive dataTable">
        <thead class="thead-dark">
            <tr>
                <?php
                for ($i = 0; $i < (count($cabeceras)); $i++) {
                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                }
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            /* Inicializamos la clase */
            $catalogo = new Catalogo();
            $query = $catalogo->obtenerLista("SELECT c.*, b.Nombre, IF(c.Activo=1,'Activo','Inactivo') AS ActivoT from c_cuentaBancaria c LEFT JOIN c_banco b ON c.IdBanco = b.idBanco;");
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";
                echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['noCuenta'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['tipoCuenta'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['RFC'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['clave'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['sucursal'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ejecutivoCuenta'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['telEjecutivo'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['correoEjecutivo'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['FechaCorte'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['ActivoT'] . "</td>";
                echo "<td align='center' scope='row'>" . $rs['descripcion'] . "</td>";                       
                if ($permisos_grid->getModificar()) {
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"cambiarContenidos('Bancos/alta_cuentaBancaria.php?id=" . $rs['idCuentaBancaria'] . "', 'Editar Cuenta');
                            return false;\" title='Editar'><i class='fal fa-pencil' style='font-size:1.5rem;color:peru;'></i></a></td>";
                } else {
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
                }
                if ($permisos_grid->getBaja()) {
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"eliminarcuentaBancaria('".$rs['idCuentaBancaria']."');
                            return false;\" title='Eliminar'><i class='far fa-trash' style='font-size:1.5rem;color:red;'></i></a></td>";
                } else {
                    echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
                }
            echo "</tr>";
        }
        ?>
        </tbody>
        </table>
    </div>
</div>