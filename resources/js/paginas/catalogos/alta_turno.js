var direccion = "catalogos/lista_turnos.php";
$(document).ready(function() {
    var form = "#formTurno";
    var controlador = "WEB-INF/Controllers/Catalogos/Controller_Turno.php";
    $('.boton').button().css('margin-top', '20px');

    $(form).validate({
        rules: {
            descripcion: {required: true, maxlength: 100, minlength: 2}
        },
        messages: {
            descripcion: {required: " * Ingrese la clave del proveedor", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
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
                    cambiarContenidos(direccion, "");
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});







