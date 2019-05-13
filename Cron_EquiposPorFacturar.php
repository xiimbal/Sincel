<?php

header('Content-Type: text/html; charset=utf-8');
set_time_limit(0);
include_once("WEB-INF/Classes/Catalogo.class.php");
include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("WEB-INF/Classes/ConexionMultiBD.class.php");
include_once("WEB-INF/Classes/ReporteLectura.class.php");
include_once("WEB-INF/Classes/PeriodoSinFacturar.class.php");
include_once("WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("WEB-INF/Classes/ServicioGeneral.class.php");
include_once("WEB-INF/Classes/Parametros.class.php");
include_once("WEB-INF/Classes/Lectura.class.php");
include_once("WEB-INF/Classes/Mail.class.php");
include_once("WEB-INF/Classes/ParametroGlobal.class.php");
include_once("WEB-INF/Classes/Factura2.class.php");

$con = new ConexionMultiBD();
$result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
$con->Desconectar();
$usuario = "Cron_EquiposPorFacturar";
$pantalla = "Cron_EquiposPorFacturar";
while ($rs_multi = mysql_fetch_array($result_bases)) {
    echo "<br/><br/>Procesando empresa " . $rs_multi['nombre_empresa'];
    $empresa = $rs_multi['id_empresa'];
    $catalogo = new Catalogo();
    $catalogo->setEmpresa($empresa);
    $catalogoFacturacion = new CatalogoFacturacion();
    $catalogoFacturacion->setEmpresa($empresa);
    $caracteristica = new EquipoCaracteristicasFormatoServicio();
    $caracteristica->setEmpresa($empresa);
    $reporte = new ReporteLectura();
    $prefijos = array("gim", "gfa", "im", "fa"); //Prefijos a revisar (siempre se toman las prioridades: gim, gfa, im, fa)    
    $periodo = new PeriodoSinFacturar();
    $periodo->setEmpresa($empresa);
    $parametros = new Parametros();
    $parametros->setEmpresa($empresa);
    $lectura = new Lectura();
    $lectura->setEmpresa($empresa);
    $mail = new Mail();
    $parametroGlobal = new ParametroGlobal();
    $parametroGlobal->setEmpresa($empresa);
    $factura = new Factura();
    $factura->setEmpresa($empresa);

    if ($parametroGlobal->getRegistroById("8")) {
        $mail->setFrom($parametroGlobal->getValor());
    } else {
        $mail->setFrom("scg-salida@scgenesis.mx");
    }

    //Se obtiene el mes siguiente
    $result = $catalogo->obtenerLista("SELECT DATE(DATE_ADD(NOW(),INTERVAL 1 MONTH)) AS Fecha;");
    while($rs = mysql_fetch_array($result)){
        $fecha = split("-", $rs['Fecha']);
        $next_mes = $fecha[1];
        $next_anio = $fecha[0];
        
    }
    $mes = date('m');
    $anio = date('Y');
    //$dia = date('j');    
    $dia = 10;

    $periodo->insertarNuevoPeriodo($next_mes, $next_anio, $usuario, $pantalla);

    $dias_mes = cal_days_in_month(CAL_GREGORIAN, $next_mes, $anio);
    $dias_extras = " AND DAY(kacc.Fecha) = DAY('$anio-$next_mes-$dia') ";
    if ($dias_mes < 31 && $dia == $dias_mes) {
        $dias_extras = " AND ( DAY(kacc.Fecha) = DAY('$anio-$next_mes-$dia') ";
        for ($i = date('j') + 1; $i <= 31; $i++) {
            $dias_extras .= " OR DAY(kacc.Fecha) = $i ";
        }
        $dias_extras .= ")";
    }
    /* Obtenemos todos los clientes */
    $consulta = "SELECT MIN(IdAnexoClienteCC) AS IdAnexoClienteCC, kacc.Fecha AS FechaCorte, DAY(kacc.Fecha), 
        c.ClaveCliente, c.NombreRazonSocial, c.RFC 
        FROM c_cliente AS c
        LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = (SELECT MIN(NoContrato) FROM c_contrato WHERE ClaveCliente = c.ClaveCliente AND ctt.Activo = 1 AND ctt.FechaTermino >= NOW())
        LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = (SELECT MIN(ClaveAnexoTecnico) FROM c_anexotecnico WHERE NoContrato = ctt.NoContrato)
        LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = cat.ClaveAnexoTecnico)
        WHERE c.Activo = 1 AND c.Modalidad <> 3 $dias_extras 
        GROUP BY c.ClaveCliente ORDER BY FechaCorte, c.NombreRazonSocial;";    
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if ($factura->tieneFacturaMultiPeriodo($rs['RFC'], $anio . "-" . $next_mes . "-01")) {//Verificamos si el cliente no tiene factura multiperiodo vigente
            continue;
        }
        for ($tipo_domicilio = 0; $tipo_domicilio <= 1; $tipo_domicilio++) {
            $consulta = $reporte->generarConsulta("", "", "", $rs['ClaveCliente'], "", "", true, false, false, false, false, false, $tipo_domicilio);
            $resultEquipos = $catalogo->obtenerLista($consulta);
            while ($rsEquipos = mysql_fetch_array($resultEquipos)) {
                if (!isset($rsEquipos['id_bitacora']) || !isset($rsEquipos['ClaveCliente'])) {
                    continue;
                    ;
                }
                $IdKServicio = "null";
                $IdServicio = "null";
                $RentaMensual = "null";
                $IncluidosBN = "null";
                $IncluidosColor = "null";
                $CostoExcedentesBN = "null";
                $CostoExcedentesColor = "null";
                $CostoProcesadosBN = "null";
                $CostoProcesadosColor = "null";
                $color = $rsEquipos['isColor'];

                foreach ($prefijos as $pref) {//Buscamos que tipo de servicio tiene asocido el equipo                    
                    $pref_minus = strtolower($pref);
                    if (isset($rsEquipos['IdKServicio' . $pref_minus])) { //Si existe el tipo de servicio.                                                        
                        $ServicioGeneral = new ServicioGeneral();
                        $IdKServicio = $rsEquipos['IdKServicio' . $pref_minus];
                        $IdServicio = $rsEquipos['IdServicio' . $pref_minus];
                        if ($ServicioGeneral->getCobranzasByTipoServicio($IdServicio)) {//Guardamos los datos 
                            if ($ServicioGeneral->getCobrarRenta()) {//Si para este equipo se cobra la renta mensual
                                if (isset($rsEquipos[$pref_minus . 'Renta'])) {
                                    $RentaMensual = $rsEquipos[$pref_minus . 'Renta'];
                                }
                            }
                            if ($ServicioGeneral->getCobrarExcedenteBN()) {//Si se cobran los excedentes B/N y el equipo es b/n.   
                                if (isset($rsEquipos[$pref_minus . 'incluidosBN'])) {
                                    $IncluidosBN = $rsEquipos[$pref_minus . 'incluidosBN'];
                                }
                                if (isset($rsEquipos[$pref_minus . 'ExcedentesBN'])) {
                                    $CostoExcedentesBN = $rsEquipos[$pref_minus . 'ExcedentesBN'];
                                }
                            }
                            if ($ServicioGeneral->getCobrarExcedenteColor() && $color == 1) {//Si se cobran los excedentes color y el equipo es a color.
                                if (isset($rsEquipos[$pref_minus . 'incluidosColor'])) {
                                    $IncluidosColor = $rsEquipos[$pref_minus . 'incluidosColor'];
                                }
                                if (isset($rsEquipos[$pref_minus . 'ExcedentesColor'])) {
                                    $CostoExcedentesColor = $rsEquipos[$pref_minus . 'ExcedentesColor'];
                                }
                            }
                            if ($ServicioGeneral->getCobrarProcesadasBN()) {//Si se cobran los procesados B/N y el equipo es b/n.
                                if (isset($rsEquipos[$pref_minus . 'ProcesadasBN'])) {
                                    $CostoProcesadosBN = $rsEquipos[$pref_minus . 'ProcesadasBN'];
                                }
                            }
                            if ($ServicioGeneral->getCobrarProcesadasColor() && $color == 1) {//Si se cobran los procesados color y el equipo es color.
                                if (isset($rsEquipos[$pref_minus . 'ProcesadosColor'])) {
                                    $CostoProcesadosColor = $rsEquipos[$pref_minus . 'ProcesadosColor'];
                                }
                            }
                        }
                        break;
                    }
                }

                if ($periodo->insertarEquipoSinFacturar($periodo->getIdPeriodo(), $rsEquipos['id_bitacora'], $rsEquipos['ClaveCliente'], $color, $IdServicio, $IdKServicio, $RentaMensual, $IncluidosBN, $IncluidosColor, $CostoProcesadosBN, $CostoProcesadosColor, $CostoExcedentesBN, $CostoExcedentesColor, $usuario, $pantalla)) {
                    
                } else {
                    
                }
            }
        }
    }//Termina while        

    /**************************************************** Ejecutar aviso previo y actual de fecha de corte *************************************************** */
    for ($tipo = 0; $tipo < 1; $tipo++) {//Se corre dos veces este procedimiento, uno para avisar n días antes y otro paar avisar el día de corte
        //Solo se termino ejecutando una vez, porque segun el segundo analisis soloe s necesario avisar n dias antes y no el dia de corte
        if ($tipo == 0) {
            if ($parametros->getRegistroById("29")) {
                $dias_avisar = $parametros->getValor();
            } else {
                $dias_avisar = 7;
            }
        } else {
            $dias_avisar = 0;
        }

        $fecha_lectura = "3000-01-01 00:00:00"; //Fecha fin para considerar las lecturas
        $consulta = "SELECT MIN(IdAnexoClienteCC) AS IdAnexoClienteCC, DATE(kacc.Fecha) AS FechaCorte, DAY(kacc.Fecha) AS DiaCorte, 
            MONTH(DATE_ADD('$anio-$mes-$dia', INTERVAL $dias_avisar DAY)) AS MesProximoCorte, c.ClaveCliente, c.RFC,
            c.NombreRazonSocial, u.IdUsuario, (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS ejecutivo, u.correo
            FROM c_cliente AS c 
            LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = (SELECT MIN(NoContrato) FROM c_contrato WHERE ClaveCliente = c.ClaveCliente AND ctt.Activo = 1 AND ctt.FechaTermino >= NOW()) 
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = (SELECT MIN(ClaveAnexoTecnico) FROM c_anexotecnico WHERE NoContrato = ctt.NoContrato) 
            LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = cat.ClaveAnexoTecnico) 
            LEFT JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
            WHERE c.Activo = 1 AND c.Modalidad <> 3 
            AND DAY(kacc.Fecha) = DAY(DATE_ADD('$anio-$mes-$dia', INTERVAL $dias_avisar DAY)) 
            GROUP BY c.ClaveCliente ORDER BY u.IdUsuario, c.NombreRazonSocial;";

        $result = $catalogo->obtenerLista($consulta);
        $usuario_anterior = "";
        $message = "";
        $correo_anterior = "";
        $ejecutivo = "";

        while ($rs = mysql_fetch_array($result)) {
            if ($factura->tieneFacturaMultiPeriodo($rs['RFC'], $anio . "-" . $rs['MesProximoCorte'] . "-01")) {//Verificamos si el cliente no tiene factura multiperiodo vigente
                continue;
            }

            if ($usuario_anterior != "" && $usuario_anterior != $rs['IdUsuario']) {
                if ($tipo == 0) {
                    $mail->setSubject("Aviso de clientes para lecturas de corte $dias_avisar días antes: " . $ejecutivo);
                } else {
                    $mail->setSubject("Aviso de clientes para lecturas en el día de corte: " . $ejecutivo);
                }
                $mail->setBody($message);
                if ($usuario_anterior != "NA") {
                    if (isset($correo_anterior) && $correo_anterior != "" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                        $mail->setTo($correo_anterior);
                        if ($mail->enviarMail() == "1") {
                            echo "<br/>Un correo fue enviado de notificacion de clientes sin lecturas " . $correo_anterior;
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a " . $correo_anterior . ".";
                        }
                    } else {
                        $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                        $mail->setBody($message_ejecutivo_extra . " " . $message);
                    }
                } else {
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    if ($tipo == 0) {
                        $mail->setSubject("Aviso de clientes para lecturas de corte $dias_avisar días antes");
                    } else {
                        $mail->setSubject("Aviso de clientes para lecturas en el día de corte");
                    }
                    $mail->setBody($message_ejecutivo_extra . " " . $message);
                }
                /* Correos para el cron por default */
                $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 16;");
                while ($rs2 = mysql_fetch_array($result2)) {
                    if (isset($rs2['correo']) && $rs2['correo'] != "" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                        $mail->setTo($rs2['correo']);
                        $mail->enviarMail();
                    }
                }
                $message = "";
            }

            if ($lectura->tieneTodasLecturas($rs['ClaveCliente'], $rs['IdTipoFacturacion'], $anio, $mes, $fecha_lectura, true)) {
                $message .= "<br/><br/>*El cliente: " . $rs['NombreRazonSocial'] . " ya tiene todas sus lecturas, ya se puede generar la pre-factura";
            } else {
                if ($tipo == 0) {
                    $message .= "<br/><br/>*El cliente: " . $rs['NombreRazonSocial'] . " tendrá su fecha de corte el día " . $rs['DiaCorte'] . ", 
                        faltan $dias_avisar días para recolectar sus lecturas de corte faltantes: ";
                } else {
                    $message .= "<br/><br/>*El cliente: " . $rs['NombreRazonSocial'] . " tiene hoy su fecha de corte y aún no cuenta con todas las lecturas: ";
                }
                $message .= $lectura->getMensaje();
            }

            if (isset($rs['IdUsuario']) && $rs['IdUsuario'] != "" && $rs['IdUsuario'] != "NA") {
                $usuario_anterior = $rs['IdUsuario'];
                $correo_anterior = $rs['correo'];
                $ejecutivo = $rs['ejecutivo'];
            } else {
                $usuario_anterior = "NA";
                $correo_anterior = "NA";
                $ejecutivo = "NA";
            }
        }

        if ($usuario_anterior != "") {
            if ($tipo == 0) {
                $mail->setSubject("Aviso de clientes para lecturas de corte $dias_avisar días antes: " . $ejecutivo);
            } else {
                $mail->setSubject("Aviso de clientes para lecturas en el día de corte: " . $ejecutivo);
            }
            $mail->setBody($message);
            if ($usuario_anterior != "NA") {
                if (isset($correo_anterior) && $correo_anterior != "" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                    $mail->setTo($correo_anterior);
                    if ($mail->enviarMail() == "1") {
                        echo "<br/>Un correo fue enviado de notificacion de clientes sin lecturas " . $correo_anterior;
                    } else {
                        echo "<br/>Error: No se pudo enviar el correo a " . $correo_anterior . ".";
                    }
                } else {
                    $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                    $mail->setSubject("Aviso de clientes sin lecturas de corte: " . $ejecutivo);
                    $mail->setBody($message_ejecutivo_extra . " " . $message);
                }
            } else {
                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                $mail->setSubject("Aviso de clientes sin lecturas de corte");
                $mail->setBody($message_ejecutivo_extra . " " . $message);
            }
            /* Correos para el cron por default */
            $result2 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 16;");
            while ($rs2 = mysql_fetch_array($result2)) {
                if (isset($rs2['correo']) && $rs2['correo'] != "" && filter_var($rs2['correo'], FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                    $mail->setTo($rs2['correo']);
                    $mail->enviarMail();
                }
            }
        }
    }
}
/* * ************************************************** Ejecutar Facturas Lecturas *************************************************** */
echo "<br/>";
$_GET['independiente'] = "1";
include_once "WEB-INF/Controllers/Controler_FacturasLecturas.php";
?>
