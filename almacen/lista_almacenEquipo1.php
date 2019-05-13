<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$parametrosExcel = "";
$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/lista_almacenEquipo.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("Almacen", "No serie", "Equipo", "Tipo Inventario" ,"Ubicacion","Pendiente Retiro","", "", "");
$columnas = array("nombre_almacen", "NoSerie", "Modelo", "tipoInventario" , "Ubicacion" ,"PendienteRetiro","id_bitacora");
$alta = "almacen/alta_almacenEquipo.php";
$editar = "almacen/configuracion.php?regresar=$same_page";

$catalogo = new Catalogo();

/*Obtenemos los almacenes a los que tiene permiso el usuario actual*/
$idAlmacenes = array();
$consultaAlmacen = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us "
    . " WHERE ra.IdUsuario='" . $_SESSION['idUsuario'] . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario 
        ORDER BY a.nombre_almacen ASC";
$queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
if(mysql_num_rows($queryAlmacen) == 0){
    $consultaAlmacen = "SELECT * FROM c_almacen a WHERE a.Activo=1 AND (a.TipoAlmacen = 1 OR a.Surtir = 1) ORDER BY a.nombre_almacen ASC";
    $queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacenes[$rs['id_almacen']] = $rs['nombre_almacen'];
    }
    $id_almacenes = "";
}else{
    while ($rs = mysql_fetch_array($queryAlmacen)) {
        $idAlmacenes[$rs['id_almacen']] = $rs['nombre_almacen'];        
    }
    $id_almacenes = implode(",", array_keys($idAlmacenes));//Id de los almacenes a los que tiene permiso el usuario actual
}

$filtro_responsable_almacen = "";
$array_almacenes = array();
if(isset($_POST['almacenes']) && $_POST['almacenes']!=""){
    $filtro_responsable_almacen = " AND a.id_almacen IN(".$_POST['almacenes'].") ";
    $array_almacenes = explode(",", $_POST['almacenes']);   
    $parametrosExcel .= "?almacenes=".$_POST['almacenes'];
}

$modelo = "";
$filtro_modelo = "";
if(isset($_POST['modelo']) && $_POST['modelo']!=""){
    $modelo = $_POST['modelo'];
    $filtro_modelo = " AND e.Modelo LIKE '%$modelo%' ";
    if($parametrosExcel != ""){
        $parametrosExcel.= "&modelo=".$_POST['modelo'];
    }else{
        $parametrosExcel = "?modelo=".$_POST['modelo'];
    }
}

$serie = "";
$filtro_serie = "";
if(isset($_POST['serie']) && $_POST['serie']!=""){
    $serie = $_POST['serie'];
    $filtro_serie = " AND kae.NoSerie LIKE '%$serie%' ";
    if($parametrosExcel != ""){
        $parametrosExcel.= "&serie=".$_POST['serie'];
    }else{
        $parametrosExcel = "?serie=".$_POST['serie'];
    }
}

if ($id_almacenes != "") {
    $consulta = "SELECT kae.NoSerie, a.nombre_almacen, e.Modelo, b.id_bitacora, ti.Nombre AS tipoInventario, kae.Ubicacion,
    IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'No',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'Si',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'Si',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND a.id_almacen=9,'Si','No')))) AS PendienteRetiro
    FROM k_almacenequipo AS kae
    LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen
    LEFT JOIN c_equipo AS e ON e.NoParte = kae.NoParte
    LEFT JOIN c_bitacora AS b ON b.NoSerie = kae.NoSerie
    LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
    LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
    LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = b.IdTipoInventario
    INNER JOIN k_responsablealmacen AS kra ON kra.IdUsuario = ".$_SESSION['idUsuario']." AND kra.IdAlmacen = a.id_almacen
    WHERE a.Activo = 1 AND e.Activo = 1 AND b.Activo = 1 $filtro_responsable_almacen $filtro_modelo $filtro_serie 
    ORDER BY kae.NoSerie;";
} else {
    $consulta = "SELECT kae.NoSerie, a.nombre_almacen, e.Modelo, b.id_bitacora, ti.Nombre AS tipoInventario, kae.Ubicacion,
    IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'No',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'Si',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'Si',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND a.id_almacen=9,'Si','No')))) AS PendienteRetiro
    FROM k_almacenequipo AS kae
    INNER JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen
    INNER JOIN c_equipo AS e ON e.NoParte = kae.NoParte
    LEFT JOIN c_bitacora AS b ON b.NoSerie = kae.NoSerie
    LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
    LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
    LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = b.IdTipoInventario
    WHERE a.Activo = 1 AND e.Activo = 1 AND b.Activo = 1 $filtro_responsable_almacen $filtro_modelo $filtro_serie 
    ORDER BY kae.NoSerie;";
}
//echo $consulta;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" src="resources/js/funciones.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if($permisos_grid->getAlta()){ ?>
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table style="width: 80%;">
                <tr>                    
                    <td>Almacén</td>
                    <td>
                        <select id="almacen" name="almacen[]" class="multiselect" multiple="multiple">
                            <?php
                                //echo "<option value=''>Todos los almacenes</option>";
                                foreach ($idAlmacenes as $key => $value) {
                                    $s = "";
                                    if(in_array($key, $array_almacenes)){
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='$key' $s>$value</option>";
                                }
                            ?>
                        </select>
                    </td>
                    <td>Modelo</td>
                    <td>
                        <input type="text" id="NoParte" name="NoParte" value="<?php echo $modelo; ?>"/>
                    </td>
                    <td>Serie</td>
                    <td>
                        <input type="text" id="Serie" name="Serie" value="<?php echo $serie; ?>"/>
                    </td>
                    <td>
                        <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Mostrar equipos" class="button" 
                               onclick="mostrarEquipoAlmacen('<?php echo $same_page ?>','almacen','NoParte','Serie');"/>
                    </td>
                </tr>
            </table>
            <br/><br/><br/>
            <?php if(isset($_POST['mostrar'])){  ?>
            <a href="almacen/lista_almacenEquipoXLS.php<?php echo $parametrosExcel; ?>" target="_blank" class="boton"><img src="resources/images/excel.png"></a>
            <br/>
            <table id="tAlmacen" class="tabla_datos">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        //echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        for ($i = 0; $i < count($columnas) - 1; $i++) {
                            echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
                        }
                        ?>
                    <td align='center' scope='row'>  
                        <?php if($permisos_grid->getModificar()){ ?>
                        <a href='#' onclick='editarRegistro("<?php echo $editar; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
                        return false;' title='Editar Registro' >
                            <img src="resources/images/Modify.png"/>
                        </a>
                        <?php } ?>
                    </td>                   
                    <?php
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
            <?php } ?>
        </div>
    </body>
</html>