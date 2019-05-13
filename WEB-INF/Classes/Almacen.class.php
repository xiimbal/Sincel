<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class Almacen {

    private $idAlmacen;
    private $nombre;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $tipoAlmacen;
    private $Prioridad;
    private $Surtir;
    private $localidades = array();
    private $cliente;
    private $grupoCliente;
    private $clienteGrupo;
    private $empresa;
    private $ArrayCliente = array();

    public function getRegistroById($id) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM c_almacen WHERE id_almacen='" . $id . "'");
        if ($rs = mysql_fetch_array($query)) {
            $this->idAlmacen = $rs['id_almacen'];
            $this->nombre = $rs['nombre_almacen'];
            $this->activo = $rs['Activo'];
            $this->tipoAlmacen = $rs['TipoAlmacen'];
            $this->clienteGrupo = $rs['Cliente'];
            $this->Prioridad = $rs['Prioridad'];
            $this->Surtir = $rs['Surtir'];
            return true;
        }
        return false;
    }

    public function getRegistroByNombre($nombre) {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("SELECT * FROM c_almacen WHERE nombre_almacen='" . $nombre . "'");
        if ($rs = mysql_fetch_array($query)) {
            $this->idAlmacen = $rs['id_almacen'];
            $this->nombre = $rs['nombre_almacen'];
            $this->activo = $rs['Activo'];
            $this->tipoAlmacen = $rs['TipoAlmacen'];
            $this->clienteGrupo = $rs['Cliente'];
            $this->Prioridad = $rs['Prioridad'];
            $this->Surtir = $rs['Surtir'];
            return true;
        }
        return false;
    }

    public function getRegistrByLocalidad($claveCentroCosto) {
        $catalogo = new Catalogo();
        $consulta = "SELECT a.*
            FROM k_minialmacenlocalidad AS al
            LEFT JOIN c_almacen AS a ON a.id_almacen = al.IdAlmacen
            WHERE al.ClaveCentroCosto = '$claveCentroCosto';";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $this->idAlmacen = $rs['id_almacen'];
            $this->nombre = $rs['nombre_almacen'];
            $this->activo = $rs['Activo'];
            $this->tipoAlmacen = $rs['TipoAlmacen'];
            $this->clienteGrupo = $rs['Cliente'];
            $this->Prioridad = $rs['Prioridad'];
            return true;
        }
        return false;
    }

    public function getRegistroMiniAlmacenById($id) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT cc.ClaveCliente ,GROUP_CONCAT(RTRIM(ma.ClaveCentroCosto) SEPARATOR ' // ') AS centroCosto FROM k_minialmacenlocalidad ma,c_centrocosto cc WHERE ma.IdAlmacen='$id' AND cc.ClaveCentroCosto=ma.ClaveCentroCosto GROUP BY cc.ClaveCliente");
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->ArrayCliente[$contador] = $rs['ClaveCliente'];
            $contador++;
        }
    }

    public function getRegistroMiniAlmacenByIdLocalidad($id) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT RTRIM(ma.ClaveCentroCosto) AS centroCosto, cl.ClaveCliente, cl.NombreRazonSocial, cc.Nombre
                                        FROM k_minialmacenlocalidad ma,c_centrocosto cc, c_cliente cl
                                        WHERE ma.IdAlmacen=$id AND cc.ClaveCentroCosto=ma.ClaveCentroCosto AND cl.ClaveCliente = cc.ClaveCliente;");
        $contador = 0;
        if ($rs = mysql_fetch_array($query)) {
            $this->localidades[$contador] = $rs['centroCosto'];
            $this->nombre = $rs['NombreRazonSocial'];
            $this->cliente = $rs['ClaveCliente'];
            $this->clienteGrupo = $rs['Nombre'];
            $contador++;
        }
    }

    /**
     * Asocia un cliente con el almacen en la tabla k_almacen cliente
     * @param type $ClaveCliente Clave del cliente a asociar.
     * @return boolean true en caso de haber insertado el registro correctamente, false en caso contrario.
     */
    public function newAlmacenCliente($ClaveCliente) {
        $consulta = "INSERT INTO k_almacencliente(id_almacen, ClaveCliente, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, 
            FechaUltimaModificacion, Pantalla) VALUES($this->idAlmacen,'$ClaveCliente', '$this->usuarioCreacion', NOW(), '$this->usuarioModificacion', 
            NOW(), '$this->pantalla');";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newRegistro() {
        if (!isset($this->Prioridad) || empty($this->Prioridad)) {
            $this->Prioridad = "1";
        }

        if ($this->cliente == "") {
            $consulta = "INSERT INTO c_almacen(nombre_almacen,Activo,TipoAlmacen,Prioridad,Surtir,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->nombre . "'," . $this->activo . ",'" . $this->tipoAlmacen . "',$this->Prioridad,$this->Surtir,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')";
        } else {
            $consulta = "INSERT INTO c_almacen(nombre_almacen,Activo,TipoAlmacen,Prioridad,Surtir,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Cliente)
            VALUES('" . $this->nombre . "'," . $this->activo . ",'" . $this->tipoAlmacen . "',$this->Prioridad,$this->Surtir,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "','" . $this->cliente . "')";
        }

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->idAlmacen = $catalogo->insertarRegistro($consulta);
        if ($this->idAlmacen != NULL && $this->idAlmacen != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        if (!isset($this->Prioridad) || empty($this->Prioridad)) {
            $this->Prioridad = "1";
        }
        $catalogo = new Catalogo();
        if ($this->cliente == "") {
            $query = $catalogo->obtenerLista("UPDATE c_almacen SET Surtir = $this->Surtir, nombre_almacen = '" . $this->nombre . "', Prioridad = $this->Prioridad, Activo = " . $this->activo . ",TipoAlmacen=" . $this->tipoAlmacen . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "',Cliente=null WHERE id_almacen='" . $this->idAlmacen . "';");
        } else {
            $query = $catalogo->obtenerLista("UPDATE c_almacen SET Surtir = $this->Surtir, nombre_almacen = '" . $this->nombre . "', Prioridad = $this->Prioridad, Activo = " . $this->activo . ",TipoAlmacen=" . $this->tipoAlmacen . ",UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "',Cliente='" . $this->cliente . "' WHERE id_almacen='" . $this->idAlmacen . "';");
        }
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM c_almacen WHERE id_almacen = '" . $this->idAlmacen . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteMiniAlmacen() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM k_minialmacenlocalidad WHERE IdAlmacen ='" . $this->idAlmacen . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistroMinialmacenLocalidad() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM k_minialmacenlocalidad WHERE IdAlmacen = '" . $this->idAlmacen . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function newRegistroAlmacenLocalidad($centroCosto) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("INSERT INTO k_minialmacenlocalidad(IdAlmacen,ClaveCentroCosto,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('" . $this->idAlmacen . "','" . $centroCosto . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function VerifyClienteGroup() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT * FROM c_cliente c WHERE c.ClaveCliente='" . $this->cliente . "'");
        if ($rs = mysql_fetch_array($query)) {
            $this->grupoCliente = $rs['ClaveGrupo'];
            return true;
        }
        return false;
    }

    public function insertMiniLocalidadGrupo() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("INSERT INTO k_minialmacenlocalidad (IdAlmacen,ClaveCentroCosto,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                SELECT '" . $this->idAlmacen . "',cc.ClaveCentroCosto,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'  FROM c_centrocosto cc WHERE cc.ClaveCliente IN (SELECT c.ClaveCliente FROM c_cliente c WHERE c.ClaveGrupo='$this->grupoCliente')");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function insertSinGrupo() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("INSERT INTO k_minialmacenlocalidad (IdAlmacen,ClaveCentroCosto,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                                 SELECT '$this->idAlmacen',cc.ClaveCentroCosto,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "'  FROM c_centrocosto cc WHERE cc.ClaveCliente='" . $this->cliente . "'");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editClientesAlmacen() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("UPDATE c_almacen SET Cliente = '" . $this->cliente . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE id_almacen='" . $this->idAlmacen . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editNombreAlmacen() {
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista("UPDATE c_almacen SET nombre_almacen = '" . $this->nombre . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE id_almacen='" . $this->idAlmacen . "';");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene los ids de los almacenes de los cuales es responsable el usuario especificado.
     * @param type $idUsuario id del Usuario
     * @return string id de los almacenes separados por coma
     */
    public function getAlmacenResponsable($idUsuario) {
        $consulta = "SELECT GROUP_CONCAT(CAST(IdAlmacen AS CHAR) SEPARATOR ',') AS almacenes, IdUsuario FROM `k_responsablealmacen` 
            WHERE IdUsuario = $idUsuario GROUP BY IdUsuario;";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $almacenes = $rs['almacenes'];
            return $almacenes;
        }
        return "";
    }

    public function verificarAlmacenParaSurtirByAlmacen($id) {
        $consulta = "SELECT * FROM c_almacen WHERE id_almacen = $id AND Surtir = 1";
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return true;
        }
        return false;
    }

    public function reporteResurtidoAlmacen($fechaInicio, $fechaFin, $Proveedor, $Tipos, $NoParte, $Agrupar) {
        $where = "";
        $where_es = "";
        $group_by = "GROUP BY c.NoParte";

        if ($Agrupar) {
            if (!empty($NoParte)) {
                $where .= " AND TRIM(c.Modelo) = (SELECT TRIM(Modelo) FROM c_componente WHERE NoParte = '$NoParte') ";
            }
            $group_by = "GROUP BY TRIM(c.Modelo)";
            $where_es .= "AND TRIM(c1.Modelo) = TRIM(c.Modelo)";
        } else {
            $where_es .= "AND NoParteComponente = c.NoParte";
            if (!empty($NoParte)) {
                $where .= " AND c.NoParte = '$NoParte' ";
            }
        }

        if (!empty($fechaInicio)) {
            $where_es .= " AND Fecha >= '$fechaInicio 00:00:00' ";
        }
        if (!empty($fechaFin)) {
            $where_es .= " AND Fecha <= '$fechaFin 23:59:59' ";
        }
        if (!empty($Proveedor)) {
            $where .= " AND p.ClaveProveedor = '$Proveedor' ";
        }
        if (!empty($Tipos)) {
            $where .= " AND c.IdTipoComponente IN(" . implode(",", $Tipos) . ") ";
        }


        $consulta = "SELECT a.nombre_almacen AS Almacen, c.Modelo, c.NoParte , p.NombreComercial AS Proveedor, c.Descripcion, 
            SUM(kac.cantidad_existencia) AS Existencia,kac.CantidadMinima, kac.CantidadMaxima, tc.Nombre As TipoComponente,
            (CASE WHEN kac.CantidadMaxima > kac.cantidad_existencia THEN kac.CantidadMaxima - kac.cantidad_existencia ELSE 0 END) AS Cantidad_Propuesta_Compra,
            (SELECT SUM(CantidadMovimiento) FROM movimiento_componente AS mc
            LEFT JOIN c_componente AS c1 ON c1.NoParte = mc.NoParteComponente
            WHERE IdAlmacenAnterior = a.id_almacen AND Entradada_Salida = 1 $where_es) AS Salidas,
            (SELECT SUM(CantidadMovimiento) FROM movimiento_componente  AS mc
            LEFT JOIN c_componente AS c1 ON c1.NoParte = mc.NoParteComponente
            WHERE IdAlmacenNuevo = a.id_almacen AND Entradada_Salida = 0 $where_es) AS Entradas
            FROM k_almacencomponente AS kac
            LEFT JOIN c_componente AS c ON kac.NoParte = c.NoParte
            LEFT JOIN c_almacen AS a ON a.id_almacen = kac.id_almacen
            LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = c.NoParte AND CantidadEntregada > 0)
            LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
            LEFT JOIN c_proveedor AS p ON p.ClaveProveedor = coc.FacturaEmisor
            LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
            WHERE kac.id_almacen = $this->idAlmacen $where
            $group_by
            HAVING (!ISNULL(Salidas) AND Salidas > 0) OR (!ISNULL(Entradas) AND Entradas > 0)
            ORDER BY TipoComponente, c.Modelo, c.NoParte;";
        //echo $consulta;
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function reporteResurtidoAlmacenDatos($fechaInicio, $fechaFin, $Proveedor, $Tipos, $NoParte, $Agrupar, $cabeceras) {
        $where = "";
        $where_es = "";
        $group_by = "GROUP BY c.NoParte";

        if ($Agrupar) {
            if (!empty($NoParte)) {
                $where .= " AND TRIM(c.Modelo) = (SELECT TRIM(Modelo) FROM c_componente WHERE NoParte = '$NoParte') ";
            }
            $group_by = "GROUP BY TRIM(c.Modelo)";
//            $where_es .= "AND TRIM(c1.Modelo) = TRIM(c.Modelo)";
        } else {
            $where_es .= "AND NoParteComponente = c.NoParte";
            if (!empty($NoParte)) {
                $where .= " AND c.NoParte = '$NoParte' ";
            }
        }

        if (!empty($fechaInicio)) {
            $where_es .= " AND Fecha >= '$fechaInicio 00:00:00' ";
        }
        if (!empty($fechaFin)) {
            $where_es .= " AND Fecha <= '$fechaFin 23:59:59' ";
        }
        if (!empty($Proveedor)) {
            $where .= " AND p.ClaveProveedor = '$Proveedor' ";
        }
        if (!empty($Tipos)) {
            $where .= " AND c.IdTipoComponente IN(" . implode(",", $Tipos) . ") ";
        }


        $consulta = "SELECT a.nombre_almacen AS Almacen, TRIM(c.Modelo) AS Modelo, c.NoParte , p.NombreComercial AS Proveedor, c.Descripcion, 
            SUM(kac.cantidad_existencia) AS Existencia,kac.CantidadMinima, kac.CantidadMaxima, tc.Nombre As TipoComponente,
            (CASE WHEN kac.CantidadMaxima > kac.cantidad_existencia THEN kac.CantidadMaxima - kac.cantidad_existencia ELSE 0 END) AS Cantidad_Propuesta_Compra,
            '' AS Salidas, '' AS Entradas
            FROM k_almacencomponente AS kac
            LEFT JOIN c_componente AS c ON kac.NoParte = c.NoParte
            LEFT JOIN c_almacen AS a ON a.id_almacen = kac.id_almacen
            LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = c.NoParte AND CantidadEntregada > 0)
            LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
            LEFT JOIN c_proveedor AS p ON p.ClaveProveedor = coc.FacturaEmisor
            LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
            WHERE kac.id_almacen = $this->idAlmacen $where
            $group_by
            ORDER BY TipoComponente, c.Modelo, c.NoParte;";
//        echo $consulta;
        $catalogo = new Catalogo();
        $array = array();
        $modelos = "";
        $result = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($result)) {
            $modelos .= "'" . trim($rs['Modelo']) . "',";
            foreach ($rs AS $con => $value) {
                foreach ($cabeceras as $key => $value) {
                    $array[trim($rs['Modelo'])][$key] = $rs[$key];
                }
            }
        }
        $consulta = "SELECT SUM(CantidadMovimiento) AS Suma, TRIM(c1.Modelo) AS Modeloo, 0 AS Tipo  FROM movimiento_componente AS mc
            LEFT JOIN c_componente AS c1 ON c1.NoParte = mc.NoParteComponente
            WHERE IdAlmacenNuevo = $this->idAlmacen AND Entradada_Salida = 0 AND TRIM(c1.Modelo) IN(" . trim($modelos, ",") . ") $where_es GROUP BY Modeloo
             UNION SELECT SUM(CantidadMovimiento) AS Suma, TRIM(c1.Modelo) AS Modeloo, 1 AS Tipo FROM movimiento_componente AS mc
            LEFT JOIN c_componente AS c1 ON c1.NoParte = mc.NoParteComponente
            WHERE IdAlmacenAnterior = $this->idAlmacen AND Entradada_Salida = 1 AND TRIM(c1.Modelo) IN(" . trim($modelos, ",") . ") $where_es GROUP BY Modeloo;";
//        echo $consulta;
        $result = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($result) > 0) {
            while ($rs = mysql_fetch_array($result)) {
                if (isset($array[trim($rs['Modeloo'])])) {
                    if ($rs['Tipo'] == 0) {
                        $array[trim($rs['Modeloo'])]['Entradas'] = $rs['Suma'];
                    } else {
                        $array[trim($rs['Modeloo'])]['Salidas'] = $rs['Suma'];
                    }
                }
            }
        }
        return $array;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
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

    public function getTipoAlmacen() {
        return $this->tipoAlmacen;
    }

    public function setTipoAlmacen($tipoAlmacen) {
        $this->tipoAlmacen = $tipoAlmacen;
    }

    public function getLocalidades() {
        return $this->localidades;
    }

    public function setLocalidades($localidades) {
        $this->localidades = $localidades;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function getGrupoCliente() {
        return $this->grupoCliente;
    }

    public function setGrupoCliente($grupoCliente) {
        $this->grupoCliente = $grupoCliente;
    }

    public function getClienteGrupo() {
        return $this->clienteGrupo;
    }

    public function setClienteGrupo($clienteGrupo) {
        $this->clienteGrupo = $clienteGrupo;
    }

    public function getArrayCliente() {
        return $this->ArrayCliente;
    }

    public function setArrayCliente($ArrayCliente) {
        $this->ArrayCliente = $ArrayCliente;
    }

    public function getPrioridad() {
        return $this->Prioridad;
    }

    public function setPrioridad($Prioridad) {
        $this->Prioridad = $Prioridad;
    }

    function getSurtir() {
        return $this->Surtir;
    }

    function setSurtir($Surtir) {
        $this->Surtir = $Surtir;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
