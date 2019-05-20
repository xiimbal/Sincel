$(document).ready(function() {
    var form = "#formProvServicio";
    var paginaExito = "admin/lista_proveedorServicio.php";
    var controlador = "WEB-INF/Controllers/Controler_ProveedorServicio.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            sl_proveedor: {selectcheck: true},
            sl_sucursal: {selectcheck: true},
            sl_servicio: {selectcheck: true}
        },
        messages: {
            // nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            event.preventDefault();
            var id_prov = $("#id_prov").val();
            if (id_prov === "") {
                id_prov = $("#sl_proveedor").val();
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
function select_sucursal(valor) {
    $("#sl_sucursal").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {id: valor, slct: "sl_sucursal"}, function(data) {
    });
}