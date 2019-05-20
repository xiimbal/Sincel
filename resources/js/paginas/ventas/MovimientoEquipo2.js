var form = "#formmovimientos";
var controlador = "WEB-INF/Controllers/Ventas/Controller_Cambio_Equipo2.php";
$(document).ready(function() {
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
    $(".fecha").mask("9999-99-99");
    $(".boton").button();
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            fecha_mov: {required: true},
            selectcli2: {cliente: true},
            selectcliloc2: {cliente: true},
            selectanexocli2: {demostracion: true},
            selectlocserv2: {demostracion: true},
            selectalm: {almacen: true, demostracion3: true},
            comentario: {required: true},
            tipomovimiento: {required: true}
        },
        messages: {
            tipomovimiento: {required: " * Selecciona el tipo de movimiento"},
            comentario: {required: " * Ingresa un comentario"},
            fecha_mov: {required: " * Ingrese la fecha de movimiento"}
        }
    });

    jQuery.validator.addMethod("demostracion", function(value, element) {
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
    $("#movloc2").change(function() {
        var habilitar = new Array("selectvendedor2", "selectcli2", "selectcliloc2", "selectanexocli2", "selectlocserv2");
        var deshabilitar = new Array("selectloc", "selectanexocli", "selectlocserv", "selectalm");
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
        var deshabilitar = new Array("selectloc", "selectanexocli", "selectlocserv", "selectvendedor2", "selectcli2", "selectcliloc2", "selectanexocli2", "selectlocserv2");
        if ($('#movloc3').is(':checked')) {
            for (i = 0; i < habilitar.length; i++) {
                $("#" + habilitar[i]).removeAttr("disabled");
            }
            for (i = 0; i < deshabilitar.length; i++) {
                $("#" + deshabilitar[i]).attr("disabled", "disabled");
            }
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                finished();
                tgmov(null, null);
                $("#divinfoup").html(data);
            });
            loading("Enviando...");
            $("#divinfo").empty();
        }
    });

});

function validarextra(valor) {
    for (var i = 1; i < valor; i++) {
        $("#fecha" + i).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true,
            maxDate: '+0D'
        });
        $("#fecha" + i).rules("add", {
            required: true,
            messages: {
                required: " * Seleccione la fecha"
            }
        });
        if ($("#contadorbn" + i).length > 0) {
            $("#contadorbn" + i).rules("add", {
                required: true,
                number: true,
                min:1,
                messages: {
                    required: " * Ingrese el contador blanco y negro",
                    number: " * Ingrese un número",
                    min: "* Ingrese un n\u00famero mayor a 0"
                }
            });
        }

        if ($("#contadorcl" + i).length > 0) {
            $("#contadorcl" + i).rules("add", {
                required: true,
                number: true,
                min:1,
                messages: {
                    required: " * Ingrese el contador color",
                    number: " * Ingrese un número",
                    min: "* Ingrese un n\u00famero mayor a 0"
                }
            });
        }

        if ($("#contadorbnml" + i).length > 0) {
            $("#contadorbnml" + i).rules("add", {
                required: true,
                number: true,
                min:1,
                messages: {
                    required: " * Ingrese el contador blanco y negro",
                    number: " * Ingrese un número",
                    min: "* Ingrese un n\u00famero mayor a 0"
                }
            });
        }

        if ($("#contadorclml" + i).length > 0) {
            $("#contadorclml" + i).rules("add", {
                required: true,
                number: true,
                min:1,
                messages: {
                    required: " * Ingrese el contador color",
                    number: " * Ingrese un número",
                    min: "* Ingrese un n\u00famero mayor a 0"
                }
            });
        }

        if ($("#NivelTN" + i).length > 0) {
            $("#NivelTN" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }

        if ($("#NivelTC" + i).length > 0) {
            $("#NivelTC" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }

        if ($("#NivelTM" + i).length > 0) {
            $("#NivelTM" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }
        if ($("#NivelTA" + i).length > 0) {
            $("#NivelTA" + i).rules("add", {
                number: true,
                range: [0, 100],
                messages: {
                    number: " * Ingrese un número",
                    range: " * Ingrese un n\u00famero mayor a 0 y máximo hasta 100"
                }
            });
        }
    }
}

function cargarServicios(origen, destino) {
    /*var dir = "WEB-INF/Controllers/Ajax/updates.php";
    $("#contenidos_invisibles").load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true'}, function() {*/
        var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
        $("#" + destino).load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'anexo_completo': true});
    //});
}