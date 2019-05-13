<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

if(!isset($_GET['origen']) || !isset($_GET['destino'])){
    echo "No se recibieron los clientes origen ni destino";
    return;
}

$origen = $_GET['origen'];
$destino = $_GET['destino'];

include_once("./WEB-INF/Classes/Catalogo.class.php");
include_once("./WEB-INF/Classes/CentroCosto.class.php");

$catalogo = new Catalogo;
$consulta = "SELECT *
    FROM c_centrocosto AS cc
    WHERE cc.ClaveCliente = '$origen'
    ORDER BY cc.ClaveCentroCosto;";
$pantalla = "CopiarLocalidades";

$result = $catalogo->obtenerLista($consulta);
while($rs = mysql_fetch_array($result)){
    $cc = new CentroCosto();    
    $cc->setClaveCliente($destino);
    $cc->setNombre($rs['Nombre']);
    $cc->setActivo($rs['Activo']);
    $cc->setUsuarioCreacion($rs['UsuarioCreacion']);
    $cc->setUsuarioUltimaModificacion($rs['UsuarioUltimaModificacion']);
    $cc->setPantalla($pantalla);
    $cc->setClaveZona($rs['ClaveZona']);
    $cc->setMoroso($rs['Moroso']);
    $cc->setTipoDomicilioFiscal($rs['TipoDomicilioFiscal']);
    if($cc->newRegistro()){        
        //Copiamos los domicilios
        $consulta = "INSERT INTO c_domicilio (IdDomicilio,ClaveEspecialDomicilio,IdTipoDomicilio,Calle, NoExterior, NoInterior, Colonia, Ciudad, Estado, 
            Delegacion, Pais, CodigoPostal, ClaveZona, Localidad, 
            Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla, Latitud, Longitud)
            SELECT 0,'".$cc->getClaveCentroCosto()."',IdTipoDomicilio,Calle, NoExterior, NoInterior, Colonia, Ciudad,Estado, Delegacion, Pais, CodigoPostal, ClaveZona, 
            Localidad, Activo, UsuarioCreacion, NOW(), UsuarioUltimaModificacion, NOW(), '$pantalla', Latitud, Longitud 
            FROM c_domicilio WHERE ClaveEspecialDomicilio = '".$rs['ClaveCentroCosto']."';";
        //echo $consulta;
        $result2 = $catalogo->obtenerLista($consulta);        
        echo "Se insertó la localidad ".$cc->getNombre()."<br/>";
    }else{
        echo "<br/>Error: no se pudo insertar la localidad ".$cc->getNombre();
    }
}