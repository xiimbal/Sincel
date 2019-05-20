$(document).ready(function() {
    var form = "#formEquipo";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_Inventario.php";
    if ( $( "#independiente" ).length ) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_Inventario.php";
    }
    /*var paginaExito = "admin/lista_equipos.php";*/   

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            // orden: {required: true, maxlength: 10, minlength: 4},
            no_serie2: {required: true, maxlength: 50, minlength: 4},
            ubicacion2: {required: true, maxlength: 50, minlength: 4}
        },
        messages: {
            no_serie2: {required: " * Ingrese el n\u00famero de serie", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            ubicacion2: {required: " * Ingrese el modelo", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"}            
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/                    
                    $('#mensaje_equipo2').html("El equipo ("+data+") se guard\u00f3  correctamente");                    
                    buscarEquipo();
                } else {
                    $('#mensaje_equipo2').html(data);                    
                }
            });
        }
    });
});