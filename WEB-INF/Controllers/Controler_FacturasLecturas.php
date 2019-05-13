<html>
    <head>
        <!-- JS -->
        <?php
        if (!isset($_GET['independiente'])) {
            echo '<link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />';
            $pagina_cargar = "reportes/ReporteLecturaPDF.php";
			echo '<script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
			<script src="../resources/js/jquery/jquery-ui.min.js"></script>   ';
        } else {
            echo '<link rel="stylesheet" href="resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />';
            $pagina_cargar = "reportes/ReporteLecturaPDF.php";
			echo '<script src="resources/js/jquery/jquery-1.11.3.min.js"></script>
			<script src="resources/js/jquery/jquery-ui.min.js"></script>   ';
        }
        
        
        ?>                        
        <title></title>
    </head>
    <body>
        <?php
        set_time_limit (0);
        if (isset($_POST['sistema']) || isset($_GET['sistema'])) {
            session_start();
            if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
                header("Location: ../../index.php");
            }
            include_once("../Classes/Catalogo.class.php");
            include_once("../Classes/CatalogoFacturacion.class.php");
            include_once("../Classes/Lectura.class.php");
            include_once("../Classes/Mail.class.php");
            include_once("../Classes/ParametroGlobal.class.php");
            include_once("../Classes/Parametros.class.php");
            include_once("../Classes/ConexionMultiBD.class.php");
            include_once("../Classes/Factura2.class.php");
            $usuario = $_SESSION['user'];
            $pantalla = "Controler_FacturasLecturas";
        } else {
            include_once("WEB-INF/Classes/Catalogo.class.php");
            include_once("WEB-INF/Classes/CatalogoFacturacion.class.php");
            include_once("WEB-INF/Classes/Lectura.class.php");
            include_once("WEB-INF/Classes/Mail.class.php");
            include_once("WEB-INF/Classes/ParametroGlobal.class.php");
            include_once("WEB-INF/Classes/Parametros.class.php");
            include_once("WEB-INF/Classes/ConexionMultiBD.class.php");
            include_once("WEB-INF/Classes/Factura2.class.php");
            $usuario = "CRON PHP";
            $pantalla = "Controler_FacturasLecturas";
        }

        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
        $year = date("Y");
        $month = date("m");
        //$dia = date('j');
        $dia = 10;
        
        $fecha_lectura = "3000-01-01 00:00:00";

        $dias_mes = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $dias_extras = " AND DAY(kacc.Fecha) = DAY('$year-$month-$dia') ";
        if ($dias_mes < 31 && $dia == $dias_mes) {
            $dias_extras = " AND ( DAY(kacc.Fecha) = DAY('$year-$month-$dia') ";
            for ($i = date('j') + 1; $i <= 31; $i++) {
                $dias_extras .= " OR DAY(kacc.Fecha) = $i ";
            }
            $dias_extras .= ")";
        }

        $con = new ConexionMultiBD();
        $result_bases = mysql_query("SELECT id_empresa, nombre_empresa FROM `c_empresa` WHERE Activo = 1;");
        $con->Desconectar();

        while ($rs_multi = mysql_fetch_array($result_bases)) {
            echo "<br/><br/>Procesando empresa " . $rs_multi['nombre_empresa'];
            $empresa = $rs_multi['id_empresa'];
            $parametroGlobal = new ParametroGlobal();
            $parametroGlobal->setEmpresa($empresa);
            $catalogo = new Catalogo();
            $catalogo->setEmpresa($empresa);
            $lectura = new Lectura();
            $lectura->setEmpresa($empresa);
            $mail = new Mail();
            $mail->setEmpresa($empresa);
            $parametro = new Parametros();
            $parametro->setEmpresa($empresa);
            $factura = new Factura();
            $factura->setEmpresa($empresa);
            if ($parametroGlobal->getRegistroById("8")) {
                $mail->setFrom($parametroGlobal->getValor());
            } else {
                $mail->setFrom("scg-salida@scgenesis.mx");
            }
            if ($parametro->getRegistroById("8")) {
                $liga = $parametro->getDescripcion() . $pagina_cargar;
            } else {
                $liga = "http://genesis2.techra.com.mx/genesis2/" . $pagina_cargar;
            }
            /* Obtenemos todos los clientes que tienen fecha de corte hoy */
            $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.IdTipoFacturacion, ctt.NoContrato, cat.ClaveAnexoTecnico, 
                kacc.IdAnexoClienteCC, kacc.Fecha, c.GeneraFactura, c.RFC,
                CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoPaterno) AS EjecutivoCuenta, u.correo,
                (CASE WHEN !ISNULL(u.IdUsuario) THEN u.IdUsuario ELSE 'NA' END) AS IdUsuario
                FROM c_cliente AS c
                LEFT JOIN c_usuario AS u ON c.EjecutivoCuenta = u.IdUsuario
                LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = (SELECT MIN(NoContrato) FROM c_contrato WHERE ClaveCliente = c.ClaveCliente AND ctt.Activo = 1 AND ctt.FechaTermino >= NOW())
                LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = (SELECT MIN(ClaveAnexoTecnico) FROM c_anexotecnico WHERE NoContrato = ctt.NoContrato)
                LEFT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = (SELECT MIN(IdAnexoClienteCC) FROM k_anexoclientecc WHERE ClaveAnexoTecnico = cat.ClaveAnexoTecnico)
                WHERE c.Activo = 1 AND c.Modalidad <> 3 $dias_extras 
                ORDER BY IdUsuario,c.NombreRazonSocial, ctt.NoContrato, cat.ClaveAnexoTecnico, kacc.IdAnexoClienteCC;";
            $result = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($result) > 0) {
                $contador = 1;
                $usuario_anterior = "";
                $correo_anterior = "";
                $ejecutivo = "";
                $message = "";
                /* Recorremos todos los clientes obtenidos */
                while ($rs = mysql_fetch_array($result)) {
                    if ($factura->tieneFacturaMultiPeriodo($rs['RFC'], $year . "-" . $month . "-01")) {//Verificamos si el cliente no tiene factura vigentes de arrendamiento
                        continue;
                    }

                    echo "<br/> ********* Procesando " . $rs['NombreRazonSocial'];
                    if ($usuario_anterior != "" && $usuario_anterior != $rs['IdUsuario']) {
                        $mail->setSubject("Facturación de lecturas de corte " . $meses[$month - 1] . " del $year de $ejecutivo");
                        $mail->setBody($message);
                        if ($usuario_anterior != "NA") {
                            if (isset($correo_anterior) && $correo_anterior != "" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                                $mail->setTo($correo_anterior);
                                if ($mail->enviarMail() == "1") {
                                    echo "<br/>Un correo fue enviado de facturacion de lecturas a " . $correo_anterior;
                                } else {
                                    echo "<br/>Error: No se pudo enviar el correo a " . $correo_anterior . ".";
                                }
                            } else {
                                $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                                $mail->setSubject("Facturación de lecturas de corte " . $meses[$month - 1] . " del $year de $ejecutivo");
                                $mail->setBody($message_ejecutivo_extra . " " . $message);
                            }
                        } else {
                            $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                            $mail->setSubject("Facturación de lecturas de corte " . $meses[$month - 1] . " del $year de $ejecutivo");
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

                    if ($lectura->tieneTodasLecturas($rs['ClaveCliente'], $rs['IdTipoFacturacion'], $year, $month, $fecha_lectura, true)) {
                        if ($rs['GeneraFactura'] != "1") {
                            echo "<br/>El cliente <b>" . $rs['NombreRazonSocial'] . "</b> ya tiene todas las lecturas pero no está marcado como generar factura automáticamente";
                            $message .= "<br/><br/>El cliente <b>" . $rs['NombreRazonSocial'] . "</b> ya tiene todas las lecturas pero no está marcado para generar factura automáticamente";
                            if (isset($rs['IdUsuario']) && $rs['IdUsuario'] != "" && $rs['IdUsuario'] != "NA") {
                                $usuario_anterior = $rs['IdUsuario'];
                                $correo_anterior = $rs['correo'];
                                $ejecutivo = $rs['EjecutivoCuenta'];
                            } else {
                                $usuario_anterior = "NA";
                                $correo_anterior = "NA";
                                $ejecutivo = "NA";
                            }
                            continue;
                        }
                        $message .= "<br/><br/> Cliente <b>" . $rs['NombreRazonSocial'] . "</b>";
                        $consultaParametros = "SELECT * FROM `c_parametro_lectura` AS pl 
                            WHERE pl.ClaveAnexoTecnico = '" . $rs['ClaveAnexoTecnico'] . "';";
                        $resultParametros = $catalogo->obtenerLista($consultaParametros);
                        if (mysql_num_rows($resultParametros) > 0) {
                            while ($rsParametros = mysql_fetch_array($resultParametros)) {
                                echo "<br/>Haciendo anexo " . $rs['ClaveAnexoTecnico'];
                                if (isset($rsParametros['Id_parametro'])) {
                                    //extract data from the post
                                    extract($_POST);
                                    //set POST variables
                                    $url = $liga;
                                    $fields = array(
                                        'cliente' => urlencode($rs['ClaveCliente']),
                                        'localidad' => urlencode($rs['centro_costo']),
                                        'anexo' => urlencode($rs['ClaveAnexoTecnico']),
                                        'atm_post' => urlencode("1"),
                                        'postfijo' => urlencode($contador),
                                        'independiente' => urlencode("1"),
                                        'empresa' => urlencode($empresa)
                                    );

                                    if (isset($rsParametros['Numero_proveedor'])) {
                                        $fields['num_prov'] = urlencode($rsParametros['Numero_proveedor']);
                                    } else {
                                        $fields['num_prov'] = NULL;
                                    }
                                    if (isset($rsParametros['Numero_orden'])) {
                                        $fields['num_orden'] = urlencode($rsParametros['Numero_orden']);
                                    } else {
                                        $fields['num_orden'] = NULL;
                                    }
                                    if (isset($rsParametros['Observaciones_dentro_xml'])) {
                                        $fields['obs_in_xml'] = urlencode($rsParametros['Observaciones_dentro_xml']);
                                    } else {
                                        $fields['obs_in_xml'] = NULL;
                                    }
                                    if (isset($rsParametros['Observaciones_fuera_xml'])) {
                                        $fields['obs_out_xml'] = urlencode($rsParametros['Observaciones_fuera_xml']);
                                    } else {
                                        $fields['obs_out_xml'] = NULL;
                                    }
                                    if (isset($rsParametros['Mostrar_area']) && $rsParametros['Mostrar_area'] == "1") {
                                        $fields['mostrar_area'] = urlencode("1");
                                    } else {
                                        $fields['mostrar_area'] = NULL;
                                    }
                                    if (isset($rsParametros['Resaltar_periodo']) && $rsParametros['Resaltar_periodo'] == "1") {
                                        $fields['resal_perio'] = urlencode("1");
                                    } else {
                                        $fields['resal_perio'] = NULL;
                                    }
                                    if (isset($rsParametros['Rentas_lecturas']) && $rsParametros['Rentas_lecturas'] == "1") {
                                        $fields['rentas_lecturas'] = urlencode("1");
                                    } else {
                                        $fields['rentas_lecturas'] = NULL;
                                    }

                                    if (isset($rsParametros['Factura_renta_adelantada']) && $rsParametros['Factura_renta_adelantada'] == "1") {
                                        $fields['fact_adel'] = urlencode("1");
                                    } else {
                                        $fields['fact_adel'] = NULL;
                                    }

                                    if (isset($rsParametros['Dir_reporte']) && $rsParametros['Dir_reporte'] == "1") {
                                        $fields['dir_rep'] = urlencode("1");
                                    } else {
                                        $fields['dir_rep'] = NULL;
                                    }

                                    if (isset($rsParametros['MostrarImporteCero']) && $rsParametros['MostrarImporteCero'] == "1") {
                                        $fields['MostrarImporteCero'] = urlencode("1");
                                    } else {
                                        $fields['MostrarImporteCero'] = NULL;
                                    }

                                    if (isset($rsParametros['MostrarEncabezadoServicio']) && $rsParametros['MostrarEncabezadoServicio'] == "1") {
                                        $fields['MostrarEncabezadoServicio'] = urlencode("1");
                                    } else {
                                        $fields['MostrarEncabezadoServicio'] = NULL;
                                    }

                                    if (isset($rsParametros['Dividir_Color']) && $rsParametros['Dividir_Color'] == "1") {
                                        $fields['Dividir_Color'] = urlencode("1");
                                    } else {
                                        $fields['Dividir_Color'] = NULL;
                                    }

                                    if (isset($rsParametros['Dividir_factura'])) {
                                        $fields['dividir_factura'] = urlencode($rsParametros['Dividir_factura']);
                                    } else {
                                        $fields['dividir_factura'] = NULL;
                                    }

                                    if (isset($rsParametros['Agrupar_factura'])) {
                                        $fields['agrupar_factura'] = urlencode($rsParametros['Agrupar_factura']);
                                    } else {
                                        $fields['agrupar_factura'] = NULL;
                                    }

                                    if (isset($rsParametros['Mostrar_Serie']) && $rsParametros['Mostrar_Serie'] == "1") {
                                        $fields['Mostrar_Serie'] = urlencode("1");
                                    } else {
                                        $fields['Mostrar_Serie'] = NULL;
                                    }

                                    if (isset($rsParametros['Mostrar_Modelo']) && $rsParametros['Mostrar_Modelo'] == "1") {
                                        $fields['Mostrar_Modelo'] = urlencode("1");
                                    } else {
                                        $fields['Mostrar_Modelo'] = NULL;
                                    }

                                    if (isset($rsParametros['Agrupar_Renta']) && $rsParametros['Agrupar_Renta'] == "1") {
                                        $fields['Agrupar_Renta'] = urlencode("1");
                                    } else {
                                        $fields['Agrupar_Renta'] = NULL;
                                    }


                                    //url-ify the data for the POST
                                    foreach ($fields as $key => $value) {
                                        $fields_string .= $key . '=' . $value . '&';
                                    }
                                    rtrim($fields_string, '&');
                                    //open connection
                                    $ch = curl_init();
                                    //set the url, number of POST vars, POST data
                                    curl_setopt($ch, CURLOPT_URL, $url);
                                    curl_setopt($ch, CURLOPT_POST, count($fields));
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                                    //execute post
                                    $result_post = curl_exec($ch);

                                    $message .= $result_post;
                                    //echo "Resultado:".$result_post;

                                    curl_close($ch);
                                    $contador++;
                                }
                            }
                        } else {
                            echo "<br/>Haciendo cliente " . $rs['ClaveCliente'];
                            //extract data from the post
                            extract($_POST);
                            //set POST variables
                            $url = $liga;
                            $fields = array(
                                'postfijo' => urlencode($contador),
                                'independiente' => urlencode("1"),
                                'atm_post' => urlencode("1"),
                                'empresa' => urlencode($empresa),
                                'cliente' => urlencode($rs['ClaveCliente']),
                                'localidad' => urlencode(""),
                                'centro_costo' => urlencode(""),
                                'anexo' => urlencode(""),
                                'agrupar_factura' => urlencode("0"),
                                'dividir_factura' => urlencode("0"),
                                'Agrupar_Renta' => urlencode("1"),
                                'Mostrar_Modelo' => urlencode("1"),
                                'Mostrar_Serie' => urlencode("1")
                            );

                            $fields['num_prov'] = NULL;
                            $fields['num_orden'] = NULL;
                            $fields['obs_in_xml'] = NULL;
                            $fields['obs_out_xml'] = NULL;
                            $fields['mostrar_area'] = NULL;
                            $fields['resal_perio'] = NULL;
                            $fields['rentas_lecturas'] = NULL;
                            $fields['fact_adel'] = NULL;
                            $fields['dir_rep'] = NULL;
                            $fields['MostrarImporteCero'] = NULL;
                            $fields['MostrarEncabezadoServicio'] = NULL;
                            $fields['Dividir_Color'] = NULL;

                            //url-ify the data for the POST
                            foreach ($fields as $key => $value) {
                                $fields_string .= $key . '=' . $value . '&';
                            }
                            rtrim($fields_string, '&');
                            //open connection
                            $ch = curl_init();
                            //set the url, number of POST vars, POST data
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_POST, count($fields));
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                            //execute post
                            $result_post = curl_exec($ch);

                            $message .= $result_post;
                            //echo "Resultado:".$result_post;
                            //close connection
                            curl_close($ch);

                            $contador++;
                        }
                    } else {
                        echo "<br/>El cliente " . $rs['NombreRazonSocial'] . " no tiene todas las lecturas de corte capturadas, mandar al ejecutivo.";
                        $message .= ("<br/><br/>El cliente " . $rs['NombreRazonSocial'] . " no tiene todas las lecturas de corte capturadas del mes 
                            de " . $meses[$month - 1] . " de $year");
                        $message .= $lectura->getMensaje();
                    }

                    if (isset($rs['IdUsuario']) && $rs['IdUsuario'] != "" && $rs['IdUsuario'] != "NA") {
                        $usuario_anterior = $rs['IdUsuario'];
                        $correo_anterior = $rs['correo'];
                        $ejecutivo = $rs['EjecutivoCuenta'];
                    } else {
                        $usuario_anterior = "NA";
                        $correo_anterior = "NA";
                        $ejecutivo = "NA";
                    }
                }

                if ($usuario_anterior != "") {
                    $mail->setSubject("Facturación de lecturas de corte " . $meses[$month - 1] . " del $year de $ejecutivo");
                    $mail->setBody($message);
                    if ($usuario_anterior != "NA") {
                        if (isset($correo_anterior) && $correo_anterior != "" && filter_var($correo_anterior, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                            $mail->setTo($correo_anterior);
                            if ($mail->enviarMail() == "1") {
                                echo "<br/>Un correo fue enviado de facturacion de lecturas a " . $correo_anterior;
                            } else {
                                echo "<br/>Error: No se pudo enviar el correo a " . $correo_anterior . ".";
                            }
                        } else {
                            $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                            $mail->setSubject("Facturación de lecturas de corte " . $meses[$month - 1] . " del $year de $ejecutivo");
                            $mail->setBody($message_ejecutivo_extra . " " . $message);
                        }
                    } else {
                        $message_ejecutivo_extra = "<br/><b>Los datos siguientes no fueron enviados a ningún ejecutivo, ya que no se pudo asociar la información</b><br/><br/>";
                        $mail->setSubject("Facturación de lecturas de corte " . $meses[$month - 1] . " del $year de $ejecutivo");
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
            } else {
                echo "<br/>No se encontraron clientes con fecha de corte de toma de lectura " . $dia . " de cada mes";
            }
        }
        ?>        
    </body>
</html>