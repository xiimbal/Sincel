$(document).ready(function() {
    var form = "#formAlamcenComponente";
    var paginaExito = "almacen/lista_k_almacenComponente.php";
    var controlador = "WEB-INF/Controllers/Controler_AlmacenComponente.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $.validator.addMethod("cantidades", function(value, element) {
        if (parseInt($("#minima").val()) > parseInt($("#maxima").val())) {
            return false;
        }
        else {
            return true;
        }
    }, "* La cantidad mínima debe ser menor a la cantidad máxima");
    $.validator.addMethod("valComentario", function(value) {
        if ($("#id").val() != "" && $("#id2").val() != "") {
            if ($("#comentario").val() != "")
                return true;
            else
                return false;
        }
        else {
            return true;
        }
    }, "* Ingrese el comentario");

    /*validate form*/
    $(form).validate({
        rules: {
            cantidad: {required: true, number: true},
            apartados: {required: true, number: true},
            noParte: {selectcheck: true},
            almacen: {selectcheck: true},
            tipoComponente: {selectcheck: true},
            minima: {cantidades: true},
            comentario: {valComentario: true}            
        },
        messages: {
            //serie: {required: " * Ingrese el n\u00famero de serie ", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            cantidad: {required: " * Ingrese el n\u00famero de existencias ", number: " * Ingresa s\u00f3lo n\u00fameros"},
            apartados: {required: " * Ingrese el n\u00famero de apartados ", number: " * Ingresa s\u00f3lo n\u00fameros"}            
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
                            $('#contenidos').load(paginaExito, function() {
                                finished();
                            });
                        } else {
                            finished();
                        }
                    });
        }
    });

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});

function mostrarRefacciones()
{
    var tipoRefaccion = $("#tipoComponente").val();
    var almacen = $("#almacen").val();
    var existencia = $("#cantidad").val();
    var apartados = $("#apartados").val();
    var pagina = "almacen/alta_k_almacenComponente.php";
    loading("Cargando ...");
    $("#contenidos").load(pagina, {"tipoRefaccion": tipoRefaccion, "almacen": almacen, "existencia": existencia, "apartados": apartados}, function() {
        $(".button").button();
        finished();
    });
}

function filtarTipoCompoenten(pagina)
{
    var tipoComponente = $("#tipoComponenteFiltro").val();
    loading("Cargando ...");
    $("#contenidos").load(pagina, {"tipoComponente": tipoComponente}, function() {
        $(".button").button();
        finished();
    });
}
function mostrarMaximoMinimo(value) {
    alert(value);
}

function submitform() {
    $("#FormularioExportacion").submit();
}
