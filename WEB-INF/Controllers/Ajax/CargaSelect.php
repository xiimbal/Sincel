<?php

session_start();

if ((!isset($_SESSION['user']) || $_SESSION['user'] == "")) {
    header("Location: ../../../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();

if (isset($_POST['cliente']) && isset($_POST['contrato'])) {
    $query = $catalogo->obtenerLista("SELECT NoContrato, date(FechaInicio) as FechaInicio, date(FechaTermino) as FechaTermino FROM `c_contrato` WHERE ClaveCliente = '" . $_POST['cliente'] . "' AND Activo = 1;");
    echo "<option value=\"\">Selecciona el contrato</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['NoContrato'] . "\">" . $rs['NoContrato'] . " / " . $rs['FechaInicio'] . " / " . $rs['FechaTermino'] . "</option>";
    }
} else if (isset($_POST['cliente']) && isset($_POST['tipo_cliente'])) {
    include_once("../../Classes/Cliente.class.php");
    $cliente = new Cliente();
    if ($cliente->getRegistroById($_POST['cliente'])) {
        echo $cliente->getIdTipoCliente();
    } else {
        echo "";
    }
} else if (isset($_POST['ClaveCliente']) && isset($_POST['ClienteUnico'])) {
    include_once("../../Classes/Cliente.class.php");
    $cliente = new Cliente();
    if ($cliente->getRegistroById($_POST['ClaveCliente'])) {
        echo "<option value=\"" . $cliente->getClaveCliente() . "\">" . $cliente->getNombreRazonSocial() . "</option>";
        return;
    }
    echo "<option value=\"\">Selecciona el cliente</option>";
}else if(isset ($_POST['ClaveCliente']) && isset ($_POST['contactos'])){
    include_once("../../Classes/Contacto.class.php");
    $obj = new Contacto();
    $result = $obj->getTodosContactosCliente($_POST['ClaveCliente']);
    echo "<option value=\"null\">Selecciona el contacto</option>";
    while ($rsContacto = mysql_fetch_array($result)) {
        echo "<option value='".$rsContacto['IdContacto']."'>".$rsContacto['Nombre']." (".$rsContacto['TipoContacto'].")</option>";        
    }    
}else if(isset ($_GET['FiltroCliente']) && isset($_GET['palabra'])) {
    $consulta = "SELECT ClaveCliente, NombreRazonSocial FROM c_cliente WHERE NombreRazonSocial LIKE '%".$_GET['palabra']."%' AND Activo = 1;";
    $array_clientes = array();
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        $array_aux = array();
        $array_aux['id'] = $rs['ClaveCliente'];
        $array_aux['label'] = $rs['NombreRazonSocial'];
        $array_aux['value'] = $rs['NombreRazonSocial'];
        array_push($array_clientes, $array_aux);
    }
    echo json_encode(array_values($array_clientes));
    
}else if(isset ($_POST['usuario']) && isset ($_POST['negocios_propios'])){
    $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial
        FROM `k_usuarionegocio` AS kun
        LEFT JOIN c_usuario AS u ON u.IdUsuario = kun.IdUsuario
        LEFT JOIN c_cliente AS c ON kun.ClaveCliente = c.ClaveCliente
        WHERE c.Activo = 1 AND u.IdUsuario = ".$_POST['usuario']."
        GROUP BY c.ClaveCliente;";
    $result = $catalogo->obtenerLista($consulta);
    while($rs = mysql_fetch_array($result)){
        echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . "</option>";
    }
} else if (isset($_POST['IdTipoSolicitud']) && isset($_POST['TipoSolicitudUnico'])) {
    $consulta = "SELECT IdTipoMovimiento, Nombre FROM `c_tiposolicitud` WHERE Activo = 1 AND IdTipoMovimiento = " . $_POST['IdTipoSolicitud'] . ";";
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        echo "<option value=\"" . $rs['IdTipoMovimiento'] . "\">" . $rs['Nombre'] . "</option>";
        return;
    }
    echo "<option value=\"\">Selecciona el tipo de solicitud</option>";
} else if (isset($_POST['IdUsuario']) && isset($_POST['UsuarioUnico'])) {
    include_once("../../Classes/Usuario.class.php");
    $usuario = new Usuario();
    if ($usuario->getRegistroById($_POST['IdUsuario'])) {
        echo "<option value=\"" . $usuario->getId() . "\">" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</option>";
        return;
    }
    echo "<option value=\"\">Selecciona el vendedor</option>";
} else if (isset($_POST['ClaveCentroCosto']) && isset($_POST['LocalidadUnico'])) {
    include_once("../../Classes/CentroCosto.class.php");
    $obj = new CentroCosto();
    if ($obj->getRegistroById($_POST['ClaveCentroCosto'])) {
        echo "<option value=\"" . $obj->getClaveCentroCosto() . "\">" . $obj->getNombre() . "</option>";
        return;
    }
    echo "<option value=\"\">Selecciona el vendedor</option>";
} else if (isset($_POST['localidad']) && isset($_POST['equipos'])) {
    $consulta = "SELECT 
    (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
    (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto,         
    cinv.NoSerie AS NoSerie, cinv.NoParteEquipo,	
    c_equipo.Modelo AS Modelo			
    FROM `c_inventarioequipo` AS cinv
    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
    RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
    RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC	
    LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
    WHERE !ISNULL(cinv.NoSerie) 
    HAVING ClaveCentroCosto = '" . $_POST['localidad'] . "' ORDER BY NoSerie DESC;";
    $query = $catalogo->obtenerLista($consulta);
    echo "<option value=\"\">Selecciona un equipo</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['NoSerie'] . "\">" . $rs['NoSerie'] . " / " . $rs['Modelo'] . "</option>";
    }
} else if (isset($_POST['contrato']) && isset($_POST['cc']) && isset($_POST['client']) && isset($_POST['anexo'])) {
    include_once("../../Classes/CentroCosto.class.php");
    include_once("../../Classes/Anexo.class.php");
    $anexo = new Anexo();
    $query = $anexo->getAnexosDeContratoLocalidad($_POST['contrato'], $_POST['cc']);
    //echo "<option value=\"\">Selecciona el anexo</option>";
    if (mysql_num_rows($query) > 0) {
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value=\"" . $rs['IdAnexoClienteCC'] . "\">" . $rs['ClaveAnexoTecnico'] . " / " . $rs['FechaElaboracion'] . "</option>";
        }
        /* $query = $catalogo->obtenerLista("SELECT kacc.IdAnexoClienteCC, kacc.ClaveAnexoTecnico, kacc.CveEspClienteCC, DATE(cat.FechaElaboracion) AS FechaElaboracion, 
          cat.NoContrato FROM k_anexoclientecc AS kacc
          INNER JOIN c_anexotecnico AS cat ON kacc.CveEspClienteCC = '".$_POST['client']."'
          AND kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico AND cat.NoContrato = '".$_POST['contrato']."' AND cat.Activo= 1;");
          if(mysql_num_rows($query)>0){
          while ($rs = mysql_fetch_array($query)) {
          echo "<option value=\"" . $rs['IdAnexoClienteCC'] . "\">" . $rs['ClaveAnexoTecnico'] . " / ".$rs['FechaElaboracion']." Global</option>";
          }
          } */
    } else {
        /* $query = $catalogo->obtenerLista("SELECT kacc.IdAnexoClienteCC, kacc.ClaveAnexoTecnico, kacc.CveEspClienteCC, DATE(cat.FechaElaboracion) AS FechaElaboracion, 
          cat.NoContrato FROM k_anexoclientecc AS kacc INNER JOIN c_anexotecnico AS cat ON kacc.CveEspClienteCC = '".$_POST['client']."'
          AND kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico AND cat.NoContrato = '".$_POST['contrato']."' AND cat.Activo= 1;");
          if(mysql_num_rows($query)>0){
          while ($rs = mysql_fetch_array($query)) {
          echo "<option value=\"" . $rs['IdAnexoClienteCC'] . "\">" . $rs['ClaveAnexoTecnico'] . " / ".$rs['FechaElaboracion']." Global</option>";
          }
          }else{ */
        echo "<option value=\"\">Sin anexos para la localidad en el contrato " . $_POST['contrato'] . "</option>";
        //}
    }
} else if (isset($_POST['contrato']) && isset($_POST['anexo'])) {
    $query = $catalogo->obtenerLista("SELECT ClaveAnexoTecnico, DATE(FechaElaboracion) AS FechaElaboracion FROM `c_anexotecnico` WHERE NoContrato = '" . $_POST['contrato'] . "' AND Activo = 1;");
    //echo "<option value=\"\">Selecciona el anexo</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveAnexoTecnico'] . "\">" . $rs['ClaveAnexoTecnico'] . " / " . $rs['FechaElaboracion'] . "</option>";
    }
} else if (isset($_POST['cliente']) && isset($_POST['anexos'])) {
    $consulta = "SELECT DISTINCT(ClaveAnexoTecnico) AS ClaveAnexoTecnico FROM `c_contrato` AS ctt
    INNER JOIN c_anexotecnico AS cat ON ctt.ClaveCliente = '" . $_POST['cliente'] . "' AND ctt.Activo = 1 AND ctt.FechaTermino >= NOW() AND ctt.NoContrato = cat.NoContrato AND cat.Activo = 1;";
    $query = $catalogo->obtenerLista($consulta);
    if (isset($_POST['todos'])) {
        echo "<option value=\"\">Todos los anexos</option>";
    } else {
        echo "<option value=\"\">Selecciona el anexo</option>";
    }
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveAnexoTecnico'] . "\" >" . $rs['ClaveAnexoTecnico'] . "</option>";
    }
} else if (isset($_POST['anexo']) && isset($_POST['idAnexoClienteCC'])) {
    $query = $catalogo->obtenerLista("SELECT IdAnexoClienteCC, CveEspClienteCC, (SELECT CASE WHEN !ISNULL(cc.ClaveCentroCosto) THEN cc.Nombre ELSE c.NombreRazonSocial END) AS ClienteCC, Fecha 
        FROM `k_anexoclientecc` AS kacc 
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = kacc.CveEspClienteCC
        WHERE kacc.ClaveAnexoTecnico = '" . $_POST['anexo'] . "';");
    echo "<option value=\"\">Selecciona la localidad</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['IdAnexoClienteCC'] . "\">" . $rs['ClienteCC'] . "</option>";
    }
} else if (isset($_POST['idAnexoClienteCC']) && isset($_POST['catalogo_servicios']) && isset($_POST['anexo_completo'])) {
    include_once("../../Classes/ServicioIM.class.php");
    $servicio = new ServicioIM();
    $hay_opciones = false;
    $query = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'], "im");
    
    echo "<option value=\"\">Selecciona el servicio de la partida</option>";
    if (mysql_num_rows($query) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value=\"" . $rs['IdServicioIM'] . "-" . $rs['IdKServicioIM'] . "\">"
                    . "[" . $rs['IdKServicioIM'] . "] " . $rs['Nombre'] . " ".$servicio->escribirServicioAbreviado($rs, "im")."</option>";
        }
    }

    $query2 = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'], "gim");
    if (mysql_num_rows($query2) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query2)) {
            echo "<option value=\"" . $rs['IdServicioGIM'] . "-" . $rs['IdKServicioGIM'] . "\">"
                    . "[" . $rs['IdKServicioGIM'] . "] " . $rs['Nombre'] . " ".$servicio->escribirServicioAbreviado($rs, "gim")."</option>";
        }
    }

    $query = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'], "fa");
    if (mysql_num_rows($query) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value=\"" . $rs['IdServicioFA'] . "-" . $rs['IdKServicioFA'] . "\">"
                    . "[" . $rs['IdKServicioFA'] . "] " . $rs['Nombre'] . " ".$servicio->escribirServicioAbreviado($rs, "fa")."</option>";
        }
    }

    $query = $servicio->getServiciosAnexoByIdAnexoClienteCC($_POST['idAnexoClienteCC'], "gfa");
    if (mysql_num_rows($query) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value=\"" . $rs['IdServicioGFA'] . "-" . $rs['IdKServicioGFA'] . "\">"
                    . "[" . $rs['IdKServicioGFA'] . "] " . $rs['Nombre'] . " ".$servicio->escribirServicioAbreviado($rs, "gfa")."</option>";
        }
    }
    if (!$hay_opciones) {
        echo "<option value=\"\">Sin servicios activos</option>";
    }
} else if (isset($_POST['idAnexoClienteCC']) && isset($_POST['catalogo_servicios'])) {
    include_once("../../Classes/ServicioIM.class.php");
    $servicio = new ServicioIM();
    $hay_opciones = false;
    $query = $servicio->getServiciosIMByIdAnexo($_POST['idAnexoClienteCC']);
    //echo "<option value=\"\">Selecciona el servicio</option>";
    if (mysql_num_rows($query) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value=\"" . $rs['IdServicio'] . "-" . $rs['IdKServicio'] . "\">" . $rs['Nombre'] . "</option>";
        }
    }
    $query2 = $servicio->getServiciosGIMByIdAnexo($_POST['idAnexoClienteCC']);
    if (mysql_num_rows($query2) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query2)) {
            echo "<option value=\"" . $rs['IdServicio'] . "-" . $rs['IdKServicio'] . "\">" . $rs['Nombre'] . "</option>";
        }
    }
    $query = $servicio->getServiciosFAByIdAnexoClienteCC($_POST['idAnexoClienteCC']);

    if (mysql_num_rows($query) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value=\"" . $rs['IdServicio'] . "-" . $rs['IdKServicio'] . "\">" . $rs['Nombre'] . "</option>";
        }
    }

    $query = $servicio->getServiciosGFAByIdAnexoClienteCC($_POST['idAnexoClienteCC']);
    if (mysql_num_rows($query) > 0) {
        $hay_opciones = true;
        while ($rs = mysql_fetch_array($query)) {
            echo "<option value=\"" . $rs['IdServicio'] . "-" . $rs['IdKServicio'] . "\">" . $rs['Nombre'] . "</option>";
        }
    }
    if (!$hay_opciones) {
        echo "<option value=\"\">Sin servicios activos</option>";
    }
} else if (isset($_POST['IdAnexoClienteCC']) && isset($_POST['servicio'])) {
    $query = $catalogo->obtenerLista("SELECT * FROM  k_anexoclientecc AS kacc
    LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
    LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
    LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
    LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
    WHERE kacc.IdAnexoClienteCC = " . $_POST['IdAnexoClienteCC'] . ";");
    echo "<option value=\"\">Selecciona el IdAnexo</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['IdAnexoClienteCC'] . "\">" . $rs['ClienteCC'] . "</option>";
    }
} else if (isset($_POST['cliente']) && isset($_POST['is_suspendido'])) {
    include_once("../../Classes/Cliente.class.php");
    include_once("../../Classes/Parametros.class.php");
    $cliente = new Cliente();
    $cliente->getRegistroById($_POST['cliente']);
    $parametros = new Parametros();
    $valor = "0";
    if ($parametros->getRegistroById("14")) {
        $valor = $parametros->getValor();
    }
    if ($cliente->getSuspendido() == "1" || ($cliente->getIdEstatusCobranza() == "2" && $valor == "0")) {
        echo "true";
    } else {
        echo "false";
    }
} else if (isset($_POST['parte_modelo'])) {
    $query = $catalogo->obtenerLista("SELECT Modelo FROM `c_equipo` WHERE NoParte = '" . $_POST['parte_modelo'] . "';");
    if ($rs = mysql_fetch_array($query)) {
        echo $rs['Modelo'];
    } else {
        echo "";
    }
} else if (isset($_POST['id_solicitud'])) {
    $query = $catalogo->obtenerLista("SELECT c.ClaveCliente FROM c_solicitud AS s INNER JOIN c_cliente AS c ON s.id_solicitud = " . $_POST['id_solicitud'] . " AND s.ClaveCliente = c.ClaveCliente;");
    if ($rs = mysql_fetch_array($query)) {
        echo $rs['ClaveCliente'];
    } else {
        echo "";
    }
} else if (isset($_POST['almacen']) && isset($_POST['NoParte'])) {
    $query = $catalogo->obtenerLista("SELECT NoSerie, k_almacenequipo.NoParte, c_equipo.Modelo 
    FROM `k_almacenequipo` 
    LEFT JOIN c_equipo ON c_equipo.NoParte = k_almacenequipo.NoParte
    WHERE k_almacenequipo.id_almacen = " . $_POST['almacen'] . " AND k_almacenequipo.NoParte = '" . $_POST['NoParte'] . "' AND (ISNULL(k_almacenequipo.Apartado) OR k_almacenequipo.Apartado<>1);");
    echo "<option value=\"\">Selecciona el No. de Serie</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['NoSerie'] . "\">" . $rs['NoSerie'] . " - " . $rs['Modelo'] . "</option>";
    }
} else if (isset($_POST['idTipoComponente']) && isset($_POST['NoParteEquipo'])) {
    $query = $catalogo->obtenerLista("SELECT c.NoParte, c.IdTipoComponente, c.Modelo, c.Descripcion, cc.NoParteEquipo, cc.Soportado 
    FROM c_componente AS c
    LEFT JOIN k_equipocomponentecompatible AS cc ON c.NoParte = cc.NoParteComponente
    WHERE c.IdTipoComponente = " . $_POST['idTipoComponente'] . " AND (cc.NoParteEquipo = '" . $_POST['NoParteEquipo'] . "' OR ISNULL(NoParteEquipo));");
    echo "<option value=\"\">Selecciona el modelo</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['NoParte'] . "\" >" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
    }
} else if (isset($_POST['tipoServicio']) && isset($_POST['campoID'])) {
    $consulta = "SELECT " . $_POST['campoID'] . ", Nombre FROM `" . $_POST['tipoServicio'] . "` WHERE Activo = 1;";
    $query = $catalogo->obtenerLista($consulta);
    echo "<option value=\"\">Selecciona el servicio</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs[$_POST['campoID']] . "\" >" . $rs['Nombre'] . "</option>";
    }
} else if (isset($_POST['cen_costo']) && isset($_POST['cliente'])) {
    $consulta = "SELECT id_cc, nombre FROM c_cen_costo WHERE ClaveCliente = '" . $_POST['cliente'] . "' ORDER BY nombre;";
    $query = $catalogo->obtenerLista($consulta);
    echo "<option value=\"\">Todos los centro de costo</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['id_cc'] . "\" >" . $rs['nombre'] . "</option>";
    }
} else if (isset($_POST['centro_costo']) && isset($_POST['localidad'])) {
    $consulta = "SELECT ClaveCentroCosto, Nombre FROM `c_centrocosto` WHERE id_cr = " . $_POST['centro_costo'] . " ORDER BY Nombre;";
    $query = $catalogo->obtenerLista($consulta);
    echo "<option value=\"\">Todas las localidades</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveCentroCosto'] . "\" >" . $rs['Nombre'] . "</option>";
    }
} else if (isset($_POST['cc']) && isset($_POST['anexo_particular'])) {
    $consulta = "SELECT DISTINCT(cat.ClaveAnexoTecnico) AS ClaveAnexoTecnico
    FROM `k_anexoclientecc` AS kacc
    INNER JOIN c_anexotecnico AS cat ON kacc.CveEspClienteCC = '" . $_POST['cc'] . "' AND kacc.ClaveAnexoTecnico = cat.ClaveAnexoTecnico AND cat.Activo = 1
    INNER JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato AND ctt.Activo = 1 AND ctt.FechaTermino >= NOW();";
    $query = $catalogo->obtenerLista($consulta);
    if (isset($_POST['todos'])) {
        echo "<option value=\"\">Todos los anexos</option>";
    } else {
        echo "<option value=\"\">Selecciona el anexo</option>";
    }
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveAnexoTecnico'] . "\" >" . $rs['ClaveAnexoTecnico'] . "</option>";
    }
} else if (isset($_POST['cliente']) && isset($_POST['toner_solicitado'])) {
    $consulta = "SELECT
	DISTINCT(cc.ClaveCentroCosto) AS ClaveCentroCosto, cc.Nombre	
        FROM k_nota_refaccion nr, c_notaticket nt, c_ticket t, c_componente c, c_cliente cl, c_estado e, c_centrocosto cc
        WHERE t.IdTicket = nt.IdTicket AND nt.IdNotaTicket = nr.IdNotaTicket AND nr.NoParteComponente = c.NoParte AND t.ClaveCliente = cl.ClaveCliente
        AND t.ClaveCentroCosto = cc.ClaveCentroCosto AND nt.IdEstatusAtencion = e.IdEstado AND (nt.IdEstatusAtencion = 65 OR nt.IdEstatusAtencion = 20) 
        AND nr.Cantidad <> 0 AND c.IdTipoComponente = 2 AND t.ClaveCliente = '" . $_POST['cliente'] . "' AND t.EstadoDeTicket<>4 AND t.EstadoDeTicket<>2
        ORDER BY cc.Nombre;";
    $query = $catalogo->obtenerLista($consulta);
    if (isset($_POST['todos'])) {
        echo "<option value=\"\">Todas las localidades</option>";
    } else {
        echo "<option value=\"\">Selecciona la localidad</option>";
    }
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveCentroCosto'] . "\">" . $rs['Nombre'] . "</option>";
    }
} else if (isset($_POST['OrdenCompraTipoComponente'])) {//Hugo
    $query = $catalogo->obtenerLista("SELECT tc.IdTipoComponente,tc.Nombre FROM c_tipocomponente tc WHERE tc.ACtivo=1 ORDER BY tc.Nombre ASC");
    echo "<option value=\"0\">Selecciona una opción</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['IdTipoComponente'] . "\">" . $rs['Nombre'] . "</option>";
    }
} else if (isset($_POST['componentesOC']) && isset($_POST['tipoComponente'])) {
    $prov = $_POST['proveedor'];
    $tipo = $_POST['tipoComponente'];
    if ($tipo == "7") {//servicios
        $consulta = "SELECT  c.NoParte,CONCAT(c.Modelo,' // ',c.NoParte,' // ',c.Descripcion) AS componente FROM k_proveedorservicio ps INNER JOIN c_componente c ON ps.IdServicio=c.NoParte WHERE ps.IdProveedor='$prov'";
    } else if ($tipo == "8") {//productos
        $consulta = "SELECT  c.NoParte,CONCAT(c.Modelo,' // ',c.NoParte,' // ',c.Descripcion) AS componente FROM k_proveedorproducto ps INNER JOIN c_componente c ON ps.IdProducto=c.NoParte WHERE ps.IdProveedor='$prov'";
    } else {
        $consulta = "SELECT c.NoParte,CONCAT(c.Modelo,' // ',c.NoParte,' // ',c.Descripcion) AS componente FROM  c_componente c WHERE c.IdTipoComponente='" . $tipo . "' AND c.Activo=1 ORDER BY componente ASC";
    }
    $query = $catalogo->obtenerLista($consulta);
    $arrarResultado = array();
    while ($rs = mysql_fetch_array($query)) {
        $arrarResultado[] = array('Componente' => $rs['componente']);
    }
    echo json_encode($arrarResultado);
} else if (isset($_POST['SelectEquipo'])) {
    $query = $catalogo->obtenerLista("SELECT e.NoParte,CONCAT(e.Modelo,' / ',e.NoParte,' / ',SUBSTRING(e.Descripcion,1,50)) AS equipo FROM  c_equipo e WHERE e.Activo=1 ORDER BY equipo ASC");
    $arrarResultado = array();
    while ($rs = mysql_fetch_array($query)) {
        $arrarResultado[] = array('Equipo' => $rs['equipo']);
    }
    echo json_encode($arrarResultado);
} else if (isset($_POST['ImportarComponentes'])) {
    $consulta = "SELECT GROUP_CONCAT(nr.IdNotaTicket) AS IdNotaTicket,CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS importComp,SUM(nr.Cantidad) AS cantidad,c.IdTipoComponente,c.PrecioDolares
                FROM c_componente c INNER JOIN k_nota_refaccion nr ON c.NoParte=nr.NoParteComponente INNER JOIN c_notaticket nt ON nr.IdNotaTicket=nt.IdNotaTicket
                WHERE nt.IdEstatusAtencion=20 AND nr.Cantidad>0 AND nr.IdNotaTicket NOT IN (SELECT ki.IdNotaTicket FROM k_importacion_orden_compra ki WHERE (ki.IdOrdenCompra <>'' OR ki.IdOrdenCompra IS NOT NULL)) AND c.Activo=1
                GROUP BY nr.NoParteComponente ORDER BY nr.IdNotaTicket DESC";
    $query = $catalogo->obtenerLista($consulta);
    $arrarResultado = array();
    while ($rs = mysql_fetch_array($query)) {
        $arrarResultado[] = array('tipoComponente' => $rs['IdTipoComponente'], 'noParte' => $rs['importComp'], 'cantidad' => $rs['cantidad'], 'idNotaTicket' => $rs['IdNotaTicket'], 'precio' => $rs['PrecioDolares']);
    }
    echo json_encode($arrarResultado);
} else if (isset($_POST['direccionProv'])) {
    $clavePRoveedor = $_POST['prov'];
    $noCliente = "";
    $query = $catalogo->obtenerLista("SELECT p.noClienteProveedor  FROM c_proveedor p WHERE p.ClaveProveedor='$clavePRoveedor'");
    while ($rs = mysql_fetch_array($query)) {
        $noCliente = $rs['noClienteProveedor'];
    }
    echo $noCliente;
} else if (isset($_POST['direccionFacturacion'])) {
    $claveDatos = $_POST['fact'];
    $direccion = "";
    $query = $catalogo->obtenerLista("SELECT CONCAT(df.Calle,' ',df.NoExterior,', ',df.Colonia,', ',df.Delegacion,', ',df.Estado,df.Pais,', ',df.CP) AS domicilio FROM c_datosfacturacionempresa df WHERE df.IdDatosFacturacionEmpresa='$claveDatos'");
    while ($rs = mysql_fetch_array($query)) {
        $direccion = $rs['domicilio'];
    }
    echo $direccion;
} else if (isset($_POST['direccionAlmacen'])) {
    $idAlmacen = $_POST['idAlmacen'];
    $direccion = "";
    $query = $catalogo->obtenerLista("SELECT CONCAT(da.Calle,' ', da.NoExterior,', ',da.Colonia,', ',da.Delegacion,', ',da.Estado,', ',da.CodigoPostal) AS direccion FROM c_almacen a LEFT JOIN c_domicilio_almacen da ON a.id_almacen=da.IdAlmacen  WHERE a.id_almacen='$idAlmacen'");
    while ($rs = mysql_fetch_array($query)) {
        $direccion = $rs['direccion'];
    }
    echo $direccion;
} else if (isset($_POST['precioComponente'])) {
    $precio = "";    
    if (isset($_POST['noParte']) && $_POST['noParte'] != "") {
        $noParte = $_POST['noParte'];
        $query = $catalogo->obtenerLista("SELECT c.PrecioDolares FROM c_componente c WHERE c.NoParte='$noParte'");        
        while ($rs = mysql_fetch_array($query)) {
            $precio = $rs['PrecioDolares'];
        }
    }
    if ($precio == "") {
        echo $precio = 0;
    } else {
        echo $precio;
    }
} else if (isset($_POST['precioEquipo'])) {
    $precio = "";
    if (isset($_POST['auxequipo']) && $_POST['auxequipo'] != "") {
        $noParte = $_POST['auxequipo'];
        $query = $catalogo->obtenerLista("SELECT e.PrecioDolares FROM c_equipo e WHERE e.NoParte='$noParte'");
        while ($rs = mysql_fetch_array($query)) {
            $precio = $rs['PrecioDolares'];
        }
    }
    if ($precio == "") {
        echo $precio = 0;
    } else {
        echo $precio;
    }
} else if (isset($_POST['noParteComponenteRendimiento']) && $_POST['noParteComponenteRendimiento'] != "") {
    $noParte = $_POST['noParteComponenteRendimiento'];
    $rendimiento = "0";
    $query = $catalogo->obtenerLista("SELECT (c.Rendimiento*1) AS rendimiento FROM c_componente c WHERE c.NoParte='$noParte'");
    while ($rs = mysql_fetch_array($query)) {
        $rendimiento = $rs['rendimiento'];
    }
    echo $rendimiento;
} else if (isset($_POST['porcRendimiento'])) {
    $porcentaje = 0;
    $query = $catalogo->obtenerLista("SELECT p.Valor FROM c_parametro p WHERE p.IdParametro=5");
    while ($rs = mysql_fetch_array($query)) {
        $porcentaje = $rs['Valor'];
    }
    echo $porcentaje;
} else if (isset($_POST['ColorComponenteToner'])) {
    $tipo = "";
    $noParte = $_POST['ColorComponenteToner'];
    $query = $catalogo->obtenerLista("SELECT c.IdColor FROM c_componente c WHERE c.NoParte='$noParte'");
    while ($rs = mysql_fetch_array($query)) {
        $tipo = $rs['IdColor'];
    }
    echo $tipo;
} else if (isset($_POST['cargarContadoresPorColor'])) {
    $color = $_POST['color'];
    $serie = $_POST['serie'];
    $arrarResultado = array();
    $query = $catalogo->obtenerLista("SELECT (CASE WHEN !ISNULL(lt.Fecha) THEN lt.Fecha ELSE t.FechaHora END) AS Fecha,lt.ContadorBN AS ContadorBN,lt.ContadorCL AS ContadorCL,lt.ContadorBNA AS ContadorBNML,
                                            lt.ContadorCLA AS ContadorCLML,lt.NivelTonNegro AS NivelTonNegro,lt.NivelTonCian AS NivelTonCian,lt.NivelTonMagenta AS NivelTonMagenta,
                                            lt.NivelTonAmarillo AS NivelTonAmarillo
                                            FROM c_lecturasticket lt INNER JOIN c_ticket t ON t.IdTicket =(SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 LEFT JOIN c_notaticket AS nt  ON nt.IdNotaTicket = 
                                            (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket) WHERE lt.fk_idticket = t2.IdTicket AND t2.TipoReporte = 15 AND t2.EstadoDeTicket <> 4 
                                            AND (nt.IdEstatusAtencion <> 59 OR ISNULL(nt.IdEstatusAtencion))) INNER JOIN c_notaticket nt3 ON nt3.IdTicket=t.IdTicket 
                                            INNER JOIN k_nota_refaccion nr ON nt3.IdNotaTicket=nr.IdNotaTicket INNER JOIN c_componente c ON c.NoParte=nr.NoParteComponente aND c.IdColor=$color
                                            WHERE lt.ClvEsp_Equipo='$serie' ORDER BY t.IdTicket DESC LIMIT 0,1");
    while ($rs = mysql_fetch_array($query)) {
        $arrarResultado[] = array('fecha' => $rs['Fecha'], 'contadornegro' => $rs['ContadorBN'], 'contadorcolor' => $rs['ContadorCL'], 'nivelnegro' => $rs['NivelTonNegro'], 'nivelcia' => $rs['NivelTonCian'], 'nivelmagenta' => $rs['NivelTonMagenta'], 'nivelamarillo' => $rs['NivelTonAmarillo']);
    }
    echo json_encode($arrarResultado);
} else if (isset($_POST['NoSerie']) && isset($_POST['cliente'])) {
    include_once("../../Classes/Inventario.class.php");
    $inventario = new Inventario();
    $result = $inventario->getDatosDeInventario($_POST['NoSerie']);
    if (mysql_num_rows($result) == 0) {//No se encuentra el equipo en inventario
        echo "Error: el equipo " . $_POST['NoSerie'] . " no está en cliente";
        return;
    } else {
        while ($rs = mysql_fetch_array($result)) {//Recorremos inventario
            if (isset($_POST['tfs'])) {//Verificar si el usuario es TFS y si el cliente le pertenece
                include_once("../../Classes/Usuario.class.php");
                $usuario = new Usuario();
                if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)) {//El usuario es TFS
                    include_once("../../Classes/TFSCliente.class.php");
                    $tfs = new TFSCliente();
                    $result2 = $tfs->getRegistroById($_SESSION['idUsuario'], $rs['ClaveCliente']);
                    if (mysql_num_rows($result2) == 0) {
                        echo "Error: el equipo pertenece al cliente " . $rs['NombreCliente'] . " asignado a otro TFS";
                        return;
                    }
                }
            }
            if (isset($_POST['minialmacen'])) {//Verificar si la localidad tiene mini-almacen
                $result2 = $catalogo->obtenerLista("SELECT IdClienteLocalidad FROM `k_minialmacenlocalidad` WHERE ClaveCentroCosto = '" . $rs['ClaveCentroCosto'] . "';");
                if (mysql_num_rows($result2) == 0) {//No tiene minialmacén.
                    echo "Error: la localidad " . $rs['CentroCostoNombre'] . " del equipo " . $_POST['NoSerie'] . " no tiene mini-almacén";
                    return;
                }
            }
            $arrarResultado[] = array('ClaveCliente' => $rs['ClaveCliente'], 'NombreCliente' => $rs['NombreCliente'],
                'ClaveCentroCosto' => $rs['ClaveCentroCosto'], 'CentroCostoNombre' => $rs['CentroCostoNombre'],
                'NoParteEquipo' => $rs['NoParteEquipo'], 'Modelo' => $rs['Modelo']);
        }
        echo json_encode($arrarResultado);
    }
} else if (isset($_POST['tfs']) && isset($_POST['cliente'])) {
    $result = $catalogo->obtenerLista("SELECT u.IdUsuario, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS TFS FROM `k_tfscliente` AS ktfs
        INNER JOIN c_usuario AS u ON u.IdUsuario = ktfs.IdUsuario
        WHERE ktfs.ClaveCliente = '" . $_POST['cliente'] . "' AND Tipo = 1;");
    if (mysql_num_rows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            echo "<option value=\"" . $rs['IdUsuario'] . "\" selected='selected'>" . $rs['TFS'] . "</option>";
        }
    } else {
        echo "<option value=\"\">Sin TFS</option>";
    }
} else if (isset($_POST['cliente'])) {
    $query = $catalogo->obtenerLista("SELECT ClaveCentroCosto,Nombre FROM `c_centrocosto` WHERE ClaveCliente = '" . $_POST['cliente'] . "' ORDER BY Nombre;");
    if (isset($_POST['todos'])) {
        echo "<option value=\"\">Todas las localidades</option>";
    } else {
        echo "<option value=\"\">Selecciona la localidad</option>";
    }
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveCentroCosto'] . "\">" . $rs['Nombre'] . "</option>";
    }
} else if (isset($_POST['proveedor']) && isset($_POST['sucursales'])) {
    $query = $catalogo->obtenerLista("SELECT ClaveSucursal, Descripcion FROM c_sucursal WHERE ClaveProveedor = '" . $_POST['proveedor'] . "' AND Activo = 1 ORDER BY Descripcion;");
    if (isset($_POST['todos'])) {
        echo "<option value=\"\">Todas las sucursales</option>";
    } else {
        echo "<option value=\"\">Selecciona la sucursal</option>";
    }
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveSucursal'] . "\">" . $rs['Descripcion'] . "</option>";
    }
}//hugo
else if (isset($_POST['slct']) && $_POST['slct'] == "sl_sucursal") {
    $proveedor = $_POST['id'];
    $query = $catalogo->obtenerLista("SELECT sp.id_prov_sucursal,sp.NombreComercial AS nombre FROM k_proveedorsucursal sp WHERE sp.ClaveProveedor='$proveedor' ORDER BY sp.NombreComercial ASC");
    echo "<option value=\"0\">Selecciona una opción</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['id_prov_sucursal'] . "\">" . $rs['nombre'] . "</option>";
    }
} else if (isset($_POST['slct']) && $_POST['slct'] == "sl_zona") {
    $gzona = $_POST['id'];
    $query = $catalogo->obtenerLista("SELECT z.ClaveZona,z.NombreZona FROM c_zona z WHERE z.fk_id_gzona='$gzona' ORDER BY z.NombreZona ASC");
    echo "<option value=\"0\">Selecciona una zona</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['ClaveZona'] . "\">" . $rs['NombreZona'] . "</option>";
    }
} else if (isset($_POST['almacen']) && isset($_POST['inventario'])) {//Muestra los componentes que están en el inventario del almacen seleccionado
    $consulta = "SELECT c.NoParte, c.Modelo, c.Descripcion, a.nombre_almacen, kacc.cantidad_existencia, kacc.cantidad_apartados, kacc.CantidadMinima, kacc.CantidadMaxima 
        FROM `k_almacencomponente` AS kacc
        LEFT JOIN c_componente AS c ON kacc.NoParte = c.NoParte
        LEFT JOIN c_almacen AS a ON a.id_almacen = kacc.id_almacen
        WHERE a.id_almacen = " . $_POST['almacen'] . " AND c.Activo = 1 AND a.Activo = 1
        GROUP BY c.NoParte, a.id_almacen
        ORDER BY a.nombre_almacen, Modelo;";
    $query = $catalogo->obtenerLista($consulta);
    echo "<option value=\"\">Selecciona el componente</option>";
    while ($rs = mysql_fetch_array($query)) {
        echo "<option value=\"" . $rs['NoParte'] . "\">" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
    }
} else if (isset($_POST['tipo']) && isset($_POST['multiple'])) {
    $tipos = $_POST['tipo'];
    echo "<option value=\"\">Selecciona el modelo</option>";
    foreach ($tipos as $id) {
        if ($id == 0) {
            $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                c_equipo.Modelo AS Modelo,
                c_equipo.NoParte AS Parte 
                FROM c_equipo WHERE Activo = 1 
                ORDER BY Modelo");
            while ($rsp = mysql_fetch_array($query3)) {
                echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . "</option>";
            }
        } else {
            $query3 = $catalogo->obtenerLista("SELECT DISTINCT
                c_componente.Modelo AS Modelo,
                c_componente.NoParte AS Parte,
                c_componente.Descripcion AS Descripcion
                FROM c_componente
                INNER JOIN c_tipocomponente ON c_tipocomponente.IdTipoComponente=c_componente.IdTipoComponente
                WHERE c_componente.IdTipoComponente=" . $id . " AND c_componente.Activo = 1
                ORDER BY Modelo");
            while ($rsp = mysql_fetch_array($query3)) {
                echo "<option value=\"" . $rsp['Parte'] . "\" >" . $rsp['Modelo'] . " / " . $rsp['Parte'] . " / " . $rsp['Descripcion'] . "</option>";
            }
        }
    }
} else if(isset($_POST['FlujoFalla']) && $_POST['FlujoFalla'] == 1){
    echo "<option value = 'tfs' $s>Enviar a usuarios TFS </option>";
    $queryUsuario = "";
    if(isset($_POST['agregar']) && strcmp($_POST['agregar'], "clientes") == 0){
        echo "<option value= 'cl' >Correos envío facturacion cliente</option>";
    }else if(isset($_POST['agregar']) && strcmp($_POST['agregar'], "contactos") == 0){
        $query = $catalogo->getListaAlta("c_tipocontacto", "Nombre");
        while ($rs = mysql_fetch_array($query)) {
            $s = "";
            echo "<option value= 'co" . $rs['IdTipoContacto'] . "' " . $s . ">Contacto tipo: " . $rs['Nombre'] . "</option>";
        }
    }else if(isset($_POST['agregar']) && strcmp($_POST['agregar'], "basico") == 0){
        if(isset($_POST['area']) && $_POST['area'] != 0){
            $queryU = "SELECT u.Nombre, u.IdUsuario, u.Loggin, u.correo FROM c_estado e 
                LEFT JOIN k_areapuesto AS kap ON kap.IdEstado = e.IdEstado 
                LEFT JOIN c_usuario AS u ON kap.IdPuesto = u.IdPuesto WHERE e.IdArea = ".$_POST['area'].
                " GROUP BY u.IdUsuario";
            $queryUsuario = $catalogo->obtenerLista($queryU);
        }else{
            $queryUsuario = $catalogo->getListaAlta("c_usuario", "Nombre");
        }
        while ($rsUsuario = mysql_fetch_array($queryUsuario)) {
            if((!empty($rsUsuario['correo'])))
            {    
                $s = "";
                echo "<option value= 'us" . $rsUsuario['IdUsuario'] . "' " . $s . ">" .$rsUsuario['Loggin']."-". $rsUsuario['correo'] . "</option>";
            }
        }
    }else if(isset($_POST['agregar']) && strcmp($_POST['agregar'], "basicoContactos") == 0){
        if(isset($_POST['area']) && $_POST['area'] != 0){
            $queryU = "SELECT u.Nombre, u.IdUsuario, u.Loggin, u.correo FROM c_estado e 
                LEFT JOIN k_areapuesto AS kap ON kap.IdEstado = e.IdEstado 
                LEFT JOIN c_usuario AS u ON kap.IdPuesto = u.IdPuesto WHERE e.IdArea = ".$_POST['area'].
                " GROUP BY u.IdUsuario";
            $queryUsuario = $catalogo->obtenerLista($queryU);
        }else{
            $queryUsuario = $catalogo->getListaAlta("c_usuario", "Nombre");
        }
        while ($rsUsuario = mysql_fetch_array($queryUsuario)) {
            if((!empty($rsUsuario['correo'])))
            {    
                $s = "";
                echo "<option value= 'us" . $rsUsuario['IdUsuario'] . "' " . $s . ">" .$rsUsuario['Loggin']."-". $rsUsuario['correo'] . "</option>";
            }
        }
        $query = $catalogo->getListaAlta("c_tipocontacto", "Nombre");
        while ($rs = mysql_fetch_array($query)) {
            $s = "";
            echo "<option value= 'co" . $rs['IdTipoContacto'] . "' " . $s . ">Contacto tipo: " . $rs['Nombre'] . "</option>";
        }
    }
    else if(isset($_POST['agregar']) && strcmp($_POST['agregar'], "basicoClientes") == 0){
        if(isset($_POST['area']) && $_POST['area'] != 0){
            $queryU = "SELECT u.Nombre, u.IdUsuario, u.Loggin, u.correo FROM c_estado e 
                LEFT JOIN k_areapuesto AS kap ON kap.IdEstado = e.IdEstado 
                LEFT JOIN c_usuario AS u ON kap.IdPuesto = u.IdPuesto WHERE e.IdArea = ".$_POST['area'].
                " GROUP BY u.IdUsuario";
            $queryUsuario = $catalogo->obtenerLista($queryU);
        }else{
            $queryUsuario = $catalogo->getListaAlta("c_usuario", "Nombre");
        }
        while ($rsUsuario = mysql_fetch_array($queryUsuario)) {
            if((!empty($rsUsuario['correo'])))
            {    
                $s = "";
                echo "<option value= 'us" . $rsUsuario['IdUsuario'] . "' " . $s . ">" .$rsUsuario['Loggin']."-". $rsUsuario['correo'] . "</option>";
            }
        }
        echo "<option value= 'cl' >Correos envío facturacion cliente</option>";
    }else if(isset($_POST['agregar']) && strcmp($_POST['agregar'], "todo") == 0){
        if(isset($_POST['area']) && $_POST['area'] != 0){
            $queryU = "SELECT u.Nombre, u.IdUsuario, u.Loggin, u.correo FROM c_estado e 
                LEFT JOIN k_areapuesto AS kap ON kap.IdEstado = e.IdEstado 
                LEFT JOIN c_usuario AS u ON kap.IdPuesto = u.IdPuesto WHERE e.IdArea = ".$_POST['area'].
                " GROUP BY u.IdUsuario";
            $queryUsuario = $catalogo->obtenerLista($queryU);
        }else{
            $queryUsuario = $catalogo->getListaAlta("c_usuario", "Nombre");
        }
        while ($rsUsuario = mysql_fetch_array($queryUsuario)) {
            if((!empty($rsUsuario['correo'])))
            {    
                $s = "";
                echo "<option value= 'us" . $rsUsuario['IdUsuario'] . "' " . $s . ">" .$rsUsuario['Loggin']."-". $rsUsuario['correo'] . "</option>";
            }
        }
        $query = $catalogo->getListaAlta("c_tipocontacto", "Nombre");
        while ($rs = mysql_fetch_array($query)) {
            $s = "";
            echo "<option value= 'co" . $rs['IdTipoContacto'] . "' " . $s . ">Contacto tipo: " . $rs['Nombre'] . "</option>";
        }
        echo "<option value= 'cl' >Correos envío facturacion cliente</option>";
    }
}else if (isset($_POST['noParteComponenteRendimiento2']) && $_POST['noParteComponenteRendimiento2'] != "") {
    $noParte = $_POST['noParteComponenteRendimiento2'];
    $contadorAnterior = "0";
    $rendimiento = "0";
    $query = $catalogo->obtenerLista("SELECT (c.Rendimiento*1) AS rendimiento FROM c_componente c WHERE c.NoParte='$noParte'");
    while ($rs = mysql_fetch_array($query)) {
        $rendimiento = $rs['rendimiento'];
    }
    $queryLecturaAnterior = "SELECT lt.ContadorBN FROM k_nota_refaccion nr 
        INNER JOIN c_notaticket AS nt ON nr.IdNotaTicket = nt.IdNotaTicket
        INNER JOIN c_ticket AS t ON t.IdTicket = nt.IdTicket
        INNER JOIN c_lecturasticket AS lt ON t.IdTicket = lt.fk_idticket
        WHERE nr.NoParteComponente = '".$_POST['noParteComponenteRendimiento2']."' AND t.NoSerieEquipo = '".$_POST['NoSerieEquipo']."'";
    $query2 = $catalogo->obtenerLista($queryLecturaAnterior);
    while ($rs2 = mysql_fetch_array($query2)) {
        $contadorAnterior = $rs2['ContadorBN'];
    }
    echo $contadorAnterior."//".$rendimiento;
}else if(isset($_POST['idCliente']) && $_POST['idCliente'] != "" && isset($_POST['pantalla']) && $_POST['pantalla'] == "HS"){
    $consulta = "SELECT NoContrato,DATE_FORMAT(FechaInicio,'%Y-%m-%d') AS FechaInicio,DATE_FORMAT(FechaTermino,'%Y-%m-%d')
                 AS FechaTermino FROM c_contrato WHERE Activo = 1 AND ClaveCliente = '".$_POST['idCliente']."' ORDER BY NoContrato ASC;";
    $query = $catalogo->obtenerLista($consulta);
    echo "<option value=''>Selecciona una opción</option>";
    while ($row = mysql_fetch_array($query)) {
        echo "<option value='".$row['NoContrato']."'>".$row['NoContrato']."-(".$row['FechaInicio'].$row['FechaTermino'].")</option>";
    }
}else if(isset ($_POST['idContrato']) && $_POST['idContrato'] != "" && isset ($_POST['pantalla']) && $_POST['pantalla'] == "HS"){
    $consulta = "SELECT ClaveAnexoTecnico FROM c_anexotecnico WHERE Activo = 1 AND NoContrato = '".$_POST['idContrato']."';";
    $query = $catalogo->obtenerLista($consulta);
    echo "<option value=''>Selecciona una opción</option>";
    while ($row1 = mysql_fetch_array($query)) {
        echo "<option value='".$row1['ClaveAnexoTecnico']."'>".$row1['ClaveAnexoTecnico']."</option>";
    }
}



    