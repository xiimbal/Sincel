<?php
    
    session_start();

    include("../../Classes/Catalogo.class.php");    

    $proceso = $_POST['proceso'];
    // $proceso = "consultar";
    
    if ($proceso == "consultar") {
        
        $data["data"] = array();  

        $conexion = new Catalogo();    
        $conexion->setEmpresa($_SESSION["idEmpresa"]);

        $query_obtener_rutas = $conexion->obtenerLista("SELECT * FROM c_logistica WHERE estatus = '307' AND Activo = '1';"); 
        
        while($rutas = mysql_fetch_assoc($query_obtener_rutas)){                
                    
            array_push($data["data"], $rutas);        

        }    
        
        print json_encode($data, JSON_UNESCAPED_UNICODE);

    } elseif ($proceso == "actualizar_piezas_camion") {
        
        $id_csv = $_POST['id_csv'];
        $piezas_encamion = $_POST['piezas_encamion'];        

        $conexion = new Catalogo();    
        $conexion->setEmpresa($_SESSION["idEmpresa"]);
        
        $query_actualizar_logistica = $conexion->obtenerLista("UPDATE c_logistica SET piezas_encamion = '$piezas_encamion', estatus = '304' WHERE id_csv= '$id_csv';"); 

        if ($query_actualizar_logistica == true) {
            print json_encode("Exito");
        }else{
            print json_encode("Error");
        }  

    }   

?>