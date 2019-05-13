<?php

include_once("CatalogoFacturacion.class.php");
include_once("Catalogo.class.php");
include_once("Mail.class.php");
include_once("ParametroGlobal.class.php");

/**
 * Description of EstadoCuenta
 *
 * @author MAGG
 */
class EstadoCuenta {

    private $empresa;

    public function generarEstadoCuenta($clientes, $fecha_inicio, $fecha_fin, $mostrar_pagado, $mandar_mail) {
        $catalogo = new Catalogo();
        $catalogo_fac = new CatalogoFacturacion();
        $mail = new Mail();
        $parametroGlobal = new ParametroGlobal();

        if (isset($this->empresa) && !empty($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
            $catalogo_fac->setEmpresa($this->empresa);
            $mail->setEmpresa($this->empresa);
            $parametroGlobal->setEmpresa($this->empresa);
        }

        if ($parametroGlobal->getRegistroById("8")) {
            $mail->setFrom($parametroGlobal->getValor());
        } else {
            $mail->setFrom("scg-salida@scgenesis.mx");
        }

        $string_clientes = "";
        $html = "";
        $where_f = "";
        $where_p = "";

        if (isset($fecha_inicio) && !empty($fecha_inicio)) {
            $where_f .= " AND f.FechaFacturacion >= '$fecha_inicio'";
            $where_p .= " AND pp.FechaPago >= '$fecha_inicio'";
        }

        if (isset($fecha_fin) && !empty($fecha_fin)) {
            $where_f .= " AND f.FechaFacturacion <= '$fecha_fin'";
            $where_p .= " AND pp.FechaPago <= '$fecha_fin'";
        }

        if (!$mostrar_pagado) {
            $where_f .= " AND f.FacturaPagada = 0 ";
            $where_p .= " AND f.FacturaPagada = 0 ";
        }

        if (!empty($clientes)) {
            $negocios = $clientes;
            foreach ($negocios as $value) {
                $string_clientes .= "'$value',";
            }
            if (!empty($string_clientes)) {
                $string_clientes = substr($string_clientes, 0, strlen($string_clientes) - 1);
            }
            $where_clientes = " WHERE c.ClaveCliente IN($string_clientes) AND Modalidad = 1 ";
        } else {
            $where_clientes = " WHERE c.Activo = 1 AND Modalidad = 1 ";
        }

        $correos_correctos = array();
        if ($mandar_mail) {
            /* Correos configurados para mandar estado de cuenta */
            $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 22;");
            while ($rs = mysql_fetch_array($query4)) {
                $value = $rs['correo'];
                if (isset($value) && $value != "" && !in_array($value, $correos_correctos)) {
                    if (isset($value) && $value != NULL && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                        array_push($correos_correctos, $value);
                    } else {
                        echo "<br/>Error: correo electrónico inválido: <b>$value</b>";
                    }
                }
            }
        }

        $info_clientes = array();
        $empresa = "";
        $direccion_empresa = "";
        $imagen = "";
        $de = $catalogo->formatoFechaReportes($fecha_inicio);
        $a = $catalogo->formatoFechaReportes($fecha_fin);
        $consulta = "SELECT DISTINCT(c.ClaveCliente) AS ClaveCliente, 
            c.RFC, c.NombreRazonSocial,
            ctt.Nombre AS contacto, 
            ctt.Telefono, 
            ctt.Celular, 
            ctt.CorreoElectronico,
            CONCAT_WS(' ',d.Calle,' #',d.NoExterior,' No. Int:',d.NoInterior,', ',d.Colonia,', ',d.Delegacion,' ',d.Estado,',',d.Pais,' C.P.',d.CodigoPostal) AS Direccion,
            fe.RazonSocial AS Empresa, CONCAT(fe.Calle,' ',fe.NoExterior,', ',fe.NoInterior,', ',fe.Colonia,', ',fe.Delegacion,' ',fe.Estado,',',fe.Pais,' C.P.',fe.CP) AS DireccionEmpresa,
            fe.ImagenPHP
            FROM c_cliente AS c
            LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio AS d2 WHERE d2.ClaveEspecialDomicilio = c.ClaveCliente AND d2.IdTipoDomicilio = 3)
            LEFT JOIN c_contacto AS ctt ON ctt.IdContacto = (SELECT MIN(IdContacto) FROM c_contacto AS ctt2 WHERE ctt2.ClaveEspecialContacto = c.ClaveCliente AND ctt2.IdTipoContacto = 15 AND ctt2.Activo = 1)
            LEFT JOIN c_datosfacturacionempresa AS fe ON fe.IdDatosFacturacionEmpresa = c.IdDatosFacturacionEmpresa
            $where_clientes
            GROUP BY ClaveCliente;";
        //echo $consulta;
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $info_clientes[$rs['ClaveCliente']]['RFC'] = $rs['RFC'];
            $info_clientes[$rs['ClaveCliente']]['Nombre'] = $rs['NombreRazonSocial'];
            $info_clientes[$rs['ClaveCliente']]['Contacto'] = $rs['Contacto'];
            $info_clientes[$rs['ClaveCliente']]['Telefono'] = $rs['Telefono'];
            $info_clientes[$rs['ClaveCliente']]['Celular'] = $rs['Celular'];
            $info_clientes[$rs['ClaveCliente']]['CorreoElectronico'] = $rs['CorreoElectronico'];
            $info_clientes[$rs['ClaveCliente']]['Direccion'] = $rs['Direccion'];
            if (empty($empresa)) {
                $empresa = $rs['Empresa'];
                $direccion_empresa = $rs['DireccionEmpresa'];
                $imagen = $rs['ImagenPHP'];
            }
        }

        if (!mysql_data_seek($result, 0)) {
            $result = $catalogo->obtenerLista($consulta);
        }

        $consulta_facturas = "SELECT * FROM(
            SELECT DATE(f.FechaFacturacion) AS Fecha, c.ClaveCliente, f.RFCReceptor, DATE(f.PeriodoFacturacion) AS PeriodoFacturacion, NULL AS Referencia, f.Folio, f.Total As Cargo, NULL AS Abono, NULL AS Observaciones
            FROM c_factura AS f
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1) 
            $where_clientes AND f.EstadoFactura = 1 
            AND f.TipoComprobante = 'ingreso' $where_f
            AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3)
            UNION
            SELECT DATE(pp.FechaPago), c.ClaveCliente, f.RFCReceptor, DATE(f.PeriodoFacturacion) AS PeriodoFacturacion, pp.Referencia, f.Folio, NULL AS Cargo, pp.ImportePagado AS Abono, pp.Observaciones
            FROM c_pagosparciales AS pp
            LEFT JOIN c_factura AS f ON f.IdFactura = pp.IdFactura
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1) 
            $where_clientes AND f.EstadoFactura = 1 AND !ISNULL(c.ClaveCliente)
            AND f.TipoComprobante = 'ingreso' $where_p
            AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3)
            ) AS t_1 ORDER BY ClaveCliente, Fecha";
        //echo $consulta_facturas;
        $result_edo = $catalogo_fac->obtenerLista($consulta_facturas);
        //echo $consulta_facturas;
        $saldo = 0;
        $total_cargos = 0;
        $total_abonos = 0;
        $claveClienteAnterior = "";
        $procesado_ultimo = true;
        while ($rs = mysql_fetch_array($result_edo)) {
            if ($claveClienteAnterior != $rs['ClaveCliente']) {
                if (!empty($claveClienteAnterior)) {
                    $html .= "<tr>"
                            . "<td colspan='2'></td>"
                            . "<td style='text-align: rigth; font-weight:bold;'>TOTAL:</td>"
                            . "<td></td>"
                            . "<td style='border-top: 2px solid black;'>$" . number_format($total_cargos, 2) . "</td>"
                            . "<td style='border-top: 2px solid black;'>$" . number_format($total_abonos, 2) . "</td>"
                            . "<td style='border-top: 2px solid black;'>$" . number_format($saldo, 2) . "</td>"
                            . "</tr>"
                            . "</tbody></table>";
                    if ($mandar_mail) {
                        $correos_correctos_aux = $correos_correctos;
                        $value = $info_clientes[$claveClienteAnterior]['CorreoElectronico'];
                        if (isset($value) && $value != "" && !in_array($value, $correos_correctos_aux)) {
                            if (isset($value) && $value != NULL && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                                array_push($correos_correctos_aux, $value);
                            } else {
                                echo "<br/>Error: correo electrónico inválido: <b>$value</b>";
                            }
                        }
                    }

                    if ($mandar_mail && $saldo != 0 && !empty($correos_correctos_aux)) {
                        $mail->setSubject("Estado de cuenta " . $info_clientes[$claveClienteAnterior]['Nombre']);
                        $mail->setBody($html);
                        $mail->setTo($correos_correctos_aux);
                        if ($mail->enviarMail() != "0") {
                            echo "<br/>Un correo (" . $mail->getSubject() . ") de estado de cuenta fue enviado a " . implode(",", $correos_correctos_aux);
                        } else {
                            echo "<br/>Error: No se pudo enviar el correo a " . implode(",", $correos_correctos_aux);
                        }
                        //echo "<br/>Aquí se manda por correo $html";
                        $html = "";
                    } else {
                        $html .= '<div class="page-break"></div>';
                    }
                    $procesado_ultimo = true;
                }
                $html .= "<table style='width:80%;'>"
                        . "<tr>"
                        . "<td style='width:30%;' rowspan='2'>";
                //if (!$mandar_mail) {
                $html .= "<img src='../$imagen'/>";
                //}
                $html .= "</td>"
                        . "<td style='text-align:center; font-weight: bold;'>$empresa</td>"
                        . "</tr>"
                        . "<tr>"
                        . "<td style='text-align:center;'>$direccion_empresa</td>"
                        . "</tr>"
                        . "<tr><td></td>"
                        . "<td style='text-align:center; font-weight: bold;'>ESTADO DE CUENTA CLIENTE</td>"
                        . "</tr>"
                        . "<tr><td></td>"
                        . "<td style='text-align:center; font-weight: bold;'>DE: $de A: $a</td>"
                        . "</tr>"
                        . "</table>";

                $html .= "<br/><br/><table style='width:100%; border: 1px solid black;'>"
                        . "<tr>"
                        . "<td><b>N° de cliente:</b> " . $rs['ClaveCliente'] . "</td>"
                        . "<td><b>RFC:</b> " . $info_clientes[$rs['ClaveCliente']]['RFC'] . "</td>"
                        . "<td style='width:10%;'></td>"
                        . "<td><b>Contacto:</b>" . $info_clientes[$rs['ClaveCliente']]['Contacto'] . "</td>"
                        . "</tr>"
                        . "<tr>"
                        . "<td><b>Nombre:</b> " . $info_clientes[$rs['ClaveCliente']]['Nombre'] . "</td>"
                        . "<td></td>"
                        . "<td style='width:10%;'></td>"
                        . "<td><b>Celular:</b> " . $info_clientes[$rs['ClaveCliente']]['Celular'] . "</td>"
                        . "</tr>"
                        . "<tr>"
                        . "<td colspan='3'><b>Dirección:</b> " . $info_clientes[$rs['ClaveCliente']]['Direccion'] . "</td>"
                        . "<td><b>Teléfono:</b> " . $info_clientes[$rs['ClaveCliente']]['Telefono'] . "</td>"
                        . "</tr>"
                        . "<tr>"
                        . "<td></td>"
                        . "<td></td>"
                        . "<td style='width:10%;'></td>"
                        . "<td><b>Email:</b> " . $info_clientes[$rs['ClaveCliente']]['CorreoElectronico'] . "</td>"
                        . "</tr>"
                        . "</table><br/><br/>";

                $html .= "<table style='width:100%;'>"
                        . "<thead>"
                        . "<tr style='border: 1px solid black; background: #D8D7D6;'>"
                        . "<th>Fecha</th>"
                        . "<th>Periodo</th>"
                        //. "<th>RFC</th>"
                        . "<th>Referencia</th>"
                        . "<th>Folio</th>"
                        . "<th>Cargo</th>"
                        . "<th>Abono</th>"
                        . "<th>Saldo</th>"
                        . "<th>Notas</th>"
                        . "</tr>"
                        . "</thead><tbody>";
                $saldo = 0;
                $total_cargos = 0;
                $total_abonos = 0;
                $claveClienteAnterior = $rs['ClaveCliente'];
                $procesado_ultimo = false;
            }
            $cargo = "";
            $abono = "";
            if (isset($rs['Cargo'])) {
                $cargo = "$" . number_format($rs['Cargo'], 2);
                $saldo += (float) $rs['Cargo'];
                $total_cargos += (float) $rs['Cargo'];
            } else if (isset($rs['Abono'])) {
                $abono = "$" . number_format($rs['Abono'], 2);
                $saldo -= (float) $rs['Abono'];
                $total_abonos += (float) $rs['Abono'];
            }
            $html .= "<tr>"
                    . "<td>" . $rs['Fecha'] . "</td>"
                    . "<td>" . substr($catalogo->formatoFechaReportes($rs['PeriodoFacturacion']), 5) . "</td>"
                    //. "<td>" . $rs['RFCReceptor'] . "</td>"
                    . "<td>" . $rs['Referencia'] . "</td>"
                    . "<td>" . $rs['Folio'] . "</td>"
                    . "<td>$cargo</td>"
                    . "<td>$abono</td>"
                    . "<td>" . number_format($saldo, 2) . "</td>"
                    . "<td>" . $rs['Observaciones'] . "</td>"
                    . "</tr>";
        }

        if (!empty($claveClienteAnterior) && !$procesado_ultimo) {
            $html .= "<tr>"
                    . "<td colspan='2'></td>"
                    . "<td style='text-align: rigth; font-weight:bold;'>TOTAL:</td>"
                    . "<td></td>"
                    . "<td style='border-top: 2px solid black;'>$" . number_format($total_cargos, 2) . "</td>"
                    . "<td style='border-top: 2px solid black;'>$" . number_format($total_abonos, 2) . "</td>"
                    . "<td style='border-top: 2px solid black;'>$" . number_format($saldo, 2) . "</td>"
                    . "</tr>"
                    . "</tbody></table>";
            $correos_correctos_aux = $correos_correctos;
            $value = $info_clientes[$claveClienteAnterior]['CorreoElectronico'];
            if (isset($value) && $value != "" && !in_array($value, $correos_correctos_aux)) {
                if (isset($value) && $value != NULL && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                    array_push($correos_correctos_aux, $value);
                } else {
                    echo "<br/>Error: correo electrónico inválido: <b>$value</b>";
                }
            }

            if ($mandar_mail && $saldo != 0 && !empty($correos_correctos_aux)) {
                $mail->setSubject("Estado de cuenta " . $info_clientes[$claveClienteAnterior]['Nombre']);
                $mail->setBody($html);
                $mail->setTo($correos_correctos_aux);
                if ($mail->enviarMail() != "0") {
                    echo "<br/>Un correo (" . $mail->getSubject() . ") de estado de cuenta fue enviado a " . implode(",", $correos_correctos_aux);
                } else {
                    echo "<br/>Error: No se pudo enviar el correo a " . implode(",", $correos_correctos_aux);
                }
                //echo "<br/>Aquí se manda por correo $html";
                $html = "";
            }
        }

        if (!$mandar_mail) {
            echo $html;
        }
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}
