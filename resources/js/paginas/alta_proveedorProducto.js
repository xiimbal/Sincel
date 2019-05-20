$(document).ready(function() {
    var form = "#formProvProducto";
    var paginaExito = "admin/lista_proveedorProducto.php";
    var controlador = "WEB-INF/Controllers/Controler_ProveedorProducto.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            producto: {selectcheck: true},
            sucursal: {selectcheck: true}
        },
        messages: {
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
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
    /*Si hay proveedor, cargamos las sucursales automaticamente*/
    if ($("#ClaveProveedorH").length && $("#ClaveProveedorH").val() != "") {
        cargarSucursales('proveedor', 'sucursal', true, 'ClaveSucursalH');
    }
});

function cargarSucursales(origen, destino, preseleccionar, claveSucursal) {
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'proveedor': $("#" + origen).val(), 'sucursales': true}, function(data) {
        if (preseleccionar && $("#" + claveSucursal).val() != "") {//Si tenemos que preseleccionar una sucursal
            $("#" + destino).val($("#" + claveSucursal).val());
        }
    });
}

function select_sucursal_pro(valor) {
    $("#sucursal").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {id: valor, slct: "sl_sucursal"}, function(data) {
    });
}