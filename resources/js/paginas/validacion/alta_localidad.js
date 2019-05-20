$(document).ready(function() {
    var form = "#formLocalidad";
    var controlador = "WEB-INF/Controllers/Validacion/Controler_Localidad.php"; 
    if ( $( "#independiente" ).length ) {
        controlador = "../WEB-INF/Controllers/Validacion/Controler_Localidad.php";
    }
    
    $(".boton").button().css('font-size','12px');

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            // orden: {required: true, maxlength: 10, minlength: 4},
            clave_cc2: {required: true, maxlength: 50, minlength: 3},
            nombre_cc2: {required: true, maxlength: 50, minlength: 3},
            Calle:{required:true},
            NoExterior:{required:true},
            Colonia:{required:true},
            Estado:{required:true},
            Delegacion:{required:true},
            CP:{required:true},
            zona: {selectcheck:true}
        },
        messages: {
            clave_cc2: {required: " * Ingrese la clave", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            nombre_cc2: {required: " * Ingrese el nombre", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},           
            Calle:{required:" * Ingrese la calle"},
            NoExterior:{required: " * Ingrese el número"},
            Colonia:{required:" * Ingrese la colonia"},
            Estado:{required:" * Seleccione el estado"},
            Delegacion:{required:" * Ingrese la delegación"},
            CP:{required:" * Ingrese el código postal"}
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
                    $('#mensaje_localidad2').html("La localidad ("+data+") se guard\u00f3  correctamente");                     
                    $( "#cancelar" ).trigger( "click" );/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                    if($("#recarga").length){
                        setTimeout(function(){location.reload(); $("#mensajes").html(data);},4000);
                    }                   
                } else {
                    $('#mensaje_localidad2').html(data);                    
                }
            });
        }
    });
});