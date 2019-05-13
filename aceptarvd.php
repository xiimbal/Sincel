<?php

echo "Favor de crear la venta directa desde solicitudes de equipo";
return;
exit;

if (!isset($_GET['clv']) || !isset($_GET['vd']) || !isset($_GET['tipo']) || !isset($_GET['flujo'])) {
    header("Location: index.php");
}

if(!isset($_GET['uguid'])){
    /*echo "La liga no está completa, favor de comunicarlo a soporte.";
    return;*/
    $empresa = 1;//Temporalmente, se toma por default la empresa 1, que es genesis.
}else{
    $empresa = $_GET['uguid'];
}

include_once("WEB-INF/Classes/VentaDirecta.class.php");
include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/Usuario.class.php");
include_once("WEB-INF/Classes/Parametros.class.php");
include_once("WEB-INF/Classes/Solicitud.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");

$parametroGlobal = new ParametroGlobal();
$obj = new VentaDirecta();
$modificacion = "";
$urlextra = "";
if (isset($_GET['vd']) && $_GET['vd'] != "") {
    $catalogo = new Catalogo();
    $existe = $catalogo->obtenerLista("SELECT * FROM c_ventadirecta WHERE IdVentaDirecta='" . $_GET['vd'] . "'");
    if ($sr = mysql_fetch_array($existe)) {
        if ($_GET['flujo'] == "vd") {
            if (isset($_GET['mod']) && $_GET['mod'] == 1) {
                $modificacion = "(Modificada) ";
                $urlextra = "&mod=1";
            }
            $obj->setId($_GET['vd']);
            if ($obj->Autorizar_vd($_GET['tipo'], $_GET['clv'])) {
                echo "El estatus de la venta directa fue actualizado correctamente";
                if ($_GET['tipo'] == "1") {//Si la venta estuvo autorizada.            
                    $idventadirecta = $_GET['vd'];
                    $catalogo = new Catalogo();
                    //enviamos el correo
                    $query3 = $catalogo->obtenerLista("SELECT c_ventadirecta.ClaveCliente AS ClaveCliente,
                    c_ventadirecta.Clave_Localidad AS Localidad,
                    c_ventadirecta.Fecha AS Fecha,
                    c_ventadirecta.Estatus AS Estatus,
                    k_ventadirectadet.Cantidad AS Cantidad,
                    k_ventadirectadet.TipoProducto AS Tipo,
                    k_ventadirectadet.IdProduto AS IdProduto,
                    k_ventadirectadet.Costo AS Costo,
                    CASE WHEN k_ventadirectadet.TipoProducto=0 THEN (SELECT c_equipo.Modelo FROM c_equipo WHERE c_equipo.NoParte=k_ventadirectadet.IdProduto)
                    ELSE (SELECT c_componente.Modelo FROM c_componente WHERE c_componente.NoParte=k_ventadirectadet.IdProduto) END AS Modelo,
                    c_cliente.EjecutivoCuenta AS EjecutivoCuenta,
                    c_cliente.NombreRazonSocial AS Cliente
                    FROM c_ventadirecta
                    INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta=c_ventadirecta.IdVentaDirecta
                    INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_ventadirecta.ClaveCliente
                    WHERE c_ventadirecta.IdVentaDirecta=" . $idventadirecta);

                    //$cliente = "";
                    $texto = "<table border=\"1\">"; //primera tabla
                    $texto .="<thead><tr><th>Cantidad</th><th>No Parte</th><th>Modelo</th><th>Costo</th><th>Total</th></tr></thead><tbody>";
                    $total = 0;
                    while ($rs = mysql_fetch_array($query3)) {
                        $texto .= "<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['IdProduto'] . "</td><td>" . $rs['Modelo'] . "</td><td>$" . number_format($rs['Costo']) . "</td><td>$" . number_format($rs['Cantidad'] * $rs['Costo']) . "</td></tr>";
                        $total+=$rs['Cantidad'] * $rs['Costo'];
                        //echo "<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Nombre'] . "</td></tr>";
                    }
                    $texto .= "<tr><td></td><td></td><td></td><td></td><td>$" . number_format($total) . "</td></tr>";
                    $texto .= "</tbody></table><br/>"; //fin de la primera tabla                    
                    //segunda tabla
                    mysql_data_seek($query3, 0);
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
                    mysql_data_seek($query3, 0);
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
                            WHERE c_equipo.Modelo = '" . $rs['Modelo'] . "'
                            AND k_almacenequipo.Apartado = 1 GROUP BY Cliente,CentroCosto");
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
                    mysql_data_seek($query3, 0);
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
                    mysql_data_seek($query3, 0);
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
                    $usuario->getRegistroById($rs['EjecutivoCuenta']);
                    $message .= "<h3>Hay una solicitud " . $modificacion . "del tipo de venta directa del usuario:</h3><h4>" . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</h4><br/>";
                    $message .= "<h3>Para el cliente:</h3><h4>" . $rs['Cliente'] . "</h4>";
                    /* Obtenemos los correos a quien mandaremos el mail */
                    //$query4 = $catalogo->obtenerLista("SELECT correo FROM c_usuario WHERE IdPuesto=27 AND Activo = 1;");
                    $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 10;");
                    $correos = array();
                    $z = 0;
                    while ($rs = mysql_fetch_array($query4)) {
                        $correos[$z] = $rs['correo'];
                        $z++;
                    }
                    $correos[$z++] = "ronaldomagg@msn.com";
                    $correos[$z++] = "romeo@techra.com.mx";
                    $correos[$z] = "surieches@gmail.com";
                    $message .= $texto;
                    /* Guardamos y creamos la liga para aceptar/rechazar la solicitud directamente */
                    $parametros = new Parametros();
                    $parametros->getRegistroById(8);
                    $clave = $mail->generaPass();
                    $liga = $parametros->getDescripcion() . "/aceptarvd.php?clv=$clave&vd=$idventadirecta&flujo=alm&tipo";

                    $catalogo->insertarRegistro("UPDATE c_ventadirecta SET c_ventadirecta.clave_aut_alm='" . $clave . "' WHERE c_ventadirecta.IdVentaDirecta='" . $idventadirecta . "'");

                    $message = $message . "<br/>Autorizar solicitud: " . $liga . "=1" . $urlextra . " <br/><br/>";
                    $message = $message . "<br/>Rechazar solicitud: " . $liga . "=0" . $urlextra . " <br/><br/>";
                    $message = $message . "<br/>Para editar la solicitud, ingrese al sistema por favor:  " . $_SESSION['ip_server'];
                    $message .= "</body></html>";
                    $mail->setBody($message);
                    foreach ($correos as $value) {
                        if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                            $mail->setTo($value);
                            if ($mail->enviarMail() == "1") {
                                echo "";
                            } else {
                                echo "Error: No se pudo enviar el correo para autorizar.";
                            }
                        }
                    }

                    /* mysql_data_seek($query3, 0);
                      if ($rs = mysql_fetch_array($query3)) {
                      $id_solicitud = $catalogo->insertarRegistro("INSERT INTO c_solicitud(id_crea,id_autoriza,comentario_creo,estatus,ClaveCliente,fecha_solicitud,id_tiposolicitud,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                      VALUES('" . $rs['EjecutivoCuenta'] . "','2','',0,'" . $rs['ClaveCliente'] . "',NOW(),'6',1,'kyocera',NOW(),'kyocera',NOW(),'PHP aceptarvd.php');");
                      mysql_data_seek($query3, 0);
                      $contador = 1;
                      while ($rs = mysql_fetch_array($query3)) {
                      $query2 = $catalogo->obtenerLista("INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,ClaveCentroCosto,Modelo,tipo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                      VALUES('" . $id_solicitud . "','" . $contador . "','" . $rs['Cantidad'] . "','" . $rs['Localidad'] . "','" . $rs['IdProduto'] . "','" . $rs['Tipo'] . "','kyocera',NOW(),'kyocera',NOW(),'PHP aceptarvd.php');");
                      $contador++;
                      }
                      }
                      $catalogo->insertarRegistro("UPDATE c_ventadirecta SET id_solicitud=" . $id_solicitud . " WHERE IdVentaDirecta=" . $idventadirecta); */
                }
            } else {
                echo "Hubo un error al actualizar, vuelva a intentarlo por favor";
            }
        } elseif ($_GET['flujo'] == "alm") {
            $obj->setId($_GET['vd']);
            if ($obj->Autorizar_alm($_GET['tipo'], $_GET['clv'])) {
                if ($obj->getBandera() == "1") {
                    $catalogo = new Catalogo();
                    $idventadirecta = $_GET['vd'];
                    $query3 = $catalogo->obtenerLista("SELECT c_ventadirecta.ClaveCliente AS ClaveCliente,
                c_ventadirecta.Clave_Localidad AS Localidad,
		c_ventadirecta.Fecha AS Fecha,
		c_ventadirecta.Estatus AS Estatus,
                c_ventadirecta.id_solicitud AS id_solicitud,
		k_ventadirectadet.Cantidad AS Cantidad,
		k_ventadirectadet.TipoProducto AS Tipo,
		k_ventadirectadet.IdProduto AS IdProduto,
		k_ventadirectadet.Costo AS Costo,
                CASE WHEN k_ventadirectadet.TipoProducto=0 THEN (SELECT c_equipo.Modelo FROM c_equipo WHERE c_equipo.NoParte=k_ventadirectadet.IdProduto)
                ELSE (SELECT c_componente.Modelo FROM c_componente WHERE c_componente.NoParte=k_ventadirectadet.IdProduto) END AS Modelo,
                c_cliente.EjecutivoCuenta AS EjecutivoCuenta,
                c_cliente.NombreRazonSocial AS Cliente,
                c_cliente.ClaveCliente AS ClaveCliente
                FROM c_ventadirecta
                INNER JOIN k_ventadirectadet ON k_ventadirectadet.IdVentaDirecta=c_ventadirecta.IdVentaDirecta
                INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_ventadirecta.ClaveCliente
                WHERE c_ventadirecta.IdVentaDirecta=" . $idventadirecta);

                    if ($rs = mysql_fetch_array($query3)) {
                        $id_solicitud = $catalogo->insertarRegistro("INSERT INTO c_solicitud(id_crea,id_autoriza,comentario_creo,estatus,ClaveCliente,fecha_solicitud,id_tiposolicitud,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $rs['EjecutivoCuenta'] . "','2','',1,'" . $rs['ClaveCliente'] . "',NOW(),'6',1,'kyocera',NOW(),'kyocera',NOW(),'PHP aceptarvd.php');");
                        mysql_data_seek($query3, 0);
                        $contador = 1;
                        while ($rs = mysql_fetch_array($query3)) {
                            $query2 = $catalogo->obtenerLista("INSERT INTO k_solicitud(id_solicitud,id_partida,cantidad,cantidad_autorizada,ClaveCentroCosto,Modelo,tipo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $id_solicitud . "','" . $contador . "','" . $rs['Cantidad'] . "','" . $rs['Cantidad'] . "','" . $rs['Localidad'] . "','" . $rs['IdProduto'] . "','" . $rs['Tipo'] . "','kyocera',NOW(),'kyocera',NOW(),'PHP aceptarvd.php');");
                            $contador++;
                        }
                        /* Se autoriza la solicitud de equipo */
                        /* $solicitud = new Solicitud();
                          $solicitud->setId_solicitud($rs['id_solicitud']);
                          $solicitud->cambiarEstatusSolicitud("1"); */
                    }
                    $catalogo->insertarRegistro("UPDATE c_ventadirecta SET id_solicitud=" . $id_solicitud . " WHERE IdVentaDirecta=" . $idventadirecta);
                }
                echo "El estatus de la venta directa fue actualizado correctamente";
            } else {
                echo "Error: No se pudo enviar el correo para autorizar.";
            }
        } else {
            header("Location: index.php?msj=ClaveNotFound");
        }
    } else {
        echo "La venta directa ya no existe, fue borrada.";
    }
} else {
    echo "No existe la venta directa.";
}
?>
