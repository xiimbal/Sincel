<?
/**
 *
 */

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

class loadcsv{

private $empresa;


public function prueba($noparte, $modelo, $descripcion)
{
	//global $conexion;
	$sentencia = "insert into c_componente(NoParte, IdTipoComponente, Modelo, Descripcion ) values ('$noparte',8,'$modelo','$descripcion')";
	$catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($sentencia);
    return $query;
	//$ejecutar = mysqli_query($conexion,$sentencia);
	//return $ejecutar;
	
}


public function prueba2($noPedido, $dir, $transportista)
{

    $nAlmacen = "SELECT dal.IdAlmacen AS ID
                FROM c_domicilio_almacen AS dal
                WHERE CONCAT (dal.Calle,' ', dal.NoExterior,' ',dal.Colonia,' ',dal.Delegacion,' ',dal.Ciudad,' ',dal.CodigoPostal ) 
                ='$dir'";
    $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($nAlmacen);
        while ($rs = mysql_fetch_array($query)) {
            $ID  = $rs['ID'];           
        }        



        $nAl = "SELECT men.idMensajeria FROM c_mensajeria men WHERE men.Nombre = '$transportista' ";
    $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($nAl);
        while ($rs = mysql_fetch_array($query)) {
            $idmen  = $rs['idMensajeria'];           
        }      
    //global $conexion;

    $sentencia = "insert into c_orden_compra(NoPedido, FechaOrdenCompra, FacturaEmisor, FacturaReceptor, CondicionesPago, Estatus, IdAlmacen, Embarca, Transportista, Activo ) values ('".$noPedido."', now(), 980, 1000, 7, 71, ".$ID.", '".$dir."', ".$idmen.", 1)";
    $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($sentencia);
    return $query;
    //$ejecutar = mysqli_query($conexion,$sentencia);
    //return $ejecutar;

    echo $sentencia;
    
}

public function prueba3($noparte,$cantidad)
{

     
$ordencom = "SELECT w.Id_orden_compra FROM c_orden_compra w order by w.Id_orden_compra
desc limit 1";
    $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($ordencom);
        while ($rs = mysql_fetch_array($query)) {
            $por  = $rs['Id_orden_compra'];           
        }      


    $sentencia = "insert into k_orden_compra(IdOrdenCompra, NoParteComponente, Cantidad) values ($por, '$noparte', $cantidad)";
    $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($sentencia);
    return $por;
    //$ejecutar = mysqli_query($conexion,$sentencia);
    //return $ejecutar;
    
}



  public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    }
?>