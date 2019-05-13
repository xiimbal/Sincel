<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/NotaTicket.class.php");
include_once("../WEB-INF/Classes/NotaTicket.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/lista_ticket.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$notaTicket = new NotaTicket();
$idNotaTicket = $_POST['idNota'];
$tipoReporte = $_POST['tipoReporte'];
$textoSol = "";
if ($tipoReporte == "15") {
    $textoSol = "Toner";
} else {
    $textoSol = "Refacción";
}
$notaTicket->getRegistroById($idNotaTicket);
$catalogo = new Catalogo();

$idTicket = $notaTicket->getIdTicket();
$fechaHora = $notaTicket->getFechaHora();
$diagnostico = $notaTicket->getDiagnostico();
$estatus = $notaTicket->getIdEstatus();
$activo = "";
$mostrar = "";
if ($notaTicket->getActivo() == "1"){
    $activo = "checked";
}

if ($notaTicket->getMostrarCliente() == "1"){
    $mostrar = "checked";
}

if(!$permisos_grid->getModificar()){
    $enabled = "disabled='disabled'";
}else{
    $enabled = "";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head> 
        <?php
        if (isset($_GET['frame']) && $_GET['frame'] == "1") {
            $path_previo = "../";    
            echo '<script type="text/javascript" language="javascript" src="../resources/js/paginas/listaValidarRefaccion.js"></script>';
        }else{
            $path_previo = "";    
            echo '<script type="text/javascript" language="javascript" src="resources/js/paginas/listaValidarRefaccion.js"></script>';
        }
        ?>         
    </head>
    <body>  
        <div id="mensajeEdicion"></div>
        <table>  
            <tr> 
                <td>  
                    Fecha hora
                </td>  
                <td>  
                    <input type="text" name="fechaHora" id="fechaHora" value="<?php echo $fechaHora ?> "/>
                </td> 
            </tr>  
            <tr> 
                <td>  
                    Diagnostico
                </td>  
                <td>  
                    <textarea id='diagnostico' name='diagnostico' cols='50'><?php echo $diagnostico; ?></textarea>
                </td> 
            </tr>  
            <tr>
                <td>Estatus de atención:</td>
                <td>
                    <select id="estatus" name="estatus" onchange="mostrarRefacciones();">
                        <?php
                        $query = $catalogo->obtenerLista("SELECT e.IdEstado,e.Nombre AS nombreEstado,f.IdFlujo,f.Nombre,fe.IdKFlujo
                                                                            FROM c_estado e,c_flujo f,k_flujoestado fe
                                                                            WHERE e.IdEstado=fe.IdEstado
                                                                            AND fe.IdFlujo=f.IdFlujo
                                                                            AND fe.IdFlujo=6 ORDER BY nombreEstado ASC");
                        echo "<option value='0' >Selecciona una opción</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            $s = "";
                            if ($estatus != "" && $estatus == $rs['IdEstado']) {
                                $s = "selected";
                            }
                            echo "<option value=" . $rs['IdEstado'] . " " . $s . ">" . $rs['nombreEstado'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?> <?php echo $enabled; ?>/>Activo</td>
                <td><input type="checkbox" name="show" id="show" <?php echo $mostrar; ?> <?php echo $enabled; ?> onclick="CambiarMostrarCliente('<?php echo $idNotaTicket;?>');"/>Mostrar a cliente</td>
            </tr> 
        </table>  
        <table>   
            <?php
            if ($estatus == "12") {
                $queryArea = $catalogo->obtenerLista("SELECT na.IdArea,a.Descripcion FROM k_nota_area na,c_area a WHERE na.IdNota='$idNotaTicket' AND a.IdArea=na.IdArea");
                while ($rs = mysql_fetch_array($queryArea)) {
                    echo "<tr><td>Área: <input style='width: 250px' type='text' name='componente' id='componente' value='" . $rs['Descripcion'] . "'/></td>";
                    echo "</tr>";
                }
            } else {
                $queryRefaccion = $catalogo->obtenerLista("SELECT c.Modelo,c.NoParte,nr.Cantidad,c.Descripcion,
                    (CASE WHEN nt.IdEstatusAtencion <> 24 THEN nr.Cantidad WHEN !ISNULL(nr2.CantidadNota) THEN nr2.CantidadNota ELSE nr.Cantidad END) AS CantidadNota
                    FROM k_nota_refaccion AS nr
                    INNER JOIN c_componente AS c ON c.NoParte=nr.NoParteComponente
                    LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = nr.IdNotaTicket
                    LEFT JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (
                    SELECT MIN(nt3.IdNotaTicket) 
                    FROM c_notaticket AS nt3
                    INNER JOIN k_nota_refaccion AS nr3 ON nr3.IdNotaTicket = nt3.IdNotaTicket 
                    WHERE nt3.IdTicket = nt.IdTicket AND nt3.IdEstatusAtencion = 9 AND nr3.NoParteComponente = nr.NoParteComponente
                    )
                    LEFT JOIN k_nota_refaccion AS nr2 ON nr2.IdNotaTicket = nt2.IdNotaTicket AND nr2.NoParteComponente = nr.NoParteComponente
                    WHERE nt.IdNotaTicket = $idNotaTicket AND nt.IdEstatusAtencion = $estatus GROUP BY NoParte, nr.IdNotaTicket");
                while ($rs = mysql_fetch_array($queryRefaccion)) {
                    echo "<tr><td>$textoSol <input style='width: 250px' type='text' name='componente' id='componente' value='" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "'/></td>";
                    echo "<td>Cantidad: <input type='text' name='cantidad' id='cantidad' value='" . $rs['CantidadNota'] . "'/></td></tr>";
                }
            }
            ?>
        </table>  
    </body>
</html>
