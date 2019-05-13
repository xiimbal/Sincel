<?php

require('libfpdf/fpdf.php');

//Clase en blanco
class PDFFactura extends FPDF {

    private $titulo = "";
    private $FolioFiscal = "";
    private $Folio = "";
    private $LugarExpedicion = "";
    private $HoraEmision = "";
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
    private $tabla;
    private $num_Letra = "";
    private $subtotal = "";
    private $iva = "";
    private $total = "";
    private $cadena_SAT = "";
    private $sello_Emisor = "";
    private $sello_Digital = "";
    private $cbb;
    private $logo;
    private $leyenda;
    private $comentarios;
    private $localidad;

    function CrearPDF() { //Encabezado
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
        if($this->NumeroCtaPago!=""){
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
        
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, 10, 10, null, null, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, 10, 10, null, null);
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
        $this->MultiCell(200, 5, utf8_decode("Nombre: " . $this->nombre_Receptor . "\nRFC: " . $this->RFC_Receptor . "\nCalle: " . $this->calle_Receptor . " No.Exterior: " . $this->no_Ext_Receptor . " No.Interior: " . $this->no_int_Receptor . "\nColonia: " . $this->colonia_Receptor . "\nLocalidad:" . $this->localidad . "\nEstado: " . $this->Estado_Receptor . "\nDelegación: " . $this->delegacion_Receptor . " País: " . $this->pais_Receptor . "\nCódigo Postal: " . $this->CP_Receptor), 1, 'L', FALSE);
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(20, 5, utf8_decode('Cantidad'), 1, 0, 'C', true);
        $this->Cell(20, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(80, 5, utf8_decode('Descripción'), 1, 0, 'C', true);
        $this->Cell(40, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(40, 5, utf8_decode('Importe'), 1, 1, 'C', true);

        for ($i = 0; $i < count($this->tabla); $i++) {
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(80, $array[2]));
            $nb = $nb * 5;
            $this->SetFont('Arial', '', 9);
            if ($array[5] == "1") {
                //$this->SetTextColor('255', '233', '0'); //
                //$this->SetFont('Arial', 'B', 9);
                $this->Cell(200, 5, utf8_decode(strtoupper($array[2])), 1, 1, 'C', false);
            } else {
                $this->Cell(20, $nb, utf8_decode(number_format($array[0])), 1, 0, 'C', false);
                $this->Cell(20, $nb, utf8_decode($array[1]), 1, 0, 'C', false);
                $y1 = $this->GetY();
                $x1 = $this->GetX();
                $this->MultiCell(80, 5, utf8_decode($array[2]), 1, 'L', false);
                $this->SetY($y1);
                $this->SetX($x1 + 80);
                //$this->Cell(60, 5, , 1, 0, 'L',false);
                $this->Cell(40, $nb, utf8_decode("$ " . number_format($array[3], 2)), 1, 0, 'R', false);
                $this->Cell(40, $nb, utf8_decode("$ " . number_format($array[4], 2)), 1, 1, 'R', false);
            }
        }




        /* $nb=0;
          $nb=max($nb,$this->NbLines(60,'GAVETA PARA EQUIPO FS-1035MFP NW12908804 poadbyasd sahdiybcasjdohubas asduhoaudsnasd asudh aosdjia sduasd u'));
          $nb=$nb*5;
          $this->SetFont('Arial', '', 9);
          $this->Cell(20, $nb, utf8_decode('1'), 1, 0, 'C', false);
          $this->Cell(40, $nb, utf8_decode('Pieza'), 1, 0, 'C', false);
          $y1 = $this->GetY();
          $x1 = $this->GetX();
          $this->MultiCell(60, 5, utf8_decode('GAVETA PARA EQUIPO FS-1035MFP NW12908804 poadbyasd sahdiybcasjdohubas asduhoaudsnasd asudh aosdjia sduasd u'), 1, 'L', false);
          $this->SetY($y1);
          $this->SetX($x1+60);
          //$this->Cell(60, 5, , 1, 0, 'L',false);
          $this->Cell(40, $nb, utf8_decode('$2,075.00'), 1, 0, 'R', false);
          $this->Cell(40, $nb, utf8_decode('$2,075.00'), 1, 1, 'R', false); */

        $this->MultiCell(120, 5, utf8_decode("(" . $this->num_Letra . ")"), 0, 'L', false);
        $this->Cell(160, 5, utf8_decode('Subtotal:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->subtotal), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('IVA 16%:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->iva), 0, 1, 'R', false);
        $this->Cell(160, 5, utf8_decode('Total:$'), 0, 0, 'R', false);
        $this->Cell(40, 5, utf8_decode($this->total), 0, 1, 'R', false);
        $this->ln(3);
        $this->Cell(200, 5, utf8_decode($this->comentarios), 0, 1, false);
        $this->Image($this->cbb, $this->GetX(), $this->GetY(), 30, 30, 'PNG');
        $this->SetX($this->GetX() + 30);
        $this->SetFont('Arial', '', 6);
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
        //$this->Cell(111,25,'ALGÚN TÍTULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25
    }

    function CrearPDF_PREF() { //Encabezado
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor('255', '0', '0'); //para imprimir en rojo        
        $this->SetX(110);
        $this->Cell(50, 2, utf8_decode($this->titulo . ' FOLIO ' . $this->Folio), 0, 1, 'L');
        $this->SetTextColor('0', '0', '0'); //para imprimir en rojo
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
        if($this->NumeroCtaPago!=""){
            $this->SetX(110);
            $this->Cell(50, 5, utf8_decode('NumCtaPago'), 0, 0, 'L');
            $this->SetFont('Arial', '', 9);        
            $this->Cell(50, 5, utf8_decode($this->NumeroCtaPago), 0, 1, 'L');
            $this->SetFont('Arial', 'B', 9);
        }
        if ($this->logo != "") {
            $nom = explode(".", $this->logo);
            if ($nom[1] == "png") {
                $this->Image('../../../LOGOS/' . $this->logo, 10, 10, null, null, 'PNG');
            } else {
                $this->Image('../../../LOGOS/' . $this->logo, 10, 10, null, null);
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
        $this->MultiCell(200, 5, utf8_decode("Nombre: " . $this->nombre_Receptor . "\nRFC: " . $this->RFC_Receptor . "\nCalle: " . $this->calle_Receptor . " No.Exterior: " . $this->no_Ext_Receptor . " No.Interior: " . $this->no_int_Receptor . "\nColonia: " . $this->colonia_Receptor . "\nLocalidad:" . $this->localidad . "\nEstado: " . $this->Estado_Receptor . "\nDelegación: " . $this->delegacion_Receptor . " País: " . $this->pais_Receptor . "\nCódigo Postal: " . $this->CP_Receptor), 1, 'L', FALSE);
        $this->Ln(3);
        $this->Cell(200, 5, utf8_decode('Conceptos'), 1, 1, 'L', true);
        $this->SetFont('Arial', '', 9);

        $this->Cell(20, 5, utf8_decode('Cantidad'), 1, 0, 'C', true);
        $this->Cell(20, 5, utf8_decode('Unidad'), 1, 0, 'C', true);
        $this->Cell(80, 5, utf8_decode('Descripción'), 1, 0, 'C', true);
        $this->Cell(40, 5, utf8_decode('P Unitario'), 1, 0, 'C', true);
        $this->Cell(40, 5, utf8_decode('Importe'), 1, 1, 'C', true);

        for ($i = 0; $i < count($this->tabla); $i++) {
            $this->SetFont('Arial', '', 9);
            $this->SetTextColor('0', '0', '0');
            $array = $this->tabla[$i];
            $nb = 0;
            $nb = max($nb, $this->NbLines(80, $array[2]));
            $nb = $nb * 5;
            if ($array[5] == "1") {
                //$this->SetTextColor('255', '233', '0'); //
                //$this->SetFont('Arial', 'B', 9);
                $this->Cell(200, 5, utf8_decode(strtoupper($array[2])), 1, 1, 'C', false);
            } else {
                $this->Cell(20, $nb, utf8_decode(number_format($array[0])), 1, 0, 'C', false);
                $this->Cell(20, $nb, utf8_decode($array[1]), 1, 0, 'C', false);
                $y1 = $this->GetY();
                $x1 = $this->GetX();
                $this->MultiCell(80, 5, $array[2], 1, 'L', false);
                $this->SetY($y1);
                $this->SetX($x1 + 80);
                //$this->Cell(60, 5, , 1, 0, 'L',false);
                $this->Cell(40, $nb, utf8_decode("$ " . number_format($array[3], 2)), 1, 0, 'R', false);
                $this->Cell(40, $nb, utf8_decode("$ " . number_format($array[4], 2)), 1, 1, 'R', false);
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
        $this->Cell(200, 5, utf8_decode("Esta es una prefactura."), 0, 1, false);
        //$this->Cell(111,25,'ALGÚN TÍTULO DE ALGÚN LUGAR',0,0,'C', $this->Image('images/logoIzquierda.png',20,12,20));
        //$this->Cell(40,25,'',0,0,'C',$this->Image('images/logoDerecha.png', 175, 12, 19));
        //Se da un salto de línea de 25
    }

    function NbLines($w, $txt) {
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
            if ($c == "\n") {
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
}
?>
