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
    oTable = $('#tcc').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 25,
        "width":100
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

function eliminarCC(id){
    if (confirm("¿Esta seguro que eliminar el centro de costo?")) {
        loading("Actualizando y cargando ...");
        $.post("../WEB-INF/Controllers/Ventas/Controller_CC.php", {clave: id}, function(data) {
            cambiarContenidos('tabla_cc.php?id=' + $("#clavecliente").val(),"Centro Costo");
        });
    }
}

