$(document).ready(function() {
    var form = "#formMiniAlmacen";
    var paginaExito = "almacen/lista_miniAlmacen.php";
    var controlador = "WEB-INF/Controllers/Controler_MiniAlmacen.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            nombre: {required: true, maxlength: 50, minlength: 4},
            descripcion: {required: true, maxlength: 200, minlength: 4},
            localidad: {selectcheck: true},
            encargado: {selectcheck: true, }

        },
        messages: {
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            descripcion: {required: " * Ingrese la descripci\u00f3n", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
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
                    var cliente = $("#idcliente").val()
                    $('#contenidos').load(paginaExito, {"id": cliente}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});
function editarRegistroMini(pagina, id, cliente)
{
    loading("Cargando ...");
    limpiarMensaje();
    $("#contenidos").load(pagina, {"idM": id, "id": cliente}, function() {
        $(".button").button();
        finished();
    });
}
function eliminarRegistroMini(controlador, lista, id) {
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        $('#mensajes').load(controlador, function() {
            $('#contenidos').load(lista, {"id": id}, function() {
                $(".button").button();
            });
        });
    }
}
function agregarComponenteMinialmacen(pagina, id1, id2)
{
    loading("Cargando ...");
    limpiarMensaje();
    $("#contenidos").load(pagina, {"minialmacen": id1, "cliente": id2}, function() {
        $(".button").button();
        finished();
    });
}