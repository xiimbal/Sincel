<?php

include_once("Catalogo.class.php");
include_once("Mail.class.php");
include_once("Usuario.class.php");
include_once("ParametroGlobal.class.php");

/**
 * Description of Solicitud
 *
 * @author MAGG
 */
class Solicitud {

    private $id_mail;
    private $id_solicitud;
    private $contestada;
    private $clave;
    private $IdUsuario;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    
    public function getRegistroById($id){
        $consulta = "SELECT * FROM `c_solicitud` where id_solicitud = $id;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }                
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_solicitud = $rs['id_solicitud'];
            return true;
        }
        return false;
    }

    public function getIdByClaveLink($clave, $solicitud) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }        
        $consulta = ("SELECT * FROM `c_mailsolicitud` WHERE clave = MD5('$clave') AND id_solicitud = $solicitud;");
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_mail = $rs['id_mail'];
            $this->id_solicitud = $rs['id_solicitud'];
            $this->contestada = $rs['contestada'];
            $this->clave = $rs['clave'];
            $this->IdUsuario = $rs['IdUsuario'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $estatus
     * @return boolean
     */
    public function cambiarEstatusSolicitud($estatus) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if (isset($_SESSION['user'])) {
            $usuario = $_SESSION['user'];
        } else {
            $usuario = "Gerente_Almacen";
        }
        $query = $catalogo->obtenerLista("UPDATE c_solicitud SET estatus = $estatus, 
            UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(),
            Pantalla = 'Solicitud cambiarEstatusSolicitud' WHERE id_solicitud = $this->id_solicitud;");

        if ($query == "1") {
            if ($estatus == "1") {/* Autorizamos todos los componentes y equipos */
                $consulta = "UPDATE k_solicitud SET cantidad_autorizada=cantidad, UsuarioUltimaModificacion = '$usuario', FechaUltimaModificacion = NOW(),
				Pantalla = 'Solicitud cambiarEstatusSolicitud' WHERE id_solicitud = $this->id_solicitud";
                $query = $catalogo->obtenerLista($consulta);
            }
            return true;
        }
        return false;
    }

    public function aceptarRechazarSolicitud($tipo) {        
        $catalogo = new Catalogo();        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }        
        $consulta = "UPDATE c_solicitud SET estatus = $tipo, id_autoriza = $this->IdUsuario, Pantalla = 'Aceptacion por mail' WHERE id_solicitud = $this->id_solicitud;";        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            $consulta = "UPDATE c_ventadirecta SET Estatus=$tipo,autorizada_vd = 1, autorizada_alm = 1, facturada = 0, Pantalla = 'Aceptacion por mail' WHERE id_solicitud = $this->id_solicitud;";
            $catalogo->obtenerLista($consulta);
            $query = $catalogo->obtenerLista("UPDATE c_mailsolicitud SET contestada = 1 WHERE id_mail = $this->id_mail;");            
            if ($tipo == "1") {/* Autorizamos toda la cantidad solicitada */
                $query = $catalogo->obtenerLista("UPDATE k_solicitud SET cantidad_autorizada = cantidad WHERE id_solicitud = $this->id_solicitud;");
            }
            if ($query == "1") {
                $this->enviarCorreoSol($tipo);
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Equipos en almacen de la solicitud
     * @param type $idSolicitud
     * @return type
     */
    public function equiposEnAlmacen($idSolicitud) {
        $consulta = "SELECT id_bitacora, id_almacen FROM `c_bitacora` AS b 
            INNER JOIN k_almacenequipo AS kae ON b.id_solicitud = $idSolicitud AND b.NoSerie = kae.NoSerie;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }        
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Regresa true en caso de que todos los equipos ya tengan una serie asociada.
     * @param type $idSolicitud id se la solicitud.
     * @return boolean
     */
    public function todosEquiposAsignados($idSolicitud) {
        $consulta = "SELECT 
            (
            CASE 
            WHEN (SELECT SUM(cantidad_autorizada) AS cantidadautorizada FROM `k_solicitud` WHERE id_solicitud = $idSolicitud AND tipo = 0) IS NULL THEN 1
            WHEN 
            (SELECT SUM(cantidad_autorizada) AS cantidadautorizada FROM `k_solicitud` WHERE id_solicitud = $idSolicitud AND tipo = 0) <= 
            (SELECT COUNT(id_bitacora) AS suma FROM c_bitacora WHERE id_solicitud = $idSolicitud) 
            THEN 1 ELSE 0 END) 
            AS SURTIDA;";        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            if ($rs['SURTIDA'] == "1") {
                return true;
            }
        }
        return false;
    }

    /**
     * True en caso de que todaví haya equipos de la solicitud en el almacen, false en caso contrario
     * @param type $idSolicitud
     * @return type
     */
    public function sobranEquiposEnAlmacenBySolicitud() {
        $result = $this->equiposEnAlmacen($this->id_solicitud);
        $resultados = mysql_num_rows($result);
        return ($resultados > 0) ? true : false; /* Si hay resultados, entonces no han sido todos los equipos enviados y se regresa false, true en caso contrario */
    }

    /**
     * Agrega el comentario a la solicitud
     * @param type $comentario comentario que se va a agregar
     * @return boolean true en caso de haber agregado el comentario, false en caso contrario.
     */
    public function agregarComentario($comentario) {
        $consulta = "UPDATE `c_solicitud` SET comentario = '$comentario' WHERE id_solicitud = $this->id_solicitud;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    /**
     * Marcar como surtido (0) o no surtido (1) la partida de la solicitud
     * @param type $idSolicitud id de la solicitud
     * @param type $idPartida id de la partida
     * @param type $surtir 0 para surtir o 1 para no surtir
     * @return boolean true en caso de haber actualizado el estado, false en caso contrario.
     */
    public function NoSurtir($idSolicitud, $idPartida, $surtir) {
        $consulta = "UPDATE k_solicitud SET NoSurtir = $surtir WHERE id_solicitud = $idSolicitud AND id_partida = $idPartida;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    /**
     * Reduce en uno los equipos autorizados en la partida especificada
     * @param type $idSolicitud id de la solicitud
     * @param type $idPartida id de la partida
     * @return boolean true en caso de haber reducido los equipos autorizados, false en caso contrario
     */
    public function eliminarEquipoPartida($idSolicitud, $idPartida) {
        $consulta = "SELECT cantidad_autorizada FROM `k_solicitud` WHERE id_solicitud = $idSolicitud AND id_partida = $idPartida;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            if ($rs['cantidad_autorizada'] > 0) {
                $consulta = "UPDATE k_solicitud SET cantidad_autorizada = cantidad_autorizada - 1, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', 
                    FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla' WHERE id_solicitud = $idSolicitud AND id_partida = $idPartida;";
                $query = $catalogo->obtenerLista($consulta);
                if ($query == "1") {
                    return true;
                }
            }
        }
        return false;
    }

    public function enviarCorreoSol($tipo) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query3 = $catalogo->obtenerLista("SELECT
                c_solicitud.fecha_solicitud AS Fecha,
                c_cliente.ClaveCliente AS ClaveCliente,
                c_cliente.NombreRazonSocial AS Cliente,
                c_cliente.IdEstatusCobranza,
                c_cliente.RFC,
                c_solicitud.id_solicitud AS ID,
                k_solicitud.cantidad AS Cantidad,
                k_solicitud.tipo AS Tipo,
                k_solicitud.Modelo AS Modelo,
                k_solicitud.NoSerie AS NoSerie,
                c_tiposolicitud.Nombre AS TipoSolicitud,
                c_formapago.Nombre AS formaPago,
                c_tipoinventario.Nombre AS tipoInventario,
                (SELECT CASE WHEN k_solicitud.tipo = 0 THEN (SELECT MAX(Modelo) FROM c_equipo WHERE NoParte = k_solicitud.Modelo) ELSE (SELECT MAX(Modelo) FROM c_componente WHERE NoParte = k_solicitud.Modelo) END) AS Modelo,
                k_solicitud.ClaveCentroCosto AS Localidad
                FROM c_solicitud
                INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
                INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
                LEFT JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
                LEFT JOIN c_formapago ON c_formapago.IdFormaPago = c_solicitud.IdFormaPago
                LEFT JOIN c_tipoinventario ON c_tipoinventario.idTipo = k_solicitud.TipoInventario
                WHERE c_solicitud.id_solicitud =" . $this->id_solicitud . "
                ORDER BY k_solicitud.id_partida");
        $texto = "<table border=\"1\">";
        $texto .="<thead><tr><th>Cantidad</th><th>Modelo</th><th>Localidad</th><th>Estado del equipo/Equipo con cliente</th></tr></thead><tbody>";
        $formasPago = "";
        while ($rs = mysql_fetch_array($query3)) {
            $query4 = $catalogo->obtenerLista("SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto='" . $rs['Localidad'] . "'");
            $rsp = mysql_fetch_array($query4);
            $estado = "";
            if ($rs['Tipo'] == "0") {
                $estado = $rs['tipoInventario'];
            } else {//Si es componente
                if (isset($rs['NoSerie']) && $rs['NoSerie'] != "") {//Si tiene número de serie asociado de un equipo en cliente
                    $estado = $rs['NoSerie'] . " (Con cliente)";
                }
            }
            $texto.="<tr><td>" . $rs['Cantidad'] . "</td><td>" . $rs['Modelo'] . "</td><td>" . $rsp['Nombre'] . "</td><td>$estado</td></tr>";
            $formasPago = $rs['formaPago'];
        }
        $texto .= "</tbody></table><br/>";
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
                        FROM
                                k_almacenequipo
                        INNER JOIN c_equipo ON c_equipo.NoParte = k_almacenequipo.NoParte
                        INNER JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = k_almacenequipo.ClaveCentroCosto
                        INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                        WHERE
                                c_equipo.Modelo = '" . $rs['Modelo'] . "'
                        AND k_almacenequipo.Apartado = 1 GROUP BY Cliente,CentroCosto");
                while ($rsp = mysql_fetch_array($query4)) {
                    $val = false;
                    $texto3 .= "<tr><td>" . $rsp['Suma'] . "</td><td>" . $rs['Modelo'] . "</td><td>Equipo</td><td>" . $rsp['Cliente'] . "</td><td>" . $rsp['CentroCosto'] . "</td></tr>";
                }
            } else {
                $query4 = $catalogo->obtenerLista("SELECT
                        k_almacencomponente.cantidad_apartados  AS Apartado,
                        k_almacencomponente.cantidad_existencia AS Existencias
                        FROM
                                k_almacencomponente
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
                        FROM
                                k_almacencomponente
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
        $parametroGlobal = new ParametroGlobal();
        if(isset($this->empresa)){
            $parametroGlobal->setEmpresa($this->empresa);
        }
        if ($parametroGlobal->getRegistroById("8")) {
            $mail->setFrom($parametroGlobal->getValor());
        } else {
            $mail->setFrom("scg-salida@scgenesis.mx");
        }
        $mail->setSubject("No Solicitud: " . $this->id_solicitud);
        $message = "<html><body>";
        $aceptada = " aceptada ";
        if ($tipo == 3) {
            $aceptada = " rechazada ";
        }
        $message .= "<h3>La solicitud de tipo <font color=red>" . $rs['TipoSolicitud'] . " fue$aceptada</font></h4>";
        
        if ($rs['IdEstatusCobranza'] != "2") {
            $message .= "<h3>Para el cliente:</h3><h4>" . $rs['Cliente'] . "</h4>";
        } else {
            include_once("ReporteFacturacion.class.php");
            $facturas = new ReporteFacturacion();
            if(isset($this->empresa)){
                $facturas->setEmpresa($this->empresa);
            }
            $facturas->setRfccliente($rs['RFC']);
            $facturas->setStatus(array(1)); /* Para que muestre solo las facturas no pagadas */
            $result3 = $facturas->getTabla(false);
            if (mysql_num_rows($result3) > 0) {
                $facturas_pendientes = "<table border=\"1\"><thead><tr><th>Folio</th><th>Fecha Facturación</th><th>Total</th></tr></thead><tbody>";
                while ($rs3 = mysql_fetch_array($result3)) {
                    $facturas_pendientes.= "<tr>";
                    $facturas_pendientes.= "<td align='center' scope='row'>" . $rs3['Folio'] . "</td>";
                    $facturas_pendientes.= "<td align='center' scope='row'>" . $rs3['FechaFacturacion'] . "</td>";
                    $facturas_pendientes.= "<td align='center' scope='row'>$" . $rs3['Total'] . "</td>";
                    $facturas_pendientes.= "</tr>";
                }
                $facturas_pendientes.="</tbody></table>";
                $message .= "<h3>Para el <font color=red>cliente moroso:</font></h3><h4>" . $rs['Cliente'] . "</h4><b>Con las facturas pendientes:</b><br/>$facturas_pendientes<br/><br/>";
            } else {
                $message .= "<h3>Para el <font color=red>cliente moroso:</font></h3><h4>" . $rs['Cliente'] . "</h4>";
            }
        }

        /* Obtenemos los correos a quien mandaremos el mail */
        $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 12;");
        $correos = array();
        $z = 0;
        while ($rs = mysql_fetch_array($query4)) {
            $correos[$z] = $rs['correo'];
            $z++;
        }
        $query4 = $catalogo->obtenerLista("SELECT c_usuario.correo FROM c_solicitud
            INNER JOIN c_usuario ON c_usuario.Loggin=c_solicitud.UsuarioCreacion
            WHERE c_solicitud.id_solicitud='" . $this->id_solicitud . "'");
        while ($rs = mysql_fetch_array($query4)) {
            $correos[$z] = $rs['correo'];
            $z++;
        }
                
        $message .= $texto;
        $message .= "</body></html>";
        $mail->setBody($message);
        $todos_enviados = true;
        foreach ($correos as $value) {
            if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                $mail->setTo($value);
                if ($mail->enviarMail() == "1") {
                    //echo "Un correo fue enviado para la autorización.";
                } else {
                    echo "<br/>Error: No se pudo enviar el correo de confirmación a $value.";
                    $todos_enviados = false;
                }
            }
        }
        if($todos_enviados){
            echo "Los correos de confirmación fueron enviados.<br/>";
            /*foreach ($correos as $value) {
                echo "$value, ";
            }*/
        }
    }

    public function getId_mail() {
        return $this->id_mail;
    }

    public function setId_mail($id_mail) {
        $this->id_mail = $id_mail;
    }

    public function getId_solicitud() {
        return $this->id_solicitud;
    }

    public function setId_solicitud($id_solicitud) {
        $this->id_solicitud = $id_solicitud;
    }

    public function getContestada() {
        return $this->contestada;
    }

    public function setContestada($contestada) {
        $this->contestada = $contestada;
    }

    public function getClave() {
        return $this->clave;
    }

    public function setClave($clave) {
        $this->clave = $clave;
    }

    public function getIdUsuario() {
        return $this->IdUsuario;
    }

    public function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
