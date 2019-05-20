var mostrado = false;
$(document).ready(function() {
    mostrado = false;
    if ($("#cc_actual").length) {
        $("#clave_cc_contrato").val($("#cc_actual").val());
    } else {
        $("#clave_cc_contrato").val($("#clave_localidad1").val());
    }

    var form = "#formAnexo";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_Anexo.php";

    if ($("#independiente").length) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_Anexo.php";
    }

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            // orden: {required: true, maxlength: 10, minlength: 4},
            fecha_anexo2: {required: true},
            dia_corte: {number:true, min:1, max:31}
        },
        messages: {
            fecha_anexo2: {required: " * Ingrese la fecha"},
            dia_corte: {number: "Sólo se permiten números", min:"El valor mínimo es {0}", max: "El valor máximo es {0}"}
        }
    });
    $(".fecha").mask("9999-99-99");
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                    $('#mensaje_anexo2').html("El anexo se guard\u00f3  correctamente");
                    $("#cancelar_anexo").trigger("click");/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                } else {
                    $('#mensaje_anexo2').html(data);
                }
            });
        }
    });
    
    if($("#paramatros_lecturas").length && $("#id").val() != ""){        
        $("#paramatros_lecturas").load("../facturacion/Parametros_Lectura.php",{'anexo':$("#id").val(),'externo':true});        
    }
});

function mostrarParametros(){
    if(!mostrado){
        $("#paramatros_lecturas").show(10);
        $('a#liga_parametros').text('Ocultar parámetros de lecturas');
        mostrado = true;
    }else{
        $("#paramatros_lecturas").hide(10);
        $('a#liga_parametros').text('Mostrar parámetros de lecturas');
        mostrado = false;
    }
}