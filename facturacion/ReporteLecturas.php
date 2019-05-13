<?php

    session_start();

    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: ../index.php");
    }

    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");
    
    $usuario = new Usuario();

?>

<!DOCTYPE html>
<html lang="es">
    
    <!-- METATAGS PARA HACER SITIO RESPONSIVE -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- FIN DE LOS METATAGS PARA HACER SITIO RESPONSIVE -->

    <head>               
        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ReporteLecturas.js"></script>
        
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">

    </head>    

    <body>
        
        <!-- CONTENEDOR PRINCIPAL DEL CONTENIDO  -->
        <div class="principal">


            <div id="divinfoup">         
            </div>
            

            <!-- FORMULARIO -->
            <form id="FormLectura" name="FormLectura" action="reportes/ReporteLecturaPDF.php" target="_blank" method="POST" novalidate>


                <!-- CONTENEDOR DEL FORMULARIO -->
                <div class="container-fluid">                    


                        <?php

                            /* SE CREA UNA INSTANCIA DE LA CLASE CATALOGO QUE SE ENCARGA DE REALIZAR LA CONSULTA A L BD (A TRAVES DE LA CLASE CONEXION.CLASS.PHP) */
                            $catalogo = new Catalogo();

                            /* CONSULTA PARA CARGAR EL ID DEL PUESTO DEL USUARIO (PARA RECTIFICAR SI ES VENDEDOR O NO) */
                            $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto 
                                                              FROM `c_usuario` 
                                                              INNER JOIN c_puesto 
                                                              ON c_usuario.IdPuesto = c_puesto.IdPuesto 
                                                              WHERE c_usuario.IdUsuario = " . $_SESSION['idUsuario']);
                            
                            /* SE OBTIENEN LOS DATOS DE LA CONSULTA */
                            $relsultado_consulta = mysql_fetch_array($query);

                            /* SE EVALUA LA CONDICION PARA SABER SI ES VENDEDOR O NO (EN FUNCION DEL RESULTADO SE GENERARA UN SELECT EN EL HTML) */
                            if ($relsultado_consulta['IdPuesto'] != 11) { ?>

                                <!-- CONTENEDOR DEL SELECT QUE CARGA A LOS VENDEDORES -->
                                <div class="form-group">

                                    <label for="vendedor">Vendedor</label>
                                    
                                    <select id="vendedor" class="form-control" name="vendedor" onchange="cargarclientes('vendedor', 'cliente');">
                                        
                                        <option value="">Selecciona el vendedor</option>
                                            
                                            <!-- EL PHP SE ENCARGA DE REALIZAR CONSULTA PARA TRAER A LOS VENDEDORES -->
                                            <?php

                                                // SE REALIZA UNA CONSULTA PARA OBTENER EL NOMBRE DE LOS VENDEDORES 
                                                $query = $catalogo->obtenerLista("
                                                    SELECT c_usuario.IdUsuario,
                                                    CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre 
                                                    FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto 
                                                    WHERE c_puesto.IdPuesto=11 AND c_usuario.Activo = 1 ORDER BY Nombre");
                                                
                                                // CON EL BUCLE SE RECORRE EL RESULTADO DE LA CONSULTA Y SE AGREGAN EN UN ELEMENTO DEL SELECT
                                                while ($rs = mysql_fetch_array($query)) {
                                                    echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                                }

                                            ?>

                                    </select>

                                </div> 
                                <!-- CONTENEDOR DEL SELECT QUE CARGA A LOS VENDEDORES -->                                                 

                        <?php } ?> <!-- Linea que cierra la llave el IF  -->
                        

                        <!-- CONTENEDOR DE LOS SELECTS QUE CARGAN A LOS CLIENTES, CONTRATOS, ZONAS Y ANEXOS --> 
                        <div class="form-row">

                            <!-- CONTENEDOR DEL SELECT QUE CARGA A LOS CLIENTES -->
                            <div class="form-group col-md-3">
                              
                                <label for="cliente">Cliente</label>
    
                                <select id="cliente" class="form-control " name="cliente" onchange="cargarContratos('cliente', 'contrato');">
                                        
                                    <option value="">Selecciona el cliente</option>
    
                                    <?php
                                        
                                        if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)){
                                            
                                            $query = $catalogo->obtenerLista("SELECT
                                                    c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                                    c_cliente.ClaveCliente AS ClaveCliente
                                                    FROM c_usuario
                                                    INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario
                                                    INNER JOIN c_cliente ON c_cliente.ClaveCliente = k_tfscliente.ClaveCliente
                                                    WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                                    ORDER BY NombreRazonSocial ASC");
                                            
                                        }else if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)){
    
                                            $query = $catalogo->obtenerLista("SELECT
                                                    c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                                    c_cliente.ClaveCliente AS ClaveCliente
                                                    FROM c_usuario
                                                    INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                                    WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                                    ORDER BY NombreRazonSocial ASC;");
                                        }else{
                                            
                                            $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE Activo=1 ORDER BY NombreRazonSocial");
                                        }
                                        
                                        while ($rs = mysql_fetch_array($query)) {                                           
                                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreRazonSocial'] . "</option>";
                                        }
                                    ?>

                                </select>

                            </div>
                            <!-- FIN DEL CONTENEDOR DEL SELECT QUE CARGA LOS CLIENTES --> 

                            <!-- CONTENEDOR DEL SELECT QUE CARGA LOS CONTRATOS -->
                            <div class="form-group col-md-3">

                                <label for="cliente">Contrato</label>

                                <select id="contrato" class="form-control" name="contrato" onchange="cargarZona('contrato', 'zona'); cargarAnexos('contrato', 'anexo');">
                                    <option value="">Todos los contratos</option>
                                </select>                                

                            </div>  
                            <!-- FIN DEL CONTENEDOR DEL SELECT QUE CARGA LOS CONTRATOS -->                                

                            <!-- CONTENEDOR DEL SELECT QUE CARGA LAS ZONAS -->
                            <div class="form-group col-md-3">

                                <label for="zona">Zona</label>   
                                <select id="zona" class="form-control" name="zona" onchange="cargarCentroCosto('cliente', 'centro_costo','contrato','zona');">
                                    <option value="">Todas las zonas</option>
                                </select>                                

                            </div>
                            <!-- FIN DEL CONTENEDOR DEL SELECT QUE CARGA LAS ZONAS --> 
                                        
                            <!-- CONTENEDOR DEL SELECT QUE CARGA LOS ANEXOS -->                             
                            <div class="form-group col-md-3">
                                
                                <label for="anexo">Anexo:</label>
                                <select id="anexo" name="anexo" class="form-control" onchange="cargarLectura();">    
                                    <option value="">Todas los anexos</option>                                    
                                </select>
                            
                            </div>
                            <!-- FIN DEL CONTENEDOR DEL SELECT QUE CARGA LOS ANEXOS --> 

                        </div>  
                        <!-- FIN DEL CONTENEDOR DE LOS SELECTS QUE CARGAN A LOS CLIENTES, CONTRATOS, ZONAS Y ANEXOS -->                                                       


                        <!-- CONTENEDOR DE LOS SELECTS QUE CARGAN A LOS CENTRO COSTO Y LOCALIDAD -->                                             
                        <div class="form-row">
                            
                            <!-- CONTENEDOR DEL SELECT QUE CARGA CENTRO COSTO -->
                            <div class="form-group col-md-4">
                                
                                <label for="centro_costo">Centro costo</label>
                                
                                <select id="centro_costo" class="form-control" name="centro_costo" onchange="cargarCentroCosto('cliente', 'centro_costo','contrato','zona');">
                                    <option value="">Selecciona el centro de costo</option>
                                </select>

                            </div>
                            <!-- FIN DEL CONTENEDOR DEL SELECT QUE CARGA CENTRO COSTO -->

                            <!-- CONTENEDOR DEL SELECT QUE CARGA LA LOCALIDAD-->
                            <div class="form-group col-md-4">

                                <label for="localidad">Localidad</label>

                                <select id="localidad" name="localidad" class="form-control">
                                    <option value="">Todas las localidades</option>                            
                                </select>

                            </div>
                            <!-- FIN DEL CONTENEDOR DEL SELECT QUE CARGA LA LOCALIDAD -->


                            <!-- CONTENEDOR DEL INPUT QUE CONTIENE LA FECHA -->            
                            <div class="form-group col-md-4">
                                
                                <label for="fecha">Fecha</label>
                                <input type="text" class="fecha form-control" id="fecha" name="fecha" />

                            </div>
                            <!-- FIN DEL CONTENEDOR DEL INPUT QUE CONTIENE LA FECHA -->    


                            <button type="submit" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" id="submit_lecturas" name="submit_lecturas">
                                <i class="fas fa-file-chart-line"></i> Generar reporte 
                            </button>                       


                        </div>
                        <!-- FIN DEL CONTENEDOR DE LOS SELECTS QUE CARGAN A LOS CENTRO COSTO Y LOCALIDAD -->                                                                               


                </div>                                
                <!-- FIN DEL CONTENEDOR DEL FORMULARIO -->


                <div id="parametros_lectura"></div>       


            </form>  
            <!-- FIN DEL FORMULARIO -->                                        


        </div> 
        <!-- FIN DEL CONTENEDOR PRINCIPAL DEL CONTENIDO  -->
        

        <!-- Bootstrap core JavaScript -->
        <script type="text/javascript" src="resources/js/Bootstrap 4/bootstrap.min.js"></script>

        <!-- Bootstrap tooltips -->
        <!-- <script type="text/javascript" src="resources/js/Bootstrap 4/popper.min.js"></script> -->

    </body>

</html>