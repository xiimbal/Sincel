var direccion = "Bancos/lista_movimientos.php";
$(document).ready(function() {
    var form = "#formMovimientoBancario";
    var controlador = "WEB-INF/Controllers/Bancos/Controller_MovimientoBancario.php";
    $('.boton').button().css('margin-top', '20px');
    $("#copiarComponente").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $("#noCuenta").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true
        });        
    });
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            factura: {required: true},
            tipo: {required: true},
            total: {required: true},
            noCuenta: {required: true},
            fecha: {required: true},
            referencia: {required: true}
        },
        messages: {
            factura: {required: " * Ingrese el número de cuenta"},
            tipo: {required: " * Ingrese el tipo de cuenta"},
            total: {required: " * Ingrese la sucursal"},
            noCuenta: {required: " * Ingrese el nombre del ejectivo"},
            fecha: {required: "* Ingresa la fecha de corte"},
            referencia: {required: "* Ingresa la referencia"}
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
                    cambiarContenidos(direccion, "Movimientos Bancarios");
                }
                finished();
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});

