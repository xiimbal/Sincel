$(document).ready(function() {
    var espanol = {
        "sProcessing":     "Procesando...",
        "sLengthMenu":     "Mostrar _MENU_ registros",
        "sZeroRecords":    "No se encontraron resultados",
        "sEmptyTable":     "Ning\u00fan dato disponible en esta tabla",
        "sInfo":           "Mostrando de _START_ a _END_ de  _TOTAL_ registros",
        "sInfoEmpty":      "Mostrando 0 registros",
        "sInfoFiltered":   "(filtrado de _MAX_ registros)",
        "sInfoPostFix":    "",
        "sSearch":         "Buscar:",
        "sUrl":            "",
        "sInfoThousands":  ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst":    "Primero",
            "sLast":     "\u00daltimo",
            "sNext":     "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };   
    
    oTable = $('#tableTicket').dataTable({                        
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "WEB-INF/Controllers/Ajax/Ticket_Mesa.php",
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        
        /*"aoColumnDefs": [ {//Columna de editar
                "aTargets": [4],                        
                "mRender": function( data, type, full) {                
                    // Inplace of <i></i> you can use <img src="/your_media_path/img.png">
                    return '<td><a href="#" class="add" onclick="$(\'#contenidos\').load(\'logistica/alta_PuntosInteres.php\',{id:'+data+'}); return false;"><i class="cus-plus" title="Editar registro"></i>Editar</a></td>';
                }               
            }
        ],*/
        
        "iDisplayLength" : 50
    });
});