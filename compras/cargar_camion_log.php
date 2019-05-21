<!DOCTYPE html>
<html lang="es">

<head>

    <!-- ADICIÓN DE METATAGS PARA WEB RESPONSIVE -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- FIN DE LA ADICIÓN DE METATAGS PARA WEB RESPONSIVE -->    

    <title><?php echo $_SESSION['nombreEmpresa']; ?></title>      

    <!-- LINK HACIA BOOTSTRAP, ESTILOS DE LA SIDENAV E ICONOS FONTAWESOME -->
    <link href="../resources/css/Bootstrap 4/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="../resources/css/Bootstrap 4/css/all.min.css" rel="stylesheet">

    <!-- ADICION DE LIBRERIA DATATABLE -->
    <script language="javascript" type="text/javascript" src="../resources/js/jquery-3.3.1.min.js"></script>    

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

                        <th  class="text-center" scope="col">Ruta</th>
                        <th  class="text-center" scope="col">Destino</th>
                        <th  class="text-center" scope="col">Pedido</th>
                        <th  class="text-center" scope="col">Etiqueta</th>
                        <th  class="text-center" scope="col">Suma de Piezas</th>                        
                        <th  class="text-center" scope="col">Piezas Dañadas</th>
                        <th  class="text-center" scope="col">Cargar Camión</th>
                        <th  class="text-center" scope="col">Observaciones</th>

                    </tr>

                </thead>
                <!-- FIN DE LAS CABECERAS DE LA TABLA -->

                <tbody>

                    <tr class="text-center">
                        <th scope="row">23</th>
                        <td>Puebla</td>
                        <td>21345655431</td>
                        <td>TO41531000631222002</td>
                        <td>7</td>
                        <td>0</td>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="validar">                            
                                <label class="custom-control-label" for="validar">Cargar Camión</label>
                            </div>
                        </td>
                        <td></td>
                    </tr> 
                                       
                    <tr class="text-center">
                        <th scope="row">23</th>
                        <td>Tulancingo</td>
                        <td>1032423556</td>
                        <td>TO415323245675431</td>
                        <td>12</td>
                        <td>2</td>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="validar">                            
                                <label class="custom-control-label" for="validar">Cargar Camión</label>
                            </div>
                        </td>
                        <td></td>
                    </tr>    

                    <tr class="text-center">
                        <th scope="row">23</th>
                        <td>CDMX</td>
                        <td>23323454</td>
                        <td>TO415313245406622001</td>
                        <td>50</td>
                        <td>5</td>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="validar">                            
                                <label class="custom-control-label" for="validar">Cargar Camión</label>
                            </div>
                        </td>
                        <td></td>
                    </tr>     
                                        
                    <tr class="text-center">
                        <th scope="row">23</th>
                        <td>Guadalajara</td>
                        <td>1000606622</td>
                        <td>TO41531000606622001</td>
                        <td>25</td>
                        <td>2</td>
                        <td>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="validar">                            
                                <label class="custom-control-label" for="validar">Cargar Camión</label>
                            </div>
                        </td>
                        <td></td>
                    </tr>                    
                    
                </tbody>
            </table>
        </div>
        <!-- FIN DEL CONTENEDOR DE LA TABLA -->

        <button type="button" name="btn-cargar-camion" id="btn-cargar-camion" class="btn btn-block btn-success mt-2 mb-4" btn-lg btn-block">
            <i class="fas fa-truck-loading">   </i> Iniciar Ruta  
        </button>

    </div>
    <!-- FIN DEL CONTENEDOR PRINCIPAL DE LA VISTA -->

    <!-- ENLACES HACIA LOS ESTILOS Y LAS FUNCIONALIDADES DEL DATATABLE -->
    <link rel="stylesheet" type="text/css" href="../resources/DataTables/datatables.min.css">
    <script type="text/javascript" charset="utf8" src="../resources/DataTables/datatables.min.js"></script>

<script>
    $(document).ready( function () {
        $('#tabla_rutas').DataTable();
    } );
</script>    
</body>
</html>