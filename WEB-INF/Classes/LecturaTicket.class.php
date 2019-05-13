<?php

include_once("Catalogo.class.php");

class LecturaTicket {

    private $idLectura;
    private $claveEspEquipo;
    private $contadorBN;
    private $contadorColor;
    private $nivelNegro;
    private $nivelCia;
    private $nivelMagenta;
    private $nivelAmarillo;
    private $modeloEquipo;
    private $idTicket;
    private $fecha;
    private $Activo;
    private $UsuarioCreacion;
    private $UsuarioUltimaModificacion;
    private $Pantalla;
    private $fechaA;
    private $contadorBNA;
    private $contadorColorA;
    private $nivelNegroA;
    private $nivelCiaA;
    private $nivelMagentaA;
    private $nivelAmarilloA;
    private $noSerie;
    private $Comentario;
    private $empresa;

    public function NewRegistro() {
        if ($this->contadorBN != "") {
            $cbn = $this->contadorBN;
        } else {
            $cbn = "null";
        }

        if ($this->contadorColor != "") {
            $nc = $this->contadorColor;
        } else {
            $nc = "null";
        }
        if ($this->nivelNegro != "") {
            $nn = $this->nivelNegro;
        } else {
            $nn = "null";
        }
        if ($this->nivelCia != "")
            $nci = $this->nivelCia;
        else
            $nci = "null";
        if ($this->nivelMagenta != "")
            $nm = $this->nivelMagenta;
        else
            $nm = "null";
        if ($this->nivelAmarillo != "")
            $na = $this->nivelAmarillo;
        else
            $na = "null";

        if ($this->contadorBNA != "")
            $cbnA = $this->contadorBNA;
        else
            $cbnA = "null";
        if ($this->contadorColorA != "")
            $ccA = $this->contadorColorA;
        else
            $ccA = "null";
        if ($this->nivelNegroA != "")
            $nna = $this->nivelNegroA;
        else
            $nna = "null";
        if ($this->nivelCiaA != "")
            $nciA = $this->nivelCiaA;
        else
            $nciA = "null";
        if ($this->nivelMagentaA != "")
            $nmA = $this->nivelMagentaA;
        else
            $nmA = "null";
        if ($this->nivelAmarilloA != "")
            $nAA = $this->nivelAmarilloA;
        else
            $nAA = "null";
        if ($this->fechaA != "") {  
            $consulta = "INSERT INTO c_lecturasticket (id_lecturaticket, ClvEsp_Equipo, ContadorBN, ContadorCL, NivelTonNegro, 
                    NivelTonCian, NivelTonMagenta, NivelTonAmarillo, ModeloEquipo, fk_idticket, Fecha,FechaA,ContadorBNA,ContadorCLA,NivelTonNegroA,NivelTonCianA,NivelTonMagentaA,NivelTonAmarilloA,
                    Activo,UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Comentario) 
                    VALUES(0, '$this->claveEspEquipo',$cbn,$nc,$nn,
                   $nci, $nm, $na, '$this->modeloEquipo', '$this->idTicket', now(), 
                    '$this->fechaA',$cbnA ,$ccA,$nna,$nciA,$nmA, $nAA,
                    $this->Activo, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla','$this->Comentario' );";
        } else {
            $consulta = "INSERT INTO c_lecturasticket (id_lecturaticket, ClvEsp_Equipo, ContadorBN, ContadorCL, NivelTonNegro, 
                    NivelTonCian, NivelTonMagenta, NivelTonAmarillo, ModeloEquipo, fk_idticket, Fecha,FechaA,ContadorBNA,ContadorCLA,NivelTonNegroA,NivelTonCianA,NivelTonMagentaA,NivelTonAmarilloA,
                    Activo,UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Comentario) 
                    VALUES(0, '$this->claveEspEquipo',$cbn,$nc,$nn,
                   $nci, $nm, $na, '$this->modeloEquipo', '$this->idTicket', now(),NULL,
                    $cbnA ,$ccA,$nna,$nciA,$nmA, $nAA,
                    $this->Activo, '$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla','$this->Comentario' );";
        }
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->idLectura = $catalogo->insertarRegistro($consulta);
        if ($this->idLectura != NULL && $this->idLectura != 0) {
            return true;
        }
        return false;
    }

    public function getLecturaByTicket($idTicket){
        $consultaNiveles = "SELECT * FROM c_lecturasticket WHERE fk_idticket = $idTicket;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consultaNiveles);
        while ($rs = mysql_fetch_array($query)) {
            $this->idLectura = $rs['id_lecturaticket'];
            $this->fechaA = $rs['Fecha'];
            $this->contadorBNA = $rs['ContadorBN'];
            $this->contadorColorA = $rs['ContadorCL'];
            $this->nivelNegroA = $rs['NivelTonNegro'];
            $this->nivelCiaA = $rs['NivelTonCian'];
            $this->nivelMagentaA = $rs['NivelTonMagenta'];
            $this->nivelAmarilloA = $rs['NivelTonAmarillo'];
            return true;
        }
        return false;
    }
    
    public function getLecturaBYNoSerieAndTicket($idTicket) {
        $consultaNiveles = "SELECT *, 
            (SELECT MAX(Fecha) FROM c_lecturasticket INNER JOIN c_ticket t ON t.idTicket = fk_idticket WHERE ClvEsp_Equipo = lt.ClvEsp_Equipo AND fk_idticket < lt.fk_idticket AND ContadorBN = lt.ContadorBNA AND t.TipoReporte = t2.TipoReporte) AS FechaAnterior
            FROM c_lecturasticket AS lt 
            INNER JOIN c_ticket t2 ON lt.fk_idticket = t2.idTicket
            WHERE lt.ClvEsp_Equipo = '$this->noSerie' AND lt.fk_idticket = $idTicket;";        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consultaNiveles);
        while ($rs = mysql_fetch_array($query)) {                        
            $this->fecha = $rs['Fecha'];
            $this->contadorBN = $rs['ContadorBN'];
            $this->contadorColor = $rs['ContadorCL'];
            $this->nivelNegro = $rs['NivelTonNegro'];
            $this->nivelCia = $rs['NivelTonCian'];
            $this->nivelMagenta = $rs['NivelTonMagenta'];
            $this->nivelAmarillo = $rs['NivelTonAmarillo'];
            $this->fechaA = $rs['FechaAnterior'];
            $this->contadorBNA = $rs['ContadorBNA'];
            $this->contadorColorA = $rs['ContadorCLA'];
            $this->nivelNegroA = $rs['NivelTonNegroA'];
            $this->nivelCiaA = $rs['NivelTonCianA'];
            $this->nivelMagentaA = $rs['NivelTonMagentaA'];
            $this->nivelAmarilloA = $rs['NivelTonAmarilloA'];
            return true;
        }
        return false;
    }

    public function getLecturaBYNoSerie() {
        $consultaNiveles = "SELECT 
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.Fecha ELSE lt.Fecha END) AS Fecha,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNPaginas ELSE lt.ContadorBN END) AS ContadorBN,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorPaginas ELSE lt.ContadorCL END)AS ContadorCL,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNML ELSE lt.ContadorBNA END) AS ContadorBNML,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorML ELSE lt.ContadorCLA END) AS ContadorCLML,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonNegro ELSE lt.NivelTonNegro END) AS NivelTonNegro,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonCian ELSE lt.NivelTonCian END) AS NivelTonCian,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonMagenta ELSE lt.NivelTonMagenta END) AS NivelTonMagenta,
            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonAmarillo ELSE lt.NivelTonAmarillo END) AS NivelTonAmarillo
            FROM c_lecturasticket lt LEFT JOIN c_inventarioequipo ie ON lt.ClvEsp_Equipo=ie.NoSerie AND lt.Fecha=(SELECT MAX(lt2.Fecha)FROM c_lecturasticket lt2 WHERE lt2.ClvEsp_Equipo=ie.NoSerie)
            LEFT join c_lectura l ON l.NoSerie=ie.NoSerie AND l.Fecha=(SELECT MAX(l2.Fecha) FROM c_lectura l2 WHERE l2.NoSerie=ie.NoSerie)
            WHERE ie.NoSerie='$this->noSerie'";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consultaNiveles);
        while ($rs = mysql_fetch_array($query)) {
            $this->fechaA = $rs['Fecha'];
            $this->contadorBNA = $rs['ContadorBN'];
            $this->contadorColorA = $rs['ContadorCL'];
            $this->nivelNegroA = $rs['NivelTonNegro'];
            $this->nivelCiaA = $rs['NivelTonCian'];
            $this->nivelMagentaA = $rs['NivelTonMagenta'];
            $this->nivelAmarilloA = $rs['NivelTonAmarillo'];
            return true;
        }
        return false;
    }
    
    public function getLecturaTonerBNColorBYNoSerie() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $consultaNiveles = "SELECT lt.id_lecturaticket, lt.ClvEsp_Equipo, lt.Fecha, lt.ContadorBN, lt.NivelTonNegro
            FROM c_lecturasticket AS lt
            INNER JOIN c_pedido AS p ON p.IdTicket = lt.fk_idticket AND p.TonerNegro = 1
            WHERE lt.ClvEsp_Equipo = '$this->noSerie'
            ORDER BY lt.Fecha DESC LIMIT 1;";
        
        $query = $catalogo->obtenerLista($consultaNiveles);
        while ($rs = mysql_fetch_array($query)) {
            $this->fechaA = $rs['Fecha'];
            $this->contadorBNA = $rs['ContadorBN'];            
            $this->nivelNegroA = $rs['NivelTonNegro'];            
            
        }
        
        $consultaNiveles = "SELECT lt.id_lecturaticket, lt.ClvEsp_Equipo, lt.Fecha, lt.ContadorCL, lt.NivelTonAmarillo, lt.NivelTonMagenta, lt.NivelTonCian
            FROM c_lecturasticket AS lt
            INNER JOIN c_pedido AS p ON p.IdTicket = lt.fk_idticket AND (p.TonerAmarillo = 1 OR p.TonerMagenta = 1 OR p.TonerCian = 1)
            WHERE lt.ClvEsp_Equipo = '$this->noSerie'
            ORDER BY lt.Fecha DESC LIMIT 1;";
        
        $query = $catalogo->obtenerLista($consultaNiveles);
        while ($rs = mysql_fetch_array($query)) {
            $this->fechaA = $rs['Fecha'];            
            $this->contadorColorA = $rs['ContadorCL'];            
            $this->nivelCiaA = $rs['NivelTonCian'];
            $this->nivelMagentaA = $rs['NivelTonMagenta'];
            $this->nivelAmarilloA = $rs['NivelTonAmarillo'];
            
        }
        return true;
    }

    public function getLecturaTonerBySerieRS() {
        $consulta = "SELECT (CASE WHEN !ISNULL(lt.Fecha) THEN lt.Fecha ELSE t.FechaHora END) AS Fecha,lt.ContadorBN AS ContadorBN,lt.ContadorCL AS ContadorCL,lt.ContadorBNA AS ContadorBNML,
            lt.ContadorCLA AS ContadorCLML,lt.NivelTonNegro AS NivelTonNegro,lt.NivelTonCian AS NivelTonCian,lt.NivelTonMagenta AS NivelTonMagenta,
            lt.NivelTonAmarillo AS NivelTonAmarillo
            FROM c_lecturasticket lt 
            INNER JOIN c_ticket t ON t.IdTicket = 
            (SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 
            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket)
            WHERE lt.fk_idticket = t2.IdTicket AND t2.TipoReporte = 15 AND t2.EstadoDeTicket <> 4 AND (nt.IdEstatusAtencion <> 59 OR ISNULL(nt.IdEstatusAtencion)))
            WHERE lt.ClvEsp_Equipo='$this->noSerie'
            ORDER BY t.IdTicket DESC LIMIT 0,1;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getLecturaTonerByNoSerie() {
        $query = $this->getLecturaTonerBySerieRS();
        while ($rs = mysql_fetch_array($query)) {
            $this->fechaA = $rs['Fecha'];
            $this->contadorBNA = $rs['ContadorBN'];
            $this->contadorColorA = $rs['ContadorCL'];
            $this->nivelNegroA = $rs['NivelTonNegro'];
            $this->nivelCiaA = $rs['NivelTonCian'];
            $this->nivelMagentaA = $rs['NivelTonMagenta'];
            $this->nivelAmarilloA = $rs['NivelTonAmarillo'];
        }
    }

    public function deleteRegitro() {
        $consulta = ("DELETE FROM c_lecturasticket WHERE fk_idticket = '" . $this->idTicket . "';");
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
    
    public function editContadores(){
        $algo = "";
        if(isset($this->contadorColor) && $this->contadorColor!=""){
            $algo = ", ContadorCL = $this->contadorColor";
        }
        $consulta = ("UPDATE c_lecturasticket SET ContadorBN = $this->contadorBN $algo"
                . " WHERE id_lecturaticket = " . $this->idLectura . ";");
        //echo $consulta;
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
    
    public function getUltimaLecturaCorte(){
        $consulta = "SELECT * from c_lectura l 
                    WHERE l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura l2 WHERE l2.NoSerie = '$this->noSerie' AND l2.LecturaCorte = 1)";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->contadorBNA = $rs['ContadorBNPaginas'];
            $this->contadorColorA = $rs['ContadorColorPaginas'];
        }
    }
    
    public function getUltimaLecturaCambioToner(){      
        $catalogo = new Catalogo();
        
        $consulta = "SELECT lt.id_lecturaticket, lt.ClvEsp_Equipo, lt.Fecha, lt.ContadorBN, lt.NivelTonNegro
            FROM c_lecturasticket AS lt
            INNER JOIN c_pedido AS p ON p.IdTicket = lt.fk_idticket AND p.TonerNegro = 1
            WHERE lt.ClvEsp_Equipo = '$this->noSerie'
            ORDER BY lt.Fecha DESC LIMIT 1;";
        
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->contadorBNA = $rs['ContadorBN'];
        }
        
        $consultaNiveles = "SELECT lt.id_lecturaticket, lt.ClvEsp_Equipo, lt.Fecha, lt.ContadorCL, lt.NivelTonAmarillo, lt.NivelTonMagenta, lt.NivelTonCian
            FROM c_lecturasticket AS lt
            INNER JOIN c_pedido AS p ON p.IdTicket = lt.fk_idticket AND (p.TonerAmarillo = 1 OR p.TonerMagenta = 1 OR p.TonerCian = 1)
            WHERE lt.ClvEsp_Equipo = '$this->noSerie'
            ORDER BY lt.Fecha DESC LIMIT 1;";
        
        $query = $catalogo->obtenerLista($consultaNiveles);
        while ($rs = mysql_fetch_array($query)) {            
            $this->contadorColorA = $rs['ContadorCL'];
        }
    }

    public function getComentario() {
        return $this->Comentario;
    }

    public function setComentario($Comentario) {
        $this->Comentario = $Comentario;
    }

    public function getIdLectura() {
        return $this->idLectura;
    }

    public function getClaveEspEquipo() {
        return $this->claveEspEquipo;
    }

    public function getContadorBN() {
        return $this->contadorBN;
    }

    public function getContadorColor() {
        return $this->contadorColor;
    }

    public function getNivelNegro() {
        return $this->nivelNegro;
    }

    public function getNivelCia() {
        return $this->nivelCia;
    }

    public function getNivelMagenta() {
        return $this->nivelMagenta;
    }

    public function getNivelAmarillo() {
        return $this->nivelAmarillo;
    }

    public function getModeloEquipo() {
        return $this->modeloEquipo;
    }

    public function getIdTicket() {
        return $this->idTicket;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function getFechaA() {
        return $this->fechaA;
    }

    public function getContadorBNA() {
        return $this->contadorBNA;
    }

    public function getContadorColorA() {
        return $this->contadorColorA;
    }

    public function getNivelNegroA() {
        return $this->nivelNegroA;
    }

    public function getNivelCiaA() {
        return $this->nivelCiaA;
    }

    public function getNivelMagentaA() {
        return $this->nivelMagentaA;
    }

    public function getNivelAmarilloA() {
        return $this->nivelAmarilloA;
    }

    public function setIdLectura($idLectura) {
        $this->idLectura = $idLectura;
    }

    public function setClaveEspEquipo($claveEspEquipo) {
        $this->claveEspEquipo = $claveEspEquipo;
    }

    public function setContadorBN($contadorBN) {
        $this->contadorBN = $contadorBN;
    }

    public function setContadorColor($contadorColor) {
        $this->contadorColor = $contadorColor;
    }

    public function setNivelNegro($nivelNegro) {
        $this->nivelNegro = $nivelNegro;
    }

    public function setNivelCia($nivelCia) {
        $this->nivelCia = $nivelCia;
    }

    public function setNivelMagenta($nivelMagenta) {
        $this->nivelMagenta = $nivelMagenta;
    }

    public function setNivelAmarillo($nivelAmarillo) {
        $this->nivelAmarillo = $nivelAmarillo;
    }

    public function setModeloEquipo($modeloEquipo) {
        $this->modeloEquipo = $modeloEquipo;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function setFechaA($fechaA) {
        $this->fechaA = $fechaA;
    }

    public function setContadorBNA($contadorBNA) {
        $this->contadorBNA = $contadorBNA;
    }

    public function setContadorColorA($contadorColorA) {
        $this->contadorColorA = $contadorColorA;
    }

    public function setNivelNegroA($nivelNegroA) {
        $this->nivelNegroA = $nivelNegroA;
    }

    public function setNivelCiaA($nivelCiaA) {
        $this->nivelCiaA = $nivelCiaA;
    }

    public function setNivelMagentaA($nivelMagentaA) {
        $this->nivelMagentaA = $nivelMagentaA;
    }

    public function setNivelAmarilloA($nivelAmarilloA) {
        $this->nivelAmarilloA = $nivelAmarilloA;
    }

    public function getNoSerie() {
        return $this->noSerie;
    }

    public function setNoSerie($noSerie) {
        $this->noSerie = $noSerie;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
