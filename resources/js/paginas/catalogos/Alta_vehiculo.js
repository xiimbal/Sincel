var direccion = "catalogos/lista_vehiculo.php";
$(document).ready(function() {
    var form = "#formvehiculo";
    var controlador = "WEB-INF/Controllers/Catalogos/Controller_Vehiculo.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            placas: {required: true},
            modelo: {required: true},
            capacidad: {required: true, maxlength: 2, minlength: 1, }
        },
        messages: {
            placas: {required: " * Ingrese las placas"},
            modelo: {required: " * Ingrese el Modelo"},
            capacidad: {required: " * Ingrese la Capacidad", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    cambiarContenidos(direccion, "Vehículos");
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });

});
