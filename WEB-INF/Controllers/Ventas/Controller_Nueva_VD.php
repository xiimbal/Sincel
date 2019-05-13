<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../../Classes/Catalogo.class.php");
include_once("../../Classes/Mail.class.php");
include_once("../../Classes/Usuario.class.php");
include_once("../../Classes/Cliente.class.php");
include_once("../../Classes/Componente.class.php");
include_once("../../Classes/Equipo.class.php");
include_once("../../Classes/ParametroGlobal.class.php");
$parametroGlobal = new ParametroGlobal();

$modificacion = "";
$urlextra = "";
if (isset($_POST['numero'])) {    
    $urlextra = "&mod=1";
    $numero = $_POST['numero'];
    if (isset($_POST['form'])) {
        $parametros = "";
        parse_str($_POST['form'], $parametros);
        $catalogo = new Catalogo();
        if (isset($parametros['NoVenta'])) {
            $modificacion = "(Modificada) ";
            $query1 = $catalogo->obtenerLista("SELECT
                    c_ventadirecta.IdVentaDirecta AS ID,
                    k_ventadirectadet.IdVentaDirectaDet AS IDK
                    FROM c_ventadirecta
                    INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta = c_ventadirecta.IdVentaDirecta
                    WHERE c_ventadirecta.IdVentaDirecta =" . $parametros['NoVenta']);
            $idventadirecta = $parametros['NoVenta'];
            $num_rows = mysql_num_rows($query1);
            $i = 1;
            $rs;
            for (; $i < $numero; $i++) {
                if ($i <= $num_rows) {
                    $precio = 0;
                    if ($parametros['costo' . $i] == "none") {
                        $precio = $parametros['costotro' . $i];
                    } else {
                        $precio = $parametros['costo' . $i];
                    }
                    $rs = mysql_fetch_array($query1);
                    if ($parametros['tipo' . $i] == 0) {
                        $query2 = $catalogo->obtenerLista("UPDATE k_ventadirectadet SET  Cantidad='" . $parametros['numero' . $i] . "',TipoProducto='" . $parametros['tipo' . $i] . "',IdProduto='" . $parametros['modelo' . $i] . "',Costo='" . $precio . "',UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW() WHERE k_ventadirectadet.IdVentaDirectaDet='" . $rs['IDK'] . "'");
                    } else {
                        $query2 = $catalogo->obtenerLista("UPDATE k_ventadirectadet SET  Cantidad='" . $parametros['numero' . $i] . "',TipoProducto='1',IdProduto='" . $parametros['modelo' . $i] . "',Costo='" . $precio . "',UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW() WHERE k_ventadirectadet.IdVentaDirectaDet='" . $rs['IDK'] . "'");
                    }
                } else {
                    if ($parametros['tipo' . $i] == 0) {
                        $query2 = $catalogo->obtenerLista("INSERT INTO k_ventadirectadet(Cantidad,TipoProducto,IdProduto,Costo,IdVentaDirecta,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                            VALUES('" . $parametros['numero' . $i] . "','" . $parametros['tipo' . $i] . "','" . $parametros['modelo' . $i] . "','" . $precio . "','" . $rs['ID'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Nueva_Venta_Directa')");
                    } else {
                        $query2 = $catalogo->obtenerLista("INSERT INTO k_ventadirectadet(Cantidad,TipoProducto,IdProduto,Costo,IdVentaDirecta,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                            VALUES('" . $parametros['numero' . $i] . "','1','" . $parametros['modelo' . $i] . "','" . $precio . "','" . $rs['ID'] . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Nueva_Venta_Directa')");
                    }
                }
            }
            if ($num_rows >= $i) {
                for (; $i <= $num_rows; $i++) {
                    $rs = mysql_fetch_array($query1);
                    $catalogo->obtenerLista("DELETE FROM k_ventadirectadet WHERE k_ventadirectadet.IdVentaDirectaDet=" . $rs['IDK']);
                }
            }
            $query2 = $catalogo->obtenerLista("UPDATE c_ventadirecta SET Fecha='" . $parametros['Fecha'] . "',ClaveCliente='" . $parametros['cliente'] . "',Clave_Localidad='" . $parametros['localidad'] . "',Estatus='1',UsuarioUltimaModificacion='" . $_SESSION['user'] . "',FechaUltimaModificacion=NOW(),autorizada_vd=null,autorizada_alm=null WHERE c_ventadirecta.IdVentaDirecta='" . $rs['ID'] . "'");
        } else {
            $idventadirecta = $catalogo->insertarRegistro("INSERT INTO c_ventadirecta(Fecha,ClaveCliente,Clave_Localidad,Estatus,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $parametros['Fecha'] . "','" . $parametros['cliente'] . "','" . $parametros['localidad'] . "','1','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Nueva_Venta_Directa');");            
            for ($i = 1; $i < $numero; $i++) {
                $precio = 0;
                if ($parametros['costo' . $i] == "none") {
                    $precio = $parametros['costotro' . $i];
                } else {
                    $precio = $parametros['costo' . $i];
                }
                if ($parametros['tipo' . $i] == 0) {
                    $query2 = $catalogo->obtenerLista("INSERT INTO k_ventadirectadet(Cantidad,TipoProducto,IdProduto,Costo,IdVentaDirecta,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        VALUES('" . $parametros['numero' . $i] . "','" . $parametros['tipo' . $i] . "','" . $parametros['modelo' . $i] . "','" . $precio . "','" . $idventadirecta . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Nueva_Venta_Directa')");
                } else {
                    $query2 = $catalogo->obtenerLista("INSERT INTO k_ventadirectadet(Cantidad,TipoProducto,IdProduto,Costo,IdVentaDirecta,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        VALUES('" . $parametros['numero' . $i] . "','1','" . $parametros['modelo' . $i] . "','" . $precio . "','" . $idventadirecta . "','" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'PHP Nueva_Venta_Directa')");
                }
            }
        }

        //enviamos el correo
        $consulta = "SELECT c_ventadirecta.ClaveCliente AS ClaveCliente,
		c_ventadirecta.Fecha AS Fecha,
		c_ventadirecta.Estatus AS Estatus,
		k_ventadirectadet.Cantidad AS Cantidad,
		k_ventadirectadet.TipoProducto AS Tipo,
		k_ventadirectadet.IdProduto AS IdProduto,
		k_ventadirectadet.Costo AS Costo,
                CASE WHEN k_ventadirectadet.TipoProducto=0 THEN 
                (SELECT c_equipo.Modelo FROM c_equipo WHERE c_equipo.NoParte=k_ventadirectadet.IdProduto)
                ELSE (SELECT c_componente.Modelo FROM c_componente WHERE c_componente.NoParte=k_ventadirectadet.IdProduto) END AS Modelo,
                c_cliente.EjecutivoCuenta AS EjecutivoCuenta,
                c_cliente.NombreRazonSocial AS Cliente,
                c_cliente.IdEstatusCobranza,
                c_cliente.RFC
                FROM c_ventadirecta
                INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta=c_ventadirecta.IdVentaDirecta
                INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_ventadirecta.ClaveCliente
                WHERE c_ventadirecta.IdVentaDirecta=" . $idventadirecta;
        $query3 = $catalogo->obtenerLista($consulta);
        
        $texto = "<table border=\"1\">"; //primera tabla
        $texto .="<thead><tr><th>Cantidad</th><th>No Parte</th><th>Modelo</th><th>Costo</th><th>Total</th></tr></thead><tbody>";
        $total = 0;
        /*while ($rs = mysql_fetch_array($query3)) {
            $texto .= "<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['IdProduto'] . "</td><td>" . $rs['Modelo'] . "</td><td>$" . number_format($rs['Costo']) . "</td><td>$" . number_format($rs['Cantidad'] * $rs['Costo']) . "</td></tr>";
            $total+= ((int)$rs['Cantidad'] * (float)$rs['Costo']);
            //echo "<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Nombre'] . "</td></tr>";
        }*/
        for ($i = 1; $i < $numero; $i++) {
            $precio = 0;
            if ($parametros['costo' . $i] == "none") {
                $precio = $parametros['costotro' . $i];
            } else {
                $precio = $parametros['costo' . $i];
            }
            
            if ($parametros['tipo' . $i] == 0) {//Si es equipo
                $obj = new Equipo();
                $obj->getRegistroById($parametros['modelo'.$i]);
            }else{//Si es componente
                $obj = new Componente();
                $obj->getRegistroById($parametros['modelo'.$i]);
            }
            
            $texto .= "<tr><td>" . (int)$parametros['numero' . $i] . "</td><td>" . $parametros['modelo' . $i] . "</td>
                <td>" . $obj->getModelo() . "</td><td>$" . $precio . "</td><td>$" . number_format($parametros['numero' . $i] * $precio) . "</td></tr>";
            $total+= ((int)$parametros['numero' . $i] * (float)$precio);
        }
        $texto .= "<tr><td></td><td></td><td></td><td></td><td>$" . number_format($total) . "</td></tr>";
        $texto .= "</tbody></table><br/>"; //fin de la primera tabla
        //
        //segunda tabla
        $query3 = $catalogo->obtenerLista($consulta);
        $tabla2 = "<h4>Precios base:</h4><br/><br/><table border=\"1\">";
        $tabla2 .="<thead><tr><th>Modelo</th><th>Precio A</th><th>Precio B</th><th>Precio C</th></tr></thead><tbody>";
        $tb2 = false;
        while ($rs = mysql_fetch_array($query3)) {
            if ($rs['Tipo'] == 0) {
                $query4 = $catalogo->obtenerLista("SELECT FORMAT(c_precios_abc.Precio_A,2) AS Precio_A,FORMAT(c_precios_abc.Precio_B,2) AS Precio_B,FORMAT(c_precios_abc.Precio_C,2) AS Precio_C FROM c_equipo
                    INNER JOIN c_precios_abc ON c_precios_abc.Id_precio_abc=c_equipo.Id_precios_abc
                    WHERE c_equipo.NoParte='" . $rs['IdProduto'] . "'");
                if ($rsp = mysql_fetch_array($query4)) {
                    $tabla2 .="<tr><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Precio_A'] . "</td><td>" . $rsp['Precio_B'] . "</td><td>" . $rsp['Precio_C'] . "</td></tr>";
                    $tb2 = true;
                }
            } else {
                $query4 = $catalogo->obtenerLista("SELECT FORMAT(c_precios_abc.Precio_A,2) AS Precio_A,FORMAT(c_precios_abc.Precio_B,2) AS Precio_B,FORMAT(c_precios_abc.Precio_C,2) AS Precio_C FROM c_componente
                        INNER JOIN c_precios_abc ON c_precios_abc.Id_precio_abc=c_componente.Id_precios_abc
                        WHERE c_componente.NoParte='" . $rs['IdProduto'] . "'");
                if ($rsp = mysql_fetch_array($query4)) {
                    $tabla2 .="<tr><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Precio_A'] . "</td><td>" . $rsp['Precio_B'] . "</td><td>" . $rsp['Precio_C'] . "</td></tr>";
                    $tb2 = true;
                }
            }
            //echo "<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Nombre'] . "</td></tr>";
        }
        $tabla2 .= "</tbody></table><br/>"; //fin de tabla 2
        if ($tb2) {
            $texto.=$tabla2;
        }
        //apartados
        $query3 = $catalogo->obtenerLista($consulta);
        $texto3 = "<h4>Apartados:</h4>";
        $texto3.= "<table border=\"1\"><thead><tr><th>Cantidad</th><th>Modelo</th><th>Tipo</th><th>Cliente</th><th>Localidad</th></tr></thead><tbody>";
        $val = true;
        while ($rs = mysql_fetch_array($query3)) {
            if ($rs['Tipo'] == 0) {
                $query4 = $catalogo->obtenerLista("SELECT
                        k_almacenequipo.Apartado AS Apartado,
                        c_centrocosto.Nombre AS CentroCosto,
                        c_cliente.NombreRazonSocial AS Cliente,
                        COUNT(c_centrocosto.Nombre) AS Suma
                        FROM k_almacenequipo
                        INNER JOIN c_equipo ON c_equipo.NoParte = k_almacenequipo.NoParte
                        INNER JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = k_almacenequipo.ClaveCentroCosto
                        INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                        WHERE c_equipo.Modelo = '" . $rs['Modelo'] . "' AND k_almacenequipo.Apartado = 1 GROUP BY Cliente,CentroCosto");
                while ($rsp = mysql_fetch_array($query4)) {
                    $val = false;
                    $texto3 .= "<tr><td>" . $rsp['Suma'] . "</td><td>" . $rs['Modelo'] . "</td><td>Equipo</td><td>" . $rsp['Cliente'] . "</td><td>" . $rsp['CentroCosto'] . "</td></tr>";
                }
            } else {
                $query4 = $catalogo->obtenerLista("SELECT
                k_almacencomponente.cantidad_apartados  AS Apartado,
                k_almacencomponente.cantidad_existencia AS Existencias
                FROM k_almacencomponente
                INNER JOIN c_componente ON c_componente.NoParte = k_almacencomponente.NoParte
                WHERE c_componente.Modelo='" . $rs['Modelo'] . "' AND k_almacencomponente.cantidad_apartados!=0");
                while ($rsp = mysql_fetch_array($query4)) {
                    $val = false;
                    $texto3 .= "<tr><td>" . $rsp['Apartado'] . "</td><td>" . $rs['Modelo'] . "</td><td>Componente</td><td></td><td></td></tr>";
                }
            }
        }
        if ($val) {
            $texto3 = "";
        }
        //existencias
        $query3 = $catalogo->obtenerLista($consulta);
        $texto1 = "<h4>Existencias antes de autorizar:</h4><br/>";
        $texto1 .= "<table border=\"1\"><thead><tr><th>Cantidad</th><th>Modelo</th></tr></thead><tbody>";
        $texto2 = "<h4>Existencias después de autorizar:</h4><br/>";
        $texto2 .= "<table border=\"1\"><thead><tr><th>Cantidad</th><th>Modelo</th></tr></thead><tbody>";
        $modelo_equipo = Array();
        $modelo_compo = Array();
        $cantidad_equipo = Array();
        $cantidad_compo = Array();
        $contador_equipo = 0;
        $contador_compo = 0;
        $val = true;
        while ($rs = mysql_fetch_array($query3)) {
            if ($rs['Tipo'] == 0) {
                $query4 = $catalogo->obtenerLista("SELECT COUNT(*) AS Cuenta FROM k_almacenequipo 
                INNER JOIN c_equipo ON c_equipo.NoParte=k_almacenequipo.NoParte
                WHERE c_equipo.Modelo='" . $rs['Modelo'] . "' AND (k_almacenequipo.Apartado!=1 OR ISNULL(k_almacenequipo.Apartado))");
                $rsp = mysql_fetch_array($query4);
                $texto1 .= "<tr><td>" . $rsp['Cuenta'] . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                $cantidad = $rsp['Cuenta'] - $rs['Cantidad'];
                if ($contador_equipo == 0) {
                    $modelo_equipo[$contador_equipo] = $rs['Modelo'];
                    $cantidad_equipo[$contador_equipo] = $cantidad;
                } else {
                    $i = 0;
                    foreach ($modelo_equipo as $value) {
                        if ($value == $rs['Modelo']) {
                            $cantidad = $cantidad_equipo[$i] - $rs['Cantidad'];
                        }
                        $i++;
                    }
                    $modelo_equipo[$contador_equipo] = $rs['Modelo'];
                    $cantidad_equipo[$contador_equipo] = $cantidad;
                }
                $contador_equipo++;
                $texto2 .="<tr><td>" . $cantidad . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                $val = false;
            } else {
                $query4 = $catalogo->obtenerLista("SELECT
                (k_almacencomponente.cantidad_existencia-k_almacencomponente.cantidad_apartados)  AS Cuenta
                FROM k_almacencomponente
                INNER JOIN c_componente ON c_componente.NoParte = k_almacencomponente.NoParte
                WHERE c_componente.Modelo='" . $rs['Modelo'] . "'");
                $rsp = mysql_fetch_array($query4);
                if ($rsp['Cuenta'] != null) {
                    $cuenta = $rsp['Cuenta'];
                } else {
                    $cuenta = "0";
                }
                $texto1 .= "<tr><td>" . $cuenta . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                $cantidad = 0;
                if ($rsp['Cuenta'] == null) {
                    $cantidad = (-1) * $rs['Cantidad'];
                } else {
                    $cantidad = $rsp['Cuenta'] - $rs['Cantidad'];
                }
                if ($contador_compo == 0) {
                    $modelo_compo[$contador_compo] = $rs['Modelo'];
                    $cantidad_compo[$contador_compo] = $cantidad;
                } else {
                    $i = 0;
                    foreach ($modelo_compo as $value) {
                        if ($value == $rs['Modelo']) {
                            $cantidad = $cantidad_compo[$i] - $rs['Cantidad'];
                        }
                        $i++;
                    }
                    $modelo_compo[$contador_compo] = $rs['Modelo'];
                    $cantidad_compo[$contador_compo] = $cantidad;
                }
                $contador_compo++;
                $texto2 .="<tr><td>" . $cantidad . "</td><td>" . $rs['Modelo'] . "</td></tr>";
                $val = false;
            }
        }
        $texto1 .= "</tbody></table><br/>";
        $texto2 .= "</tbody></table><br/>";
        if ($val) {
            $texto1 = "";
            $texto2 = "";
        }
        if ($texto3 != "") {
            $texto3 .= "</tbody></table><br/>";
        }
        $query3 = $catalogo->obtenerLista($consulta);
        $texto .= $texto1 . $texto3 . "<br/>" . "<br/>" . $texto2;
        $rs = mysql_fetch_array($query3);
        $mail = new Mail();
        if($parametroGlobal->getRegistroById("8")){
            $mail->setFrom($parametroGlobal->getValor());
        }else{
            $mail->setFrom("scg-salida@scgenesis.mx");
        }
        $mail->setSubject("No Venta Directa: " . $idventadirecta);
        $message = "<html><body>";
        $usuario = new Usuario();
        $message .= "<h3>Hay una solicitud " . $modificacion . "del tipo de venta directa </h3>"; 
        if($usuario->getRegistroById($parametros['vendedor'])){
            $message .= "del usuario:<h4>" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno()."</h4>";
        }
        $message .= "<br/>";
        $cliente = new Cliente();
        $cliente->getRegistroById($parametros['cliente']);
        //$message .= "<h3>Para el cliente:</h3><h4>" . $rs['Cliente'] . "</h4>";
        if($rs['IdEstatusCobranza'] != "2" || !isset($rs['IdEstatusCobranza'])){
            $message .= "<h3>Para el cliente:</h3><h4>" . $cliente->getNombreRazonSocial() . "</h4>";
        }else{
            include_once("../../Classes/ReporteFacturacion.class.php");
            $facturas = new ReporteFacturacion();
            $facturas->setRfccliente($rs['RFC']);
            $facturas->setStatus(1);/*Para que muestre solo las facturas no pagadas*/
            $result3 = $facturas->getTabla(false);
            if(mysql_num_rows ($result3) > 0){
                $facturas_pendientes = "<table border=\"1\"><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Total</th></tr></thead><tbody>";
                while($rs3 = mysql_fetch_array($result3)){                        
                    $facturas_pendientes.= "<tr>";
                    $facturas_pendientes.= "<td align='center' scope='row'>" . $rs3['Folio'] . "</td>";
                    $facturas_pendientes.= "<td align='center' scope='row'>" . $rs3['FechaFacturacion'] . "</td>";                        
                    $facturas_pendientes.= "<td align='center' scope='row'>$" . $rs3['Total'] . "</td>";                        
                    $facturas_pendientes.= "</tr>";            
                }
                $facturas_pendientes.="</tbody></table>";
                $message .= "<h3>Para el <font color=red>cliente moroso:</font></h3><h4>" .$cliente->getNombreRazonSocial(). "</h4><b>Con las facturas pendientes:</b><br/>$facturas_pendientes<br/><br/>";                        
            }else{
                $message .= "<h3>Para el <font color=red>cliente moroso:</font></h3><h4>" .$cliente->getNombreRazonSocial(). "</h4>";
            }
        }
        /* Obtenemos los correos a quien mandaremos el mail */
        $query4 = $catalogo->obtenerLista("SELECT correo_vd FROM `c_correovd` WHERE Activo = 1;");
        $correos = array();
        $z = 0;
        while ($rs = mysql_fetch_array($query4)) {
            $correos[$z] = $rs['correo_vd'];
            $z++;
        }
        $message .= $texto;
        /* Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente */
        $clave = $mail->generaPass();
        $liga = $_SESSION['ip_server'] . "/aceptarvd.php?clv=$clave&vd=$idventadirecta&flujo=vd&tipo";

        $catalogo->insertarRegistro("UPDATE c_ventadirecta SET c_ventadirecta.clave_aut_vd='" . $clave . "' WHERE c_ventadirecta.IdVentaDirecta='" . $idventadirecta . "'");

        $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1&uguid=".$_SESSION['idEmpresa']."" . $urlextra . " <br/><br/>";
        $message = $message . "<br/>Rechazar solicitud: " . $liga . "=0&uguid=".$_SESSION['idEmpresa']."" . $urlextra . " <br/><br/>";
        $message = $message . "<br/>Para editar la solicitud, ingrese al sistema por favor: " . $_SESSION['ip_server'];
        $message .= "</body></html>";
        $mail->setBody($message);
        foreach ($correos as $value) {
            if(isset($value) && $value!="" && filter_var($value, FILTER_VALIDATE_EMAIL)){/*Si el correo es valido*/
                $mail->setTo($value);
                if ($mail->enviarMail() == "1") {
                    echo "Un correo fue enviado para la autorización.";
                } else {
                    echo "Error: No se pudo enviar el correo para autorizar.";
                }
            }
        }
    }
}
?>
