var seleccionar = true;
$(document).ready(function(){
    $(".boton").button();
    
    $.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings)
    {
        return {
            "iStart": oSettings._iDisplayStart,
            "iEnd": oSettings.fnDisplayEnd(),
            "iLength": oSettings._iDisplayLength,
            "iTotal": oSettings.fnRecordsTotal(),
            "iFilteredTotal": oSettings.fnRecordsDisplay(),
            "iPage": oSettings._iDisplayLength === -1 ?
                    0 : Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
            "iTotalPages": oSettings._iDisplayLength === -1 ?
                    0 : Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
        };
    };    
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
    
    oTable = $('#tabla1').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,
        "aaSorting": [[1, "desc"]],
        "fnDrawCallback": function() {
            currentPage = this.fnPagingInfo().iPage;
        }
    });
});

function BuscarTicketsValidarViaticos(liga, ticket){
    var ticketEnvia = $("#" + ticket).val();
    loading("Buscando");
    $("#contenidos").load(liga, {"ticket" : ticketEnvia, buscar: 1},
    function () {
        $("#agrupar").show();
        finished();
    });
}
function seleccionarTodosSolicitados(){    
    var n = parseInt($("#contador").val());
    for(var i=0;i<n;i++){
        $('#ckViaticos'+i).prop('checked', seleccionar);
    }
    seleccionar = !seleccionar;
    if(seleccionar){
        $("#mensaje_sel").text("Seleccionar todo");
    }else{
        $("#mensaje_sel").text("Deseleccionar todo");
    }
}

function validarSeleccionados(){
    var n = parseInt($("#contador").val());
    var tickets = new Array();
    var contador = 0;
    var controlador = "WEB-INF/Controllers/facturacion/Controler_Validar_Viaticos.php";
    var pagina = "facturacion/validar_viaticos.php";
    loading("Cargando ...");
    
    for (var i = 0; i < n; i++) {
        if ($('#ckViaticos' + i).is(':checked') && $('#ckViaticos' + i).val() != "" && $('#ckViaticos' + i).val() != "0") {
            contador++;
            var str = $('#ckViaticos' + i).val();
            tickets[contador - 1] = str;
        }
    }
    
    if (contador > 0) {
        /*$("#contenidos").load(pagina, {"tickets": tickets}, function() {
            finished();
        });*/
        $.post(controlador,{tickets: tickets, ValidacionGral:1}).done(function(data){
            $("#contenidos").load(pagina);
            $('#mensajes').html(data);
            finished();
        });
    }else{
        finished();
    }
}