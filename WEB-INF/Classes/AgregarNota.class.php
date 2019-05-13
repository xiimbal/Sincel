<?php

include_once ("Parametros.class.php");
include_once ("Catalogo.class.php");

class AgregarNota {

    private $idNotaTicket;
    private $idTicket;
    private $diagnosticoSolucion;
    private $idestatusAtencion;
    private $activo;
    private $fechaHora;
    private $claveProveedor;
    private $idArea;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $refaccion;
    private $cantidad;
    private $id;
    private $usuarioSolicitud;
    private $show;
    private $arreglo_php = array();
    private $estatusRefaccion;
    private $modelo;
    private $noSerie;
    private $noParte; 
    private $PathImagen;
    private $validada;

    public function obtenerListaComponentes($idTicket) {        
        $consulta = ("SELECT c.NoParte, c.IdTipoComponente, c.Modelo, c.Descripcion, cc.NoParteEquipo, cc.Soportado
                               FROM c_componente AS c
                               LEFT JOIN k_equipocomponentecompatible AS cc ON c.NoParte = cc.NoParteComponente
                               WHERE (cc.NoParteEquipo = (SELECT b.NoParte FROM c_bitacora b WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket='" . $idTicket . "')) OR ISNULL(NoParteEquipo)) AND c.IdTipoComponente=1 ORDER BY c.Modelo ASC");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) == 0)
            array_push($this->arreglo_php, "No hay datos");
        else {
            $countador = 0;
            while ($palabras = mysql_fetch_array($query)) {
                $this->arreglo_php[$countador] = $palabras["NoParte"];
                $countador++;
            }
        }
    }

    public function getRegistroById($id) {        
        $consulta = ("SELECT * FROM c_notaticket WHERE IdNotaTicket='" . $id . "'");
        //echo $consulta . "<br>";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idNotaTicket = $rs['IdNotaTicket'];
            $this->idTicket = $rs['IdTicket'];
            $this->diagnosticoSolucion = $rs['DiagnosticoSol'];
            $this->idestatusAtencion = $rs['IdEstatusAtencion'];
            $this->activo = $rs['Activo'];
            $this->usuarioSolicitud = $rs['UsuarioSolicitud'];
            $this->fechaHora = $rs['FechaHora'];
            $this->show = $rs['MostrarCliente'];
        }
        return $query;
    }

    public function getRefaccionesById($id) {        
        $consulta = "SELECT * FROM k_nota_refaccion WHERE IdNotaTicket=$id;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    /**
     * Obtiene las refacciones solicitadas, su cantidad y la cantidad marcada como lista o como no surtir
     * @param type $id
     * @return type
     */
    public function estaTodaEntregadaNotaRefaccion($id, $refaccion){
        $consulta = "SELECT nr.NoParteComponente, nr.Cantidad AS CantidadSolicitada,
            (SELECT SUM(nr2.Cantidad) FROM c_ticket AS t LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket LEFT JOIN k_nota_refaccion AS nr2 ON nr2.IdNotaTicket = nt.IdNotaTicket
            WHERE t.IdTicket = cnt.IdTicket AND nt.IdEstatusAtencion IN(20,21) AND nr2.NoParteComponente = nr.NoParteComponente) AS CantidadListo
            FROM k_nota_refaccion AS nr 
            LEFT JOIN c_notaticket cnt ON cnt.IdNotaTicket = nr.IdNotaTicket
            WHERE nr.IdNotaTicket=$id AND nr.NoParteComponente = '$refaccion' GROUP BY NoParteComponente;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            if(!isset($rs['CantidadListo']) || $rs['CantidadSolicitada'] > $rs['CantidadListo']){
                return false;
            }else{
                return true;
            }
        }
        return true;
    }

    public function newRegistro() {
        if(isset($this->PathImagen) && $this->PathImagen != ""){
            $path = ",PathImagen";
            $pathI = ",'$this->PathImagen'";
        }
        if ($this->fechaHora == "") {
            $catalogo = new Catalogo();
            $consulta = ("INSERT INTO c_notaticket(IdTicket,DiagnosticoSol,IdEstatusAtencion,FechaHora,MostrarCliente,Activo,UsuarioSolicitud,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla$path)
                VALUES('" . $this->idTicket . "','" . $this->diagnosticoSolucion . "','" . $this->idestatusAtencion . "',now()," . $this->show . "," . $this->activo . ",'" . $this->usuarioSolicitud . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'$pathI)");
            $this->idNotaTicket = $catalogo->insertarRegistro($consulta);            
            if ($this->idNotaTicket != NULL && $this->idNotaTicket != 0) {
                return true;
            }
            return false;
        } else {
            $catalogo = new Catalogo();
            $consulta = ("INSERT INTO c_notaticket(IdNotaTicket,IdTicket,DiagnosticoSol,IdEstatusAtencion,FechaHora,MostrarCliente,Activo,UsuarioSolicitud,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla$path)
            VALUES(0,'" . $this->idTicket . "','" . $this->diagnosticoSolucion . "','" . $this->idestatusAtencion . "',now()," . $this->show . "," . $this->activo . ",'" . $this->usuarioSolicitud . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'$pathI)");
            $this->idNotaTicket = $catalogo->insertarRegistro($consulta);            
            if ($this->idNotaTicket != NULL && $this->idNotaTicket != 0) {
                return true;
            }
            return false;
        }
    }
    
    public function newRegistro2() {
        if(isset($this->PathImagen) && $this->PathImagen != ""){
            $path = ",PathImagen";
            $pathI = ",'$this->PathImagen'";
        }
        if ($this->fechaHora == "") {
            $catalogo = new Catalogo();
            $consulta = ("INSERT INTO c_notaticket(IdTicket,DiagnosticoSol,IdEstatusAtencion,FechaHora,MostrarCliente,Activo,UsuarioSolicitud,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla$path)
                VALUES('" . $this->idTicket . "','" . $this->diagnosticoSolucion . "','" . $this->idestatusAtencion . "',now()," . $this->show . "," . $this->activo . ",'" . $this->usuarioSolicitud . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'$pathI)");
            $this->idNotaTicket = $catalogo->insertarRegistro($consulta);            
            if ($this->idNotaTicket != NULL && $this->idNotaTicket != 0) {
                return true;
            }
            return false;
        } else {
            $catalogo = new Catalogo();
            $consulta = ("INSERT INTO c_notaticket(IdNotaTicket,IdTicket,DiagnosticoSol,IdEstatusAtencion,FechaHora,MostrarCliente,Activo,UsuarioSolicitud,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla$path)
            VALUES(0,'" . $this->idTicket . "','" . $this->diagnosticoSolucion . "','" . $this->idestatusAtencion . "','".$this->fechaHora."'," . $this->show . "," . $this->activo . ",'" . $this->usuarioSolicitud . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'$pathI)");
            $this->idNotaTicket = $catalogo->insertarRegistro($consulta);            
            if ($this->idNotaTicket != NULL && $this->idNotaTicket != 0) {
                return true;
            }
            return false;
        }
    }

    public function reabrirNotas() {        
        $consulta = ("UPDATE c_notaticket SET IdEstatusAtencion = $this->idestatusAtencion WHERE IdTicket = $this->idTicket AND IdEstatusAtencion = 16;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function comprobarExistenciaRefaccion($idNota, $refaccion) {        
        $consulta = ("SELECT * FROM k_nota_refaccion nr WHERE nr.IdNotaTicket='" . $idNota . "' AND nr.NoParteComponente='" . $refaccion . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return FALSE;
        }
        return TRUE;
    }
    
    public function comprobarExistenciaRefaccionEnTicket($idTicket, $refaccion, $idEstatus){
        $parametros = new Parametros();
        
        if($parametros->getRegistroById(27) && $parametros->getValor()=="1"){
            return true;
        }
        
        $consulta = ("SELECT nr.IdNotaTicket, nr.NoParteComponente 
            FROM k_nota_refaccion nr 
            INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = nr.IdNotaTicket             
            WHERE nt.IdTicket = '$idTicket' AND nt.IdEstatusAtencion = $idEstatus AND nr.NoParteComponente='$refaccion';");         
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);        
        while($rs = mysql_fetch_array($result)){            
            return false;
        }        
        return true;
    }

    public function EditEstadoTicket($idTicket, $estatus) {
        $consulta = ("UPDATE c_ticket  SET EstadoDeTicket = $estatus,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdTicket='" . $idTicket . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newProveedor($idNota) {
        $consulta = ("INSERT INTO k_proveedor_nota(IdNota,ClaveProveedor,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $idNota . "','" . $this->claveProveedor . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newArea($idNota) {
        $consulta = ("INSERT INTO k_nota_area(IdNota,IdArea,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $idNota . "','" . $this->idArea . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editTicket($id, $atencion) {
        $consulta = ("UPDATE  c_ticket  SET AreaAtencion = '" . $atencion . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdTicket='" . $id . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function ObtenerModelo($NoParte) {
        $consulta = ("SELECT * FROM c_componente c WHERE c.NoParte='" . $NoParte . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->modelo = $rs['Modelo'];
            return true;
        }
        return false;
    }

    public function VerificarNoSerie($idTicket) {
        $consulta = ("SELECT b.NoParte FROM c_bitacora b  WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket ='" . $idTicket . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['NoParte'];
            return true;
        }
        return false;
    }

    public function ObtenerNoParte($idTicket) {
        $consulta = ("SELECT b.NoParte FROM c_bitacora b  WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket IN ($idTicket))");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['Modelo'];
            return true;
        }
        return false;
    }

    public function newRefaccion($idNota) {
        if(isset($this->validada) && $this->validada != ""){
            $validada = $this->validada;
        }else{
            $validada = 0;
        }
        $consulta = ("INSERT INTO k_nota_refaccion(IdNotaTicket,NoParteComponente,Cantidad,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,
            FechaUltimaModificacion,Pantalla,CantidadNota,Validada)
            VALUES('" . $idNota . "','" . $this->refaccion . "','" . $this->cantidad . "','" . $this->usuarioCreacion . "',now(),
                '" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','".$this->cantidad."',$validada)");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    

    public function cambiarCantidadRefaccion($idNota) {
        $consulta = ("UPDATE k_nota_refaccion SET Cantidad = '" . $this->cantidad . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' 
            WHERE IdNotaTicket='" . $idNota . "' AND NoParteComponente='" . $this->refaccion . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function ComprobarExistenciaCompatiblesEquipo($equipo, $componente) {
        $consulta = ("SELECT * FROM k_equipocomponentecompatible ecc WHERE ecc.NoParteEquipo='" . $equipo . "' AND ecc.NoParteComponente='" . $componente . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return FALSE;
        }
        return TRUE;
    }

    public function newEquipoComponenteCompatible() {
        $consulta = ("INSERT INTO k_equipocomponentecompatible(NoParteEquipo,NoParteComponente,Soportado,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->noParte . "','" . $this->refaccion . "','1','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getEstatusRefaccion() {
        return $this->estatusRefaccion;
    }

    public function setEstatusRefaccion($estatusRefaccion) {
        $this->estatusRefaccion = $estatusRefaccion;
    }

    public function getShow() {
        return $this->show;
    }

    public function setShow($show) {
        $this->show = $show;
    }

    public function newNota() {
        $consulta = ("INSERT INTO k_notaatendidas(IdNotaAtendidad,IdNota,NoParteRefaccion,CantidadAtendida,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $idNota . "','" . $this->refaccion . "','" . $this->cantidad . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_notaticket SET DiagnosticoSol = '" . $this->diagnosticoSolucion . "',IdEstatusAtencion = '" . $this->idestatusAtencion . "',FechaHora='" . $this->fechaHora . "',MostrarCliente=" . $this->show . ", Activo = " . $this->activo . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "',MostrarCliente=0 WHERE IdNotaTicket='" . $this->idNotaTicket . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM k_nota_refaccion WHERE IdNotaTicket = '" . $this->idNotaTicket . "';");        
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function modificarPathImagen(){
        $consulta = "UPDATE c_notaticket SET PathImagen = '$this->PathImagen', FechaUltimaModificacion = now(), UsuarioUltimaModificacion = '$this->usuarioModificacion' "
                . "WHERE IdNotaTicket = $this->idNotaTicket";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if($query == 1){
            return true;
        }
        return false;
    }

    public function getArreglo_php() {
        return $this->arreglo_php;
    }

    public function setArreglo_php($arreglo_php) {
        $this->arreglo_php = $arreglo_php;
    }

    public function getUsuarioSolicitud() {
        return $this->usuarioSolicitud;
    }

    public function setUsuarioSolicitud($usuarioSolicitud) {
        $this->usuarioSolicitud = $usuarioSolicitud;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getClaveProveedor() {
        return $this->claveProveedor;
    }

    public function setClaveProveedor($claveProveedor) {
        $this->claveProveedor = $claveProveedor;
    }

    public function getIdArea() {
        return $this->idArea;
    }

    public function setIdArea($idArea) {
        $this->idArea = $idArea;
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

    public function getRefaccion() {
        return $this->refaccion;
    }

    public function setRefaccion($refaccion) {
        $this->refaccion = $refaccion;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function getModelo() {
        return $this->modelo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getNoSerie() {
        return $this->noSerie;
    }

    public function setNoSerie($noSerie) {
        $this->noSerie = $noSerie;
    }

    public function getValidada() {
        return $this->validada;
    }

    public function setValidada($validada) {
        $this->validada = $validada;
    }
    
    function getPathImagen() {
        return $this->PathImagen;
    }

    function setPathImagen($PathImagen) {
        $this->PathImagen = $PathImagen;
    }

    
}

?>
