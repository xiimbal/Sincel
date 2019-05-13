<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$catalogo = new Catalogo();

$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);


$where = "";
$tipoComponFiltro = "";
if(isset($_POST['componente']) && $_POST['componente'] != ""){
    if($_POST['componente'] != 0){
        $tipoComponFiltro = $_POST['componente'];
        if($where == ""){
            $where = " WHERE c.IdTipoComponente = ".$_POST['componente'];
        }else{
            $where .= " AND c.IdTipoComponente = ".$_POST['componente'];
        }
    }
}
$array_almacenes = array();

if(isset($_POST['almacen']) && $_POST['almacen'][0] != ""){

    $array_almacenes = $_POST['almacen'];
    if($where == ""){
        $where = " WHERE a.id_almacen IN(".implode(",",$_POST['almacen']).")";
    }else{
        $where .= " AND a.id_almacen IN(".implode(",",$_POST['almacen']).")";
    }
}else{


}
$enSa = 3;
if(isset($_POST['enSa']) && $_POST['enSa'] != ""){
    /*if($_POST['enSa'] != 3){
        if($where == ""){
            $where = " WHERE mo.Entradada_Salida = ".$_POST['enSa'];
        }else{
            $where .= " AND mo.Entradada_Salida = ".$_POST['enSa'];
        }
    }*/
    $enSa = $_POST['enSa'];
}
$mes = "";
$fecha_movimientos = "";
if(isset($_POST['mes']) && $_POST['mes'] != ""){
    $mes = $_POST['mes'];
   /* if($where == ""){
        $where = " WHERE DATE_FORMAT(mo.Fecha, '%m-%Y') = '".$_POST['mes']."'";
    }else{
        $where .= " AND DATE_FORMAT(mo.Fecha, '%m-%Y') = '".$_POST['mes']."'";
    }*/
    echo "<input type='hidden' id=\"f\" name=\"f\" value=\"DATE_FORMAT(mc.Fecha, '%m-%Y') = '".$_POST['mes']."'\"/>";
    $fecha_movimientos = " AND DATE_FORMAT(m.Fecha, '%m-%Y') = '".$_POST['mes']."'";
}else{
  /*  if($where == ""){
        $where = " WHERE DATE_FORMAT(mo.Fecha, '%m-%Y') = CONCAT(MONTH(DATE_ADD(CURDATE(),INTERVAL -1 MONTH)),'-',DATE_FORMAT(NOW(),'%Y'))";
    }else{
        $where .= " AND DATE_FORMAT(mo.Fecha, '%m-%Y') = CONCAT(MONTH(DATE_ADD(CURDATE(),INTERVAL -1 MONTH)),'-',DATE_FORMAT(NOW(),'%Y'))";
    }*/
    echo "<input type='hidden' id=\"f\" name=\"f\" value=\"DATE_FORMAT(mc.Fecha, '%m-%Y') = CONCAT(MONTH(DATE_ADD(CURDATE(),INTERVAL -1 MONTH)),'-',DATE_FORMAT(NOW(),'%Y')\"/>";
    $fecha_movimientos = " AND DATE_FORMAT(m.Fecha, '%m-%Y') = CONCAT(MONTH(DATE_ADD(CURDATE(),INTERVAL -1 MONTH)),'-',DATE_FORMAT(NOW(),'%Y'))";
}



$consulta2="SELECT k.NoParte,k.id_almacen,k.cantidad_existencia,a.nombre_almacen,c.Modelo,k.CantidadMinima,k.CantidadMaxima,c.Descripcion,
(SELECT SUM(m.CantidadMovimiento) FROM movimiento_componente AS m WHERE m.NoParteComponente = k.NoParte AND m.Entradada_Salida = 0 $fecha_movimientos AND m.IdAlmacenNuevo = k.id_almacen) AS entrada,
(SELECT SUM(m.CantidadMovimiento) FROM movimiento_componente AS m WHERE m.NoParteComponente = k.NoParte AND m.Entradada_Salida = 1 $fecha_movimientos AND m.IdAlmacenAnterior = k.id_almacen) AS salida
FROM k_almacencomponente AS k 
INNER JOIN c_almacen AS a ON a.id_almacen = k.id_almacen
INNER JOIN c_componente AS c ON k.NoParte = c.NoParte
$where ORDER BY a.id_almacen ASC;";

//

//echo $consulta2;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <script type="text/javascript" language="javascript" src="resources/js/paginas/almacen/reporte_entradas_salidas.js"></script>
    </head>
    <body>
        <div class="principal">
            <table width="95%">
                <tr>
                    <td>Tipo de componente</td>
                        <td>
                            <select id="tipoComponente" name="tipoComponente">
                                <option value="0">Todos los componentes</option>
                                <?php
                                $obj1 = new Catalogo();
                                $query1 = $obj1->getListaAlta('c_tipocomponente', 'Nombre');
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($tipoComponFiltro != "" && $tipoComponFiltro == $rs['IdTipoComponente']){
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoComponente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>Almacén</td>
                        <td>
                            <select id="almacen" name="almacen[]" multiple="multiple">

                                <?php
                                    /*Obtenemos los almacenes a los que tiene permiso el usuario actual*/
                                    $idAlmacenes = array();
                                    $consultaAlmacen = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us "
                                        . " WHERE ra.IdUsuario='" . $_SESSION['idUsuario'] . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario 
                                            ORDER BY a.nombre_almacen ASC";
                                    $queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);

                                    if(mysql_num_rows($queryAlmacen) == 0){
                                        $consultaAlmacen = "SELECT * FROM c_almacen a WHERE a.Activo=1 ORDER BY a.nombre_almacen ASC";
                                        $queryAlmacen = $catalogo->obtenerLista($consultaAlmacen);
                                        while ($rs = mysql_fetch_array($queryAlmacen)) {
                                            $s = '';
                                            if(in_array($rs['id_almacen'], $array_almacenes)){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='".$rs['id_almacen']."' ".$s.">".$rs['nombre_almacen']."</option>";
                                        }
                                    }else{
                                        while ($rs = mysql_fetch_array($queryAlmacen)) {
                                            $s = '';
                                            if(in_array($rs['id_almacen'], $array_almacenes)){
                                                $s = "selected='selected'";
                                            }
                                            echo "<option value='".$rs['id_almacen']."'>".$rs['nombre_almacen']."</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                
                </tr>
                <tr>
                        <td>Entrada/Salida</td>
                        <td>
                            <select id="EntradaSalida" name="EntradaSalida">
                                <?php
                                if($enSa == 3){
                                 echo '<option value="3" selected="selected">Todos</option>';   
                                }else{
                                 echo '<option value="3">Todos</option>';   
                                }
                                
                                if($enSa == 0){
                                 echo '<option value="0" selected="selected">Entradas</option>';  
                                }else{
                                 echo '<option value="0">Entradas</option>';   
                                }
                                
                                if($enSa == 1){
                                 echo '<option value="1" selected="selected">Salidas</option>';   
                                }else{
                                 echo '<option value="1">Salidas</option>'; 
                                }
                                
                                 
                                ?>
                            </select>
                        </td>
                    <td>Mes</td>
                    <td>
                        <input type="text" class="fecha_periodo" id="mes" name="mes" value="<?php echo $mes; ?>"/>
                    </td>
                    <td>
                        <input type="button" id="mostrar" name="mostrar" class="boton" value="Mostrar"/>
                    </td>
                </tr>
            </table>
            
            <br/><br/><br/>
            <?php if(isset($_POST['mostrar'])){  ?>
            <table id="tComponentes">
                <thead>
                    <tr>
                        <th width="2%" align="center" scope="col">Almacén</th>
                        <th width="2%" align="center" scope="col">Componentes</th>
                        <th width="2%" align="center" scope="col">Descripción</th>
                        <th width="2%" align="center" scope="col">Existencia</th>
                        <th width="2%" align="center" scope="col">Mínimo</th>
                        <th width="2%" align="center" scope="col">Máximo</th>
                        <th width="2%" align="center" scope="col">Entradas</th>
                        <th width="2%" align="center" scope="col">Salidas</th>
                    </tr>
                </thead>
                <tbody>
                     <?php
                     $result = $catalogo->obtenerLista($consulta2);
                     while ($row = mysql_fetch_array($result)) {
                        $cantidadM="";
                        $cantidadS="";
                        $almacen=$row['id_almacen'];
                        if($row['entrada']==""){
                            $cantidadM="0";
                            
                        }else{
                            $cantidadM=$row['entrada'];
                           
                        }

                        if($row['salida']==""){
                            $cantidadS="0";
                        }else{
                            $cantidadS=$row['salida']; 
                        }
                        
                         echo "<tr>";
                         echo "<td align='center' scope='row'>".$row['nombre_almacen']."</td>";
                         echo "<td align='center' scope='row'>".$row['Modelo']."</td>";
                         echo "<td align='center' scope='row'>".$row['Descripcion']."</td>";
                         echo "<td align='center' scope='row'>".$row['cantidad_existencia']."</td>";
                         echo "<td align='center' scope='row'>".$row['CantidadMinima']."</td>";
                         echo "<td align='center' scope='row'>".$row['CantidadMaxima']."</td>";
                         echo "<td onclick=\"verDetalles('".$row['NoParte']."',0,'".$row['id_almacen']."');\" align='center' scope='row'><a href='#'>".$cantidadM."</a></td>";
                         echo "<td onclick=\"verDetalles('".$row['NoParte']."',1,'".$row['id_almacen']."');\" align='center' scope='row'><a href='#'>".$cantidadS."</a></td>";
                         echo "</tr>";
                     }
                     ?>
                </tbody>
            </table>
            <?php } ?>
            
            
        </div>
    </body>
</html>
