$(document).ready(function() {
    var form = "#formBitacoraTicket";    
    var controlador = "WEB-INF/Controllers/Controler_AltaBitacora.php";
    var paginaExito = "almacen/alta_bitacora.php";
    var id = $("#idB").val();
    /*Prevent form*/
    
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            loading("Cargando ...");
            var formData = new FormData($('form')[0]);
                $.ajax({
                url: controlador,  //Server script to process data
                type: 'POST',
                xhr: function() {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    return myXhr;
                },
                success: function(data) {
                    if (data.toString().indexOf("Error:") === -1) {
                        $('#contenidos').load(paginaExito, {"id": id}, function() {
                            finished();
                        });
                    } else {
                        $('#mensajes').html(data);
                        finished();
                    }
                },
                data: formData,
                cache: false,
                contentType: false,
                processData: false
            });//post agregar
        }
    });//

    $('.boton').button().css('margin-top', '20px');

});


