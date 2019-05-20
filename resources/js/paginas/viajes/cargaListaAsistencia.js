var direccion = "Bancos/estadoCuenta.php";
$(document).ready(function() {
    var form = "#formCargaLista";
    var controlador = "WEB-INF/Controllers/Viajes/Controller_mostrarLista.php";
    $('.boton').button().css('margin-top', '20px');
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');  //Es el valor de la primera opción
    }, " * Selecciona un elemento de la lista");
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            file: {required: true},
        },
        messages: {
            file: {required: "* Selecciona un archivo"}
        }
    });

    $('#upload').click(function(){
        if($("#formCargaLista").valid())
        {
            loading("Cargando...");
            var formData = new FormData($('form')[0]);
            $.ajax({
                url: 'WEB-INF/Controllers/Viajes/Controller_mostrarLista.php',  //Server script to process data
                type: 'POST',
                xhr: function() {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    /*if(myXhr.upload){ // Check if upload property exists
                        myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                    }*/
                    return myXhr;
                },
                //Ajax events
                //beforeSend: beforeSendHandler,
                success: function(result){
                    finished();
                    $("#div1").html(result);
                },
                //error: errorHandler,
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        }   
    });
});







