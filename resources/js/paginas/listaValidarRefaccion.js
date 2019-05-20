var oTable;
var currentPage = 0;
var currentFilter = "";
var muestraSolicitados = false;
var muestraEnviados = false;
var seleccionar = true;
var seleccionar_envios = true;

$(document).ready(function() {
    muestraSolicitados = false;
    muestraEnviados = false;
    $(".boton").button().css('font-size','9px');/*Estilo de botones*/
    $.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings)
    {
        return {
            "iStart": oSettings._iDisplayStart,
            "iEnd": oSettings.fnDisplayEnd(),
            "iLength": oSettings._iDisplayLength,
            "iTotal": oSettings.fnRecordsTotal(),
            "iFilteredTotal": oSettings.fnRecordsDisplay(),
            "iPage": oSettings._iDisplayLength === -1 ? 0 : Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
            "iTotalPages": oSettings._iDisplayLength === -1 ? 0 : Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
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


    oTable = $('.tabla_grid').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "aLengthMenu": [[-1],[ "All"]],
        "iDisplayLength": -1,    
        "bFilter": false,
        "fnDrawCallback": function() {                     
            currentPage = this.fnPagingInfo().iPage;
            currentFilter = $('div.dataTables_filter input').val();            
        }
    });
    
    oTable = $('.tabla_grid_length').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,    
        "bFilter": true,
        "fnDrawCallback": function() {
            currentPage = this.fnPagingInfo().iPage;
            currentFilter = $('div.dataTables_filter input').val();            
        }
    });

    $("#ticket_color").val($("#color_hidden").val());/*Seleccionamos el color que se ha filtrado, en caso que se haya seleccionado*/
    
    if($("#mostrar_grid").length && $("#mostrar_grid").val()=="1"){
        mostrarSolicitados();
        mostrarEnviados();
    }        
    
    $("#busqueda_ticket").keyup(function(event) {
        if (event.keyCode == 13) {
            $("#boton_aceptar").click();
        }
    });
    seleccionar = true;
    seleccionar_envios = true;
    
    if($("#stoner").length){
        $("#tAlmacen").show();
    }
    
    if($("#etoner").length){
        $("#tAlmacen2").show();
    }
});

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

/**
 * Busca el texto recibido en la columna 0(Numero de ticket)
 * @param {type} texto String que se busca en la columna 0
 * @returns nada.
 */
function buscarFolio(texto) {
    oTable.fnFilter(texto, 0);
}

function buscarEnTabla(texto) {
    if (texto != "" || texto != " ") {
        oTable.fnFilter(texto);
    }
}

function seleccionarPagina(pagina) {
    oTable.fnPageChange(pagina);
    /*for(var i=0;i<pagina;i++){
     oTable.fnPageChange('next');
     }*/
}

function mostrarSolicitados(){
    if(!muestraSolicitados){
        $("#tAlmacen").show();
        muestraSolicitados = true;
        $('a#liga_solicitado').text('Ocultar solicitados');        
    }else{
        $("#tAlmacen").hide();
        muestraSolicitados = false;
        $('a#liga_solicitado').text('Mostrar solicitados');        
    }
}

function mostrarEnviados(){    
    if(!muestraEnviados){
        $("#tAlmacen2").show();
        muestraEnviados = true;
        $('a#liga_envio').text('Ocultar envíos');        
    }else{
        $("#tAlmacen2").hide();
        muestraEnviados = false;
        $('a#liga_envio').text('Mostrar envíos');        
    }
}

function editarRegistroRecordandoFiltro(liga, id) {
    loading("Cargando ...");
    limpiarMensaje();
    var filtro = currentFilter;
    if (filtro == "" && $("#filter").val() != "") {
        filtro = $("#filter").val();
    }
    filtro = filtro.replace(/ /g, "_XX__XX_");

    $("#contenidos").load(liga, {"id": id, "page": currentPage, "filter": filtro}, function() {
        $(".button").button();
        finished();
    });
}

function relacionarTecnico(idTicket, tecnico, tipo) {
    if ($("#" + tecnico).val() != "0") {
        loading("Guardando informaci\u00f3n ...");
        $("#mensajes").load("WEB-INF/Controllers/Controler_Ticket_SW.php", {"idTicket": idTicket, "tecnico": $("#" + tecnico).val(), "tipo": tipo}, function(data) {
            //alert(data);
            if (tipo == 1) {
                cambiarContenidos("hardware/mis_tickets.php", "Asigna Técnico HW");
            } else if (tipo == 2) {
                cambiarContenidos("software/mis_tickets.php", "Asigna Técnico SW");
            } else {
                cambiarContenidos("tfs/mis_tickets_asigna.php", "Asigna TFS");
            }
        });
    } else {
        $("#error_tecnico_" + idTicket).show();
    }
}

function recargarTicketConUsuario(liga, idUsuario) {
    if (idUsuario != "" || idUsuario != 0) {
        loading("Cargando ...");
        $("#contenidos").load(liga, {"idUsuario": idUsuario}, function() {
            finished();
        });
    }
}

function recargarListaTicketBusquedaFolio(liga, folio) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {"idTicket": folio}, function() {
        finished();
    });
}

function recargarListaTicket(liga, checkbox, cliente, color, estado, checkmoroso, checkcancelado) {
    loading("Cargando ...");

    var cerrado = false;
    var moroso = false;
    var cancelado = false;
    var filtro = currentFilter;
    filtro = filtro.replace(/ /g, "_XX__XX_");

    //$("#contenidos").empty();

    $("#contenidos").load(liga + "?page=" + currentPage + "&filter=" + filtro, {"cerrado": cerrado, "cliente": $("#" + cliente).val(), "color": $("#" + color).val(),
        "estado": $("#" + estado).val(), "moroso": moroso, "cancelado": cancelado}, function() {
        finished();
    });
}

function BuscarTicket(liga){
    var cerrado = 0;
    var cancelado = 0;
    var moroso = 0;
    var enviados = 0;
    if ($("#verCancelado").length && $("#verCerrado").is(':checked'))
        cerrado = 1;
    if ($("#verCancelado").length && $("#verCancelado").is(':checked'))
        cancelado = 1;
    if ($("#verMoroso").length && $("#verMoroso").is(':checked'))
        moroso = 1;
    if ($("#verEnviados").length && $("#verEnviados").is(':checked'))
        enviados = 1;
    var ticket = $("#busqueda_ticket").val();
    var color = $("#ticket_color").val();
    var cliente = $("#cliente_ticket").val();
    var usuario = $("#usuarioslc").val();
    var areaAtencion = $("#slcAreaAtencion").val();    
    if (ticket !== "") {
        loading("Cargando ...");
        $("#contenidos").load(liga, {"ticket": ticket, 'mostrar':true}, function() {                        
            finished();            
        });
    }
    else {
        loading("Cargando ...");
        $("#contenidos").load(liga, {"usuario": usuario, "color": color, "cliente": cliente, "cerrado": cerrado, 
            "cancelado": cancelado, "moroso": moroso, "areaAtencion": areaAtencion, "enviados":enviados, 'mostrar':true}, function() {                        
            finished();            
        });
    }
}

function seleccionarTodosSolicitados(){    
    var n = parseInt($("#contador_solicitados").val());
    for(var i=1;i<=n;i++){
        $('#ckTonerSeleccionado'+i).prop('checked', seleccionar);
    }
    seleccionar = !seleccionar;
    if(seleccionar){
        $("#mensaje_sel").text("Seleccionar todo");
    }else{
        $("#mensaje_sel").text("Deseleccionar todo");
    }
}

function seleccionarTodosSolicitadosRef(){    
    var n = parseInt($("#cantidad_solicitadas").val());
    for(var i=1;i<=n;i++){
        $('#check_guardar_'+i).prop('checked', seleccionar);
    }
    seleccionar = !seleccionar;
    if(seleccionar){
        $("#mensaje_sel").text("Seleccionar todo");
    }else{
        $("#mensaje_sel").text("Deseleccionar todo");
    }
}

function seleccionarTodosEnviados(){    
    var n = parseInt($("#numeroCheckEnvios").val());
    for(var i=0;i<n;i++){
        $('#check_'+i).prop('checked', seleccionar_envios);
    }
    seleccionar_envios = !seleccionar_envios;
    if(seleccionar_envios){
        $("#mensaje_sel_env").text("Seleccionar todo");
    }else{
        $("#mensaje_sel_env").text("Deseleccionar todo");
    }
}

function seleccionarTodosEnviadosRef(){
    var n = parseInt($("#numeroCheck").val());
    for(var i=1;i<=n;i++){
        $('#listo'+i).prop('checked', seleccionar_envios);
    }
    seleccionar_envios = !seleccionar_envios;
    if(seleccionar_envios){
        $("#mensaje_sel_env").text("Seleccionar todo");
    }else{
        $("#mensaje_sel_env").text("Deseleccionar todo");
    }
}

function BuscarTicketById(idTicket, liga){
    $("#busqueda_ticket").val(idTicket);
    BuscarTicketSolicitud(liga);
}

function BuscarRefaccionesByTicket(idTicket, liga){
    $("#busqueda_ticket").val(idTicket);
    BuscarTicket(liga);
}

function BuscarTicketSolicitud(liga){
    var cerrado = 0;
    var cancelado = 0;
    var moroso = 0;
    var enviados = 0;
    var ultimo_cambio = "";
    if ($("#verCancelado").length && $("#verCerrado").is(':checked'))
        cerrado = 1;
    if ($("#verCancelado").length && $("#verCancelado").is(':checked'))
        cancelado = 1;
    if ($("#verMoroso").length && $("#verMoroso").is(':checked'))
        moroso = 1;
    if ($("#verEnviados").length && $("#verEnviados").is(':checked'))
        enviados = 1;
    if ($("#ultimo_cambio").length && $("#ultimo_cambio").is(':checked'))
        ultimo_cambio = 1;
    var ticket = $("#busqueda_ticket").val();
    var color = $("#ticket_color").val();
    var cliente = $("#cliente_ticket").val();
    var usuario = $("#usuarioslc").val();
    var areaAtencion = $("#slcAreaAtencion").val();
    var localidad = $("#localidad").val();
    if (ticket !== "") {
        loading("Cargando ...");
        $("#contenidos").load(liga, {"ticket": ticket, 'mostrar':true}, function() {
            finished();
        });
    } else {
        $("#errorCliente").html("");
        $("#errorLocalidad").html("");
        loading("Cargando ...");
        $("#contenidos").load(liga, {"usuario": usuario, "color": color, "cliente": cliente, "cerrado": cerrado, 
            "cancelado": cancelado, "moroso": moroso, "areaAtencion": areaAtencion, "localidad": localidad, 
            "enviados":enviados, "ultimo_cambio":ultimo_cambio, 'mostrar':true}, function() {
            finished();
        });        
    }
}
function detalleTicketAlmacen(pagina, idTicket, area, editar) {
    loading("Cargando ...");
    $("#detalleTicket").load(pagina, {"idTicket": idTicket, "area": area, "editar": editar, "detalle": 1}, function() {
        $("#detalleTicket").dialog({
            resizable: false,
            height: 'auto',
            width: '1000px',
            modal: true,
            title: "Detalle ticket",
            buttons: {
                "Cancelar": function() {
                    $(this).dialog("close");
                }
            }
        });
        finished();
    });
}
function CambiarMostrarCliente(idNota) {
    var mostrar = "";
    if ($("#show").is(':checked'))
        mostrar = 1;
    else
        mostrar = 0;

    $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"idNota": idNota, "mostrar": mostrar, "buscar": "cambiarNotaMostrar"}).done(function(data) {
        $("#mensajeEdicion").html(data);
    });
}
function LocalidadesCliente(claveCliente) {
    $("#localidad").load("WEB-INF/Controllers/Ventas/Controller_select_localidad.php", {'id': claveCliente}, function() {
    //$("#localidad").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'cliente': claveCliente, 'toner_solicitado':true}, function() {//Mostrar solo localidades con solicitud de toner del cliente especificado
        /*Refrescamos las opciones*/
        var x = $("#localidad").find('option');
        $("#localidad").multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $("#localidad").multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#localidad").css('width', '250px');
    });
}

function detalleReporte(pagina, idTicket, area, tipo, editar) {
    window.open(pagina + "?idTicket=" + idTicket, '_blank');
    /*$.post("WEB-INF/Controllers/Ajax/altaTicketValida.php", {'imprimir':1, 'ticket':idTicket}, function(data){
        if(data == ""){
            $.post("WEB-INF/Controllers/Ajax/altaTicketValida.php", {'confirmar_imprimir':1, 'ticket':idTicket}, function(data2){
                if(data2 != ""){
                    alert(data2);
                }
            });
            window.open(pagina + "?idTicket=" + idTicket, '_blank');
        }else{            
            $(function() {
                $("<div>"+data+"</div>").dialog({
                    resizable: true, height: 300, width: 450, modal: true, closeOnEscape: false,
                    open: function(event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Cancelar": function() {
                            $(this).dialog("close");
                            $(this).dialog('destroy').remove();                            
                        },
                        "Continuar": function() {
                            $(this).dialog("close");
                            $(this).dialog('destroy').remove();
                            $.post("WEB-INF/Controllers/Ajax/altaTicketValida.php", {'confirmar_imprimir':1, 'ticket':idTicket}, function(data2){
                                if(data2 != ""){
                                    alert(data2);
                                }
                            });
                            window.open(pagina + "?idTicket=" + idTicket, '_blank');
                        }
                    }
                });
            });
        }
    });*/
}

function AgregarNotaTicketLista(menu,action,id,pagina_regresar) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function (data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/            
            //window.open(window.location.href.split('?')[0]+"?mnu="+menu+"&action="+action+"&id="+id+"&param1="+pagina_regresar ,'_blank');
            abrirNuevaVentana("?mnu="+menu+"&action="+action+"&id="+id+"&param1="+pagina_regresar);
            finished();
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function envioMultiple(get){
    if($("#enviar").length){
        $("#enviar").hide();
    }
    $("#mensajes").empty();
    loading("Cargando ...");
    var seleccionados = "";
    var tabla_envios_size = parseInt($("#numeroCheckEnvios").val());
    for(var i=0;i<tabla_envios_size;i++){
        if($('#check_'+i).prop('checked') ){
            var valor = $('#check_'+i).val();
            seleccionados += ($('#valor_'+valor).val()+",");
        }
    }

    if(seleccionados!=""){
        seleccionados = seleccionados.substring(0, seleccionados.length - 1);
        if(get == "etoner"){
            $("#contenidos").load("almacen/altaEnvioToner.php?etoner='1'", {"idNota": seleccionados}, function() {
                finished();
            });
        }else{
            $("#contenidos").load("almacen/altaEnvioToner.php", {"idNota": seleccionados}, function() {
                finished();
            });
        }
    }else{
        alert("Selecciona al menos un pedido para enviar");
        finished();
        if($("#enviar").length){
            $("#enviar").show();
        }
    }
    
}
