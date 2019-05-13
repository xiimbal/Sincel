<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");
include_once("Configuracion.class.php");
include_once("Almacen.class.php");

class AlmacenEquipo {

    private $id;
    private $idAlmacen;
    private $noSerie;
    private $noParteEquipo;
    private $Ubicacion;
    private $Apartado;
    private $ClaveCentroCosto;
    private $fechaIngreso;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $modelo;
    private $cliente;
    private $empresa;
    private $comentario;
    private $idOrden;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM k_almacenequipo WHERE NoSerie='" . $id . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idAlmacen = $rs['id_almacen'];
            $this->noSerie = $rs['NoSerie'];
            $this->noParteEquipo = $rs['NoParte'];
            $this->fechaIngreso = $rs['Fecha_ingreso'];
            $this->Ubicacion = $rs['Ubicacion'];
            $this->Apartado = $rs['Apartado'];
            $this->ClaveCentroCosto = $rs['ClaveCentroCosto'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $consulta = ("SELECT * FROM c_inventarioequipo WHERE NoSerie='" . $this->noSerie . "' AND !ISNULL(IdAnexoClienteCC)");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {
            $consulta = ("SELECT * FROM k_almacenequipo WHERE NoSerie='" . $this->noSerie . "'");
            $verificar = $catalogo->obtenerLista($consulta);
            if (mysql_num_rows($verificar) == 0) {
                $consulta = ("INSERT INTO k_almacenequipo(id_almacen,NoSerie,NoParte,Fecha_ingreso,Ubicacion,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $this->idAlmacen . "','" . $this->noSerie . "','" . $this->noParteEquipo . "','" . $this->fechaIngreso . "','$this->Ubicacion','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
                $query = $catalogo->obtenerLista($consulta);
                if ($query == 1) {
                    return "1"; //REGISTRADO
                }return "2"; //NO SE REGISTRO
            } else {
                return "3"; //EXISTE EN ALMACEN EQUIPO
            }
        } else {
            return "4"; //eXISTE EN C_INVENTARIOEQUIPO
        }
    }

    public function newEquipoBitacora() {
        $consulta = ("SELECT * FROM c_bitacora WHERE  NoSerie='" . $this->noSerie . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {
            $consulta = ("INSERT INTO c_bitacora(id_bitacora,NoSerie,NoParte,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->noSerie . "','" . $this->noParteEquipo . "',1,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return "1";
            }return "2";
        } else {
            return "3";
        }
    }

    public function newMovimientoEquipo() {
        $consulta = ("INSERT INTO movimientos_equipo(id_movimientos,NoSerie,pendiente,almacen_nuevo,tipo_movimiento,Fecha,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->noSerie . "',0,'" . $this->idAlmacen . "','5',now(),'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("SELECT * FROM c_inventarioequipo WHERE NoSerie='" . $this->noSerie . "' AND !ISNULL(IdAnexoClienteCC)");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {
            $consulta = ("UPDATE k_almacenequipo SET id_almacen = '" . $this->idAlmacen . "',Ubicacion = '$this->Ubicacion', NoParte='" . $this->noParteEquipo . "',NoSerie='" . $this->noSerie . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
                                     WHERE NoSerie='" . $this->id . "';");
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return "1";
            }
            return "2";
        } else {
            return "4";
        }
    }

    public function getModeloInventario($idSerie) {
        $consulta = ("SELECT *  FROM c_equipo e,k_almacenequipo ae WHERE e.NoParte=ae.NoParte AND ae.NoSerie='" . $idSerie . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->modelo = $rs['Modelo'];
        }
        return true;
    }

    public function obtenerSerieBitacora() {
        $consulta = ("SELECT * FROM c_bitacora WHERE  NoSerie='" . $this->noSerie . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $verificar = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($verificar) == 0) {
            return true;
        } else
            return false;
    }

    public function editBitacora() {
        $consulta = ("UPDATE c_bitacora SET NoParte='" . $this->noParteEquipo . "',NoSerie='" . $this->noSerie . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
        WHERE NoSerie='" . $this->id . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return "1";
        }
        return "2";
    }

    function deleteRegistro() {
        $consulta = ("DELETE FROM k_almacenequipo WHERE NoSerie = '" . $this->noSerie . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function deleteBitacora() {
        $consulta = ("DELETE FROM c_bitacora WHERE NoSerie = '" . $this->noSerie . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newRegistroCompra() {
        $comen = "'$this->comentario'";
        if ($this->comentario == "") {
            $comen = "NULL";
        }
        $consulta = ("SELECT * FROM k_almacenequipo ae WHERE ae.NoSerie='$this->noSerie' AND ae.NoParte='$this->noParteEquipo'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) == 0) {
            $configuracion = new Configuracion();
            /* Verificamos que la serie coincida con el prefijo del modelo */
            if (!$configuracion->validarSerie($this->noSerie, $this->noParteEquipo)) {
                return false;
            }
            $consulta = ("INSERT INTO k_almacenequipo(id_almacen,NoSerie,NoParte,Fecha_ingreso,Ubicacion,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                VALUES('" . $this->idAlmacen . "','" . $this->noSerie . "','" . $this->noParteEquipo . "',now(),'$this->Ubicacion','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
            $queryAlmacenEquip = $catalogo->obtenerLista($consulta);
            if ($queryAlmacenEquip == 1) {
                if(!isset($this->idOrden) || $this->idOrden == ""){
                    $this->idOrden = "NULL";
                }
                $consulta = ("INSERT INTO movimientos_equipo(id_movimientos,NoSerie,pendiente,almacen_nuevo,tipo_movimiento,Fecha,comentario,
                        UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Id_compra)
                    VALUES(0,'" . $this->noSerie . "',0,'" . $this->idAlmacen . "','5',now(),$comen,'" . $this->usuarioCreacion . "',now(),
                        '" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "',$this->idOrden)");
                $queryMov = $catalogo->obtenerLista($consulta);
                if ($queryMov == 1) {
                    return true;
                }
                return false;
            } else {
                return false;
            }
        } else {
            $almacen = new Almacen();
            while ($rs = mysql_fetch_array($query)) {
                if ($almacen->getRegistroById($rs['id_almacen'])) {
                    echo "<br/>El equipo ya estaba registrado en el almacén <b>" . $almacen->getNombre() . "</b>";
                    return false;
                }
            }
        }
        return false;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getNoSerie() {
        return $this->noSerie;
    }

    public function setNoSerie($noSerie) {
        $this->noSerie = $noSerie;
    }

    public function getNoParteEquipo() {
        return $this->noParteEquipo;
    }

    public function setNoParteEquipo($noParteEquipo) {
        $this->noParteEquipo = $noParteEquipo;
    }

    public function getFechaIngreso() {
        return $this->fechaIngreso;
    }

    public function setFechaIngreso($fechaIngreso) {
        $this->fechaIngreso = $fechaIngreso;
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

    public function getModelo() {
        return $this->modelo;
    }

    public function setModelo($modelo) {
        $this->modelo = $modelo;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function getUbicacion() {
        return $this->Ubicacion;
    }

    public function setUbicacion($Ubicacion) {
        $this->Ubicacion = $Ubicacion;
    }

    public function getApartado() {
        return $this->Apartado;
    }

    public function setApartado($Apartado) {
        $this->Apartado = $Apartado;
    }

    public function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
    }

    public function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getComentario() {
        return $this->comentario;
    }

    public function setComentario($comentario) {
        $this->comentario = $comentario;
    }

    public function getIdOrden() {
        return $this->idOrden;
    }

    public function setIdOrden($idOrden) {
        $this->idOrden = $idOrden;
    }

}
