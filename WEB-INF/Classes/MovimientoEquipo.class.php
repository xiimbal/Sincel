<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("Conexion.class.php");
include_once("Catalogo.class.php");
include_once("SolicitudRetiroGeneral.class.php");
include_once("SolicitudRetiro.class.php");
include_once("Mail.class.php");
include_once("Usuario.class.php");
include_once("Parametros.class.php");
include_once("ParametroGlobal.class.php");

class MovimientoEquipo {

    private $idalmacen;
    private $claveCentro;
    private $seriesAlm = Array();
    private $seriesLoc = Array();
    private $idSolicitudGeneral;
    private $serie;
    private $solicitudRetiro = "";
    private $solicitudRetiroGeneral = "";
    private $tipomovimiento = "";
    private $user = "";
    private $comentario = "";
    private $lectura = "";
    private $reporte_historico;
    private $ClaveMail;
    private $idUsuario = "";
    private $Fecha = "";
    private $Causa_Movimiento = "";
    private $id_lectura;
    private $empresa;

    /**
     * Cambia a almacén un equipo tomando en cuenta $serie siendo el No de serie
     */
    public function cambiarAlmacen() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if ($this->solicitudRetiro == "") {
            $this->solicitudRetiro = new SolicitudRetiro();
            if (isset($this->empresa)) {
                $this->solicitudRetiro->setEmpresa($this->empresa);
            }
        }
        if ($this->solicitudRetiroGeneral == "") {
            $this->solicitudRetiroGeneral = new SolicitudRetiroGeneral();
            if (isset($this->empresa)) {
                $this->solicitudRetiroGeneral->setEmpresa($this->empresa);
            }
        }

        $query = $catalogo->obtenerLista("SELECT c_inventarioequipo.NoParteEquipo AS Parte,c_inventarioequipo.IdAnexoClienteCC AS IDAnexo  FROM
c_inventarioequipo WHERE c_inventarioequipo.NoSerie='" . $this->serie . "'");
        $rs = mysql_fetch_array($query);
        if ($rs != NULL && $rs['IDAnexo'] != NULL) {//echo "Viene de cliente";
            if ($this->verificarSolicitudexistente()) {
                $query = $catalogo->obtenerLista("SELECT
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCliente, 
	(CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.ClaveCentroCosto FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.IdAnexoClienteCC ELSE ka.IdAnexoClienteCC END) AS IDkAnexo,
        c_equipo.NoParte AS NoParte
FROM `c_inventarioequipo` AS cinv
LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
WHERE
cinv.NoSerie='" . $this->serie . "'");
                $rs = mysql_fetch_array($query);
                $clavecentrocosto = $rs['ClaveCentroCosto'];
                $query = $catalogo->obtenerLista("SELECT * FROM c_bitacora WHERE NoSerie='$this->serie'");
                $rs = mysql_fetch_array($query);
                $idbitacora = $rs['id_bitacora'];
                $this->claveCentro = $clavecentrocosto;
                $this->solicitudRetiro->setActivo(1);
                $this->solicitudRetiro->setPantalla("Cambio de equipo PHP");
                $this->solicitudRetiro->setClaveLocalidad($clavecentrocosto);
                $this->solicitudRetiro->setIdAlmacen($this->idalmacen);
                $this->solicitudRetiro->setIdBitacora($idbitacora);
                $this->solicitudRetiro->setUsuarioCreacion($this->user);
                $this->solicitudRetiro->setUsuarioUltimaModificacion($this->user);
                $this->solicitudRetiro->setId_lectura($this->id_lectura);
                if ($this->solicitudRetiroGeneral->getIdSolicitudRetiroGeneral() == "") {
                    $mail = new Mail();
                    if (isset($this->empresa)) {
                        $mail->setEmpresa($this->empresa);
                    }
                    $this->solicitudRetiroGeneral->setCausa_Movimiento($this->Causa_Movimiento);
                    $this->solicitudRetiroGeneral->setAceptada(0);
                    $this->solicitudRetiroGeneral->setActivo(1);
                    $this->solicitudRetiroGeneral->setClave($mail->generaPass());
                    $this->solicitudRetiroGeneral->setContestado(0);
                    $this->solicitudRetiroGeneral->setPantalla("Cambio de equipo PHP");
                    $this->solicitudRetiroGeneral->setUsuarioCreacion($this->user);
                    $this->solicitudRetiroGeneral->setUsuarioUltimaModificacion($this->user);
                    $this->solicitudRetiroGeneral->setTipoReporte($this->tipomovimiento);
                    $this->solicitudRetiroGeneral->setFechaReporte($this->Fecha);
                    $this->solicitudRetiroGeneral->newRegistro();
                }
                $this->solicitudRetiro->setIdSolicitudRetiroGeneral($this->solicitudRetiroGeneral->getIdSolicitudRetiroGeneral());
                $this->solicitudRetiro->newRegistro();
                array_push($this->seriesLoc, $this->serie);
            }
        } else {//echo "Viene de almacen";
            $query = $catalogo->obtenerLista("SELECT k_almacenequipo.id_almacen AS ID FROM k_almacenequipo WHERE k_almacenequipo.NoSerie='" . $this->serie . "'");
            $rs = mysql_fetch_array($query);
            $catalogo->obtenerLista("UPDATE k_almacenequipo SET id_almacen='9',UsuarioUltimaModificacion='" . $this->user . "' WHERE k_almacenequipo.NoSerie='" . $this->serie . "';");
            $consulta = "INSERT INTO movimientos_equipo(NoSerie,almacen_nuevo,almacen_anterior,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente,causa_movimiento,IdTipoMovimiento,id_lectura)
                VALUES('" . $this->serie . "','" . $this->idalmacen . "','" . $rs['ID'] . "',4,NOW(),'" . $this->user . "',NOW(),'" . $this->user . "',NOW(),'PHP modificacion_equipos',1,'" . $this->comentario . "','" . $this->tipomovimiento . "'," . $this->lectura . ");";
            $id_movimiento = $catalogo->insertarRegistro($consulta);

            $catalogo->insertarRegistro("INSERT INTO reportes_movimientos(id_reportes,id_movimientos)
                VALUES(" . $this->reporte_historico . "," . $id_movimiento . ");");
            array_push($this->seriesAlm, $this->serie);
        }
    }

    /**
     * verifica que el equipo no tenga solicitudes existentes
     * @return boolean
     */
    public function verificarSolicitudexistente() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM c_solicitudretiro AS s
INNER JOIN c_solictudretirogeneral AS sg ON sg.IdSolicitudRetiroGeneral=s.IdSolicitudRetiroGeneral
INNER JOIN c_bitacora AS b ON b.id_bitacora=s.IdBitacora
WHERE b.NoSerie='$this->serie' AND sg.Contestado='0'");
        $num_rows = mysql_num_rows($query);
        if ($num_rows > 0) {
            echo "El equipo $this->serie tiene actualmente una solicitud de retiro.";
            return false;
        } else {
            return true;
        }
    }

    /**
     * Envía el mail de solicitud
     */
    public function EnviarMailCli() {
        if (count($this->seriesLoc) > 0) {
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }

            /*             * ********************************************************CORREO************************************************************************ */
            $count_mysql = 0;
            $cadena = "";
            foreach ($this->seriesLoc as $value) {
                $cadena.="'$value',";
            }
            $cadena = substr($cadena, 0, -1);
            $queryprin = $catalogo->obtenerLista("SELECT
                    b.NoSerie AS NoSerie,
                    e.Modelo,
                    e.Descripcion AS Descripcion,
                    c_inventarioequipo.Ubicacion AS Ubicacion,
                    c_inventarioequipo.NoParteEquipo AS NoParte,
                    k_almacenequipo.Ubicacion AS UbicacionAlm
                    FROM  c_bitacora as b 
                    LEFT JOIN c_equipo as e ON e.NoParte = b.NoParte
                    LEFT JOIN c_inventarioequipo ON c_inventarioequipo.NoSerie=b.NoSerie
                    LEFT JOIN k_almacenequipo ON k_almacenequipo.NoSerie=b.NoSerie
                    WHERE b.NoSerie IN($cadena)");
            $numero_filas = mysql_num_rows($queryprin);
            $usuario = new Usuario();
            if (isset($this->empresa)) {
                $usuario->setEmpresa($this->empresa);
            }
            
            $usuario->getRegistroById($this->idUsuario);
            //titulo
            $text1 = "<h4>Movimiento Realizado por " . $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno() . "</h4><br/>";
            while ($rss = mysql_fetch_array($queryprin)) {
                //titulo
                $text1.="<h4>Reporte de movimiento de equipo</h4><br/>";
                //tabla 1
                $text1.="<table border='1'><tr><td>FECHA</td><td>" . date("Y-m-d H:i:s") . "</td></tr></table><br/>";
                //tabla 2
                $text1.="<table border='1'><tr><td colspan='4' align='center'><b>CAUSA</b></td></tr>";
                $text1.="<tr><td colspan='4'>$this->Causa_Movimiento</td></tr>";
                $text1.="<tr><td colspan='4' align='center'><b>DESTINO</b></td></tr>";
                $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre,CONCAT(c_domicilio_almacen.Calle,\" #\",c_domicilio_almacen.NoExterior) AS Calle,
                            c_domicilio_almacen.Colonia AS Colonia,
                            c_domicilio_almacen.Delegacion AS Delegacion,
                            c_domicilio_almacen.Estado AS Estado,
                            c_domicilio_almacen.CodigoPostal AS CP FROM c_almacen LEFT JOIN c_domicilio_almacen ON c_domicilio_almacen.IdAlmacen = c_almacen.id_almacen WHERE c_almacen.id_almacen='" . $this->idalmacen . "'");
                if ($resultSet = mysql_fetch_array($query)) {
                    $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                    $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                    $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                    $text1.="<tr><td>DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                    $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                    $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>: " . $resultSet['CP'] . "</td></tr>";
                } else {
                    $query = $catalogo->obtenerLista("SELECT c_almacen.nombre_almacen AS Nombre FROM c_almacen WHERE c_almacen.id_almacen='" . $this->idalmacen . "'");
                    if ($resultSet = mysql_fetch_array($query)) {
                        $text1.="<tr><td>ALMACÉN</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                    }
                }
                $text1.="<tr><td colspan='4' align='center'><b>ORIGEN</b></td></tr>";
                $query = $catalogo->obtenerLista("SELECT
                            c_cliente.NombreRazonSocial AS Nombre,
                            c_cliente.RFC AS RFC,
                            CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoMaterno,\" \",c_usuario.ApellidoPaterno) AS Contacto,
                            c_centrocosto.Nombre AS CentroCosto,
                            CONCAT(c_domicilio.Calle,\" #\",c_domicilio.NoExterior) AS Calle,
                            c_domicilio.Colonia AS Colonia,
                            c_domicilio.Delegacion AS Delegacion,
                            c_domicilio.Estado AS Estado,
                            c_domicilio.CodigoPostal AS CP
                            FROM
                                    c_cliente
                            INNER JOIN c_centrocosto ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
                            INNER JOIN c_usuario ON c_usuario.IdUsuario=c_cliente.EjecutivoCuenta
                            INNER JOIN c_domicilio ON c_centrocosto.ClaveCentroCosto=c_domicilio.ClaveEspecialDomicilio
                            WHERE c_centrocosto.ClaveCentroCosto='" . $this->claveCentro . "'");
                if ($resultSet = mysql_fetch_array($query)) {
                    $text1.="<tr><td>NOMBRE ó RAZON SOCIAL</td><td colspan='3'>" . $resultSet['Nombre'] . "</td></tr>";
                    $text1.="<tr><td>CONTACTO COMERCIAL</td><td>" . $resultSet['Contacto'] . "</td><td colspan='2'><b>RFC:</b>" . $resultSet['RFC'] . "</tr>";
                    $text1.="<tr><td colspan='4'>Localidad: " . $resultSet['CentroCosto'] . "</td></tr>";
                    $text1.="<tr><td>CALLE Y NÚMERO</td><td colspan='3'>" . $resultSet['Calle'] . "</td></tr>";
                    $text1.="<tr><td>COLONIA</td><td colspan='3'>" . $resultSet['Colonia'] . "</td></tr>";
                    $text1.="<tr><td >DELEGACION ó MUNICIPIO</td><td colspan='3'>" . $resultSet['Delegacion'] . "</td></tr>";
                    $text1.="<tr><td>CIUDAD / ESTADO</td><td colspan='3'>" . $resultSet['Estado'] . "</td></tr>";
                    $text1.="<tr><td>TELEFONO Y EXTENSION</td><td></td><td colspan='2'><b>C. POSTAL</b>:" . $resultSet['CP'] . "</td></tr>";
                }
                $text1.="</table><br/><table border='1'><tr><td colspan='4' align='center'><b>DESCRIPCION DE EQUIPOS</b></td></tr>";
                $text1.="<tr><td>No Serie</td><td>Modelo</td><td>Ubicación</td><td>Contadores</td></tr>";


                mysql_data_seek($queryprin, $count_mysql);
                $aux_ant = "";
                while ($rss = mysql_fetch_array($queryprin)) {
                    $text1.="<tr>";
                    if ($aux_ant == "" || $aux_ant == $this->claveCentro) {
                        $val = false;
                        $query4 = $catalogo->obtenerLista("SELECT ts.Nombre AS servicio, ts.IdTipoServicio AS ID FROM `k_equipocaracteristicaformatoservicio` AS ke
                                    INNER JOIN c_tiposervicio AS ts ON ke.NoParte = '" . $rss['NoParte'] . "' AND ts.IdTipoServicio = ke.IdTipoServicio;");
                        while ($resultSet = mysql_fetch_array($query4)) {
                            if ($resultSet['ID'] == 1) {
                                $val = true;
                            }
                        }
                        $query = $catalogo->obtenerLista("SELECT
                                            DATE(c_lectura.Fecha) AS Fecha,
                                            c_lectura.ContadorBNPaginas AS ContadorBN,
                                            c_lectura.ContadorColorPaginas AS ContadorCL,
                                            c_lectura.ContadorBNML AS ContadorBNML,
                                            c_lectura.ContadorColorML AS ContadorCLML,
                                            c_lectura.NivelTonNegro AS NivelTonNegro,
                                            c_lectura.NivelTonCian AS NivelTonCian,
                                            c_lectura.NivelTonMagenta AS NivelTonMagenta,
                                            c_lectura.NivelTonAmarillo AS NivelTonAmarillo
                                            FROM movimientos_equipo
                                            INNER JOIN c_lectura ON c_lectura.NoSerie = movimientos_equipo.NoSerie
                                            WHERE movimientos_equipo.NoSerie ='" . $rss['NoSerie'] . "' ORDER BY Fecha DESC");
                        if ($rs = mysql_fetch_array($query)) {
                            if ($val)
                                $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td><td>BN: " . $rs['ContadorBN'] . "<br/>Color: " . $rs['ContadorCL'] . "</td>";
                            else
                                $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td><td>BN: " . $rs['ContadorBN'] . "</td>";
                        } else {
                            $text1.="<td>" . $rss['NoSerie'] . "</td><td>" . $rss['Modelo'] . "</td><td>" . $rss['Ubicacion'] . "</td> <td></td>";
                        }
                        $aux_ant = $this->claveCentro;
                    } else {
                        break;
                    }
                    $text1.="</tr>";
                    $count_mysql++;
                }//Cierre
                if ($numero_filas > $count_mysql) {
                    mysql_data_seek($queryprin, $count_mysql);
                }
                $text1.="</table><br/><br/><br/>";
            }
            $parametros = new Parametros();
            if (isset($this->empresa)) {
                $parametros->setEmpresa($this->empresa);
            }
            $parametros->getRegistroById(8);
            $liga = $parametros->getDescripcion();
            if(isset($_SESSION['idEmpresa'])){
                $id_empresa = $_SESSION['idEmpresa'];
            }else{
                if(isset($this->empresa)){
                    $id_empresa = $this->empresa;
                }else{
                    $id_empresa = 1;
                }
            }
            $text1.="<br/>Para aceptar la solicitud $liga/aceptaRetiro.php?clv=" . $this->solicitudRetiroGeneral->getClave() . "&soli=" . $this->solicitudRetiroGeneral->getIdSolicitudRetiroGeneral() . "&awr=1&uguid=" . $id_empresa;
            $text1.="<br/>Para rechazar la solicitud $liga/aceptaRetiro.php?clv=" . $this->solicitudRetiroGeneral->getClave() . "&soli=" . $this->solicitudRetiroGeneral->getIdSolicitudRetiroGeneral() . "&awr=2&uguid=" . $id_empresa;
            $mail = new Mail();
            if (isset($this->empresa)) {
                $mail->setEmpresa($this->empresa);
            }
            $mail->setSubject("Cambio a Almacén No " . $this->solicitudRetiroGeneral->getIdSolicitudRetiroGeneral());
            $parametroGlobal = new ParametroGlobal();
            if (isset($this->empresa)) {
                $parametroGlobal->setEmpresa($this->empresa);
            }
            if ($parametroGlobal->getRegistroById("8")) {
                $mail->setFrom($parametroGlobal->getValor());
            } else {
                $mail->setFrom("scg-salida@scgenesis.mx");
            }
            $mail->setBody($text1);
            $query4 = $catalogo->obtenerLista("SELECT correo FROM `c_correossolicitud` WHERE Activo = 1 AND TipoSolicitud = 7;");
            $correos = array();
            $z = 0;
            while ($rs = mysql_fetch_array($query4)) {
                $correos[$z] = $rs['correo'];
                $z++;
            }
            $bol = false;
            foreach ($correos as $value) {
                if (isset($value) && $value != "" && filter_var($value, FILTER_VALIDATE_EMAIL)) {/* Si el correo es valido */
                    $mail->setTo($value);
                    if ($mail->enviarMail() != "1") {
                        echo "Error: No se pudo enviar el correo de solicitud.";
                    } else {
                        $bol = true;
                    }
                }
            }
            if ($bol) {
                echo "<br/>Se ha creado y enviado la solicitud de retiro.";
            }
        }
    }

    public function getId_lectura() {
        return $this->id_lectura;
    }

    public function setId_lectura($id_lectura) {
        $this->id_lectura = $id_lectura;
    }

    public function getClaveMail() {
        return $this->ClaveMail;
    }

    public function setClaveMail($ClaveMail) {
        $this->ClaveMail = $ClaveMail;
    }

    public function getSeriesAlm() {
        return $this->seriesAlm;
    }

    public function getSeriesLoc() {
        return $this->seriesLoc;
    }

    public function getSolicitudRetiro() {
        return $this->solicitudRetiro;
    }

    public function getSolicitudRetiroGeneral() {
        return $this->solicitudRetiroGeneral;
    }

    public function getTipomovimiento() {
        return $this->tipomovimiento;
    }

    public function getUser() {
        return $this->user;
    }

    public function getComentario() {
        return $this->comentario;
    }

    public function getLectura() {
        return $this->lectura;
    }

    public function getReporte_historico() {
        return $this->reporte_historico;
    }

    public function setSeriesAlm($seriesAlm) {
        $this->seriesAlm = $seriesAlm;
    }

    public function setSeriesLoc($seriesLoc) {
        $this->seriesLoc = $seriesLoc;
    }

    public function setSolicitudRetiro($solicitudRetiro) {
        $this->solicitudRetiro = $solicitudRetiro;
    }

    public function setSolicitudRetiroGeneral($solicitudRetiroGeneral) {
        $this->solicitudRetiroGeneral = $solicitudRetiroGeneral;
    }

    public function setTipomovimiento($tipomovimiento) {
        $this->tipomovimiento = $tipomovimiento;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    public function setLectura($lectura) {
        $this->lectura = $lectura;
    }

    public function setReporte_historico($reporte_historico) {
        $this->reporte_historico = $reporte_historico;
    }

    public function getSerie() {
        return $this->serie;
    }

    public function getIdSolicitudGeneral() {
        return $this->idSolicitudGeneral;
    }

    public function setSerie($serie) {
        $this->serie = $serie;
    }

    public function setIdSolicitudGeneral($idSolicitudGeneral) {
        $this->idSolicitudGeneral = $idSolicitudGeneral;
    }

    public function getIdalmacen() {
        return $this->idalmacen;
    }

    public function getClaveCentro() {
        return $this->claveCentro;
    }

    public function setIdalmacen($idalmacen) {
        $this->idalmacen = $idalmacen;
    }

    public function setClaveCentro($claveCentro) {
        $this->claveCentro = $claveCentro;
    }

    public function getIdUsuario() {
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function getCausa_Movimiento() {
        return $this->Causa_Movimiento;
    }

    public function setCausa_Movimiento($Causa_Movimiento) {
        $this->Causa_Movimiento = $Causa_Movimiento;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}
