<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");
include_once("EquipoCaracteristicasFormatoServicio.class.php");

/**
 * Description of Lectura
 *
 * @author MAGG
 */
class Lectura {

    private $IdLectura;
    private $IdLecturaMasiva;
    private $NoSerie;
    private $Fecha;
    private $ContadorBNPaginas;
    private $ContadorColorPaginas;
    private $ContadorBNML;
    private $ContadorColorML;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $NivelTonNegro;
    private $NivelTonCian;
    private $NivelTonMagenta;
    private $NivelTonAmarillo;
    private $LecturaCorte;
    private $IdSolicitud;
    private $Comentario;
    private $mensaje;
    private $empresa;

    //**************************************
    private $claveLocalidadCargaLectura;
    private $clienteCargaLectura;
    private $localidadCargaLectura;

    public function getRegistroById($clave) {
        $consulta = ("SELECT * FROM `c_lectura` WHERE IdLectura = $clave;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdLectura = $rs['IdLectura'];
            $this->NoSerie = $rs['NoSerie'];
            $this->Fecha = $rs['Fecha'];
            $this->ContadorBNPaginas = $rs['ContadorBNPaginas'];
            $this->ContadorColorPaginas = $rs['ContadorColorPaginas'];
            $this->ContadorBNML = $rs['ContadorBNML'];
            $this->ContadorColorML = $rs['ContadorColorML'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->NivelTonNegro = $rs['NivelTonNegro'];
            $this->NivelTonCian = $rs['NivelTonCian'];
            $this->NivelTonMagenta = $rs['NivelTonMagenta'];
            $this->NivelTonAmarillo = $rs['NivelTonAmarillo'];
            $this->LecturaCorte = $rs['LecturaCorte'];
            $this->IdSolicitud = $rs['IdSolicitud'];
            return true;
        }
        return false;
    }
    
    public function getRegistroByIdSolicitud($idSolicitud, $NoSerie) {
        $consulta = ("SELECT * FROM `c_lectura` WHERE IdSolicitud = $idSolicitud AND NoSerie = '$NoSerie';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdLectura = $rs['IdLectura'];
            $this->NoSerie = $rs['NoSerie'];
            $this->Fecha = $rs['Fecha'];
            $this->ContadorBNPaginas = $rs['ContadorBNPaginas'];
            $this->ContadorColorPaginas = $rs['ContadorColorPaginas'];
            $this->ContadorBNML = $rs['ContadorBNML'];
            $this->ContadorColorML = $rs['ContadorColorML'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->NivelTonNegro = $rs['NivelTonNegro'];
            $this->NivelTonCian = $rs['NivelTonCian'];
            $this->NivelTonMagenta = $rs['NivelTonMagenta'];
            $this->NivelTonAmarillo = $rs['NivelTonAmarillo'];
            $this->LecturaCorte = $rs['LecturaCorte'];
            $this->IdSolicitud = $rs['IdSolicitud'];
            return true;
        }
        return false;
    }

    /**
     * Nuevo registro en c_lectura
     * @return boolean true en caso de insertar correctamente, false en caso contrario.
     */
    public function newRegistro() {
        if (isset($this->Fecha) && $this->Fecha != "") {
            $fecha = "'" . $this->Fecha . "'";
        } else {
            $fecha = "NOW()";
        }

        if (isset($this->IdSolicitud) && $this->IdSolicitud != "") {
            $solicitud = $this->IdSolicitud;
        } else {
            $solicitud = "null";
        }

        if (!isset($this->NivelTonNegro) || $this->NivelTonNegro == "") {
            $this->NivelTonNegro = "null";
        }

        $consulta = "INSERT INTO c_lectura (IdLectura, NoSerie, Fecha, ContadorBNPaginas, ContadorBNML, 
            ContadorColorPaginas, ContadorColorML, NivelTonNegro, NivelTonMagenta, NivelTonCian, NivelTonAmarillo, LecturaCorte, IdSolicitud, Activo,UsuarioCreacion,
            FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Comentario) 
            VALUES(0, '$this->NoSerie', $fecha, $this->ContadorBNPaginas, $this->ContadorBNML,
            $this->ContadorColorPaginas, $this->ContadorColorML, $this->NivelTonNegro, $this->NivelTonMagenta, $this->NivelTonCian, $this->NivelTonAmarillo, 
            $this->LecturaCorte, $solicitud, $this->Activo, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla', 
            '$this->Comentario' );";
        //echo $consulta;
        $catalogo = new Catalogo();
        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdLectura = $catalogo->insertarRegistro($consulta);
        if ($this->IdLectura != NULL && $this->IdLectura != 0) {
            return true;
        }
        return false;
    }

    public function newRegistroCargasMasivas() {

        $consulta = "INSERT INTO c_lecturaMasiva(IdLecturasMasivas,RegistrosOK,RegistrosError,FechaRegistro,UsuarioCreacion,
            FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,0,0, NOW(), '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');";
        //echo $consulta;
        $catalogo = new Catalogo();
        
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdLecturaMasiva = $catalogo->insertarRegistro($consulta);
        if ($this->IdLecturaMasiva != NULL && $this->IdLecturaMasiva != 0) {
            return true;
        }
        return false;
    }

    public function newRegistrosCargasMasivas($Serie, $Cliente, $Ubicacion, $Status){

        $consulta = "INSERT INTO c_registrosLecturaM(IdRegistrosLecturaM,Serie,Cliente,Ubicacion,Status,IdLecturaMasivas) 
            VALUES(0,'$Serie','$Cliente','$Ubicacion','$Status',$this->IdLecturaMasiva);";
        //echo $consulta;
        $catalogo = new Catalogo();

        $id = $catalogo->insertarRegistro($consulta);
        if ($id != NULL && $id != 0) { 
            return true;
        }
        return false;
    }

    //********************************************  Obtener el registro por serie
    public function getRegistroBySerie($Serie){
        $consulta = "SELECT
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre,
            (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        cinv.NoSerie AS NoSerie,
        bit.VentaDirecta AS VentaDirecta
        FROM `c_inventarioequipo` AS cinv
        LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_tfscliente AS tf ON tf.ClaveCliente=c.ClaveCliente
        LEFT JOIN c_usuario AS u ON u.IdUsuario=tf.IdUsuario
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
        INNER JOIN c_bitacora AS bit ON bit.NoSerie=cinv.NoSerie
        LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
        WHERE cinv.NoSerie = $Serie";
        $catalogo = new Catalogo();
     
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->claveLocalidadCargaLectura = $rs['ClaveCentroCosto'];
            $this->clienteCargaLectura = $rs['NombreCliente'];
            $this->localidadCargaLectura = $rs['CentroCostoNombre'];
            return true;
        }
        return false;
    }

    public function editarRegistro() {
        $consulta = "UPDATE c_lectura SET NoSerie = '$this->NoSerie', Fecha = '$this->Fecha', ContadorBNPaginas = $this->ContadorBNPaginas, ContadorBNML = $this->ContadorBNML, 
            ContadorColorPaginas = $this->ContadorColorPaginas, ContadorColorML = $this->ContadorColorML, NivelTonNegro = $this->NivelTonNegro, 
            NivelTonMagenta = $this->NivelTonMagenta, NivelTonCian = $this->NivelTonCian, NivelTonAmarillo = $this->NivelTonAmarillo,Comentario='$this->Comentario',
            LecturaCorte = $this->LecturaCorte, Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',
            FechaUltimaModificacion = NOW(),Pantalla = '$this->Pantalla' WHERE IdLectura = $this->IdLectura";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene las lecturas por cliente, por localidad o por centro de costo
     * @param type $cliente clave del cliente
     * @param type $cc clave de localidad (NULL)
     * @param type $idCC clave Centro de costo (NULL)
     * @param type $year 
     * @param type $month
     * @param type $fecha_lectura fecha limite de las lecturas que se consideran
     * @return type resultSet con los datos de la consulta generada
     */
    public function getLecturasByCC($cliente, $cc, $idCC, $year, $month, $fecha_lectura) {
        $consulta = "SELECT DISTINCT(cie.NoSerie) AS NoSerie,
	cie.NoParteEquipo,
	cie.ClaveEspKServicioFAIM,
	e.Modelo,
	kacc.IdAnexoClienteCC,
	(CASE WHEN !ISNULL(ks.IdKServiciogimgfa) THEN cc2.ClaveCentroCosto ELSE kacc.CveEspClienteCC END) AS ClaveCC,
        (CASE WHEN !ISNULL(ks.IdKServiciogimgfa) THEN cc2.Nombre ELSE cc.Nombre END) AS NombreCentroCosto,
        (CASE WHEN !ISNULL(ks.IdKServiciogimgfa) THEN cc2.id_cr ELSE cc.id_cr END) AS IdCentroCosto,
        (CASE WHEN !ISNULL(ks.IdKServiciogimgfa) THEN c2.Clavecliente ELSE c.ClaveCliente END) AS ClaveCliente,
	(CASE WHEN !ISNULL(ks.IdKServiciogimgfa) THEN cc2.Nombre ELSE cc.Nombre END) AS Nombre,
        (SELECT group_concat( DISTINCT(CONVERT(IdCaracteristicaEquipo, CHAR(8))) separator ', ') FROM k_equipocaracteristicaformatoservicio AS ke
	WHERE ke.NoParte = cie.NoParteEquipo GROUP BY ke.NoParte) AS caracteristicas,
	(SELECT group_concat( DISTINCT(CONVERT(IdTipoServicio, CHAR(8))) separator ', ') FROM k_equipocaracteristicaformatoservicio AS ke
	WHERE ke.NoParte = cie.NoParteEquipo GROUP BY ke.NoParte) AS servicio,
	l.IdLectura, l.Fecha, l.ContadorBNPaginas, l.ContadorColorPaginas, l.ContadorBNML, l.ContadorColorML, l.NivelTonAmarillo, l.NivelTonCian, 
        l.NivelTonMagenta, l.NivelTonNegro, l.Comentario,
	l1.IdLectura AS IdLecturaA, l1.Fecha AS FechaA, l1.ContadorBNPaginas AS ContadorBNPaginasA, l1.ContadorColorPaginas AS ContadorColorPaginasA, 
	l1.ContadorBNML AS ContadorBNMLA, l1.ContadorColorML AS ContadorColorMLA, l1.NivelTonAmarillo AS NivelTonAmarilloA, 
        l1.NivelTonCian AS NivelTonCianA, l1.NivelTonMagenta AS NivelTonMagentaA, l1.NivelTonNegro AS NivelTonNegroA, l1.UsuarioUltimaModificacion, l1.FechaCreacion,
	(SELECT CASE WHEN !ISNULL(lmaxPBN.ContadorBNPaginas) THEN lmaxPBN.ContadorBNPaginas WHEN !ISNULL(lmaxMLBN.ContadorBNML) THEN lmaxMLBN.ContadorBNML ELSE 0 END) AS MAXBN,
	(SELECT CASE WHEN !ISNULL(lmaxtPBN.ContadorBN) THEN lmaxtPBN.ContadorBN ELSE 0 END) MAXTBN,
	(SELECT CASE WHEN MAXBN > MAXTBN THEN MAXBN ELSE MAXTBN END) AS MaxContadorBN,
	(SELECT CASE WHEN !ISNULL(lmaxPColor.ContadorColorPaginas) THEN lmaxPColor.ContadorColorPaginas WHEN !ISNULL(lmaxMLColor.ContadorColorML) THEN lmaxMLColor.ContadorColorML ELSE 0 END) AS MAXCL,
	(SELECT CASE WHEN !ISNULL(lmaxtPColor.ContadorCL) THEN lmaxtPColor.ContadorCL ELSE 0 END) MAXTCL,
	(SELECT CASE WHEN MAXCL > MAXTCL THEN MAXCL ELSE MAXTCL END) AS MaxContadorCL,
        b.VentaDirecta
	FROM k_anexoclientecc AS kacc    
	LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
	LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa
	LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
	LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo
	LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = cie.NoSerie AND LecturaCorte = 1 AND Fecha BETWEEN '$year-$month-01' AND DATE_SUB(DATE_ADD('$year-$month-01',INTERVAL 1 MONTH), INTERVAL 1 DAY))
        LEFT JOIN c_lectura AS l1 ON l1.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = cie.NoSerie AND LecturaCorte = 1 AND Fecha BETWEEN DATE_SUB('$year-$month-01',INTERVAL 1 MONTH) AND DATE_SUB('$year-$month-01',INTERVAL 1 DAY))
	LEFT JOIN c_lectura AS lmaxPBN ON lmaxPBN.NoSerie = cie.NoSerie AND lmaxPBN.ContadorBNPaginas = (SELECT MAX(ContadorBNPaginas) FROM c_lectura WHERE NoSerie = cie.NoSerie AND Fecha <= '$fecha_lectura')
	LEFT JOIN c_lectura AS lmaxMLBN ON lmaxMLBN.NoSerie = cie.NoSerie AND lmaxMLBN.ContadorBNML = (SELECT MAX(ContadorBNML) FROM c_lectura WHERE NoSerie = cie.NoSerie AND Fecha <= '$fecha_lectura')	
	LEFT JOIN c_lecturasticket AS lmaxtPBN ON lmaxtPBN.ClvEsp_Equipo = cie.NoSerie AND lmaxtPBN.ContadorBN = (SELECT MAX(ContadorBN) FROM c_lecturasticket WHERE ClvEsp_Equipo = cie.NoSerie AND Fecha <= '$fecha_lectura')	
	LEFT JOIN c_lectura AS lmaxPColor ON lmaxPColor.NoSerie = cie.NoSerie AND lmaxPColor.ContadorColorPaginas = (SELECT MAX(ContadorColorPaginas) FROM c_lectura WHERE NoSerie = cie.NoSerie AND Fecha <= '$fecha_lectura')
	LEFT JOIN c_lectura AS lmaxMLColor ON lmaxMLColor.NoSerie = cie.NoSerie AND lmaxMLColor.ContadorColorML = (SELECT MAX(ContadorColorML) FROM c_lectura WHERE NoSerie = cie.NoSerie AND Fecha <= '$fecha_lectura')	
	LEFT JOIN c_lecturasticket AS lmaxtPColor ON lmaxtPColor.ClvEsp_Equipo = cie.NoSerie AND lmaxtPColor.ContadorCL = (SELECT MAX(ContadorCL) FROM c_lecturasticket WHERE ClvEsp_Equipo = cie.NoSerie AND Fecha <= '$fecha_lectura')";
        if (isset($cc) && $cc != "") {
            $consulta .= " WHERE !ISNULL(cie.NoSerie) AND b.Activo = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0) AND ((kacc.CveEspClienteCC = '$cc' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$cc')) ORDER BY NoSerie DESC;";
        } else if (isset($idCC) && $idCC != "") {
            $consulta .= " WHERE !ISNULL(cie.NoSerie) AND b.Activo = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0) HAVING IdCentroCosto = $idCC ORDER BY NoSerie DESC;";
        } else {
            $consulta .= " WHERE !ISNULL(cie.NoSerie) AND b.Activo = 1 AND (ISNULL(VentaDirecta) OR VentaDirecta = 0) AND (c.ClaveCliente = '$cliente' OR c2.ClaveCliente = '$cliente') ORDER BY NombreCentroCosto, NoSerie DESC;";
        }
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }        
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene las ultimas lecturas de las series recibidas
     * @param type $series array con las series de los equipos, con el formato 'serie'
     * @return array lecturas de los equipos
     */
    public function getUltimasLecturasPorSeries($series){
        $lecturas = array();
        
        if(empty($series)){//Si no se reciben series
            return $lecturas;
        }
        
        $catalogo = new Catalogo();
        $equipo = new EquipoCaracteristicasFormatoServicio();
        $consultaNiveles = "SELECT 
            ie.NoSerie, ie.NoParteEquipo, e.Modelo,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.Fecha ELSE lt.Fecha END) AS Fecha,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNPaginas ELSE lt.ContadorBN END) AS ContadorBN,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorPaginas ELSE lt.ContadorCL END)AS ContadorCL,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNML ELSE lt.ContadorBNA END) AS ContadorBNML,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorML ELSE lt.ContadorCLA END) AS ContadorCLML,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonNegro ELSE lt.NivelTonNegro END) AS NivelTonNegro,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonCian ELSE lt.NivelTonCian END) AS NivelTonCian,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonMagenta ELSE lt.NivelTonMagenta END) AS NivelTonMagenta,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonAmarillo ELSE lt.NivelTonAmarillo END) AS NivelTonAmarillo,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.UsuarioCreacion ELSE lt.UsuarioCreacion END) AS UsuarioCreacion,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN NULL ELSE lt.fk_idticket END) AS IdTicket
            FROM c_inventarioequipo ie
            LEFT JOIN c_lecturasticket lt ON lt.ClvEsp_Equipo = ie.NoSerie AND lt.Fecha=(SELECT MAX(lt2.Fecha)FROM c_lecturasticket lt2 WHERE lt2.ClvEsp_Equipo=ie.NoSerie)
            LEFT join c_lectura l ON l.NoSerie=ie.NoSerie AND l.Fecha=(SELECT MAX(l2.Fecha) FROM c_lectura l2 WHERE l2.NoSerie=ie.NoSerie)
            LEFT JOIN c_equipo e ON e.NoParte = ie.NoParteEquipo
            WHERE ie.NoSerie IN (".  implode(",", $series).")";
        
        $result = $catalogo->obtenerLista($consultaNiveles);
        while($rs = mysql_fetch_array($result)){
            if(!$equipo->isFormatoAmplio($rs['NoParteEquipo'])){
                $lecturas[$rs['NoSerie']]['bn'] = $rs['ContadorBN'];
                $lecturas[$rs['NoSerie']]['color'] = $rs['ContadorCL'];                
            }else{
                $lecturas[$rs['NoSerie']]['bn'] = $rs['ContadorBNML'];
                $lecturas[$rs['NoSerie']]['color'] = $rs['ContadorCLML'];                
            }
            $lecturas[$rs['NoSerie']]['modelo'] = $rs['Modelo'];
            $lecturas[$rs['NoSerie']]['noParte'] = $rs['NoParteEquipo'];
            $lecturas[$rs['NoSerie']]['usuario'] = $rs['UsuarioCreacion'];
            $lecturas[$rs['NoSerie']]['ticket'] = $rs['IdTicket'];
            $lecturas[$rs['NoSerie']]['fecha'] = $rs['Fecha'];
        }
        return $lecturas;
    }
    
    /**
     * Regresa la primer fecha de corte que encuentra en k_anexoclientecc de las localidades del cliente especificado.
     * @param type $cliente clave del cliente
     * @return type dia de corte, sino encuentra por consulta de bd, deja por default 1
     */
    public function getDiaDeCorteByCliente($cliente) {
        $consulta = "SELECT ClaveAnexoTecnico, CveEspClienteCC, DAY(Fecha) AS DiaCorte 
        FROM `k_anexoclientecc` 
        WHERE CveEspClienteCC IN (SELECT ClaveCentroCosto FROM c_centrocosto WHERE ClaveCliente = '$cliente') ORDER BY ClaveAnexoTecnico;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $dia = "01";
        if ($rs = mysql_fetch_array($query)) {
            $dia = $rs['DiaCorte'];
        }
        return $dia;
    }

    /**
     * Obtiene la maxima lectura registrada en c_lectura o c_lecturaticket del NoSerie especificado
     * @param type $NoSerie NoSerie a buscar
     * @return type ResultSet con los datos de las maximas lecturas de los contadores (MaxContadorBN, MaxContadorCL)
     */
    public function getMaximaLecturaNoSerie($NoSerie) {
        $consulta = "SELECT b.id_bitacora,b.NoParte,b.NoSerie,
            (SELECT CASE WHEN !ISNULL(lmaxPBN.ContadorBNPaginas) THEN lmaxPBN.ContadorBNPaginas WHEN !ISNULL(lmaxMLBN.ContadorBNML) THEN lmaxMLBN.ContadorBNML ELSE 0 END) AS MAXBN,
            (SELECT CASE WHEN !ISNULL(lmaxtPBN.ContadorBN) THEN lmaxtPBN.ContadorBN ELSE 0 END) MAXTBN,
            (SELECT CASE WHEN MAXBN > MAXTBN THEN MAXBN ELSE MAXTBN END) AS MaxContadorBN,
            (SELECT CASE WHEN !ISNULL(lmaxPColor.ContadorColorPaginas) THEN lmaxPColor.ContadorColorPaginas WHEN !ISNULL(lmaxMLColor.ContadorColorML) THEN lmaxMLColor.ContadorColorML ELSE 0 END) AS MAXCL,
            (SELECT CASE WHEN !ISNULL(lmaxtPColor.ContadorCL) THEN lmaxtPColor.ContadorCL ELSE 0 END) MAXTCL,
            (SELECT CASE WHEN MAXCL > MAXTCL THEN MAXCL ELSE MAXTCL END) AS MaxContadorCL
            FROM `c_bitacora` AS b
            LEFT JOIN c_lectura AS lmaxPBN ON lmaxPBN.NoSerie = b.NoSerie AND lmaxPBN.ContadorBNPaginas = (SELECT MAX(ContadorBNPaginas) FROM c_lectura WHERE NoSerie = b.NoSerie)
            LEFT JOIN c_lectura AS lmaxMLBN ON lmaxMLBN.NoSerie = b.NoSerie AND lmaxMLBN.ContadorBNML = (SELECT MAX(ContadorBNML) FROM c_lectura WHERE NoSerie = b.NoSerie)	
            LEFT JOIN c_lecturasticket AS lmaxtPBN ON lmaxtPBN.ClvEsp_Equipo = b.NoSerie AND lmaxtPBN.ContadorBN = (SELECT MAX(ContadorBN) FROM c_lecturasticket WHERE ClvEsp_Equipo = b.NoSerie)	
            LEFT JOIN c_lectura AS lmaxPColor ON lmaxPColor.NoSerie = b.NoSerie AND lmaxPColor.ContadorColorPaginas = (SELECT MAX(ContadorColorPaginas) FROM c_lectura WHERE NoSerie = b.NoSerie)
            LEFT JOIN c_lectura AS lmaxMLColor ON lmaxMLColor.NoSerie = b.NoSerie AND lmaxMLColor.ContadorColorML = (SELECT MAX(ContadorColorML) FROM c_lectura WHERE NoSerie = b.NoSerie)	
            LEFT JOIN c_lecturasticket AS lmaxtPColor ON lmaxtPColor.ClvEsp_Equipo = b.NoSerie AND lmaxtPColor.ContadorCL = (SELECT MAX(ContadorCL) FROM c_lecturasticket WHERE ClvEsp_Equipo = b.NoSerie)
            WHERE b.NoSerie = '$NoSerie' GROUP BY b.NoSerie ORDER BY MAXBN DESC;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    /**
     * Obtiene la maxima lectura registrada en c_lectura o c_lecturaticket del NoSerie especificado
     * @param type $NoSerie NoSerie a buscar
     * @return type ResultSet con los datos de las maximas lecturas de los contadores (MaxContadorBN, MaxContadorCL)
     */
    public function getMaximaLecturaCorteNoSerie($NoSerie, $periodo) {
        $consulta = "SELECT b.id_bitacora,b.NoParte,b.NoSerie,
            (SELECT CASE WHEN !ISNULL(lmaxPBN.ContadorBNPaginas) THEN lmaxPBN.ContadorBNPaginas WHEN !ISNULL(lmaxMLBN.ContadorBNML) THEN lmaxMLBN.ContadorBNML ELSE 0 END) AS MaxContadorBN,
            (SELECT CASE WHEN !ISNULL(lmaxPColor.ContadorColorPaginas) THEN lmaxPColor.ContadorColorPaginas WHEN !ISNULL(lmaxMLColor.ContadorColorML) THEN lmaxMLColor.ContadorColorML ELSE 0 END) AS MaxContadorCL
            FROM `c_bitacora` AS b
            LEFT JOIN c_lectura AS lmaxPBN ON lmaxPBN.NoSerie = b.NoSerie AND lmaxPBN.ContadorBNPaginas = (SELECT MAX(ContadorBNPaginas) FROM c_lectura WHERE NoSerie = b.NoSerie AND Fecha < '$periodo' AND LecturaCorte = 1)
            LEFT JOIN c_lectura AS lmaxMLBN ON lmaxMLBN.NoSerie = b.NoSerie AND lmaxMLBN.ContadorBNML = (SELECT MAX(ContadorBNML) FROM c_lectura WHERE NoSerie = b.NoSerie AND Fecha < '$periodo' AND LecturaCorte = 1)	
            LEFT JOIN c_lectura AS lmaxPColor ON lmaxPColor.NoSerie = b.NoSerie AND lmaxPColor.ContadorColorPaginas = (SELECT MAX(ContadorColorPaginas) FROM c_lectura WHERE NoSerie = b.NoSerie AND Fecha < '$periodo' AND LecturaCorte = 1)
            LEFT JOIN c_lectura AS lmaxMLColor ON lmaxMLColor.NoSerie = b.NoSerie AND lmaxMLColor.ContadorColorML = (SELECT MAX(ContadorColorML) FROM c_lectura WHERE NoSerie = b.NoSerie AND Fecha < '$periodo' AND LecturaCorte = 1)	
            WHERE b.NoSerie = '$NoSerie' GROUP BY b.NoSerie ORDER BY MaxContadorBN DESC;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    /**
     * Verifica si los equipos tienen todas las lecturas ya capturadas por cliente, dependiendo su tipo de facturacion
     * @param type $claveCliente clave del cliente
     * @param type $tipoFacturacion tipo de facturacion: 1 xCliente, 2: xLocalidad, 3: xCentroCosto
     * @return boolean true en caso de que haya todas las lecturas capturadas.
     */
    public function tieneTodasLecturas($claveCliente, $tipoFacturacion, $year, $month, $fecha_lectura, $mostrar_equipos) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $this->getLecturasByCC($claveCliente, null, null, $year, $month, $fecha_lectura);
        $todos = true;
        $this->mensaje = "";
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) {
                if (!isset($rs['IdLectura']) || $rs['IdLectura'] == "" || !isset($rs['IdLecturaA']) || $rs['IdLecturaA'] == "") {
                    $todos = false;
                    if($mostrar_equipos){//Si se necesita que se impriman los equipos que no tienen lectura
                        $mensaje = "<br/>* El equipo ".$rs['NoSerie']." no tiene la lectura de corte del periodo ".substr($catalogo->formatoFechaReportes($year."-".$month."-01"),5);                        
                        $this->mensaje .= $mensaje;
                    }else{
                        break;
                    }                    
                }
            }
        } else {
            $todos = false;
        }
        return $todos;
    }

    public function getIdLectura() {
        return $this->IdLectura;
    }

    public function setIdLectura($IdLectura) {
        $this->IdLectura = $IdLectura;
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function getContadorBNPaginas() {
        return $this->ContadorBNPaginas;
    }

    public function setContadorBNPaginas($ContadorBNPaginas) {
        $this->ContadorBNPaginas = $ContadorBNPaginas;
    }

    public function getContadorColorPaginas() {
        return $this->ContadorColorPaginas;
    }

    public function setContadorColorPaginas($ContadorColorPaginas) {
        $this->ContadorColorPaginas = $ContadorColorPaginas;
    }

    public function getContadorBNML() {
        return $this->ContadorBNML;
    }

    public function setContadorBNML($ContadorBNML) {
        $this->ContadorBNML = $ContadorBNML;
    }

    public function getContadorColorML() {
        return $this->ContadorColorML;
    }

    public function setContadorColorML($ContadorColorML) {
        $this->ContadorColorML = $ContadorColorML;
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

    public function getNivelTonNegro() {
        return $this->NivelTonNegro;
    }

    public function setNivelTonNegro($NivelTonNegro) {
        $this->NivelTonNegro = $NivelTonNegro;
    }

    public function getNivelTonCian() {
        return $this->NivelTonCian;
    }

    public function setNivelTonCian($NivelTonCian) {
        $this->NivelTonCian = $NivelTonCian;
    }

    public function getNivelTonMagenta() {
        return $this->NivelTonMagenta;
    }

    public function setNivelTonMagenta($NivelTonMagenta) {
        $this->NivelTonMagenta = $NivelTonMagenta;
    }

    public function getNivelTonAmarillo() {
        return $this->NivelTonAmarillo;
    }

    public function setNivelTonAmarillo($NivelTonAmarillo) {
        $this->NivelTonAmarillo = $NivelTonAmarillo;
    }

    public function getLecturaCorte() {
        return $this->LecturaCorte;
    }

    public function setLecturaCorte($LecturaCorte) {
        $this->LecturaCorte = $LecturaCorte;
    }

    public function getIdSolicitud() {
        return $this->IdSolicitud;
    }

    public function setIdSolicitud($IdSolicitud) {
        $this->IdSolicitud = $IdSolicitud;
    }
    
    public function getComentario() {
        return $this->Comentario;
    }

    public function setComentario($Comentario) {
        $this->Comentario = $Comentario;
    }
    
    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getMensaje() {
        return $this->mensaje;
    }

    public function setMensaje($mensaje) {
        $this->mensaje = $mensaje;
    }

    //**************************************************    *JT
    public function getClaveLocalidadCargaLectura() {
        return $this->claveLocalidadCargaLectura;
    }

    public function getClienteCargaLectura() {
        return $this->clienteCargaLectura;
}

    public function getLocalidadCargaLectura() {
        return $this->localidadCargaLectura;
    }

}

?>
