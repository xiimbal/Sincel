<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of Pedido
 *
 * @author samsung
 */
class Pedido {

    private $IdPedido;
    private $IdTicket;
    private $ClaveEspEquipo;
    private $TonerNegro;
    private $TonerCian;
    private $TonerMagenta;
    private $TonerAmarillo;
    private $IdLecturaTicket;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $Estado;
    private $Modelo;
    private $ubicacion;
    private $color;
    private $arrayPedido = array();
    private $arrayNoSerie = array();
    private $arrayNegro = array();
    private $arrayCian = array();
    private $arrayMagenta = array();
    private $arrayAmarillo = array();
    private $empresa;

    public function getPedidoByIdTicket($id) {
        $consulta = ("SELECT p.ClaveEspEquipo,p.TonerNegro,p.TonerCian,p.TonerMagenta,p.TonerAmarillo FROM c_pedido p WHERE p.IdTicket=$id");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->arrayNoSerie[$contador] = $rs['ClaveEspEquipo'];
            $this->arrayNegro[$contador] = $rs['TonerNegro'];
            $this->arrayCian[$contador] = $rs['TonerCian'];
            $this->arrayMagenta[$contador] = $rs['TonerMagenta'];
            $this->arrayAmarillo[$contador] = $rs['TonerAmarillo'];
            $contador++;
        }
    }

    public function getClaveByIdTicket($id) {
        $consulta = ("SELECT ClaveEspEquipo FROM `c_pedido` WHERE IdTicket = $id;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $resultado = "";
        while ($rs = mysql_fetch_array($query)) {
            $resultado = $rs['ClaveEspEquipo'] . " -- " . $resultado;
        }
        $resultado = substr($resultado, 0, strlen($resultado) - 4);
        return $resultado;
    }

    public function newRegistro() {
        if(isset($this->ClaveEspEquipo) && !empty($this->ClaveEspEquipo)){
            $serie = "'$this->ClaveEspEquipo'";
        }else{
            $serie = "NULL";
        }
        
        $consulta = "INSERT INTO c_pedido(IdPedido,IdTicket,ClaveEspEquipo,TonerNegro,TonerCian,TonerMagenta,TonerAmarillo,IdLecturaTicket,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Estado,Modelo)
            VALUES(0,'" . $this->IdTicket . "',$serie,'" . $this->TonerNegro . "','" . $this->TonerCian . "','" . $this->TonerMagenta . "','" . $this->TonerAmarillo . "','" . $this->IdLecturaTicket . "'," . $this->Activo . ",'" . $this->UsuarioCreacion . "',now(),'" . $this->UsuarioUltimaModificacion . "',now(),'" . $this->Pantalla . "','" . $this->Estado . "','" . $this->Modelo . "')";
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
        $consulta = ("UPDATE c_pedido SET ClaveEspEquipo = '" . $this->ClaveEspEquipo . "',TonerNegro = '" . $this->TonerNegro . "',TonerCian = '" . $this->TonerCian . "',TonerMagenta = '" . $this->TonerMagenta . "',TonerAmarillo = '" . $this->TonerAmarillo . "', Activo = " . $this->Activo . ",UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->Pantalla . "' WHERE IdPedido='" . $this->IdPedido . "';");
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

    public function deleteRegitro() {
        $consulta = ("DELETE FROM c_pedido WHERE IdTicket = '" . $this->IdTicket . "';");
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

    public function getPedidoToner() {
        $consulta = "SELECT p.IdPedido,e.Modelo,cc.Nombre,p.ClaveEspEquipo,p.TonerNegro,p.TonerCian,p.TonerMagenta,p.TonerAmarillo,p.IdLecturaTicket,
            p.Estado,e.Modelo,
            (SELECT (SELECT CASE WHEN ef.IdTipoServicio =3  THEN 'No' ELSE 'Si' END) FROM k_equipocaracteristicaformatoservicio ef WHERE ef.NoParte=e.NoParte AND ef.IdTipoServicio <>2 ORDER BY ef.IdTipoServicio DESC LIMIT 1) AS serivicio
            FROM c_pedido p LEFT JOIN c_inventarioequipo ie ON p.ClaveEspEquipo=ie.NoSerie
            LEFT JOIN c_equipo e ON ie.NoParteEquipo=e.NoParte 
            LEFT JOIN k_anexoclientecc an ON an.IdAnexoClienteCC=ie.IdAnexoClienteCC 
            LEFT JOIN c_centrocosto cc ON cc.ClaveCentroCosto=an.CveEspClienteCC
            WHERE p.IdTicket='$this->IdTicket' ORDER BY p.FechaCreacion ASC";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->arrayPedido[$contador] = $this->IdPedido = $rs['IdPedido'] . "/****/" .
                    $this->Modelo = $rs['Modelo'] . "/****/" .
                    $this->ubicacion = $rs['Nombre'] . "/****/" .
                    $this->ClaveEspEquipo = $rs['ClaveEspEquipo'] . "/****/" .
                    $this->TonerNegro = $rs['TonerNegro'] . "/****/" .
                    $this->TonerNegro = $rs['TonerCian'] . "/****/" .
                    $this->TonerMagenta = $rs['TonerMagenta'] . "/****/" .
                    $this->TonerAmarillo = $rs['TonerAmarillo'] . "/****/" .
                    $this->IdLecturaTicket = $rs['IdLecturaTicket'] . "/****/" .
                    $this->Estado = $rs['Estado'] . "/****/" .
                    $this->color = $rs['serivicio'];
            $contador++;
        }
        return $this->arrayPedido;
    }

    public function getIdPedido() {
        return $this->IdPedido;
    }

    public function setIdPedido($IdPedido) {
        $this->IdPedido = $IdPedido;
    }

    public function getIdTicket() {
        return $this->IdTicket;
    }

    public function setIdTicket($IdTicket) {
        $this->IdTicket = $IdTicket;
    }

    public function getClaveEspEquipo() {
        return $this->ClaveEspEquipo;
    }

    public function setClaveEspEquipo($ClaveEspEquipo) {
        $this->ClaveEspEquipo = $ClaveEspEquipo;
    }

    public function getTonerNegro() {
        return $this->TonerNegro;
    }

    public function setTonerNegro($TonerNegro) {
        $this->TonerNegro = $TonerNegro;
    }

    public function getTonerCian() {
        return $this->TonerCian;
    }

    public function setTonerCian($TonerCian) {
        $this->TonerCian = $TonerCian;
    }

    public function getTonerMagenta() {
        return $this->TonerMagenta;
    }

    public function setTonerMagenta($TonerMagenta) {
        $this->TonerMagenta = $TonerMagenta;
    }

    public function getTonerAmarillo() {
        return $this->TonerAmarillo;
    }

    public function setTonerAmarillo($TonerAmarillo) {
        $this->TonerAmarillo = $TonerAmarillo;
    }

    public function getIdLecturaTicket() {
        return $this->IdLecturaTicket;
    }

    public function setIdLecturaTicket($IdLecturaTicket) {
        $this->IdLecturaTicket = $IdLecturaTicket;
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

    public function getEstado() {
        return $this->Estado;
    }

    public function setEstado($Estado) {
        $this->Estado = $Estado;
    }

    public function getModelo() {
        return $this->Modelo;
    }

    public function setModelo($Modelo) {
        $this->Modelo = $Modelo;
    }

    public function getUbicacion() {
        return $this->ubicacion;
    }

    public function setUbicacion($ubicacion) {
        $this->ubicacion = $ubicacion;
    }

    public function getColor() {
        return $this->color;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function getArrayPedido() {
        return $this->arrayPedido;
    }

    public function setArrayPedido($arrayPedido) {
        $this->arrayPedido = $arrayPedido;
    }

    public function getArrayNoSerie() {
        return $this->arrayNoSerie;
    }

    public function getArrayNegro() {
        return $this->arrayNegro;
    }

    public function getArrayCian() {
        return $this->arrayCian;
    }

    public function getArrayMagenta() {
        return $this->arrayMagenta;
    }

    public function getArrayAmarillo() {
        return $this->arrayAmarillo;
    }

    public function setArrayNoSerie($arrayNoSerie) {
        $this->arrayNoSerie = $arrayNoSerie;
    }

    public function setArrayNegro($arrayNegro) {
        $this->arrayNegro = $arrayNegro;
    }

    public function setArrayCian($arrayCian) {
        $this->arrayCian = $arrayCian;
    }

    public function setArrayMagenta($arrayMagenta) {
        $this->arrayMagenta = $arrayMagenta;
    }

    public function setArrayAmarillo($arrayAmarillo) {
        $this->arrayAmarillo = $arrayAmarillo;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
