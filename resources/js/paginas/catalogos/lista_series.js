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
    oTable = $('#tserie').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25
    });


});

function eliminarMensajeria(id) {
    if (confirm("¿Esta seguro que eliminar la mensajeria?")) {
        loading("Actualizando y cargando ...");
        $.post("WEB-INF/Controllers/Catalogos/Controller_Serie.php", {clave: id}, function(data) {
            $('#mensajes').html(data);
            if (data.toString().indexOf("Error:") === -1) {
                cambiarContenidos("catalogos/lista_series.php", "SErie");
            }
            finished();
        });
    }
}

function eliminarSeriePago(id){
    if (confirm("Esta seguro que eliminar el pago parcial?")) {
        loading("Actualizando y cargando ...");
        $.post("WEB-INF/Controllers/Catalogos/Controller_Serie_Pago.php", {clave: id}, function(data) {
             $('#mensajes').html(data);
                setTimeout(function(){cambiarContenidos('catalogos/lista_series_pagos.php',"Serie Pago");},3000);
           }
        );
    }
}

function eliminarSerie(id){
    if (confirm("Esta seguro que eliminar el pago parcial?")) {
        loading("Actualizando y cargando ...");
        $.post("WEB-INF/Controllers/Catalogos/Controller_Serie.php", {clave: id}, function(data) {
             $('#mensajes').html(data);
                setTimeout(function(){cambiarContenidos('catalogos/lista_series.php',"Serie Pago");},3000);
           }
        );
    }
}
