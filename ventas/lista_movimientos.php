<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();

include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/lista_movimientos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/menu_movimiento.js"></script>
  <!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet"> 
<br/><br/>
<!--<table style="width: 100%;">    
    <tr>
        <td>Cliente</td>
        <td>
            <select id="cliente" name="cliente" style="width: 200px;" onchange="cargarlocalidades('cliente', 'localidad');" class="filtro">
                <?php
                echo "<option value=''>Todos los clientes</option>";
                $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                while ($rs = mysql_fetch_array($query)) {
                    echo "<option value='" . $rs['ClaveCliente'] . "' >" . $rs['NombreRazonSocial'] . "</option>";
                }
                ?> 
            </select>
        </td>
        <td>Localidad</td>
        <td>
            <select id="localidad" name="localidad" style="width: 200px;" class="filtro">
                <option value="" >Todos las localidades</option>
            </select>
        </td>
        <td>Tipo de movimiento</td>
        <td>
            <select id="tipo" name="tipo" style="width: 200px;" class="filtro">
                <option value="" >Todos tipos de movimiento</option>
                <?php
                $query = $catalogo->obtenerLista("SELECT * FROM c_tipomovimiento");
                while ($rs = mysql_fetch_array($query)) {
                    $s="";
                    if($rs['IdTipoMovimiento']==6){
                        $s="selected";
                    }
                    echo "<option value='" . $rs['IdTipoMovimiento'] . "' $s>" . $rs['Nombre'] . "</option>";
                }?>
            </select>
        </td>
        <td>Retirados</td>
        <td>            
            <input type="checkbox" name="retirado" id="retirado" value="1"/>            
        </td>
    </tr>
    <tr>
        <td>No de reporte</td>
        <td>
            <input type="text" id="NoRep" name="NoRep" />
        </td>
        <td>No de Serie</td>
        <td>
            <input type="text" id="NoSerie" name="NoSerie" />
        </td>
        <td>Fecha inicio</td>
        <td>
            <input type="text" id="fecha1" name="Fecha1" class="fecha" />
        </td>
        <td>Fecha final</td>
        <td>
            <input type="text" id="fecha2" name="Fecha2" class="fecha"/>
        </td>
    </tr>
    </table>
  

    <form action="reportes/reporte_retiros.php" target="_blank" method="POST">
    <table style="margin-left: 80%; width: 19%;">
        <tr>
            <td>
                <input type="button" id="enviar" value="Mostrar" class="boton" onclick="enviardatos(); return false;"/>
            </td>
            <!--<td>
                <input type="image" src="resources/images/icono_impresora.jpg" alt="Reporte retiros" title="Reporte retiros" style="width: 24px; height: 24px;"/>
                <br/>
                Reporte retiros
            </td>-->
        </tr>
    </table>
</form>
</table>
<br/>
<style>
        .ui-multiselect {
            max-width: 200px;
            font-size: 10px;
        }
        .ui-multiselect-checkboxes {
            font-size: 12px;
        }
    </style>
<form action="reportes/reporte_retiros.php" target="_blank" method="POST">
    <div class="container-fluid">

    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="cliente">Cliente</label>
            <select class="form-control" id="cliente" name="cliente" onchange="cargarlocalidades('cliente', 'localidad');" class="filtro">
                <?php
                    echo "<option value=''>Todos los clientes</option>";
                    $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['ClaveCliente'] . "' >" . $rs['NombreRazonSocial'] . "</option>";
                    }
                ?> 
            </select>
        </div>
        <div class="form-group col-md-4">
            <label for="localidad">Localidad</label>
            <select class="form-control" id="localidad" name="localidad"  class="filtro">
                <option value="" >Todos las localidades</option>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label for="tipo">Tipo de movimiento</label>
            <select class="form-control" id="tipo" name="tipo" class="filtro" ">
                <option value="" >Todos tipos de movimiento</option>
                <?php
                    $query = $catalogo->obtenerLista("SELECT * FROM c_tipomovimiento");
                    while ($rs = mysql_fetch_array($query)) {
                        $s="";
                        if($rs['IdTipoMovimiento']==6){
                            $s="selected";
                        }
                        echo "<option value='" . $rs['IdTipoMovimiento'] . "' $s>" . $rs['Nombre'] . "</option>";
                    }
                ?>
            </select>
        </div>
        
         <div class="col-md-4">
        <div class="custom-control custom-checkbox mt-2">
            <input class="custom-control-input" type="checkbox" name="retirado" id="retirado" value="1"> 
            <label class="custom-control-label" for="retirado">Retirados</label> 
        </div>
    </div>
        <!--NoRep-->
        <div class="form-group col-md-4">
            <label for="NoRep">Numero de Reporte</label>
            <input class="form-control" type="text" id="NoRep" name="NoRep"/>
        </div>
        <!--NoSerie-->
        <div class="form-group col-md-4">
            <label for="NoSerie">Numero de Serie</label>
            <input class="form-control" type="text" id="NoSerie" name="NoSerie"/>
        </div>
        <!--Fecha1-->
        <div class="form-group col-md-4">
            <label for="fecha1">Fecha inicio</label>
            <input class="form-control" type="text" id="fecha1" name="Fecha1" class="fecha"/>
        </div>
        <!--Fecha2-->
        <div class="form-group col-md-4">
            <label for="fecha2">Fecha final</label>
            <input class="form-control" type="text" id="fecha2" name="Fecha2" class="fecha"/>
        </div>
        <!--Send-->
        
            <input type="button" id="enviar" value="Mostrar" class="button btn btn-lang btn-block btn-outline-secondary mt-3 mb-3" onclick="enviardatos(); return false;"/>
       
    </div>
</div>
</form>
<div id="tablainfo"></div>

