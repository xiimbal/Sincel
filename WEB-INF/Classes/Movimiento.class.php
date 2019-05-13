<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of Movimiento
 *
 * @author MAGG
 */
class Movimiento {

    private $id_movimientos;
    private $NoSerie;
    private $clave_cliente_anterior;
    private $clave_centro_costo_anterior;
    private $k_anexo_anterior;
    private $clave_cliente_nuevo;
    private $clave_centro_costo_nuevo;
    private $k_anexo_nuevo;
    private $almacen_anterior;
    private $almacen_nuevo;
    private $tipo_movimiento;
    private $Fecha;
    private $id_lectura;
    private $id_lectura2;
    private $lectura;
    private $pendiente;
    private $id_autorizador;
    private $comentario;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;

    /**
     * 
     * @param type $Serie
     * @param type $AnteriorCliente
     * @param type $AnteriorLocalidad
     * @param type $NuevoCliente
     * @param type $NuevaLocalidad
     * @param type $Pantalla
     * @return boolean
     */
    public function nuevoMovimientoClienteCliente($Serie, $AnteriorCliente, $AnteriorLocalidad, $NuevoCliente, $NuevaLocalidad, $Pantalla) {
        $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,clave_cliente_nuevo,
                    clave_centro_costo_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                    VALUES('$Serie','$AnteriorCliente','$AnteriorLocalidad','$NuevoCliente','$NuevaLocalidad',1,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$Pantalla',0);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $Serie
     * @param type $NuevoCliente
     * @param type $NuevaLocalidad
     * @param type $Pantalla
     * @return boolean
     */
    public function nuevoMovimientoaCliente($Serie, $NuevoCliente, $NuevaLocalidad, $Pantalla) {
        $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_nuevo,
                    clave_centro_costo_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                    VALUES('$Serie','$NuevoCliente','$NuevaLocalidad',1,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$Pantalla',0);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $Serie
     * @param type $id_lectura
     * @param type $lectura
     * @param type $Cliente
     * @param type $Localidad
     * @return boolean
     */
    public function actualizarLecturaCliente($Serie, $NuevoCliente, $NuevaLocalidad, $id_lectura2, $lectura) {
        $consulta = "UPDATE movimientos_equipo SET id_lectura2 = $id_lectura2 , lectura = $lectura"
                . " where id_movimientos = (select * from (select MAX(id_movimientos) as i from movimientos_equipo where NoSerie = '$Serie'"
                . " and clave_cliente_nuevo = '$NuevoCliente' and clave_centro_costo_nuevo = '$NuevaLocalidad') AS X);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }
    /**
     * 
     * @param type $Serie
     * @param type $AnteriorAlmacen
     * @param type $NuevoCliente
     * @param type $NuevaLocalidad
     * @param type $Pantalla
     * @return boolean
     */
    public function nuevoMovimientoAlmacenCliente($Serie, $AnteriorAlmacen, $NuevoCliente, $NuevaLocalidad, $Pantalla, $idSolicitud) {
        if (isset($NuevaLocalidad) && $NuevaLocalidad != "") {
            $NuevaLocalidad = "'$NuevaLocalidad'";
        } else {
            $NuevaLocalidad = "null";
        }
        
        if(!isset($this->id_lectura) || empty($this->id_lectura)){
            $this->id_lectura = "null";
        }
        
        $consulta = "INSERT INTO movimientos_equipo(NoSerie,almacen_anterior,clave_cliente_nuevo,id_lectura,
                        clave_centro_costo_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente,id_solicitud)
                        VALUES('$Serie','$AnteriorAlmacen','$NuevoCliente',$this->id_lectura,$NuevaLocalidad,2,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$Pantalla',1,$idSolicitud);";
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $Serie
     * @param type $AnteriorAlmacen
     * @param type $NuevoAlmacen
     * @param type $Pantalla
     * @return boolean
     */
    public function nuevoMovimientoAlmacenAlmacen($Serie, $AnteriorAlmacen, $NuevoAlmacen, $Pantalla) {
        $consulta = "INSERT INTO movimientos_equipo(NoSerie,almacen_anterior,almacen_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                            VALUES('$Serie','$AnteriorAlmacen','$NuevoAlmacen',4,NOW(),'" . $_SESSION['user'] . "',NOW(),'" . $_SESSION['user'] . "',NOW(),'$Pantalla',0);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == "1") {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $Serie
     * @param type $AnteriorCliente
     * @param type $AnteriorLocalidad
     * @param type $NuevoAlmacen
     * @param type $usuario
     * @param type $Pantalla
     * @return boolean
     */
    public function nuevoMovimientoClienteAlmacen($Serie, $AnteriorCliente, $AnteriorLocalidad, $NuevoAlmacen, $IdTipoMovimiento, 
            $Causa, $FechaMovimiento, $pendiente, $usuario, $Pantalla, $IdKServicio, $IdServicio, $IdAnexo, $IdKServicioGimGfa) {
        if(empty($IdAnexo)){
            $IdAnexo = "NULL";
        }
        if(empty($IdKServicioGimGfa)){
            $IdKServicioGimGfa = "NULL";
        }
        if(empty($IdKServicio)){
            $IdKServicio = "NULL";
        }
        if(empty($IdServicio)){
            $IdServicio = "NULL";
        }
        $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,almacen_nuevo,tipo_movimiento,
                    Fecha,IdTipoMovimiento,causa_movimiento,
                    IdAnexoClienteCCAnterior,IdKserviciogimgfaAnterior,IdKServicioAnterior,IdServicioAnterior,
                    UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente)
                    VALUES('$Serie','$AnteriorCliente','$AnteriorLocalidad',$NuevoAlmacen,3,'$FechaMovimiento',$IdTipoMovimiento,'$Causa',"
                    . "$IdAnexo,$IdKServicioGimGfa,$IdKServicio,$IdServicio,"
                    . "'$usuario',NOW(),'$usuario',NOW(),'$Pantalla',$pendiente);";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->id_movimientos = $catalogo->insertarRegistro($consulta);
        if ($this->id_movimientos != NULL && $this->id_movimientos != 0) {
            return true;
        }
        return false;
    }

    public function cambiarEstatusMovimiento($estatus, $comentario) {
        $consulta = ("UPDATE `movimientos_equipo` SET pendiente = $estatus, comentario = '$comentario', 
            id_autorizador = " . $_SESSION['idUsuario'] . ",
            UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = now(), Pantalla = 'PHP Acepta Movimiento' 
            WHERE id_movimientos = $this->id_movimientos;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $update = $catalogo->obtenerLista($consulta);
        if ($update == "1") {
            return true;
        }
        return false;
    }

    public function editMovimientoEquipo($comentario, $idAlmacen, $idLectura) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if ($idAlmacen == "0") {
            $update = $catalogo->obtenerLista("UPDATE movimientos_equipo SET pendiente = 0, id_lectura2 = '$idLectura', lectura = 0, comentario = '$comentario',id_autorizador = '" . $_SESSION['idUsuario'] . "',
            UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "', FechaUltimaModificacion = now(), Pantalla = '" . $this->Pantalla . "' 
            WHERE id_movimientos = $this->id_movimientos;");
            $consulta = "UPDATE movimientos_equipo SET pendiente = 0 WHERE NoSerie = '$this->NoSerie'";
            $catalogo->obtenerLista($consulta);
        } else {
            $update = $catalogo->obtenerLista("UPDATE movimientos_equipo SET pendiente = 0,id_lectura2 = '$idLectura', lectura = 0, comentario = '$comentario',almacen_nuevo='$idAlmacen',id_autorizador = '" . $_SESSION['idUsuario'] . "',
            UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "', FechaUltimaModificacion = now(), Pantalla = '" . $this->Pantalla . "' 
            WHERE id_movimientos = $this->id_movimientos;");
            $consulta = "UPDATE movimientos_equipo SET pendiente = 0 WHERE NoSerie = '$this->NoSerie'";
            $catalogo->obtenerLista($consulta);
        }

        if ($update == "1") {
            return true;
        }
        return false;
    }

    public function editEquipoEnAlmacen($nuevoAlmacen, $almacen, $noSerie) {
        $consulta = ("UPDATE k_almacenequipo SET id_almacen = $nuevoAlmacen, UsuarioUltimaModificacion = '" . $_SESSION['user'] . "', FechaUltimaModificacion = now(), Pantalla = '" . $this->Pantalla . "' 
            WHERE id_almacen ='" . $almacen . "' AND NoSerie='" . $noSerie . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $update = $catalogo->obtenerLista($consulta);
        if ($update == "1") {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $Serie
     * @param type $AnteriorCliente
     * @param type $AnteriorLocalidad
     * @param type $NuevoAlmacen
     * @param type $IdTipoMovimiento
     * @param type $Causa
     * @param type $FechaMovimiento
     * @param type $pendiente
     * @param type $usuario
     * @param type $Pantalla
     * @param type $idLectura
     * @return boolean
     */
    public function nuevoMovimientoClienteAlmacenFull($Serie, $AnteriorCliente, $AnteriorLocalidad, $NuevoAlmacen, $IdTipoMovimiento, $Causa, 
            $FechaMovimiento, $pendiente, $usuario, $Pantalla, $idLectura,$IdAnexo,$IdKServicioGimGfa,$IdKServicio,$IdServicio) {
        if ($idLectura == "") {
            $idLectura = "null";
        }
        $consulta = "INSERT INTO movimientos_equipo(NoSerie,clave_cliente_anterior,clave_centro_costo_anterior,almacen_nuevo,tipo_movimiento,
                        Fecha,IdTipoMovimiento,causa_movimiento,
                        IdAnexoClienteCCAnterior,IdKserviciogimgfaAnterior,IdKServicioAnterior,IdServicioAnterior,
                        UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pendiente,id_lectura)
                        VALUES('$Serie','$AnteriorCliente','$AnteriorLocalidad',$NuevoAlmacen,3,'$FechaMovimiento',$IdTipoMovimiento,'$Causa',"
                        . "$IdAnexo,$IdKServicioGimGfa,$IdKServicio,$IdServicio,"
                        . "'$usuario',NOW(),'$usuario',NOW(),'$Pantalla',$pendiente,$idLectura);";        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->id_movimientos = $catalogo->insertarRegistro($consulta);
        if ($this->id_movimientos != NULL && $this->id_movimientos != 0) {
            return true;
        }
        return false;
    }

    public function getId_movimientos() {
        return $this->id_movimientos;
    }

    public function setId_movimientos($id_movimientos) {
        $this->id_movimientos = $id_movimientos;
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getClave_cliente_anterior() {
        return $this->clave_cliente_anterior;
    }

    public function setClave_cliente_anterior($clave_cliente_anterior) {
        $this->clave_cliente_anterior = $clave_cliente_anterior;
    }

    public function getClave_centro_costo_anterior() {
        return $this->clave_centro_costo_anterior;
    }

    public function setClave_centro_costo_anterior($clave_centro_costo_anterior) {
        $this->clave_centro_costo_anterior = $clave_centro_costo_anterior;
    }

    public function getK_anexo_anterior() {
        return $this->k_anexo_anterior;
    }

    public function setK_anexo_anterior($k_anexo_anterior) {
        $this->k_anexo_anterior = $k_anexo_anterior;
    }

    public function getClave_cliente_nuevo() {
        return $this->clave_cliente_nuevo;
    }

    public function setClave_cliente_nuevo($clave_cliente_nuevo) {
        $this->clave_cliente_nuevo = $clave_cliente_nuevo;
    }

    public function getClave_centro_costo_nuevo() {
        return $this->clave_centro_costo_nuevo;
    }

    public function setClave_centro_costo_nuevo($clave_centro_costo_nuevo) {
        $this->clave_centro_costo_nuevo = $clave_centro_costo_nuevo;
    }

    public function getK_anexo_nuevo() {
        return $this->k_anexo_nuevo;
    }

    public function setK_anexo_nuevo($k_anexo_nuevo) {
        $this->k_anexo_nuevo = $k_anexo_nuevo;
    }

    public function getAlmacen_anterior() {
        return $this->almacen_anterior;
    }

    public function setAlmacen_anterior($almacen_anterior) {
        $this->almacen_anterior = $almacen_anterior;
    }

    public function getAlmacen_nuevo() {
        return $this->almacen_nuevo;
    }

    public function setAlmacen_nuevo($almacen_nuevo) {
        $this->almacen_nuevo = $almacen_nuevo;
    }

    public function getTipo_movimiento() {
        return $this->tipo_movimiento;
    }

    public function setTipo_movimiento($tipo_movimiento) {
        $this->tipo_movimiento = $tipo_movimiento;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function getId_lectura() {
        return $this->id_lectura;
    }

    public function setId_lectura($id_lectura) {
        $this->id_lectura = $id_lectura;
    }

    public function getPendiente() {
        return $this->pendiente;
    }

    public function setPendiente($pendiente) {
        $this->pendiente = $pendiente;
    }

    public function getId_autorizador() {
        return $this->id_autorizador;
    }

    public function setId_autorizador($id_autorizador) {
        $this->id_autorizador = $id_autorizador;
    }

    public function getComentario() {
        return $this->comentario;
    }

    public function setComentario($comentario) {
        $this->comentario = $comentario;
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
    
    function getId_lectura2() {
        return $this->id_lectura2;
    }

    function getLectura() {
        return $this->lectura;
    }

    function setId_lectura2($id_lectura2) {
        $this->id_lectura2 = $id_lectura2;
    }

    function setLectura($lectura) {
        $this->lectura = $lectura;
    }

}

?>
