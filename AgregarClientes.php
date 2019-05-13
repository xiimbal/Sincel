<?php
include_once("WEB-INF/Classes/CentroCosto.class.php");
include_once("WEB-INF/Classes/Localidad.class.php");
include_once("WEB-INF/Classes/Inventario.class.php");

$claves = array('V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21',	'V02_CL21');
$nombres = array('VALLEJO',	'BELLAVISTA',	'ECATEPEC',	'ERMITA CAPACITACION',	'GALEANA',	'MAIZ',	'MAIZ',	'ROJO GOMEZ',	'MAIZ',	'MEXICO AEROPUERTO',	'PERIFERICO SUR',	'POLANCO',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'OFICINA CENTRAL',	'PUERTO ALEGRE',	'ACAPULCO',	'AGUASCALIENTES',	'CANCUN',	'CD JUAREZ',	'CHIHUAHUA',	'CULIACAN',	'DURANGO',	'GUADALAJARA',	'GUADALAJARA',	'HERMOSILLO',	'LEON',	'MERIDA',	'MERIDA',	'MERIDA',	'MEXICALI',	'MONTERREY',	'MONTERREY',	'MONTERREY',	'MORELIA',	'OAXACA',	'OAXACA',	'OAXACA',	'PUEBLA',	'QUERETARO',	'QUERETARO',	'QUERETARO',	'REYNOSA',	'SAN LUIS P.',	'SAN LUIS P.',	'TAMPICO',	'TIJUANA',	'TIJUANA',	'TOLUCA',	'TORREON',	'TUXTLA',	'VERACRUZ',	'VILLA HERMOSA',	'VILLA HERMOSA');
$series = array('NNB2202993',	'NNB2202994',	'NNB2803365',	'NNB2202910',	'NNB2805413',	'NNB2202935',	'NNB2202972',	'NNB2803409',	'XVP0910017',	'XGU0232250',	'PPJ8911047',	'XPM4659935',	'ABW037902',	'XPL9821790',	'AJK3138211',	'PPJ8709753',	'NNB1Z02266',	'NNB3405171',	'NNB3705515',	'NNB2803386',	'NNB2803404',	'nnb3405102',	'NNB2803385',	'AJK3067130',	'NNJ1Y00890',	'NNJ1Y00914',	'NNJ1800278',	'NNJ1Y00950',	'NNJ1X00669',	'NNJ1X00694',	'NNJ1Y00952',	' NNJ1X00741',	'NNJ1X00693',	'NNJ1800317',	'NNJ1Y00957',	'NNJ1X00668',	'NNJ1X00740',	'AJK3107211',	'NNJ1X00650',	'NNJ1800335 ,',	' NNJ1Y00881',	'NNJ1X00632',	'NNJIX00667',	'NNJ1Y00877',	'NNB3103761',	'NNB3906773',	'NNJ1X00560',	'NNJ1800276',	'NW12604907',	'NNB1Z02280',	'NNJ1X00559',	'NNJ1Y00875',	'NNJ1YX00546',	'NNJ1Y00839',	'NNJ1Y00886',	'NNB2803413',	'NNJ1Y00844',	'NNJ1X00675',	'NNJ1X00739',	'NNJ1X00636',	'NNJ1Y00951',	'NNJ1800306');
$partes = array('FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'KM-2820',	'KM-1500',	'KM-4050',	'FS-1116',	'KM-1500',	'FS-1016MFP',	'KM-3035',	'KM-4050',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'KM-3035',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'KM-3035',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP',	'FS-3540MFP');
$ubicacion = array('OFICINAS OPERACIONES',	'OFICINAS OPERACIONES',	'OFICINAS OPERACIONES',	'',	'OFICINAS OPERACIONES',	'ALMACEN MAIZ OFICINAS',	'ALMACEN ',	'OFICINAS OPERACIONES',	'OFICINAS EMPRESAS EXT.',	'OFICINAS AUDITORIA INTERNA',	'JURIDICO',	'CENTRO DE PAGO',	'VICEPRECIDENCIA',	'CENTRO DE PAGO',	'CENTRO DE FOTOCOPIADO',	'CENTRO DE FOTOCOPIADO',	'CENTRO DE FOTOCOPIADO',	'CENTRO DE FOTOCOPIADO',	'',	'CALL CENTER',	'CONTABILIDAD',	'CENTRO DE FOTOCOPIADO',	'CREDITO Y COBRANZA',	'',	'OFICINAS ADMINISTRATIVAS',	'',	'OFICINAS ADMINISTRATIVAS',	'',	'OFICINAS ADMINISTRATIVAS',	'',	'OFICINAS ADMINISTRATIVAS',	'',	'',	'',	'',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS OPERACIONES',	'ALMACEN',	'',	'',	'',	'',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'',	'',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'',	'',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS OPERACIONES',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'ALMACEN',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'OFICINAS ADMINISTRATIVAS',	'ALMACEN');

$centroCosto = new CentroCosto();
$inventario = new Inventario();
$usuario = "kyocera";
$pantalla = "PHP Carga masiva";
$contador = 0;
for($i=0;$i<count($nombres);$i++){
    $result = $centroCosto->getCentroCostoByClienteYNombre($claves[$i], $nombres[$i]);
    if(mysql_num_rows($result) == 0){
        $centroCosto->setClaveCliente($claves[$i]);
        $centroCosto->setNombre($nombres[$i]);
        $centroCosto->setActivo("1");
        $centroCosto->setUsuarioCreacion($usuario);
        $centroCosto->setUsuarioUltimaModificacion($usuario);
        $centroCosto->setPantalla($pantalla);
        if($centroCosto->newRegistro()){
            echo "<br/><br/><br/>El centro de costo ".$centroCosto->getNombre()." fue registrado correctamente";
            $localidad = new Localidad();
            $localidad->setClaveEspecialDomicilio($centroCosto->getClaveCentroCosto());
            $localidad->setCalle("Falta calle");
            $localidad->setNoExterior("0");
            $localidad->setNoInterior("0");
            $localidad->setColonia("Falta colonia");
            $localidad->setCiudad("Falta ciudad");
            $localidad->setEstado("Distrito Federal");
            $localidad->setDelegacion("Falta delegación");
            $localidad->setPais("México");
            $localidad->setCodigoPostal("15510");
            $localidad->setActivo("1");
            $localidad->setUsuarioCreacion($usuario);
            $localidad->setUsuarioUltimaModificacion($usuario);
            $localidad->setPantalla($pantalla);
            if($localidad->newRegistro("5")){
                echo "<br/>La direccion del centro de costo ".$centroCosto->getNombre()." fue registrado correctamente";
                if($inventario->insertarInventarioValidando($series[$i],null,$ubicacion[$i],$centroCosto->getClaveCentroCosto(),$claves[$i],$partes[$i],true)){
                    $contador++;
                }
            }else{
                echo "<br/>No se pudo registrar la dirección del centro de costo ".$centroCosto->getNombre();
            }
        }else{
            echo "<br/>El centro de costo ".$centroCosto->getNombre()." no se pudo registrar";
        }
    }else{
        echo "<br/><br/><br/>El centro de costo ".$nombres[$i]." del cliente ".$claves[$i]." ya existe";
        if($rs = mysql_fetch_array($result)){
            //$centroCosto->getRegistroById($rs['ClaveCentroCosto']);
            if($inventario->insertarInventarioValidando($series[$i],null,$ubicacion[$i],$rs['ClaveCentroCosto'],$claves[$i],$partes[$i],true)){
                $contador++;
            }
        }
    }
}
echo "<br/><br/>Al final del proceso se guardaron $contador equipos";
?>
