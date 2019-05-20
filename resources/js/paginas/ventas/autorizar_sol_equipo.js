 var contador=2;
$(document).ready(function() {
    var form = "#solform";
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Autorizar_Solicitud.php";
    
    /*
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            fecha: {required: true},
            hora: {required: true},
            contadorbn: {required: true,number: true, range: [0, 1000000]},
            contadorcl: {required: true,number: true, range: [0, 1000000]},
            contadorbnml: {required: true,number: true, range: [0, 1000000]},
            contadorclml: {required: true,number: true, range: [0, 1000000]},
            NivelTN: {required: true,number: true, range: [0, 100]},
            NivelTC: {required: true,number: true, range: [0, 100]},
            NivelTM: {required: true,number: true, range: [0, 100]},
            NivelTA: {required: true,number: true, range: [0, 100]}
        },
        messages: {
            fecha: {required: " * Ingrese la fecha"},
            hora: {required: " * Ingrese la hora"},
            contadorbn: {required: " * Ingrese el n\u00famero el contador B/N",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"},
            contadorcl: {required: " * Ingrese el n\u00famero el contador de color",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"},
            contadorbnml: {required: " * Ingrese el n\u00famero el contador B/N",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"},
            contadorclml: {required: " * Ingrese el n\u00famero el contador de color",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"},
            NivelTN: {required: " * Ingrese el n\u00famero el nivel de toner negro",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"},
            NivelTC: {required: " * Ingrese el n\u00famero el nivel de toner de cyan",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"},
            NivelTM: {required: " * Ingrese el n\u00famero el nivel de toner magenta",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"},
            NivelTA: {required: " * Ingrese el n\u00famero el nivel de toner amarillo",number: " * Ingresa s\u00f3lo n\u00fameros",range:"* Ingrese un n\u00famero mayor a 0"}
        }
    });*/

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize(),num:contador}).done(function(data) {
                var rechazar = false;
                if($('#autorizar2').is(':checked') || $("#autorizar3").is(':checked')) {
                        rechazar = true;
                }
                var id = $("#solicitud").val();
                $("#mensajes").html(data);
                $('#contenidos').load("ventas/Autorizaciones_Solicitud.php", function() {
                    if(rechazar){
                        $('#mensajes').load("ventas/CancelacionSeriesTotal.php", {id: id}, function() {
                            finished();                            
                        });
                    }else{
                        finished();
                    }
                });
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
});


function establecercontador(count){
contador=count;
}

function cambiarselectmodelo(origen,destino){
    dir="WEB-INF/Controllers/Ventas/Controller_select_compoequip.php";
    $("#"+destino).load(dir,{id:$("#"+origen).val()});
}

function cambiarccosto(origen,destino){
    dir="WEB-INF/Controllers/Ventas/Controller_select_localidades.php";
    $("#"+destino).load(dir,{id:$("#"+origen).val()});
}