<?php

include_once ("Catalogo.class.php");
include_once ("Log.class.php");

class CambiarEstatusNota {

    private $idNotaTicket;
    private $idTicket;
    private $diagnosticoSolucion;
    private $idestatusAtencion;
    private $activo;
    private $fechaHora;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $cantidadAlmacen;
    private $MostrarCliente;
    private $usuarioSolicitud;
    private $cantidadExistentesAlmacen;
    private $cantidadApartadas;
    private $noParte;
    private $arrayNoComponente = Array();
    private $modelo;

    public function cambiarNota() {
        $consulta = ("INSERT INTO c_notaticket(IdTicket,DiagnosticoSol,IdEstatusAtencion,FechaHora,Activo,UsuarioSolicitud,MostrarCliente,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idTicket . "','" . $this->diagnosticoSolucion . "','" . $this->idestatusAtencion . "',now()," . $this->activo . ",'" . $this->usuarioSolicitud . "'," . $this->MostrarCliente . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");        
        $catalogo = new Catalogo(); $this->idNotaTicket = $catalogo->insertarRegistro($consulta);
        if ($this->idNotaTicket!=NULL && $this->idNotaTicket!=0) {
            return true;
        }
        return false;
    }

    public function obtenerExistenciaAlmacen($idAlmacen, $idRefaccion) {
        $consulta = ("SELECT ca.cantidad_existencia AS total,cantidad_apartados FROM k_almacencomponente ca WHERE ca.id_almacen='" . $idAlmacen . "' AND ca.NoParte='" . $idRefaccion . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->cantidadAlmacen = $rs['total'];
            $this->cantidadApartadas = $rs['cantidad_apartados'];
            return true;
        }
        return false;
    }

    public function EditAlmacenRefaccion($nota, $componente, $almacen) {
        $consulta = ("UPDATE k_nota_refaccion SET IdAlmacen = '" . $almacen . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE IdNotaTicket='" . $nota . "' AND NoParteComponente='" . $componente . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function EditApartados($almacen, $componente) {
        /*Verificamos que no entren existencias negativas*/
        if($this->cantidadAlmacen < 0){
            $log = new Log();
            $log->setConsulta("Intento de registrar existencias negativas ($this->cantidadAlmacen)");
            $log->setSeccion($this->pantalla);
            $log->setIdUsuario($_SESSION['idUsuario']);
            $log->setTipo("Incidencia sistema");
            $log->newRegistro();
            $this->cantidadAlmacen = 0;
        }
        
        /*Verificamos que no entren apartados negativos*/
        if($this->cantidadExistentesAlmacen < 0){
            $log = new Log();
            $log->setConsulta("Intento de registrar apartados negativos ($this->cantidadExistentesAlmacen)");
            $log->setSeccion($this->pantalla);
            $log->setIdUsuario($_SESSION['idUsuario']);
            $log->setTipo("Incidencia sistema");
            $log->newRegistro();
            $this->cantidadExistentesAlmacen = 0;
        } 
        $consulta = ("UPDATE k_almacencomponente SET cantidad_apartados = '" . $this->cantidadExistentesAlmacen . "',cantidad_existencia='" . $this->cantidadAlmacen . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE NoParte='" . $componente . "' AND id_almacen='" . $almacen . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function copiarNotaSolicitud($idNota) {
        $consulta = "INSERT INTO c_notaticket(IdTicket,DiagnosticoSol,IdEstatusAtencion,FechaHora,MostrarCliente,Activo,UsuarioSolicitud,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                SELECT IdTicket,DiagnosticoSol,24,now(),$this->MostrarCliente,Activo,UsuarioSolicitud,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "' FROM c_notaticket nt WHERE nt.IdNotaTicket='" . $idNota . "'";
        $catalogo = new Catalogo(); 
        $this->idNotaTicket = $catalogo->insertarRegistro($consulta);
        if ($this->idNotaTicket!=NULL && $this->idNotaTicket!=0) {
            return true;
        }
        return false;
    }

    public function CopiarRefaccionesSolicitadas($notaAnterio, $notaNueva) {
        $consulta = ("INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        SELECT '" . $notaNueva . "',NoParteComponente,Cantidad,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "' 
                        FROM k_nota_refaccion nt WHERE nt.IdNotaTicket='" . $notaAnterio . "'");        
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function copiarRefaccionesSolciitadasPorTicket($idTicket, $idNota){
        $consulta = ("INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        SELECT '" . $idNota . "',NoParteComponente,Cantidad,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "' 
                        FROM c_notaticket AS n 
                        LEFT JOIN k_nota_refaccion nt ON n.IdNotaTicket = nt.IdNotaTicket
                        WHERE n.IdTicket = $idTicket AND nt.Validada = 0 AND n.IdEstatusAtencion = 9 AND nt.NoParteComponente NOT IN(SELECT NoParteComponente FROM c_notaticket
                        INNER JOIN k_nota_refaccion ON c_notaticket.IdNotaTicket = k_nota_refaccion.IdNotaTicket
                        WHERE c_notaticket.IdEstatusAtencion = 17 AND c_notaticket.IdTicket = n.IdTicket);");  
        //echo $consulta;
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        
        if ($query >= 1) {            
            $consulta = "UPDATE k_nota_refaccion SET Validada = 1 WHERE IdNotaTicket IN (SELECT IdNotaTicket FROM c_notaticket WHERE IdTicket = $idTicket AND IdEstatusAtencion = 9);";
            
            $catalogo->obtenerLista($consulta);
            return true;
        }
        return false;
    }

    public function obtenerNoParteEquipo($idNota) {
        $consulta = ("SELECT b.NoParte FROM c_bitacora b WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket=(SELECT nt.IdTicket FROM c_notaticket nt WHERE nt.IdNotaTicket='" . $idNota . "'))");
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['NoParte'];
            return true;
        }
        return false;
    }

    public function obtenerComponentesNotaRefaccion($idNota) {
        $consulta = ("SELECT nr.NoParteComponente FROM k_nota_refaccion nr WHERE nr.IdNotaTicket='" . $idNota . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        While ($rs = mysql_fetch_array($query)) {
            $this->arrayNoComponente[$contador] = $rs['NoParteComponente'];
            $contador++;
        }
    }

    public function ObtenerNoParteVarios($idNota) {
        $consulta = ("SELECT e.NoParte FROM c_equipo e 
                                WHERE e.Modelo=( SELECT p.Modelo FROM c_pedido p WHERE p.IdTicket=(SELECT nt.IdTicket FROM c_notaticket nt WHERE nt.IdNotaTicket='" . $idNota . "') 
                                AND p.IdPedido=(SELECT MAX(p2.IdPedido) AS id FROM c_pedido p2 WHERE p2.IdTicket=(SELECT nt.IdTicket FROM c_notaticket nt WHERE nt.IdNotaTicket='" . $idNota . "')))");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['NoParte'];
            return true;
        }
        return false;
    }

    public function getCantidadExistentesAlmacen() {
        return $this->cantidadExistentesAlmacen;
    }

    public function setCantidadExistentesAlmacen($cantidadExistentesAlmacen) {
        $this->cantidadExistentesAlmacen = $cantidadExistentesAlmacen;
    }

    public function getIdNotaTicket() {
        return $this->idNotaTicket;
    }

    public function setIdNotaTicket($idNotaTicket) {
        $this->idNotaTicket = $idNotaTicket;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function getDiagnosticoSolucion() {
        return $this->diagnosticoSolucion;
    }

    public function setDiagnosticoSolucion($diagnosticoSolucion) {
        $this->diagnosticoSolucion = $diagnosticoSolucion;
    }

    public function getIdestatusAtencion() {
        return $this->idestatusAtencion;
    }

    public function setIdestatusAtencion($idestatusAtencion) {
        $this->idestatusAtencion = $idestatusAtencion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function getFechaHora() {
        return $this->fechaHora;
    }

    public function setFechaHora($fechaHora) {
        $this->fechaHora = $fechaHora;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getCantidadAlmacen() {
        return $this->cantidadAlmacen;
    }

    public function setCantidadAlmacen($cantidadAlmacen) {
        $this->cantidadAlmacen = $cantidadAlmacen;
    }

    public function getMostrarCliente() {
        return $this->MostrarCliente;
    }

    public function setMostrarCliente($MostrarCliente) {
        $this->MostrarCliente = $MostrarCliente;
    }

    public function getUsuarioSolicitud() {
        return $this->usuarioSolicitud;
    }

    public function setUsuarioSolicitud($usuarioSolicitud) {
        $this->usuarioSolicitud = $usuarioSolicitud;
    }

    public function getCantidadApartadas() {
        return $this->cantidadApartadas;
    }

    public function setCantidadApartadas($cantidadApartadas) {
        $this->cantidadApartadas = $cantidadApartadas;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getArrayNoComponente() {
        return $this->arrayNoComponente;
    }

    public function setArrayNoComponente($arrayNoComponente) {
        $this->arrayNoComponente = $arrayNoComponente;
    }

    public function getModelo() {
        return $this->modelo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }

}

?>
