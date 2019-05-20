$(document).ready(function() {
    
    if($("#nuevo_contacto").val()!=""){/*Si se va a editar, pre-cargamos los valores con lo que hay en ticket*/
        $("#nombre_contacto2").val($("#nombre_contacto1").val());
    }
    
    var form = "#formContacto";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_Contacto.php";
    if ( $( "#independiente" ).length ) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_Contacto.php";
    }

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {            
            nombre_contacto2: {required: true, maxlength: 50, minlength: 3},
            telefono_contacto2: {required: true},
            tipo_contacto2: {selectcheck: true},
            correo_contacto2: {email: true}
        },
        messages: {
            nombre_contacto2: {required: " * Ingrese el nombre", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            telefono_contacto2: {required: " * Ingrese el tel\u00e9fono"},
            correo_contacto2: {email: " * Ingrese un correo v\u00e1lido"}
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
                    $('#mensaje_contacto2').html("El contacto se guard\u00f3  correctamente");                     
                     $( "#cancelar_contacto" ).trigger( "click" );/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                } else {
                    $('#mensaje_contacto2').html(data);                    
                }
            });
        }
    });
});