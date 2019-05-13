<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/equipos_sin_identificar.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("No. Serie", "Modelo", "Fecha ingreso" , "Causa","Usuario","Origen", "Reporte movimiento");

$NoSerie = "";
$Modelo = "";
$Where = " where (`kae`.`id_almacen` = 9) ";
if(isset($_POST['NoSerie']) && $_POST['NoSerie']!=""){
    $NoSerie = $_POST['NoSerie'];
    if($Where == ""){
        $Where = " WHERE kae.NoSerie = '$NoSerie' ";
    }else{
        $Where .= " AND kae.NoSerie = '$NoSerie' ";
    }
}

if(isset($_POST['Modelo']) && $_POST['Modelo']!=""){
    $Modelo = $_POST['Modelo'];
    if($Where == ""){
        $Where = " WHERE e.Modelo LIKE '%$Modelo%' ";
    }else{
        $Where .= " AND e.Modelo LIKE '%$Modelo%' ";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">            
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="NoSerieConfi">Serie</label>
                    <input class="form-control" type="text" id="NoSerieConfi" name="NoSerieConfi" value="<?php echo $NoSerie; ?>"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="ModeloConfi">Modelo</label>
                    <input class="form-control" type="text" id="ModeloConfi" name="ModeloConfi" value="<?php echo $Modelo; ?>"/>
                </div>
                <div class="form-group col-md-4 p-4">
                    <input type="button" id="ver_equiposConfi" name="ver_equiposConfi" value="Mostrar equipos" class="button btn btn-success btn-block"  onclick="mostrarEquiposConfiguracion('<?php echo $same_page; ?>','NoSerieConfi','ModeloConfi')"/>
                </div>
            </div>
            <?php if (isset($_POST['mostrar'])) { ?>
                <style>
                    .fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-tl.ui-corner-tr.ui-helper-clearfix{
                        min-width: 452px;
                    }
                    .fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-bl.ui-corner-br.ui-helper-clearfix{
                        min-width: 452px;
                    }
                </style>
                <div class="table-responsive">
                    <table id="tAlmacen" class="tabla_datos w-100">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) ); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }                                             
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("select `kae`.`NoSerie` AS `NoSerie`,`e`.`Modelo` AS `Modelo`,`mov`.`id_movimientos` AS `id_movimientos`,
                        `mov`.`causa_movimiento` AS `causa_movimiento`,`mov`.`UsuarioCreacion` AS `UsuarioCreacion`,
                        (case when (`a1`.`id_almacen` is not null) then `a1`.`nombre_almacen` else concat(`c1`.`NombreRazonSocial`,' - ',`cc1`.`Nombre`) end) AS `origen`,
                        `rm`.`id_reportes` AS `id_reportes`, (CASE WHEN !ISNULL(mov.id_movimientos) THEN mov.Fecha ELSE kae.Fecha_ingreso END) AS Fecha_ingreso
                        from ((((((`k_almacenequipo` `kae` left join `movimientos_equipo` `mov` on((`mov`.`id_movimientos` = kae.id_movimiento )))
                        left join `reportes_movimientos` `rm` on((`rm`.`id_movimientos` = `mov`.`id_movimientos`)))
                        left join `c_almacen` `a1` on((`mov`.`almacen_anterior` = `a1`.`id_almacen`)))
                        left join `c_cliente` `c1` on((`mov`.`clave_cliente_anterior` = `c1`.`ClaveCliente`)))
                        left join `c_centrocosto` `cc1` on((`mov`.`clave_centro_costo_anterior` = `cc1`.`ClaveCentroCosto`)))
                        left join `c_equipo` `e` on((`e`.`NoParte` = `kae`.`NoParte`)))
                         where (`kae`.`id_almacen` = 9)  order by `kae`.`NoSerie`,`e`.`Modelo`,`mov`.`id_movimientos`;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";                        
                        echo "<td align='center' scope='row'><a href='#' onclick='cambiarContenidos(\"almacen/alta_bitacora.php?consulta_tiquet=almacen/equipos_sin_identificar.php&NoSerie=".$rs['NoSerie']."\"); return false;'>" . $rs['NoSerie'] . "</a></td>";
                        echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                        echo "<td align='center' scope='row' style='width: 12%;'>" . $rs['Fecha_ingreso'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['causa_movimiento'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['UsuarioCreacion'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['origen'] . "</td>";
                        echo "<td align='center' scope='row'><a target='_blank' 
                            href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=".$rs['id_reportes']."'>" . $rs['id_reportes'] . "</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </body>
</html>