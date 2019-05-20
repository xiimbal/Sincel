$(document).ready(function() {
    var form = "#formProveedor";
    var paginaExito = "admin/lista_proveedor.php";
    var controlador = "WEB-INF/Controllers/Controler_Proveedor.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            txt_clave: {required: true, maxlength: 50, minlength: 2},
            txt_nombre: {required: true, maxlength: 100, minlength: 1},
            txt_rfc: {required: true, maxlength: 13, minlength: 12},
            txt_calle: {required: true, maxlength: 50, minlength: 2},
            txt_numExt: {required: true, maxlength: 50, minlength: 1},
            txt_colonia: {required: true, maxlength: 50, minlength: 2},
            txt_ciudad: {required: true, maxlength: 50, minlength: 1},
            txt_delegacion: {required: true, maxlength: 50, minlength: 1},
            txt_estado: {required: true, maxlength: 50, minlength: 1},
            txt_pais: {required: true, maxlength: 50, minlength: 1},
            txt_cp: {required: true, number: true, maxlength: 5, minlength: 5},
            sl_tipo: {selectcheck: true}
        },
        messages: {
            txt_clave: {required: " * Ingrese la clave del proveedor", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_rfc: {required: " * Ingrese el RFC", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_calle: {required: " * Ingrese la calle", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_numExt: {required: " * Ingrese el número exterior", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_colonia: {required: " * Ingrese la colonia", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_ciudad: {required: " * Ingrese la ciudad", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_delegacion: {required: " * Ingrese la delegación", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_estado: {required: " * Ingrese el estado", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_pais: {required: " * Ingrese el pais", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txt_cp: {required: " * Ingrese código postal", number: "Ingrese solo números", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            event.preventDefault();
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
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