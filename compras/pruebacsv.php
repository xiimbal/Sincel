<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Subir archivo a la BD mysql</title>
	<script type="text/javascript" src="resources/js/funciones.js"></script>
</head>
<body>
	 
	 <div class="formulario bg-light">
	 	<center>
	 	<form action="../WEB-INF/Controllers/controlcsv.php"  class="formulariocompleto" method="post" enctype="multipart/form-data">
	 		<label for="imagen" class="form-control">Inserte archivo con extencion CSV</label>
	 		 <input type="file" name="archivo" required />
	 		<input type="submit" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" value="Subir archivo" name="enviar" >
	 	</form>
	 	</center>
	 </div>
</body>
</html>


