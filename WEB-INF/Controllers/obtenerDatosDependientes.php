<?php

    if(isset($_POST['id']) && isset($_POST['tipo']) && $_POST['tipo'] == "proveedor"){
        include_once("../Classes/Proveedor.class.php");
        $obj = new Proveedor();  
        $resultado = $obj->getProveedoresByCliente($_POST['id']);
        echo "<option value=''>Selecciona el proveedor</option>";
        while($rs = mysql_fetch_array($resultado)){
            echo "<option value='".$rs['id']."'>".$rs['proveedor']."</option>";
        }
    }else if(isset($_POST['id']) && isset($_POST['tipo']) && $_POST['tipo'] == "area"){
        include_once("../Classes/Area.class.php");
        $obj = new Area();  
        $resultado = $obj->getAreaByAlmacen($_POST['id']);
        echo "<option value=''>Selecciona el &aacute;rea</option>";
        while($rs = mysql_fetch_array($resultado)){
            echo "<option value='".$rs['id']."'>".$rs['area_almacen']."</option>";
        }
    }else if(isset($_POST['id']) && isset($_POST['tipo']) && $_POST['tipo'] == "ubicacion_almacen"){
        include_once("../Classes/Almacen.class.php");
        $obj = new Almacen();  
        if($_POST['id']!=""){
            $obj->getRegistroById($_POST['id']);        
            echo "Calle y n&uacute;mero: ".$obj->getCalle()." C.P. : ".$obj->getCp();
        }
    }else if(isset($_POST['id']) && isset($_POST['tipo']) && $_POST['tipo'] == "recurso"){
        include_once("../Classes/Recurso.class.php");
        $obj = new Recurso();  
        $resultado = $obj->getRecursoByTipo($_POST['id']);
        echo "<option value=''>Selecciona el recurso</option>";
        while($rs = mysql_fetch_array($resultado)){
            echo "<option value='".$rs['id']."'>".$rs['recurso']."</option>";
        }
    }else if(isset($_POST['id']) && isset($_POST['tipo']) && $_POST['tipo'] == "articulo"){
        include_once("../Classes/Articulo.class.php");
        $obj = new Articulo();          
        $resultado = $obj->getRegistroById($_POST['id']);        
        if($_POST['dato'] == "descripcion"){
            echo $obj->getDescripcion();
        }else if($_POST['dato'] == "unidad medida"){
            echo $obj->getUnidadMedida();
        }else if($_POST['dato'] == "costo"){
            echo $obj->getCosto();
        }        
    }    
?>
