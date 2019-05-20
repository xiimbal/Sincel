var form = "#formmovimientos";
var controlador = "WEB-INF/Controllers/Ventas/Controller_Cambio_Equipo.php";
$(document).ready(function(){
    $("#fecha_mov").datepicker({
        dateFormat: 'yy-mm-dd',
        changeYear: true,
        changeMonth: true/*,
        maxDate: '+0D'*/
    });
    $("#fecha_mov").change(function() {
        $(".fecha").val($("#fecha_mov").val());
    });
    $("#fecha_mov").mask("9999-99-99");
    $("#fecha").datepicker({
        dateFormat: 'yy-mm-dd',
        changeYear: true,
        changeMonth: true,
        maxDate: '+0D'
    });
    $(".boton").button();
    $(".fecha").mask("9999-99-99");
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            fecha_mov:{required:true},
            selectcli: {localidad: true},
            selectcliloc: {localidad: true},
            selectanexocli: {localidad: true},
            selectloc: {localidad: true},
            selectcliserv: {localidad: true},
            selectanexoloc: {demostracion: true},
            selectlocserv: {demostracion: true},
            selectcli2: {cliente: true},
            selectcliloc2: {cliente: true},
            selectanexocli2: {demostracion2: true},
            selectcliserv2: {demostracion2: true},
            selectalm: {almacen: true,demostracion3:true},
            comentario: {required: true},
            fecha: {required: true},
            tipomovimiento: {required: true},
            contadorbn: {required: true, number: true, min:1},
            contadorcl: {required: true, number: true, min: 1},
            contadorbnml: {required: true, number: true, min: 1},
            contadorclml: {required: true, number: true, min: 1},
            NivelTN: {number: true, range: [0, 100]},
            NivelTC: {number: true, range: [0, 100]},
            NivelTM: {number: true, range: [0, 100]},
            NivelTA: {number: true, range: [0, 100]}
        },
        messages: {
            tipomovimiento: {required: " * Selecciona el tipo de movimiento"},
            comentario: {required: " * Ingresa un comentario"},
            fecha: {required: " * Selecciona la fecha"},
            contadorbn: {required: " * Ingrese el n\u00famero el contador B/N", number: " * Ingresa s\u00f3lo n\u00fameros", min: "* Ingrese un n\u00famero mayor a 0"},
            contadorcl: {required: " * Ingrese el n\u00famero el contador de color", number: " * Ingresa s\u00f3lo n\u00fameros", min: "* Ingrese un n\u00famero mayor a 0"},
            contadorbnml: {required: " * Ingrese el n\u00famero el contador B/N", number: " * Ingresa s\u00f3lo n\u00fameros", min: "* Ingrese un n\u00famero mayor a 0"},
            contadorclml: {required: " * Ingrese el n\u00famero el contador de color", number: " * Ingresa s\u00f3lo n\u00fameros", min: "* Ingrese un n\u00famero mayor a 0"},
            NivelTN: {required: " * Ingrese el n\u00famero el nivel de toner negro", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            NivelTC: {required: " * Ingrese el n\u00famero el nivel de toner de cyan", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            NivelTM: {required: " * Ingrese el n\u00famero el nivel de toner magenta", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            NivelTA: {required: " * Ingrese el n\u00famero el nivel de toner amarillo", number: " * Ingresa s\u00f3lo n\u00fameros", range: "* Ingrese un n\u00famero mayor a 0"},
            fecha_mov:{required: " * Ingrese la fecha de movimiento"}
        }
    });
    jQuery.validator.addMethod("demostracion", function(value, element) {
        if ($("#movloc").is(':checked')) {
            if ($("#tipomovimiento").val() == 2) {
                return true;
            } else {
                if (value !== "") {
                    return true;
                }
                return false;
            }
        }
        return false;
    }, " * Selecciona");
    jQuery.validator.addMethod("demostracion2", function(value, element) {
        if ($("#movloc2").is(':checked')) {
            if ($("#tipomovimiento").val() == 2) {
                return true;
            } else {
                if (value !== "") {
                    return true;
                }
                return false;
            }
        }
        return false;
    }, " * Selecciona");
    jQuery.validator.addMethod("demostracion3", function(value, element) {
        if ($("#movloc3").is(':checked')) {
            if ($("#tipomovimiento").val() == 2) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }, " * No se puede hacer una demo a almacén");
    jQuery.validator.addMethod("localidad", function(value, element) {
        if ($("#movloc").is(':checked')) {
            if (value !== "") {
                return true;
            }
            return false;
        }
        return true;
    }, " * Selecciona");
    jQuery.validator.addMethod("cliente", function(value, element) {
        if ($("#movloc2").is(':checked')) {
            if (value !== "") {
                return true;
            }
            return false;
        }
        return true;
    }, " * Selecciona");
    jQuery.validator.addMethod("almacen", function(value, element) {
        if ($("#movloc3").is(':checked')) {
            if (value !== "") {
                return true;
            }
            return false;
        }
        return true;
    }, " * Selecciona");
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                $("#botonvarios").show();
                finished();
                tgmov(null, null);
                $("#divinfoup").html(data);
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });
    $("#movloc").change(function() {
        var habilitar = new Array("selectloc", "selectanexoloc", "selectlocserv");
        var deshabilitar = new Array("selectvendedor2", "selectcli2", "selectcliloc2", "selectanexocli2", "selectcliserv2", "selectalm");
        if ($('#movloc').is(':checked')) {
            for (i = 0; i < habilitar.length; i++) {
                $("#" + habilitar[i]).removeAttr("disabled");
            }
            for (i = 0; i < deshabilitar.length; i++) {
                $("#" + deshabilitar[i]).attr("disabled", "disabled");
            }
        }
    });

    $("#movloc2").change(function() {
        var habilitar = new Array("selectvendedor2", "selectcli2", "selectcliloc2", "selectanexocli2", "selectcliserv2");
        var deshabilitar = new Array("selectloc", "selectanexoloc", "selectlocserv", "selectalm");
        if ($('#movloc2').is(':checked')) {
            for (i = 0; i < habilitar.length; i++) {
                $("#" + habilitar[i]).removeAttr("disabled");
            }
            for (i = 0; i < deshabilitar.length; i++) {
                $("#" + deshabilitar[i]).attr("disabled", "disabled");
            }
        }
    });

    $("#movloc3").change(function() {
        var habilitar = new Array("selectalm");
        var deshabilitar = new Array("selectloc", "selectanexoloc", "selectlocserv", "selectvendedor2", "selectcli2", "selectcliloc2", "selectanexocli2", "selectcliserv2");
        if ($('#movloc3').is(':checked')) {
            for (i = 0; i < habilitar.length; i++) {
                $("#" + habilitar[i]).removeAttr("disabled");
            }
            for (i = 0; i < deshabilitar.length; i++) {
                $("#" + deshabilitar[i]).attr("disabled", "disabled");
            }
        }
    });

});

var visible = false;

function mostrarDetalle() {
    if (visible) {
        $('.info-extra').hide();
        $('a#muestra_historico').text('Mostrar hist\u00f3rico de movimientos');
        visible = false;
    } else {
        $('.info-extra').show();
        $('a#muestra_historico').text('Ocultar hist\u00f3rico de movimientos');
        visible = true;
    }
}

function cargarServicios(origen, destino) {
    /*var dir = "WEB-INF/Controllers/Ajax/updates.php";
    $("#contenidos_invisibles").load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true'}, function() {*/
        var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
        $("#" + destino).load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'anexo_completo':true});
    //});
}

function cancelarmovimiento() {
    $("#botonvarios").show();
    $("#divinfo").empty();
}