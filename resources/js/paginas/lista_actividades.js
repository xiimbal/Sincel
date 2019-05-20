$(document).ready(function(){
    
    closeNav();
    $(".boton").button();/*Estilo de botones*/
    var paginaInicial = (Number($("#page").val()) * 25);
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
    
    $.fn.dataTableExt.oApi.fnPagingInfo = function (oSettings)
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
    
    oTable = $('#tActividades').dataTable({
        "iDisplayStart": paginaInicial,
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,
        "aaSorting": [[1, "desc"]],
        "fnDrawCallback": function () {
            currentPage = this.fnPagingInfo().iPage;
            currentFilter = $('div.dataTables_filter input').val();
        }
    });
});

function crearActividad(){
    limpiarMensaje();
    loading("Cargando ...");
    var regresar = "";
    if ($("#regresar").length) {
        regresar = $("#regresar").val();
    }
    var pagina = "mesa/alta_actividad.php";//ticket tiene que ser pasado por GET.
    var idTicket = $("#id").val();
    $("#contenidos").load(pagina, {"idTicket": idTicket, "regresar":regresar}, function () {
        $(".button").button();
        finished();
    });
}

function editarActividad(idActividad,idTicket){
    limpiarMensaje();
    loading("Cargando ...");
    var regresar = "";
    if ($("#regresar").length) {
        regresar = $("#regresar").val();
    }
    var pagina = "mesa/alta_actividad.php";//ticket tiene que ser pasado por GET.
    //var idTicket = $("#id").val();
    $("#contenidos").load(pagina, {"idTicket": idTicket, idActividad : idActividad, "regresar":regresar}, function () {
        $(".button").button();
        finished();
    });
}

function eliminarActividad(idActividad){
    var actividad = $("#actividad").val();
    var idTicket = $("#id").val();
    var r = confirm("¿Esta seguro que desea eliminar " + actividad + "?");
    if (r == true) {
        var dir = "WEB-INF/Controllers/Controler_Actividad.php?id=" + idActividad;
        $.post(dir, function(data) {
            limpiarMensaje();
            if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                cambiarContenidos("mesa/lista_actividades.php?id=" + idTicket, "Mesa de ayuda > Actividades");
                $('#mensajes').html(data);
            } else {
                $('#mensajes').html(data);
            }
        });
    }
}