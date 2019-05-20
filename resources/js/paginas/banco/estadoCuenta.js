var direccion = "Bancos/estadoCuenta.php";
$(document).ready(function() {
    var form = "#formEstado";
    var controlador = "WEB-INF/Controllers/Bancos/Controller_cargaEstado.php";
    $('.boton').button().css('margin-top', '20px');
    $("#id_cuenta").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $("#id_periodo").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $("#mes").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.fecha').each(function() {
            $(this).datepicker({
                dateFormat: 'mm-dd',
                changeMonth: true,
                changeYear: true
            });
        });
    $('.fecha').mask("99-99");
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');  //Es el valor de la primera opción
    }, " * Selecciona un elemento de la lista");
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            file: {required: true},
            id_periodo: {selectcheck: true},
            id_cuenta: {selectcheck: true},
            mes: {selectcheck: true}
        },
        messages: {
            file: {required: "* Selecciona un archivo"}
        }
    });

    $('#upload').click(function(){
        $("#upload").hide();
        if($("#formEstado").valid())
        {
            var formData = new FormData($('form')[0]);
            $.ajax({
                url: 'WEB-INF/Controllers/Bancos/Controller_cargaEstado.php',  //Server script to process data
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







