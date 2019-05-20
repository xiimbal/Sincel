$(document).ready(function() {
    var form = "#formProvZona";
    var paginaExito = "admin/lista_proveedorZona.php";
    var controlador = "WEB-INF/Controllers/Controler_ProveedorZona.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            sl_proveedor: {selectcheck: true},
            sl_sucursal: {selectcheck: true},
            gzona: {selectcheck: true},
            zona: {selectcheck: true},
            tiempo: {required: true, number: true}

        },
        messages: {
            tiempo: {required: " * Ingrese el tiempo maximo de solucion", number: " * Ingresa solo numeros"}
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
function verZonasOP(valor) {
    $("#zona").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {id: valor, slct: "sl_zona"}, function(data) {
    });
}
function select_sucursal_zona(valor) {
    $("#sl_sucursal").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {id: valor, slct: "sl_sucursal"}, function(data) {
    });
}