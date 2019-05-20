var oTable;
$(document).ready(function() {
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
    oTable = $('#movimientos').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,
        "aaSorting": [[0,"desc"]]
    });
});

function cambiarestatus(num) {
    var val = 0;
    if ($("#check" + num).is(':checked')) {
        val = 1;
    }
    loading("Cargando ...");
    $.post("WEB-INF/Controllers/Ventas/Controller_Retirado_Movimiento.php", {"NumReporte": num, "val": val}, function(data) {
        $('#mensajes').html(data);
        finished();
    });
}


function cambiarestatusFac(num) {
    var val = 0;
    if ($("#check_fac_" + num).is(':checked')) {
        val = 1;
    }
    loading("Cargando ...");
    $.post("WEB-INF/Controllers/Ventas/Controller_Retirado_Movimiento.php", {"NumReporte": num, "val": val, "Facturar":true}, function(data) {
        $('#mensajes').html(data);
        finished();
    });
}