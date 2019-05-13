<?php

include_once("Catalogo.class.php");

/**
 * Description of Pedido
 *
 * @author samsung
 */
class PrecioABC {

    private $Id_precio_abc;
    private $Precio_A;
    private $Precio_B;
    private $Precio_C;
    private $NoParteEquipo;
    private $NoParteComponente;
    private $IdAlmacen;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;
    private $precio;

    public function newcompoabc() {
        if(!isset($this->IdAlmacen) || empty($this->IdAlmacen)){
            $this->IdAlmacen = 'NULL';
        }
        
        if(!isset($this->NoParteEquipo) || empty($this->NoParteEquipo)){
            $this->NoParteEquipo = "NULL";
        }else{
            $this->NoParteEquipo = "'".$this->NoParteEquipo."'";
        }
        
        if(!isset($this->NoParteComponente) || empty($this->NoParteComponente)){
            $this->NoParteComponente = "NULL";
        }else{
            $this->NoParteComponente = "'".$this->NoParteComponente."'";
        }
        
        $consulta = ("INSERT INTO c_precios_abc(Precio_A,Precio_B,Precio_C,NoParteEquipo,NoParteComponente,IdAlmacen,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(" . $this->Precio_A . "," . $this->Precio_B . "," . $this->Precio_C . ",$this->NoParteEquipo,$this->NoParteComponente,$this->IdAlmacen,"
                . "'" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "');");        
        $catalogo = new Catalogo();
        $this->Id_precio_abc = $catalogo->insertarRegistro($consulta);
        if($this->Id_precio_abc != NULL && $this->Id_precio_abc != 0){
            return true;
        }
        return false;
    }

    public function editarabc() {
        if(!isset($this->IdAlmacen) || empty($this->IdAlmacen)){
            $this->IdAlmacen = 'NULL';
        }
        $consulta = ("UPDATE c_precios_abc SET Precio_A=" . $this->Precio_A . ",Precio_B=" . $this->Precio_B . ",Precio_C=" . $this->Precio_C . ","
                . "IdAlmacen = $this->IdAlmacen,"
                . "UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Pantalla='" . $this->Pantalla . "'
            WHERE c_precios_abc.Id_precio_abc='" . $this->Id_precio_abc . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query >= 1) {
            return true;
        }
        return false;
    }

    public function getRegistroById($id) {
        $catalogo = new Catalogo();
        $consulta = "SELECT * FROM `c_precios_abc` WHERE Id_precio_abc = $id;";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $this->Id_precio_abc = $rs['Id_precio_abc'];
            $this->Precio_A = $rs['Precio_A'];
            $this->Precio_B = $rs['Precio_B'];
            $this->Precio_C = $rs['Precio_C'];
            $this->NoParteEquipo = $rs['NoParteEquipo'];
            $this->NoParteComponente = $rs['NoParteComponente'];
            $this->IdAlmacen = $rs['IdAlmacen'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }        
        return false;
    }

    public function getRegistroByComponenteAndTipo($noParteComponente,$tipo) {
        $catalogo = new Catalogo();
        if(isset($this->empresa))
        {
            $catalogo->setEmpresa($this->empresa);
        }
        $tipo = strtoupper($tipo);
        $consulta = "SELECT * FROM `c_precios_abc` 
            WHERE NoParteComponente = '$noParteComponente' AND !ISNULL(Precio_$tipo);";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $this->Id_precio_abc = $rs['Id_precio_abc'];
            $this->Precio_A = $rs['Precio_A'];
            $this->Precio_B = $rs['Precio_B'];
            $this->Precio_C = $rs['Precio_C'];
            $this->NoParteEquipo = $rs['NoParteEquipo'];
            $this->NoParteComponente = $rs['NoParteComponente'];
            $this->IdAlmacen = $rs['IdAlmacen'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->precio = $rs['Precio_'.$tipo];
            return true;
        }        
        return false;
    }
    
    function getId_precio_abc() {
        return $this->Id_precio_abc;
    }

    function getPrecio_A() {
        return $this->Precio_A;
    }

    function getPrecio_B() {
        return $this->Precio_B;
    }

    function getPrecio_C() {
        return $this->Precio_C;
    }

    function getNoParteEquipo() {
        return $this->NoParteEquipo;
    }

    function getNoParteComponente() {
        return $this->NoParteComponente;
    }

    function getIdAlmacen() {
        return $this->IdAlmacen;
    }

    function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    function getPantalla() {
        return $this->Pantalla;
    }

    function setId_precio_abc($Id_precio_abc) {
        $this->Id_precio_abc = $Id_precio_abc;
    }

    function setPrecio_A($Precio_A) {
        $this->Precio_A = $Precio_A;
    }

    function setPrecio_B($Precio_B) {
        $this->Precio_B = $Precio_B;
    }

    function setPrecio_C($Precio_C) {
        $this->Precio_C = $Precio_C;
    }

    function setNoParteEquipo($NoParteEquipo) {
        $this->NoParteEquipo = $NoParteEquipo;
    }

    function setNoParteComponente($NoParteComponente) {
        $this->NoParteComponente = $NoParteComponente;
    }

    function setIdAlmacen($IdAlmacen) {
        $this->IdAlmacen = $IdAlmacen;
    }

    function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }
    
    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    
    function getPrecio() {
        return $this->precio;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }
}

?>
