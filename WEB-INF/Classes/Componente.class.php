<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

/**
 * Description of Componente
 *
 * @author MAGG
 */
class Componente {

    private $numero;
    private $tipo;
    private $imagen;
    private $modelo;
    private $descripcion;
    private $precio;
    private $activo;
    private $usuarioCreacion;
    private $fechaCreacion;
    private $UsuarioModificacion;
    private $fechaModificacion;
    private $pantalla;
    private $parteAnterior;
    private $rendimiento;
    private $color;
    private $mensaje_error;
    private $empresa;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_componente` WHERE NoParte = '" . $id . "';");
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->numero = $id;
            $this->tipo = $rs['IdTipoComponente'];
            $this->imagen = $rs['PathImagen'];
            $this->modelo = $rs['Modelo'];
            $this->descripcion = $rs['Descripcion'];
            $this->precio = $rs['PrecioDolares'];
            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            $this->fechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioModificacion = $rs['UsuarioUltimaModificacion'];
            $this->fechaModificacion = $rs['FechaUltimaModificacion'];
            $this->pantalla = $rs['Pantalla'];
            $this->parteAnterior = $rs['NoParteAnterior'];
            $this->rendimiento = $rs['Rendimiento'];
            $this->color = $rs['IdColor'];
            return true;
        }
        return false;
    }

    public function getRegistroByParteAnterior($parteAnterior) {
        $consulta = ("SELECT * FROM `c_componente` WHERE NoParteAnterior = '$parteAnterior' ORDER BY FechaCreacion DESC LIMIT 1;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->numero = $rs['NoParte'];
            $this->tipo = $rs['IdTipoComponente'];
            $this->imagen = $rs['PathImagen'];
            $this->modelo = $rs['Modelo'];
            $this->descripcion = $rs['Descripcion'];
            $this->precio = $rs['PrecioDolares'];
            $this->activo = $rs['Activo'];
            $this->usuarioCreacion = $rs['UsuarioCreacion'];
            $this->fechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioModificacion = $rs['UsuarioUltimaModificacion'];
            $this->fechaModificacion = $rs['FechaUltimaModificacion'];
            $this->pantalla = $rs['Pantalla'];
            $this->parteAnterior = $rs['NoParteAnterior'];
            $this->rendimiento = $rs['Rendimiento'];
            $this->color = $rs['IdColor'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_componente (NoParte,IdTipoComponente,Modelo,Descripcion,PrecioDolares,Activo,UsuarioCreacion,FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,NoParteAnterior,Rendimiento,IdColor) 
			VALUES('" . $this->numero . "','" . $this->tipo . "','" . $this->modelo . "',
			'" . $this->descripcion . "','" . $this->precio . "'," . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'$this->UsuarioModificacion',now(),'$this->pantalla','$this->parteAnterior','$this->rendimiento',$this->color)");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if($this->activo == "0"){//Si se intenta poner inactivo el componente
            if($this->tieneExistenciaInventario()){
                echo "<br/>El componente $this->numero no se puede marcar como inactivo: ";
                echo $this->mensaje_error."<br/>";
                $this->activo = "1";
            }
            if($this->tieneTicketAbierto()){
                echo "<br/>El componente $this->numero no se puede marcar como inactivo: ";
                echo $this->mensaje_error."<br/>";
                $this->activo = "1";
            }
            if($this->tieneSolicitudAbierto()){
                echo "<br/>El componente $this->numero no se puede marcar como inactivo: ";
                echo $this->mensaje_error."<br/>";
                $this->activo = "1";
            }
        }
        
        $consulta = ("UPDATE c_componente SET IdTipoComponente = '$this->tipo', Modelo = '$this->modelo', Descripcion = '$this->descripcion', PrecioDolares = $this->precio,
            Activo = $this->activo,UsuarioUltimaModificacion = '$this->UsuarioModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->pantalla', NoParteAnterior = '$this->parteAnterior', Rendimiento = '$this->rendimiento',IdColor=$this->color
            WHERE NoParte = '" . $this->numero . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function tieneExistenciaInventario() {
        $consulta = "SELECT kac.NoParte, c.Modelo, c.Descripcion, kac.cantidad_existencia, kac.cantidad_apartados, a.nombre_almacen 
            FROM k_almacencomponente AS kac
            LEFT JOIN c_componente AS c ON kac.NoParte = c.NoParte
            LEFT JOIN c_almacen AS a ON a.id_almacen = kac.id_almacen
            WHERE kac.NoParte = '$this->numero';";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        
        if(mysql_num_rows($query) > 0){
            $this->mensaje_error = "Está registrado en el inventario de los almacénes: ";
            while ($rs = mysql_fetch_array($query)){
                $this->mensaje_error .= ("<br/>* ".$rs['nombre_almacen']." (".$rs['cantidad_existencia'].")");
            }
            return true;
        }else{
            return false;
        }
    }
    
    public function tieneTicketAbierto(){
        $consulta = "SELECT t.IdTicket, nr.NoParteComponente, nr.Cantidad FROM c_ticket AS t
            LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
            LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
            LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
            WHERE nr.NoParteComponente = '$this->numero' AND t.EstadoDeTicket NOT IN(2,4) AND lastnt.IdEstatusAtencion NOT IN(16,59)
            GROUP BY t.IdTicket
            ORDER BY t.IdTicket;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($query) > 0){
            $this->mensaje_error = "Tiene tickets abiertos: ";
            while ($rs = mysql_fetch_array($query)){
                $this->mensaje_error .= ("<br/>* ".$rs['IdTicket']." (".$rs['Cantidad'].")");
            }
            return true;
        }else{
            return false;
        }
    }

    public function tieneSolicitudAbierto(){
        $consulta = "SELECT s.id_solicitud, ks.Modelo, ks.cantidad_autorizada 
            FROM c_solicitud AS s
            LEFT JOIN k_solicitud AS ks ON ks.id_solicitud = s.id_solicitud
            WHERE ks.Modelo = '$this->numero' AND s.estatus NOT IN(4,5,3)
            GROUP BY s.id_solicitud
            ORDER BY s.id_solicitud;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($query) > 0){
            $this->mensaje_error = "Tiene solicitudes de equipo abiertas: ";
            while ($rs = mysql_fetch_array($query)){
                $this->mensaje_error .= ("<br/>* ".$rs['id_solicitud']." (".$rs['cantidad_autorizada'].")");
            }
            return true;
        }else{
            return false;
        }
    }

    
    public function editImage() {
        $consulta = ("UPDATE c_componente SET PathImagen = '$this->imagen' WHERE NoParte = '" . $this->numero . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_componente WHERE NoParte = '" . $this->numero . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function nuevoComponenteInicial($NoParteComponentePadre) {
        $consulta = ("INSERT INTO k_componentecomponenteinicial(NoParteComponentePadre,NoParteComponente,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES('$NoParteComponentePadre','$this->numero','" . $this->usuarioCreacion . "',now(),'$this->UsuarioModificacion',now(),'$this->pantalla');");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteComponenteInicial($idPadre, $id) {
        $consulta = ("DELETE FROM k_componentecomponenteinicial WHERE 
            NoParteComponentePadre = '" . $idPadre . "' AND NoParteComponente = '$id';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function nuevoComponenteNecesario($NoParteComponentePadre) {
        $consulta = ("INSERT INTO k_componentecomponentenecesario(NoParteComponentePadre,NoParteComponente,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES('$NoParteComponentePadre','$this->numero','" . $this->usuarioCreacion . "',now(),'$this->UsuarioModificacion',now(),'$this->pantalla');");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteComponenteNecesario($idPadre, $id) {
        $consulta = ("DELETE FROM k_componentecomponentenecesario WHERE 
            NoParteComponentePadre = '" . $idPadre . "' AND NoParteComponente = '$id';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
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

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function getFechaModificacion() {
        return $this->fechaModificacion;
    }

    public function setFechaModificacion($fechaModificacion) {
        $this->fechaModificacion = $fechaModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getParteAnterior() {
        return $this->parteAnterior;
    }

    public function setParteAnterior($parteAnterior) {
        $this->parteAnterior = $parteAnterior;
    }

    public function getRendimiento() {
        return $this->rendimiento;
    }

    public function setRendimiento($rendimiento) {
        $this->rendimiento = $rendimiento;
    }

    public function getColor() {
        return $this->color;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function getMensaje_error() {
        return $this->mensaje_error;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}

?>
