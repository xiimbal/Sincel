<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class XMLReadSAT {

    private $file;
    private $uuid;
    private $selloSAT;
    private $selloCSD;
    private $fecha;
    private $String;

    public function LeerXML() {
        $xml = simplexml_load_file($this->file);
        $rs = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $rs['cfdi']);
        $xml->registerXPathNamespace('t', $rs['tfd']);
        foreach ($xml->xpath('//t:TimbreFiscalDigital') as $vr) {
            $this->uuid = $vr['UUID'];
            $this->selloSAT = $vr['selloSAT'];
            $this->selloCSD = $vr['selloCFD'];
            $this->fecha = $vr['FechaTimbrado'];
        }
    }

    public function LeerXML33() {
        $xml = simplexml_load_file($this->file);
        $rs = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $rs['cfdi']);
        $xml->registerXPathNamespace('t', $rs['tfd']);
        foreach ($xml->xpath('//t:TimbreFiscalDigital') as $vr) {
            $this->uuid = $vr['UUID'];
            $this->selloSAT = $vr['SelloSAT'];
            $this->selloCSD = $vr['SelloCFD'];
            $this->fecha = $vr['FechaTimbrado'];
        }
    }
    
    public function LeerXMLString() {
        $xml = simplexml_load_string($this->String);
        $rs = $xml->getNamespaces(true);
        $xml->registerXPathNamespace('c', $rs['cfdi']);
        $xml->registerXPathNamespace('t', $rs['tfd']);
        foreach ($xml->xpath('//t:TimbreFiscalDigital') as $vr) {
            $this->uuid = $vr['UUID'];
            $this->selloSAT = $vr['SelloSAT'];
            $this->selloCSD = $vr['SelloCFD'];
            $this->fecha = $vr['FechaTimbrado'];
        }
    }

    public function getString() {
        return $this->String;
    }

    public function setString($String) {
        $this->String = $String;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    public function getFile() {
        return $this->file;
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getSelloSAT() {
        return $this->selloSAT;
    }

    public function getSelloCSD() {
        return $this->selloCSD;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    public function setSelloSAT($selloSAT) {
        $this->selloSAT = $selloSAT;
    }

    public function setSelloCSD($selloCSD) {
        $this->selloCSD = $selloCSD;
    }

}

?>