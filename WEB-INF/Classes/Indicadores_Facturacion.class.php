<?php
include_once("CatalogoFacturacion.class.php");
include_once("DatosFacturacionEmpresa.class.php");

/**
 * Description of Indicadores_Facturacion
 *
 * @author MAGG
 */
class Indicadores_Facturacion {
    private $costoToal;
    
    /**
     * Imprime la fila del resultset recibido con su cuenta y precio
     * @param type $concepto
     * @param type $result
     */
    public function imprimirFila($concepto, $result){        
        while($rs = mysql_fetch_array($result)){            
            $this->costoToal += (float)($rs['Cuenta']);
            $this->imprimirConceptoCosto($concepto, $rs['Cuenta']);            
        }
    }
    
    /**
     * Imprime la fila con el concepto y el costo recibidos
     * @param type $concepto
     * @param type $costo
     */
    public function imprimirConceptoCosto($concepto, $costo){
        echo "<tr>";
        echo "<td>$concepto</td>";
        echo "<td style='text-align: right;'>$ ".  number_format($costo, 2)."</td>";            
        echo "</tr>";
    }
    
    /**
     * Imprime la fila con el concepto y la cantidad recibidos
     * @param type $concepto
     * @param type $cantidad
     */
    public function imprimirConceptoCantidad($concepto, $cantidad){
        echo "<tr>";
        echo "<td>$concepto</td>";
        echo "<td style='text-align: right;'> ".  number_format($cantidad, 0)."</td>";            
        echo "</tr>";
    }
    /**
     * Obtiene la suma de las facturas obtenidas por los filtros correspondientes.
     * @param type $ndc true en caso de considerar solo notas de credito
     * @param type $canceladas true en caso de considerar solo facturas canceladas
     * @param type $emisor Id del emisor
     * @param type $ejecutivo Id del ejecutivo
     * @param type $cliente Clave Cliente
     * @param type $fechaInicio
     * @param type $fechaFinal
     * @return type resultSet
     */
    public function obtenerMontoFacturas($ndc, $canceladas, $emisor, $ejecutivo, $cliente, $fechaInicio, $fechaFinal, 
            $pendienteCancelar, $noPagadas, $clienteVenta){
        $tipoFactura = "ingreso";
        if($ndc){
            $tipoFactura = "egreso";
        }
        
        $vigente = "1";
        if($canceladas){
            $vigente = "0";
        }
        
        $pendiente = "";
        if($pendienteCancelar){
            $tipoFactura = "ingreso";
            $vigente = "1";
            $pendiente = " AND f.PendienteCancelar = 1 ";
        }
        
        $np = "";
        if($noPagadas){
            $tipoFactura = "ingreso";
            $vigente = "1";
            $np = " AND f.PendienteCancelar = 0 AND (ISNULL(f.EstatusFactura) OR f.EstatusFactura <> 3) AND f.FacturaPagada = 0 ";
        }
        
        $where = " WHERE f.Serie = '' AND f.TipoComprobante = '$tipoFactura' AND f.EstadoFactura = $vigente $pendiente $np";
        if($emisor != ""){
            $datosFacturacionEmpresa = new DatosFacturacionEmpresa();
            if($datosFacturacionEmpresa->getRegistroById($emisor)){
                $where .= " AND f.RFCEmisor = '".$datosFacturacionEmpresa->getRFC()."' ";
            }else{
                $where .= " AND c.IdDatosFacturacionEmpresa = $emisor ";
            }
        }
        if($ejecutivo != ""){
            $where .= " AND c.EjecutivoCuenta = $ejecutivo ";
        }
        if($cliente != ""){
            $where .= " AND c.ClaveCliente = '$cliente' ";
        }
        if($fechaInicio != ""){
            $where .= " AND f.FechaFacturacion >= '$fechaInicio 00:00:00' ";
        }
        if($fechaFinal != ""){
            $where .= " AND f.FechaFacturacion <= '$fechaFinal 23:59:59' ";
        }
        
        if($clienteVenta === 2){
            
        }else if($clienteVenta){
            $where .= " AND f.TipoArrendamiento = 2 ";
        }else{
            $where .= " AND (f.TipoArrendamiento = 1 OR ISNULL(f.TipoArrendamiento)) ";
        }
        
        $consulta = "SELECT SUM(f.Total) AS Cuenta FROM c_factura AS f 
            LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MAX(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor) 
                $where;";
        $catalogo = new CatalogoFacturacion();   
        //echo "<br/><br/>$consulta";
        $result = $catalogo->obtenerLista($consulta);        
        return $result;
    }        
    
    public function getLecturasPendientes($ClaveCliente, $FechaInicio, $FechaFin, $Emisor, $Ejecutivo){
        
    }
    
    function __construct() {
        $this->costoToal = 0;
    }

    public function getCostoToal() {
        return $this->costoToal;
    }

    public function setCostoToal($costoToal) {
        $this->costoToal = $costoToal;
    }
}

?>
