var tabla = 1;
var tabCargada = [true, false, false,false];
var nombreTab = ["#tabs-1","#tabs-2","#tabs-3","#tabs-4"];
var paginaACargar = ["","../facturacion/indices.php","cxc/antiguedadSaldos.php","cxc/topTenDeudores.php"];
$(document).ready(function(){
    
    var direccion = "contrato/resumen.php";
    var form = "#formResumenContratos";
    
    $('#tabs, #tabs-1').tabs({
        activate: function(event ,ui){
            //console.log(event);
            if(!tabCargada[ui.newTab.index()]){
                tabCargada[ui.newTab.index()] = true;
                $(nombreTab[ui.newTab.index()]).load(paginaACargar[ui.newTab.index()]);
            }
        }
    });
    
    $("#cliente").multiselect({
        multiple: false,
        selectedList: 1,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
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
    
    oTable = $('#totales').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "bPaginate": false,
        "bFilter": false, 
        "bInfo": false
    });
    
    oTable = $('#totalesDia').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "bPaginate": false,
        "bFilter": false, 
        "bInfo": false
    });
    
    oTable = $('#detalle').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,
        "aLengthMenu": [[10, 25, 50, 100,-1], [10, 25, 50,100, "Todo"]],
        "aaSorting": [[0, "asc"]]
    });
    
    $('.fecha').each(function () {
        $(this).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-01',
            onClose: function () {
                var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(iYear, iMonth, 1));
            }
        });
    });
    
    $(".button").button();

    
    $('#totales').width('100%');
    $('#totalesDia').width('100%');
    
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            loading("Cargando...");
            /*Serialize and post the form*/
            $.post(direccion, {form: $(form).serialize()}).done(function(data) {
                $("#contenidos").empty();
                $('#contenidos').html(data);
                finished();
            });
        }
    });
});

function verDetalle(){
    $("#arreglarAltura").height(640);   //Esto es para que la pantalla no se quede con la altura que ya tenía este div
    if(tabla == 1){
        $("#mostrarDetalle").show();
        $("#textoDetalle").text("Ocultar detalle");
        tabla = 0;
        $("#arreglarAltura").height($( document ).height() - 360);
    }else{
        $("#mostrarDetalle").hide();
        $("#textoDetalle").text("Ver detalle");
        tabla = 1;
        $("#arreglarAltura").height($( document ).height() - 450);
    }
}

