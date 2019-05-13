<?php
include_once("Catalogo.class.php");

/**
 * Description of Indicadores
 *
 * @author MAGG
 */
class Indicadores {
    private $totalEquipos;
    private $costoToal;
    
    function __construct() {
        $this->totalEquipos = 0;
        $this->costoToal = 0;
    }
    
    /**
     * Imprime la fila del resultset recibido con su cuenta y precio
     * @param type $concepto
     * @param type $result
     */
    public function imprimirFila($concepto, $result){        
        while($rs = mysql_fetch_array($result)){
            $this->totalEquipos += (int)($rs['Cuenta']);
            $this->costoToal += (float)($rs['PrecioDolares']);
            echo "<tr>";
            echo "<td>$concepto</td>";
            echo "<td style='text-align: right;'>".  number_format($rs['Cuenta'], 0)."</td>";
            echo "<td style='text-align: right;'>$ ".  number_format($rs['PrecioDolares'], 2)."</td>";
            echo "</tr>";            
        }
    }
    
    /**
     * Imprime la fila de cuenta, costo y concepto.
     * @param type $concepto
     * @param type $cuenta
     * @param type $costo
     */
    public function imprimirFilaByCuenta($concepto, $cuenta, $costo){
        $this->totalEquipos += (int)($cuenta);
        $this->costoToal += (float)($costo);
        echo "<tr>";
        echo "<td>$concepto</td>";
        echo "<td style='text-align: right;'>".  number_format($cuenta, 0)."</td>";
        echo "<td style='text-align: right;'>$ ".  number_format($costo, 2)."</td>";   
        echo "</tr>";
    }
    
    /**
     * Obtiene los equipos en arrendamiento.
     * @return type
     */
    public function obtenerEquiposArrendamiento($emisor, $ejecutivo, $cliente){
        $where = "WHERE !ISNULL(cinv.NoSerie) ";
        $having = "";
        if($emisor != ""){
            //$where .= " AND c.IdDatosFacturacionEmpresa = $emisor ";
            if(empty($having)){
                $having = " HAVING IdDatosFacturacionEmpresa = $emisor ";
            }else{
                $having = " AND IdDatosFacturacionEmpresa = $emisor ";
            }
        }
        
        if($ejecutivo != ""){
            //$where .= " AND c.EjecutivoCuenta = $ejecutivo ";
            if(empty($having)){
                $having = " HAVING EjecutivoCuenta = $ejecutivo ";
            }else{
                $having = " AND EjecutivoCuenta = $ejecutivo ";
            }
        }
        
        if($cliente != ""){
            //$where .= " AND c.ClaveCliente = '$cliente' ";
            if(empty($having)){
                $having = " HAVING ClaveCliente = '$cliente' ";
            }else{
                $having = " AND ClaveCliente = '$cliente' ";
            }
        }
        
        $consulta = "SELECT id_bitacora, id_solicitud, 
            (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial, 
            (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.ClaveCliente ELSE c.ClaveCliente END) AS ClaveCliente, 
            (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.IdDatosFacturacionEmpresa ELSE c.IdDatosFacturacionEmpresa END) AS IdDatosFacturacionEmpresa, 
            (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.EjecutivoCuenta ELSE c.EjecutivoCuenta END) AS EjecutivoCuenta, 
            (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE cc.Nombre END) AS localidad, 
            a.nombre_almacen, b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParteCompuesta,
            e.PrecioDolares, b.VentaDirecta, cinv.Demo, b.IdTipoInventario
            FROM `c_bitacora` AS b 
            LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte 
            LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie = b.NoSerie 
            LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC 
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC 
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente 
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa 
            LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto 
            LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente 
            LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = b.NoSerie 
            LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen 
            $where 
            GROUP BY id_bitacora $having;";   
        //echo $consulta;
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);                
        $contador = array(0,0,0,0);
        $costo = array(0.0,0.0,0.0,0.0);
        while($rs = mysql_fetch_array($result)){
            if($rs['VentaDirecta'] == "1"){//Campo 1 es para venta directa
                $contador[1]++;
                $costo[1] += (float)$rs['PrecioDolares'];
            }else if($rs['Demo'] == "1"){
                $contador[2]++;
                $costo[2] += (float)$rs['PrecioDolares'];
            }else if($rs['IdTipoInventario'] == "8"){
                $contador[3]++;
                $costo[3] += (float)$rs['PrecioDolares'];
            }else{
                $contador[0]++;
                $costo[0] += (float)$rs['PrecioDolares'];
            }
        }
               
        $this->imprimirFilaByCuenta("En arrendamiento", $contador[0], $costo[0]);
        $this->imprimirFilaByCuenta("En Venta Directa", $contador[1], $costo[1]);
        $this->imprimirFilaByCuenta("En Demo", $contador[2], $costo[2]);
        $this->imprimirFilaByCuenta("En Back-up", $contador[3], $costo[3]);
    }
    
    /**
     * Obtiene el numero de equipos por cada almacen
     * @return type
     */
    public function obtenerEquiposPorCadaAlmacen(){
        $consulta = "SELECT COUNT(NoSerie) AS Cuenta, a.nombre_almacen, SUM(e.PrecioDolares) AS PrecioDolares
            FROM k_almacenequipo AS kae 
            INNER JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen 
            LEFT JOIN c_equipo AS e ON e.NoParte = kae.NoParte
            WHERE a.Activo = 1 
            GROUP BY a.id_almacen ORDER BY a.nombre_almacen;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    /**
     * Imprime el resultset que se obtiene de la funcion obtenerEquiposPorCadaAlmacen.
     */
    public function imprimirTablaEquiposPorAlmacen(){
        $result = $this->obtenerEquiposPorCadaAlmacen();
        while($rs = mysql_fetch_array($result)){
            $this->imprimirFilaByCuenta($rs['nombre_almacen'], $rs['Cuenta'], $rs['PrecioDolares']);            
        }
    }
        
    
    /**
     * Obtiene resultset con los equipos en las localidades tipotaller
     * @return type
     */
    public function obtenerEquiposEnTaller(){
        $consulta = "SELECT COUNT(*) AS Cuenta, SUM(e.PrecioDolares) AS PrecioDolares
            FROM `c_inventarioequipo` AS cinv
            LEFT JOIN c_bitacora AS b ON b.NoSerie = cinv.NoSerie
            LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
            LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto            
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente            
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato
            LEFT JOIN c_equipo AS e ON e.NoParte = cinv.NoParteEquipo
            WHERE !ISNULL(cinv.NoSerie) AND (ISNULL(b.VentaDirecta) OR b.VentaDirecta = 0) AND (cc.Taller = 1);";
            $catalogo = new Catalogo();
            $result = $catalogo->obtenerLista($consulta);
            return $result;
    }
    
    /**
     * Obtiene los equipos en backup(2), garantia (3), demo (4), prestamo (5)
     * @param type $tipoSolicitud tipo de solicitud de equipo
     * @return type
     */
    public function obtenerEquiposEnTipoSolicitud($tipoSolicitud, $emisor, $ejecutivo, $cliente){
        $where = " WHERE s.id_tiposolicitud = $tipoSolicitud ";
        if($emisor != ""){
            $where .= " AND c.IdDatosFacturacionEmpresa = $emisor ";
        }
        if($ejecutivo != ""){
            $where .= " AND c.EjecutivoCuenta = $ejecutivo ";
        }
        if($cliente != ""){
            $where .= " AND c.ClaveCliente = '$cliente' ";
        }
        $consulta = "SELECT s.id_solicitud, ks.Modelo, b.NoSerie, ks.ClaveCentroCosto AS ClaveCentroCostoSolicitud,
            (CASE WHEN ISNULL(ksg.ClaveCentroCosto) THEN c.ClaveCliente ELSE (SELECT ClaveCliente FROM c_cliente WHERE c_cliente.ClaveCliente = cc2.ClaveCliente) END) AS ClaveCliente, 	
            (CASE WHEN ISNULL(ksg.ClaveCentroCosto) THEN c.NombreRazonSocial ELSE (SELECT NombreRazonSocial FROM c_cliente WHERE c_cliente.ClaveCliente = cc2.ClaveCliente) END) AS NombreCliente, 	
            (CASE WHEN ISNULL(ksg.ClaveCentroCosto) THEN cc.Nombre ELSE cc2.Nombre END) AS CentroCostoNombre, 
            (CASE WHEN ISNULL(ksg.ClaveCentroCosto) THEN cc.ClaveCentroCosto ELSE ksg.ClaveCentroCosto END) AS ClaveCentroCosto, e.PrecioDolares
            FROM `c_solicitud` AS s
            INNER JOIN k_solicitud AS ks ON ks.id_solicitud = s.id_solicitud AND ks.tipo = 0
            INNER JOIN c_bitacora AS b ON b.id_solicitud = ks.id_solicitud AND b.ClaveCentroCosto = ks.ClaveCentroCosto AND b.NoParte = ks.Modelo 
            INNER JOIN c_inventarioequipo AS cinv ON cinv.NoSerie = b.NoSerie
            LEFT JOIN k_serviciogimgfa AS ksg ON ksg.IdKserviciogimgfa = cinv.IdKserviciogimgfa
            RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
            RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC
            LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ksg.ClaveCentroCosto
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente            
            LEFT JOIN c_anexotecnico AS cat ON cat.ClaveAnexoTecnico = kacc.ClaveAnexoTecnico
            LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato
            LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
            $where GROUP BY b.NoSerie HAVING ClaveCentroCosto = ClaveCentroCostoSolicitud;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }
    
    /**
     * Imprime la fila que se obtiene del metodo obtenerEquiposEnTipoSolicitud
     * @param type $idTipo tipos de solicitudes
     * @param type $concepto concepto de la fila
     */
    public function imprimirTablaTipoSolicitud($idTipo, $concepto, $emisor, $ejecutivo, $cliente){
        $result = $this->obtenerEquiposEnTipoSolicitud($idTipo, $emisor, $ejecutivo, $cliente);
        $cuenta = 0; $costo = 0;
        while($rs = mysql_fetch_array($result)){
            $cuenta++;
            $costo += (float)($rs['PrecioDolares']);
        }
        $this->imprimirFilaByCuenta($concepto, $cuenta, $costo);
    }
    
    public function getTotalEquipos() {
        return $this->totalEquipos;
    }

    public function setTotalEquipos($totalEquipos) {
        $this->totalEquipos = $totalEquipos;
    }

    public function getCostoToal() {
        return $this->costoToal;
    }

    public function setCostoToal($costoToal) {
        $this->costoToal = $costoToal;
    }
}

?>
