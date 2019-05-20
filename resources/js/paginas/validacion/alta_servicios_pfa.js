$(document).ready(function() {
    var form = "#table_fa_parti";
    var paginaExito = "validacion/lista_servicios_parti.php";
    var controlador = "../WEB-INF/Controllers/Validacion/Controler_ServicioFA.php";
    
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");       
    
    /*validate form*/
    $(form).validate({
        rules: {            
            c_servicio_parti_fa: {selectcheck: true}
        },
        messages: {
            
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();            
            /*Serialize and post the form*/
            $.post(controlador, {'form': $(form).serialize(), 'servicio':servicio, 'IdAnexo':$("#idClaveAnexoCC").val()}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/                      
                    $('#mensaje_serviciosp2').html("El servicio se guard\u00f3  correctamente");
                    cargarDependencia("servicios_p2",paginaExito+"?tipo=fa&idkanexo="+$("#idClaveAnexoCC").val()+"&ClaveCliente="+$("#claveClienteS").val(),$("#claveAnexo").val(),null,null);
                    //$( "#cancelar" ).trigger( "click" );/*llamamos el evento onclick del boton cancelar para que regrese a la pantalla de lista*/
                } else {
                    $('#mensaje_serviciosp2').html(data);                    
                }
            });
        }
    });        
});