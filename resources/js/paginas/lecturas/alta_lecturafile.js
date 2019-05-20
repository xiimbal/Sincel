$(document).ready(function() {
    var form = "#formCargaLista";
    $('.boton').button().css('margin-top', '20px');
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');  //Es el valor de la primera opción
    }, " * Selecciona un elemento de la lista");
    
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            
            file: {required: true}
        },
        messages: {
            file: {required: " * Selecciona un archivo"}
        }
    });
    
    $(".select").multiselect({
        multiple: false,
        selectedList: 1,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

    $('#upload').click(function(){        
        
        modificarValidacion();
        
        if($("#formCargaLista").valid())
        {
            $("#upload").hide();
            $("#mensajeLista").empty();
            loading("Leyendo el archivo ...");
            var formData = new FormData($('form')[0]);
            $.ajax({
                url: '../WEB-INF/Controllers/Controler_LecturaFile.php',  //Server script to process data
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
                    $("#upload").show();
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

function modificarValidacion(){
    var tipo = $("#tipo_archivo").val();
    if(tipo==1){
        $("#cliente").rules("add", {
        required: true,
        messages: {
            required: "* Seleccione un cliente"
        }
    });
    }else{
        $("#cliente").rules("remove");
    }
}





