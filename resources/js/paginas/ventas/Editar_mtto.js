var direccion = "ventas/lista_mttos.php";
$(document).ready(function() {
    var form = "#formmtto";
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Editar_mtto.php";
    $(".boton").button();/*Estilo de botones*/
    $("#fecha").datepicker({
        dateFormat: 'yy-mm-dd'
    });
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            fecha: {required: true},
            NoSerie: {required: true},
            Estatus: {required: true},
            cliente: {required: true},
            localidad: {required: true}
        },
        messages: {
            fecha: {required: " * Ingrese la fecha"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                finished();
                cambiarContenidos(direccion, "Mtto Preventivo");
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});

function setdireccion(dir) {
    direccion += dir;
}