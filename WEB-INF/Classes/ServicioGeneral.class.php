<?php

/**
 * Description of ServicioGeneral
 *
 * @author MAGG
 */
class ServicioGeneral {
    private $cobrarRenta;
    private $cobrarExcedenteBN;
    private $cobrarExcedenteColor;
    private $cobrarProcesadasBN;
    private $cobrarProcesadasColor;
    
    function __construct() {
        $this->cobrarRenta = false;
        $this->cobrarExcedenteBN = false;
        $this->cobrarExcedenteColor = false;
        $this->cobrarProcesadasBN = false;
        $this->cobrarProcesadasColor = false;
    }

    
    public function getCobranzasByTipoServicio($idServicio){
        //Dependiendo del servicio, es lo que se va a cobrar
        switch ($idServicio) {
            case "1":
                $this->cobrarRenta = true;
                $this->cobrarExcedenteBN = true;
                $this->cobrarExcedenteColor = true;
                break;
            case "2":
                $this->cobrarRenta = true;
                $this->cobrarProcesadasBN = true;
                $this->cobrarProcesadasColor = true;
                break;
            case "3":
                $this->cobrarProcesadasBN = true;
                $this->cobrarProcesadasColor = true;
                break;
            case "100":
                $this->cobrarRenta = true;
                $this->cobrarExcedenteBN = true;
                $this->cobrarExcedenteColor = true;
                break;
            case "200":
                $this->cobrarRenta = true;
                $this->cobrarProcesadasBN = true;
                $this->cobrarProcesadasColor = true;
                break;
            case "300":
                $this->cobrarProcesadasBN = true;
                $this->cobrarProcesadasColor = true;
                //$descripcion_renta = "";
                break;
            case "400":
                $this->cobrarRenta = true;
                $this->cobrarExcedenteBN = true;
                $this->cobrarProcesadasColor = true;
                break;
            case "500":
                $this->cobrarRenta = true;
                $this->cobrarExcedenteColor = true;
                $this->cobrarProcesadasBN = true;
                break;
            case "1001":
                $this->cobrarRenta = true;
                $this->cobrarExcedenteBN = true;
                $this->cobrarExcedenteColor = true;
                break;
            case "1002":
                $this->cobrarRenta = true;
                $this->cobrarProcesadasBN = true;
                $this->cobrarProcesadasColor = true;
                break;
            case "1050":
                $this->cobrarRenta = true;
                $this->cobrarExcedenteBN = true;
                $this->cobrarExcedenteColor = true;
                break;
            case "1051":
                $this->cobrarRenta = true;
                $this->cobrarProcesadasBN = true;
                $this->cobrarProcesadasColor = true;
                break;
            default:
                $this->cobrarRenta = true;
                $this->cobrarExcedenteBN = true;
                $this->cobrarExcedenteColor = true;
                $this->cobrarProcesadasBN = true;
                $this->cobrarProcesadasColor = true;
                break;
        }
        return true;
    }
    
    public function getCobrarRenta() {
        return $this->cobrarRenta;
    }

    public function setCobrarRenta($cobrarRenta) {
        $this->cobrarRenta = $cobrarRenta;
    }

    public function getCobrarExcedenteBN() {
        return $this->cobrarExcedenteBN;
    }

    public function setCobrarExcedenteBN($cobrarExcedenteBN) {
        $this->cobrarExcedenteBN = $cobrarExcedenteBN;
    }

    public function getCobrarExcedenteColor() {
        return $this->cobrarExcedenteColor;
    }

    public function setCobrarExcedenteColor($cobrarExcedenteColor) {
        $this->cobrarExcedenteColor = $cobrarExcedenteColor;
    }

    public function getCobrarProcesadasBN() {
        return $this->cobrarProcesadasBN;
    }

    public function setCobrarProcesadasBN($cobrarProcesadasBN) {
        $this->cobrarProcesadasBN = $cobrarProcesadasBN;
    }

    public function getCobrarProcesadasColor() {
        return $this->cobrarProcesadasColor;
    }

    public function setCobrarProcesadasColor($cobrarProcesadasColor) {
        $this->cobrarProcesadasColor = $cobrarProcesadasColor;
    }
}

?>
