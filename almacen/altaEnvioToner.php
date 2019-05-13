<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$idNota = "";
if (isset($_POST['idNota_array']) && !empty($_POST['idNota_array'])) {
    $idNota = $_POST['idNota_array'];
} else {
    $idNota = $_POST['idNota'];
}
$pagina_listaRegresar = "almacen/toner_solicitado.php";
if(isset($_GET['etoner'])){
    $pagina_listaRegresar.="?etoner='1'";
}
$totalRestantes = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>        

        <script type="text/javascript" language="javascript" src="resources/js/paginas/EntregarToner.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/listaValidarRefaccion.js"></script>        
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
                $(".filtro").multiselect({
                    multiple: false,
                    noneSelectedText: "No ha seleccionado",
                    selectedList: 1
                }).multiselectfilter({
                    label: 'Filtro',
                    placeholder: 'Escribe el filtro'
                });
                $("#mensajeria").hide();
                //$("#propio").hide();
                $("#div_otro").hide();
            });
        </script>
    </head>
    <body>
        <form id="frmEntregaToner" name="frmEntregaToner" action="/" method="POST">
            <table style="width:80%">
                <?php
                $consulta = "SELECT t.IdTicket,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,(SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area
                            ,CONCAT ('(',nr.Cantidad,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                            ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte, e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,nr.IdAlmacen,cl.ClaveCliente,t.ClaveCentroCosto,p.IdPedido,c.Descripcion                                                        
                            FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e,c_pedido p
                            WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                            AND nt.IdEstatusAtencion=e.IdEstado AND nt.IdEstatusAtencion=21 AND p.IdTicket=t.IdTicket
                            AND nr.Cantidad<>0 AND c.IdTipoComponente=2 AND nt.IdNotaTicket IN ($idNota)                                            
                            GROUP BY nt.IdNotaTicket ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                
                $query = $catalogo->obtenerLista($consulta);
                $contador = 0;
                while ($rs = mysql_fetch_array($query)) {
                    $totalRestantes = (int) $rs['Cantidad'] - (int) $rs['CantidadSurtida'];
                    echo "<tr>
                        <td>Toner: </td>
                        <td><input type='text' name='toner$contador' id='toner$contador' value='" . $rs['Modelo'] . "' readonly /></td>"
                    . "<td>Cantidad solicitada: </td><td><input type='text' name='cantidadRestante$contador' id='cantidadRestante$contador' value='" . $rs['Cantidad'] . "' readonly style='width: 50px'/></td>"
                    . "<td>Cantidad enviada: </td><td><input type='text' name='cantidadEnviada$contador' id='cantidadEnviada$contador' value='" . $rs['CantidadSurtida'] . "' readonly style='width: 50px'/></td>"
                    . "<td>cantidad pendiente.: </td><td><input type='text' name='cantidad$contador' id='cantidad$contador' value='$totalRestantes' style='width: 50px'/></td>
                    </tr>";
                    ?>
                    <input type="hidden" name="nota<?php echo $contador; ?>" id="nota<?php echo $contador; ?>" value="<?php echo $rs['IdNotaTicket'] ?>"/>
                    <input type="hidden" name="noparte<?php echo $contador; ?>" id="noparte<?php echo $contador; ?>" value="<?php echo $rs['NoParte'] ?>"/>
                    <input type="hidden" name="almacen<?php echo $contador; ?>" id="almacen<?php echo $contador; ?>" value="<?php echo $rs['IdAlmacen'] ?>"/>
                    <input type="hidden" name="cliente<?php echo $contador; ?>" id="cliente<?php echo $contador; ?>" value="<?php echo $rs['ClaveCliente'] ?>"/>
                    <input type="hidden" name="localidad<?php echo $contador; ?>" id="localidad<?php echo $contador; ?>" value="<?php echo $rs['ClaveCentroCosto'] ?>"/>
                    <input type="hidden" name="pedido<?php echo $contador; ?>" id="pedido<?php echo $contador; ?>" value="<?php echo $rs['IdPedido'] ?>"/>
                    <input type="hidden" name="modelo<?php echo $contador; ?>" id="modelo<?php echo $contador; ?>" value="<?php echo $rs['Modelo'] ?>"/>
                    <input type="hidden" name="descripcion<?php echo $contador; ?>" id="descripcion<?php echo $contador; ?>" value="<?php echo $rs['Descripcion'] ?>"/>
                    <input type="hidden" name="idTicket<?php echo $contador; ?>" id="idTicket<?php echo $contador; ?>" value="<?php echo $rs['IdTicket'] ?>"/>
                    <input type="hidden" name="diagnostico<?php echo $contador; ?>" id="diagnostico<?php echo $contador; ?>" value="<?php echo $rs['DiagnosticoSol'] ?>"/>
                    <?php
                    $contador++;
                }
                echo "<input type='hidden' name='contador_envios' id='contador_envios' value='$contador'/>";
                ?>
            </table>
            <br/>
            <table>
                <tr>
                    <td><input type='radio' id='tipoMensajeria1' name='tipoMensajeria' value="1" onclick="MostrarTransporte('1');" checked="checked"/>Transporte propio</td>
                    <td><input type='radio' id='tipoMensajeria2' name='tipoMensajeria' value="2" onclick="MostrarTransporte('2');"/>Mensajería</td>
                    <td><input type='radio' id='tipoMensajeria3' name='tipoMensajeria' value="3" onclick="MostrarTransporte('3');"/>Otro</td>
                </tr>
            </table>
            <br/>
            <div id="propio">
                <table  style="width: 50%;">
                    <tr>
                        <td>
                            <select id='tranportepropio' name='tranportepropio' class='filtro' style="width: 150px;">
                                <option value='0'>Selecciona el vehículo</option>
                                <?php
                                $consulta1 = "SELECT v.IdVehiculo,v.Modelo,v.Placas FROM c_vehiculo v WHERE v.Activo=1 ORDER BY v.Modelo ASC";
                                $query1 = $catalogo->obtenerLista($consulta1);
                                while ($rs = mysql_fetch_array($query1)) {
                                    echo "<option value=" . $rs['IdVehiculo'] . ">" . $rs['Modelo'] . "/" . $rs['Placas'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>Conductor</td>
                        <td>
                            <select id='conductor' name='conductor' class='filtro' style="width: 150px;">
                                <option value='0'>Selecciona la conductor</option>
                                <?php
                                $consulta2 = "SELECT c.IdConductor,c.Nombre,c.ApellidoPaterno,c.ApellidoMaterno FROM c_conductor c WHERE c.Activo=1 ORDER BY c.Nombre ASC";
                                $query2 = $catalogo->obtenerLista($consulta2);
                                while ($rs = mysql_fetch_array($query2)) {

                                    echo "<option value=" . $rs['IdConductor'] . ">" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div id="mensajeria">
                <table>
                    <tr>
                        <td>
                            <select id='tranporteMensajeria' name='tranporteMensajeria' class='filtro'style="width: 150px;">
                                <option value='0'>Selecciona la mensajería</option>
                                <?php
                                $consulta3 = "SELECT m.IdMensajeria,m.Nombre FROM c_mensajeria m WHERE m.Activo=1 ORDER BY m.Nombre ASC";
                                $query3 = $catalogo->obtenerLista($consulta3);
                                while ($rs = mysql_fetch_array($query3)) {
                                    echo "<option value=" . $rs['IdMensajeria'] . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <?php 
                        $NoGuia = "";
                        $queryGuia = "SELECT t.NoGuia FROM c_ticket t 
                            INNER JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                            WHERE nt.IdNotaTicket = $idNota";
                        $consultaGuia = $catalogo->obtenerLista($queryGuia);
                        if($rsGuia = mysql_fetch_array($consultaGuia)){
                            $NoGuia = $rsGuia['NoGuia'];
                        }
                        ?>
                        <td>No. de guía</td><td><input type='text' name='noGuia' id='noGuia' style="width: 150px;" value="<?php echo $NoGuia;?>"/></td>
                    </tr>
                </table>
            </div>
            <div id="div_otro">
                <table>
                    <tr>
                        <td><input type="text" id="otro" name="otro"/></td>
                    </tr>
                </table>
            </div>
            <input type="hidden" value="<?php echo $pagina_listaRegresar; ?>" name="paginaExito" id="paginaExito" />
            <input type="submit" id="botonGuardar" name="botonGuardar" class="boton" value="Entregar"/>
            <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                    return false;"/>

        </form>       
    </body>
</html>