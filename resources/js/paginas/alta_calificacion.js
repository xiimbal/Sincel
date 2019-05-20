$(document).ready(function() {
    var form = "#formCalificacion";    
    var controlador = "WEB-INF/Controllers/Controler_Calificacion.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            cliente: {selectcheck: true},
            usuario: {selectcheck: true},
            calificacion: {required: true, number: true, max: 10, min: 0}
        },
        messages: {            
            calificacion: {required: " * Ingresa la calificación", number: " * Ingresa sólo números", max: " * El valor máximo permitido es {0}", min: " * El valor permitido es {0}"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");            
            /* stop form from submitting normally */
            event.preventDefault();            
            /*Serialize and post the form*/            
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {                
                if (data.toString().indexOf("Error:") === -1) {
                    subirImagen(data);
                    /*$('#contenidos').load(paginaExito, function() {
                        finished();
                    });*/
                } else {
                    $('#mensajes').html(data);
                    finished();
                }
            });
        }
    });

    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true
        });    
    });
    $('.boton').button().css('margin-top', '20px');
});

function subirImagen(idFolio){
    var paginaExito = "admin/lista_calificacion.php";
    var intp = $("#foto").val();
    if (intp !== "") {
        var formData = new FormData($(".formulario")[0]);
        formData.append("tipo", "6");
        formData.append("folio", idFolio);
        formData.append("empresa", $("#empresa").val());
        $.ajax({
            url: 'WEB-INF/Controllers/Controler_UploadPruebas.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                loading("Subiendo imagen ...");
            },
            //una vez finalizado correctamente
            success: function(data) {
                /*var $fileupload = $('#foto');
                $fileupload.replaceWith($fileupload.clone(true));*/                
                $('#contenidos').load(paginaExito, function() {
                    $('#mensajes').html("La calificación se dio de alta correctamente");
                    finished();
                });
            },
            error: function(data) {
                $('#mensajes').html("Error al subir la imagen: "+data);
            }
        });
    }else{
        $('#contenidos').load(paginaExito, function() {
            $('#mensajes').html("La calificación se dio de alta correctamente");
            finished();
        });
    }    
}