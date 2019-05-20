$(document).ready(function () {
    
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

    if ($('#tComponentes').length) {
        oTable = $('#tComponentes').dataTable({
            "bJQueryUI": true,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": espanol,
            "sPaginationType": "full_numbers",
            "bDeferRender": true,
            "iDisplayLength": 100
        });
    }
    
    $(".boton").button();
    
    $(".fecha_periodo").mask("99-9999");
    $(".fecha_periodo").each(function () {
        $(this).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'mm-yy',
            maxDate: "+0D",
            onClose: function () {
                var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(iYear, iMonth, 1));
            }
        });
    });
    
    $("#tipoComponente").multiselect({
        noneSelectedText: "Selecciona tipo de componente",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();
    
    $("#EntradaSalida").multiselect({
        noneSelectedText: "Selecciona una opción",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();
    
     $("#almacen").multiselect({
        multiple: true,
        noneSelectedText: "Todos los almacenes",
        selectedList: 3, selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $("#mostrar").click(function(){
        verTabla();
    });
    
});

function verTabla(){
    $("#contenidos").load("almacen/reporte_entradas_salidas.php",{componente:$("#tipoComponente").val(),almacen:$("#almacen").val(),enSa:$("#EntradaSalida").val(),mes:$("#mes").val(),mostrar:true});
}

function verDetalles(noParte,enSa,almacenId){
    $("#contenidos").load("almacen/detalle_movimientos.php",{noParte:noParte,enSa:enSa,almacenId:almacenId,fecha:$("#f").val(),componente:$("#tipoComponente").val(),almacen:$("#almacen").val(),mes:$("#mes").val(),mostrar:true,enSaFiltro:$("#EntradaSalida").val()});
}
//,almacen:$("#almacen").val()