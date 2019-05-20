$(document).ready(function() {
    var form = "#formCliente";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_Cliente.php";
    if ( $( "#independiente" ).length ) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_Cliente.php";
    }

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");
    
    /*jQuery.validator.addMethod('validaRfc', function(value) {
        var rfcStr = $("#rfc_cliente2").val();
        var strCorrecta;
        var valid;
        strCorrecta = rfcStr;
        if (rfcStr.length == 12) {
            valid = '^(([A-Z]|[a-z]){3})([0-9]{6})((([A-Z]|[a-z]|[0-9]){3}))';
        } else {
            valid = '^(([A-Z]|[a-z]|\s){1})(([A-Z]|[a-z]){3})([0-9]{6})((([A-Z]|[a-z]|[0-9]){3}))';
        }
        var validRfc = new RegExp(valid);
        var matchArray = strCorrecta.match(validRfc);
        if (matchArray == null) {
            //$("#error_rfc").text("El RFC es invalido");
            return false;
        }    
        //$("#error_rfc").text("");
        return true;
    }, " * El RFC es inv\u00e1lido");*/

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            // orden: {required: true, maxlength: 10, minlength: 4},
            nombre_cliente2: {required: true, maxlength: 100, minlength: 3},
            rfc_cliente2: {required: true, maxlength: 13, minlength: 12},
            razon_cliente2: {selectcheck: true},
            modalidad2: {selectcheck: true},
            tipo_facturacion: {selectcheck: true},
            tipo_morosidad: {selectcheck: true}
        },
        messages: {
            nombre_cliente2: {required: " * Ingrese el nombre", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            rfc_cliente2: {required: " * Ingrese el RFC", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Guardando ... ");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/                    
                    $('#mensaje_cliente2').html("El cliente ("+data+") se guard\u00f3  correctamente");
                    $("#servicios_p2").empty(); $("#servicios_g2").empty();
                    finished();
                    buscarCliente(data);
                } else {
                    $('#mensaje_cliente2').html(data);
                    finished();
                }                
            });
        }
    });
});