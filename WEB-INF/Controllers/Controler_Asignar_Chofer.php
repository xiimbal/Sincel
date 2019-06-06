<?php

    session_start();

    include("../Classes/Catalogo.class.php");
    
    $proceso = $_POST['proceso'];  
    
    if ($proceso == "consultar") {
        
        $fecha = $_POST['fecha_embarque']; 
    
        $data["claves_vehiculares"] = array();
        $data["conductores"] = array();     
    
        $conexion = new Catalogo();    
        $conexion->setEmpresa($_SESSION["idEmpresa"]);
    
        $query_claves_vehiculares = $conexion->obtenerLista("CALL SELECT_CLAVES_VEHICULARES('$fecha')");
        $query_conductores = $conexion->obtenerLista("SELECT * FROM c_mensajeria WHERE Activo = 1");
        
        while($claves_vehiculares = mysql_fetch_assoc($query_claves_vehiculares)){                
                    
            array_push($data["claves_vehiculares"], $claves_vehiculares);        
    
        }    
        
        while($conductores = mysql_fetch_assoc($query_conductores)){                
                    
            array_push($data["conductores"], $conductores);
    
        }    
        
        print json_encode($data, JSON_UNESCAPED_UNICODE);

    }elseif ($proceso == "insertar") {

        $fecha = $_POST['fecha_embarque'];
        
        $clave_vehicular = $_POST['clave_vehicular'];
        $conductor = $_POST['conductor'];
        $usuario = $_SESSION['user'];

        $conexion = new Catalogo();    
        $conexion->setEmpresa($_SESSION["idEmpresa"]);
    
        $query_insertar_logistica_chofer = $conexion->obtenerLista("INSERT INTO c_logistica_chofer VALUES (NULL, '$conductor', '$clave_vehicular', '$fecha', '307', '$usuario', NOW(), '$usuario', NOW(), 'PHP');");
        $query_insertar_logistica_chofer = $conexion->obtenerLista("UPDATE c_logistica SET estatus = '307' WHERE fecha = '$fecha' AND CV = '$clave_vehicular';");
        // $query_insertar_logistica_chofer = $conexion->obtenerLista("UPDATE c_logistica_localidad SET estatus = '307' WHERE fecha = '$fecha' AND CV = '$clave_vehicular';");
        
        if ($query_insertar_logistica_chofer == true) {
            print json_encode("Exito");
        }else{
            print json_encode("Error");
        }                

    }





?>