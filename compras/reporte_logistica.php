<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include("../WEB-INF/Classes/Catalogo.class.php");

$fila='';
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

	<div class="formulario bg-light" >

		<a href="compras/crear_pdf_logistica.php">Crear PDF </a>
        
		<div class="table-responsive">

			<table class="table">
				<tr>
					<td>Numero de embarque</td>
					<td>Tienda</td>
					<td>Bultos</td>
					<td>Ciudad destino</td>
					<td>Fecha de Embarque</td>
					<td>Notas</td>
					<td>Control vehicular</td>
				</tr>
			
			<?php

			$catalogo = new Catalogo();

			$sql="SELECT oc.NoPedido, al.nombre_almacen,  koc.Cantidad, cda.Ciudad, oc.FechaOrdenCompra, oc.Notas, me.Nombre
					FROM k_orden_compra AS koc
					RIGHT JOIN c_orden_compra AS oc ON koc.IdOrdenCompra = oc.Id_orden_compra 
					INNER JOIN c_almacen AS al ON oc.IdAlmacen = al.id_almacen 
					INNER JOIN c_domicilio_almacen AS cda ON al.id_almacen = cda.IdAlmacen
					INNER JOIN c_mensajeria AS me ON me.IdMensajeria = oc.Transportista ";

					/*$consultafiltros = $sql."WHERE oc.NoPedido = '".$variable1."' OR al.nombre_almacen = ".$variable2 ;
					$variable1 = 323423*/
	        if (isset($empresa)) {
	            $catalogo->setEmpresa($empresa);
	        } 

	        $query = $catalogo->obtenerLista($sql);
	        while ($fila=mysql_fetch_array($query)) {
			?>

			<tr>

				<td><?php echo $fila ['NoPedido'];  ?></td>
				<td><?php echo $fila ['nombre_almacen'];  ?></td>
				<td><?php echo $fila ['Cantidad'];  ?></td>
				<td><?php echo $fila ['Ciudad'];  ?></td>
				<td><?php echo $fila ['FechaOrdenCompra'];  ?></td>
				<td><?php echo $fila ['Notas'];  ?></td>
				<td><?php echo $fila ['Nombre'];  ?></td>
				
			</tr>



		<?php } ?>

		</table>
		</div>
	</div>
</body>
</html>