$(document).ready(function(){
    $(".boton").button();
    var form = "#frmValidarViaticos";
    var controlador = "WEB-INF/Controllers/facturacion/Controler_Validar_Viaticos.php";
    var paginaExito = "facturacion/validar_viaticos.php";
    $(form).validate({
        rules: {
            
        },
        messages: {
        }
    });
    
    $(form).submit(function (event) {
        event.preventDefault();
        if ($(form).valid()) {  
            loading("Validando");
            /*Serialize and post the form*/
            var inputs = $("input[type=file]"),
            files = [];
            for (var i = 0; i < inputs.length; i++) {
                files.push(inputs.eq(i).prop("files")[0]);
            }
            
            var formData = new FormData();
            $.each(files, function(key, value)
            {
                formData.append(key, value);
            });
            formData.append('form', $(form).serialize());
            
            $.ajax({
                url: controlador,
                type: 'POST',
                xhr: function () {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    /*if(myXhr.upload){ // Check if upload property exists
                     myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                     }*/
                    return myXhr;
                },
                success: function (data) {
                    $('#mensajes').html(data);
                    if (data.toString().indexOf("Error:") === -1) {
                        $('#contenidos').load(paginaExito, function() {
                            finished();
                        });
                    } else {
                        finished();
                    }
                },
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
        }
    });
    agregarValidaciones();
});

function agregarValidaciones(){
    var numero = parseInt($("#contador").val());
    for(var x = 0; x < numero; x++){
        $("#cantidad" + x).rules("add", {required: true, min: 1, number: true, messages: {required: "* Ingresa la cantidad", min: "No puede ingresar cantidades menores a {0}", number: "Sólo números decimales"}});
    }
}