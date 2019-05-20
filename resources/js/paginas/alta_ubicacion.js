$(document).ready(function () {
    var form = "#formUbicacion";
    var paginaExito = "catalogos/lista_ubicacion.php";
    var controlador = "WEB-INF/Controllers/Controler_Ubicacion.php";
    $('.boton').button().css('margin-top', '20px');
    $('#fecha').datepicker({dateFormat: 'yy-mm-dd'});
    $('#hora').mask("99:99:99");

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        rules: {
            txtDescripcion: {required: true, maxlength: 200, minlength: 2}
        },
        messages: {
            txtDescripcion: {required: " * Ingrese Descripción", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    /*Prevent form*/

    $(form).submit(function (event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function (data) {
//                        var idCampania = $("#slcCampania").val();
                        $('#mensajes').html(data);
                        if (data.toString().indexOf("Error:") === -1) {
                            $('#contenidos').load(paginaExito, {
//                                "CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 
                                'mostrar': true}, function () {
                                $(".button").button();
                                finished();
                            });
                        } else {
                            finished();
                        }
                    });
//                }
//            }
        }
    });

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        noneSelectedText: "Selecciona localidad",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();
    finished();
    return;
});

function cancelar() {
    loading("Cargando ...");
    var paginaExito = "catalogos/lista_ubicacion.php";
//    var idCampania = $("#slcCampania").val();
    $('#contenidos').load(paginaExito, {
//        "CampaniaFiltro": idCampania,
        'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function eliminarUbicacion(controlador, idCampania, idTurno, lista) {
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        loading("Eliminando ... ");
        $('#mensajes').load(controlador, function () {
            $('#contenidos').load(lista, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function () {
                $(".button").button();
                finished();
            });
        });
    }
}
