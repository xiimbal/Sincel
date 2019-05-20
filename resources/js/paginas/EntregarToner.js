$(document).ready(function() {
    var form = "#frmEntregaToner";
    var paginaExito = $("#paginaExito").val();
    var controlador = "WEB-INF/Controllers/Controler_EntregarToner.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Seleccione el tipo de almac\u00e9n");

    $.validator.addMethod("totalEntregar", function(value, element) {
        if (parseInt($("#cantidadRestante").val()) < parseInt($("#cantidad").val()) || parseInt($("#cantidad").val())==0) {
            return false;
        }
        else {
            return true;
        }
    }, "* La cantidad debe ser mayor a cero y menor o igual a la cantidad restante");
    
    $.validator.addMethod("Transporte", function(value, element) {
        if ($("#propio").is(':visible')) {
            if ($("#tranportepropio").val() != '0') {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Selecciona el transporte");

    $.validator.addMethod("chofer", function(value, element) {
        if ($("#propio").is(':visible')) {
            if ($("#conductor").val() != '0') {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Selecciona el conductor");


    $.validator.addMethod("TransporteMensajeria", function(value, element) {
        if ($("#mensajeria").is(':visible')) {
            if ($("#tranporteMensajeria").val() != '0') {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Selecciona la mensajer\u00eda");

    $.validator.addMethod("numeroGuia", function(value, element) {
        if ($("#mensajeria").is(':visible')) {
            if ($("#noGuia").val() != '') {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Ingresa el n\u00famero de gu\u00eda");

    /*validate form*/
    $(form).validate({
        rules: {
            tipoMensajeria: {required: true},
            cantidad: {totalEntregar: true},
            conductor: {chofer: true},
            tranportepropio: {Transporte: true},
            noGuia: {numeroGuia: true},
            tranporteMensajeria: {TransporteMensajeria: true}

        },
        messages: {
            tipoMensajeria: {required: " * Selecciona el tipo de envio"},
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            $("#botonGuardar").hide();
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});