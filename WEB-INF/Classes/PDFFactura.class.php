<?php

require('libfpdf/fpdf.php');
include_once "lib/PHPImagen.lib.php";
include_once ("Catalogo.class.php");
include_once ("Factura2.class.php");
include_once ("EnLetras.class.php");

//Clase en blanco
class PDFFactura extends FPDF {

    private $TOTAL_LINEAS_PAGINA = 65; //sale ya funcionó suertes xD jajajjaajaja
    private $cadena_pagina = array();
    private $titulo = "";
    private $FolioFiscal = "";
    private $Folio = "";
    private $Referencia;
    private $LugarExpedicion = "";
    private $HoraEmision = "";
    private $condicionesPago = "";
    private $FormaPago = "";
    private $MetodoPago = "";
    private $NumeroCtaPago = "";
    private $CSD_Emisor;
    private $CSD_Sat;
    private $Fecha_Cert;
    private $nombre_Emisor = "";
    private $RFC_Emisor = "";
    private $regimenFiscal_Emisor = "";
    private $calle_Emisor = "";
    private $no_Ext_Emisor = "";
    private $no_int_Emisor = "";
    private $colonia_Emisor = "";
    private $Estado_Emisor = "";
    private $delegacion_Emisor = "";
    private $pais_Emisor = "";
    private $CP_Emisor = "";
    private $Tel_Emisor = "";
    private $Periodo_Facturacion_Emisor = "";
    private $nombre_Receptor = "";
    private $RFC_Receptor = "";
    private $regimenFiscal_Receptor = "";
    private $calle_Receptor = "";
    private $no_Ext_Receptor = "";
    private $no_int_Receptor = "";
    private $colonia_Receptor = "";
    private $Estado_Receptor = "";
    private $delegacion_Receptor = "";
    private $pais_Receptor = "";
    private $CP_Receptor = "";
    private $Tel_Receptor = "";
    private $Periodo_Facturacion_Receptor = "";
    private $ClaveCliente;
    private $clave_receptor;
    private $tabla;
    private $num_Letra = "";
    private $subtotal = "";
    private $descuento = "";
    private $iva = "";
    private $total = "";
    private $cadena_SAT = "";
    private $sello_Emisor = "";
    private $sello_Digital = "";
    private $cbb;
    private $logo;
    private $leyenda;
    private $valores;
    private $comentarios;
    private $localidad;
    private $filas;
    private $ndc;
    private $contrato;
    private $mes_contrato;
    private $Addendas; //Array bidimensional
    private $IdPreFactura;
    private $arrayPago; //Información del pago
    private $arrayDocumentosRelacionados;    //Información de los documentos relacionados del pago
    private $TipoComprobante;
    private $UsoCFDI;
    private $UUIDRelacionado;
    private $TipoRelacion;
    private $Moneda;
    private $Conceptos;
    
    function CrearPDFPago() {
        /**
         * 
         * Ponemos los datos de la empresa
         * 
         * * */
        $x = 72;
        $this->SetFont('Arial', 'B', 10);
        $this->SetX($x);
        $this->MultiCell(80, 4, utf8_decode($this->nombre_Emisor), 0, 'L');        
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor('0', '0', '0'); //para imprimir en negro        
        $this->SetX(147);
        $this->SetFont('Arial', '', 7);
        $this->SetFillColor(210, 203, 203);
        $this->Cell(65, 5, utf8_decode('COMPLEMENTO DE RECEPCIÓN DE PAGOS'), 1, 1, 'C', true);
        $this->SetX(147);
        $this->SetFont('Arial', '', 8);
        $referencia = "";
        if(isset($this->Referencia) && !empty($this->Referencia)){
            $referencia = "\nNum Operacion: . " . $this->Referencia;
        }
        $this->MultiCell(65, 5, utf8_decode("Folio: ".$this->Folio
                        . $referencia .
                        "\nFolio Fiscal: \n" . $this->FolioFiscal .
                        "\nNo de serie CSD SAT: : \n" . $this->CSD_Sat .
                        "\nFecha y hora certificación: " . $this->Fecha_Cert .
                        "\nN° DE CERTIFICADO: \n" . $this->CSD_Emisor), 1, 'C', FALSE);
        
        $this->SetFont('Arial', '', 7);
        $this->SetXY($x, 22);
        $this->Cell(50, 2, utf8_decode($this->RFC_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        $this->Cell(50, 2, utf8_decode("Calle: " . $this->calle_Emisor . ", " . $this->no_Ext_Emisor . " , " . $this->no_int_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        $this->Cell(50, 2, utf8_decode($this->colonia_Emisor . ", " . $this->delegacion_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        $this->Cell(50, 2, utf8_decode($this->Estado_Emisor . " " . $this->CP_Emisor . " Tel. " . $this->Tel_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        
        if(strlen($this->regimenFiscal_Emisor) < 50){
            $this->Cell(50, 2, utf8_decode("Régimen Fiscal: " . $this->regimenFiscal_Emisor), 0, 2, 'L');
        }else{
            $this->MultiCell(70, 3, utf8_decode("Régimen Fiscal: " . $this->regimenFiscal_Emisor), 0, 'L', false);
        }
        
        /**
         * 
         * Ponemos la imagen del PDF
         * 
         * * */
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);
            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(250, 80);
            $x = 2;
            if ($imagen->getRw() < 150) {
                $x = 15;
            } else if ($imagen->getRw() > 200) {
                $imagen->resize(200, 60);
                $x = 2;
            }

            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 5, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 5, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }
        
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 8);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('Lugar de expedición: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->LugarExpedicion), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->Ln(1);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('Fecha de expedición: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->HoraEmision), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->Ln(1);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('Receptor CFDI: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->nombre_Receptor), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->Ln(1);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('RFC Receptor CFDI: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->RFC_Receptor), 0, 1, 'L');
        
        
        $param = new Parametros();
        if($param->getRegistroById(37) && $param->getValor() == "1" && $param->getActivo() == "1"){
            $this->SetFont('Arial', 'B', 8);
            $this->SetX(130);
            $this->Cell(30, 0, utf8_decode('Cliente: '), 0, 0, 'L');
            $this->SetFont('Arial', '', 8);
            $this->Cell(30, 1, utf8_decode($this->clave_receptor), 0, 0, 'L');
            //
            $this->Ln(4);
            $this->SetFont('Arial', 'B', 8);
            $this->SetX(10);
            $this->Cell(30, 3, utf8_decode('Nombre: '), 0, 0, 'L');
            $this->SetFont('Arial', '', 8);
            if(strlen($this->nombre_Receptor) < 50){
                $this->Cell(30, 3,  str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($this->nombre_Receptor))), 0, 0, 'L');
            }else{
                $this->MultiCell(80, 3, str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($this->nombre_Receptor))), 0, 'L', false);
            }
        }
        
        $this->Ln(10);
        $this->SetFillColor(256, 256, 256);
        $this->SetFont('Arial', '', 9);

        $this->Cell(40, 5, utf8_decode('Fecha de pago'), 1, 0, 'C', false);
        $this->Cell(50, 5, utf8_decode('Forma pago'), 1, 0, 'C', false);
        $this->Cell(40, 5, utf8_decode('Moneda'), 1, 0, 'C', false);
        $this->Cell(44, 5, utf8_decode('Monto pago'), 1, 0, 'C', false);
        $this->Ln(5);
        
        $this->Cell(40, 5, utf8_decode($this->arrayPago[0]), 1, 0, 'C', false);
        $this->SetFont('Arial', '', 7.5);
        $this->Cell(50, 5, utf8_decode($this->arrayPago[1]), 1, 0, 'L', false);
        $this->SetFont('Arial', '', 9);
        $this->Cell(40, 5, utf8_decode($this->arrayPago[2]), 1, 0, 'C', false);
        $this->Cell(44, 5, utf8_decode($this->arrayPago[3]), 1, 0, 'R', false);
        
        /* Información cuenta ordenante */
        if(!empty($this->arrayPago[4]) || !empty($this->arrayPago[4]) || !empty($this->arrayPago[6])){
            $this->Ln(13);
            $this->SetFillColor(256, 256, 256);
            $this->SetFont('Arial', '', 9);

            $this->Cell(40, 5, utf8_decode('RFC Banco Emisor'), 1, 0, 'C', false);
            $this->Cell(60, 5, utf8_decode('Nombre Banco Emisor'), 1, 0, 'C', false);
            //FERNANDO
            //$this->Cell(50, 5, utf8_decode('Clabe Interbancaria'), 1, 0, 'C', false);
            $this->Cell(50, 5, utf8_decode('Cuenta Ordenante'), 1, 0, 'C', false);
            $this->Ln(5);

            $this->Cell(40, 5, utf8_decode($this->arrayPago[4]), 1, 0, 'C', false);
            $this->SetFont('Arial', '', 7.5);
            $this->Cell(60, 5, utf8_decode($this->arrayPago[5]), 1, 0, 'C', false);
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->arrayPago[6]), 1, 0, 'C', false);
        }
        
        /*Conceptos*/
//        $this->Ln(10);
//        $this->SetFillColor(256, 256, 256);
//        $this->SetFont('Arial', '', 8);
//        $this->SetX(3);
//
//        $this->Cell(89, 5, utf8_decode('Descripción'), 1, 0, 'C', false);
//        $this->Cell(30, 5, utf8_decode('Cantidad'), 1, 0, 'C', false);
//        $this->Cell(30, 5, utf8_decode('Precio'), 1, 0, 'C', false);
//        $this->Cell(30, 5, utf8_decode('Importe'), 1, 0, 'C', false);
//        $this->Cell(30, 5, utf8_decode('Descuento'), 1, 0, 'C', false);
        
        $totalArticulos = 0;
        $numeroDeConceptos = 0;
        $subTotal = 0;
        
        while($row = mysql_fetch_array($this->Conceptos)){
//        $this->Ln(5);
//        $this->SetX(3);
//        $this->Cell(89, 5, utf8_decode($row['Descripcion']), 1, 0, 'C', false);
//        $this->Cell(30, 5, utf8_decode("$".number_format($row['Cantidad'],2)), 1, 0, 'R', false);
//        $this->Cell(30, 5, utf8_decode("$".number_format($row['PrecioUnitario'],2)), 1, 0, 'R', false);
//        $this->Cell(30, 5, utf8_decode("$".number_format($row['importe'],2)), 1, 0, 'R', false);
//        $this->Cell(30, 5, utf8_decode("$".number_format($row['Descuento'],2)), 1, 0, 'R', false);
        $totalArticulos += $row['Cantidad'];
        $numeroDeConceptos++;
        $subTotal += $row['PrecioUnitario'];
        $sumatotal+=($row['Cantidad']*$row['PrecioUnitario'])-$row['Descuento'];
        }
        $total = $sumatotal*1.16;
        $letras = new EnLetras();
        
        $totalConLetra = strtoupper($letras->ValorEnLetras($total,""));
       
        /*$this->Ln(10);
        $this->SetFillColor(256, 256, 256);
        $this->SetFont('Arial', '', 8);
        $this->SetX(3);
        $y = $this->GetY();
        //$this->MultiCell(150,5, utf8_decode("Total de articulos: ".number_format($totalArticulos,2)."\nNo.Conceptos: ".number_format($numeroDeConceptos,2)."\nTotal con letra: ".$totalConLetra), 1, 'L', false);
        $this->SetXY(153, $y);
        //$this->MultiCell(59,5, utf8_decode("Subtotal: $".number_format($sumatotal,2)."\nTotal: $".number_format($total,2)), 1, 'L', false);*/
        
        //Pueden ser automáticos (tan pronto como el texto llegue al borde derecho de la celda) o explícitos (a través del carácter \ n). 
        //Negritas con $this->SetFont('Arial', 'B', 8);
        /*conceptos*/
        
        $this->Ln(13);
        $this->SetFillColor(256, 256, 256);
        $this->SetFont('Arial', '', 7);
        
        $this->SetX(3);
        $this->Cell(59, 5, utf8_decode('Id documento'), 1, 0, 'C', true);
        $this->Cell(14, 5, utf8_decode('Folio'), 1, 0, 'C', true);
        $this->Cell(12, 5, utf8_decode('Moneda'), 1, 0, 'C', true);
        $this->Cell(36, 5, utf8_decode('Método de pago'), 1, 0, 'C', true);
        $this->Cell(19, 5, utf8_decode('No.Parcialidad'), 1, 0, 'C', true);
        $this->Cell(23, 5, utf8_decode('Saldo anterior'), 1, 0, 'C', true);
        $this->Cell(23, 5, utf8_decode('Importe pagado'), 1, 0, 'C', true);
        $this->Cell(23, 5, utf8_decode('Saldo insoluto'), 1, 0, 'C', true);
        $this->SetFont('Arial', '', 7.5);
        foreach($this->arrayDocumentosRelacionados as $arrayDocumento){
            $this->Ln(5);
            $this->SetX(3);
            $this->Cell(59, 5, utf8_decode($arrayDocumento[0]), 1, 0, 'C', true);
            $this->Cell(14, 5, utf8_decode($arrayDocumento[1]), 1, 0, 'C', true);
            $this->Cell(12, 5, utf8_decode($arrayDocumento[2]), 1, 0, 'C', true);
            $this->Cell(36, 5, utf8_decode(substr($arrayDocumento[3],0,25)), 1, 0, 'C', true);
            $this->Cell(19, 5, utf8_decode($arrayDocumento[4]), 1, 0, 'C', true);
            $this->Cell(23, 5, utf8_decode($arrayDocumento[6]), 1, 0, 'R', true);
            $this->Cell(23, 5, utf8_decode($arrayDocumento[5]), 1, 0, 'R', true);
            $this->Cell(23, 5, utf8_decode($arrayDocumento[7]), 1, 0, 'R', true);
        }
        
        $this->ln(15);
        $this->Image($this->cbb, $this->GetX(), $this->GetY(), 30, 30, 'PNG');
        $this->SetX($this->GetX() + 30);
        $this->SetFont('Arial', '', 6);
        $this->MultiCell(140, 4, utf8_decode(trim($this->leyenda)), 0, 'L', false);
        $this->SetY($this->GetY() + 30);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Cadena original del complemento de certificación del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 4, utf8_decode($this->cadena_SAT), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 4, utf8_decode("Sello digital del emisor:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 4, utf8_decode($this->sello_Emisor), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 4, utf8_decode("Sello digital del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 4, utf8_decode($this->sello_Digital), 0, false);
        $this->SetFont('Arial', 'B', 9);

        $this->Cell(200, 4, utf8_decode("Este documento es una representación impresa de un CFDI."), 0, 1, false);    
    }

    function CrearPDFPrePago() {
        /**
         * 
         * Ponemos los datos de la empresa
         * 
         * * */
        $x = 72;
        $this->SetFont('Arial', 'B', 10);
        $this->SetX($x);
        $this->MultiCell(80, 4, utf8_decode($this->nombre_Emisor), 0, 'L');        
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor('0', '0', '0'); //para imprimir en negro        
        $this->SetX(147);
        $this->SetFont('Arial', '', 7);
        $this->SetFillColor(210, 203, 203);
        $this->Cell(65, 5, utf8_decode('PrePago'), 1, 1, 'C', true);
        $this->SetX(147);
        $this->SetFont('Arial', '', 8);
        $referencia = "";
        if(isset($this->Referencia) && !empty($this->Referencia)){
            $referencia = "\nNum Operacion: . " . $this->Referencia;
        }
        $this->MultiCell(65, 5, utf8_decode("Folio: ".$this->Folio
                        . $referencia .
                        "\nFolio Fiscal: \n" .
                        "\nNo de serie CSD SAT: \n" . 
                        "\nFecha y hora certificación: " . 
                        "\nN° DE CERTIFICADO: \n" ), 1, 'C', FALSE);
        
        $this->SetFont('Arial', '', 7);
        $this->SetXY($x, 22);
        $this->Cell(50, 2, utf8_decode($this->RFC_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        $this->Cell(50, 2, utf8_decode("Calle: " . $this->calle_Emisor . ", " . $this->no_Ext_Emisor . " , " . $this->no_int_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        $this->Cell(50, 2, utf8_decode($this->colonia_Emisor . ", " . $this->delegacion_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        $this->Cell(50, 2, utf8_decode($this->Estado_Emisor . " " . $this->CP_Emisor . " Tel. " . $this->Tel_Emisor), 0, 2, 'L');
        $this->Ln(1);
        $this->SetX($x);
        
        if(strlen($this->regimenFiscal_Emisor) < 50){
            $this->Cell(50, 2, utf8_decode("Régimen Fiscal: " . $this->regimenFiscal_Emisor), 0, 2, 'L');
        }else{
            $this->MultiCell(70, 3, utf8_decode("Régimen Fiscal: " . $this->regimenFiscal_Emisor), 0, 'L', false);
        }
        
        /**
         * 
         * Ponemos la imagen del PDF
         * 
         * * */
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);
            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(250, 80);
            $x = 2;
            if ($imagen->getRw() < 150) {
                $x = 15;
            } else if ($imagen->getRw() > 200) {
                $imagen->resize(200, 60);
                $x = 2;
            }

            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 5, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 5, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }
        
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 8);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('Lugar de expedición: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->LugarExpedicion), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->Ln(1);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('Fecha de expedición: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->HoraEmision), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->Ln(1);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('Receptor CFDI: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->nombre_Receptor), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->Ln(1);
        $this->SetX(10);
        $this->Cell(30, 3, utf8_decode('RFC Receptor CFDI: '), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(30, 3, utf8_decode($this->RFC_Receptor), 0, 1, 'L');
        
        
        $param = new Parametros();
        if($param->getRegistroById(37) && $param->getValor() == "1" && $param->getActivo() == "1"){
            $this->SetFont('Arial', 'B', 8);
            $this->SetX(130);
            $this->Cell(30, 0, utf8_decode('Cliente: '), 0, 0, 'L');
            $this->SetFont('Arial', '', 8);
            $this->Cell(30, 1, utf8_decode($this->clave_receptor), 0, 0, 'L');
            //
            $this->Ln(4);
            $this->SetFont('Arial', 'B', 8);
            $this->SetX(10);
            $this->Cell(30, 3, utf8_decode('Nombre: '), 0, 0, 'L');
            $this->SetFont('Arial', '', 8);
            if(strlen($this->nombre_Receptor) < 50){
                $this->Cell(30, 3,  str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($this->nombre_Receptor))), 0, 0, 'L');
            }else{
                $this->MultiCell(80, 3, str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($this->nombre_Receptor))), 0, 'L', false);
            }
        }
        
        $this->Ln(10);
        $this->SetFillColor(256, 256, 256);
        $this->SetFont('Arial', '', 9);

        $this->Cell(40, 5, utf8_decode('Fecha de pago'), 1, 0, 'C', false);
        $this->Cell(50, 5, utf8_decode('Forma pago'), 1, 0, 'C', false);
        $this->Cell(40, 5, utf8_decode('Moneda'), 1, 0, 'C', false);
        $this->Cell(44, 5, utf8_decode('Monto pago'), 1, 0, 'C', false);
        $this->Ln(5);
        
        $this->Cell(40, 5, utf8_decode($this->arrayPago[0]), 1, 0, 'C', false);
        $this->SetFont('Arial', '', 7.5);
        $this->Cell(50, 5, utf8_decode($this->arrayPago[1]), 1, 0, 'L', false);
        $this->SetFont('Arial', '', 9);
        $this->Cell(40, 5, utf8_decode($this->arrayPago[2]), 1, 0, 'C', false);
        $this->Cell(44, 5, utf8_decode($this->arrayPago[3]), 1, 0, 'R', false);
        
        if(!empty($this->arrayPago[4]) || !empty($this->arrayPago[4]) || !empty($this->arrayPago[6])){
            $this->Ln(13);
            $this->SetFillColor(256, 256, 256);
            $this->SetFont('Arial', '', 9);

            $this->Cell(40, 5, utf8_decode('RFC Banco Emisor'), 1, 0, 'C', false);
            $this->Cell(60, 5, utf8_decode('Nombre Banco Emisor'), 1, 0, 'C', false);
            //FERNANDO
            //$this->Cell(50, 5, utf8_decode('Clabe Interbancaria'), 1, 0, 'C', false);
            $this->Cell(50, 5, utf8_decode('Cuenta Ordenante'), 1, 0, 'C', false);
            $this->Ln(5);

            $this->Cell(40, 5, utf8_decode($this->arrayPago[4]), 1, 0, 'C', false);
            $this->SetFont('Arial', '', 7.5);
            $this->Cell(60, 5, utf8_decode($this->arrayPago[5]), 1, 0, 'C', false);
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->arrayPago[6]), 1, 0, 'C', false);
        }
        
        /*Conceptos*/
        /*$this->Ln(10);
        $this->SetFillColor(256, 256, 256);
        $this->SetFont('Arial', '', 8);
        $this->SetX(3);

        $this->Cell(89, 5, utf8_decode('Descripción'), 1, 0, 'C', false);
        $this->Cell(30, 5, utf8_decode('Cantidad'), 1, 0, 'C', false);
        $this->Cell(30, 5, utf8_decode('Precio'), 1, 0, 'C', false);
        $this->Cell(30, 5, utf8_decode('Importe'), 1, 0, 'C', false);
        $this->Cell(30, 5, utf8_decode('Descuento'), 1, 0, 'C', false);*/
        
        $totalArticulosUni = 0;
        $totalArticulos = 0;
        $numeroDeConceptos = 0;
        $subTotal = 0;
        $sumatotal=0;
        
        while($row = mysql_fetch_array($this->Conceptos)){
        /*this->Ln(5);
        $this->SetX(3);
        $this->Cell(89, 5, utf8_decode($row['Descripcion']), 1, 0, 'C', false);
        $this->Cell(30, 5, utf8_decode(number_format($row['Cantidad'])), 1, 0, 'R', false);
        $this->Cell(30, 5, utf8_decode("$".number_format($row['PrecioUnitario'],2)), 1, 0, 'R', false);
        $this->Cell(30, 5, utf8_decode("$".number_format($row['importe'],2)), 1, 0, 'R', false);
        $this->Cell(30, 5, utf8_decode("$".number_format($row['Descuento'],2)), 1, 0, 'R', false);*/
        $totalArticulos += $row['Cantidad'];
        $numeroDeConceptos++;
        $subTotal += $row['PrecioUnitario'];
        $sumatotal+=($row['Cantidad']*$row['PrecioUnitario'])-$row['Descuento'];
        }
        $total = $sumatotal*1.16;
        $letras = new EnLetras();
        
        $totalConLetra = strtoupper($letras->ValorEnLetras($total,""));
       
        /*$this->Ln(10);
        $this->SetFillColor(256, 256, 256);
        $this->SetFont('Arial', '', 8);
        $this->SetX(3);
        $y = $this->GetY();
        //$this->MultiCell(150,5, utf8_decode("Total de articulos: ".number_format($totalArticulos,2)."\nNo.Conceptos: ".number_format($numeroDeConceptos,2)."\nTotal con letra: ".$totalConLetra), 1, 'L', false);
        $this->SetXY(153, $y);
        $this->MultiCell(59,5, utf8_decode("Subtotal: $".number_format($sumatotal,2)."\nTotal: $".number_format($total,2)), 1, 'L', false);*/
        
        //Pueden ser automáticos (tan pronto como el texto llegue al borde derecho de la celda) o explícitos (a través del carácter \ n). 
        //Negritas con $this->SetFont('Arial', 'B', 8);
        /*conceptos*/
        
        $this->Ln(13);
        $this->SetFillColor(256, 256, 256);
        $this->SetFont('Arial', '', 7);
        
        $this->SetX(3);
        $this->Cell(59, 5, utf8_decode('Id documento'), 1, 0, 'C', true);
        $this->Cell(14, 5, utf8_decode('Folio'), 1, 0, 'C', true);
        $this->Cell(12, 5, utf8_decode('Moneda'), 1, 0, 'C', true);
        $this->Cell(36, 5, utf8_decode('Método de pago'), 1, 0, 'C', true);
        $this->Cell(19, 5, utf8_decode('No.Parcialidad'), 1, 0, 'C', true);
        $this->Cell(23, 5, utf8_decode('Saldo anterior'), 1, 0, 'C', true);
        $this->Cell(23, 5, utf8_decode('Importe pagado'), 1, 0, 'C', true);
        $this->Cell(23, 5, utf8_decode('Saldo insoluto'), 1, 0, 'C', true);
        $this->SetFont('Arial', '', 7.5);
        foreach($this->arrayDocumentosRelacionados as $arrayDocumento){
            $this->Ln(5);
            $this->SetX(3);
            $this->Cell(59, 5, utf8_decode($arrayDocumento[0]), 1, 0, 'C', true);
            $this->Cell(14, 5, utf8_decode($arrayDocumento[1]), 1, 0, 'C', true);
            $this->Cell(12, 5, utf8_decode($arrayDocumento[2]), 1, 0, 'C', true);
            $this->Cell(36, 5, utf8_decode(substr($arrayDocumento[3],0,25)), 1, 0, 'C', true);
            $this->Cell(19, 5, utf8_decode($arrayDocumento[4]), 1, 0, 'C', true);
            $this->Cell(23, 5, utf8_decode($arrayDocumento[6]), 1, 0, 'R', true);
            $this->Cell(23, 5, utf8_decode($arrayDocumento[5]), 1, 0, 'R', true);
            $this->Cell(23, 5, utf8_decode($arrayDocumento[7]), 1, 0, 'R', true);
        }
        
        $this->ln(15);
        $this->SetX($this->GetX() + 30);
        $this->SetFont('Arial', '', 6);
        $this->MultiCell(140, 4, utf8_decode(trim()), 0, 'L', false);
        $this->SetY($this->GetY() + 30);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Cadena original del complemento de certificación del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 4, utf8_decode(""), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 4, utf8_decode("Sello digital del emisor:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 4, utf8_decode(""), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 4, utf8_decode("Sello digital del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 4, utf8_decode(""), 0, false);
        $this->SetFont('Arial', 'B', 9);

        $this->Cell(200, 4, utf8_decode("Este documento es una representación impresa de un CFDI."), 0, 1, false);    
    }
    
    function CrearPDF() { //Encabezado es correcto estamos en factura no?, no, ahorita la que viste estaba en pref copia las líneas no? es que se traba a veces conmigo
        //Define tipo de letra a usar, Arial, Negrita, 15
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor('255', '0', '0'); //para imprimir en rojo

        /* Líneas paralelas
         * Line(x1,y1,x2,y2)
         * El origen es la esquina superior izquierda
         * Cambien los parámetros y chequen las posiciones
         * */
        //$this->Line(10,10,206,10);
        //$this->Line(10,35.5,206,35.5);

        /* Explicaré el primer Cell() (Los siguientes son similares)
         * 30 : de ancho
         * 25 : de alto
         * ' ' : sin texto
         * 0 : sin borde
         * 0 : Lo siguiente en el código va a la derecha (en este caso la segunda celda)
         * 'C' : Texto Centrado
         *  Método para insertar imagen
         *     'images/logo.png' : ruta de la imagen
         *     152 : posición X (recordar que el origen es la esquina superior izquierda)
         *     12 : posición Y
         *     19 : Ancho de la imagen (w)
         *     Nota: Al no especificar el alto de la imagen (h), éste se calcula automáticamente
         * */

        $this->SetX(110);
        $this->Cell(150, 2, utf8_decode($this->titulo . " FOLIO " . $this->Folio), 0, 1, 'L');
        $this->SetTextColor('0', '0', '0'); //para imprimir en rojo
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(150, 10, utf8_decode('Folio Fiscal'), 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->SetX(110);
        $this->Cell(150, 5, utf8_decode($this->FolioFiscal), 0, 1, 'T');
        $this->Ln(3);
        /* $this->SetFont('Arial', 'B', 9);
          $this->SetX(110);
          $this->Cell(50, 5, utf8_decode('Folio'), 0, 0, 'L');
          $this->SetFont('Arial', '', 9);
          $this->Cell(50, 5, utf8_decode($this->Folio), 0, 1, 'L'); */
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Lugar de expedición'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->LugarExpedicion), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Fecha y hora de emisión'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->HoraEmision), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Forma de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->FormaPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Método de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->MetodoPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        if ($this->condicionesPago != "") {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Condiciones de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->condicionesPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if ($this->NumeroCtaPago != "") {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('NumCtaPago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->NumeroCtaPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('No de serie CSD emisor:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->CSD_Emisor), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('No de serie CSD SAT:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, $this->CSD_Sat, 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Fecha y hora certificación:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->Fecha_Cert), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        if (isset($this->mes_contrato) && !empty($this->mes_contrato)) {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Mes de contrato'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->mes_contrato), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        /* Codigo para mostrar la clave del cliente */
        $catalogo = new Catalogo();
        $mostrarClaveQuery = "Select ClaveCliente from c_cliente WHERE RFC = '" . $this->RFC_Receptor . "' and verCClientePDF = 1";
        $result = $catalogo->obtenerLista($mostrarClaveQuery);
        if ($rs = mysql_fetch_array($result)) {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Referencia de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($rs['ClaveCliente']), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);

            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(300, 130);
            $x = 10;
            if ($imagen->getRw() < 150) {
                $x = 30;
            }
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }

        $this->Ln(5);

        $this->SetFillColor(210, 203, 203);
        $this->Cell(200, 5, utf8_decode('Emisor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->MultiCell(200, 5, utf8_decode("Nombre: " . $this->nombre_Emisor . "\nRFC: " . $this->RFC_Emisor . "\nRégimen Fiscal: " . $this->regimenFiscal_Emisor . "\nCalle: " . $this->calle_Emisor . " No.Exterior: " . $this->no_Ext_Emisor . " No.Interior: " . $this->no_int_Emisor . "\nColonia: " . $this->colonia_Emisor . " Estado: " . $this->Estado_Emisor . "\nDelegación: " . $this->delegacion_Emisor . " País: " . $this->pais_Emisor . "\nCódigo Postal: " . $this->CP_Emisor . "\nTel: " . $this->Tel_Emisor . "\nPeríodo facturación: " . $this->Periodo_Facturacion_Emisor), 1, 'L', FALSE);
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode('Receptor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);
        $string_receptor = "Nombre: " . $this->nombre_Receptor . "\nRFC: " . $this->RFC_Receptor . "\nCalle: " . $this->calle_Receptor . " No.Exterior: " . $this->no_Ext_Receptor . " No.Interior: " . $this->no_int_Receptor . "\nColonia: " . $this->colonia_Receptor . "\nLocalidad:" . $this->localidad . "\nEstado: " . $this->Estado_Receptor . "\nDelegación: " . $this->delegacion_Receptor . " País: " . $this->pais_Receptor . "\nCódigo Postal: " . $this->CP_Receptor;
        if ($this->Addendas != NULL) {
            $contador = 0;
            foreach ($this->Addendas as $value) {
                if ($contador == 0 || $contador % 5 == 0) {
                    $string_receptor .= "\n";
                }
                $string_receptor .= $value[0] . ": " . $value[1] . "      ";
                $contador++;
            }
        }
        $this->MultiCell(200, 5, utf8_decode($string_receptor), 1, 'L', FALSE);
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(15, 5, utf8_decode('Cantidad'), 1, 0, 'C', true);
        $this->Cell(15, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(120, 5, utf8_decode('Descripción'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Importe'), 1, 1, 'C', true);

        for ($i = 0; $i < count($this->tabla); $i++) {
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(120, $array[2])); //120 caracteres es lo máximo permitido por línea
            $nb = $nb * 5; //se multiplica por el tamaño de pixeles verticales!
            $this->SetFont('Arial', '', 9);
            if ($array[5] == "1") {//Si es un encabezado                
                $this->Cell(200, 5, utf8_decode(strtoupper($array[2])), 1, 1, 'C', false);
            } else {//Si es un concepto normal   
                if (isset($this->filas[1]) && strlen($this->filas[1]) < 30 && isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0] . $this->filas[1];
                    $j_aux = 2;
                } else if (isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0];
                    $j_aux = 1;
                } else {
                    //$concepto = "1.".$this->filas[1]." -- 2.".$this->filas[2]." -- 3.".$this->filas[3];
                    $concepto = $this->filas[1];
                    $j_aux = 2;
                }
                //$nb = max(0, $this->NbLinesSimple(120, $concepto)) * 5;
                $nb = max(0, $this->NbLines2(100, $concepto)) * 5;  
                
                if (strpos($array[0], ',') !== false) {
                    $this->Cell(15, $nb, utf8_decode($array[0]), 1, 0, 'C', false);
                } else {
                    $floatVal = floatval($array[0]);
                    if ($floatVal && intval($floatVal) != $floatVal) {
                        $this->Cell(15, $nb, utf8_decode(number_format((float) $array[0], 4)), 1, 0, 'C', false);
                    } else {
                        $this->Cell(15, $nb, utf8_decode(number_format((int) $array[0])), 1, 0, 'C', false);
                    }//dale
                }
                $this->Cell(15, $nb, utf8_decode($array[1]), 1, 0, 'C', false);
                $y1 = $this->GetY();
                $x1 = $this->GetX();
                //tendremos que ocupar toda una pagina, en la primer hoja son menos lineas, no? perame estoy haciendo cosas mal                
                $this->MultiCell(120, 5, utf8_decode($concepto), 1, 'L', false);
                $this->SetY($y1);
                $this->SetX($x1 + 120);
                //echo "<br/><b>Escribiendo en Y ".  $this->GetY()."</b> -- ".$this->filas[0];
                $this->Cell(25, $nb, utf8_decode("$ " . number_format($array[3], 2)), 1, 0, 'R', false);
                $this->Cell(25, $nb, utf8_decode("$ " . number_format($array[4], 2)), 1, 1, 'R', false);


                for ($j = $j_aux; $j < count($this->filas); $j++) {//recorremos hasta la ultima(se puede meter a una funcion)   
                    if (!isset($this->filas[$j]) || empty($this->filas[$j])) {
                        continue;
                    }
                    $nb = max(0, $this->NbLinesSimple(120, $this->filas[$j])) * 5;
                    $this->Cell(15, $nb, utf8_decode(''), 1, 0, 'C', false);
                    $this->Cell(15, $nb, utf8_decode(''), 1, 0, 'C', false); //prueba porque no se que pase xD
                    $y1 = $this->GetY(); //
                    $x1 = 40;
                    //echo "<br/><b>Escribiendo 2 en Y ".  $this->GetY()."</b> -- ".$this->filas[$j];
                    $this->MultiCell(120, 5, utf8_decode($this->filas[$j]), 1, 'L', false); //estos conceptos ocuparan una página
                    $this->SetY($y1); //a ver que pasa con un concepto :P
                    $this->SetX($x1 + 120); // ;) (Y) sale te dejo ;) gra
                    $this->Cell(25, $nb, utf8_decode(''), 1, 0, 'R', false);
                    $this->Cell(25, $nb, utf8_decode(''), 1, 1, 'R', false); //esta linea 
                    $this->SetY($this->GetY() + $nb + 10);
                }//en teoría solo debería poner un concepto si lo pruebas ahorita
            }
        }


        $this->MultiCell(120, 5, utf8_decode("(" . $this->num_Letra . ")"), 0, 'L', false);
        $this->Cell(160, 5, utf8_decode('Subtotal:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->subtotal), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('IVA 16%:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->iva), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('Total:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->total), 0, 1, 'R', false);
        $this->ln(3);
        $this->SetFont('Arial', '', 6);
        $this->Cell(200, 5, utf8_decode($this->comentarios), 0, 1, false);
        $this->Image($this->cbb, $this->GetX(), $this->GetY(), 30, 30, 'PNG');
        $this->SetX($this->GetX() + 30);
        if (is_array($this->valores)) {
            foreach ($this->valores as $key => $value) {
                $this->Cell(80, 5, utf8_decode($key . ":" . $value), 0, 1, 'C', false);
                $this->SetX($this->GetX() + 30);
            }
        }

        $this->MultiCell(140, 5, utf8_decode(trim($this->leyenda)), 0, 'L', false);
        $this->SetY($this->GetY() + 30);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Cadena original del complemento de certificación del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 5, utf8_decode($this->cadena_SAT), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Sello digital del emisor:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 5, utf8_decode($this->sello_Emisor), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Sello digital del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 5, utf8_decode($this->sello_Digital), 0, false);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Este documento es una representación impresa de un CFDI."), 0, 1, false);
        //$this->Cell(111,25,'ALGÚN T�?TULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25
    }
    
    function CrearPDF33() { //Encabezado es correcto estamos en factura no?, no, ahorita la que viste estaba en pref copia las líneas no? es que se traba a veces conmigo
        //Define tipo de letra a usar, Arial, Negrita, 15
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor('255', '0', '0'); //para imprimir en rojo

        /* Líneas paralelas
         * Line(x1,y1,x2,y2)
         * El origen es la esquina superior izquierda
         * Cambien los parámetros y chequen las posiciones
         * */
        //$this->Line(10,10,206,10);
        //$this->Line(10,35.5,206,35.5);

        /* Explicaré el primer Cell() (Los siguientes son similares)
         * 30 : de ancho
         * 25 : de alto
         * ' ' : sin texto
         * 0 : sin borde
         * 0 : Lo siguiente en el código va a la derecha (en este caso la segunda celda)
         * 'C' : Texto Centrado
         *  Método para insertar imagen
         *     'images/logo.png' : ruta de la imagen
         *     152 : posición X (recordar que el origen es la esquina superior izquierda)
         *     12 : posición Y
         *     19 : Ancho de la imagen (w)
         *     Nota: Al no especificar el alto de la imagen (h), éste se calcula automáticamente
         * */

        $this->SetX(110);
        $this->Cell(150, 2, utf8_decode($this->titulo . " FOLIO " . $this->Folio), 0, 1, 'L');
        $this->SetTextColor('0', '0', '0'); //para imprimir en rojo
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(150, 10, utf8_decode('Folio Fiscal'), 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->SetX(110);
        $this->Cell(150, 5, utf8_decode($this->FolioFiscal), 0, 1, 'T');
        $this->Ln(3);
        /* $this->SetFont('Arial', 'B', 9);
          $this->SetX(110);
          $this->Cell(50, 5, utf8_decode('Folio'), 0, 0, 'L');
          $this->SetFont('Arial', '', 9);
          $this->Cell(50, 5, utf8_decode($this->Folio), 0, 1, 'L'); */
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Lugar de expedición'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->LugarExpedicion), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Fecha y hora de emisión'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->HoraEmision), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Forma de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->FormaPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Método de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->MetodoPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        if ($this->condicionesPago != "") {                        
            
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Condiciones de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->condicionesPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if ($this->NumeroCtaPago != "") {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('NumCtaPago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->NumeroCtaPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('No de serie CSD emisor:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->CSD_Emisor), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('No de serie CSD SAT:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, $this->CSD_Sat, 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Fecha y hora certificación:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->Fecha_Cert), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        if (isset($this->mes_contrato) && !empty($this->mes_contrato)) {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Mes de contrato'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->mes_contrato), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        /* Codigo para mostrar la clave del cliente */
        $catalogo = new Catalogo();
        $mostrarClaveQuery = "Select ClaveCliente from c_cliente WHERE RFC = '" . $this->RFC_Receptor . "' and verCClientePDF = 1";
        $result = $catalogo->obtenerLista($mostrarClaveQuery);
        if ($rs = mysql_fetch_array($result)) {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Referencia de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($rs['ClaveCliente']), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Tipo de comprobante'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->TipoComprobante), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);

            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(300, 130);
            $x = 10;
            if ($imagen->getRw() < 150) {
                $x = 30;
            }
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }

        $this->Ln(5);

        $this->SetFillColor(210, 203, 203);
        $this->Cell(200, 5, utf8_decode('Emisor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->MultiCell(200, 5, utf8_decode("Nombre: " . $this->nombre_Emisor . "\nRFC: " . $this->RFC_Emisor . "\nRégimen Fiscal: " . $this->regimenFiscal_Emisor . "\nCalle: " . $this->calle_Emisor . " No.Exterior: " . $this->no_Ext_Emisor . " No.Interior: " . $this->no_int_Emisor . "\nColonia: " . $this->colonia_Emisor . " Estado: " . $this->Estado_Emisor . "\nDelegación: " . $this->delegacion_Emisor . " País: " . $this->pais_Emisor . "\nCódigo Postal: " . $this->CP_Emisor . "\nTel: " . $this->Tel_Emisor . "\nPeríodo facturación: " . $this->Periodo_Facturacion_Emisor), 1, 'L', FALSE);
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode('Receptor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);
        $string_receptor = "Nombre: " . $this->nombre_Receptor . "\nRFC: " . $this->RFC_Receptor . "\nUsoCFDI: $this->UsoCFDI\nCalle: " . $this->calle_Receptor . " No.Exterior: " . $this->no_Ext_Receptor . " No.Interior: " . $this->no_int_Receptor . "\nColonia: " . $this->colonia_Receptor . "\nLocalidad:" . $this->localidad . "\nEstado: " . $this->Estado_Receptor . "\nDelegación: " . $this->delegacion_Receptor . " País: " . $this->pais_Receptor . "\nCódigo Postal: " . $this->CP_Receptor;
        if ($this->Addendas != NULL) {
            $contador = 0;
            foreach ($this->Addendas as $value) {
                if ($contador == 0 || $contador % 5 == 0) {
                    $string_receptor .= "\n";
                }
                $string_receptor .= $value[0] . ": " . $value[1] . "      ";
                $contador++;
            }
        }
        $this->MultiCell(200, 5, utf8_decode($string_receptor), 1, 'L', FALSE);
        
        //Para UUIDs relacionados en caso de que existan
        if(isset($this->UUIDRelacionado) && !empty($this->UUIDRelacionado)){
            $this->Ln(2);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(200, 5, utf8_decode('CFDI Relacionados'), 1, 1, 'L', true);
            $this->SetFont('Arial', '', 9);
            $string_rrelacionado = "";
            foreach ($this->UUIDRelacionado as $value) {
                $string_rrelacionado .= "Tipo de relación: " . $this->TipoRelacion."\nFolio Fiscal: $value" ;
            }
            $this->MultiCell(200, 5, utf8_decode($string_rrelacionado), 1, 'L', FALSE);
        }
        
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(20, 5, utf8_decode('Cantidad'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(105, 5, utf8_decode('Descripción'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Importe'), 1, 1, 'C', true);

        for ($i = 0; $i < count($this->tabla); $i++) {
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(120,"(".$array[1].") ".$array[4])); //120 caracteres es lo máximo permitido por línea
            $nb = $nb * 5; //se multiplica por el tamaño de pixeles verticales!
            $this->SetFont('Arial', '', 9);
            if ($array[5] == "1") {//Si es un encabezado                
                $this->Cell(200, 5, utf8_decode(strtoupper($array[4])), 1, 1, 'C', false);
            } else {//Si es un concepto normal   
                if (isset($this->filas[1]) && strlen($this->filas[1]) < 30 && isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0] . $this->filas[1];
                    $j_aux = 2;
                } else if (isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0];
                    $j_aux = 1;
                } else {
                    //$concepto = "1.".$this->filas[1]." -- 2.".$this->filas[2]." -- 3.".$this->filas[3];
                    $concepto = $this->filas[1];
                    $j_aux = 2;
                }
                $nb=max(0,1+(int)($this->GetStringWidth(str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($concepto))))/(105-2*$this->cMargin))) * 5;
                if (strpos($array[0], ',') !== false) {
                    $this->Cell(20, $nb, utf8_decode($array[0]), 1, 0, 'R', false);
                } else {
                    $floatVal = floatval($array[0]);
                    if ($floatVal && intval($floatVal) != $floatVal) {
                        $this->Cell(20, $nb, utf8_decode(number_format((float) $array[0], 4)), 'TLR', 0, 'R', false);
                    } else {
                        $this->Cell(20, $nb, utf8_decode(number_format((int) $array[0])), 'TLR', 0, 'R', false);
                    }//dale
                }
                $this->Cell(25, $nb, utf8_decode(substr($array[2],0,26)), 'TLR', 0, 'C', false);
                $y1 = $this->GetY();
                $x1 = $this->GetX();
                //tendremos que ocupar toda una pagina, en la primer hoja son menos lineas, no? perame estoy haciendo cosas mal                
                $this->MultiCell(105, 5, str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($concepto))), 'TLR', 'L', false);
                $y_aux_final = $this->GetY();
                $this->SetY($y1);
                $this->SetX($x1 + 105);
                $this->Cell(25, $y_aux_final - $y1, utf8_decode("$ " . number_format($array[5], 2)), 'TLR', 0, 'R', false);
                $this->Cell(25, $y_aux_final - $y1, utf8_decode("$ " . number_format($array[6], 2)), 'TLR', 1, 'R', false);                
                //Completamos las líneas si es que hacen falta
                if($nb < ($y_aux_final - $y1)){
                    $this->SetY($y1);
                    $this->Cell(20, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
                    $this->Cell(25, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
                }
                $this->SetY($y_aux_final);

                for ($j = $j_aux; $j < count($this->filas); $j++) {//recorremos hasta la ultima(se puede meter a una funcion)   
                    if (!isset($this->filas[$j]) || empty($this->filas[$j])) {
                        continue;
                    }
                    //$nb=max(0,1+(int)($this->GetStringWidth(str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($this->filas[$j]))))/(105-2*$this->cMargin))) * 5;
                    $this->Cell(20, $nb, utf8_decode(''), 0, 0, 'C', false);
                    $this->Cell(25, $nb, utf8_decode(''), 0, 0, 'C', false); 
                    //$y1 = $this->GetY(); //
                    //$x1 = 40;
                    $y1 = $this->GetY();
                    $x1 = $this->GetX(); 
                    
                    $this->MultiCell(105, 5, utf8_decode($this->filas[$j]), 'LR', 'L', false); //estos conceptos ocuparan una página
                    $y_aux_final = $this->GetY();
                    
                    $this->SetY($y1); //a ver que pasa con un concepto :P
                    $this->Cell(20, $y_aux_final - $y1, utf8_decode(''), 'LR', 0, 'C', false);
                    $this->Cell(25, $y_aux_final - $y1, utf8_decode(''), 'LR', 0, 'C', false); 
                    $this->SetY($y1); //a ver que pasa con un concepto :P
                    $this->SetX($x1 + 105); // ;) (Y) sale te dejo ;) gra
                    $this->Cell(25, $y_aux_final - $y1, utf8_decode(''), 'LR', 0, 'R', false);
                    $this->Cell(25, $y_aux_final - $y1, utf8_decode(''), 'LR', 1, 'R', false); //esta linea   
                }
                
                if(isset($array[8]) && !empty($array[8])){//Si hay descuento por partida                    
                    if(isset($array[9]) && $array[9] == "1"){//Si el descuento es por porcentaje
                        $descuento_partida = $array[6] * ($array[8] / 100);
                    }else{
                        $descuento_partida = $array[8];
                    }                                       
                    $nb = 5;
                    $this->setY($y_aux_final);
                    //Concepto
                    $x1 = $this->GetX();
                    $y1 = $this->GetY(); 
                    if((int)$y1 >= 315){
                        $this->MultiCell($width, 30, "", '', 'L', false);  
                        $y1 = 20;
                    }

                    $this->SetXY($x1,$y1);
                    $x1_inicial = $x1;
                    $this->Cell(45, $nb, utf8_decode(""), 'TLR', 'C', false);
                    $x1+=45;
                    
                    $this->Cell(130, $nb, utf8_decode("Descuento"), 'TLR', 'C', false);
                    $x1+=130;
                    $this->SetXY($x1, $y1);
                    //Importe
                    $this->Cell(25, $nb, utf8_decode("$ " . number_format($descuento_partida, 2)), 'TLR', 'R', 'R'); 
                    $this->SetY($this->GetY() + $nb);
                    $y_aux_final = $this->GetY();
                }
            }
        }

        $this->setY($y_aux_final);
        
        //Esta línea sólo es para poner las líneas que han hecho falta. MAGG 2018-02-08
        $this->Cell(200, 1, utf8_decode(''), 'T', 0, 'L', false);
        $this->SetY($y1);
        if($nb < ($y_aux_final - $y1)){
            $this->Cell(20, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
            $this->Cell(25, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
        }
        $this->SetY($y_aux_final + 1);
        //--Fin
        $this->MultiCell(120, 5, utf8_decode("(" . $this->num_Letra . ")"), 0, 'L', false);
        $this->Cell(40, 5, utf8_decode('Moneda: '.$this->Moneda), 0, 0, 'L', false);
        $size = 120;
        if(isset($this->descuento) && !empty($this->descuento)){
            $this->Cell(120, 5, utf8_decode('Descuento:$'), 0, 0, 'R', false);
            $this->Cell(40, 5, utf8_decode($this->descuento), 0, 1, 'R', false);
            $size = 160;
        }
        $this->Cell($size, 5, utf8_decode('Subtotal:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->subtotal), 0, 1, 'R', false);
        
        $this->Cell(160, 5, utf8_decode('IVA 16%:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->iva), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('Total:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->total), 0, 1, 'R', false);
        $this->ln(3);
        $this->SetFont('Arial', '', 6);
        $this->Cell(200, 5, utf8_decode($this->comentarios), 0, 1, false);
        $y_imagen = $this->GetY();
        $this->Image($this->cbb, $this->GetX(), $this->GetY(),  27.5, 27.5, 'PNG');
        $this->SetX($this->GetX() + 30);
        if (is_array($this->valores)) {
            foreach ($this->valores as $key => $value) {
                $this->Cell(80, 5, utf8_decode($key . ":" . $value), 0, 1, 'C', false);
                $this->SetX($this->GetX() + 30);
            }
        }

        $this->MultiCell(140, 5, utf8_decode(trim($this->leyenda)), 0, 'L', false);
        if($this->GetY() + 15 > ($y_imagen + 30)){
            $this->SetY($this->GetY() + 15);
        }else{
            $this->SetY($y_imagen + 40);
        }
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Cadena original del complemento de certificación del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 5, utf8_decode($this->cadena_SAT), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Sello digital del emisor:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 5, utf8_decode($this->sello_Emisor), 0, false);

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Sello digital del SAT:"), 0, 1, false);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(200, 5, utf8_decode($this->sello_Digital), 0, false);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode("Este documento es una representación impresa de un CFDI."), 0, 1, false);
        //$this->Cell(111,25,'ALGÚN T�?TULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25
    }

    function CrearPDF_PREF33() {
        //Define tipo de letra a usar, Arial, Negrita, 15
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor('255', '0', '0'); //para imprimir en rojo

        /* Líneas paralelas
         * Line(x1,y1,x2,y2)
         * El origen es la esquina superior izquierda
         * Cambien los parámetros y chequen las posiciones
         * */
        //$this->Line(10,10,206,10);
        //$this->Line(10,35.5,206,35.5);

        /* Explicaré el primer Cell() (Los siguientes son similares)
         * 30 : de ancho
         * 25 : de alto
         * ' ' : sin texto
         * 0 : sin borde
         * 0 : Lo siguiente en el código va a la derecha (en este caso la segunda celda)
         * 'C' : Texto Centrado
         *  Método para insertar imagen
         *     'images/logo.png' : ruta de la imagen
         *     152 : posición X (recordar que el origen es la esquina superior izquierda)
         *     12 : posición Y
         *     19 : Ancho de la imagen (w)
         *     Nota: Al no especificar el alto de la imagen (h), éste se calcula automáticamente
         * */

        $this->SetX(110);
        $this->Cell(150, 2, utf8_decode($this->titulo . " FOLIO " . $this->Folio), 0, 1, 'L');
        $this->SetTextColor('0', '0', '0'); //para imprimir en rojo
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(150, 10, utf8_decode('Folio Fiscal'), 0, 1, 'L');
        $this->SetFont('Arial', '', 9);
        $this->SetX(110);
        $this->Cell(150, 5, utf8_decode($this->FolioFiscal), 0, 1, 'T');
        $this->Ln(3);
        /* $this->SetFont('Arial', 'B', 9);
          $this->SetX(110);
          $this->Cell(50, 5, utf8_decode('Folio'), 0, 0, 'L');
          $this->SetFont('Arial', '', 9);
          $this->Cell(50, 5, utf8_decode($this->Folio), 0, 1, 'L'); */
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Lugar de expedición'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->LugarExpedicion), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Fecha y hora de emisión'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->HoraEmision), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Forma de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->FormaPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Método de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->MetodoPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        if ($this->condicionesPago != "") {                                    
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Condiciones de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->condicionesPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if ($this->NumeroCtaPago != "") {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('NumCtaPago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->NumeroCtaPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        /* Codigo para mostrar la clave del cliente */
        $catalogo = new Catalogo();
        $mostrarClaveQuery = "Select ClaveCliente from c_cliente WHERE RFC = '" . $this->RFC_Receptor . "' and verCClientePDF = 1";
        $result = $catalogo->obtenerLista($mostrarClaveQuery);
        if ($rs = mysql_fetch_array($result)) {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Referencia de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($rs['ClaveCliente']), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Tipo de comprobante'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->TipoComprobante), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        
        
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);

            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(300, 130);
            $x = 10;
            if ($imagen->getRw() < 150) {
                $x = 30;
            }
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }

        $this->Ln(5);

        $this->SetFillColor(210, 203, 203);
        $this->Cell(200, 5, utf8_decode('Emisor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->MultiCell(200, 5, utf8_decode("Nombre: " . $this->nombre_Emisor . "\nRFC: " . $this->RFC_Emisor . "\nRégimen Fiscal: " . $this->regimenFiscal_Emisor . "\nCalle: " . $this->calle_Emisor . " No.Exterior: " . $this->no_Ext_Emisor . " No.Interior: " . $this->no_int_Emisor . "\nColonia: " . $this->colonia_Emisor . " Estado: " . $this->Estado_Emisor . "\nDelegación: " . $this->delegacion_Emisor . " País: " . $this->pais_Emisor . "\nCódigo Postal: " . $this->CP_Emisor . "\nTel: " . $this->Tel_Emisor . "\nPeríodo facturación: " . $this->Periodo_Facturacion_Emisor), 1, 'L', FALSE);
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode('Receptor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);
        $string_receptor = "Nombre: " . $this->nombre_Receptor . "\nRFC: " . $this->RFC_Receptor . "\nUsoCFDI: $this->UsoCFDI\nCalle: " . $this->calle_Receptor . " No.Exterior: " . $this->no_Ext_Receptor . " No.Interior: " . $this->no_int_Receptor . "\nColonia: " . $this->colonia_Receptor . "\nLocalidad:" . $this->localidad . "\nEstado: " . $this->Estado_Receptor . "\nDelegación: " . $this->delegacion_Receptor . " País: " . $this->pais_Receptor . "\nCódigo Postal: " . $this->CP_Receptor;
        if ($this->Addendas != NULL) {
            $contador = 0;
            foreach ($this->Addendas as $value) {
                if ($contador == 0 || $contador % 5 == 0) {
                    $string_receptor .= "\n";
                }
                $string_receptor .= $value[0] . ": " . $value[1] . "      ";
                $contador++;
            }
        }
        $this->MultiCell(200, 5, utf8_decode($string_receptor), 1, 'L', FALSE);
        
        //Para UUIDs relacionados en caso de que existan
        if(isset($this->UUIDRelacionado) && !empty($this->UUIDRelacionado)){
            $this->Ln(2);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(200, 5, utf8_decode('CFDI Relacionados'), 1, 1, 'L', true);
            $this->SetFont('Arial', '', 9);
            $string_rrelacionado = "";
            foreach ($this->UUIDRelacionado as $value) {
                $string_rrelacionado .= "Tipo de relación: " . $this->TipoRelacion."\nFolio Fiscal: $value" ;
            }
            $this->MultiCell(200, 5, utf8_decode($string_rrelacionado), 1, 'L', FALSE);
        }
        
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(20, 5, utf8_decode('Cantidad'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(105, 5, utf8_decode('Descripción'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Importe'), 1, 1, 'C', true);

        for ($i = 0; $i < count($this->tabla); $i++) {
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(120, "(".$array[1].") ".$array[4])); //120 caracteres es lo máximo permitido por línea
            $nb = $nb * 5; //se multiplica por el tamaño de pixeles verticales!
            $this->SetFont('Arial', '', 9);
            if ($array[7] == "1") {//Si es un encabezado                
                $this->Cell(200, 5, utf8_decode(strtoupper($array[4])), 1, 1, 'C', false);
            } else {//Si es un concepto normal   
                if (isset($this->filas[1]) && strlen($this->filas[1]) < 30 && isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0] . $this->filas[1];
                    $j_aux = 2;
                } else if (isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0];
                    $j_aux = 1;
                } else {
                    //$concepto = "1.".$this->filas[1]." -- 2.".$this->filas[2]." -- 3.".$this->filas[3];
                    $concepto = $this->filas[1];
                    $j_aux = 2;
                }
                $nb=max(0,1+(int)($this->GetStringWidth(str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($concepto))))/(105 - (2*$this->cMargin) ))) * 5;
                if (strpos($array[0], ',') !== false) {
                    $this->Cell(20, $nb, utf8_decode($array[0]), 1, 0, 'R', false);
                } else {
                    $floatVal = floatval($array[0]);
                    if ($floatVal && intval($floatVal) != $floatVal) {
                        $this->Cell(20, $nb, utf8_decode(number_format((float) $array[0], 4)), 'TLR', 0, 'R', false);
                    } else {
                        $this->Cell(20, $nb, utf8_decode(number_format((int) $array[0])), 'TLR', 0, 'R', false);
                    }//dale
                }
                //MAGG 2018-02-08
                $this->Cell(25, $nb, utf8_decode(substr($array[2],0,26)), 'TLR', 0, 'C', false);
                $y1 = $this->GetY();
                $x1 = $this->GetX();                
                $this->MultiCell(105, 5, str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($concepto))), 'TLR', 'L', false);
                $y_aux_final = $this->GetY();
                $this->SetY($y1);
                $this->SetX($x1 + 105);                
                $this->Cell(25, $y_aux_final - $y1, utf8_decode("$ " . number_format($array[5], 2)), 'TLR', 0, 'R', false);
                $this->Cell(25, $y_aux_final - $y1, utf8_decode("$ " . number_format($array[6], 2)), 'TLR', 1, 'R', false);                
                //Completamos las líneas si es que hacen falta
                if($nb < ($y_aux_final - $y1)){
                    $this->SetY($y1);
                    $this->Cell(20, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
                    $this->Cell(25, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
                }
                $this->SetY($y_aux_final);
                
                for ($j = $j_aux; $j < count($this->filas); $j++) {//recorremos hasta la ultima(se puede meter a una funcion)   
                    if (!isset($this->filas[$j]) || empty($this->filas[$j])) {
                        continue;
                    }
                    //$nb=max(0,1+(int)($this->GetStringWidth(str_replace("&amp;","&",str_replace("&quot;", '"', utf8_decode($this->filas[$j]))))/(105-2*$this->cMargin))) * 5;
                    $this->Cell(20, $nb, utf8_decode(''), 0, 0, 'C', false);
                    $this->Cell(25, $nb, utf8_decode(''), 0, 0, 'C', false); 
                    //$y1 = $this->GetY(); //
                    //$x1 = 40;
                    $y1 = $this->GetY();
                    $x1 = $this->GetX(); 
                    
                    $this->MultiCell(105, 5, utf8_decode($this->filas[$j]), 'LR', 'L', false); //estos conceptos ocuparan una página
                    $y_aux_final = $this->GetY();
                    
                    $this->SetY($y1); //a ver que pasa con un concepto :P
                    $this->Cell(20, $y_aux_final - $y1, utf8_decode(''), 'LR', 0, 'C', false);
                    $this->Cell(25, $y_aux_final - $y1, utf8_decode(''), 'LR', 0, 'C', false); 
                    $this->SetY($y1); //a ver que pasa con un concepto :P
                    $this->SetX($x1 + 105); // ;) (Y) sale te dejo ;) gra
                    $this->Cell(25, $y_aux_final - $y1, utf8_decode(''), 'LR', 0, 'R', false);
                    $this->Cell(25, $y_aux_final - $y1, utf8_decode(''), 'LR', 1, 'R', false); //esta linea                                    
                }//en teoría solo debería poner un concepto si lo pruebas ahorita
                
                if(isset($array[8]) && !empty($array[8])){//Si hay descuento por partida                    
                    if(isset($array[9]) && $array[9] == "1"){//Si el descuento es por porcentaje
                        $descuento_partida = $array[6] * ($array[8] / 100);
                    }else{
                        $descuento_partida = $array[8];
                    }                                        
                    $nb = 5;
                    $this->setY($y_aux_final);
                    //Concepto
                    $x1 = $this->GetX();
                    $y1 = $this->GetY(); 
                    if((int)$y1 >= 315){
                        $this->MultiCell($width, 30, "", '', 'L', false);  
                        $y1 = 20;
                    }

                    $this->SetXY($x1,$y1);
                    $x1_inicial = $x1;
                    $this->Cell(45, $nb, utf8_decode(""), 'TLR', 'C', false);
                    $x1+=45;
                    
                    $this->Cell(130, $nb, utf8_decode("Descuento"), 'TLR', 'C', false);
                    $x1+=130;
                    $this->SetXY($x1, $y1);
                    //Importe
                    $this->Cell(25, $nb, utf8_decode("$ " . number_format($descuento_partida, 2)), 'TLR', 'R', 'R'); 
                    $this->SetY($this->GetY() + $nb);
                    $y_aux_final = $this->GetY();
                }
            }
        }

        $this->setY($y_aux_final);
        
        //Esta línea sólo es para poner las líneas que han hecho falta. MAGG 2018-02-08
        $this->Cell(200, 1, utf8_decode(''), 'T', 0, 'L', false);
        $this->SetY($y1);
        if($nb < ($y_aux_final - $y1)){
            $this->Cell(20, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
            $this->Cell(25, ($y_aux_final - $y1), utf8_decode(''), 'LR', 0, 'L', false);
        }        
        $this->SetY($y_aux_final + 1);
        // ----- Fin
        $this->MultiCell(120, 5, utf8_decode("(" . $this->num_Letra . ")"), 0, 'L', false);
        $this->Cell(40, 5, utf8_decode('Moneda: '.$this->Moneda), 0, 0, 'L', false);
        $size = 120;
        if(isset($this->descuento) && !empty($this->descuento)){
            $this->Cell(120, 5, utf8_decode('Descuento:$'), 0, 0, 'R', false);
            $this->Cell(40, 5, utf8_decode($this->descuento), 0, 1, 'R', false);
            $size = 160;
        } 
        $this->Cell($size, 5, utf8_decode('Subtotal:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->subtotal), 0, 1, 'R', false);
               
        $this->Cell(160, 5, utf8_decode('IVA 16%:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->iva), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('Total:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->total), 0, 1, 'R', false);
        $this->ln(3);
        $this->SetFont('Arial', '', 6);
        $this->Cell(200, 5, utf8_decode($this->comentarios), 0, 1, false);

        $this->SetFont('Arial', '', 10);
        if (!isset($this->ndc) || !$this->ndc) {
            $this->Cell(200, 5, utf8_decode("Esta es una prefactura."), 0, 1, false);
        } else {
            $this->Cell(200, 5, utf8_decode("Esta es un previo de nota de crédito."), 0, 1, false);
        }
        //$this->Cell(111,25,'ALGÚN T�?TULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25


    }
    
    function CrearPDF_PREF() { //Encabezado 
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor('255', '0', '0'); //para imprimir en rojo        
        $this->SetX(110);
        $this->Cell(50, 2, utf8_decode($this->titulo . ' FOLIO ' . $this->Folio), 0, 1, 'L');
        $this->SetTextColor('0', '0', '0'); //
        $this->SetFont('Arial', 'B', 9);
        $this->Ln(3);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Lugar de expedición'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->LugarExpedicion), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Fecha y hora de emisión'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->HoraEmision), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Forma de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->FormaPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(110);
        $this->Cell(50, 5, utf8_decode('Método de pago'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, utf8_decode($this->MetodoPago), 0, 1, 'L');
        $this->SetFont('Arial', 'B', 9);
        if ($this->condicionesPago != "") {
            $this->SetX(110);
            
            $this->Cell(50, 5, utf8_decode('Condiciones de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->condicionesPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if (isset($this->mes_contrato) && !empty($this->mes_contrato)) {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Mes de contrato'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->mes_contrato), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if ($this->NumeroCtaPago != "") {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('NumCtaPago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($this->NumeroCtaPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        /* Codigo para mostrar la clave del cliente */
        $catalogo = new Catalogo();
        $mostrarClaveQuery = "Select ClaveCliente from c_cliente WHERE RFC = '" . $this->RFC_Receptor . "' and verCClientePDF = 1";
        $result = $catalogo->obtenerLista($mostrarClaveQuery);
        if ($rs = mysql_fetch_array($result)) {
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('Referencia de pago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);
            $this->Cell(50, 5, utf8_decode($rs['ClaveCliente']), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);
            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(300, 120);
            $x = 10;
            if ($imagen->getRw() < 150) {
                $x = 30;
            }
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x, 10, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }

        $this->Ln(25);
        $this->SetFillColor(210, 203, 203);
        $this->Cell(200, 5, utf8_decode('Emisor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->MultiCell(200, 5, utf8_decode("Nombre: " . $this->nombre_Emisor . "\nRFC: " . $this->RFC_Emisor . "\nRégimen Fiscal: " . $this->regimenFiscal_Emisor . "\nCalle: " . $this->calle_Emisor . " No.Exterior: " . $this->no_Ext_Emisor . " No.Interior: " . $this->no_int_Emisor . "\nColonia: " . $this->colonia_Emisor . " Estado: " . $this->Estado_Emisor . "\nDelegación: " . $this->delegacion_Emisor . " País: " . $this->pais_Emisor . "\nCódigo Postal: " . $this->CP_Emisor . "\nTel: " . $this->Tel_Emisor . "\nPeríodo facturación: " . $this->Periodo_Facturacion_Emisor), 1, 'L', FALSE);
        $this->Ln(2);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(200, 5, utf8_decode('Receptor'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);
        $string_receptor = "Nombre: " . $this->nombre_Receptor . "\nRFC: " . $this->RFC_Receptor . "\nCalle: " . $this->calle_Receptor . " No.Exterior: " . $this->no_Ext_Receptor . " No.Interior: " . $this->no_int_Receptor . "\nColonia: " . $this->colonia_Receptor . "\nLocalidad:" . $this->localidad . "\nEstado: " . $this->Estado_Receptor . "\nDelegación: " . $this->delegacion_Receptor . " País: " . $this->pais_Receptor . "\nCódigo Postal: " . $this->CP_Receptor;
        if ($this->Addendas != NULL) {
            $contador = 0;
            foreach ($this->Addendas as $value) {
                if ($contador == 0 || $contador % 5 == 0) {
                    $string_receptor .= "\n";
                }
                $string_receptor .= $value[0] . ": " . $value[1] . "      ";
                $contador++;
            }
        }
        $this->MultiCell(200, 5, utf8_decode($string_receptor), 1, 'L', FALSE);
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(15, 5, utf8_decode('Cantidad'), 1, 0, 'C', true);
        $this->Cell(15, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(120, 5, utf8_decode('Descripción'), 1, 0, 'C', true); //de aqui se obtiene el 120, es una tabla 200 es lo máximo lo demás queda repartido
        $this->Cell(25, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Importe'), 1, 1, 'C', true);
        //print_r($this->filas);
        //print_r($this->tabla);       

        for ($i = 0; $i < count($this->tabla); $i++) {
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(120, $array[2])); //120 caracteres es lo máximo permitido por línea
            $nb = $nb * 5; //se multiplica por el tamaño de pixeles verticales!
            $this->SetFont('Arial', '', 9);
            if ($array[5] == "1") {//Si es un encabezado                
                $this->Cell(200, 5, utf8_decode(strtoupper($array[2])), 1, 1, 'C', false);
            } else {//Si es un concepto normal   
                if (isset($this->filas[1]) && strlen($this->filas[1]) < 30 && isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0] . $this->filas[1];
                    $j_aux = 2;
                } else if (isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0];
                    $j_aux = 1;
                } else {
                    //$concepto = "1.".$this->filas[1]." -- 2.".$this->filas[2]." -- 3.".$this->filas[3];
                    $concepto = $this->filas[1];
                    $j_aux = 2;
                }
                //$nb = max(0, $this->NbLinesSimple(120, $concepto)) * 5;
                $nb = max(0, $this->NbLines2(100, $concepto)) * 5;  
                if (strpos($array[0], ',') !== false) {
                    $this->Cell(15, $nb, utf8_decode($array[0]), 1, 0, 'C', false);
                } else {
                    $floatVal = floatval($array[0]);
                    if ($floatVal && intval($floatVal) != $floatVal) {
                        $this->Cell(15, $nb, utf8_decode(number_format((float) $array[0], 4)), 1, 0, 'C', false);
                    } else {
                        $this->Cell(15, $nb, utf8_decode(number_format((int) $array[0])), 1, 0, 'C', false);
                    }//dale
                }
                $this->Cell(15, $nb, utf8_decode($array[1]), 1, 0, 'C', false);
                $y1 = $this->GetY();
                $x1 = $this->GetX();
                //tendremos que ocupar toda una pagina, en la primer hoja son menos lineas          
                $this->MultiCell(120, 5, utf8_decode($concepto), 1, 'L', false);
                $this->SetY($y1);
                $this->SetX($x1 + 120);
                //echo "<br/><b>Escribiendo en Y ".  $this->GetY()."</b> -- ".$this->filas[0];
                $this->Cell(25, $nb, utf8_decode("$ " . number_format($array[3], 2)), 1, 0, 'R', false);
                $this->Cell(25, $nb, utf8_decode("$ " . number_format($array[4], 2)), 1, 1, 'R', false);


                for ($j = $j_aux; $j < count($this->filas); $j++) {//recorremos hasta la ultima(se puede meter a una funcion)   
                    if (!isset($this->filas[$j]) || empty($this->filas[$j])) {
                        continue;
                    }
                    $nb = max(0, $this->NbLinesSimple(120, $this->filas[$j])) * 5;
                    $this->Cell(15, $nb, utf8_decode(''), 1, 0, 'C', false);
                    $this->Cell(15, $nb, utf8_decode(''), 1, 0, 'C', false); //prueba porque no se que pase xD
                    $y1 = $this->GetY(); //
                    $x1 = 40;
                    //echo "<br/><b>Escribiendo 2 en Y ".  $this->GetY()."</b> -- ".$this->filas[$j];
                    $this->MultiCell(120, 5, utf8_decode($this->filas[$j]), 1, 'L', false); //estos conceptos ocuparan una página
                    $this->SetY($y1); //a ver que pasa con un concepto :P
                    $this->SetX($x1 + 120); // ;) (Y) sale te dejo ;) gra
                    $this->Cell(25, $nb, utf8_decode(''), 1, 0, 'R', false);
                    $this->Cell(25, $nb, utf8_decode(''), 1, 1, 'R', false); //esta linea 
                    $this->SetY($this->GetY() + $nb + 10);
                }
            }
        }
        //$y1 = $this->GetY();
        //$x1 = $this->GetX();
        $this->MultiCell(120, 3, utf8_decode("(" . $this->num_Letra . ")"), 0, 'L', false);
        //$this->SetY($y1-340);
        //$this->SetX($x1 + 120);
        $this->Cell(160, 5, utf8_decode('Subtotal:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->subtotal), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('IVA 16%:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->iva), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('Total:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->total), 0, 1, 'R', false);
        $this->ln(3);
        $this->Cell(200, 5, utf8_decode($this->comentarios), 0, 1, false);
        $this->ln(3);
        if (!isset($this->ndc) || !$this->ndc) {
            $this->Cell(200, 5, utf8_decode("Esta es una prefactura."), 0, 1, false);
        } else {
            $this->Cell(200, 5, utf8_decode("Esta es un previo de nota de crédito."), 0, 1, false);
        }
        //$this->Cell(111,25,'ALGÚN T�?TULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25
    }

    function CrearPDF_DetalleSerie($prefactura) { //Encabezado 
        //Nombre de empresa que factura
        $x = 80;
        $this->SetX($x);
        $y = $this->GetY();
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 5, utf8_decode($this->nombre_Emisor), 0, 1, false);
        //Titulo de factura, nr o ndc
        $x = 170;
        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'BU', 9);
        $this->MultiCell(25, 5, utf8_decode($this->titulo), 0, 'C', false);
        //Direccion de la empresa que factura
        $x = 75;
        $this->SetX($x);
        $y = $this->GetY();
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode($this->calle_Emisor . " " . $this->no_Ext_Emisor . " " . $this->no_int_Emisor . " " . $this->colonia_Emisor . ", " . $this->delegacion_Emisor . ", " . $this->Estado_Emisor . "\n" . $this->pais_Emisor . " CP. " . $this->CP_Emisor), 0, 'C', FALSE);
        //Folio del documento
        $x = 168;
        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor('255', '0', '0'); //para imprimir en rojo   
        $this->MultiCell(30, 5, utf8_decode($this->Folio), 0, 'C', false);
        //Folio Fiscal
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor('0', '0', '0'); //para imprimir en rojo           
        $this->Cell(30, 4, utf8_decode("Folio Fiscal:"), 0, 1, 'C', false);
        $y = $this->GetY();
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->FolioFiscal), 0, 1, 'C', FALSE);
        //Serie del certificado del SAT
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Nº de Serie del Certificado del SAT:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->CSD_Sat), 0, 1, 'C',FALSE);
        //Serie del certificado del Emisor
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Nº de Serie del Certificado del Emisor:"), 0, 1, 'C', FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->CSD_Emisor), 0, 1, 'C',FALSE);
        //Fecha y hora de emisión
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $y_aux = $this->GetY();
        $this->Cell(30, 4, utf8_decode("Fecha y hora de emisión:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->HoraEmision), 0, 1, 'C',FALSE);
        //Fecha y hora de certificación
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Fecha y hora de certificación:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->Fecha_Cert), 0, 1, 'C',FALSE);
        //Lugar de elaaboracion
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Lugar de Elaboración:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->LugarExpedicion), 0, 1, 'C',FALSE);
        //RFC Empresa que factura
        $x = 65;
        $this->Ln(3);
        $this->SetXY($x, $y);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode($this->RFC_Emisor), 0, 'C', FALSE);
        //Telefono Empresa que factura
        $this->Ln(1);
        $this->SetX($x);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode("Tel: " . $this->Tel_Emisor), 0, 'C', FALSE);
        //Regimen Empresa que factura
        $this->Ln(1);
        $this->SetX($x);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode($this->regimenFiscal_Emisor), 0, 'C', FALSE);

        $this->SetY($y_aux);
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);
            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(300, 120);
            $x = 10;
            $y_img = 5;
            if ($imagen->getRw() < 150) {
                $x = 30;
            } else if($imagen->getRw() > 250){
                $imagen->resize(200, 220);
                $y_img = 15;
            }
            //echo $imagen->getRh()." - ".$imagen->getRw();
            
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, $y_img, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x,$y_img, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }

        $width = 150;
        $this->SetFont('Arial', '', 8);
        $string_receptor = "Cliente: " . $this->nombre_Receptor . "         RFC: " . $this->RFC_Receptor .
                "\n\nDirección: " . $this->calle_Receptor . " " . $this->no_Ext_Receptor . " ," . $this->no_int_Receptor . ", " . $this->colonia_Receptor . ", " . $this->delegacion_Receptor . "\n" . $this->Estado_Receptor . ", " . $this->pais_Receptor . ", C.P. " . $this->CP_Receptor;
        if ($this->Addendas != NULL) {
            $contador = 0;
            foreach ($this->Addendas as $value) {
                if ($contador == 0 || $contador % 5 == 0) {
                    $string_receptor .= "\n";
                }
                $string_receptor .= $value[0] . ": " . $value[1] . "      ";
                $contador++;
            }
        }
        $this->Ln(4);
        $this->MultiCell($width, 5, utf8_decode($string_receptor), 1, 'L', FALSE);
        $this->Ln(1);

        $dias_credito = "";
        if(isset($this->IdPreFactura) && !empty($this->IdPreFactura)){  
            $factura = new Factura();
            $factura->setIdFactura($this->IdPreFactura);
            if($factura->getRegistrobyID() && $factura->getDiasCredito() != ""){
                $dias_credito = $factura->getDiasCredito()." días";
            }
        }
        
        
        $x = $this->GetX();
        $width = 67;
        $this->Cell($width, 5, utf8_decode('Condiciones de pago'), 1, 0, 'C', false);
        $this->Cell($width, 5, utf8_decode('Método de pago'), 1, 0, 'C', false);
        $y = $this->GetY();
        $this->MultiCell($width-1, 5, utf8_decode('Nº cliente: ' . $this->ClaveCliente . "\nContrato: ".$this->contrato."\nPeriodo de facturación: ".$this->Periodo_Facturacion_Emisor."\n  "), 1, 'L', false);
        $y+=5;
        $this->SetXY($x, $y);
        $this->MultiCell($width, 5, utf8_decode($this->FormaPago ."\nCrédito: ".$dias_credito), 1, 'L', false);
        $this->SetXY($x + $width, $y);
        $this->MultiCell($width, 5, utf8_decode($this->MetodoPago . "\nCuenta: " . $this->NumeroCtaPago), 1, 'L', false);

        $this->SetFillColor(210, 203, 203);
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(15, 5, utf8_decode('Cantidad'), 1, 0, 'C', true);
        $this->Cell(15, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(120, 5, utf8_decode('Descripción'), 1, 0, 'C', true); //de aqui se obtiene el 120, es una tabla 200 es lo máximo lo demás queda repartido
        $this->Cell(25, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Importe'), 1, 1, 'C', true);
        //print_r($this->filas);
        //print_r($this->tabla);       

        for ($i = 0; $i < count($this->tabla); $i++) {
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(120, $array[2])); //120 caracteres es lo máximo permitido por línea
            $nb = $nb * 5; //se multiplica por el tamaño de pixeles verticales!
            $this->SetFont('Arial', '', 9);
            if ($array[5] == "1") {//Si es un encabezado                
                $this->Cell(200, 5, utf8_decode(strtoupper($array[2])), 1, 1, 'C', false);
            } else {//Si es un concepto normal                                   
                if(isset($this->IdPreFactura) && !empty($this->IdPreFactura)){                   
                    $x1 = $this->GetX();
                    $y1 = $this->GetY();                 
                    $this->SetXY($x1,$y1);
                    $x1_inicial = $x1;
                
                    $string_series = "";
                    $nb_aux = 0;
                    foreach ($series as $value) {
                        $string_series.= "'$value',";
                    }
                    if(!empty($string_series)){
                        $string_series = substr($string_series, 0, strlen($string_series)-1);
                    }
                    
                    $catalogo = new Catalogo();
                    $consulta = "SELECT b.NoSerie, e.Modelo, cc.Nombre AS Ubicacion,fd.ContadorBNAnterior,fd.ContadorBN,fd.Ubicacion AS Area,f.MostrarUbicacion,
                            fd.ContadorProcesadasBN, fd.ContadorProcesadasColor,
                            fd.ContadorColorAnterior,fd.ContadorColor
                            FROM `c_facturadetalle` AS fd
                            LEFT JOIN c_bitacora AS b ON b.id_bitacora = fd.IdBitacora
                            LEFT JOIN c_inventarioequipo AS cie ON cie.NoSerie = b.NoSerie
                            LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
                            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = fd.ClaveCentroCosto
                            LEFT JOIN c_factura AS f ON f.IdFactura = fd.IdFactura
                            WHERE fd.IdFactura = $this->IdPreFactura AND fd.NumeroPartida = ".($i+1)." ORDER BY cc.Nombre;";
                    $result = $catalogo->obtenerLista($consulta);
                    
                    //$y1 += $nb;
                    $x1 = $x1_inicial;
                    if((int)$y1 >= 315){
                        $this->MultiCell($width, 30, "", '', 'L', false);  
                        $y1 = 20;
                    }
                    $this->SetXY($x1, $y1);
                    if(mysql_num_rows($result) > 0){
                        $nb_aux = 5;
                        //Aquí se ponen el detalle de cada fila
                        $this->SetFont('Arial', 'BU', 7);                        
                        $width = 20;
                        $this->MultiCell($width, $nb_aux, utf8_decode("Serie"), 'L', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Modelo"), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 28;
                        $this->MultiCell($width, $nb_aux, utf8_decode("Localidad"), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 22;
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Ant. BN"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Act. BN"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Pág. Proc."), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Ant. Color"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Act. Color"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Pág. Proc."), 'R', 'C', false);
                    }
                                 
                    $contador_result = 0;
                    while($rs = mysql_fetch_array($result)){
                        $contador_result++;
                        $this->SetFont('Arial', '', 6);                        
                        $y1 += $nb_aux;
                        
                        if((int)$y1 >= 315){
                            $this->MultiCell($width, 30, "", '', 'L', false);  
                            $y1 = 20;
                        }
                        
                        $x1 = $x1_inicial;
                        $this->SetXY($x1, $y1);
                        $width = 20;
                        
                        $nb_aux = 5;
                        if(strlen($rs['Ubicacion']) > 12 || strlen($rs['Modelo']) > 12 ){
                            $nb_aux += 10;
                        }                    
                        
                        $this->MultiCell($width, $nb_aux, utf8_decode($rs['NoSerie']), 'L', 'L', false);  
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, 5, utf8_decode($rs["Modelo"]), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 28;
                        if(strlen($rs['Ubicacion']) > 40){
                            $aux = "...";
                        }else{
                            $aux = "";
                        }
                        $this->MultiCell($width, 5, utf8_decode(substr($rs["Ubicacion"], 0, 40).$aux), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 22;
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorBNAnterior'],0)), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorBN'],0)), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorProcesadasBN'],0) ), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorColorAnterior'],0) ), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorColor'],0) ), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorProcesadasColor'],0)), 'R', 'C', false);
                        
                        if($rs['MostrarUbicacion'] == "1" && !empty($rs['Area']) && $rs['Area'] != " "){
                            $this->SetFont('Arial', '', 6);  
                            $espacio_ubicacin = 7;
                            $y1 += $espacio_ubicacin;

                            if((int)$y1 >= 315){
                                $this->MultiCell($width, 30, "", '', 'L', false);  
                                $y1 = 20;
                            }

                            $x1 = $x1_inicial;
                            $this->SetXY($x1, $y1);
                            $width = 200;

                            $nb_aux_2 = 5;
                            
                            if(mysql_num_rows($result) > $contador_result){
                                $y1 -= $espacio_ubicacin;
                            }else{
                                $nb_aux_2 = $nb_aux - $espacio_ubicacin;
                                $y1 -= 5;
                            }
                            
                            $this->MultiCell($width, $nb_aux_2, utf8_decode("Ubicación: ".$rs['Area']), 'LR', 'L', false);
                        }
                    }
                    //Lineas separadoras
                    $y1 += $nb_aux;
                    $this->SetXY($x1_inicial, $y1);
                    $this->MultiCell(200, 0, "", 'B', 'C', false);
                }
                
                $this->SetFont('Arial', '', 9);
                if (isset($this->filas[1]) && strlen($this->filas[1]) < 30 && isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0] . $this->filas[1];
                    $j_aux = 2;
                } else if (isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0];
                    $j_aux = 1;
                } else {
                    //$concepto = "1.".$this->filas[1]." -- 2.".$this->filas[2]." -- 3.".$this->filas[3];
                    $concepto = $this->filas[1];
                    $j_aux = 2;
                }
                $nb = max(0, $this->NbLines2(100, $concepto)) * 5;                
                //Cantidad
                $x1 = $this->GetX();
                $y1 = $this->GetY(); 
                if((int)$y1 >= 315){
                    $this->MultiCell($width, 30, "", '', 'L', false);  
                    $y1 = 20;
                }
                
                $this->SetXY($x1,$y1);
                $x1_inicial = $x1;
                if (strpos($array[0], ',') !== false) {
                    $this->MultiCell(15, $nb, utf8_decode($array[0]), 'BL', 'C', false);
                } else {
                    $floatVal = floatval($array[0]);                    
                    if ($floatVal && intval($floatVal) != $floatVal) {                                                
                        $this->MultiCell(15, $nb, utf8_decode(number_format((float) $array[0], 4)), 'BL', 'C', false);
                    } else {                       
                        $this->MultiCell(15, $nb, utf8_decode(number_format((int) $array[0])), 'BL', 'C', false);
                    }//dale
                }              
                
                //Unidad
                if($y1 >= (int)315){
                    $y1 = 10;
                }
                $x1 += 15;
                
                $this->SetXY($x1,$y1);
                $this->MultiCell(15, $nb, utf8_decode($array[1]), 'B', 'C', false);                
                //Concepto
                $x1+=15;
                $this->SetXY($x1,$y1);
                //tendremos que ocupar toda una pagina, si en la primer hoja son menos lineas
                $this->MultiCell(120, 5, utf8_decode($concepto), 'B', 'L', false);
                $x1+=120;
                $this->SetXY($x1,$y1);
                
                //Precio Unitario
                $this->MultiCell(25, $nb, utf8_decode("$ " . number_format($array[3], 2)), 'B', 'R', false);
                $x1+=25;
                $this->SetXY($x1, $y1);
                //Importe
                $this->MultiCell(25, $nb, utf8_decode("$ " . number_format($array[4], 2)), 'BR', 'R', false);                                                
                
                for ($j = $j_aux; $j < count($this->filas); $j++) {//recorremos hasta la ultima(se puede meter a una funcion)   
                    if (!isset($this->filas[$j]) || empty($this->filas[$j])) {
                        continue;
                    }
                    $nb = max(0, $this->NbLinesSimple(120, $this->filas[$j])) * 5;
                    $x1 = $this->GetX();
                    $y1 = $this->GetY();
                    $this->MultiCell(15, $nb, utf8_decode(''), 'LB', 'C', false);
                    //Unidad
                    if($y1 >= (int)320){
                        $y1 = 10;
                    }
                    $x1+=15;
                    $this->SetXY($x1, $y1);
                    $this->MultiCell(15, $nb, utf8_decode(''), 'B', 'C', false); 
                    $x1+=15;
                    $this->SetXY($x1, $y1);                    
                    //echo "<br/><b>Escribiendo 2 en Y ".  $this->GetY()."</b> -- ".$this->filas[$j];
                    $this->MultiCell(120, 5, utf8_decode($this->filas[$j]), 'B', 'L', false); //estos conceptos ocuparan una página
                    $x1+=120;
                    $this->SetXY($x1, $y1);
                    $this->MultiCell(25, $nb, utf8_decode(''), 'B', 'R', false);
                    $x1+=25;
                    $this->SetXY($x1, $y1);
                    $this->MultiCell(25, $nb, utf8_decode(''), 'RB', 'R', false); //esta linea 
                    $this->SetY($this->GetY() + $nb + 5);
                }
            }
        }
        $this->SetY($this->GetY() + $nb + 5);
        //$y1 = $this->GetY();
        //$x1 = $this->GetX();
        $this->MultiCell(120, 3, utf8_decode("(" . $this->num_Letra . ")"), 0, 'L', false);
        //$this->SetY($y1-340);
        //$this->SetX($x1 + 120);
        $this->Cell(160, 5, utf8_decode('Subtotal:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->subtotal), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('IVA 16%:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->iva), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('Total:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->total), 0, 1, 'R', false);
        $this->ln(3);
        $this->Cell(200, 5, utf8_decode($this->comentarios), 0, 1, false);
        $this->ln(3);
        if ($prefactura) {
            if (!isset($this->ndc) || !$this->ndc) {
                $this->Cell(200, 5, utf8_decode("Esta es una prefactura."), 0, 1, false);
            } else {
                $this->Cell(200, 5, utf8_decode("Esta es un previo de nota de crédito."), 0, 1, false);
            }
        } else {
            $y1 = $this->GetY();
            $x1 = $this->GetX();
            if($y1 >= (int)320){
                $this->AddPage();
                $y1 = 10;
            }
            
            $this->SetXY($x1, $y1);
            $this->Image($this->cbb, $this->GetX(), $this->GetY(), 30, 30, 'PNG');
            $this->SetX($this->GetX() + 30);
            if (is_array($this->valores)) {
                foreach ($this->valores as $key => $value) {
                    $this->Cell(80, 5, utf8_decode($key . ":" . $value), 0, 1, 'C', false);
                    $this->SetX($this->GetX() + 30);
                }
            }

            $this->MultiCell(140, 5, utf8_decode(trim($this->leyenda)), 0, 'L', false);
            $this->SetY($this->GetY() + 30);
            $this->SetFont('Arial', 'B', 7);
            $this->Cell(200, 5, utf8_decode("Cadena original del complemento de certificación del SAT:"), 0, 1, false);
            $this->SetFont('Arial', '', 5);
            $this->MultiCell(200, 5, utf8_decode($this->cadena_SAT), 0, false);

            $this->SetFont('Arial', 'B', 7);
            $this->Cell(200, 5, utf8_decode("Sello digital del emisor:"), 0, 1, false);
            $this->SetFont('Arial', '', 5);
            $this->MultiCell(200, 5, utf8_decode($this->sello_Emisor), 0, false);

            $this->SetFont('Arial', 'B', 7);
            $this->Cell(200, 5, utf8_decode("Sello digital del SAT:"), 0, 1, false);
            $this->SetFont('Arial', '', 5);
            $this->MultiCell(200, 5, utf8_decode($this->sello_Digital), 0, false);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(200, 5, utf8_decode("Este documento es una representación impresa de un CFDI."), 0, 1, false);
        }
        //$this->Cell(111,25,'ALGÚN T�?TULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25
    }
    
    function CrearPDF_DetalleSerie33($prefactura) { //Encabezado 
        //Nombre de empresa que factura
        $x = 80;
        $this->SetX($x);
        $y = $this->GetY();
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 5, utf8_decode($this->nombre_Emisor), 0, 1, false);
        //Titulo de factura, nr o ndc
        $x = 170;
        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'BU', 9);
        $this->MultiCell(25, 5, utf8_decode($this->titulo), 0, 'C', false);
        //Direccion de la empresa que factura
        $x = 75;
        $this->SetX($x);
        $y = $this->GetY();
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode($this->calle_Emisor . " " . $this->no_Ext_Emisor . " " . $this->no_int_Emisor . " " . $this->colonia_Emisor . ", " . $this->delegacion_Emisor . ", " . $this->Estado_Emisor . "\n" . $this->pais_Emisor . " CP. " . $this->CP_Emisor), 0, 'C', FALSE);
        //Folio del documento
        $x = 168;
        $this->SetXY($x, $y);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor('255', '0', '0'); //para imprimir en rojo   
        $this->MultiCell(30, 5, utf8_decode($this->Folio), 0, 'C', false);
        //Folio Fiscal
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->SetTextColor('0', '0', '0'); //para imprimir en rojo           
        $this->Cell(30, 4, utf8_decode("Folio Fiscal:"), 0, 1, 'C', false);
        $y = $this->GetY();
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->FolioFiscal), 0, 1, 'C', FALSE);
        //Serie del certificado del SAT
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Nº de Serie del Certificado del SAT:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->CSD_Sat), 0, 1, 'C',FALSE);
        //Serie del certificado del Emisor
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Nº de Serie del Certificado del Emisor:"), 0, 1, 'C', FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->CSD_Emisor), 0, 1, 'C',FALSE);
        //Fecha y hora de emisión
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $y_aux = $this->GetY();
        $this->Cell(30, 4, utf8_decode("Fecha y hora de emisión:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->HoraEmision), 0, 1, 'C',FALSE);
        //Fecha y hora de certificación
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Fecha y hora de certificación:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->Fecha_Cert), 0, 1, 'C',FALSE);
        //Lugar de elaboracion
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Lugar de Expedición:"), 0, 1, 'C',FALSE);
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->LugarExpedicion), 0, 1, 'C',FALSE);
        //Lugar de elaboracion
        $this->SetX($x);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(30, 4, utf8_decode("Tipo de comprobante: "), 0, 1, 'C',FALSE);  
        $this->SetFont('Arial', '', 7);
        $this->SetX($x);
        $this->Cell(30, 4, utf8_decode($this->TipoComprobante), 0, 1, 'C',FALSE);
        $y_tipo_comprobante = $this->getY();
        //Contrato
        if (isset($this->mes_contrato) && !empty($this->mes_contrato)) {
            $this->SetX($x);
            $this->SetFont('Arial', 'B', 7);
            $this->Cell(30, 4, utf8_decode('Mes de contrato'), 0, 1, 'C',FALSE);
            $this->SetFont('Arial', '', 7);
            $this->Cell(30, 4, utf8_decode($this->mes_contrato), 0, 1, 'C',FALSE);
        }
        //RFC Empresa que factura
        $x = 65;
        $this->Ln(3);
        $this->SetXY($x, $y);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode($this->RFC_Emisor), 0, 'C', FALSE);
        //Telefono Empresa que factura
        $this->Ln(1);
        $this->SetX($x);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode("Tel: " . $this->Tel_Emisor), 0, 'C', FALSE);
        //Regimen Empresa que factura
        $this->Ln(1);
        $this->SetX($x);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(85, 4, utf8_decode($this->regimenFiscal_Emisor), 0, 'C', FALSE);

        $this->SetY($y_aux);
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);
            //Se trata de ajustar la imagen para que no se distorsione
            $imagen = new Imagen('../../../LOGOS/' . $this->logo);
            $imagen->resize(300, 120);
            $x = 10;
            $y_img = 5;
            if ($imagen->getRw() < 150) {
                $x = 30;
            } else if($imagen->getRw() > 250){
                $imagen->resize(200, 220);
                $y_img = 15;
            }
            //echo $imagen->getRh()." - ".$imagen->getRw();
            
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, $x, $y_img, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, $x,$y_img, $imagen->getRw() / 3.2, $imagen->getRh() / 3.2);
            }
        } else {
            //echo "Se genero la factura sin logo de empresa";
        }

        $width = 150;
        $this->SetFont('Arial', '', 8);
        $string_receptor = "Cliente: " . $this->nombre_Receptor . "         RFC: " . $this->RFC_Receptor .
                "\nUsoCFDI: $this->UsoCFDI\nDirección: " . $this->calle_Receptor . " " . $this->no_Ext_Receptor . " ," . $this->no_int_Receptor . ", " . $this->colonia_Receptor . ", " . $this->delegacion_Receptor . "\n" . $this->Estado_Receptor . ", " . $this->pais_Receptor . ", C.P. " . $this->CP_Receptor;
        if ($this->Addendas != NULL) {
            $contador = 0;
            foreach ($this->Addendas as $value) {
                if ($contador == 0 || $contador % 5 == 0) {
                    $string_receptor .= "\n";
                }
                $string_receptor .= $value[0] . ": " . $value[1] . "      ";
                $contador++;
            }
        }
        $this->Ln(4);
        $this->MultiCell($width, 5, utf8_decode($string_receptor), 1, 'L', FALSE);
        $this->Ln(1);

        $dias_credito = "";
        if(isset($this->IdPreFactura) && !empty($this->IdPreFactura)){  
            $factura = new Factura();
            $factura->setIdFactura($this->IdPreFactura);
            if($factura->getRegistrobyID() && $factura->getDiasCredito() != ""){
                $dias_credito = $factura->getDiasCredito()." días";
            }
        }
        
        
        $x = $this->GetX();        
        $this->SetXY($x, $y_tipo_comprobante);
        
        $width = 67;
        $this->Cell($width, 5, utf8_decode('Forma de pago'), 1, 0, 'C', false);
        $this->Cell($width, 5, utf8_decode('Método de pago'), 1, 0, 'C', false);
        $y = $this->GetY();
        $this->MultiCell($width-1, 5, utf8_decode('Nº cliente: ' . $this->ClaveCliente . "\nContrato: ".$this->contrato."\nPeriodo de facturación: ".$this->Periodo_Facturacion_Emisor."\n  "), 1, 'L', false);
        $y+=5;
        $this->SetXY($x, $y);
        $this->MultiCell($width, 5, utf8_decode($this->FormaPago ."\nCrédito: ".$dias_credito), 1, 'L', false);
        $this->SetXY($x + $width, $y);
        $this->MultiCell($width, 5, utf8_decode($this->MetodoPago . "\nCuenta: " . $this->NumeroCtaPago), 1, 'L', false);

        $this->SetFillColor(210, 203, 203);
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(10, 5, utf8_decode('Cant'), 1, 0, 'C', true);
        $this->Cell(35, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(105, 5, utf8_decode('Descripción'), 1, 0, 'C', true); //de aqui se obtiene el 120, es una tabla 200 es lo máximo lo demás queda repartido
        $this->Cell(25, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(25, 5, utf8_decode('Importe'), 1, 1, 'C', true);
        //print_r($this->filas);
        //print_r($this->tabla);       

        for ($i = 0; $i < count($this->tabla); $i++) {
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(120, $array[4])); //120 caracteres es lo máximo permitido por línea
            $nb = $nb * 5; //se multiplica por el tamaño de pixeles verticales!
            $this->SetFont('Arial', '', 9);
            if ($array[7] == "1") {//Si es un encabezado                
                $this->Cell(200, 5, utf8_decode(strtoupper($array[4])), 1, 1, 'C', false);
            } else {//Si es un concepto normal                                   
                if(isset($this->IdPreFactura) && !empty($this->IdPreFactura)){                   
                    $x1 = $this->GetX();
                    $y1 = $this->GetY();                 
                    $this->SetXY($x1,$y1);
                    $x1_inicial = $x1;
                
                    $string_series = "";
                    $nb_aux = 0;
                    foreach ($series as $value) {
                        $string_series.= "'$value',";
                    }
                    if(!empty($string_series)){
                        $string_series = substr($string_series, 0, strlen($string_series)-1);
                    }
                    
                    $catalogo = new Catalogo();
                    $consulta = "SELECT b.NoSerie, e.Modelo, cc.Nombre AS Ubicacion,fd.ContadorBNAnterior,fd.ContadorBN,fd.Ubicacion AS Area,f.MostrarUbicacion,
                            fd.ContadorProcesadasBN, fd.ContadorProcesadasColor,
                            fd.ContadorColorAnterior,fd.ContadorColor, fd.isBackup
                            FROM `c_facturadetalle` AS fd
                            LEFT JOIN c_bitacora AS b ON b.id_bitacora = fd.IdBitacora
                            LEFT JOIN c_inventarioequipo AS cie ON cie.NoSerie = b.NoSerie
                            LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
                            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = fd.ClaveCentroCosto
                            LEFT JOIN c_factura AS f ON f.IdFactura = fd.IdFactura
                            WHERE fd.IdFactura = $this->IdPreFactura AND fd.NumeroPartida = ".($i+1)." ORDER BY cc.Nombre;";
                    $result = $catalogo->obtenerLista($consulta);
                    
                    //$y1 += $nb;
                    $x1 = $x1_inicial;
                    if((int)$y1 >= 315){
                        $this->MultiCell($width, 30, "", '', 'L', false);  
                        $y1 = 20;
                    }
                    $this->SetXY($x1, $y1);
                    if(mysql_num_rows($result) > 0){
                        $nb_aux = 5;
                        //Aquí se ponen el detalle de cada fila
                        $this->SetFont('Arial', 'BU', 7);                        
                        $width = 20;
                        $this->MultiCell($width, $nb_aux, utf8_decode("Serie"), 'L', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Modelo"), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 28;
                        $this->MultiCell($width, $nb_aux, utf8_decode("Localidad"), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 22;
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Ant. BN"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Act. BN"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Pág. Proc."), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Ant. Color"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Lec. Act. Color"), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode("Pág. Proc."), 'R', 'C', false);
                    }
                                 
                    $contador_result = 0;
                    while($rs = mysql_fetch_array($result)){
                        $contador_result++;
                        $this->SetFont('Arial', '', 6);                        
                        $y1 += $nb_aux;
                        
                        if((int)$y1 >= 315){
                            $this->MultiCell($width, 30, "", '', 'L', false);  
                            $y1 = 20;
                        }
                        
                        $x1 = $x1_inicial;
                        $this->SetXY($x1, $y1);
                        $width = 20;
                        
                        $nb_aux = 5;
                        if(strlen($rs['Ubicacion']) > 12 || strlen($rs['Modelo']) > 12 ){
                            $nb_aux += 10;
                        }                    
                        $es_back = "";
                        if($rs['isBackup']){
                            $es_back = " *";
                        }
                        $this->MultiCell($width, $nb_aux, utf8_decode($rs['NoSerie'].$es_back), 'L', 'L', false);  
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, 5, utf8_decode($rs["Modelo"]), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 28;
                        if(strlen($rs['Ubicacion']) > 40){
                            $aux = "...";
                        }else{
                            $aux = "";
                        }
                        $this->MultiCell($width, 5, utf8_decode(substr($rs["Ubicacion"], 0, 40).$aux), '', 'C', false);                
                        $this->SetXY($x1+=$width, $y1);
                        $width = 22;
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorBNAnterior'],0)), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorBN'],0)), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorProcesadasBN'],0) ), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorColorAnterior'],0) ), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorColor'],0) ), '', 'C', false);
                        $this->SetXY($x1+=$width, $y1);
                        $this->MultiCell($width, $nb_aux, utf8_decode(number_format($rs['ContadorProcesadasColor'],0)), 'R', 'C', false);
                        
                        if($rs['MostrarUbicacion'] == "1" && !empty($rs['Area']) && $rs['Area'] != " "){
                            $this->SetFont('Arial', '', 6);  
                            $espacio_ubicacin = 7;
                            $y1 += $espacio_ubicacin;

                            if((int)$y1 >= 315){
                                $this->MultiCell($width, 30, "", '', 'L', false);  
                                $y1 = 20;
                            }

                            $x1 = $x1_inicial;
                            $this->SetXY($x1, $y1);
                            $width = 200;

                            $nb_aux_2 = 5;
                            
                            if(mysql_num_rows($result) > $contador_result){
                                $y1 -= $espacio_ubicacin;
                            }else{
                                $nb_aux_2 = $nb_aux - $espacio_ubicacin;
                                $y1 -= 5;
                            }
                            
                            $this->MultiCell($width, $nb_aux_2, utf8_decode("Ubicación: ".$rs['Area']), 'LR', 'L', false);
                        }
                    }
                    //Lineas separadoras
                    $y1 += $nb_aux;
                    $this->SetXY($x1_inicial, $y1);
                    $this->MultiCell(200, 0, "", 'B', 'C', false);
                }
                
                $this->SetFont('Arial', '', 9);
                if (isset($this->filas[1]) && strlen($this->filas[1]) < 30 && isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0] . $this->filas[1];
                    $j_aux = 2;
                } else if (isset($this->filas[0]) && !empty($this->filas[0])) {
                    $concepto = $this->filas[0];
                    $j_aux = 1;
                } else {
                    //$concepto = "1.".$this->filas[1]." -- 2.".$this->filas[2]." -- 3.".$this->filas[3];
                    $concepto = $this->filas[1];
                    $j_aux = 2;
                }
                $concepto = "(".$array[1].") ".$concepto;
                $nb = max(0, $this->NbLines2(105, $concepto)) * 5;                
                //Cantidad
                $x1 = $this->GetX();
                $y1 = $this->GetY(); 
                if((int)$y1 >= 315){
                    $this->MultiCell($width, 30, "", '', 'L', false);  
                    $y1 = 20;
                }
                
                $this->SetXY($x1,$y1);
                $x1_inicial = $x1;
                if (strpos($array[0], ',') !== false) {
                    $this->Cell(10, $nb, utf8_decode($array[0]), 'BL', 'C', false);
                } else {
                    $floatVal = floatval($array[0]);                    
                    if ($floatVal && intval($floatVal) != $floatVal) {                                                
                        $this->Cell(10, $nb, utf8_decode(number_format((float) $array[0], 4)), 'BL', 'C', 'R',false);
                    } else {                       
                        $this->Cell(10, $nb, utf8_decode(number_format((int) $array[0])), 'BL', 'C', 'R',false);
                    }
                }              
                
                //Unidad
                if($y1 >= (int)315){
                    $y1 = 10;
                }
                $x1 += 10;
                $this->SetFont('Arial', '', 8);
                $this->SetXY($x1,$y1);
                $this->Cell(35, $nb, substr(utf8_decode($array[2]),0,25), 'B', 'C','C', false);                
                
                //Concepto
                $this->SetFont('Arial', '', 9);
                $x1+=35;
                $this->SetXY($x1,$y1);
                //tendremos que ocupar toda una pagina, si en la primer hoja son menos lineas
                $this->MultiCell(105, 5, utf8_decode($concepto), 'B', 'L', false);
                $x1+=105;
                $this->SetXY($x1,$y1);
                
                //Precio Unitario
                $this->Cell(25, $nb, utf8_decode("$ " . number_format($array[5], 2)), 'B', 'R', 'R',false);
                $x1+=25;
                $this->SetXY($x1, $y1);
                //Importe
                $this->Cell(25, $nb, utf8_decode("$ " . number_format($array[6], 2)), 'BR', 'R', 'R',false);                  
                if(isset($array[8]) && !empty($array[8])){//Si hay descuento por partida                    
                    if(isset($array[9]) && $array[9] == "1"){//Si el descuento es por porcentaje
                        $descuento_partida = $array[6] * ($array[8] / 100);
                    }else{
                        $descuento_partida = $array[8];
                    }                    
                    $this->SetY($this->GetY() + $nb);
                    $nb = 5;
                    //Concepto
                    $x1 = $this->GetX();
                    $y1 = $this->GetY(); 
                    if((int)$y1 >= 315){
                        $this->MultiCell($width, 30, "", '', 'L', false);  
                        $y1 = 20;
                    }

                    $this->SetXY($x1,$y1);
                    $x1_inicial = $x1;
                    $this->Cell(45, $nb, utf8_decode(""), 'BL', 'C', false);
                    $x1+=45;
                    
                    $this->Cell(130, $nb, utf8_decode("Descuento"), 'B', 'C', false);
                    $x1+=130;
                    $this->SetXY($x1, $y1);
                    //Importe
                    $this->Cell(25, $nb, utf8_decode("$ " . number_format($descuento_partida, 2)), 'BR', 'R', false);                     
                }
            }
            $this->SetY($this->GetY() + $nb);
        }
        //$y1 = $this->GetY();
        //$x1 = $this->GetX();
        $this->SetY($this->GetY() + 5);
        $this->MultiCell(120, 3, utf8_decode("(" . $this->num_Letra . ")"), 0, 'R', false);
        //$this->SetY($y1-340);
        //$this->SetX($x1 + 120);
        $this->Cell(40, 5, utf8_decode('Moneda: '.$this->Moneda), 0, 0, 'L', false);
        $size = 120;
        if(isset($this->descuento) && !empty($this->descuento)){
            $this->Cell(120, 5, utf8_decode('Descuento:$'), 0, 0, 'R', false);
            $this->Cell(40, 5, utf8_decode(number_format($this->descuento,2)), 0, 1, 'R', false);
            $size = 160;
        }
        $this->Cell($size, 5, utf8_decode('Subtotal:$'), 0, 0, 'R', false);        
        $this->Cell(40, 5, utf8_decode($this->subtotal), 0, 1, 'R', false);
        
        $this->Cell(160, 5, utf8_decode('IVA 16%:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->iva), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('Total:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->total), 0, 1, 'R', false);
        $this->ln(3);
        $this->Cell(200, 5, utf8_decode($this->comentarios), 0, 1, false);
        $this->ln(3);
        if ($prefactura) {
            if (!isset($this->ndc) || !$this->ndc) {
                $this->Cell(200, 5, utf8_decode("Esta es una prefactura."), 0, 1, false);
            } else {
                $this->Cell(200, 5, utf8_decode("Esta es un previo de nota de crédito."), 0, 1, false);
            }
        } else {
            $y1 = $this->GetY();
            $x1 = $this->GetX();
            if($y1 >= (int)320){
                $this->AddPage();
                $y1 = 10;
            }
            
            $this->SetXY($x1, $y1);
            $this->Image($this->cbb, $this->GetX(), $this->GetY(), 30, 30, 'PNG');
            $this->SetX($this->GetX() + 30);
            if (is_array($this->valores)) {
                foreach ($this->valores as $key => $value) {
                    $this->Cell(80, 5, utf8_decode($key . ":" . $value), 0, 1, 'C', false);
                    $this->SetX($this->GetX() + 30);
                }
            }

            $this->MultiCell(140, 5, utf8_decode(trim($this->leyenda)), 0, 'L', false);
            $this->SetY($this->GetY() + 30);
            $this->SetFont('Arial', 'B', 7);
            $this->Cell(200, 5, utf8_decode("Cadena original del complemento de certificación del SAT:"), 0, 1, false);
            $this->SetFont('Arial', '', 5);
            $this->MultiCell(200, 5, utf8_decode($this->cadena_SAT), 0, false);

            $this->SetFont('Arial', 'B', 7);
            $this->Cell(200, 5, utf8_decode("Sello digital del emisor:"), 0, 1, false);
            $this->SetFont('Arial', '', 5);
            $this->MultiCell(200, 5, utf8_decode($this->sello_Emisor), 0, false);

            $this->SetFont('Arial', 'B', 7);
            $this->Cell(200, 5, utf8_decode("Sello digital del SAT:"), 0, 1, false);
            $this->SetFont('Arial', '', 5);
            $this->MultiCell(200, 5, utf8_decode($this->sello_Digital), 0, false);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(200, 5, utf8_decode("Este documento es una representación impresa de un CFDI."), 0, 1, false);
        }
        //$this->Cell(111,25,'ALGÚN T�?TULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25
    }
    
    function NbLines2($w,$txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
    
    function NbLines($w, $txt) {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        $return = $nl;
        $this->filas = Array("");
        $nl_auxiliar = 0;
        while ($i < $nb) {//ya recorde que es el 5, son los pixeles o algo así verticalmente por eso lo multiplico por 5 
            if (($nl * 5) > (297 - $this->GetY()) && $nl_auxiliar == 0) {//297 es el limite vertical
                //array_push($this->filas, "");//espera
                $nl_auxiliar++; //vemos cuantas líneas sobran en la página con esto ($nl * 5) > (297 - $this->GetY()) si superan aumentamos línea
                $return = $nl; 
                $nl = 1;
                //$array_aux++;
            } elseif ((($nl - $nl_auxiliar) * 5) > 297) {
                $nl_auxiliar++;
                $nl = 1;
                //array_push($this->filas, "");
                //$array_aux++;
            }
            $c = $s[$i];
            $this->filas[$nl_auxiliar].= $c;

            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++; //tienen saltos de línea
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l+=$cw[$c];
            if ($l > $wmax) {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $return;
    }

    function NbLinesSimple($w, $txt) {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;

        while ($i < $nb) {
            $c = $s[$i];
            if ($c = "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l+=$cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    public function formatoFechaHora($fecha) {
        $aux1 = split(" ", $fecha);
        $aux2 = split("-", $aux1[0]);
        return $aux2[2] . "/" . $aux2[1] . "/" . $aux2[0] . " " . $aux1[1];
    }
    
    public function getLocalidad() {
        return $this->localidad;
    }

    public function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    public function getComentarios() {
        return $this->comentarios;
    }

    public function setComentarios($comentarios) {
        $this->comentarios = $comentarios;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function getFolioFiscal() {
        return $this->FolioFiscal;
    }

    public function getFolio() {
        return $this->Folio;
    }

    public function getLugarExpedicion() {
        return $this->LugarExpedicion;
    }

    public function getHoraEmision() {
        return $this->HoraEmision;
    }

    public function getFormaPago() {
        return $this->FormaPago;
    }

    public function getMetodoPago() {
        return $this->MetodoPago;
    }

    public function getNoSerieEmisor() {
        return $this->NoSerieEmisor;
    }

    public function getFecha_Cert() {
        return $this->Fecha_Cert;
    }

    public function getNombre_Emisor() {
        return $this->nombre_Emisor;
    }

    public function getRFC_Emisor() {
        return $this->RFC_Emisor;
    }

    public function getRegimenFiscal_Emisor() {
        return $this->regimenFiscal_Emisor;
    }

    public function getCalle_Emisor() {
        return $this->calle_Emisor;
    }

    public function getNo_Ext_Emisor() {
        return $this->no_Ext_Emisor;
    }

    public function getNo_int_Emisor() {
        return $this->no_int_Emisor;
    }

    public function getColonia_Emisor() {
        return $this->colonia_Emisor;
    }

    public function getEstado_Emisor() {
        return $this->Estado_Emisor;
    }

    public function getDelegacion_Emisor() {
        return $this->delegacion_Emisor;
    }

    public function getPais_Emisor() {
        return $this->pais_Emisor;
    }

    public function getCP_Emisor() {
        return $this->CP_Emisor;
    }

    public function getTel_Emisor() {
        return $this->Tel_Emisor;
    }

    public function getPeriodo_Facturacion_Emisor() {
        return $this->Periodo_Facturacion_Emisor;
    }

    public function getNombre_Receptor() {
        return $this->nombre_Receptor;
    }

    public function getRFC_Receptor() {
        return $this->RFC_Receptor;
    }

    public function getRegimenFiscal_Receptor() {
        return $this->regimenFiscal_Receptor;
    }

    public function getCalle_Receptor() {
        return $this->calle_Receptor;
    }

    public function getNo_Ext_Receptor() {
        return $this->no_Ext_Receptor;
    }

    public function getNo_int_Receptor() {
        return $this->no_int_Receptor;
    }

    public function getColonia_Receptor() {
        return $this->colonia_Receptor;
    }

    public function getEstado_Receptor() {
        return $this->Estado_Receptor;
    }

    public function getDelegacion_Receptor() {
        return $this->delegacion_Receptor;
    }

    public function getPais_Receptor() {
        return $this->pais_Receptor;
    }

    public function getCP_Receptor() {
        return $this->CP_Receptor;
    }

    public function getTel_Receptor() {
        return $this->Tel_Receptor;
    }

    public function getPeriodo_Facturacion_Receptor() {
        return $this->Periodo_Facturacion_Receptor;
    }

    public function getTabla() {
        return $this->tabla;
    }

    public function getNum_Letra() {
        return $this->num_Letra;
    }

    public function getSubtotal() {
        return $this->subtotal;
    }

    public function getIva() {
        return $this->iva;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getCadena_SAT() {
        return $this->cadena_SAT;
    }

    public function getSello_Emisor() {
        return $this->sello_Emisor;
    }

    public function getSello_Digital() {
        return $this->sello_Digital;
    }

    public function setTitulo($titulo) {
        $this->titulo = $titulo;
    }

    public function setFolioFiscal($FolioFiscal) {
        $this->FolioFiscal = $FolioFiscal;
    }

    public function setFolio($Folio) {
        $this->Folio = $Folio;
    }

    public function setLugarExpedicion($LugarExpedicion) {
        $this->LugarExpedicion = $LugarExpedicion;
    }

    public function setHoraEmision($HoraEmision) {
        $this->HoraEmision = $HoraEmision;
    }

    public function setFormaPago($FormaPago) {
        $this->FormaPago = $FormaPago;
    }

    public function setMetodoPago($MetodoPago) {
        $this->MetodoPago = $MetodoPago;
    }

    public function setFecha_Cert($Fecha_Cert) {
        $this->Fecha_Cert = $Fecha_Cert;
    }

    public function setNombre_Emisor($nombre_Emisor) {
        $this->nombre_Emisor = $nombre_Emisor;
    }

    public function setRFC_Emisor($RFC_Emisor) {
        $this->RFC_Emisor = $RFC_Emisor;
    }

    public function setRegimenFiscal_Emisor($regimenFiscal_Emisor) {
        $this->regimenFiscal_Emisor = $regimenFiscal_Emisor;
    }

    public function setCalle_Emisor($calle_Emisor) {
        $this->calle_Emisor = $calle_Emisor;
    }

    public function setNo_Ext_Emisor($no_Ext_Emisor) {
        $this->no_Ext_Emisor = $no_Ext_Emisor;
    }

    public function setNo_int_Emisor($no_int_Emisor) {
        $this->no_int_Emisor = $no_int_Emisor;
    }

    public function setColonia_Emisor($colonia_Emisor) {
        $this->colonia_Emisor = $colonia_Emisor;
    }

    public function setEstado_Emisor($Estado_Emisor) {
        $this->Estado_Emisor = $Estado_Emisor;
    }

    public function setDelegacion_Emisor($delegacion_Emisor) {
        $this->delegacion_Emisor = $delegacion_Emisor;
    }

    public function setPais_Emisor($pais_Emisor) {
        $this->pais_Emisor = $pais_Emisor;
    }

    public function setCP_Emisor($CP_Emisor) {
        $this->CP_Emisor = $CP_Emisor;
    }

    public function setTel_Emisor($Tel_Emisor) {
        $this->Tel_Emisor = $Tel_Emisor;
    }

    public function setPeriodo_Facturacion_Emisor($Periodo_Facturacion_Emisor) {
        $this->Periodo_Facturacion_Emisor = $Periodo_Facturacion_Emisor;
    }

    public function setNombre_Receptor($nombre_Receptor) {
        $this->nombre_Receptor = $nombre_Receptor;
    }

    public function setRFC_Receptor($RFC_Receptor) {
        $this->RFC_Receptor = $RFC_Receptor;
    }

    public function setRegimenFiscal_Receptor($regimenFiscal_Receptor) {
        $this->regimenFiscal_Receptor = $regimenFiscal_Receptor;
    }

    public function setCalle_Receptor($calle_Receptor) {
        $this->calle_Receptor = $calle_Receptor;
    }

    public function setNo_Ext_Receptor($no_Ext_Receptor) {
        $this->no_Ext_Receptor = $no_Ext_Receptor;
    }

    public function setNo_int_Receptor($no_int_Receptor) {
        $this->no_int_Receptor = $no_int_Receptor;
    }

    public function setColonia_Receptor($colonia_Receptor) {
        $this->colonia_Receptor = $colonia_Receptor;
    }

    public function setEstado_Receptor($Estado_Receptor) {
        $this->Estado_Receptor = $Estado_Receptor;
    }

    public function setDelegacion_Receptor($delegacion_Receptor) {
        $this->delegacion_Receptor = $delegacion_Receptor;
    }

    public function setPais_Receptor($pais_Receptor) {
        $this->pais_Receptor = $pais_Receptor;
    }

    public function setCP_Receptor($CP_Receptor) {
        $this->CP_Receptor = $CP_Receptor;
    }

    public function setTel_Receptor($Tel_Receptor) {
        $this->Tel_Receptor = $Tel_Receptor;
    }

    public function setPeriodo_Facturacion_Receptor($Periodo_Facturacion_Receptor) {
        $this->Periodo_Facturacion_Receptor = $Periodo_Facturacion_Receptor;
    }

    public function setTabla($tabla) {
        $this->tabla = $tabla;
    }

    public function setNum_Letra($num_Letra) {
        $this->num_Letra = $num_Letra;
    }

    public function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;
    }

    public function setIva($iva) {
        $this->iva = $iva;
    }

    public function setTotal($total) {
        $this->total = $total;
    }
    
    function getDescuento() {
        return $this->descuento;
    }

    function setDescuento($descuento) {
        $this->descuento = $descuento;
    }
    
    public function setCadena_SAT($cadena_SAT) {
        $this->cadena_SAT = $cadena_SAT;
    }

    public function setSello_Emisor($sello_Emisor) {
        $this->sello_Emisor = $sello_Emisor;
    }

    public function setSello_Digital($sello_Digital) {
        $this->sello_Digital = $sello_Digital;
    }

    public function getCbb() {
        return $this->cbb;
    }

    public function setCbb($cbb) {
        $this->cbb = $cbb;
    }

    public function getLogo() {
        return $this->logo;
    }

    public function setLogo($logo) {
        $this->logo = $logo;
    }

    public function getCSD_Emisor() {
        return $this->CSD_Emisor;
    }

    public function setCSD_Emisor($CSD_Emisor) {
        $this->CSD_Emisor = $CSD_Emisor;
    }

    public function getCSD_Sat() {
        return $this->CSD_Sat;
    }

    public function setCSD_Sat($CSD_Sat) {
        $this->CSD_Sat = $CSD_Sat;
    }

    public function getLeyenda() {
        return $this->leyenda;
    }

    public function setLeyenda($leyenda) {
        $this->leyenda = $leyenda;
    }

    public function getNumeroCtaPago() {
        return $this->NumeroCtaPago;
    }

    public function setNumeroCtaPago($NumeroCtaPago) {
        $this->NumeroCtaPago = $NumeroCtaPago;
    }

    function getNdc() {
        return $this->ndc;
    }

    function setNdc($ndc) {
        $this->ndc = $ndc;
    }

    function getMes_contrato() {
        return $this->mes_contrato;
    }

    function setMes_contrato($mes_contrato) {
        $this->mes_contrato = $mes_contrato;
    }

    function setContrato($contrato){
        $this->contrato = $contrato;
    }

    function getCondicionesPago() {
        return $this->condicionesPago;
    }

    function setCondicionesPago($condicionesPago) {
        $this->condicionesPago = $condicionesPago;
    }

    function getAddendas() {
        return $this->Addendas;
    }

    function setAddendas($Addendas) {
        $this->Addendas = $Addendas;
    }

    function getValores() {
        return $this->valores;
    }

    function setValores($valores) {
        $this->valores = $valores;
    }

    function getClaveCliente() {
        return $this->ClaveCliente;
    }

    function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
    }

    function getIdPreFactura() {
        return $this->IdPreFactura;
    }

    function setIdPreFactura($IdPreFactura) {
        $this->IdPreFactura = $IdPreFactura;
    }

    function getArrayPago() {
        return $this->arrayPago;
    }

    function getArrayDocumentosRelacionados() {
        return $this->arrayDocumentosRelacionados;
    }

    function setArrayPago($arrayPago) {
        $this->arrayPago = $arrayPago;
    }

    function setArrayDocumentosRelacionados($arrayDocumentosRelacionados) {
        $this->arrayDocumentosRelacionados = $arrayDocumentosRelacionados;
    }
    
    function getClave_receptor() {
        return $this->clave_receptor;
    }

    function setClave_receptor($clave_receptor) {
        $this->clave_receptor = $clave_receptor;
    }

    function getTipoComprobante() {
        return $this->TipoComprobante;
    }

    function setTipoComprobante($TipoComprobante) {
        $this->TipoComprobante = $TipoComprobante;
    }

    function getUsoCFDI() {
        return $this->UsoCFDI;
    }

    function setUsoCFDI($UsoCFDI) {
        $this->UsoCFDI = $UsoCFDI;
    }
    
    function getReferencia() {
        return $this->Referencia;
    }

    function setReferencia($Referencia) {
        $this->Referencia = $Referencia;
    }

    function getUUIDRelacionado() {
        return $this->UUIDRelacionado;
    }

    function getTipoRelacion() {
        return $this->TipoRelacion;
    }

    function setUUIDRelacionado($UUIDRelacionado) {
        $this->UUIDRelacionado = $UUIDRelacionado;
    }

    function setTipoRelacion($TipoRelacion) {
        $this->TipoRelacion = $TipoRelacion;
    }

    function getMoneda() {
        return $this->Moneda;
    }

    function setMoneda($Moneda) {
        $this->Moneda = $Moneda;
    }
    function setConceptos($conceptos){
        $this->Conceptos = $conceptos;
    }
    function getConceptos(){
        return $this->Conceptos;
    }

}

?>
