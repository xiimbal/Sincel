var oTable;
var currentPage = 0;
var currentFilter = "";

$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
    jQuery(function($) {
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '<Ant',
            nextText: 'Sig>',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['es']);
    });

    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: "+0D",
            changeMonth: true,
            changeYear: true
        });
    });

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

    var paginaInicial = (Number($("#page").val()) * 25);

    oTable = $('.dataTable').dataTable({
        "iDisplayStart": paginaInicial,
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
            currentFilter = $('div.dataTables_filter input').val();
        }
    });

    $("#busqueda_ticket").keyup(function(event) {
        if (event.keyCode == 13) {
            $("#boton_aceptar").click();
        }
    });

    $("#num_serie").keyup(function(event) {
        if (event.keyCode == 13) {
            $("#boton_aceptar").click();
        }
    });

    $("#ticket_color").val($("#color_hidden").val());/*Seleccionamos el color que se ha filtrado, en caso que se haya seleccionado*/

    if (!$("#vacio").length) {
        buscarEnTabla($("#filter").val());
    }

    $("#cliente_ticket").multiselect({
        noneSelectedText: "Todos los clientes",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter();

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

/**
 * Cargamos la liga mandando el POST idTicket
 * @param {type} liga direccion relativo al script a cargar en contenidos.
 * @param {type} folio idTicket
 * @returns {undefined} void.
 */
function recargarListaTicketBusquedaFolio(liga, folio) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {"idTicket": folio, "mostrar": true}, function() {
        $(".button").button();
        finished();
    });
}

/**
 * Recarga el grid de lista de tickets
 * @param {type} liga php del sistema que se va a recargar
 * @param {type} checkbox nombre del checkbox de cerrado
 * @param {type} cliente nombre del select de cliente
 * @param {type} color nombre del select de color
 * @param {type} estado nombre del select de estado
 * @param {type} checkmoroso nombre del checkbox de moroso
 * @param {type} checkcancelado nombre del checkbox de cancelado
 * @param {type} mostrarGrid true en caso de querer mostrar el grid o false en caso contrario
 * @param {type} NoSerie NoSerie de equipo
 * @param {type} FechaInicio Fecha de inicio de los tickets
 * @param {type} FechaFin Fecha final de los tickets
 * @param {type} area Area de lso tickets
 * @param {type} tipoReporte Tipo de reporte de los tickets
 * @returns {undefined} void
 */
function recargarListaTicket(liga, checkbox, cliente, color, estado, checkmoroso, checkcancelado, mostrarGrid, NoSerie, FechaInicio, FechaFin, area, tipoReporte) {
    if ($("#busqueda_ticket").length && $("#busqueda_ticket").val() != "") {
        //if($.isNumeric($("#busqueda_ticket").val())){/*Buscamos por IdTicket*/
        recargarListaTicketBusquedaFolio(liga, $("#busqueda_ticket").val());
        //}
    } else {
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
        if ((filtro == "" && $("#filter").val() != "") || filtro == null) {
            filtro = $("#filter").val();
        }
        if (filtro == null) {
            filtro = "";
        }
        filtro = filtro.replace(/ /g, "_XX__XX_");

        /*Procesamos los clientes que vengan concatenados*/
        var res = $("#" + cliente).val();
        var clientes = "";
        if (res != null) {
            for (var i = 0; i < res.length; i++) {
                clientes += ("'" + res[i].substring(0, res[i].length - 5) + "',");
            }
            if (clientes != "") {
                clientes = clientes.substring(0, clientes.length - 1);
            }
        }

        $("#contenidos").load(liga + "?page=" + currentPage + "&filter=" + filtro, {"cerrado": cerrado, "cliente": clientes, "color": $("#" + color).val(),
            "estado": $("#" + estado).val(), "moroso": moroso, "cancelado": cancelado, "mostrar": mostrarGrid, "NoSerie": $("#" + NoSerie).val(),
            "FechaInicio": $("#" + FechaInicio).val(), "FechaFin": $("#" + FechaFin).val(), "area": $("#" + area).val(), "tipoReporte": $("#" + tipoReporte).val()},
        function() {
            $(".button").button();
            finished();
        });
    }
}

function reabrirTicket(idTicket) {
    loading("Abriendo ticket ...");
    var controlador = "WEB-INF/Controllers/Ajax/updates.php";
    $("#mensajes").load(controlador, {"idTicket": idTicket, "reabrir": true}, function() {
        //recargarListaTicket(liga, checkbox, cliente, color, estado, checkmoroso, checkcancelado, true);
        $("#boton_aceptar").click();
    });
}
function editarTicket(pagina, idTicket, area, detalle) {
    limpiarMensaje();
    loading("Cargando ...");
    var regresar = "";
    if ($("#regresar").length) {
        regresar = $("#regresar").val();
    }
    $("#contenidos").load(pagina, {"idTicket": idTicket, "area": area, "detalle": detalle, "regresar":regresar}, function() {
        $(".button").button();
        finished();
    });
}
function detalleTicket(pagina, idTicket, area, tipo, editar) {
    loading("Cargando ...");
    $("#detalleTicket").load(pagina, {"idTicket": idTicket, "area": area, "editar": editar, "detalle": 1}, function() {
        $("#detalleTicket").dialog({
            resizable: false,
            height: 'auto',
            width: '1250px',
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

function marcarVentaFacturada(NoVenta){
    if(confirm("Esta seguro que desea marcar como factura la venta directa "+NoVenta)){
        var controlador = "WEB-INF/Controllers/Ajax/updates.php";
        $("#mensajes").load(controlador, {'NoVenta':NoVenta, 'facturar':true}, function(){
            cambiarContenidos("facturacion/pendientes.php");
        });
    }
}