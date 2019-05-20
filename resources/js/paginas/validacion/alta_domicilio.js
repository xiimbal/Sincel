$(document).ready(function() {
    
    if($("#nuevo_domicilio").val()!=""){/*Si se va a editar, pre-cargamos los valores con lo que hay en ticket*/
        $("#calle2").val($("#calle1").val());
        $("#exterior2").val($("#exterior1").val());
        $("#interior2").val($("#interior1").val());
        $("#colonia2").val($("#colonia1").val());
        $("#delegacion2").val($("#delegacion1").val());
        $("#ciudad2").val($("#ciudad1").val());
        $("#estado2").val($("#estado1").val());
        $("#cp2").val($("#cp1").val());
    }
    
    var form = "#formDomicilio";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_Domicilio.php";
    if ( $( "#independiente" ).length ) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_Domicilio.php";
    }

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {            
            calle2: {required: true, maxlength: 150, minlength: 3},
            exterior2: {required: true, maxlength: 150, minlength: 1},
            interior2: {maxlength: 150, minlength: 1},
            colonia2: {required: true, maxlength: 150, minlength: 1},
            delegacion2: {required: true, maxlength: 150, minlength: 1},
            ciudad2: {required: true, maxlength: 150, minlength: 1},
            estado2: {required: true, maxlength: 150, minlength: 1},
            cp2: {required: true, number:true, maxlength: 150, minlength: 1}
        },
        messages: {
            calle2: {required: " * Ingrese la calle", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            exterior2: {required: " * Ingrese el n\u00famero exterior", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            interior2: {required: " * Ingrese el n\u00famero interior", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            colonia2: {required: " * Ingrese la colonia", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"} ,           
            delegacion2: {required: " * Ingrese la delegaci\u00f3n", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            ciudad2: {required: " * Ingrese la ciudad", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"}   ,         
            estado2: {required: " * Ingrese el estado", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            cp2: {required: " * Ingrese el cp", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"}            
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
                    $('#mensaje_domicilio2').html("El domicilio se guard\u00f3  correctamente");                     
                     $( "#cancelar_domicilio" ).trigger( "click" );/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                } else {
                    $('#mensaje_domicilio2').html(data);                    
                }
            });
        }
    });
});