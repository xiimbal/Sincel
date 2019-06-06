<?php
    session_start();    
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $_SESSION['nombreEmpresa']; ?></title>
    
    <!-- ADICION DE LIBRERÍA PARA CREAR ALERTS -->
    <script type="text/javascript" charset="utf8" src="resources/js/sweetalert.min.js"></script>

</head>
<body>
    
    <!-- CONTENEDOR PRINCIPAL DE LA INTERFAZ -->
    <div class="container-fluid">
        
        <div class="card">
            <div class="card-header">
                <h2 class="text-primary text-center mb-3">Buscar por fecha de embarque</h2>
            </div>            
            <div class="card-body">
                <form id="busqueda" class="mt-3 form-inline">
                    <div class="form-group mr-2">
                        <label class="mr-2" for="fecha_embarque">Fecha de Embarque</label>
                        <input type="date" name="fecha_embarque" id="fecha_embarque" class="form-control" placeholder="Fecha Embarque" aria-describedby="Fecha de embarque">                            
                    </div>
                    <button name="buscar_rutas" id="buscar_rutas" class="btn btn-primary" href="" role="button"> <i class="fa fa-print-search"></i> Buscar</button>
                </form>
            </div>
            <div id="resultado_consulta" class="">
                
            </div>
        </div>                

    </div>
    <!-- FIN DEL CONTENEDOR PRINCIPAL DE LA INTERFAZ -->

    

<script>
    $(document).ready(function () {        
        
        $("#buscar_rutas").on("click", function (e) {
            e.preventDefault();
            $("#resultado_consulta").empty();     
            let fecha = $("#fecha_embarque").val();                     
            if ( fecha == '') {
                $("#resultado_consulta").append("<h5 class='text-center text-muted'>Primero seleccione una fecha.</h5>");
            }else{
                $.ajax({
                    method: "POST",
                    url: "WEB-INF/Controllers/Controler_Asignar_Chofer.php",
                    data: {                
                        "proceso": "consultar",
                        "fecha_embarque": fecha
                    }
                }).done(function (info) {
                    // console.log(info);                
                    let respuesta_servidor = JSON.parse(info);                               
                    if (respuesta_servidor.claves_vehiculares.length == 0) {                 
                        $("#resultado_consulta").empty();                     
                        $("#resultado_consulta").addClass("card-footer mb-4");                     
                        $("#resultado_consulta").append("<h5 class='text-center text-muted'>No existen pedidos para esta fecha.</h5>");
                    }else{
                        // console.log(respuesta_servidor.claves_vehiculares[0].CV);                        
                        $("#resultado_consulta").addClass("card-footer mb-4");                                     
                        for (let elemento = 0; elemento < respuesta_servidor.claves_vehiculares.length; elemento++) {

                            $("#resultado_consulta").append(
                                "<div class='form-group mb-1'>"
                                    + "<label for='clave_vehicular_"+ elemento +"'>Clave Vehicular</label>"
                                    + "<input type='text' id='clave_vehicular_"+ elemento +"' class='form-control' value='"+ respuesta_servidor.claves_vehiculares[elemento].CV + "' readonly>"
                                + "</div>"
                                + "<div class='form-group'>"
                                    + "<label for='conductor_"+ elemento +"'>Conductor</label>"
                                    + "<select class='form-control' id='conductor_"+ elemento +"'>"                                    
                                    + "</select>"
                                    + "<button name='asignar_chofer_"+ elemento +"' id='asignar_chofer' onClick='cargarDatos($(this))' class='btn btn-success mt-3' role='button'> <i class='fa fa-person-dolly'></i> Asignar Conductor</button>"
                                + "</div>"
                                + "<hr>");  
                                                        
                            for (let item = 0; item < respuesta_servidor.conductores.length; item++) {                                
                                
                                $("#conductor_"+ elemento +"").append("<option id='" + respuesta_servidor.conductores[item].IdMensajeria + "'>" + respuesta_servidor.conductores[item].Nombre + "</option>");                          
                            }    
                        }                    
                    }
                })
            }
        });         

    });    

</script>

<script>

    function cargarDatos(button) {        

        let campos = $(button).attr("name");   
        $(button).addClass("disabled");
        
        let valor_campos = campos.split("_");                                

        let clave_vehicular = $("#clave_vehicular_"+valor_campos[2]).val();
        let conductor = $("#conductor_"+valor_campos[2]).children(":selected").attr("id");
        let fecha_embarque = $("#fecha_embarque").val();

            // console.log(clave_vehicular);
            // console.log(conductor);

        $.ajax({
            method: "POST",
            url: "WEB-INF/Controllers/Controler_Asignar_Chofer.php",
            data: {
                "proceso": "insertar",
                "fecha_embarque": fecha_embarque,
                "clave_vehicular": clave_vehicular,
                "conductor": conductor
            }
        }).done (function(info){
            
            let respuesta_servidor = JSON.parse(info);

            if (respuesta_servidor = "Exito") {
                swal({

                    icon: "success",

                    text: "Se asigno correctamente el conductor.",

                });
            } else {
                swal({

                    title: "¡Algo salió mal!",

                    text: "Al parecer ha ocurrido un error, pruebe intentarlo en un momento",

                    icon: "error",

                });
            }

        });
        
    }             
    
</script>
</body>
</html>