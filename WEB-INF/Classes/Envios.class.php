<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");
/**
 * Description of Envios
 *
 * @author MAGG
 */
class Envios {

    private $IdEnvio;
    private $NoSerie;
    private $IdSolicitud;
    private $ClaveCentroCosto;
    private $IdMensajeria;
    private $NoGuia;
    private $IdVehiculo;
    private $IdConductor;
    private $IdLectura;
    private $Otros;
    private $Estatus;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function newRegistro(){
        $catalogo = new Catalogo(); 
        if(isset($this->ClaveCentroCosto) && $this->ClaveCentroCosto!=""){
            $cc = "'$this->ClaveCentroCosto'";
        }else{
            $cc = "null";
        }               
        $consulta = "INSERT INTO k_enviosmensajeria(NoSerie, IdSolicitud, ClaveCentroCosto, IdMensajeria, NoGuia, IdVehiculo, IdConductor, Otro, Estatus, 
            Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
            VALUES('$this->NoSerie',$this->IdSolicitud,$cc,$this->IdMensajeria, '$this->NoGuia' , $this->IdVehiculo, $this->IdConductor, '$this->Otros' ,$this->Estatus,
                $this->Activo, '$this->UsuarioCreacion', NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";          
        
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    /**
     * Obtiene los componentes que no se les ha asiganado mensajería de la solicitud especificada
     * @param type $idSolicitud
     * @return type result set con los componentes no enviados
     */
    public function getComponentesSinMensajeriaBySolicitud($idSolicitud){
        $consulta = "SELECT ks.id_solicitud, ks.id_partida, ket.IdEnvio 
        FROM `k_solicitud` AS ks
        LEFT JOIN k_enviotoner AS ket ON ks.Modelo = ket.NoParte AND ks.id_solicitud = ket.IdSolicitudEquipo AND ket.ClaveCentroCosto = ks.ClaveCentroCosto
        WHERE ks.id_solicitud = $idSolicitud AND ks.tipo = 1 AND ISNULL(ket.IdEnvio);";        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }
     /**
     * Si todos los componentes de la solicitud ya fueron enviados, devuelve true, o false en caso contrario
     * @param type $idSolicitud
     * @return type
     */
    public function todosComponentesEnviados($idSolicitud){        
        $enviados = true;
        $consulta = "SELECT ks.id_solicitud, ks.id_partida, ket.IdEnvio, ks.cantidad_autorizada, ks.cantidad_surtida, ks.NoSurtir,  
        (SELECT SUM(Cantidad) FROM k_enviotoner WHERE NoParte = ks.Modelo AND IdSolicitudEquipo = ks.id_solicitud AND ClaveCentroCosto = ks.ClaveCentroCosto) AS enviados
        FROM `k_solicitud` AS ks
        LEFT JOIN k_enviotoner AS ket ON ket.IdEnvio = (SELECT MAX(IdEnvio) FROM k_enviotoner WHERE NoParte = ks.Modelo AND IdSolicitudEquipo = ks.id_solicitud 
        AND ClaveCentroCosto = ks.ClaveCentroCosto)
        LEFT JOIN c_componente AS c ON c.NoParte = ks.Modelo
        WHERE ks.id_solicitud = $idSolicitud AND ks.tipo = 1 AND c.IdTipoComponente <> 7;";        
        $catalogo = new Catalogo(); 
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $num_enviados = 0;
            if(isset($rs['enviados']) && $rs['enviados']!=""){
                $num_enviados = intval($rs['enviados']);
            }
            if(intval($num_enviados) < intval($rs['cantidad_autorizada']) && $rs['NoSurtir'] == "0"){// Si los enviados son menores a los autorizados, y no esta marcado como no surtir
                $enviados = false;
            }
            if(intval($num_enviados) < intval($rs['cantidad_surtida'])){
                $enviados = false;
            }
        }        
        return $enviados;
    }
    /**
     * Obtiene los equipos que no han sido enviador por mensajeria de la solicitud especificada
     * @param type $idSolicitud
     * @return type
     */
    public function getNoSerieSinMensajeriaBySolicitud($idSolicitud){
        $consulta = "SELECT b.id_bitacora, b.id_solicitud, b.NoSerie, msj.IdSolicitud FROM `c_bitacora` AS b 
            LEFT JOIN k_enviosmensajeria AS msj ON b.NoSerie = msj.NoSerie AND b.id_solicitud = msj.IdSolicitud
            WHERE b.id_solicitud = $idSolicitud AND ISNULL(msj.IdEnvio);";        
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);        
        return $query;
    }
    
    /**
     * Si todos los equipos de la solicitud ya fueron enviados, devuelve true, o false en caso contrario
     * @param type $idSolicitud
     * @return type
     */
    public function todosEquiposEnviados($idSolicitud){
        $result = $this->getNoSerieSinMensajeriaBySolicitud($idSolicitud);        
        $resultados = mysql_num_rows($result);        
        return ($resultados > 0) ? false : true; /* Si hay resultados, entonces no han sido todos los equipos enviados y se regresa false, true en caso contrario */        
    }        
    
    public function getIdEnvio() {
        return $this->IdEnvio;
    }

    public function setIdEnvio($IdEnvio) {
        $this->IdEnvio = $IdEnvio;
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getIdSolicitud() {
        return $this->IdSolicitud;
    }

    public function setIdSolicitud($IdSolicitud) {
        $this->IdSolicitud = $IdSolicitud;
    }

    public function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
    }

    public function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
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
    
    public function getIdMensajeria() {
        return $this->IdMensajeria;
    }

    public function setIdMensajeria($IdMensajeria) {
        $this->IdMensajeria = $IdMensajeria;
    }

    public function getIdVehiculo() {
        return $this->IdVehiculo;
    }

    public function setIdVehiculo($IdVehiculo) {
        $this->IdVehiculo = $IdVehiculo;
    }

    public function getIdConductor() {
        return $this->IdConductor;
    }

    public function setIdConductor($IdConductor) {
        $this->IdConductor = $IdConductor;
    }

    public function getEstatus() {
        return $this->Estatus;
    }

    public function setEstatus($Estatus) {
        $this->Estatus = $Estatus;
    }
    
    public function getNoGuia() {
        return $this->NoGuia;
    }

    public function setNoGuia($NoGuia) {
        $this->NoGuia = $NoGuia;
    }
    
    public function getIdLectura() {
        return $this->IdLectura;
    }

    public function setIdLectura($IdLectura) {
        $this->IdLectura = $IdLectura;
    }
    
    function getOtros() {
        return $this->Otros;
    }

    function setOtros($Otros) {
        $this->Otros = $Otros;
    }
}
?>
