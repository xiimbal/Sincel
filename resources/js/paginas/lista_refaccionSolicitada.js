var oTable;
var currentPage = 0;
var currentFilter = "";

$(document).ready(function(){
    
    $.fn.dataTableExt.oApi.fnPagingInfo = function ( oSettings )
    {
        return {
            "iStart":         oSettings._iDisplayStart,
            "iEnd":           oSettings.fnDisplayEnd(),
            "iLength":        oSettings._iDisplayLength,
            "iTotal":         oSettings.fnRecordsTotal(),
            "iFilteredTotal": oSettings.fnRecordsDisplay(),
            "iPage":          oSettings._iDisplayLength === -1 ?
                0 : Math.ceil( oSettings._iDisplayStart / oSettings._iDisplayLength ),
            "iTotalPages":    oSettings._iDisplayLength === -1 ?
                0 : Math.ceil( oSettings.fnRecordsDisplay() / oSettings._iDisplayLength )
            };
    };

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

        
    oTable = $('#tAlmacen').dataTable({                        
        
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength" : 25,
        "fnDrawCallback": function () {
            //alert( 'Now on page'+ this.fnPagingInfo().iPage );            
            currentPage = this.fnPagingInfo().iPage;
            currentFilter = $('div.dataTables_filter input').val();
            //alert(currentPage);
        }                
    });     
        
    $('#busqueda_ticket').searchbox({
        searcher: function(value,name) {
           if(isNumber(value) || value == ""){
                recargarListaTicketBusquedaFolio(name,value);
           }else{
               $("#error_busqueda_ticket").show();
           }
        },
        prompt: 'Folio del ticket'
    });    
        
    $("#ticket_color").val($("#color_hidden").val());/*Seleccionamos el color que se ha filtrado, en caso que se haya seleccionado*/
    
    $(".boton").button();/*Estilo de botones*/      
    
    //seleccionarPagina($("#page").val());
});

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}

/**
 * Busca el texto recibido en la columna 0(Numero de ticket)
 * @param {type} texto String que se busca en la columna 0
 * @returns nada.
 */
function buscarFolio(texto){
    oTable.fnFilter(texto,0);
}

function buscarEnTabla(texto){
    if(texto!="" || texto!=" "){
        oTable.fnFilter(texto);
    }
}

function seleccionarPagina(pagina){
    oTable.fnPageChange(pagina);
    /*for(var i=0;i<pagina;i++){
        oTable.fnPageChange('next');
    }*/
}

function editarRegistroRecordandoFiltro(liga, id) {
    loading("Cargando ...");
    limpiarMensaje();
    var filtro = currentFilter;
    if(filtro=="" && $("#filter").val()!=""){
        filtro = $("#filter").val();
    }
    filtro = filtro.replace(/ /g,"_XX__XX_");
    
    $("#contenidos").load(liga, {"id": id, "page":currentPage, "filter": filtro}, function() {
        $(".button").button();
        finished();
    });
}

function relacionarTecnico(idTicket, tecnico, tipo){    
    if($("#"+tecnico).val() != "0"){
        loading("Guardando informaci\u00f3n ...");       
        $("#mensajes").load("WEB-INF/Controllers/Controler_Ticket_SW.php",{"idTicket":idTicket, "tecnico": $("#"+tecnico).val(), "tipo":tipo}, function(data){
            //alert(data);
            if(tipo == 1){
                cambiarContenidos("hardware/mis_tickets.php","Asigna Técnico HW");
            }else if(tipo==2){
                cambiarContenidos("software/mis_tickets.php","Asigna Técnico SW");
            }else{
                cambiarContenidos("tfs/mis_tickets_asigna.php","Asigna TFS");
            }                
        });
    }else{
        $("#error_tecnico_"+idTicket).show();
    }
}

function recargarTicketConUsuario(liga, idUsuario){
    if(idUsuario!="" || idUsuario!=0){
        loading("Cargando ...");
        $("#contenidos").load(liga,{"idUsuario":idUsuario},function(){
            finished();
        });
    }
}

function recargarListaTicketBusquedaFolio(liga,folio){
    loading("Cargando ...");
    $("#contenidos").load(liga,{"idTicket":folio},function(){
        finished();
    });
}

function recargarListaTicket(liga, checkbox, cliente, color, estado, checkmoroso, checkcancelado){
    loading("Cargando ...");
    
    var cerrado = false;
    if ($("#" + checkbox).is(":checked")) {        
        cerrado = true;
    }
    
    var moroso = false;
    if ($("#" + checkmoroso).is(":checked")) {        
        moroso = true;
    }
    
    var cancelado = false;
    if ($("#" + checkcancelado).is(":checked")) {        
        cancelado = true;
    }
    
    var filtro = currentFilter;
    if( (filtro=="" && $("#filter").val()!="") || filtro == null){
        filtro = $("#filter").val();        
    }
    filtro = filtro.replace(/ /g,"_XX__XX_");
   
    //$("#contenidos").empty();
    
    $("#contenidos").load(liga+"?page="+currentPage+"&filter="+filtro,{"cerrado":cerrado, "cliente":$("#"+cliente).val(), "color":$("#"+color).val(), 
        "estado":$("#"+estado).val(), "moroso":moroso, "cancelado":cancelado},function(){
        finished();
    });
}

