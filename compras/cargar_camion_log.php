<!DOCTYPE html>
<html lang="es">

<head>

    <!-- ADICIÓN DE METATAGS PARA WEB RESPONSIVE -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- FIN DE LA ADICIÓN DE METATAGS PARA WEB RESPONSIVE -->    

    <title><?php echo $_SESSION['nombreEmpresa']; ?></title>    

    <script type="text/javascript" charset="utf8" src="resources/js/jquery-3.3.1.min.js"></script>
    
    <script type="text/javascript">
        var jq = $.noConflict(true);
    </script>

</head>
<body>

    <!-- CONTENEDOR PRINCIPAL DE LA VISTA -->
    <div class="container">        

        <!-- CONTENEDOR DE LA TABLA -->
        <div class="table-responsive mt-4">
            
            <table id="tabla_rutas" class="table table-hover">
                
                <!-- CABECERAS DE LA TABLA -->
                <thead>
            
                    <tr>

                        <th  class="text-center" scope="col">Fecha</th>
                        <th  class="text-center" scope="col">Ruta</th>
                        <th  class="text-center" scope="col">Destino</th>
                        <th  class="text-center" scope="col">Pedido</th>
                        <th  class="text-center" scope="col">Etiqueta</th>
                        <th  class="text-center" scope="col">Clave de Embarque</th>
                        <th  class="text-center" scope="col">No. de Control Vehicular</th>
                        <th  class="text-center" scope="col">Suma de Piezas</th>                        
                        <th  class="text-center" scope="col">Piezas en camión</th>
                        <th  class="text-center" scope="col">Cargar Camión</th>

                    </tr>

                </thead>
                <!-- FIN DE LAS CABECERAS DE LA TABLA -->

                <tbody class="text-center">                                     
                    
                </tbody>
            </table>
        </div>
        <!-- FIN DEL CONTENEDOR DE LA TABLA -->

        <button type="button" name="btn-cargar-camion" id="btn-cargar-camion" class="btn btn-block btn-primary mt-2 mb-4" btn-lg btn-block">
            <i class="fas fa-truck-loading">   </i> Iniciar Ruta  
        </button>

    </div>
    <!-- FIN DEL CONTENEDOR PRINCIPAL DE LA VISTA -->

    <!-- ENLACES HACIA LOS ESTILOS Y LAS FUNCIONALIDADES DEL DATATABLE -->
    <link rel="stylesheet" type="text/css" href="resources/DataTables/dataTables.bootstrap4.min.css">

    <script type="text/javascript" charset="utf8" src="resources/DataTables/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="resources/DataTables/datatables.min.js"></script>    
    
    <!-- ENLACE HACIA LOS ESTILOS Y LAS FUNCIONES JS -->
    <script type="text/javascript" charset="utf8" src="resources/js/compras/logistica.js"></script>    

</body>
</html>