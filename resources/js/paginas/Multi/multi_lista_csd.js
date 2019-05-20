var oTable;
$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
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
    oTable = $('#tcsd').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25
                /*"sDom": '<"H"lTfr>t<"F"ip>',
                 "oTableTools": {
                 "sSwfPath": "resources/media/swf/copy_cvs_xls_pdf.swf",
                 "aButtons": [
                 {'sExtends': 'copy', 'sMessage': 'Copiar', 'sButtonText': 'Copiar', 'sButtonClass': "boton_tabla"},
                 {
                 "sExtends": "pdf",
                 "sFileName": "SICOP.pdf",
                 "bSelectedOnly": true
                 }
                 ]
                 }*/
    });


});

function EliminarCSD(id) {
    var r = confirm("¿Esta seguro que desea eliminar el CSD?");
    if (r == true) {
        var dir = "WEB-INF/Controllers/Multi/Controller_Alta_csd.php?id=" + id;
        $.post(dir, function(data) {
            if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                cambiarContenidos("Multi/lista_cfdi_archivos.php", "CFDI");
                $('#mensajes').html(data);
            } else {
                $('#mensajes').html(data);
            }
        });
    }
}
