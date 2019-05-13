<?php
    session_start();
    set_time_limit (0);
    include_once("WEB-INF/Classes/Catalogo.class.php");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Calcular Latitud - Longitud</title>
    </head>
    <body>
        <?php
        // Get lat and long by address         
        $consulta = "SELECT GROUP_CONCAT(CONVERT(IdDomicilio, CHAR(8))) AS IdDomicilio, 
            CONCAT(
            REPLACE(Calle,' ','%20'),'%20',
            REPLACE(NoExterior,' ','%20'),',',
            REPLACE(Colonia,' ','%20'),',',
            REPLACE(CodigoPostal,' ','%20'),'%20',
            REPLACE(Delegacion,' ','%20'),',',
            REPLACE(Estado,' ','%20'),',',
            REPLACE(Pais,' ','%20')) AS Domicilio
            FROM `c_domicilio` AS d
            LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = d.ClaveEspecialDomicilio
            WHERE !ISNULL(cc.ClaveCentroCosto) AND (ISNULL(d.Latitud) OR ISNULL(d.Longitud)) AND d.NoExterior <> 'S/N' 
            GROUP BY Domicilio
            ORDER BY d.IdDomicilio DESC;";
        $catalogo = new Catalogo();
        $catalogo->setEmpresa(8);
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $prepAddr = $rs['Domicilio'];
            $geocode=file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$prepAddr.'&sensor=false');
            //$geocode = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=EX-HACIENDA%20DE%20ZACATEPEC%20KM%2053.8%20CARRT.%20FED.%20140%20PUEBLA%20JALAPA%20ZACATEPEC.%20S/N,SAN%20JOS%C3%89%20ZACATEPEC,75029%20ORIENTAL,Puebla,M%C3%A9xico&sensor=false");
            $output = json_decode($geocode);
            $latitude = $output->results[0]->geometry->location->lat;
            $longitude = $output->results[0]->geometry->location->lng;
            $resultUpdate = $catalogo->obtenerLista("UPDATE c_domicilio SET Latitud = $latitude, Longitud=$longitude WHERE IdDomicilio IN(".$rs['IdDomicilio'].");");
            if($resultUpdate != "0"){
                echo "<br/>Actualizacion de ".$latitude . ", " . $longitude." para ".$rs['IdDomicilio'];
            }else{
                echo "<br/>Error: Actualizacion de ".$latitude . ", " . $longitude." para ".$rs['IdDomicilio'];
            }      
            // Esperar 0.2 segundos
            usleep(200000);
        }        
        ?>
    </body>
</html>