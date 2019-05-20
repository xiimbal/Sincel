$(document).ready(function() {
    var form = "#formAlamcenEquipo";
    var paginaExito = "almacen/alta_almacenEquipo.php";
    var controlador = "WEB-INF/Controllers/Controler_AlmacenEquipo.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $.validator.addMethod("validarnoparte", function(value, element) {
        if ($("#tipo").val() == "codigoBarras") {
            if ($("#equipo").val() == "") {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Seleccione el tipo de servicio");

    /*validate form*/
    $(form).validate({
        rules: {
            serie: {required: true, maxlength: 15, minlength: 4},
            fechaHora: {required: true, maxlength: 10, minlength: 4},
            almacen: {selectcheck: true},
            equipo: {validarnoparte: true, selectcheck: true}
        },
        messages: {
            serie: {required: " * Ingrese el n\u00famero de serie ", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            fechaHora: {required: " * Ingrese la fecha y hora", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                        $('#mensajes').html(data);
                        if (data.toString().indexOf("Error:") === -1) {
                            if ($("#id").val() === "") {
                                $('#contenidos').load(paginaExito, function() {
                                    finished();
                                });
                            }
                            else {
                                $('#contenidos').load("almacen/lista_almacenEquipo.php", function() {
                                    finished();
                                });
                            }
                        } else {
                            LimpiarContenido();
                            finished();
                        }
                    });
        }
    });

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});

function LimpiarContenido() {
    $("#equipo").val("");
    $("#serie").val("");
    $("#equipo").focus();
}

function formaIsertarEquipo() {
    loading("Cargando ...");
    var serie = $('#id').val();
    var fecha = $('#fechaHora').val();
    if ($('#tipo').is(':checked') === true) {
        $('#contenidos').load("almacen/alta_almacenEquipo.php", {"typeInsert": "default", "serie": serie, "fecha": fecha}, function() {
            finished();
        });
    } else if ($('#tipo').is(':checked') === false) {
        $('#contenidos').load("almacen/alta_almacenEquipo.php", {"typeInsert": "manual", "serie": serie, "fecha": fecha}, function() {
            finished();
        });
    }
}

function quitarblancos(id) {
    var val = $("#" + id).val();
    val = val.replace(" ", "");
    $("#" + id).val(val);
}