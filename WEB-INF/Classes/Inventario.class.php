<?php

include_once("Conexion.class.php");
include_once("Configuracion.class.php");
include_once("Contrato.class.php");
include_once("Anexo.class.php");
include_once("ServicioIM.class.php");
include_once("Equipo.class.php");
include_once("Catalogo.class.php");
include_once("Movimiento.class.php");

/**
 * Description of Inventario
 *
 * @author MAGG
 */
class Inventario {

    private $NoSerie;
    private $NoParteEquipo;
    private $ClaveEspKServicioFAIM;
    private $IdAnexoClienteCC;
    private $Ubicacion;
    private $ContadorInicialBNPaginas;
    private $ContadorInicialColorPaginas;
    private $ContadorInicialBNML;
    private $ContadorInicialColorML;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $ContadorActualBNPaginas;
    private $ContadorActualColorPaginas;
    private $ContadorActualBNML;
    private $ContadorActualColorML;
    private $IdKserviciogimgfa;
    private $empresa;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_inventarioequipo` WHERE NoSerie = '$id' ORDER BY FechaUltimaModificacion DESC;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->NoSerie = $rs['NoSerie'];
            $this->NoParteEquipo = $rs['NoParteEquipo'];
            $this->ClaveEspKServicioFAIM = $rs['ClaveEspKServicioFAIM'];
            $this->IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
            $this->Ubicacion = $rs['Ubicacion'];
            $this->ContadorInicialBNPaginas = $rs['ContadorInicialBNPaginas'];
            $this->ContadorInicialColorPaginas = $rs['ContadorInicialColorPaginas'];
            $this->ContadorInicialBNML = $rs['ContadorInicialBNML'];
            $this->ContadorInicialColorML = $rs['ContadorInicialColorML'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->ContadorActualBNPaginas = $rs['ContadorActualBNPaginas'];
            $this->ContadorActualColorPaginas = $rs['ContadorActualColorPaginas'];
            $this->ContadorActualBNML = $rs['ContadorActualBNML'];
            $this->ContadorActualColorML = $rs['ContadorActualColorML'];
            $this->IdKserviciogimgfa = $rs['IdKserviciogimgfa'];
            return true;
        }
        return false;
    }

    /**
     * Consulta para todos los datos de la serie en inventario, como son Cliente, Localidad, Ejecutivo, Anexo, Servicios, etc.
     * @param type $NoSerie NoSerie
     * @return type resultset con el resultado del query
     */
    public function getDatosDeInventario($NoSerie) {
        $consulta = "SELECT
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ClaveCliente, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.NombreRazonSocial FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.NombreRazonSocial END) AS NombreCliente, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_centrocosto.Nombre FROM c_centrocosto WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE cc.Nombre END) AS CentroCostoNombre, 
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN ks.ClaveCentroCosto ELSE cc.ClaveCentroCosto END) AS ClaveCentroCosto, 
        cinv.NoSerie AS NoSerie, cinv.NoParteEquipo,
        (CASE WHEN !ISNULL(ks.IdKserviciogimgfa) THEN (SELECT c_cliente.ClaveCliente FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.ClaveCliente END) AS ID, 
        c_equipo.Modelo AS Modelo,
        bi.IdTipoInventario,
        kacc.IdAnexoClienteCC,
        kacc.CveEspClienteCC,
        kacc.ClaveAnexoTecnico,
        cinv.ClaveEspKServicioFAIM,
        cinv.IdKServicio,cinv.Ubicacion,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT c_cliente.EjecutivoCuenta FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto)ELSE c.EjecutivoCuenta END) AS Usuario,
        (CASE WHEN !ISNULL(ks.ClaveCentroCosto) THEN (SELECT CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno) FROM c_centrocosto INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente INNER JOIN c_usuario ON c_usuario.IdUsuario = c_cliente.EjecutivoCuenta WHERE c_centrocosto.ClaveCentroCosto=ks.ClaveCentroCosto) ELSE (SELECT CONCAT(c_usuario.Nombre,' ',c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno) FROM c_usuario WHERE IdUsuario = c.EjecutivoCuenta) END) AS EjecutivoCuenta,
        ks.IdKserviciogimgfa,
        im.IdKServicioIM AS IdKServicioim,
        im.IdServicioIM,
        im.RentaMensual AS imRenta,
        im.PaginasIncluidasBN AS imincluidosBN,
        im.PaginasIncluidasColor AS imincluidosColor,
        im.CostoPaginasExcedentesBN AS imExcedentesBN,
        im.CostoPaginasExcedentesColor AS imExcedentesColor,
        im.CostoPaginaProcesadaBN AS imProcesadasBN,
        im.CostoPaginaProcesadaColor AS imProcesadosColor,
        fa.IdKServicioFA AS IdKServiciofa,
        fa.IdServicioFA,
        fa.RentaMensual AS faRenta,
        fa.MLIncluidosBN AS faincluidosBN,
        fa.MLIncluidosColor AS faincluidosColor,
        fa.CostoMLExcedentesBN AS faExcedentesBN,
        fa.CostoMLExcedentesColor AS faExcedentesColor,
        fa.CostoMLProcesadosBN AS faProcesadasBN,
        fa.CostoMLProcesadosColor AS faProcesadosColor,
        gim.IdKServicioGIM AS IdKServiciogim,
        gim.IdServicioGIM,
        gim.RentaMensual AS gimRenta,
        gim.PaginasIncluidasBN AS gimincluidosBN,
        gim.PaginasIncluidasColor AS gimincluidosColor,
        gim.CostoPaginasExcedentesBN AS gimExcedentesBN,
        gim.CostoPaginasExcedentesColor AS gimExcedentesColor,
        gim.CostoPaginaProcesadaBN AS gimProcesadasBN,
        gim.CostoPaginaProcesadaColor AS gimProcesadosColor,
        gfa.IdKServicioGFA AS IdKServiciogfa,
        gfa.IdServicioGFA,
        gfa.RentaMensual AS gfaRenta,
        gfa.MLIncluidosBN AS gfaincluidosBN,
        gfa.MLIncluidosColor AS gfaincluidosColor,
        gfa.CostoMLExcedentesBN AS gfaExcedentesBN,
        gfa.CostoMLExcedentesColor AS gfaExcedentesColor,
        gfa.CostoMLProcesadosBN AS gfaProcesadasBN,
        gfa.CostoMLProcesadosColor AS gfaProcesadosColor,
        bi.id_bitacora
        FROM `c_inventarioequipo` AS cinv        
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
        RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
        RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
        LEFT JOIN c_equipo ON cinv.NoParteEquipo = c_equipo.NoParte
        LEFT JOIN k_serviciogim AS gim ON gim.IdKServicioGIM = (SELECT MIN(IdKServicioGIM) FROM k_serviciogim WHERE (cinv.IdKServicio = IdKServicioGIM OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGIM = cinv.ClaveEspKServicioFAIM)
        LEFT JOIN k_serviciogfa AS gfa ON gfa.IdKServicioGFA = (SELECT MIN(IdKServicioGFA) FROM k_serviciogfa WHERE (cinv.IdKServicio = IdKServicioGFA OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioGFA = cinv.ClaveEspKServicioFAIM)
        LEFT JOIN k_servicioim AS im ON im.IdKServicioIM = (SELECT MIN(IdKServicioIM) FROM k_servicioim WHERE (cinv.IdKServicio = IdKServicioIM OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioIM = cinv.ClaveEspKServicioFAIM)
        LEFT JOIN k_serviciofa AS fa ON fa.IdKServicioFA = (SELECT MIN(IdKServicioFA) FROM k_serviciofa WHERE (cinv.IdKServicio = IdKServicioFA OR (ISNULL(cinv.IdKServicio) AND cinv.IdAnexoClienteCC = IdAnexoClienteCC)) AND IdServicioFA = cinv.ClaveEspKServicioFAIM)
        LEFT JOIN c_bitacora AS bi ON bi.NoSerie = cinv.NoSerie
        WHERE cinv.NoSerie = '$NoSerie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newRegistro() {
        $consulta = "INSERT INTO c_inventarioequipo(NoSerie, NoParteEquipo,Ubicacion,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('$this->NoSerie','$this->NoParteEquipo','$this->Ubicacion',$this->Activo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_inventarioequipo SET Ubicacion = '$this->Ubicacion', 
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = now(), 
            Pantalla = '$this->Pantalla' WHERE NoSerie = '" . $this->NoSerie . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    /**
     * Inserta el equipo validando si el cliente tiene contrato, anexo o si el equipo ya existe.
     * @param type $NoSerie NoSerie del equipo.
     * @param type $NoParte NoParte del equipo.
     * @param type $Ubicacion Ubicacion del equipo.
     * @param type $ClaveCentroCosto Clave del centro de costo.
     * @param type $ClaveCliente Clave del cliente.
     * @return boolean true en caso de haberlo insertado correctamente, false en caso contrario.
     */
    public function insertarInventarioValidando($NoSerie, $NoParte, $Ubicacion, $ClaveCentroCosto, $ClaveCliente, $Modelo, $MostrarEcho) {
        if (isset($_SESSION['user'])) {
            $usuario = $_SESSION['user'];
        } else {
            $usuario = "kyocera";
        }

        $contrato = new Contrato();
        if(isset($this->empresa)){
            $contrato->setEmpresa($this->empresa);
        }
        $result = $contrato->getRegistroValidacion($ClaveCliente);
        if (mysql_num_rows($result) == 0) {
            $contrato->newRegistroDefault("2014-01-01", "2017-12-31", $ClaveCliente, "PHP Inventario");
            if($MostrarEcho){
                echo "<br/>Se insertó nuevo contrato";
            }
        } else {            
            while ($rs = mysql_fetch_array($result)) {                
                $contrato->setNoContrato($rs['NoContrato']);
            }
        }

        $anexo = new Anexo();
        if(isset($this->empresa)){
            $anexo->setEmpresa($this->empresa);
        }
        $result = $anexo->getAnexosDeLocalidad($ClaveCentroCosto);
        if (mysql_num_rows($result) == 0) {
            $result = $anexo->getAnexosDeContrato($contrato->getNoContrato());
            if (mysql_num_rows($result) == 0) {
                $anexo->newRegistroDefault(date("Y") . "-01-01", $contrato->getNoContrato(), $ClaveCentroCosto, "PHP Inventario");
                if($MostrarEcho){
                    echo "<br/>Se insertó nuevo anexo";
                }
            } else {
                if ($rs = mysql_fetch_array($result)) {
                    $anexo->setClaveAnexoTecnico($rs['ClaveAnexoTecnico']);
                    $anexo->setClaveCC($ClaveCentroCosto);
                    $anexo->setFechaElaboracion(date("Y") . "-01-01");
                    $anexo->setUsuarioCreacion($usuario);
                    $anexo->setUsuarioUltimaModificacion($usuario);
                    $anexo->setPantalla("PHP Inventario");
                    if ($anexo->newK_anexoClienteCC()) {
                        if($MostrarEcho){
                            echo "<br/>Se creó la asociación anexo-cliente correctamente";
                        }
                    } else {
                        if($MostrarEcho){
                            echo "<br/>Error: no se pudo crear la asociación anexo-cliente correctamente";
                        }
                    }
                }
            }
        } else {
            while ($rs = mysql_fetch_array($result)) {
                $anexo->setClaveAnexoTecnico($rs['ClaveAnexoTecnico']);
                $anexo->setIdAnexoClienteCC($rs['IdAnexoClienteCC']);
            }
        }

        $servicio = new ServicioIM();
        if(isset($this->empresa)){
            $servicio->setEmpresa($this->empresa);
        }
        $idKServicio = 0;
        $idServicio = 0;

        /* Verificamos si este anexo ya tiene servicios creados. */
        $result = $servicio->getServiciosAnexoByIdAnexoClienteCC($anexo->getIdAnexoClienteCC(), "gim");
        if ($rs = mysql_fetch_array($result)) {
            $idKServicio = $rs['IdKServicioGIM'];
            $idServicio = $rs['IdServicioGIM'];
        } else {
            $result = $servicio->getServiciosAnexoByIdAnexoClienteCC($anexo->getIdAnexoClienteCC(), "im");
            if ($rs = mysql_fetch_array($result)) {
                $idKServicio = $rs['IdKServicioIM'];
                $idServicio = $rs['IdServicioIM'];
            } else {
                $result = $servicio->getServiciosAnexoByIdAnexoClienteCC($anexo->getIdAnexoClienteCC(), "fa");
                if ($rs = mysql_fetch_array($result)) {
                    $idKServicio = $rs['IdKServicioFA'];
                    $idServicio = $rs['IdServicioFA'];
                } else {
                    $result = $servicio->getServiciosAnexoByIdAnexoClienteCC($anexo->getIdAnexoClienteCC(), "gfa");
                    if ($rs = mysql_fetch_array($result)) {
                        $idKServicio = $rs['IdKServicioGFA'];
                        $idServicio = $rs['IdServicioGFA'];
                    }
                }
            }
        }

        if ($idKServicio == 0 && $idServicio == 0) {
            $idServicio = "100";
            $servicio->setIdServicioIM($idServicio);
            $servicio->setIdAnexoClienteCC($anexo->getIdAnexoClienteCC());
            $servicio->setRentaMensual("0");
            $servicio->setPaginasIncluidasBN("0");
            $servicio->setPaginasIncluidasColor("0");
            $servicio->setCostoPaginasExcedentesBN("0");
            $servicio->setCostoPaginasExcedentesColor("0");
            $servicio->setCostoPaginaProcesadaBN("0");
            $servicio->setCostoPaginaProcesadaColor("0");
            $servicio->setUsuarioCreacion($usuario);
            $servicio->setUsuarioUltimaModificacion($usuario);
            $servicio->setPantalla("PHP Inventario");
            if ($servicio->newRegistro()) {
                $idKServicio = $servicio->getIdKServicioIM();
                if($MostrarEcho){
                    echo "<br/>El servicio se agrego correctamente";
                }
            } else {
                if($MostrarEcho){
                    echo "<br/>Error: no se pudo agregar el servicio";
                }
            }
        }

        $equipo = new Equipo();
        if(isset($this->empresa)){
            $equipo->setEmpresa($this->empresa);
        }
        $hay_equipo = false;
        if (isset($NoParte) && $NoParte != "") {
            if ($equipo->getRegistroById($NoParte)) {
                $hay_equipo = true;
            }
        } else {
            if ($equipo->getRegistroByModelo($Modelo)) {
                $hay_equipo = true;
            }
        }

        if ($hay_equipo) {
            $configuracion = new Configuracion();
            if(isset($this->empresa)){
                $configuracion->setEmpresa($this->empresa);
            }
            $configuracion->setNoSerie($NoSerie);
            $configuracion->setIdKServicio($idKServicio);
            $configuracion->setIdServicio($idServicio);
            $configuracion->setIdAnexoClienteCC($anexo->getIdAnexoClienteCC());
            $configuracion->setNoParte($equipo->getNoParte());
            $configuracion->setUbicacion($Ubicacion);
            $configuracion->setUsuarioCreacion($usuario);
            $configuracion->setUsuarioUltimaModificacion($usuario);
            $configuracion->setPantalla("PHP Inventario");
            /* Inserta bitacora */
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $consulta = "INSERT INTO c_bitacora(id_solicitud,NoParte,NoSerie,NoGenesis,IP,Mac_address,IdTipoInventario,ClaveCentroCosto,IdAnexoClienteCC,IdServicio,IdAlmacen,
                Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                VALUES(null,'" . $equipo->getNoParte() . "','$NoSerie',null,null,null,null,'$ClaveCentroCosto',null,
                    null,null,1,'$usuario',now(),'$usuario',now(),'PHP Inventario')";
            $idBitacora = $catalogo->insertarRegistro($consulta);
            if ($idBitacora != null && $idBitacora != 0) {
                if ($configuracion->registrarInventario()) {
                    /* Inserta movimiento */
                    $movimiento = new Movimiento();
                    if(isset($this->empresa)){
                        $movimiento->setEmpresa($this->empresa);
                    }
                    $movimiento->setNoSerie($NoSerie);
                    $movimiento->setClave_cliente_nuevo($ClaveCliente);
                    $movimiento->setClave_centro_costo_nuevo($ClaveCentroCosto);
                    $movimiento->setPantalla("PHP Inventario");
                    $movimiento->nuevoMovimientoaCliente($NoSerie, $ClaveCliente, $ClaveCentroCosto, "PHP Inventario");
                    if($MostrarEcho){
                        echo "<br/>El equipo $NoSerie de modelo " . $equipo->getModelo() . " se registró correctamente";
                    }
                    return true;
                } else {
                    if($MostrarEcho){
                        echo "<br/>Error al insertar el equipo $NoSerie";
                    }
                    return false;
                }
            } else {
                if($MostrarEcho){
                    echo "<br/>Error: no se pudo crear la bitacora del equipo.";
                }
                return false;
            }
        } else {
            if($MostrarEcho){
                echo "<br/>El NoParte $NoParte o modelo $Modelo no fue encontrado";
            }
            return false;
        }
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getNoParteEquipo() {
        return $this->NoParteEquipo;
    }

    public function setNoParteEquipo($NoParteEquipo) {
        $this->NoParteEquipo = $NoParteEquipo;
    }

    public function getClaveEspKServicioFAIM() {
        return $this->ClaveEspKServicioFAIM;
    }

    public function setClaveEspKServicioFAIM($ClaveEspKServicioFAIM) {
        $this->ClaveEspKServicioFAIM = $ClaveEspKServicioFAIM;
    }

    public function getIdAnexoClienteCC() {
        return $this->IdAnexoClienteCC;
    }

    public function setIdAnexoClienteCC($IdAnexoClienteCC) {
        $this->IdAnexoClienteCC = $IdAnexoClienteCC;
    }

    public function getUbicacion() {
        return $this->Ubicacion;
    }

    public function setUbicacion($Ubicacion) {
        $this->Ubicacion = $Ubicacion;
    }

    public function getContadorInicialBNPaginas() {
        return $this->ContadorInicialBNPaginas;
    }

    public function setContadorInicialBNPaginas($ContadorInicialBNPaginas) {
        $this->ContadorInicialBNPaginas = $ContadorInicialBNPaginas;
    }

    public function getContadorInicialColorPaginas() {
        return $this->ContadorInicialColorPaginas;
    }

    public function setContadorInicialColorPaginas($ContadorInicialColorPaginas) {
        $this->ContadorInicialColorPaginas = $ContadorInicialColorPaginas;
    }

    public function getContadorInicialBNML() {
        return $this->ContadorInicialBNML;
    }

    public function setContadorInicialBNML($ContadorInicialBNML) {
        $this->ContadorInicialBNML = $ContadorInicialBNML;
    }

    public function getContadorInicialColorML() {
        return $this->ContadorInicialColorML;
    }

    public function setContadorInicialColorML($ContadorInicialColorML) {
        $this->ContadorInicialColorML = $ContadorInicialColorML;
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

    public function getContadorActualBNPaginas() {
        return $this->ContadorActualBNPaginas;
    }

    public function setContadorActualBNPaginas($ContadorActualBNPaginas) {
        $this->ContadorActualBNPaginas = $ContadorActualBNPaginas;
    }

    public function getContadorActualColorPaginas() {
        return $this->ContadorActualColorPaginas;
    }

    public function setContadorActualColorPaginas($ContadorActualColorPaginas) {
        $this->ContadorActualColorPaginas = $ContadorActualColorPaginas;
    }

    public function getContadorActualBNML() {
        return $this->ContadorActualBNML;
    }

    public function setContadorActualBNML($ContadorActualBNML) {
        $this->ContadorActualBNML = $ContadorActualBNML;
    }

    public function getContadorActualColorML() {
        return $this->ContadorActualColorML;
    }

    public function setContadorActualColorML($ContadorActualColorML) {
        $this->ContadorActualColorML = $ContadorActualColorML;
    }

    public function getIdKserviciogimgfa() {
        return $this->IdKserviciogimgfa;
    }

    public function setIdKserviciogimgfa($IdKserviciogimgfa) {
        $this->IdKserviciogimgfa = $IdKserviciogimgfa;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
