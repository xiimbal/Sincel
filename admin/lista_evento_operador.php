<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_evento_operador.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controller_EventoOperador.php";
$cabeceras = array("Id", "Fecha/Hora", "Registra", "LN", "Evento", "Operador", "Dato", "Servicio", "", "");
$urlextra = "";
$alta = "admin/alta_evento_operador.php";
$catalogo = new Catalogo();
$where = "";
$idOperador = "";
if (isset($_POST['OperadorFiltro']) && $_POST['OperadorFiltro'] != "" && $_POST['OperadorFiltro'] != 0) {
    $idOperador = $_POST['OperadorFiltro'];
    if ($where == NULL) {
        $where = " WHERE cb.IdUsuario=$idOperador ";
        $urlextra.="?IdOperador=" . $idOperador;
    } else {
        $where .= " AND cb.IdUsuario=$idOperador ";
        $urlextra.="&IdOperador=" . idOperador;
    }
}

$idLN = "";
if (isset($_POST['LNFiltro']) && $_POST['LNFiltro'] != "" && $_POST['LNFiltro'] != 0) {
    $idLN = $_POST['LNFiltro'];
    if ($where == NULL) {
        $where = " WHERE cb.IdLineaNegocio=$idLN ";
        $urlextra.="?IdLN=" . $idLN;
    } else {
        $where .= " AND cb.IdLineaNegocio=$idLN ";
        $urlextra.="&IdLN=" . $idLN;
    }
}

$Fecha = "";
if (isset($_POST['FechaFiltro']) && $_POST['FechaFiltro'] != "") {
    $Fecha = $_POST['FechaFiltro'];
    if ($where === "") {
        $where = " WHERE DATE (cb.FechaHora)='" . $Fecha . "' ";
        $urlextra.="?Fecha=" . $Fecha;
    } else {
        $where .= " AND DATE (cb.FechaHora)='" . $Fecha . "' ";
        $urlextra.="&Fecha=" . $Fecha;
    }
}

$idEvento = "";
if (isset($_POST['EventoFiltro']) && $_POST['EventoFiltro'] != "" && $_POST['EventoFiltro'] != 0) {
    $idEvento = $_POST['EventoFiltro'];
    if ($where === "") {
        $where = " WHERE cb.IdEvento=" . $idEvento . " ";
        $urlextra.="?IdEvento=" . $idEvento;
    } else {
        $where .= " AND cb.IdEvento=" . $idEvento . " ";
        $urlextra.="&IdEvento=" . $idEvento;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">   
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
    </head>

    <body>
        <div class="principal">
          <div class="container-fluid">   
            <div class="form-row">
                   
                <div class="form-group col-md-4">
                    <label>Operador</label>
                        <select multiple class="form-control" id="operador" name="operador" class="filtro">
                            <?php
                            $query = $catalogo->getListaAlta("c_usuario", "Nombre");
                            echo "<option value='0' >Selecciona un operador</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if ($idOperador != "" && $idOperador == $rs['IdUsuario']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['IdUsuario'] . "-" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

               <div class="form-group col-md-4">
                        <label for="ln">Linea de Negocio</label>
                        <select multiple class="form-control" class="filtro" id="ln" name="ln" >
                            <?php
                            $query = $catalogo->getListaAlta("c_tipocliente", "Orden");
                            echo "<option value='0' >Selecciona una opción</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if ($idLN != "" && $idLN == $rs['IdTipoCliente']) {
                                    $s = "selected";
                                }
                                echo "<option value=" . $rs['IdTipoCliente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

               <div class="form-group col-md-4">
                    <label>Fecha:</label>
                    <input class="fecha  form-control" type="text" id="fecha" name="fecha" value="<?php echo $Fecha; ?>"/>

                 </div> 
                           
                    <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Mostrar" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3"  onclick="mostrarBitacora('<?php echo $same_page ?>', 'operador', 'ln', 'fecha', 'evento');"/>
              


                <div class="form-group col-md-4">
                    <label>Evento</label>
                            <select class="form-control"  id="evento" name="evento"  >
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 WHERE e.IdArea=101 OR e.IdArea=102 OR e.IdArea=103 ORDER BY e.Nombre;");
                                echo "<option value=0>Selecciona un evento</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($rs['IdEstado'] == "2") {
                                        continue;
                                    }
                                    $s = "";
                                    if ($idEvento != "" && $idEvento == $rs['IdEstado']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                        </div>
                 </div>
            </div>              
          <div class="form-group col-md-12">
                <a href="admin/ReporteBitacora.php<?php echo $urlextra ?>" target="_blank" class="button btn btn-lg btn-block btn-outline-secondary mt-3 mb-3">Generar excel</a> 
            </div>
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos table-responsive">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT cb.*, ce.Nombre AS Evento, ct.Nombre AS NombreLN, CONCAT(cu.Loggin,' ',cu.Nombre,' ',cu.ApellidoPaterno) AS Usuario FROM c_bitacora_operador AS cb LEFT JOIN c_estado AS ce ON ce.IdEstado=cb.IdEvento 
                                                      LEFT JOIN c_usuario AS cu ON cu.IdUsuario=cb.IdUsuario LEFT JOIN c_tipocliente AS ct ON cb.IdLineaNegocio=ct.IdTipoCliente $where;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['IdBitacora'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["FechaHora"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["UsuarioUltimaModificacion"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["NombreLN"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Evento'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Usuario'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Dato'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Servicio'] . "</td>";
                        ?>
                    <td align='center' scope='row'>
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs['IdBitacora']; ?>");
                                    return false;' title='Editar Registro' >
                                <img src="resources/images/Modify.png"/>
                            </a>
                        <?php } ?>
                    </td>
                    <?php
                    //if ($_SESSION['idUsuario'] != $rs[$columnas[count($columnas) - 1]]) {
                    ?>
                    <td align='center' scope='row'> 
                        <?php if ($permisos_grid->getBaja()) { ?>
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs['IdBitacora']; ?>", "<?php echo $same_page; ?>");
                                    return false;'><img src="resources/images/Erase.png"/></a> 
                           <?php } ?>
                    </td>                                        
                    <?php
                    //} else {
                    //    echo "<td></td>";
                    //}
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>

        </div>
    </body>
</html>