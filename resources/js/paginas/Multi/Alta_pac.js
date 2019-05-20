var direccion = "Multi/lista_pac.php";
$(document).ready(function() {
    var form = "#formpac";
    var controlador = "WEB-INF/Controllers/Multi/Controller_PAC.php";
    $(".boton").button();/*Estilo de botones*/
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            nombre: {required: true},
            usuario: {required: true},
            password: {required: true},
            dir_timbre: {required: true, url:true},
            dir_cancelacion: { required:true,url:true}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre del PAC"},
            usuario: {required: " * Ingrese el usuario del PAC"},
            password: {required: " *  Ingrese la contraseña del usuario del PAC"},
            dir_timbre: {required: " *  Ingrese la dirección del timbrado",url:" * Ingrese una URL valida"},
            dir_cancelacion: {required: " * Ingrese la dirección de la cancelación",url:" * Ingrese una URL valida"}
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
                    cambiarContenidos(direccion, "PAC");
                    $('#mensajes').html(data);
                } else {
                    $('#mensajes').html(data);
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});