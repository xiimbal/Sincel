var direccion = "Bancos/lista_bancos.php";
$(document).ready(function() {
    var form = "#formbanco";
    var controlador = "WEB-INF/Controllers/Catalogos/Controller_Banco.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            nombre: {required: true},
        },
        messages: {
            nombre: {required: " * Ingrese el nombre"}
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
                    cambiarContenidos(direccion, "Bancos");
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});



