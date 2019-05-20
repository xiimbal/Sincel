var direccion = "Bancos/lista_cuentaBancaria.php";
$(document).ready(function() {
    var form = "#formcuentaBancaria";
    var controlador = "WEB-INF/Controllers/Catalogos/Controller_CuentaBancaria.php";
    $('.boton').button().css('margin-top', '20px');
    $("#componenteCopiar").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $("#RFC").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.fecha').each(function() {
            $(this).datepicker({
                dateFormat: 'mm-dd',
                changeMonth: true,
                changeYear: true
            });
        });
    $('.fecha').mask("99-99");
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            noCuenta: {required: true},
            tipoCuenta: {required: true},
            sucursal: {required: true},
            ejecutivo: {required: true},
            fecha_corte: {required: true}
        },
        messages: {
            noCuenta: {required: " * Ingrese el número de cuenta"},
            tipoCuenta: {required: " * Ingrese el tipo de cuenta"},
            sucursal: {required: " * Ingrese la sucursal"},
            ejecutivo: {required: " * Ingrese el nombre del ejectivo"},
            fecha_corte: {required: "* Ingresa la fecha de corte"}
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
                    cambiarContenidos(direccion, "Cuentas Bancarias");
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});




