<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}


if(isset($_POST["enviar"]))
{

	//include("conexion.php");
	include_once("../Classes/Conexion.class.php");
	include_once("../Classes/loadcsv.class.php");
	
	$archivo = $_FILES["archivo"]["name"];
	$archivotemporal = $_FILES["archivo"]["tmp_name"];


	//echo $archivo. "Esta en la ruta ".$archivotemporal ."<br/>";



	if (file_exists($archivotemporal))
	{

		$lectura = fopen($archivotemporal, "r");
		$rows = 0;
		setlocale(LC_ALL, 'es_ES.UTF8');
		$VAR = 0;

		while ($data = fgetcsv($lectura, 1000 , ",")) 
		{
			$data = array_map("utf8_encode", $data);
			$rows ++;

			if ($rows > 1) 
			{	
				$loadcsv = new loadcsv();
				$resultado = $loadcsv->prueba($data[0], $data[1], $data[2]);
				if ($VAR == 0) 
				{
					$resultado2 = $loadcsv->prueba2($data[3],$data[4],$data[5]);
					$VAR=1;
				}
				elseif ($VAR != 0) {
					
				}
				$resultado3 = $loadcsv->prueba3($data[0],$data[6]);
				//print_r($resultado);
			}
			//echo $data[0] ." " .$data[1] ."<br/>";
			
		}

		print_r( $_SESSION ['user']);  

		if ($resultado)
		{
			echo '<script language="javascript">alert("Los datos se insertaron correctamente");
			window.location.href = "../../principal.php?mnu=compras&action=alta_orden_compra&id='.$resultado3.'";
			</script>';
		}
		else 
		{
			echo '<script language="javascript">alert("Ocurrio un error por favor verifique su archivo csv");
			window.close();	
			</script>'; 
		}
	}
}

?>