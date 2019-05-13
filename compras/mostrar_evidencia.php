<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

	<div class="formulario bg-light" >
		<div class="form-row">
			<?php
			include("../WEB-INF/Classes/Catalogo.class.php");
			//include("conexion.php");


			
			$catalogo = new Catalogo();

			$sql="SELECT * FROM c_evidencia WHERE id_eve='".$_GET['id']."'";
			//$resultado=mysqli_query($catalogo, $sql);
			//echo "CON: ".$sql;
	
	        if (isset($empresa)) {
	            $catalogo->setEmpresa($empresa);
	        } 

	        $query = $catalogo->obtenerLista($sql);
	        while ($fila=mysql_fetch_array($query)) {
	        	//print_r($fila);

	        	//$comntari = $fila['coment'];
				//$ruta_img=$fila['nombre'];
				//echo "<br>Comentario: ". $comntari;

				echo "<div class='form-group col-md-3'>
						<center>
						<img src='../resources/imag_evidencias/".$fila['nombre']."' class='img-fluid' alt='imagenes  de los articulos'> 
						</center>
						<br>";
				 echo "<textarea class='form-control' type='text' placeholder='".$fila['coment']."'></textarea></div>";
			}
			?>
		</div>
	</div>
</body>
</html>