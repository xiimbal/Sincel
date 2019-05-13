<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ParametroLectura.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
if ($_POST['anexo'] != "") {
    $ruta_relativa = "";
    if(isset($_POST['externo'])){
        $ruta_relativa = "../../";
        echo '<script type="text/javascript" language="javascript" src="../resources/js/paginas/ReporteLecturas.js"></script>';
        echo '
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">';
        echo '<input type="hidden" id="anexo" name="anexo" value="'.$_POST['anexo'].'"/>';
    }
    $catalogo = new Catalogo();
    
    $anexo = $_POST['anexo'];
    
    $idDatosFacturacion = 0;
    $consulta = "SELECT IdDatosFacturacionEmpresa
        FROM c_cliente 
        WHERE ClaveCliente IN(
                SELECT ClaveCliente 
                FROM c_contrato 
                WHERE NoContrato IN(
                        SELECT NoContrato 
                        FROM `c_anexotecnico` 
                        WHERE ClaveAnexoTecnico = '$anexo'
                )
        );";
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        $idDatosFacturacion = $rs['IdDatosFacturacionEmpresa'];
    }
    
    $parametro = new ParametroLectura();
    $Ver_equipo = 1;
    $Mostrar_area = "";
    $Numero_proveedor = "";
    $Numero_orden = "";
    $Observaciones_dentro_xml = "";
    $Observaciones_fuera_xml = "";
    $Resaltar_periodo = "";
    $MostrarLocalidad = "";
    $HistoricoFacturacion = "";
    $FechaInstalacion = "";
    $Rentas_lecturas = "";
    $Factura_renta_adelantada = "";
    $Dir_reporte = "";
    $MostrarLecturas = "1";
    $MostrarImporteCero = "1";
    $MostrarEncabezadoServicio = "";
    $Agrupar_Color = "";
    $Dividir_Color = "";
    $dividir_factura = "";
    $agrupar_factura = "";
    $Mostrar_Serie = "";
    $MostrarModelo = "";
    $Agrupar_Renta = "1";
    $MostrarPeriodo = "";
    $IdProductoSATRenta = 50951;
    $IdProductoSATImpresion = 51334;
    if ($parametro->getRegistroById($_POST['anexo'])) {
        $Ver_equipo = $parametro->getVer_equipo();
        $Mostrar_area = $parametro->getMostrar_area();
        $Numero_proveedor = $parametro->getNumero_proveedor();
        $Numero_orden = $parametro->getNumero_orden();
        $Observaciones_dentro_xml = $parametro->getObservaciones_dentro_xml();
        $Observaciones_fuera_xml = $parametro->getObservaciones_fuera_xml();
        $Resaltar_periodo = $parametro->getResaltar_periodo();
        $MostrarLocalidad = $parametro->getMostrarLocalidad();
        $HistoricoFacturacion = $parametro->getHistoricoFacturacion();
        $FechaInstalacion = $parametro->getFechaInstalacion();
        $Rentas_lecturas = $parametro->getRentas_lecturas();
        $Factura_renta_adelantada = $parametro->getFactura_renta_adelantada();
        $Dir_reporte = $parametro->getDir_reporte();
        $MostrarImporteCero = $parametro->getMostrarImporteCero();
        $MostrarEncabezadoServicio = $parametro->getMostrarEncabezadoServicio();
        $Agrupar_Color = $parametro->getAgrupar_Color();
        $Dividir_Color = $parametro->getDividir_Color();
        $dividir_factura = $parametro->getDividir_factura();
        $agrupar_factura = $parametro->getAgrupar_factura();
        $Agrupar_Renta = $parametro->getAgrupar_Renta();
        $Mostrar_Serie = $parametro->getMostrar_Serie();
        $MostrarModelo = $parametro->getMostrarModelo();
        $MostrarLecturas = $parametro->getMostrar_Lecturas();
        $MostrarPeriodo = $parametro->getMostrarPeriodo();
        $IdProductoSATRenta = $parametro->getIdProductoSATRenta();
        $IdProductoSATImpresion = $parametro->getIdProductoSATImpresion();
        echo "<input type='hidden' name='param_lect' id='param_lect' value'" . $parametro->getId_parametro() . "'/>";
    }
    
    $datos_productos = array();
    $consulta = "SELECT cps.ClaveProdServ,cps.Descripcion, eps.IdEmpresaProductoSAT, cps.IdProdServ
        FROM c_claveprodserv cps
        INNER JOIN k_empresaproductosat AS eps ON eps.IdClaveProdServ = cps.IdProdServ 
        AND cps.Activo = 1 AND IdDatosFacturacionEmpresa = $idDatosFacturacion";
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){   
        $datos_productos[$rs['IdProdServ']] = $rs['ClaveProdServ']." ".$rs['Descripcion'];        
    }
    
    ?>
    <fieldset>
        <legend>Parámetros adicionales</legend>
        <fieldset>
            <legend>General</legend>
            <fieldset>
                <legend>Productos SAT</legend>
                <table style="width: 100%;">
                    <tr>
                        <td>Renta</td>
                        <td>
                            <select name="rentaSAT" id="rentaSAT" class="select">
                                <?php                                    
                                    foreach ($datos_productos as $key => $value) {
                                        $s = ($key == $IdProductoSATRenta)? "selected" : "";
                                        echo "<option value='$key' $s>$value</option>";
                                    }
                                ?>
                            </select>
                        </td>
                        <td>Impresiones</td>
                        <td>
                            <select name="impresionesSAT" id="impresionesSAT" class="select">
                                <?php
                                    foreach ($datos_productos as $key => $value) {
                                        $s = ($key == $IdProductoSATImpresion)? "selected" : "";
                                        echo "<option value='$key' $s>$value</option>";
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Visualizar</legend>
                <table style="width: 100%;">
                    <tr>
                        <td>Dividir Factura</td>
                        <td><select name="dividir_factura" id="dividir_factura">
                                <option value="0" <?php if ($dividir_factura == 0) echo "selected"; ?>>Ninguno</option>
                                <option value="1" <?php if ($dividir_factura == 1) echo "selected"; ?>>Costo</option>
                                <option value="2" <?php if ($dividir_factura == 2) echo "selected"; ?>>Servicio</option>
                                <option value="3" <?php if ($dividir_factura == 3) echo "selected"; ?>>Localidad</option>
                                <option value="4" <?php if ($dividir_factura == 4) echo "selected"; ?>>Centro de Costo</option>
                                <option value="5" <?php if ($dividir_factura == 5) echo "selected"; ?>>Zona</option>
                            </select></td>
                        <td>Agrupar por</td>
                        <td><select name="agrupar_factura" id="agrupar_factura">
                                <option value="0" <?php if ($agrupar_factura == 0) echo "selected"; ?>>Equipo</option>
                                <option value="1" <?php if ($agrupar_factura == 1) echo "selected"; ?>>Servicio</option>
                                <option value="2" <?php if ($agrupar_factura == 2) echo "selected"; ?>>Localidad</option>
                                <option value="3" <?php if ($agrupar_factura == 3) echo "selected"; ?>>Centro de Costo</option>
                                <option value="4" <?php if ($agrupar_factura == 4) echo "selected"; ?>>Zona</option>
                            </select>
                        </td>
                        <td>
                            <input type="checkbox" value="1" id="periodo_factura" name="periodo_factura" <?php
                            if ($MostrarPeriodo == 1) {
                                echo"checked='checked'";
                            }
                            ?>/>
                        </td>
                        <td>Mostrar periodo en partidas</td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" value="1" id="rentas_lecturas" name="rentas_lecturas" <?php
                            if ($Rentas_lecturas == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Dividir rentas y lecturas</td>
                        <td><input type="checkbox" value="1" id="Dividir_Color" name="Dividir_Color" <?php
                            if ($Dividir_Color == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Dividir color y B/N</td> 
                        <!--<td><input type="checkbox" value="1" id="Agrupar_Color" name="Agrupar_Color" <?php
                        /* if ($Agrupar_Color == 1) {
                          echo"checked='checked'";
                          } */
                        ?>/></td>
                        <td>Agrupar por Color</td>-->
                        <td><input type="checkbox" value="1" id="Agrupar_Renta" name="Agrupar_Renta" <?php
                            if ($Agrupar_Renta == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Agrupar Renta</td>
                    </tr>
                    <tr>                          
                        <td><input type="checkbox" value="1" id="mostrar_area" name="mostrar_area" <?php
                            if ($Mostrar_area == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Mostrar area</td>  
                        <td><input type="checkbox" value="1" id="Mostrar_Modelo" name="Mostrar_Modelo" <?php
                            if ($MostrarModelo == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Mostrar Modelo</td>
                        <td><input type="checkbox" value="1" id="Mostrar_Serie" name="Mostrar_Serie" <?php
                            if ($Mostrar_Serie == 1) {
                                echo"checked='checked'";
                            }
                            ?></td>
                        <td>Mostrar Serie</td>
                    </tr>
                    <tr>                        
                        <td>                            
                            <input type="checkbox" value="1" id="MostrarLecturas" name="MostrarLecturas" <?php
                            if ($MostrarLecturas == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Imprimir lecturas en Factura PDF</td>
                        <td>
                            <input type="checkbox" value="1" id="MostrarEncabezadoServicio" name="MostrarEncabezadoServicio" <?php
                            if ($MostrarEncabezadoServicio == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Mostrar encabezado de servicios</td>
                        <td><input type="checkbox" value="1" id="MostrarImporteCero" name="MostrarImporteCero" <?php
                            if ($MostrarImporteCero == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Mostrar importe en cero</td>
                    </tr>                                    
                    <tr> 
                        <td><input type="checkbox" value="1" id="dir_rep" name="dir_rep" <?php
                            if ($Dir_reporte == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Dirección en reporte de lecturas</td>
                        <td><input type="checkbox" value="1" id="fact_adel" name="fact_adel" <?php
                            if ($Factura_renta_adelantada == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Facturar renta y periodo adelantados</td>
                        <td><input type="checkbox" value="1" id="resal_perio" name="resal_perio" <?php
                            if ($Resaltar_periodo == 1) {
                                echo"checked='checked'";
                            }
                            ?>/></td>
                        <td>Resaltar período</td>                    
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" value="1" id="MostrarLocalidad" name="MostrarLocalidad" <?php
                            if ($MostrarLocalidad == 1) {
                                echo"checked='checked'";
                            }
                            ?>/>
                        </td>
                        <td>Mostrar localidad</td>
                        <td>
                            <input type="checkbox" value="1" id="HistoricoFacturacion" name="HistoricoFacturacion" <?php
                            if ($HistoricoFacturacion == 1) {
                                echo"checked='checked'";
                            }
                            ?>/>
                        </td>
                        <td>Histórico de facturación</td>
                        <td>
                            <input type="checkbox" value="1" id="FechaInstalacion" name="FechaInstalacion" <?php
                            if ($FechaInstalacion == 1) {
                                echo"checked='checked'";
                            }
                            ?>/>
                        </td>
                        <td>Considerar fecha de instalación</td>
                    </tr>                    
                </table>
            </fieldset>            
            <table>                
                <tr>
                    <td>Número de orden</td>
                    <td><input type="text" id="num_orden" name="num_orden" value="<?php echo $Numero_orden ?>" /></td>
                    <td>Número de proveedor</td>
                    <td><input type="text" id="num_prov" name="num_prov" value="<?php echo $Numero_proveedor ?>"/></td>
                </tr>
                <tr>
                    <td>Observaciones dentro de XML</td>
                    <td><textarea id="obs_in_xml" name="obs_in_xml" cols="15" rows="2"><?php echo $Observaciones_dentro_xml ?></textarea></td>
                    <td>Observaciones fuera de XML</td>
                    <td><textarea id="obs_out_xml" name="obs_out_xml" cols="15" rows="2"><?php echo $Observaciones_fuera_xml ?></textarea></td>
                </tr>
            </table>
        </fieldset>
        <fieldset>
            <legend>Encabezados de servicios</legend>
            <table>
                <tr>
                    <th>Encabezado</th><th>Cambiar Encabezado</th><th>Encabezado modificable</th>
                </tr>
                <?php
                $validaciones = "";                
                $query = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre FROM c_servicioim AS c WHERE c.Activo=1;");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
                    FROM c_servicioim AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioIM
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $nombre = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $nombre = $rsp['NombreKP'];
                            $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    if ($nombre == "") {
                        $nombre = $rs['Nombre'];
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td><td><input type='checkbox' onclick='validarSerNom(" . $rs['Id_servicio'] . ");' name='check_serv_nom_" . $rs['Id_servicio'] . "' id='check_serv_nom_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
                }

                $query = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogim AS c WHERE c.Activo=1;");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
                    FROM c_serviciogim AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGIM
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $nombre = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $nombre = $rsp['NombreKP'];
                            $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    if ($nombre == "") {
                        $nombre = $rs['Nombre'];
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td><td><input type='checkbox' onclick='validarSerNom(" . $rs['Id_servicio'] . ");' name='check_serv_nom_" . $rs['Id_servicio'] . "' id='check_serv_nom_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
                }


                $query = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciofa AS c WHERE c.Activo=1;");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
                    FROM c_serviciofa AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioFA
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $nombre = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $nombre = $rsp['NombreKP'];
                            $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    if ($nombre == "") {
                        $nombre = $rs['Nombre'];
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td>
                    <td><input type='checkbox' onclick='validarSerNom(" . $rs['Id_servicio'] . ");' name='check_serv_nom_" . $rs['Id_servicio'] . "' id='check_serv_nom_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
                }

                $query = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogfa AS c WHERE c.Activo=1");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre,kp.Descripcion AS NombreKP
                    FROM c_serviciogfa AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGFA
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=0;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $nombre = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $nombre = $rsp['NombreKP'];
                            $validaciones.="validarSerNom(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    if ($nombre == "") {
                        $nombre = $rs['Nombre'];
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td><td><input type='checkbox' onclick='validarSerNom(" . $rs['Id_servicio'] . ");' name='check_serv_nom_" . $rs['Id_servicio'] . "' id='check_serv_nom_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_nom_" . $rs['Id_servicio'] . "' id='text_serv_nom_" . $rs['Id_servicio'] . "' value='$nombre' $dis/></td></tr>";
                }
                ?>
            </table>
        </fieldset>

        <fieldset>
            <legend>Unidades de servicios</legend>
            <table>
                <tr>
                    <th>Servicio</th><th>Cambiar Unidad</th><th>Renta</th><th>Excedente</th><th>Impresiones</th>
                </tr>
                <?php
                
                $query = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre FROM c_servicioim AS c WHERE c.Activo=1");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioIM AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
                    FROM c_servicioim AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioIM
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $renta = "";
                    $impresiones = "";
                    $excedente = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $renta = $rsp['Renta'];
                            $impresiones = $rsp['Impresiones'];
                            $excedente = $rsp['Excedente'];
                            $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td><td><input type='checkbox' onclick='validarSerUni(" . $rs['Id_servicio'] . ");' name='check_serv_uni_" . $rs['Id_servicio'] . "' id='check_serv_uni_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
                }

                $query = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogim AS c WHERE c.Activo=1 ;");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGIM AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
                    FROM c_serviciogim AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGIM
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $renta = "";
                    $impresiones = "";
                    $excedente = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $renta = $rsp['Renta'];
                            $impresiones = $rsp['Impresiones'];
                            $excedente = $rsp['Excedente'];
                            $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td><td><input type='checkbox' onclick='validarSerUni(" . $rs['Id_servicio'] . ");' name='check_serv_uni_" . $rs['Id_servicio'] . "' id='check_serv_uni_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
                }

                $query = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciofa AS c WHERE c.Activo=1;");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioFA AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
                    FROM c_serviciofa AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioFA
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $renta = "";
                    $impresiones = "";
                    $excedente = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $renta = $rsp['Renta'];
                            $impresiones = $rsp['Impresiones'];
                            $excedente = $rsp['Excedente'];
                            $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td><td><input type='checkbox' onclick='validarSerUni(" . $rs['Id_servicio'] . ");' name='check_serv_uni_" . $rs['Id_servicio'] . "' id='check_serv_uni_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
                }

                $query = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre FROM c_serviciogfa AS c WHERE c.Activo=1");
                $query2 = $catalogo->obtenerLista("SELECT c.IdServicioGFA AS Id_servicio,c.Nombre AS Nombre,kp.Renta AS Renta,kp.Excedente AS Excedente,kp.Impresiones AS Impresiones
                    FROM c_serviciogfa AS c
                    LEFT JOIN k_parametro_servicio AS kp ON kp.Id_servicio=c.IdServicioGFA
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE c.Activo=1 AND cp.ClaveAnexoTecnico='$anexo' AND kp.Tipo=1;");
                $rsp = mysql_fetch_array($query2);
                while ($rs = mysql_fetch_array($query)) {
                    $s = "";
                    $dis = "disabled='disabled'";
                    $renta = "";
                    $impresiones = "";
                    $excedente = "";
                    if ($rsp != "") {
                        if ($rsp['Id_servicio'] == $rs['Id_servicio']) {
                            $s = "checked='checked'";
                            $dis = "";
                            $renta = $rsp['Renta'];
                            $impresiones = $rsp['Impresiones'];
                            $excedente = $rsp['Excedente'];
                            $validaciones.="validarSerUni(" . $rs['Id_servicio'] . ");";
                            $rsp = mysql_fetch_array($query2);
                        }
                    }
                    echo "<tr><td>(" . $rs['Id_servicio'] . ") " . $rs['Nombre'] . "</td><td><input type='checkbox' onclick='validarSerUni(" . $rs['Id_servicio'] . ");' name='check_serv_uni_" . $rs['Id_servicio'] . "' id='check_serv_uni_" . $rs['Id_servicio'] . "' value='1' $s/></td>";
                    echo "<td><input type='text' name='text_serv_renta_" . $rs['Id_servicio'] . "' id='text_serv_renta_" . $rs['Id_servicio'] . "' value='$renta' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_excedente_" . $rs['Id_servicio'] . "' id='text_serv_excedente_" . $rs['Id_servicio'] . "' value='$excedente' $dis/></td>";
                    echo "<td><input type='text' name='text_serv_impresiones_" . $rs['Id_servicio'] . "' id='text_serv_impresiones_" . $rs['Id_servicio'] . "' value='$impresiones' $dis/></td></tr>";
                }
                ?>
            </table>
        </fieldset>
        <fieldset>
            <legend>Conceptos adicionales</legend>
            <img class="imagenMouse" src="<?php echo $ruta_relativa; ?>resources/images/Erase.png" title="Borrar fila" onclick='eliminarFila();' style="float: right; cursor: pointer;" />
            <img class="imagenMouse" src="<?php echo $ruta_relativa; ?>resources/images/add.png" title="Nuevo" onclick='agregarServicio();' style="float: right; cursor: pointer;" />  
            <table id="concepto_tabla">
                <tr>
                    <th>Nivel de facturación</th><th>Descripción</th><th>Cantidad</th><th>Precio Unitario</th><th>Importe</th><th>Producto SAT</th>
                </tr>
                <?php
                $filas = 0;
                $query2 = $catalogo->obtenerLista("SELECT kp.Descripcion AS Descripcion,kp.IdProductoSAT,
                    kp.Cantidad AS Cantidad,kp.PrecioUnitario AS PrecioUnitario,kp.Nivel_facturacion AS Nivel_facturacion
                    FROM k_parametro_concepto AS kp
                    LEFT JOIN c_parametro_lectura AS cp ON cp.Id_parametro=kp.Id_parametro
                    WHERE cp.ClaveAnexoTecnico='$anexo';");
                while ($rs = mysql_fetch_array($query2)) {
                    $query22 = $catalogo->obtenerLista("SELECT * FROM c_nivel_facturacion ORDER BY Nombre");
                    echo "<td><select name='select_con_adic_" . $filas . "' id='select_con_adic_" . $filas . "' style='width: 150px'>";
                    echo "<option value=''>Selecciona el nivel de facturación</option>";
                    while ($rspl = mysql_fetch_array($query22)) {
                        $l = "";
                        if ($rspl['Id_nivel_facturacion'] == $rs['Nivel_facturacion']) {
                            $l = "selected";
                        }
                        echo "<option value='" . $rspl['Id_nivel_facturacion'] . "' $l>" . $rspl['Nombre'] . "</option>";
                    }
                    echo "</select></td>";
                    echo "<td><input type='text' name='text_con_adic_" . $filas . "' id='text_con_adic_" . $filas . "' value='" . $rs['Descripcion'] . "' /></td>";
                    echo "<td><input type='text' name='cantidad_con_adic_" . $filas . "' id='cantidad_con_adic_" . $filas . "' value='" . $rs['Cantidad'] . "' onchange='calcularImporte($filas)' style='width: 50px'/></td>";
                    echo "<td><input type='text' name='preciounitario_con_adic_" . $filas . "' id='preciounitario_con_adic_" . $filas . "' value='" . $rs['PrecioUnitario'] . "' onchange='calcularImporte($filas)' style='width: 50px'/></td>";
                    echo "<td><input type='text' name='importe_con_adic_" . $filas . "' id='importe_con_adic_" . $filas . "' value='$" . number_format($rs['PrecioUnitario'] * $rs['Cantidad'],2) . "' readonly='readonly' style='width: 100px'/></td>";
                    echo "<td>"
                        . "<select name='producto_adic_$filas' id='producto_adic_$filas' class='select'>";
                            foreach ($datos_productos as $key => $value) {
                                $s = ($key == $rs['IdProductoSAT'])? "selected" : "";
                                echo "<option value='$key' $s>$value</option>";
                            }
                    echo "</select>"
                            //. "<input type='text' name='unidad_con_adic_" . $filas . "' id='unidad_con_adic_" . $filas . "' value='" . $rs['Unidad_medida'] . "' />"
                        . "</td>"
                        . "</tr>";
                    $filas++;
                }
                ?>
            </table>
            <input type="hidden" name="filas_conceptos" id="filas_conceptos" value="<?php echo $filas; ?>"/>
        </fieldset>
        <?php
        $permiso = new PermisosSubMenu();
        if ($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 9)) {
            ?>
            <input type="button" name="boton_guardar" id="boton_guardar" class="button" value="Guardar parámetros" onclick="guardarParametros();">
        <?php }
        ?>
    </fieldset>
    <script><?php echo $validaciones . "setFilas($filas);" ?></script>
<?php } ?>