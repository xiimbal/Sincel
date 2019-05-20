var oTable;
var currentPage = 0;
var currentFilter = "";

$(document).ready(function(){
    //openNav();
    closeNav();
    
    $(".multiselect").multiselect({
        noneSelectedText: "Todos los registros",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

    $(".select").multiselect({
        multiple: false,
        selectedList: 1,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $("#busqueda_ticket").keyup(function (event) {
        if (event.keyCode == 13) {
            $("#boton_aceptar").click();
        }
    });

    $("#tecnico").keyup(function (event) {
        if (event.keyCode == 13) {
            $("#boton_aceptar").click();
        }
    });
    
    $(".boton").button();/*Estilo de botones*/
    jQuery(function ($) {
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

    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: "+0D",
            changeMonth: true,
            changeYear: true
        });
    });
    $('.fecha').mask("9999-99-99");

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

    if ($("#tAlmacen").length) {
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

        oTable = $('#tAlmacen').dataTable({
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

        if (!$("#vacio").length) {
            buscarEnTabla($("#filter").val());
        }
    }

    var array = [];
    for (var i = 0; i < 24; i++) {
        if (i < 10) {
            array.push("0" + i + ":00");
            array.push("0" + i + ":10");
            array.push("0" + i + ":20");
            array.push("0" + i + ":30");
            array.push("0" + i + ":40");
            array.push("0" + i + ":50");
        } else {
            array.push(i + ":00");
            array.push(i + ":10");
            array.push(i + ":20");
            array.push(i + ":30");
            array.push(i + ":40");
            array.push(i + ":50");
        }
    }

    $('.datetime').datetimepicker({//datetimepicker
        mask: '9999-19-39 29:59:59',
        allowTimes: array
    });
});


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
 * @param {type} area Area de los tickets
 * @param {type} tipoReporte Tipo de reporte de los tickets
 * @returns {undefined} void
 */
function recargarListaTicket(liga, checkbox, cliente, color, estado, checkmoroso, checkcancelado, mostrarGrid,
        NoSerie, FechaInicio, FechaFin, area, tipoReporte, NoGuia, prioridad, estadot) {
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

        /*var moroso = false;
        if ($("#" + checkmoroso).is(":checked")) {
            moroso = true;
        }*/

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
        

        var tipo_busqueda_estado = 0;
        if ($("#ultimo_estado1").length && $("#ultimo_estado1").is(":checked")) {
            tipo_busqueda_estado = 1;
        }

        $("#contenidos").load(liga + "?page=" + currentPage + "&filter=" + filtro, {"cerrado": cerrado, "cliente": $("#"+cliente).val(), "color": $("#" + color).val(),
            "estado": $("#" + estado).val(), /*"moroso": moroso,*/ "cancelado": cancelado, "mostrar": mostrarGrid, "NoSerie": $("#" + NoSerie).val(),
            "FechaInicio": $("#" + FechaInicio).val(), "FechaFin": $("#" + FechaFin).val(), "area": $("#" + area).val(),
            "tipoReporte": $("#" + tipoReporte).val(), "NoGuia": $("#" + NoGuia).val(), "tipo_busqueda_estado": tipo_busqueda_estado, "Prioridad": $("#" + prioridad).val(),
            "estadoT": $("#" + estadot).val()},
        function () {
            $(".button").button();
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
    $("#contenidos").load(liga, {"idTicket": folio, "mostrar": true}, function () {
        $(".button").button();
        finished();
    });
}

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

function detalleTicket(pagina, idTicket, area, tipo, editar, viejo) {
    if (viejo == null || viejo == "0") {        
        var $dialog = $('<div></div>').css({height: "650px", overflow: "auto", position: "relative", top: "10px"})
                .html('<iframe style="border: 0px; " src="' + pagina + '?idTicket=' + idTicket + '&area=' + area + '&detalle=1&frame=1" width="100%" height="99%" scrolling="yes"></iframe>')
                .dialog({
                    autoOpen: false,
                    modal: true,
                    height: 850,
                    width: 1100,
                    title: 'Ticket: ' + idTicket,
                    position: 'top'
                });
        $dialog.dialog('open');
    } else {        
        var $dialog = $('<div></div>').css({height: "650px", overflow: "auto", position: "relative", top: "10px"})
                .html('<iframe style="border: 0px; "src="mesa/consulta_ticket_viejo.php?idTicket=' + idTicket + '&area=' + area + '&detalle=1&frame=1" width="100%" height="99%" scrolling="yes"></iframe>')
                .dialog({
                    autoOpen: false,
                    modal: true,
                    height: 850,
                    width: 1100,
                    title: 'Ticket: ' + idTicket,
                    position: 'top'
                });
        $dialog.dialog('open');
    }
}

function editarTicket(pagina, idTicket, area, detalle) {
    limpiarMensaje();
    loading("Cargando ...");
    var regresar = "";
    if ($("#regresar").length) {
        regresar = $("#regresar").val();
    }
    $("#contenidos").load(pagina, {"idTicket": idTicket, "area": area, "detalle": detalle, "regresar":regresar}, function () {
        $(".button").button();
        finished();
    });
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

function openNav() {
    document.getElementById("mySidenav").style.width = "200px";
    document.getElementById("main_panel").style.marginLeft = "200px";
    $("#open").hide();
   //$("#mySidenav").toggle();
    
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
    document.getElementById("main_panel").style.marginLeft= "0";
    $("#open").show();
    //$("#mySidenav").toggle("slow");
}

function monitorActividades(pagina){
    window.open(pagina, '_blank');
}