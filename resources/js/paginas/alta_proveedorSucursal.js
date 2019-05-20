$(document).ready(function() {
    var form = "#formProvSucursal";
    var paginaExito = "admin/lista_proveedorSucursal.php";
    var controlador = "WEB-INF/Controllers/Controler_ProveedorSucursal.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            nombre: {required: true, maxlength: 50, minlength: 4},
            proveedor: {selectcheck: true},
            sucursal: {selectcheck: true}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            var id_prov = $("#id_prov").val();
            if (id_prov === "") {
                id_prov = $("#proveedor").val();
            }
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, {"id": id_prov}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});
function cambiarCont(liga, id, titulo) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $('#contenidos').load(liga, {"id_prov": id}, function() {
                $('#titulo').text(titulo);
                $(".tabs").tabs();
                $(".button").button();
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}
function editar_suc(liga, id) {
    loading("Cargando ...");
    limpiarMensaje();
    var prov = $("#txt_proveedor").val();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $("#contenidos").load(liga, {"id": id, "id_prov": prov}, function() {
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}
function eliminar_suc(controlador, lista) {
    var id_prov = $("#txt_proveedor").val();
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        $('#mensajes').load(controlador, function() {
            $('#contenidos').load(lista, {"id": id_prov}, function() {
                $(".button").button();
            });
        });
    }
}