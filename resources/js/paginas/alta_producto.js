$(document).ready(function() {
    var form = "#formProducto";
    var paginaExito = "admin/lista_producto.php";
    var controlador = "WEB-INF/Controllers/Controler_Producto.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {            
            nombre: {required: true, maxlength: 50, minlength: 4},
            descripcion: {required: true, maxlength: 50, minlength: 4},
            orden: {maxlength: 3, minlength: 1, number: true}            
        },
        messages: {            
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            descripcion: {required: " * Ingrese la descripci\u00f3n", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            orden: {maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres", number: " * Ingresa solamente n\u00fameros"}           
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
});