<?php

include_once("Catalogo.class.php");

class NotaTicket {

    private $idNota;
    private $idTicket;
    private $diagnostico;
    private $idEstatus;
    private $IdEstadoNota;
    private $activo;
    private $usuarioSolicitud;
    private $mostrarCliente;
    private $fechaHora;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $usuarioModificacion;
    private $fechaModificacion;
    private $pantalla;
    private $empresa;
    private $Titulo;
    private $PathImagen;
    private $NombreImagen;
    private $Latitud;
    private $Longitud;
    private $MinutosDefase;
    private $IdTipoViatico;
    private $Prioridad;
    private $Codigo;
    private $IdTecnicoAsignado;
    private $Progreso;
    private $HorasTrabajadas;
    private $FechaInicio;
    private $FechaFin;
    private $Descripcion;

    /**
     * Obtiene todas las notas del ticket especificado
     * @param type $idTicket Id del ticket
     * @return type resultset con toda la información de las notas del ticket,
     */
    public function getNotasByTicket($idTicket){
        $consulta = ("SELECT * FROM c_notaticket WHERE IdTicket = '$idTicket'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_notaticket nt WHERE nt.IdNotaTicket='$id'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idNota = $rs['IdNotaTicket'];
            $this->idTicket = $rs['IdTicket'];
            $this->diagnostico = $rs['DiagnosticoSol'];
            $this->idEstatus = $rs['IdEstatusAtencion'];
            $this->IdEstadoNota = $rs['IdEstadoNota'];
            $this->activo = $rs['Activo'];
            $this->usuarioSolicitud = $rs['UsuarioSolicitud'];
            $this->mostrarCliente = $rs['MostrarCliente'];
            $this->fechaHora = $rs['FechaHora'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            $this->fechaCreacion = $rs['FechaCreacion'];
            $this->usuarioModificacion = $rs['UsuarioUltimaModificacion'];
            $this->fechaModificacion = $rs['FechaUltimaModificacion'];
            $this->pantalla = $rs['Pantalla'];
            $this->Titulo = $rs['Titulo'];
            $this->Prioridad = $rs['Prioridad'];
            $this->Codigo = $rs['Codigo'];
            $this->IdTecnicoAsignado = $rs['IdTecnicoAsignado'];
            $this->Progreso = $rs['Progreso'];
            $this->HorasTrabajadas = $rs['HorasTrabajadas'];
            $this->FechaInicio = $rs['FechaInicio'];
            $this->FechaFin = $rs['FechaFin'];
            $this->Descripcion = $rs['Descripcion'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        if(!isset($this->Latitud) || empty($this->Latitud)){
            $this->Latitud = "NULL";
        }
        
        if(!isset($this->Longitud) || empty($this->Longitud)){
            $this->Longitud = "NULL";
        }
        
        if(!isset($this->MinutosDefase) || empty($this->MinutosDefase)){
            $this->MinutosDefase = "NULL";
        }
        
        if(!isset($this->IdTipoViatico) || empty($this->IdTipoViatico)){
            $this->IdTipoViatico = "NULL";
        }
        
        if(!isset($this->fechaHora) || empty($this->fechaHora)){
            $this->fechaHora = "NOW()";
        }else{
            $this->fechaHora = "'$this->fechaHora'";
        }
        if(empty($this->Prioridad)){
            $this->Prioridad = "NULL";
        }
        if(empty($this->IdTecnicoAsignado)){
            $this->IdTecnicoAsignado = "NULL";
        }
        if(empty($this->HorasTrabajadas)){
            $this->HorasTrabajadas = "0";
        }
        if(empty($this->FechaInicio)){
            $this->FechaInicio = "NULL";
        }else{
            $this->FechaInicio = "'$this->FechaInicio'";
        }
        if(empty($this->FechaFin)){
            $this->FechaFin = "NULL";
        }else{
            $this->FechaFin = "'$this->FechaFin'";
        }
        if(empty($this->IdEstadoNota)){
            $this->IdEstadoNota = "NULL";
        }
        if(!isset($this->Progreso) || empty($this->Progreso)){
            $this->Progreso = "NULL";
        }
        $consulta = ("INSERT INTO c_notaticket(IdTicket,DiagnosticoSol,IdEstatusAtencion,UsuarioSolicitud,MostrarCliente,FechaHora,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,
            Titulo, PathImagen, NombreImagen, Latitud, Longitud, MinutosDefase,IdViatico,Prioridad,Codigo,IdTecnicoAsignado,Progreso,
            HorasTrabajadas,FechaInicio,FechaFin,Descripcion,IdEstadoNota)
            VALUES('" . $this->idTicket . "','" . $this->diagnostico . "','" . $this->idEstatus . "','" . $this->usuarioSolicitud . "',"
                . "" . $this->mostrarCliente . ",$this->fechaHora," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',"
                . "now(),'" . $this->pantalla . "','$this->Titulo', '$this->PathImagen', '$this->NombreImagen', "
                . "$this->Latitud, $this->Longitud, $this->MinutosDefase, $this->IdTipoViatico,$this->Prioridad,'$this->Codigo',$this->IdTecnicoAsignado,$this->Progreso,"
                . "$this->HorasTrabajadas, $this->FechaInicio,$this->FechaFin,'$this->Descripcion',$this->IdEstadoNota)");        
        //echo $consulta . "<br>";
        $catalogo = new Catalogo(); 
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $this->idNota = $catalogo->insertarRegistro($consulta);
        if ($this->idNota!=NULL && $this->idNota!=0) {
            return true;
        }
        return false;
    }
    
   public function editRegistro(){
        if(!isset($this->Latitud) || empty($this->Latitud)){
            $this->Latitud = "NULL";
        }        
        if(!isset($this->Longitud) || empty($this->Longitud)){
            $this->Longitud = "NULL";
        }        
        if(!isset($this->MinutosDefase) || empty($this->MinutosDefase)){
            $this->MinutosDefase = "NULL";
        }        
        if(!isset($this->IdTipoViatico) || empty($this->IdTipoViatico)){
            $this->IdTipoViatico = "NULL";
        }        
        if(!isset($this->fechaHora) || empty($this->fechaHora)){
            $this->fechaHora = "NOW()";
        }else{
            $this->fechaHora = "'$this->fechaHora'";
        }
        if(empty($this->Prioridad)){
            $this->Prioridad = "NULL";
        }
        if(empty($this->IdTecnicoAsignado)){
            $this->IdTecnicoAsignado = "NULL";
        }
        if(empty($this->HorasTrabajadas)){
            $this->HorasTrabajadas = "0";
        }
        if(empty($this->FechaInicio)){
            $this->FechaInicio = "NULL";
        }else{
            $this->FechaInicio = "'$this->FechaInicio'";
        }
        if(empty($this->FechaFin)){
            $this->FechaFin = "NULL";
        }else{
            $this->FechaFin = "'$this->FechaFin'";
        }
        if(empty($this->IdEstadoNota)){
            $this->IdEstadoNota = "NULL";
        }
        if(!isset($this->Progreso) || empty($this->Progreso)){
            $this->Progreso = "NULL";
        }
        $consulta = ("UPDATE c_notaticket SET IdTicket='" . $this->idTicket . "',DiagnosticoSol='" . $this->diagnostico . "',IdEstatusAtencion='" . $this->idEstatus . "',
            UsuarioSolicitud='" . $this->usuarioSolicitud . "',MostrarCliente = $this->mostrarCliente,FechaHora=$this->fechaHora,Activo=$this->activo,
            UsuarioUltimaModificacion='" . $this->usuarioModificacion . "',FechaUltimaModificacion=now(),Pantalla='" . $this->pantalla . "',Titulo='$this->Titulo', 
            PathImagen='$this->PathImagen', NombreImagen='$this->NombreImagen',Prioridad=$this->Prioridad,Codigo='$this->Codigo',IdTecnicoAsignado=$this->IdTecnicoAsignado,
            Progreso=$this->Progreso,HorasTrabajadas=$this->HorasTrabajadas,FechaInicio=$this->FechaInicio,FechaFin=$this->FechaFin,Descripcion='$this->Descripcion',
            IdEstadoNota = $this->IdEstadoNota WHERE IdNotaTicket = $this->idNota");  
        //echo "$consulta <br>";
        $catalogo = new Catalogo(); 
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);      
        if ($result == 1) {
            return true;
        }
        return false;
   }

    public function deleteRegitro() {
        $consulta = ("DELETE FROM c_notaticket WHERE IdTicket='$this->idTicket' AND IdEstatusAtencion IN (65,67)");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function borrarRegistro($id){
        $consulta = "DELETE FROM c_notaticket WHERE IdNotaTicket = $id";
        //echo "$consulta <br>";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if($query == 1){
            return true;
        }
        return false;
    }
    
    /**
     * Cierra una nota
     * @param type $IdNota id de la nota
     * @return boolean true en caso de haber actualizado el estado, false en caso contrario
     */
    public function cerrarNota($IdNota){
        $consulta = ("UPDATE `c_notaticket` SET IdEstadoNota = 2 WHERE IdNotaTicket = $IdNota;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function updateNotaMostrarCliente($mostrar,$IdNota){
        $consulta = ("UPDATE `c_notaticket` SET MostrarCliente =$mostrar  WHERE IdNotaTicket = $IdNota;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function updateTipoViatico($IdTipoViatico, $IdNota){
        $consulta = ("UPDATE `c_notaticket` SET IdViatico = $IdTipoViatico  WHERE IdNotaTicket = $IdNota;");        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function getNotaTicketByTicket($id) {        
        $consulta = ("SELECT nt.IdNotaTicket FROM c_notaticket nt WHERE nt.IdTicket='$id' AND nt.IdEstatusAtencion = 67;");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->idNota = $rs['IdNotaTicket'];
            return true;
        }
        return false;
    }
    
    public function buscarNotaConArchivo(){
        $consulta = "SELECT * FROM c_notaticket WHERE IdTicket = $this->idTicket AND !ISNULL(PathImagen)";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) > 0){
            return true;
        }
        return false;
    }

    public function getIdNota() {
        return $this->idNota;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function getDiagnostico() {
        return $this->diagnostico;
    }

    public function getIdEstatus() {
        return $this->idEstatus;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getUsuarioSolicitud() {
        return $this->usuarioSolicitud;
    }

    public function getMostrarCliente() {
        return $this->mostrarCliente;
    }

    public function getFechaHora() {
        return $this->fechaHora;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setIdNota($idNota) {
        $this->idNota = $idNota;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function setDiagnostico($diagnostico) {
        $this->diagnostico = $diagnostico;
    }

    public function setIdEstatus($idEstatus) {
        $this->idEstatus = $idEstatus;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
    }

    public function setUsuarioSolicitud($usuarioSolicitud) {
        $this->usuarioSolicitud = $usuarioSolicitud;
    }

    public function setMostrarCliente($mostrarCliente) {
        $this->mostrarCliente = $mostrarCliente;
    }

    public function setFechaHora($fechaHora) {
        $this->fechaHora = $fechaHora;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }
    
    public function getIdEstadoNota() {
        return $this->IdEstadoNota;
    }

    public function setIdEstadoNota($IdEstadoNota) {
        $this->IdEstadoNota = $IdEstadoNota;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    function getTitulo() {
        return $this->Titulo;
    }

    function getPathImagen() {
        return $this->PathImagen;
    }

    function getNombreImagen() {
        return $this->NombreImagen;
    }

    function getLatitud() {
        return $this->Latitud;
    }

    function getLongitud() {
        return $this->Longitud;
    }

    function getMinutosDefase() {
        return $this->MinutosDefase;
    }

    function setTitulo($Titulo) {
        $this->Titulo = $Titulo;
    }

    function setPathImagen($PathImagen) {
        $this->PathImagen = $PathImagen;
    }

    function setNombreImagen($NombreImagen) {
        $this->NombreImagen = $NombreImagen;
    }

    function setLatitud($Latitud) {
        $this->Latitud = $Latitud;
    }

    function setLongitud($Longitud) {
        $this->Longitud = $Longitud;
    }

    function setMinutosDefase($MinutosDefase) {
        $this->MinutosDefase = $MinutosDefase;
    }
    
    function getIdTipoViatico() {
        return $this->IdTipoViatico;
    }

    function setIdTipoViatico($IdTipoViatico) {
        $this->IdTipoViatico = $IdTipoViatico;
    }
    
    function getPrioridad() {
        return $this->Prioridad;
    }

    function getCodigo() {
        return $this->Codigo;
    }

    function getIdTecnicoAsignado() {
        return $this->IdTecnicoAsignado;
    }

    function getProgreso() {
        return $this->Progreso;
    }

    function getHorasTrabajadas() {
        return $this->HorasTrabajadas;
    }

    function getFechaInicio() {
        return $this->FechaInicio;
    }

    function getFechaFin() {
        return $this->FechaFin;
    }

    function getDescripcion() {
        return $this->Descripcion;
    }

    function setPrioridad($Prioridad) {
        $this->Prioridad = $Prioridad;
    }

    function setCodigo($Codigo) {
        $this->Codigo = $Codigo;
    }

    function setIdTecnicoAsignado($IdTecnicoAsignado) {
        $this->IdTecnicoAsignado = $IdTecnicoAsignado;
    }

    function setProgreso($Progreso) {
        $this->Progreso = $Progreso;
    }

    function setHorasTrabajadas($HorasTrabajadas) {
        $this->HorasTrabajadas = $HorasTrabajadas;
    }

    function setFechaInicio($FechaInicio) {
        $this->FechaInicio = $FechaInicio;
    }

    function setFechaFin($FechaFin) {
        $this->FechaFin = $FechaFin;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }



}

?>