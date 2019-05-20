$(document).ready(function() {
    var form = "#formTipoCliente";
    var paginaExito = "admin/lista_tipocliente.php";
    var controlador = "WEB-INF/Controllers/Controler_TipoCliente.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            nombre: {required: true, maxlength: 500, minlength: 3},
            descripcion: {required: true},
            radio: {required: true}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            descripcion: {required: " * Ingrese la descripción"},
            radio: {required: " * Ingrese el radio de búsqueda"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
    
    $(document).ready(function() {
        $('.boton').button().css('margin-top', '20px');
    });
});