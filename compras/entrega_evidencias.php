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
	<title></title>
</head>
<body>
	<div class="formulario bg-light" >
	<center>

		<form method="post" action ="/compras/proceso_evidencias.php" enctype="multipart/form-data">
			<label for="imagen" class="form-control">Inserte evidencia</label>

			<input type="file" name="imagen"   size="20" required />
            <input type="hidden" name="id_eve" value="<?php echo $_GET['id']; ?> "> <br>
            <label for="imagen" class="form-control">Comentarios</label>
            <textarea id="comentario" class="form-control" name="comentario" rows="5" cols="50" placeholder="Escribe un comentario"></textarea>
            <input type="submit" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" value="Enviar" />
        </form>
	</center>
</div>

</body>
</html>