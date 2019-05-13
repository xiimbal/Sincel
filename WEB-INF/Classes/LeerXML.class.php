<?php

class LeerXML {

    private $xml;
    private $datos;
    private $fecha;
    private $folio;
    private $rfcEmisor;
    private $nombreEmisor;
    private $rfcReceptor;
    private $nombreReceptor;
    private $subTotal;
    private $iva;
    private $total;
    private $tipoComprobante;
    private $UUID;
    private $arrayCantidad = Array();
    private $arrayUnidad = Array();
    private $arrayDescripcion = Array();
    private $arrayValorUnitario = Array();
    private $arrayImporte = Array();

    public function getDatosXML() {
        /* $this->datos = simplexml_load_file($this->xml);
          $this->folio = $this->datos['folio'];
          $this->fecha = $this->datos['fecha'];
          $this->rfcEmisor = $this->datos->Emisor['rfc'];
          $this->rfcReceptor = $this->datos->Receptor['rfc'];
          $this->subTotal = $this->datos['subTotal'];
          $this->total = $this->datos['total'];
          $this->iva = $this->datos->Impuestos['totalImpuestosTrasladados'];
          foreach ($this->datos->Conceptos->Concepto as $concepto) {
          array_push($this->arrayCantidad, $concepto['cantidad']);
          array_push($this->arrayUnidad, $concepto['unidad']);
          array_push($this->arrayDescripcion, $concepto['descripcion']);
          array_push($this->arrayValorUnitario, $concepto['valorUnitario']);
          array_push($this->arrayImporte, $concepto['importe']);
          } */
        $reader = new XMLReader();
        if (!$reader->open($this->xml)) {
            die("Failed to open '$this->xml'");
        }
        while ($reader->read()) {            
            $node = $reader->expand();
            // process $node...
            if($node->nodeName == "cfdi:Comprobante"){                
                $this->folio = $reader->getAttribute("folio");
                $this->fecha = $reader->getAttribute("fecha");
                $this->subTotal = $reader->getAttribute('subTotal');
                $this->total = $reader->getAttribute('total');                
            }
            if($node->nodeName == "cfdi:Impuestos"){
                $this->iva = $reader->getAttribute("totalImpuestosTrasladados");
            }
            if($node->nodeName == "cfdi:Emisor"){                
                $this->rfcEmisor = $reader->getAttribute("rfc");
                
            }
            if($node->nodeName == "cfdi:Receptor"){
                $this->rfcReceptor = $reader->getAttribute("rfc");
            }
            if($node->nodeName == "cfdi:Concepto"){
                if(!in_array($reader->getAttribute("descripcion"), $this->arrayDescripcion)){
                //echo "Conceptos: ".$reader->getAttribute("descripcion");
                    array_push($this->arrayCantidad, $reader->getAttribute('cantidad'));
                    array_push($this->arrayUnidad, $reader->getAttribute('unidad'));
                    array_push($this->arrayDescripcion, $reader->getAttribute('descripcion'));
                    array_push($this->arrayValorUnitario, $reader->getAttribute('valorUnitario'));
                    array_push($this->arrayImporte, $reader->getAttribute('importe'));
                    
                }else{
                    //echo "<br/>Error: El concepto ".$reader->getAttribute("descripcion")." ya fue registrado en la misma factura. No se puede registrar más de una vez el mismo concepto";
                }
            }
            
        }
        $reader->close();
    }

    public function getDatosXMLCFDI() {
        $this->datos = simplexml_load_file($this->xml);
        $rs = $this->datos->getNamespaces(true);
        $this->datos->registerXPathNamespace('c', $rs['cfdi']);
        $this->datos->registerXPathNamespace('t', $rs['tfd']);
        $this->folio = $this->datos['folio'];
        $this->tipoComprobante = $this->datos['tipoDeComprobante'];
        $this->fecha = str_replace("T", " ", $this->datos['fecha']);
        $this->subTotal = $this->datos['subTotal'];
        $this->total = $this->datos['total'];
        $this->iva = $this->datos->Impuestos['totalImpuestosTrasladados'];
        foreach ($this->datos->xpath('//c:Emisor') as $concepto) {
            $this->rfcEmisor = $concepto['rfc'];
            $this->nombreEmisor = $concepto['nombre'];
        }
        foreach ($this->datos->xpath('//c:Receptor') as $concepto) {
            $this->rfcReceptor = $concepto['rfc'];
            $this->nombreReceptor = $concepto['nombre'];
        }
        foreach ($this->datos->xpath('//t:TimbreFiscalDigital') as $concepto) {
            $this->UUID = $concepto['UUID'];
        }
        foreach ($this->datos->xpath('//c:Concepto') as $concepto) {
            array_push($this->arrayCantidad, $concepto['cantidad']);
            array_push($this->arrayUnidad, $concepto['unidad']);
            array_push($this->arrayDescripcion, $concepto['descripcion']);
            array_push($this->arrayValorUnitario, $concepto['valorUnitario']);
            array_push($this->arrayImporte, $concepto['importe']);
        }
    }

    public function getXml() {
        return $this->xml;
    }

    public function getDatos() {
        return $this->datos;
    }

    public function getRfcEmisor() {
        return $this->rfcEmisor;
    }

    public function getRfcReceptor() {
        return $this->rfcReceptor;
    }

    public function getSubTotal() {
        return $this->subTotal;
    }

    public function getIva() {
        return $this->iva;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getArrayCantidad() {
        return $this->arrayCantidad;
    }

    public function getArrayUnidad() {
        return $this->arrayUnidad;
    }

    public function getArrayDescripcion() {
        return $this->arrayDescripcion;
    }

    public function getArrayValorUnitario() {
        return $this->arrayValorUnitario;
    }

    public function getArrayImporte() {
        return $this->arrayImporte;
    }

    public function setXml($xml) {
        $this->xml = $xml;
    }

    public function setDatos($datos) {
        $this->datos = $datos;
    }

    public function setRfcEmisor($rfcEmisor) {
        $this->rfcEmisor = $rfcEmisor;
    }

    public function setRfcReceptor($rfcReceptor) {
        $this->rfcReceptor = $rfcReceptor;
    }

    public function setSubTotal($subTotal) {
        $this->subTotal = $subTotal;
    }

    public function setIva($iva) {
        $this->iva = $iva;
    }

    public function setTotal($total) {
        $this->total = $total;
    }

    public function setArrayCantidad($arrayCantidad) {
        $this->arrayCantidad = $arrayCantidad;
    }

    public function setArrayUnidad($arrayUnidad) {
        $this->arrayUnidad = $arrayUnidad;
    }

    public function setArrayDescripcion($arrayDescripcion) {
        $this->arrayDescripcion = $arrayDescripcion;
    }

    public function setArrayValorUnitario($arrayValorUnitario) {
        $this->arrayValorUnitario = $arrayValorUnitario;
    }

    public function setArrayImporte($arrayImporte) {
        $this->arrayImporte = $arrayImporte;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
    }

    public function getNombreEmisor() {
        return $this->nombreEmisor;
    }

    public function setNombreEmisor($nombreEmisor) {
        $this->nombreEmisor = $nombreEmisor;
    }

    public function getNombreReceptor() {
        return $this->nombreReceptor;
    }

    public function setNombreReceptor($nombreReceptor) {
        $this->nombreReceptor = $nombreReceptor;
    }

    public function getUUID() {
        return $this->UUID;
    }

    public function setUUID($UUID) {
        $this->UUID = $UUID;
    }

    function getTipoComprobante() {
        return $this->tipoComprobante;
    }

    function setTipoComprobante($tipoComprobante) {
        $this->tipoComprobante = $tipoComprobante;
    }
}
