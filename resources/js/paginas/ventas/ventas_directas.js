

function cargarclientes(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clientes.php", {cliente: $("#" + origen).val()}, function() {
        /*Refrescamos las opciones*/
        var x = $("#" + componente).find('option');
        $("#" + componente).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Volvemos a aplicar filtros*/
        $("#" + componente).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#" + (componente)).css('width', '250px');//Width del select
    });
}

function cargarTablaVD() {
    var cadena = "";
    if ($('#checksc').prop('checked')) {
        cadena += "?surtida=1";
    }
    loading("Cargando ...");
    $("#divinfo").load("WEB-INF/Controllers/Ventas/Controller_tabla_vd.php" + cadena, function() {
        finished();
        var espanol = {
            "sProcessing": "Procesando...",
            "sLengthMenu": "Mostrar _MENU_ registros",
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ning\u00fan dato disponible en esta tabla",
            "sInfo": "Mostrando de _START_ a _END_ de  _TOTAL_ registros",
            "sInfoEmpty": "Mostrando 0 registros",
            "sInfoFiltered": "(filtrado de _MAX_ registros)",
            "sInfoPostFix": "",
            "sSearch": "Buscar:",
            "sUrl": "",
            "sInfoThousands": ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst": "Primero",
                "sLast": "\u00daltimo",
                "sNext": "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        };
        oTable = $('#tablavd').dataTable({
            "bJQueryUI": true,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": espanol,
            "sPaginationType": "full_numbers",
            "bDeferRender": true,
            "iDisplayLength": 100
        });
    });

}

function eliminarvd(dir) {
    if (confirm('¿Desea eliminar la venta?')) {
        $.post(dir);
        cargarTablaVD();
    }
}

function facturarvd(idVenta) {
    var r = confirm('¿Deseas generar la prefactura?');
    if (r == true) {
        loading("Cargando ...");
        $('#mensajes').load("ventas/PrefacturaVD.php", {vd: idVenta}, function() {
            finished();
            cargarTablaVD();
        });
    }
}

function cambiarContenidosvd(liga, titulo) {
    var surtida = 0;
    if ($('#checksc').prop('checked')) {
        var surtida = 1;
    }
    if ($('#vendedor').length && $('#vendedor').val().length) {
        liga += "?vendedor=" + $('#vendedor').val() + "&cliente=" + $('#cliente').val() + "&surtida=" + surtida;
    } else {
        liga += "?cliente=" + $('#cliente').val() + "&surtida=" + surtida;
    }
    loading("Cargando ...");
    $("#contenidos").empty();
    limpiarMensaje();
    $('#contenidos').load(liga, function() {
        $('#titulo').text(titulo);
        $(".tabs").tabs();
        $(".button").button();
        finished();
    });
}

function FacturarVenta(idVenta, tipo) {
    var pagina = "WEB-INF/Controllers/Ventas/Controller_FacturarVentaDirecta.php";
    loading("Cargando ...");
    $('#mensajes').load(pagina, {"id": idVenta, "tipo": tipo}, function() {
        finished();
        cargarTablaVD();
    });

}

function surtidasycanceladas() {
    cargarTablaVD();
}