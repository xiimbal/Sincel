<?php
session_start();
include_once("WEB-INF/Classes/Catalogo.class.php");

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

$tickets = array(
79329,
79329,
80388,
82706,
82706,
83181,
83304,
84437,
84437,
84568,
84568,
84731,
84731,
84731,
84731,
84731,
84768,
84768,
84768,
84768,
84804,
84804,
84811,
84823,
84911,
84911,
84911,
84911,
85033,
85033,
85033,
85046,
85046,
85046,
85053,
85053,
85121,
85121,
85121,
85121,
85127,
85141,
85141,
85141,
85144,
85165,
85165,
85165,
85496,
85496,
85496,
85497,
85497,
85881,
85894,
85894,
85894,
86004
);

$cantidades = array(
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
4,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1,
1
);
$partes=array(
    '302NM94040',
'302H994270',
'302HS94020',
'302K906360',
'302K906370',
'302H493011',
'302KW94080',
'A161R71300',
'A2XN0RD /',
'302H493011',
'302H493040',
'302F906230',
'302K393111',
'302F909171',
'36211110 /',
'3BR07040 /',
'302MV93021',
'302MV93031',
'302MV93051',
'302NP93030',
'302BR06520',
'302H493040',
'302H493011',
'302H493040',
'302J193064',
'302F909171',
'302F906230',
'302F906240',
'302F906230',
'302F909171',
'302F906240',
'302F906240',
'302F906230',
'302F993079',
'2BJ06010 /',
'302GR93281',
'302F906230',
'302F906240',
'302F909171',
'302H493011',
'2BJ06010 /',
'302MY93031',
'302MY93052',
'302MY93042',
'2BJ06010 /',
'302F906240',
'302F909171',
'302F906230',
'302F909171',
'302F906240',
'302F906230',
'302F906230',
'302F906240',
'302J193064',
'302F906240',
'302F906230',
'302F909171',
'302J193064',
);

foreach ($tickets as $key => $value) {
    if(count($tickets) != count($cantidades) || count($tickets) != count($partes)){
        echo "Las longitudes no coinciden";
        break;
    }
    $parte = trim(str_replace("/", "", $partes[$key]));
    $cantidad = $cantidades[$key];
    $consulta = "SELECT * FROM k_nota_refaccion AS nr
        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = nr.IdNotaTicket
        WHERE nr.NoParteComponente = '$parte' AND nt.IdTicket = $value AND nt.IdEstatusAtencion IN(20);";
    $catalogo = new Catalogo();
    $result = $catalogo->obtenerLista($consulta);
    if(mysql_num_rows($result) > 0){
        while($rs = mysql_fetch_array($result)){
            echo "<br/>Eliminar Nota ".$rs['IdNotaTicket']." con la part $parte de cantidad $cantidad para el ticket $value";
            /*$result3 = $catalogo->obtenerLista("DELETE FROM k_nota_refaccion                
                WHERE IdNotaTicket = ".$rs['IdNotaTicket']." AND NoParteComponente = '$parte' AND Cantidad = $cantidad;"); 
            
            if($result3 == "0"){
                echo " - 406: no se pudo borrar la refaccion";
            }else{
                echo " -- borrado exitoso";
            }
            $result2 = $catalogo->obtenerLista("DELETE FROM c_notaticket WHERE IdNotaTicket = ".$rs['IdNotaTicket']);
            if($result2 == "0"){
                echo " - 405: no se pudo borrar la nota";
            }else{
                echo " -- borrado exitoso";
            }*/
        }
    }else{
        echo "<br/>$key. 404 Not found: ".$rs['IdNotaTicket']." del no. parte: $parte con cantidad $cantidad del ticket $value";
    }
}
?>