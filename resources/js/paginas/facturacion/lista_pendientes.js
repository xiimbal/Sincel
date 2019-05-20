var oTable;
var mostrando_ticket = false;
var mostrando_ventas = false;
var mostrando_prefacturas = false;
var seleccionar = true;
$(document).ready(function() {
    mostrando_ticket = false;
    mostrando_ventas = false;
    mostrando_prefacturas = false;
    
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
    $(".boton").button();/*Estilo de botones*/
    $(".boton2").button().css('font-size','9px');/*Estilo de botones*/
    
    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: "+0D",
            changeMonth: true,
            changeYear: true
        });
    });
    
    oTable = $('#tabla2').dataTable({
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
    oTable = $('#tabla3').dataTable({
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
    oTable = $('#tabla4').dataTable({
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
    $("#cliente_ticket").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#estado_ticket").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#ticket_color").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();

});

function filtrar(dir, cliente, estado, color, lecorte) {
    var liga = dir;
    var select1 = document.getElementById(cliente);
    var selected1 = "";
    for (var i = 0; i < select1.length; i++) {
        if (select1.options[i].selected)
            selected1 += select1.options[i].value + ",";
    }
    selected1 = selected1.substring(0, selected1.length - 1);
    liga += "?cliente=" + selected1;
    var select1 = document.getElementById(color);
    var selected1 = "";
    for (var i = 0; i < select1.length; i++) {
        if (select1.options[i].selected)
            selected1 += select1.options[i].value + ",";
    }
    if (selected1!=="") {
        selected1 = selected1.substring(0, selected1.length - 1);
        liga += "&color=" + selected1;
    }
    var select1 = document.getElementById(estado);
    var selected1 = "";
    for (var i = 0; i < select1.length; i++) {
        if (select1.options[i].selected)
            selected1 += select1.options[i].value + ",";
    }
    if (selected1.length > 0) {
        selected1 = selected1.substring(0, selected1.length - 1);
        liga += "&estado=" + selected1;
    }
    if ($("#lecorte").is(':checked')) {
        liga += "&lec=1";
    }
    cambiarContenidos(liga, 'PENDIENTES FACTURAR');
}

function marcarVentaFacturada(IdVentaDirecta){
    if(confirm("Está seguro de marcar como facturada la venta directa "+IdVentaDirecta)){
        loading("Actualizando estatus de la venta "+IdVentaDirecta);
        var dir = "WEB-INF/Controllers/Ajax/updates.php";
        $("#mensajes").load(dir,{'NoVenta':IdVentaDirecta, 'facturar':true}, function(){
            finished();
            cambiarContenidos("facturacion/pendientes.php");
        });
    }
}

function mostrarTablaTicket(){    
    if(!mostrando_ticket){
        $("#tabla2").show();
        mostrando_ticket = true;
        $('a#liga_ticket').text('Ocultar tickets');        
    }else{
        $("#tabla2").hide();
        mostrando_ticket = false;
        $('a#liga_ticket').text('Mostrar tickets');        
    }    
}

function mostrarTablaVentas(){
    if(!mostrando_ventas){
        $("#tabla3").show();
        mostrando_ventas = true;
        $('a#liga_ventas').text('Ocultar ventas');        
    }else{
        $("#tabla3").hide();
        mostrando_ventas = false;
        $('a#liga_ventas').text('Mostrar ventas');        
    }
}

function mostrarTablaPrefacturas(){
    if(!mostrando_prefacturas){
        $("#tabla4").show();
        mostrando_prefacturas = true;
        $('a#liga_pref').text('Ocultar prefacturas');        
    }else{
        $("#tabla4").hide();
        mostrando_prefacturas = false;
        $('a#liga_pref').text('Mostrar prefacturas');        
    }
}

function seleccionarTodosSolicitados(){    
    var n = parseInt($("#contador_tickets").val());
    for(var i=1;i<=n;i++){
        $('#ckTicketSeleccionado'+i).prop('checked', seleccionar);
    }
    seleccionar = !seleccionar;
    if(seleccionar){
        $("#mensaje_sel").text("Seleccionar todo");
    }else{
        $("#mensaje_sel").text("Deseleccionar todo");
    }
}

function generarPrefactura(){
    var n = parseInt($("#contador_tickets").val());
    var tickets = new Array();
    var pagos = new Array();
    var descuentos = new Array();
    var contador = 0;
    var pagina = "facturacion/facturarTickets.php";
    loading("Cargando ...");
    
    for (var i = 1; i < n; i++) {
        if ($('#ckTicketSeleccionado' + i).is(':checked') && $('#ckTicketSeleccionado' + i).val() != "" && $('#ckTicketSeleccionado' + i).val() != "0") {
            contador++;
            var str = $('#ckTicketSeleccionado' + i).val();
            var pago = $('#pagoTicket' + i).val();
            var desc = $('#PorcentajeDesc'+i).val();
            tickets[contador - 1] = str;
            pagos[contador - 1] = str + "," + pago;
            descuentos[contador - 1] = str + "," +desc;
        }
    }
    
    if (contador > 0) {
        //Primero vamos a validar a aquellos que ya tengan NTR y OC
        $.post("facturacion/revisarAntesPrefactura.php",{"tickets": tickets}).done(function(data){
            var todoBien = true;
            if(data.toString() !== ""){//Había NTR y OC, hay que avisar para prefacturar.
                var nombreTicket = $("#nombreTicket").val();
                var texto = "";
                var textoXTicket = data.split(";");//Aquí los separamos por ticket
                for(var x = 0; x < textoXTicket.length; x++){
                    var temporal = textoXTicket[x].split(",");
                    texto += nombreTicket + " " + temporal[0] + " : ";
                    if(temporal[1] !== ""){
                        texto += "Nota de Remisión " + temporal[1] + " ";
                    }
                    if(temporal[2] !== ""){
                        texto += "Orden de Compra " + temporal[2] + "\n";
                    }
                }
                todoBien = confirm("Los siguientes " + nombreTicket + "s tiene Nota de Remisión, Orden de Compra o ambas registradas ya. \n " + texto + "\n¿Desea ingresar otra prefactura?");                
            }
            if(todoBien){
                $("#contenidos").load(pagina, {"tickets": tickets, "pagos": pagos, "descuentos": descuentos}, function() {
                    finished();
                });        
            }else{
                finished();
            }
        });
    }
}

function recargarListaPendientes(liga, cliente, color, estado, lec, FechaInicio, FechaFin){
    var clienteEnvia = $('#'+cliente).val();
    var colorEnvia = $('#'+color).val();
    var estadoEnvia = $('#'+estado).val();
    var lecEnvia = $('#'+lec).val();
    
    var pagina = liga + "?cliente=" + clienteEnvia + "&color=" + colorEnvia + "&estado=" + estadoEnvia + "$lec=" + lecEnvia;
    
    $("#contenidos").load(pagina, {"FechaInicio": $("#" + FechaInicio).val(), "FechaFin": $("#" + FechaFin).val()},
    function () {
        $(".button").button();
        finished();
    });
}

function detalleReporte(pagina, idTicket, area, tipo, editar) {
    window.open(pagina + "?idTicket=" + idTicket, '_blank');
}

function detalleTicket(pagina, idTicket, area, tipo, editar) {
    loading("Cargando ...");
    $("#detalleTicket").load(pagina, {"idTicket": idTicket, "area": area, "editar": editar, "detalle": 1}, function () {
        $("#detalleTicket").dialog({
            resizable: false,
            height: 'auto',
            width: '1250px',
            modal: true,
            title: "Detalle ticket",
            buttons: {
                "Cancelar": function () {
                    $(this).dialog("close");
                }
            }
        });
        finished();
    });
}