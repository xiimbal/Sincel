<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");

$empresa;

$nombre_imagen = $_FILES['imagen']['name'];

$tipo_imagen = $_FILES['imagen']['type'];
$tamaño_imagen = $_FILES['imagen']['size'];

$comenta = $_POST['comentario'];

//echo $variable;
//echo $_FILES['imagen']['name'];
//echo $_FILES['imagen']['type'];
//echo  $_FILES['imagen']['size'];

if($tamaño_imagen<=500000000)
{

	if ($tipo_imagen=="image/jpeg" || $tipo_imagen=="image/jpg" || $tipo_imagen=="image/png" || $tipo_imagen=="image/gif") 
	{

		$carpeta_destino=$_SERVER['DOCUMENT_ROOT'].'/resources/imag_evidencias/';
		move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta_destino.$nombre_imagen);
		
	}
	else
	{
		echo "Verifique que el archivo sea de extencion (jpeg, jpg, png, gif)";
	}
	}
	else
	{

	echo "La imagen es demaciado grande";
}
$id_rec = $_POST['id_eve'];

$sql="INSERT INTO c_evidencia (id_eve, nombre, coment) VALUES ('$id_rec','$nombre_imagen','$comenta') ";
//echo "CON: ".$sql;
//$resultado=mysqli_query($conexion, $sql);

$catalogo = new Catalogo();
        if (isset($empresa)) {
            $catalogo->setEmpresa($empresa);
        } $query = $catalogo->obtenerLista($sql);
   


echo '<script language="javascript">alert("Los datos se insertaron correctamente");
			window.location.href = "../principal.php?mnu=compras&action=lista_entrada_orden_compra&id= ";
		</script>';

			 return $query;


?>