<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once ("Equipo.class.php");
include_once ("ReporteLectura.class.php");
include_once ("Catalogo.class.php");
include_once ("AlmacenEquipo.class.php");
include_once ("ServicioGimGfa.class.php");
include_once ("ServicioGIM.class.php");

/**
 * Description of Configuracion
 *
 * @author MAGG
 */
class Configuracion {

    private $id_bitacora;
    private $id_solicitud;
    private $NoParte;
    private $NoSerie;
    private $NoSerieOriginal;
    private $IP;
    private $ClaveCentroCosto;
    private $IdAnexoClienteCC;
    private $IdServicio;
    private $NoGenesis;
    private $Mac;
    private $IdTipoInventario;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $idAlmacen;
    private $Ubicacion;
    private $TipoServicio;
    private $ventaDirecta;
    private $IdKServicio;
    private $Demo;
    private $empresa;

    public function validarSerie($NoSerie, $NoParte) {
        $equipo = new Equipo();
        if (isset($this->empresa)) {
            $equipo->setEmpresa($this->empresa);
        }
        if ($equipo->getRegistroById($NoParte)) {
            $reporte = new ReporteLectura();
            if (isset($this->empresa)) {
                $reporte->setEmpresa($this->empresa);
            }

            if ($equipo->getPrefijo() != "") {//Si el modelo tiene un prefijo
                $longitudSerie = 10; //Si no tienen registradas longitud de series, por default el valor es de 10
                if ($equipo->getLongitudSerie() != NULL && $equipo->getLongitudSerie() != "") {
                    $longitudSerie = $equipo->getLongitudSerie();
                }
                if (!$reporte->startsWith($NoSerie, $equipo->getPrefijo())) {//Si la serie no inicia con el prefijo del modelo.
                    echo "<br/>Error: las series del modelo <b>" . $equipo->getModelo() . "</b> deben de tener el prefijo <b>" . $equipo->getPrefijo() . "</b>";
                    return false;
                } else if (strlen($NoSerie) != $longitudSerie) {//Si la longitud del equipo es diferente de 10
                    echo "<br/>Error: las series del modelo <b>" . $equipo->getModelo() . "</b> deben de tener una longitud de $longitudSerie caracteres. "
                    . "Esta serie ($NoSerie) tiene " . strlen($NoSerie) . " caracteres";
                    return false;
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
        return true;
    }

    public function desasociarComponente($idSolicitud, $id_partida) {
        //Actualizamos la cantidad de componente a surtir
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $consulta = "UPDATE k_solicitud SET cantidad_surtida = null WHERE id_solicitud = $idSolicitud AND id_partida = $id_partida";
        $catalogo->obtenerLista($consulta);
        $consulta = "DELETE FROM k_solicitud_asignado WHERE id_solicitud = $idSolicitud AND id_partida = $id_partida;";
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function desasociarBitacora($idBitacora) {
        $consulta = "UPDATE c_bitacora SET id_solicitud = null, ClaveCentroCosto = null,
            UsuarioUltimaModificacion = '" . $_SESSION['user'] . "',
            FechaUltimaModificacion = now(), Pantalla = 'PHP Desasociar serie' WHERE id_bitacora = $idBitacora;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $consulta = "SELECT NoSerie FROM `c_bitacora` WHERE id_bitacora = $idBitacora;";
            $query2 = $catalogo->obtenerLista($consulta);
            while ($rs = mysql_fetch_array($query2)) {
                /* Le quitamos el estatus de apartado al equipo */
                $consulta = "UPDATE `k_almacenequipo` SET Apartado = null, ClaveCentroCosto = null, 
                    UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = NOW() WHERE NoSerie = '" . $rs['NoSerie'] . "';";
                $catalogo->obtenerLista($consulta);
            }
            return true;
        }
        return false;
    }

    public function getRegistroByNoSerie($NoSerie) {
        $consulta = ("SELECT * FROM `c_bitacora` WHERE NoSerie = '" . $NoSerie . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_bitacora = $rs['id_bitacora'];
            $this->id_solicitud = $rs['id_solicitud'];
            $this->NoParte = $rs['NoParte'];
            $this->NoSerie = $rs['NoSerie'];
            $this->NoGenesis = $rs['NoGenesis'];
            $this->IP = $rs['IP'];
            $this->Mac = $rs['Mac_address'];
            $this->IdTipoInventario = $rs['IdTipoInventario'];
            $this->ClaveCentroCosto = $rs['ClaveCentroCosto'];
            $this->IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
            $this->IdServicio = $rs['IdServicio'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->ventaDirecta = $rs['VentaDirecta'];
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

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_bitacora` WHERE id_bitacora = " . $id . ";");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_bitacora = $rs['id_bitacora'];
            $this->id_solicitud = $rs['id_solicitud'];
            $this->NoParte = $rs['NoParte'];
            $this->NoSerie = $rs['NoSerie'];
            $this->NoGenesis = $rs['NoGenesis'];
            $this->IP = $rs['IP'];
            $this->Mac = $rs['Mac_address'];
            $this->IdTipoInventario = $rs['IdTipoInventario'];
            $this->ClaveCentroCosto = $rs['ClaveCentroCosto'];
            $this->IdAnexoClienteCC = $rs['IdAnexoClienteCC'];
            $this->IdServicio = $rs['IdServicio'];
            $this->idAlmacen = $rs['IdAlmacen'];
            $this->ventaDirecta = $rs['VentaDirecta'];
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

    public function getNombreTipoInventarioById($IdTipoInventario) {
        $consulta = "SELECT Nombre FROM `c_tipoinventario` WHERE idTipo = $IdTipoInventario;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        $nombre = "";
        while ($rs = mysql_fetch_array($query)) {
            $nombre = $rs['Nombre'];
        }
        return $nombre;
    }

    public function getComponentesK() {
        $componentes = array();
        $consulta = "SELECT kb.id_kbitacora,kb.id_bitacora,kb.NoParte,kb.fecha,kb.tipo, c.IdTipoComponente
        FROM `k_bitacora` AS kb
        LEFT JOIN c_componente AS c ON kb.NoParte = c.NoParte
        WHERE id_bitacora = $this->id_bitacora ORDER BY tipo ASC;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        $i = 1;
        while ($rs = mysql_fetch_array($query)) {
            $c = array();
            $c['id'] = $rs['id_kbitacora'];
            $c['NoParte'] = $rs['NoParte'];
            $c['fecha'] = $rs['fecha'];
            $c['tipo'] = $rs['tipo'];
            $c['IdTipoComponente'] = $rs['IdTipoComponente'];
            $componentes[$i++] = $c;
        }
        return $componentes;
    }

    public function newRegistro() {
        if ($this->ClaveCentroCosto == "null") {
            $cc = "null";
        } else {
            $cc = "'$this->ClaveCentroCosto'";
        }
        $consulta = "INSERT INTO c_bitacora(id_solicitud,NoParte,NoSerie,NoGenesis,IP,Mac_address,IdTipoInventario,ClaveCentroCosto,IdAnexoClienteCC,IdServicio,IdAlmacen,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES($this->id_solicitud,'$this->NoParte','$this->NoSerie','$this->NoGenesis','$this->IP','$this->Mac',$this->IdTipoInventario,$cc,$this->IdAnexoClienteCC,
                $this->IdServicio,$this->idAlmacen,$this->Activo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->id_bitacora = $catalogo->insertarRegistro($consulta);
        if ($this->id_bitacora != NULL && $this->id_bitacora != 0) {
            /*             * ***** Agregamos el movimiento de equipo * ****** */
            $this->nuevoMovimiento();
            return true;
        }
        return false;
    }

    public function newRegistroAlmacen() {
        $consulta = "INSERT INTO c_bitacora(id_solicitud,NoParte,NoSerie,NoGenesis,IP,Mac_address,IdTipoInventario,ClaveCentroCosto,IdAnexoClienteCC,IdServicio,IdAlmacen,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES($this->id_solicitud,'$this->NoParte','$this->NoSerie','$this->NoGenesis','$this->IP','$this->Mac',$this->IdTipoInventario,null,null,
                null,$this->idAlmacen,$this->Activo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->id_bitacora = $catalogo->insertarRegistro($consulta);
        if ($this->id_bitacora != NULL && $this->id_bitacora != 0) {
            /*             * * ** Agregamos el movimiento de equipo * ****** */
            $this->nuevoMovimiento();
            return true;
        }
        return false;
    }

    public function newRegistroRapido() {
        $consulta = "SELECT * FROM c_bitacora WHERE NoSerie = '$this->NoSerie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if (isset($this->ClaveCentroCosto) && $this->ClaveCentroCosto != "") {
            $cc = "'$this->ClaveCentroCosto'";
        } else {
            $cc = "null";
        }

        if ($rs = mysql_fetch_array($query)) {
            $consulta = "UPDATE c_bitacora SET id_solicitud = $this->id_solicitud, ClaveCentroCosto = $cc, IdAnexoClienteCC=null,
            IdServicio = null, Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', 
            FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla' WHERE NoSerie = '$this->NoSerie';";
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                $this->apartarEquipoEnAlmacen();
                return true;
            }
        } else {
            $consulta = "INSERT INTO c_bitacora(id_solicitud,NoParte,NoSerie,ClaveCentroCosto,IdAnexoClienteCC,IdServicio,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES($this->id_solicitud,'$this->NoParte','$this->NoSerie',$cc,null,
                null,$this->Activo,'$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')";
            $this->id_bitacora = $catalogo->insertarRegistro($consulta);
            if ($this->id_bitacora != NULL && $this->id_bitacora != 0) {
                $this->apartarEquipoEnAlmacen();
                return true;
            }
        }

        return false;
    }

    /**
     * Edita el NoParte de la bitacora
     * @return boolean true en caso de editar correctamente, false en caso contrario
     */
    public function editarNoParte() {
        $consulta = "UPDATE c_bitacora SET NoParte = '$this->NoParte',            
            Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',
            FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE id_bitacora = $this->id_bitacora;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editarRegistroRapido() {
        $consulta = "UPDATE c_bitacora SET id_solicitud = $this->id_solicitud, NoParte = '$this->NoParte',
            NoSerie = '$this->NoSerie', ClaveCentroCosto = '$this->ClaveCentroCosto',
            Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',
            FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE id_bitacora = $this->id_bitacora;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newKRegistro($NoParte, $Fecha, $Tipo) {
        if ($NoParte != "") {
            $consulta = "INSERT INTO k_bitacora(id_bitacora, NoParte,fecha,tipo,
                Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                VALUES($this->id_bitacora,'$NoParte','$Fecha',$Tipo,$this->Activo,
                    '$this->UsuarioCreacion',now(),'$this->UsuarioUltimaModificacion',now(),'$this->Pantalla')";
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            } $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    public function editarRegistro() {
        if ($this->ClaveCentroCosto != "null") {
            $cc = "'$this->ClaveCentroCosto'";
        } else {
            $cc = "null";
        }

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }

        $consulta = "SET foreign_key_checks = 0; UPDATE c_bitacora SET id_solicitud = $this->id_solicitud, NoParte = '$this->NoParte',
            NoSerie = '$this->NoSerie', NoGenesis = '$this->NoGenesis', IP = '$this->IP', Mac_address = '$this->Mac', IdTipoInventario = $this->IdTipoInventario,
            Activo = $this->Activo, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',
            FechaUltimaModificacion = now(), Pantalla = '$this->Pantalla' WHERE id_bitacora = $this->id_bitacora; SET foreign_key_checks = 1;";

        $query = $catalogo->multiQuery($consulta);
        if ($query != "1") {
            return false;
        }

        if (!isset($this->Demo) || $this->Demo == "") {
            $this->Demo = 0;
        }

        /* Actualizar almacen e inventario */
        $consulta = "UPDATE `k_almacenequipo` SET NoParte = '$this->NoParte' WHERE NoSerie = '$this->NoSerieOriginal';";
        $query3 = $catalogo->obtenerLista($consulta);

        $consulta = "UPDATE `c_inventarioequipo` SET NoParteEquipo = '$this->NoParte', Demo = $this->Demo WHERE NoSerie = '$this->NoSerieOriginal';";
        $query2 = $catalogo->obtenerLista($consulta);

        if ($query3 != "1" && $query2 != "1") {
            //echo "Error: No se pudo actualizar el equipo en su ubicación";
            return false;
        }

        if ($this->NoSerieOriginal != null && $this->NoSerieOriginal != "" && $this->NoSerieOriginal != $this->NoSerie) {//Si la serie original es diferente a la anterior
            $configuracion = new Configuracion();
            $configuracion->getRegistroByNoSerie($this->NoSerie);
            $this->cambiarNoSerie($this->NoSerie, $this->NoSerieOriginal);
        }

        if(!$this->nuevoMovimientoEditado()){
            return false;
        }
        return true;
        /* if ($query == 1) {
          return true;
          }
          return false; */
    }

    public function cambiarNoSerie($nuevo, $anterior) {
        $consulta = "UPDATE c_ticket SET NoSerieEquipo = '$nuevo' WHERE NoSerieEquipo = '$anterior'; 
            UPDATE c_pedido SET ClaveEspEquipo = '$nuevo' WHERE ClaveEspEquipo = '$anterior'; 
            UPDATE movimientos_equipo SET NoSerie = '$nuevo' WHERE NoSerie = '$anterior'; 
            UPDATE c_incidencias SET NoSerie = '$nuevo' WHERE NoSerie = '$anterior'; 
            UPDATE k_almacenequipo SET NoSerie = '$nuevo' WHERE NoSerie = '$anterior'; 
            UPDATE c_lectura SET NoSerie = '$nuevo' WHERE NoSerie = '$anterior';
            UPDATE c_lecturasticket SET ClvEsp_Equipo = '$nuevo' WHERE ClvEsp_Equipo = '$anterior'; 
            UPDATE c_bitacora SET NoSerie = '$nuevo' WHERE NoSerie = '$anterior';
            UPDATE k_enviosmensajeria SET NoSerie = '$nuevo' WHERE NoSerie = '$anterior'; 
            UPDATE c_inventarioequipo SET NoSerie = '$nuevo' WHERE NoSerie = '$anterior';";
        $consultas = explode(";", $consulta);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        //echo $consulta;
        foreach ($consultas as $value) {
            //echo "<br/>".$value;
            if ($value != "") {
                $query = $catalogo->obtenerLista($value);
                if ($query == "0") {
                    //echo "... fallo";
                    //return false;
                }
            }
        }
        return true;
    }

    public function eliminarRegistro() {
        if ($this->eliminarDetalles()) {
            $consulta = ("DELETE FROM c_bitacora WHERE id_bitacora = $this->id_bitacora;");
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            } $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    public function apartarEquipoEnAlmacen() {
        if (isset($this->ClaveCentroCosto) && $this->ClaveCentroCosto != "") {
            $cc = "'$this->ClaveCentroCosto'";
        } else {
            $cc = "null";
        }
        $consulta = "UPDATE k_almacenequipo SET Apartado = 1, ClaveCentroCosto = $cc WHERE NoSerie = '$this->NoSerie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function nuevosDetalles($parametros) {
        $i = 1;
        while (isset($parametros['c_no_parte_' . $i])) {/* Registramos los componentes */
            if (!$this->newKRegistro($parametros['c_no_parte_' . $i], $parametros['c_fecha_' . $i], 0)) {
                echo "Error: El componente " . $parametros['c_no_parte_' . $i] . " no se pudo registrar";
            }
            $i++;
        }
        $i = 1;

        while (isset($parametros['s_no_parte_' . $i])) {/* Registramos los componentes */
            if (!$this->newKRegistro($parametros['s_no_parte_' . $i], $parametros['s_fecha_' . $i], 1)) {
                echo "Error: El componente " . $parametros['s_no_parte_' . $i] . " no se pudo registrar";
            }
            $i++;
        }
    }

    public function eliminarDetalles() {
        $consulta = ("DELETE FROM k_bitacora WHERE id_bitacora = $this->id_bitacora;");
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
     * Marca el equipo como venta directa
     * @param type $marcar true para marcar como venta directa, false para desmarcar como venta directa.
     * @return boolean true en caso de ejecutar la operacion correctamente, false en caso contrario.
     */
    public function marcarComoVentaDirecta($marcar) {
        $venta = "0";
        if ($marcar) {
            $venta = "1";
        }
        $consulta = "UPDATE c_bitacora SET VentaDirecta = $venta WHERE NoSerie = '$this->NoSerie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function marcarComoBackUp() {
        $consulta = "UPDATE c_bitacora SET IdTipoInventario = 8 WHERE NoSerie = '$this->NoSerie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function marcarComoDemo() {
        $consulta = "UPDATE `c_inventarioequipo` SET Demo = 1 WHERE NoSerie = '$this->NoSerie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function nuevoMovimientoEditado() {
        $consulta = "SELECT cc.ClaveCliente, ka.CveEspClienteCC,
            (CASE WHEN !ISNULL(i.ClaveEspKServicioFAIM) THEN i.ClaveEspKServicioFAIM ELSE 'NULL' END) AS IdServicio,
            (CASE WHEN !ISNULL(i.IdKServicio) THEN i.IdKServicio ELSE 'NULL' END) AS IdKServicio,
            (CASE WHEN !ISNULL(i.IdKserviciogimgfa) THEN i.IdKserviciogimgfa ELSE 'NULL' END) AS IdKserviciogimgfa,
            (CASE WHEN !ISNULL(i.IdAnexoClienteCC) THEN i.IdAnexoClienteCC ELSE 'NULL' END) AS IdAnexoClienteCC
            FROM `c_inventarioequipo` AS i 
            INNER JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = i.IdAnexoClienteCC
            INNER JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
            WHERE i.NoSerie = '$this->NoSerie' AND !ISNULL(i.IdAnexoClienteCC);";        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $result = $catalogo->obtenerLista($consulta);
        
        if ($rs = mysql_fetch_array($result)) {/* Se encuentra en inventario */
            /* Agregamos el movimiento de equipo */
            if ($this->ClaveCentroCosto != "null") {/* Si hay CC, entonces se va a cliente */
                if ($this->ClaveCentroCosto != $rs['CveEspClienteCC']) {/* Sino viene del mismo cliente */
                    //Verificamos que el equipo no este en una solicitud de equipo, retiro o ticket
                    $result3 = $catalogo->obtenerLista("SELECT id_bitacora, b.id_solicitud, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParte, s.estatus AS estatusSolicitud,t.IdTicket, IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'1',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9,'1','0')))) AS MoverRojo FROM `c_bitacora` AS b LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora) LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen LEFT JOIN c_solicitud AS s ON s.id_solicitud = b.id_solicitud LEFT JOIN c_ticket AS t ON t.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket) LEFT JOIN c_pedido AS p ON p.IdTicket = t2.IdTicket WHERE (t2.NoSerieEquipo = b.NoSerie OR p.ClaveEspEquipo = b.NoSerie) AND t2.EstadoDeTicket NOT IN(2,4) AND (ISNULL(nt.IdEstatusAtencion) OR nt.IdEstatusAtencion NOT IN(16,59))) WHERE b.NoSerie = '$this->NoSerie' ORDER BY NoSerie DESC;");
                    
                    while ($rs3 = mysql_fetch_array($result3)) {
                        if ($rs3['MoverRojo'] == "1") {
                            echo "Error: El equipo se encuentra en retiro, no se puede mover";
                            return false;
                        } else if ($rs3['estatusSolicitud'] == "1" || $rs3['estatusSolicitud'] == "0" || $rs3['estatusSolicitud'] == "2") {
                            echo "Error: Equipo en solicitud: " . $rs3['id_solicitud'] . ", no se puede mover";
                            return false;
                        } else if ($rs3['IdTicket']) {
                            echo "Error: Equipo en ticket abierto: " . $rs3['IdTicket'] . ", no se puede mover";
                            return false;
                        }
                    }

                    /* Ponemos en nulo los campos de anexo y servicio */
                    $consulta = "DELETE FROM `c_inventarioequipo` WHERE NoSerie = '$this->NoSerie';";
                    $result2 = $catalogo->obtenerLista($consulta);
                    if ($result2 != 1) {
                        echo "Error: No se pudo actualizar el inventario.";
                    }

                    $result2 = $catalogo->obtenerLista("SELECT ClaveCliente FROM `c_centrocosto` WHERE ClaveCentroCosto = '$this->ClaveCentroCosto';");
                    $claveCliente = "";
                    if ($rs2 = mysql_fetch_array($result2)) {
                        $claveCliente = $rs2['ClaveCliente'];
                    }
                    $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,clave_cliente_nuevo,
                        clave_centro_costo_nuevo,tipo_movimiento,Fecha,
                        IdAnexoClienteCCAnterior,IdKserviciogimgfaAnterior,IdKServicioAnterior,IdServicioAnterior,
                        UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                        VALUES('$this->NoSerie','" . $rs['ClaveCliente'] . "','" . $rs['CveEspClienteCC'] . "','$claveCliente',"
                            . "'$this->ClaveCentroCosto',1,NOW(),"
                            . $rs['IdAnexoClienteCC'].",".$rs['IdKserviciogimgfa'].",".$rs['IdKServicio'].",".$rs['IdServicio'].","
                            . "'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',0);";
                    $query = $catalogo->obtenerLista($consulta);
                    if ($query != 1) {
                        echo "Error: No se pudo registrar el movimiento correctamente";
                    }
                }
                $this->registrarInventario(); //Registramos en inventario
                return true;
            } else {/* Entonces va a almacen */

                //Verificamos que el equipo no este en una solicitud de equipo, retiro o ticket
                $result3 = $catalogo->obtenerLista("SELECT id_bitacora, b.id_solicitud, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParte, s.estatus AS estatusSolicitud,t.IdTicket, IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'1',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9,'1','0')))) AS MoverRojo FROM `c_bitacora` AS b LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora) LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen LEFT JOIN c_solicitud AS s ON s.id_solicitud = b.id_solicitud LEFT JOIN c_ticket AS t ON t.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket) LEFT JOIN c_pedido AS p ON p.IdTicket = t2.IdTicket WHERE (t2.NoSerieEquipo = b.NoSerie OR p.ClaveEspEquipo = b.NoSerie) AND t2.EstadoDeTicket NOT IN(2,4) AND (ISNULL(nt.IdEstatusAtencion) OR nt.IdEstatusAtencion NOT IN(16,59))) WHERE b.NoSerie = '$this->NoSerie' ORDER BY NoSerie DESC;");
                
                while ($rs3 = mysql_fetch_array($result3)) {
                    if ($rs3['MoverRojo'] == "1") {
                        echo "Error: El equipo se encuentra en retiro, no se puede mover";
                        return false;
                    } else if ($rs3['estatusSolicitud'] == "1" || $rs3['estatusSolicitud'] == "0" || $rs3['estatusSolicitud'] == "2") {
                        echo "Error: Equipo en solicitud: " . $rs3['id_solicitud'] . ", no se puede mover";
                        return false;
                    } else if ($rs3['IdTicket']) {
                        echo "Error: Equipo en ticket abierto: " . $rs3['IdTicket'] . ", no se puede mover";
                        return false;
                    }
                }

                $consulta = "DELETE FROM `c_inventarioequipo` WHERE NoSerie = '$this->NoSerie';";
                $result2 = $catalogo->obtenerLista($consulta);
                if ($result2 != 1) {
                    echo "Error: No se pudo actualizar el inventario.";
                }

                $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,almacen_nuevo,tipo_movimiento,Fecha,
                    IdAnexoClienteCCAnterior,IdKserviciogimgfaAnterior,IdKServicioAnterior,IdServicioAnterior,
                    UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                    VALUES('$this->NoSerie','" . $rs['ClaveCliente'] . "','" . $rs['CveEspClienteCC'] . "',$this->idAlmacen,3,NOW(),"
                        . $rs['IdAnexoClienteCC'].",".$rs['IdKserviciogimgfa'].",".$rs['IdKServicio'].",".$rs['IdServicio'].","
                        . "'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',1);";
                $query = $catalogo->obtenerLista($consulta);
                if ($query != 1) {
                    echo "Error: No se pudo registrar el movimiento correctamente";
                }
                $this->insertarEnAlmacen(); //Registramos en almacen
                return true;
            }
        } else { /* Esta en el almacen */
            /* Verificamos si el equipo esta en solicitud */
            $consulta = "DELETE FROM `c_inventarioequipo` WHERE NoSerie = '$this->NoSerie';";
            $catalogo->obtenerLista($consulta);
            $consulta = "SELECT id_almacen FROM `k_almacenequipo` WHERE NoSerie = '$this->NoSerie';";
            $result = $catalogo->obtenerLista($consulta);
            if ($rs = mysql_fetch_array($result)) {/* Se encuentra en almacen */
                /* Agregamos el movimiento de equipo */
                if ($this->ClaveCentroCosto != "null") {/* Si hay CC, entonces se va a cliente */
                    //Verificamos que el equipo no este en una solicitud de equipo, retiro o ticket
                    $result3 = $catalogo->obtenerLista("SELECT id_bitacora, b.id_solicitud, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParte, s.estatus AS estatusSolicitud,t.IdTicket, IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'1',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9,'1','0')))) AS MoverRojo FROM `c_bitacora` AS b LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora) LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen LEFT JOIN c_solicitud AS s ON s.id_solicitud = b.id_solicitud LEFT JOIN c_ticket AS t ON t.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket) LEFT JOIN c_pedido AS p ON p.IdTicket = t2.IdTicket WHERE (t2.NoSerieEquipo = b.NoSerie OR p.ClaveEspEquipo = b.NoSerie) AND t2.EstadoDeTicket NOT IN(2,4) AND (ISNULL(nt.IdEstatusAtencion) OR nt.IdEstatusAtencion NOT IN(16,59))) WHERE b.NoSerie = '$this->NoSerie' ORDER BY NoSerie DESC;");
                    
                    while ($rs3 = mysql_fetch_array($result3)) {
                        if ($rs3['MoverRojo'] == "1") {
                            echo "Error: El equipo se encuentra en retiro, no se puede mover";
                            return false;
                        } else if ($rs3['estatusSolicitud'] == "1" || $rs3['estatusSolicitud'] == "0" || $rs3['estatusSolicitud'] == "2") {
                            echo "Error: Equipo en solicitud: " . $rs3['id_solicitud'] . ", no se puede mover";
                            return false;
                        } else if ($rs3['IdTicket']) {
                            echo "Error: Equipo en ticket abierto: " . $rs3['IdTicket'] . ", no se puede mover";
                            return false;
                        }
                    }

                    /* Descontamos las existencias del almacen */
                    $query = $catalogo->obtenerLista("DELETE FROM `k_almacenequipo` WHERE NoSerie = '$this->NoSerie';");
                    if ($query != 1) {
                        echo "No se pudo eliminar la existencia del almacén";
                    }
                    $result2 = $catalogo->obtenerLista("SELECT ClaveCliente FROM `c_centrocosto` WHERE ClaveCentroCosto = '$this->ClaveCentroCosto';");
                    $claveCliente = "";
                    if ($rs2 = mysql_fetch_array($result2)) {
                        $claveCliente = $rs2['ClaveCliente'];
                    }
                    $consulta = "INSERT INTO movimientos_equipo(NoSerie,almacen_anterior,clave_cliente_nuevo,
                        clave_centro_costo_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                        VALUES('$this->NoSerie','" . $rs['id_almacen'] . "','$claveCliente','$this->ClaveCentroCosto',2,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',0);";
                    $query = $catalogo->obtenerLista($consulta);
                    if ($query != 1) {
                        echo "Error: No se pudo registrar el movimiento correctamente";
                    }
                    $this->registrarInventario(); //Registramos en inventario
                    return true;
                } else {/* Entonces va a almacen */
                    if ($this->idAlmacen != $rs['id_almacen']) {//Cuando es un almacen diferente al que se encuentra actualmente
                        //Verificamos que el equipo no este en una solicitud de equipo, retiro o ticket
                        $result3 = $catalogo->obtenerLista("SELECT id_bitacora, b.id_solicitud, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParte, s.estatus AS estatusSolicitud,t.IdTicket, IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'1',IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9,'1','0')))) AS MoverRojo FROM `c_bitacora` AS b LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora) LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen LEFT JOIN c_solicitud AS s ON s.id_solicitud = b.id_solicitud LEFT JOIN c_ticket AS t ON t.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket) LEFT JOIN c_pedido AS p ON p.IdTicket = t2.IdTicket WHERE (t2.NoSerieEquipo = b.NoSerie OR p.ClaveEspEquipo = b.NoSerie) AND t2.EstadoDeTicket NOT IN(2,4) AND (ISNULL(nt.IdEstatusAtencion) OR nt.IdEstatusAtencion NOT IN(16,59))) WHERE b.NoSerie = '$this->NoSerie' ORDER BY NoSerie DESC;");
                        
                        while ($rs3 = mysql_fetch_array($result3)) {
                            if ($rs3['MoverRojo'] == "1") {
                                echo "Error: El equipo se encuentra en retiro, no se puede mover";
                                return false;
                            } else if ($rs3['estatusSolicitud'] == "1" || $rs3['estatusSolicitud'] == "0" || $rs3['estatusSolicitud'] == "2") {
                                echo "Error: Equipo en solicitud: " . $rs3['id_solicitud'] . ", no se puede mover";
                                return false;
                            } else if ($rs3['IdTicket']) {
                                echo "Error: Equipo en ticket abierto: " . $rs3['IdTicket'] . ", no se puede mover";
                                return false;
                            }
                        }


                        /* Descontamos las existencias del almacen */
                        $query = $catalogo->obtenerLista("DELETE FROM `k_almacenequipo` WHERE NoSerie = '$this->NoSerie';");
                        if ($query != 1) {
                            echo "No se pudo eliminar la existencia del almacén";
                        }
                        $consulta = "INSERT INTO movimientos_equipo(NoSerie,almacen_anterior,almacen_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                            VALUES('$this->NoSerie','" . $rs['id_almacen'] . "','$this->idAlmacen',4,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',1);";
                        //echo $consulta;
                        $query = $catalogo->obtenerLista($consulta);
                        if ($query != 1) {
                            echo "Error: No se pudo registrar el movimiento correctamente";
                        }
                        $this->insertarEnAlmacen(); //Registramos en almacen
                    } else {
                        $this->editarEnAlmacenSinMover();
                    }
                    return true;
                }
            } else {/* Esta en el limbo */
                /* Agregamos el movimiento de equipo */
                if ($this->ClaveCentroCosto != "null") {/* Si hay CC, entonces se va a cliente */
                    $result2 = $catalogo->obtenerLista("SELECT ClaveCliente FROM `c_centrocosto` WHERE ClaveCentroCosto = '$this->ClaveCentroCosto';");
                    $claveCliente = "";
                    if ($rs2 = mysql_fetch_array($result2)) {
                        $claveCliente = $rs2['ClaveCliente'];
                    }
                    $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,clave_cliente_nuevo,
                        clave_centro_costo_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                        VALUES('$this->NoSerie','" . $rs['ClaveCliente'] . "','" . $rs['CveEspClienteCC'] . "','$claveCliente','$this->ClaveCentroCosto',1,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',0);";
                    //echo $consulta;
                    $query = $catalogo->obtenerLista($consulta);
                    if ($query != 1) {
                        //echo "Error: No se pudo registrar el movimiento correctamente";
                    }
                    $this->registrarInventario(); //Registramos en inventario
                } else {/* Entonces va a almacen */
                    //echo "A almacen";
                    $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,almacen_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                        VALUES('$this->NoSerie','" . $rs['ClaveCliente'] . "','" . $rs['CveEspClienteCC'] . "',$this->idAlmacen,3,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',1);";
                    $query = $catalogo->obtenerLista($consulta);
                    //echo $consulta;
                    if ($query != 1) {
                        //echo "Error: No se pudo registrar el movimiento correctamente";
                    }
                    $this->insertarEnAlmacen(); //Registramos en almacen
                }
                return true;
            }
        }
    }

    /*
     * Registra el nuevo movimiento segun los parametros de la clase
     */

    public function nuevoMovimiento() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if ($this->ClaveCentroCosto != "null") {/* Si hay CC, entonces se va a cliente */
            $result = $catalogo->obtenerLista("SELECT ClaveCliente FROM `c_centrocosto` WHERE ClaveCentroCosto = '$this->ClaveCentroCosto';");
            $claveCliente = "";
            if ($rs = mysql_fetch_array($result)) {
                $claveCliente = $rs['ClaveCliente'];
            }
            $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_nuevo,clave_centro_costo_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                VALUES('$this->NoSerie','$claveCliente','$this->ClaveCentroCosto',5,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',0);";
            $query = $catalogo->obtenerLista($consulta);
            if ($query != 1) {
                echo "Error: No se pudo registrar el movimiento correctamente";
            }
            $this->registrarInventario();
        } else {/* Entonces va a almacen */
            $consulta = "INSERT INTO movimientos_equipo(NoSerie,almacen_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                VALUES('$this->NoSerie',$this->idAlmacen,5,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$this->Pantalla',1);";
            $query = $catalogo->obtenerLista($consulta);
            if ($query != 1) {
                echo "Error: No se pudo registrar el movimiento correctamente";
            }
            $this->insertarEnAlmacen();
        }
    }

    /**
     * Inserta el equipo en configuracion al almacen
     */
    public function insertarEnAlmacen() {
        $obj = new AlmacenEquipo();
        $obj->setNoSerie($this->NoSerie);
        $obj->setIdAlmacen(9);
        $obj->setNoParteEquipo($this->NoParte);
        $obj->setUbicacion($this->Ubicacion);
        $hoy = getdate();
        $obj->setFechaIngreso($hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday']);
        $obj->setUsuarioCreacion($this->UsuarioCreacion);
        $obj->setUsuarioModificacion($this->UsuarioUltimaModificacion);
        $obj->setPantalla($this->Pantalla);
        if (!$obj->newRegistro()) {
            echo "Error: No se pudo registrar el equipo en el almacen";
        }
    }

    /**
     * Edita el equipo en almacen y lo manda a equipos sin identificar
     */
    public function editarEnAlmacen() {
        $obj = new AlmacenEquipo();
        $obj->setNoSerie($this->NoSerie);
        $obj->setId($this->NoSerie);
        $obj->setIdAlmacen(9);
        $obj->setNoParteEquipo($this->NoParte);
        $obj->setUbicacion($this->Ubicacion);
        $hoy = getdate();
        $obj->setFechaIngreso($hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday']);
        $obj->setUsuarioCreacion($this->UsuarioCreacion);
        $obj->setUsuarioModificacion($this->UsuarioUltimaModificacion);
        $obj->setPantalla($this->Pantalla);
        if ($obj->editRegistro() != "1") {
            echo "Error: No se pudo editar el equipo en el almacen";
        }
    }

    /**
     * Edita el equipo sin moverlo al almacen de equipos sin identificar
     */
    public function editarEnAlmacenSinMover() {
        $obj = new AlmacenEquipo();
        $obj->setNoSerie($this->NoSerie);
        $obj->setId($this->NoSerie);
        $obj->setIdAlmacen($this->idAlmacen);
        $obj->setNoParteEquipo($this->NoParte);
        $obj->setUbicacion($this->Ubicacion);
        $hoy = getdate();
        $obj->setFechaIngreso($hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday']);
        $obj->setUsuarioCreacion($this->UsuarioCreacion);
        $obj->setUsuarioModificacion($this->UsuarioUltimaModificacion);
        $obj->setPantalla($this->Pantalla);
        if ($obj->editRegistro() != "1") {
            echo "Error: No se pudo editar el equipo en el almacen";
        }
    }

    public function registrarInventario() {
        if ($this->IdKServicio == null || $this->IdKServicio == 0 || $this->IdKServicio == "") {
            $idKServicio = "null";
        } else {
            $idKServicio = $this->IdKServicio;
        }

        if ($this->IdServicio == null) {
            $servicio = "null";
        } else {
            $servicio = "'" . $this->IdServicio . "'";
        }

        if ($this->IdAnexoClienteCC == null) {
            $cc = "null";
        } else {
            $cc = "'" . $this->IdAnexoClienteCC . "'";
        }

        if (!isset($this->Demo) || $this->Demo == "") {
            $this->Demo = 0;
        }

        if (!$this->existeInventario($this->NoSerie)) {/* Si el equipo no esta registrado en c_inventario */
            $this->marcarComoVentaDirecta(false); //El cambio del equipo no es por venta directa.                                           
            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $idKservicioGIMGFA = "null";

            if (isset($this->TipoServicio) && $this->TipoServicio == "0") {/* Si se maneja un servicio global */
                $servicioGimGfa = new ServicioGim();
                $query = $servicioGimGfa->getRegistrosByIdAnexoAndCC($this->IdAnexoClienteCC, $this->ClaveCentroCosto);
                if (mysql_num_rows($query) > 0) {/* ya tiene un registro en k_serviciogimgfa */
                    while ($rs = mysql_fetch_array($query)) {
                        $idKservicioGIMGFA = $rs['IdKserviciogimgfa'];
                    }
                } else {/* Hay que insetrar un registro en k_serviciogimgfa */
                    $servicioGimGfa->setClaveCentroCosto($this->ClaveCentroCosto);
                    $servicioGimGfa->setClaveEsp("1050");
                    $servicioGimGfa->setIdAnexoCliente($cc);
                    $servicioGimGfa->setUsuarioCreacion($this->UsuarioCreacion);
                    $servicioGimGfa->setUsuarioModificacion($this->UsuarioUltimaModificacion);
                    $servicioGimGfa->setPantalla($this->Pantalla);
                    /* $consulta = "INSERT INTO k_serviciogimgfa(IdKserviciogimgfa, CveEspKservicioimfa,ClaveCentroCosto,IdAnexoClienteCC,
                      UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                      VALUES(0,1050,'$this->ClaveCentroCosto',$cc,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');"; */
                    if ($servicioGimGfa->newRegistro()) {
                        $idKservicioGIMGFA = $servicioGimGfa->getIdServicio();
                    }
                    //$idKservicioGIMGFA = $catalogo->insertarRegistro($consulta);
                }

                if ($cc != "null") {/* Verificamos que ya exista un servicio global registrado en k_serviciogim */
                    $servicioGIM = new KServicioGIM();
                    $query = $servicioGIM->getRegistrosByIdAnexo($cc);
                    if (mysql_num_rows($query) == 0) {/* Si no existe un registro en k_serviciogim, lo insertamos */
                        /* $servicioGIM->setIdAnexoClienteCC($cc);
                          $servicioGIM->setIdServicioGIM("1050");
                          $servicioGIM->setRentaMensual("0");
                          $servicioGIM->setPaginasIncluidasBN("0");$servicioGIM->setPaginasIncluidasColor("0");
                          $servicioGIM->setCostoPaginaProcesadaBN("0"); $servicioGIM->setCostoPaginaProcesadaColor("0");
                          $servicioGIM->setCostoPaginasExcedentesBN("0"); $servicioGIM->setCostoPaginasExcedentesColor("0");
                          $servicioGIM->setUsuarioCreacion($this->UsuarioCreacion); $servicioGIM->setUsuarioUltimaModificacion($this->UsuarioUltimaModificacion);
                          $servicioGIM->setPantalla($this->Pantalla);
                          $servicioGIM->newRegistro(); */
                    }
                }
                echo $idKservicioGIMGFA;
            }

            $consulta = "INSERT INTO c_inventarioequipo(NoParteEquipo,NoSerie,Ubicacion,ClaveEspKServicioFAIM,IdAnexoClienteCC,IdKserviciogimgfa,
                Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla, IdKServicio,Demo) 
            VALUES('$this->NoParte','$this->NoSerie','$this->Ubicacion',$servicio,$cc,$idKservicioGIMGFA,1,"
                    . "'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla',$idKServicio,$this->Demo);";
            //echo "Error: ".$consulta;
            $query = $catalogo->obtenerLista($consulta);
            if ($query == "1") {
                /* Actualizamos la información de la bitacora, en caso de que el equipo ya tenga */
                $NoParte = $this->NoParte;
                if ($this->getRegistroByNoSerie($this->NoSerie)) {
                    $this->NoParte = $NoParte;
                    $this->editarNoParte();
                }
                return true;
            } else {
                echo "Error: No se pudo registrar inventario del equipo $this->NoSerie";
                return false;
            }
        } else {/* Si ya tiene registro en c_inventario */

            $catalogo = new Catalogo();
            if (isset($this->empresa)) {
                $catalogo->setEmpresa($this->empresa);
            }
            $idKservicioGIMGFA = "null";
            if (isset($this->TipoServicio) && $this->TipoServicio == "0") {/* Si se maneja un servicio global */
                $consulta = "SELECT IdKserviciogimgfa FROM `k_serviciogimgfa` WHERE IdAnexoClienteCC = $this->IdAnexoClienteCC AND ClaveCentroCosto = '$this->ClaveCentroCosto';";
                $query = $catalogo->obtenerLista($consulta);

                if (mysql_num_rows($query) > 0) {/* ya tiene un registro en k_serviciogimgfa */
                    while ($rs = mysql_fetch_array($query)) {
                        $idKservicioGIMGFA = $rs['IdKserviciogimgfa'];
                    }
                } else {/* Hay que insetrar un registro en k_serviciogimgfa */
                    $consulta = "INSERT INTO k_serviciogimgfa(IdKserviciogimgfa, CveEspKservicioimfa,ClaveCentroCosto,IdAnexoClienteCC,
                        UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
                        VALUES(0,1050,'$this->ClaveCentroCosto',$cc,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
                    $idKservicioGIMGFA = $catalogo->insertarRegistro($consulta);
                }

                if ($cc != "null") {/* Verificamos que ya exista un servicio global registrado en k_serviciogim */
                    $servicioGIM = new KServicioGIM();
                    $query = $servicioGIM->getRegistrosByIdAnexo($cc);
                    if (mysql_num_rows($query) == 0) {/* Si no existe un registro en k_serviciogim, lo insertamos */
                        /* $servicioGIM->setIdAnexoClienteCC($cc);
                          $servicioGIM->setIdServicioGIM("1050");
                          $servicioGIM->setRentaMensual("0");
                          $servicioGIM->setPaginasIncluidasBN("0");$servicioGIM->setPaginasIncluidasColor("0");
                          $servicioGIM->setCostoPaginaProcesadaBN("0"); $servicioGIM->setCostoPaginaProcesadaColor("0");
                          $servicioGIM->setCostoPaginasExcedentesBN("0"); $servicioGIM->setCostoPaginasExcedentesColor("0");
                          $servicioGIM->setUsuarioCreacion($this->UsuarioCreacion); $servicioGIM->setUsuarioUltimaModificacion($this->UsuarioUltimaModificacion);
                          $servicioGIM->setPantalla($this->Pantalla);
                          $servicioGIM->newRegistro(); */
                    }
                }
            }

            $consulta = "UPDATE c_inventarioequipo SET ClaveEspKServicioFAIM=$servicio,
            IdAnexoClienteCC=$cc,Activo=1,Ubicacion='$this->Ubicacion',IdKserviciogimgfa=$idKservicioGIMGFA, IdKServicio = $idKServicio, Demo = $this->Demo,
            UsuarioUltimaModificacion='$this->UsuarioUltimaModificacion',FechaUltimaModificacion=NOW(),Pantalla='$this->Pantalla' WHERE NoSerie = '$this->NoSerie';";
            //echo $consulta;

            $query = $catalogo->obtenerLista($consulta);
            if ($query == "1") {
                /* Actualizamos la información de la bitacora, en caso de que el equipo ya tenga */
                $NoParte = $this->NoParte;
                if ($this->getRegistroByNoSerie($this->NoSerie)) {
                    $this->NoParte = $NoParte;
                    $this->editarNoParte();
                }
                return true;
            } else {
                return false;
                echo "Error: No se pudo registrar inventario del equipo $this->NoSerie";
            }
        }
    }

    public function existeInventario($serie) {
        $consulta = "SELECT NoParteEquipo FROM `c_inventarioequipo` WHERE NoSerie = '$serie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        $numero_filas = mysql_num_rows($query);
        if ($numero_filas > 0) {
            $existe = true;
        } else {
            $existe = false;
        }
        return $existe;
    }

    public function getId_bitacora() {
        return $this->id_bitacora;
    }

    public function setId_bitacora($id_bitacora) {
        $this->id_bitacora = $id_bitacora;
    }

    public function getId_solicitud() {
        return $this->id_solicitud;
    }

    public function setId_solicitud($id_solicitud) {
        $this->id_solicitud = $id_solicitud;
    }

    public function getNoParte() {
        return $this->NoParte;
    }

    public function setNoParte($NoParte) {
        $this->NoParte = $NoParte;
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
    }

    public function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
    }

    public function getIdAnexoClienteCC() {
        return $this->IdAnexoClienteCC;
    }

    public function setIdAnexoClienteCC($IdAnexoClienteCC) {
        $this->IdAnexoClienteCC = $IdAnexoClienteCC;
    }

    public function getIdServicio() {
        return $this->IdServicio;
    }

    public function setIdServicio($IdServicio) {
        $this->IdServicio = $IdServicio;
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

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getNoGenesis() {
        return $this->NoGenesis;
    }

    public function setNoGenesis($NoGenesis) {
        $this->NoGenesis = $NoGenesis;
    }

    public function getIdTipoInventario() {
        return $this->IdTipoInventario;
    }

    public function setIdTipoInventario($IdTipoInventario) {
        $this->IdTipoInventario = $IdTipoInventario;
    }

    public function getIP() {
        return $this->IP;
    }

    public function setIP($IP) {
        $this->IP = $IP;
    }

    public function getMac() {
        return $this->Mac;
    }

    public function setMac($Mac) {
        $this->Mac = $Mac;
    }

    public function getUbicacion() {
        return $this->Ubicacion;
    }

    public function setUbicacion($Ubicacion) {
        $this->Ubicacion = $Ubicacion;
    }

    public function getTipoServicio() {
        return $this->TipoServicio;
    }

    public function setTipoServicio($TipoServicio) {
        $this->TipoServicio = $TipoServicio;
    }

    public function getVentaDirecta() {
        return $this->ventaDirecta;
    }

    public function setVentaDirecta($ventaDirecta) {
        $this->ventaDirecta = $ventaDirecta;
    }

    public function getNoSerieOriginal() {
        return $this->NoSerieOriginal;
    }

    public function setNoSerieOriginal($NoSerieOriginal) {
        $this->NoSerieOriginal = $NoSerieOriginal;
    }

    public function getIdKServicio() {
        return $this->IdKServicio;
    }

    public function setIdKServicio($IdKServicio) {
        $this->IdKServicio = $IdKServicio;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getDemo() {
        return $this->Demo;
    }

    function setDemo($Demo) {
        $this->Demo = $Demo;
    }

}

?>
