<?php

session_start();

if ((!isset($_SESSION['user']) || $_SESSION['user'] == "")) {
    header("Location: ../../../index.php");
}

if (isset($_POST['clavesCC']) && isset($_POST['servicios'])) {
    include_once("../../Classes/Contrato.class.php");
    include_once("../../Classes/CentroCosto.class.php");
    include_once("../../Classes/Catalogo.class.php");
    $contrato = new Contrato();
    $ccs = explode("&_&", $_POST['clavesCC']); //Todas las localidades vienen separadas con el delimitador señalado
    echo "<table>";
    echo "<tr><td>Localidad</td><td>Contrato</td><td>Anexo</td><td>Servicios activos</td><td></td></tr>";
    for ($i = 0; $i < count($ccs); $i++) {
        $cc = new CentroCosto();
        $cc->getRegistroById($ccs[$i]);
        $result = $contrato->getContratosVigentesByLocalidad($cc->getClaveCentroCosto());
        if (mysql_num_rows($result) > 0) {//Si hay contratos
            echo "<tr>";
            echo "<td>" . $cc->getNombre() . "</td>";
            echo "<td><select id=\"contrato$i\" name=\"contrato$i\" onchange=\"cargarAnexos('contrato$i','anexo$i','" . $cc->getClaveCentroCosto() . "','servicio$i');\" style=\"width: 200px;\">";
            while ($rs = mysql_fetch_array($result)) {
                echo "<option value=\"" . $rs['NoContrato'] . "\">" . $rs['NoContrato'] . " / " . $rs['FechaInicio'] . " / " . $rs['FechaTermino'] . "</option>";
            }
            echo "</select></td>";
            echo "<td><select id=\"anexo$i\" name=\"anexo$i\" onchange=\"cargarServicios('anexo$i','servicio$i');\" style=\"width: 200px;\"></select></td>";
            echo "<td><select id=\"servicio$i\" name=\"servicio$i\" style=\"width: 200px;\"></select></td>";
            echo "<td><a href='#' onclick='verValoresContrato($i); return false;'>"
            . "<img src=\"resources/images/Textpreview.png\"/></a></td>";
            echo "</tr>";
            echo "<script type=\"text/javascript\">cargarAnexos('contrato$i','anexo$i','" . $ccs[$i] . "','servicio$i');</script>";
        } else {
            echo "<tr>";
            echo "<td>" . $cc->getNombre() . "</td>";
            echo "<td><select id=\"contrato$i\" name=\"contrato$i\" style=\"width: 200px;\">";
            echo "<option value=\"\">Sin contrato vigente activo</option>";
            echo "</select></td>";
            echo "<td><select id=\"anexo$i\" name=\"anexo$i\" style=\"width: 200px;\">";
            echo "<option value=\"\">Sin anexo</option>";
            echo "</select></td>";
            echo "<td><select id=\"servicio$i\" name=\"servicio$i\" style=\"width: 200px;\">";
            echo "<option value=\"\">Sin contrato servicios</option>";
            echo "</select></td>";
            echo "<td></td>";
            echo "</tr>";
        }
        //Para saber que fila tiene el centro de costo
        echo "<input type=\"hidden\" id=\"" . $cc->getClaveCentroCosto() . "\" name=\"" . $cc->getClaveCentroCosto() . "\" value=\"" . ($i) . "\"/>";
        echo "<input type=\"hidden\" id=\"fila$i\" name=\"fila$i\" value=\"" . $cc->getClaveCentroCosto() . "\"/>";

        if (isset($_POST['IdSolicitud']) && $_POST['IdSolicitud'] != "null") {
            $consulta = "SELECT ks.id_solicitud, ks.id_partida, ks.cantidad, ks.Modelo, ks.ClaveCentroCosto, ks.tipo, ks.IdAnexoClienteCC, ks.IdKServicio, ks.IdServicio, cat.ClaveAnexoTecnico, ctt.NoContrato 
                FROM k_solicitud AS ks 
                LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = ks.IdAnexoClienteCC
                LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
                LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato
                WHERE ks.id_solicitud = " . $_POST['IdSolicitud'] . " AND ks.ClaveCentroCosto = '" . $cc->getClaveCentroCosto() . "'
                GROUP BY ks.ClaveCentroCosto;";
            $catalogo = new Catalogo();
            $result = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($result)) {
                echo "<input type='hidden' id='kservicio_pre$i' name='kservicio_pre$i' value='" . $rs['IdKServicio'] . "'/>";
                echo "<input type='hidden' id='servicio_pre$i' name='servicio_pre$i' value='" . $rs['IdServicio'] . "'/>";
                echo "<input type='hidden' id='idanexo_pre$i' name='idanexo_pre$i' value='" . $rs['IdAnexoClienteCC'] . "'/>";
                echo "<input type='hidden' id='contrato_pre$i' name='contrato_pre$i' value='" . $rs['NoContrato'] . "'/>";
            }
        }
    }
    echo "<table>";
    echo "<input type=\"hidden\" id=\"numero_contratos\" name=\"numero_contratos\" value=\"$i\"/>";
} else if (isset($_POST['NoParte']) && isset($_POST['crea_fila_contadores'])) {
    include_once("../../Classes/EquipoCaracteristicasFormatoServicio.class.php");
    if (isset($_POST['NoSerie'])) {
        $serie = $_POST['NoSerie'];
    } else {
        $serie = "";
    }

    if (isset($_POST['idRow'])) {
        $row = $_POST['idRow'];
    } else {
        $row = "0";
    }
    $equipo = new EquipoCaracteristicasFormatoServicio();
    //Obtenemos las caracteristicas del no de parte
    $fa = false;
    $color = false;
    $result2 = $equipo->getCaracteristicasByParte($_POST['NoParte']);
    while ($rs2 = mysql_fetch_array($result2)) {
        if ($rs2['IdTipoServicio'] == "1") {
            $color = true;
        }
        if ($rs2['IdFormatoEquipo'] == "3") {
            $fa = true;
        }
    }
    echo "<tr class='contadores' id='fila_contador_$row' style='max-width:100%;'>";
    echo "<td>Contadores de $serie</td>";
    $valor = "";
    if (isset($_POST['estado']) && $_POST['estado'] == "1") {
        $valor = "0";
    }
    echo "<td>Contador b/n<input type='text' id='contador_bn_$serie' name='contador_bn_$serie' style='width: 100px;'MaxLength='8' value='$valor'/></td>";
    echo "<td>";
    if ($color) {
        $valor = "";
        if (isset($_POST['estado']) && $_POST['estado'] == "1") {
            $valor = "0";
        }
        echo "Contador color<input type='text' id='contador_color_$serie' name='contador_color_$serie' style='width: 100px;' MaxLength='8' value='$valor'/>";
    }
    echo "</td>";
    $valor = "";
    if (isset($_POST['estado']) && $_POST['estado'] == "1") {
        $valor = "100";
    }
    echo "<td>Toner negro<input type='text' id='toner_bn_$serie' name='toner_bn_$serie' style='width: 100px;' MaxLength='3' value='$valor'/></td>";
    echo "<td>";
    if ($color) {
        $valor = "";
        if (isset($_POST['estado']) && $_POST['estado'] == "1") {
            $valor = "100";
        }
        echo "Toner cian<input type='text' id='toner_cian_$serie' name='toner_cian_$serie' style='width: 100px;' MaxLength='3' value='$valor'/>";
    }
    echo "</td>";
    echo "<td>";
    if ($color) {
        $valor = "";
        if (isset($_POST['estado']) && $_POST['estado'] == "1") {
            $valor = "100";
        }
        echo "Toner magenta<input type='text' id='toner_magenta_$serie' name='toner_magenta_$serie' style='width: 100px;' MaxLength='3' value='$valor'/>";
    }
    echo "</td>";
    echo "<td>";
    if ($color) {
        $valor = "";
        if (isset($_POST['estado']) && $_POST['estado'] == "1") {
            $valor = "100";
        }
        echo "Toner amarillo<input type='text' id='toner_amarillo_$serie' name='toner_amarillo_$serie' style='width: 100px;' MaxLength='3' value='$valor'/>";
    }
    echo "</td>";
    echo "<td></td>";
    echo "<td></td>";
    echo "</tr>";
} else if (isset($_POST['servicio']) && isset($_POST['arrendamiento']) && isset($_POST['prefijo'])) {
    $prefijo_mayor = strtoupper($_POST['prefijo']);
    $prefijo_menor = strtolower($_POST['prefijo']);
    include_once("../../Classes/ServicioIM.class.php");
    $servicio = new ServicioIM();
    $result = $servicio->obtenerEsquemaByTipoServicio($_POST['servicio'], $prefijo_menor);
    while ($rs = mysql_fetch_array($result)) {
        echo $rs['RM'] . "," . $rs['IncluidoBN'] . "," . $rs['IncluidoColor'] . "," . $rs['ExcedentesBN'] . "," . $rs['ExcedentesColor'] .
        "," . $rs['CostoProcesadaBN'] . "," . $rs['CostoProcesadaColor'];
    }
} else if (isset($_POST['no_parte']) && isset($_POST['idAlmacen']) && isset($_POST['existencias'])) {
    include_once("../../Classes/AlmacenConmponente.class.php");
    $almacen_componente = new AlmacenComponente();
    if ($almacen_componente->getRegistroById($_POST['no_parte'], $_POST['idAlmacen'])) {
        echo $almacen_componente->getExistencia();
    } else {
        echo "0";
    }
} else if (isset($_POST['id_solicitud']) && isset($_POST['id_partida']) && isset($_POST['marcar'])) {
    include_once("../../Classes/Solicitud.class.php");
    $solicitud = new Solicitud();
    if ($solicitud->NoSurtir($_POST['id_solicitud'], $_POST['id_partida'], $_POST['marcar'])) {
        if ($_POST['marcar'] == "0") {
            echo "La partida fue marcada como surtible";
        } else {
            echo "La partida fue marcada como no surtir";
        }
    }
} else if (isset($_POST['geocodificar'])) {
    $calle = str_replace(" ", "%20", $_POST['calle']);
    $exterior = str_replace(" ", "%20", $_POST['exterior']);
    $delegacion = str_replace(" ", "%20", $_POST['delegacion']);
    $estado = str_replace(" ", "%20", $_POST['estado']);
    
    $colonia = "";
    if(isset($_POST['colonia'])){
        $colonia = str_replace(" ", "%20", $_POST['colonia']);
    }
    $cp = "";
    if(isset($_POST['colonia'])){
        $cp = str_replace(" ", "%20", $_POST['cp']);
    }
    $prepAddr = "$calle%20$exterior%20$colonia%20$delegacion%20$cp%20$estado";
    $geocode = file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false');
    //$geocode = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=EX-HACIENDA%20DE%20ZACATEPEC%20KM%2053.8%20CARRT.%20FED.%20140%20PUEBLA%20JALAPA%20ZACATEPEC.%20S/N,SAN%20JOS%C3%89%20ZACATEPEC,75029%20ORIENTAL,Puebla,M%C3%A9xico&sensor=false");
    $output = json_decode($geocode);
    $latitude = $output->results[0]->geometry->location->lat;
    $longitude = $output->results[0]->geometry->location->lng;
    echo "$latitude,$longitude";
}
?>
