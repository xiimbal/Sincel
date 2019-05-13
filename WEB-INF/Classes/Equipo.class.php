<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Equipo {

    private $noParte;
    private $imagen;
    private $modelo;
    private $descripcion;
    private $IdTipoEquipo;
    private $precio;
    private $meses;
    private $impresiones;
    private $mantenimientoA;
    private $mantenimientoB;
    private $veocidad;
    private $ciclo;
    private $resolucion;
    private $alimentacion;
    private $conectividadRed;
    private $capacidadMemoria;
    private $capacidadDuplex;
    private $toner;
    private $rendimientoToner;
    private $pld;
    private $pesoPapel;
    private $especificacionTecnica;
    private $giaOperacionesAvanzadas;
    private $listaPartes;
    private $operacion;
    private $manualServicion;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $incluyeToner;
    private $Prefijo;
    private $longitudSerie;
    private $empresa;    
    private $procesador;
    private $RAM;
    private $HD;
    private $sistemaOperativo;
    private $resolucionPantalla;
    private $tamanoPulgadas;
    private $HDMI;
    private $DVD;
    private $USB;
    private $WIFI;
    private $idiomaSO;

    /**
     * Obtiene los registros de equipo que contenga el modelo o el numero de serie especificados
     * @param type $modelo modelo
     * @param type $NumSerie Numero de serie
     * @return boolean True en caso de encontrar registro, false en caso contrario
     */
    public function getRegistroValidacion($modelo, $NumSerie) {
        $consulta = ("SELECT ci.NoSerie, e.Modelo, ci.Ubicacion FROM `c_inventarioequipo` AS ci INNER JOIN c_equipo AS e ON (ci.NoSerie LIKE '%$NumSerie%' AND ci.NoParteEquipo = e.NoParte);");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_equipo WHERE NoParte='" . $id . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['NoParte'];
            $this->imagen = $rs['PathImagen'];
            $this->modelo = $rs['Modelo'];
            $this->descripcion = $rs['Descripcion'];
            $this->IdTipoEquipo = $rs['IdTipoEquipo'];
            $this->precio = $rs['PrecioDolares'];
            $this->meses = $rs['PeriodoGarantiaMeses'];
            $this->impresiones = $rs['PeriodoGarantiaImpresiones'];

            $this->veocidad = $rs['VelocidadPaginasMinuto'];
            $this->ciclo = $rs['CicloMaximoMensual'];
            $this->resolucion = $rs['Resolucion'];
            $this->capacidadDuplex = $rs['CapacidadDuplex'];
            $this->pld = $rs['PDL'];
            $this->capacidadMemoria = $rs['CapacidadMemoria'];
            $this->pesoPapel = $rs['PesoMaximoPapel'];

            $this->listaPartes = $rs['PathListaPartes'];
            $this->giaOperacionesAvanzadas = $rs['PathGuiaOperacionAvanza'];
            $this->especificacionTecnica = $rs['PathEspecificacionesTecnicas'];
            $this->operacion = $rs['PathOperacion'];
            $this->manualServicion = $rs['PathManualServicio'];
            $this->activo = $rs['Activo'];
            $this->incluyeToner = $rs['IncluyeToner'];
            $this->Prefijo = $rs['Prefijo'];
            $this->longitudSerie = $rs['Longitud_serie'];
            
            $this->procesador = $rs['Procesador'];
            $this->RAM = $rs['RAM'];
            $this->HD = $rs['HD'];
            $this->sistemaOperativo = $rs['SistemaOperativo'];
            $this->resolucionPantalla = $rs['ResolucionPantalla'];
            $this->tamanoPulgadas = $rs['TamanoPulgadas'];
            $this->HDMI = $rs['HDMI'];
            $this->USB = $rs['USB'];
            $this->DVD = $rs['DVD'];
            $this->WIFI = $rs['WIFI'];
            $this->idiomaSO = $rs['IdiomaSO'];
            return true;
        }
        return false;
    }

    public function getRegistroByModelo($modelo) {
        $consulta = ("SELECT * FROM c_equipo WHERE Modelo='" . $modelo . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->noParte = $rs['NoParte'];
            $this->imagen = $rs['PathImagen'];
            $this->modelo = $rs['Modelo'];
            $this->descripcion = $rs['Descripcion'];
            $this->IdTipoEquipo = $rs['IdTipoEquipo'];
            $this->precio = $rs['PrecioDolares'];
            $this->meses = $rs['PeriodoGarantiaMeses'];
            $this->impresiones = $rs['PeriodoGarantiaImpresiones'];

            $this->veocidad = $rs['VelocidadPaginasMinuto'];
            $this->ciclo = $rs['CicloMaximoMensual'];
            $this->resolucion = $rs['Resolucion'];
            $this->capacidadDuplex = $rs['CapacidadDuplex'];
            $this->pld = $rs['PDL'];
            $this->capacidadMemoria = $rs['CapacidadMemoria'];
            $this->pesoPapel = $rs['PesoMaximoPapel'];

            $this->listaPartes = $rs['PathListaPartes'];
            $this->giaOperacionesAvanzadas = $rs['PathGuiaOperacionAvanza'];
            $this->especificacionTecnica = $rs['PathEspecificacionesTecnicas'];
            $this->operacion = $rs['PathOperacion'];
            $this->manualServicion = $rs['PathManualServicio'];
            $this->activo = $rs['Activo'];
            $this->incluyeToner = $rs['IncluyeToner'];
            $this->Prefijo = $rs['Prefijo'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        if(!isset($this->longitudSerie) || $this->longitudSerie == ""){
            $this->longitudSerie = "NULL";
        }
        if(!isset($this->HDMI)){
            $this->HDMI = 0;
        }
        if(!isset($this->DVD)){
            $this->DVD = 0;
        }
        if(!isset($this->USB)){
            $this->USB = 0;
        }
        if(!isset($this->WIFI)){
            $this->WIFI = 0;
        }
        if(!isset($this->IdTipoEquipo) || empty($this->IdTipoEquipo)){
            $this->IdTipoEquipo = 1;
        }
        $consulta = "INSERT INTO c_equipo (NoParte,PathImagen,Modelo,Descripcion,IdTipoEquipo,PrecioDolares,PeriodoGarantiaMeses,PeriodoGarantiaImpresiones,
            Activo,CapacidadDuplex,IncluyeToner,PDL,VelocidadPaginasMinuto,CicloMaximoMensual,Resolucion,CapacidadMemoria,PesoMaximoPapel,Prefijo,
            UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Longitud_serie, Procesador, RAM, HD, SistemaOperativo,
            ResolucionPantalla, TamanoPulgadas, HDMI, DVD, USB, WIFI) 
            VALUES('" . $this->noParte . "','" . $this->imagen . "','" . $this->modelo . "',
            '" . $this->descripcion . "',$this->IdTipoEquipo,'" . $this->precio . "','" . $this->meses . "','" . $this->impresiones . "',
            " . $this->activo . ",'" . $this->capacidadDuplex . "'," . $this->incluyeToner . ",'" . $this->pld . "','" . $this->veocidad . "',
            '" . $this->ciclo . "','" . $this->resolucion . "','" . $this->capacidadMemoria . "','" . $this->pesoPapel . "','$this->Prefijo',
            '" . $this->usuarioCreacion . "',now(),'$this->usuarioModificacion',now(),'$this->pantalla',$this->longitudSerie,"
                . "'$this->procesador', '$this->RAM', '$this->HD', '$this->sistemaOperativo', '$this->resolucionPantalla', '$this->tamanoPulgadas',"
                . "$this->HDMI, $this->DVD, $this->USB, $this->WIFI)";
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

    public function editRegistro() {
        if(!isset($this->longitudSerie) || $this->longitudSerie == ""){
            $this->longitudSerie = "NULL";
        }
        if(!isset($this->IdTipoEquipo) || empty($this->IdTipoEquipo)){
            $this->IdTipoEquipo = 1;
        }
        
        $consulta = "UPDATE c_equipo SET Modelo = '$this->modelo', Descripcion = '" . $this->descripcion . "',IdTipoEquipo=$this->IdTipoEquipo,
            PrecioDolares='" . $this->precio . "',PeriodoGarantiaMeses='" . $this->meses . "',PeriodoGarantiaImpresiones='" . $this->impresiones . "',
            Activo = $this->activo,CapacidadDuplex='" . $this->capacidadDuplex . "',IncluyeToner=" . $this->incluyeToner . ",PDL='" . $this->pld . "',
            VelocidadPaginasMinuto='" . $this->veocidad . "',CicloMaximoMensual='" . $this->ciclo . "',Resolucion='" . $this->resolucion . "',
            CapacidadMemoria='" . $this->capacidadMemoria . "',PesoMaximoPapel='" . $this->pesoPapel . "',Prefijo = '$this->Prefijo',Longitud_serie = $this->longitudSerie,
            Procesador = '$this->procesador', RAM = '$this->RAM', HD = '$this->HD', SistemaOperativo = '$this->sistemaOperativo', ResolucionPantalla = '$this->resolucionPantalla',
            TamanoPulgadas = '$this->tamanoPulgadas', HDMI = $this->HDMI, DVD = $this->DVD, USB = $this->USB, WIFI = $this->WIFI, IdiomaSO = '$this->idiomaSO',
            UsuarioUltimaModificacion = '$this->usuarioModificacion',FechaUltimaModificacion = now(), Pantalla = '$this->pantalla' WHERE NoParte = '" . $this->noParte . "';";
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

    public function ComprobarExistencia() {
        $consulta = ("SELECT * FROM c_equipo e WHERE e.NoParte='" . $this->noParte . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return FALSE;
        }
        return TRUE;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_equipo WHERE NoParte = '" . $this->noParte . "';");
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

    public function editUrlImg($tabla, $campo, $url, $id, $idMod) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        if ($url != "") {
            $query = $catalogo->obtenerLista("UPDATE " . $tabla . " SET $campo = '" . $url . "'
            WHERE " . $id . " = '" . $idMod . "';");
            if ($query == 1) {
                return true;
            }
            return false;
        }
    }

    public function actualizarUbicacion($ubicacion) {
        $consulta = ("INSERT INTO c_inventarioequipo(NoSerie, NoParteEquipo,Ubicacion,Activo,UsuarioCreacion,
            FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES('$this->noParte','$this->noParte','$ubicacion','" . $this->activo . "',
            '" . $this->usuarioCreacion . "',now(),'$this->usuarioModificacion',now(),'$this->pantalla');");
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
    
    public function agregarEquipoSimilar($NoParteSimilar){
        $consulta = "SELECT * FROM `k_equiposimilar` WHERE NoParte = '$this->noParte' AND NoParteEquipoSimilar = '$NoParteSimilar';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($query) == 0){
            $consulta = "INSERT INTO k_equiposimilar(NoParte, NoParteEquipoSimilar, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, 
                FechaUltimaModificacion, Pantalla) 
                VALUES('$this->noParte','$NoParteSimilar','$this->usuarioCreacion',NOW(),'$this->usuarioModificacion',NOW(),'$this->pantalla');";        
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }else{
            echo "Error: el NoParte $NoParteSimilar ya es similar a ".$this->noParte;
            return false;
        }
    }
    
    public function actualizarEquipoSimilar($NoParteSimilarAnterior, $NoParteSimilarNuevo){
        $consulta = "SELECT * FROM `k_equiposimilar` WHERE NoParte = '$this->noParte' AND NoParteEquipoSimilar = '$NoParteSimilarNuevo';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($query) == 0){
            $consulta = "UPDATE k_equiposimilar SET NoParteEquipoSimilar = '$NoParteSimilarNuevo', UsuarioUltimaModificacion = '$this->usuarioModificacion',
                FechaUltimaModificacion = NOW(), Pantalla = '$this->pantalla' WHERE NoParte = '$this->noParte' AND NoParteEquipoSimilar = '$NoParteSimilarAnterior';";        
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }else{
            echo "Error: el NoParte $NoParteSimilarNuevo ya es similar a ".$this->noParte.", no se hizo la actualización";
            return false;
        }
    }
    
    public function deleteEquipoSimiliar($NoParteSimilar){
        $consulta = ("DELETE FROM `k_equiposimilar` WHERE NoParte = '$this->noParte' AND NoParteEquipoSimilar = '$NoParteSimilar';");
        
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
    
    public function getEquiposSimilares(){
        $consulta = "SELECT kes.NoParteEquipoSimilar, e.Modelo FROM k_equiposimilar AS kes 
            LEFT JOIN c_equipo AS e ON e.NoParte = kes.NoParteEquipoSimilar 
            WHERE kes.NoParte = '$this->noParte' AND e.Activo = 1 ORDER BY Modelo;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function getImagen() {
        return $this->imagen;
    }

    public function setImagen($imagen) {
        $this->imagen = $imagen;
    }

    public function getModelo() {
        return $this->modelo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function setPrecio($precio) {
        $this->precio = $precio;
    }

    public function getMeses() {
        return $this->meses;
    }

    public function setMeses($meses) {
        $this->meses = $meses;
    }

    public function getImpresiones() {
        return $this->impresiones;
    }

    public function setImpresiones($impresiones) {
        $this->impresiones = $impresiones;
    }

    public function getMantenimientoA() {
        return $this->mantenimientoA;
    }

    public function setMantenimientoA($mantenimientoA) {
        $this->mantenimientoA = $mantenimientoA;
    }

    public function getMantenimientoB() {
        return $this->mantenimientoB;
    }

    public function setMantenimientoB($mantenimientoB) {
        $this->mantenimientoB = $mantenimientoB;
    }

    public function getVeocidad() {
        return $this->veocidad;
    }

    public function setVeocidad($veocidad) {
        $this->veocidad = $veocidad;
    }

    public function getCiclo() {
        return $this->ciclo;
    }

    public function setCiclo($ciclo) {
        $this->ciclo = $ciclo;
    }

    public function getResolucion() {
        return $this->resolucion;
    }

    public function setResolucion($resolucion) {
        $this->resolucion = $resolucion;
    }

    public function getAlimentacion() {
        return $this->alimentacion;
    }

    public function setAlimentacion($alimentacion) {
        $this->alimentacion = $alimentacion;
    }

    public function getConectividadRed() {
        return $this->conectividadRed;
    }

    public function setConectividadRed($conectividadRed) {
        $this->conectividadRed = $conectividadRed;
    }

    public function getCapacidadMemoria() {
        return $this->capacidadMemoria;
    }

    public function setCapacidadMemoria($capacidadMemoria) {
        $this->capacidadMemoria = $capacidadMemoria;
    }

    public function getCapacidadDuplex() {
        return $this->capacidadDuplex;
    }

    public function setCapacidadDuplex($capacidadDuplex) {
        $this->capacidadDuplex = $capacidadDuplex;
    }

    public function getToner() {
        return $this->toner;
    }

    public function setToner($toner) {
        $this->toner = $toner;
    }

    public function getRendimientoToner() {
        return $this->rendimientoToner;
    }

    public function setRendimientoToner($rendimientoToner) {
        $this->rendimientoToner = $rendimientoToner;
    }

    public function getPld() {
        return $this->pld;
    }

    public function setPld($pld) {
        $this->pld = $pld;
    }

    public function getPesoPapel() {
        return $this->pesoPapel;
    }

    public function setPesoPapel($pesoPapel) {
        $this->pesoPapel = $pesoPapel;
    }

    public function getEspecificacionTecnica() {
        return $this->especificacionTecnica;
    }

    public function setEspecificacionTecnica($especificacionTecnica) {
        $this->especificacionTecnica = $especificacionTecnica;
    }

    public function getGiaOperacionesAvanzadas() {
        return $this->giaOperacionesAvanzadas;
    }

    public function setGiaOperacionesAvanzadas($giaOperacionesAvanzadas) {
        $this->giaOperacionesAvanzadas = $giaOperacionesAvanzadas;
    }

    public function getListaPartes() {
        return $this->listaPartes;
    }

    public function setListaPartes($listaPartes) {
        $this->listaPartes = $listaPartes;
    }

    public function getOperacion() {
        return $this->operacion;
    }

    public function setOperacion($operacion) {
        $this->operacion = $operacion;
    }

    public function getManualServicion() {
        return $this->manualServicion;
    }

    public function setManualServicion($manualServicion) {
        $this->manualServicion = $manualServicion;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
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

    public function getIncluyeToner() {
        return $this->incluyeToner;
    }

    public function setIncluyeToner($incluyeToner) {
        $this->incluyeToner = $incluyeToner;
    }

    public function getPrefijo() {
        return $this->Prefijo;
    }

    public function setPrefijo($Prefijo) {
        $this->Prefijo = $Prefijo;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getLongitudSerie() {
        return $this->longitudSerie;
    }

    public function setLongitudSerie($longitudSerie) {
        $this->longitudSerie = $longitudSerie;
    }

    function getProcesador() {
        return $this->procesador;
    }

    function getRAM() {
        return $this->RAM;
    }

    function getHD() {
        return $this->HD;
    }

    function getSistemaOperativo() {
        return $this->sistemaOperativo;
    }

    function getResolucionPantalla() {
        return $this->resolucionPantalla;
    }

    function getTamanoPulgadas() {
        return $this->tamanoPulgadas;
    }

    function getHDMI() {
        return $this->HDMI;
    }

    function getDVD() {
        return $this->DVD;
    }

    function getUSB() {
        return $this->USB;
    }

    function getWIFI() {
        return $this->WIFI;
    }

    function getIdiomaSO() {
        return $this->idiomaSO;
    }

    function setProcesador($procesador) {
        $this->procesador = $procesador;
    }

    function setRAM($RAM) {
        $this->RAM = $RAM;
    }

    function setHD($HD) {
        $this->HD = $HD;
    }

    function setSistemaOperativo($sistemaOperativo) {
        $this->sistemaOperativo = $sistemaOperativo;
    }

    function setResolucionPantalla($resolucionPantalla) {
        $this->resolucionPantalla = $resolucionPantalla;
    }

    function setTamanoPulgadas($tamanoPulgadas) {
        $this->tamanoPulgadas = $tamanoPulgadas;
    }

    function setHDMI($HDMI) {
        $this->HDMI = $HDMI;
    }

    function setDVD($DVD) {
        $this->DVD = $DVD;
    }

    function setUSB($USB) {
        $this->USB = $USB;
    }

    function setWIFI($WIFI) {
        $this->WIFI = $WIFI;
    }

    function setIdiomaSO($idiomaSO) {
        $this->idiomaSO = $idiomaSO;
    }

    function getIdTipoEquipo() {
        return $this->IdTipoEquipo;
    }

    function setIdTipoEquipo($IdTipoEquipo) {
        $this->IdTipoEquipo = $IdTipoEquipo;
    }
}

?>
