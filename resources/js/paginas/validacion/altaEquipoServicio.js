$(document).ready(function(){
    var controlador = "WEB-INF/Controllers/Validacion/Controler_EquipoServicio.php";
    if($("#independiente").length){
        controlador = "../"+controlador;
    }
    var form = "#formEquipos";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        errorClass: "my-error-class",
        rules: {            
            
        },
        messages: {
           
        }
    });
    
    var idKServicio = $("#IdKServicio").val();
    var prefijo = $("#prefijo").val();
    
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {            
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {                
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/  
                    if($("#independiente").length){
                        cargarDependencia("equipos_p2","../cliente/validacion/lista_equiposServicio.php",idKServicio,null,prefijo);
                    }else{
                        cargarDependencia("equipos_p2","ventas/validacion/lista_equiposServicio.php",idKServicio,null,prefijo);
                    }
                    $('#mensaje_equipos').html("El equipo se registro correctamente");
                } else {
                    $('#mensaje_equipos').html(data);
                }
            });
        }
    });
});