$(document).ready(function() {
    var form = "#formEstado";
    var paginaExito = "admin/lista_sucursal.php";
    var controlador = "WEB-INF/Controllers/Controler_Sucursal.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            descripcion: {required: true, maxlength: 50, minlength: 2}
            // area: {selectcheck: true}

        },
        messages: {
            descripcion: {required: " * Ingrese la descripci\u00f3n", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
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
                    var id = $("#proveedor").val();
                    $('#contenidos').load(paginaExito, {"id": id}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});
function eliminarSucursal(controler, id, paginaLista)
{
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        $('#mensajes').load(controler, function() {
            $('#contenidos').load(paginaLista, {"id": id});
        });
    }
}