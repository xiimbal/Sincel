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
    oTable = $('#tContacto').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25
    }); 
    
});

function eliminarContacto(id, ClaveCliente) {
    if (confirm("¿Esta seguro que desea eliminar el contacto?")) {
        loading("Actualizando y cargando ...");
        $.post("../WEB-INF/Controllers/Validacion/Controler_Contacto.php", {clave: id}, function(data) {
            $('#mensaje_contacto2').html(data);
            if (data.toString().indexOf("Error:") === -1) {
                cambiarContenidosContacto("../cliente/editar_contacto.php?ClaveCliente=" + ClaveCliente, "Editar Contactos");
            }
            finished();
        });
    }
}
