var oTable;
var currentPage = 0;
var currentFilter = "";

$(document).ready(function () {
    $('[data-toggle="tooltip"]').tooltip();
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

    if ($("#tAsigna").length) {
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

        oTable = $('#tAsigna').dataTable({
            "iDisplayStart": paginaInicial,
            "bJQueryUI": true,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": espanol,
            "sPaginationType": "full_numbers",
            "bDeferRender": true,
            "iDisplayLength": 10,
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

    iTable = $('.tablaUsuarios').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 10,
        "aaSorting": [[0, "asc"]]
    });

    $("#busqueda_ticket").keyup(function (event) {
        if (event.keyCode == 13) {
            $("#boton_aceptar").click();
        }
    });

    $("#num_serie").keyup(function (event) {
        if (event.keyCode == 13) {
            $("#boton_aceptar").click();
        }
    });

    $("#ticket_color").val($("#color_hidden").val());/*Seleccionamos el color que se ha filtrado, en caso que se haya seleccionado*/
    
    /*$("#cliente_ticket").multiselect({
        noneSelectedText: "Todos los clientes",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });*/

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


    if ($("#sl_cliente").length) {

        function split(val) {
            return val.split(/;\s*/);
        }
        function extractLast(term) {
            return split(term).pop();
        }

        $("#sl_cliente")
                // don't navigate away from the field on tab when selecting an item
                .bind("keydown", function (event) {
                    if (event.keyCode === $.ui.keyCode.TAB &&
                            $(this).autocomplete("instance").menu.active) {
                        event.preventDefault();
                    }

                    var key = event.which;
                    if (key == 13) {
                        // As ASCII code for ENTER key is "13"
                        $('#form_clientes').submit(function () {
                            event.preventDefault();
                            buscar_cliente();
                            return false;
                        }); // Submit form code                        
                    }
                })
                .autocomplete({
                    source: function (request, response) {
                        $.getJSON("WEB-INF/Controllers/Ajax/CargaSelect.php", {
                            palabra: extractLast(request.term),
                            FiltroCliente: true
                        }, response);
                    },
                    search: function () {
                        // custom minLength
                        var term = extractLast(this.value);
                        if (term.length < 2) {
                            return false;
                        }
                    },
                    focus: function () {
                        // prevent value inserted on focus
                        return false;
                    },
                    select: function (event, ui) {
                        var terms = split(this.value);
                        // remove the current input
                        terms.pop();
                        // add the selected item
                        terms.push(ui.item.value);
                        // add placeholder to get the comma-and-space at the end
                        terms.push("");
                        this.value = terms.join("; ");
                        return false;
                    }
                });
    }

    mostrarRefacciones();
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

    $("#contenidos").load(liga, {"id": id, "page": currentPage, "filter": filtro}, function () {
        $(".button").button();
        finished();
    });
}

function relacionarTecnico(idTicket, tecnico, tipo) {
    if ($("#" + tecnico).val() != "0") {
        loading("Guardando informaci\u00f3n ...");
        $("#mensajes").load("WEB-INF/Controllers/Controler_Ticket_SW.php", {"idTicket": idTicket, "tecnico": $("#" + tecnico).val(), "tipo": tipo}, function (data) {
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

function relacionarEjecutivo(idTicket, tecnico) {
    if ($("#" + tecnico).val() != "0") {
        loading("Guardando informaci\u00f3n ...");
        $("#mensajes").load("WEB-INF/Controllers/Controler_AsignaEjecutivo.php", {"idTicket": idTicket, "tecnico": $("#" + tecnico).val()}, function (data) {
            $("#mensajes").html(data);
            finished();
            $("#boton_aceptar").click();
        });
    } else {
        $("#error_tecnico_" + idTicket).show();
    }
}

function recargarTicketConUsuario(liga, idUsuario) {
    if (idUsuario != "" || idUsuario != 0) {
        loading("Cargando ...");
        $("#contenidos").load(liga, {"idUsuario": idUsuario}, function () {
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

function areaObligatoria(area, div_error) {
    $("#" + div_error).text("");
    if ($("#" + area).val() == "") {
        $("#" + div_error).text("Este campo es obligatorio");
        return false;
    } else {
        return true;
    }
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

        var tipo_busqueda_estado = 0;
        if ($("#ultimo_estado1").length && $("#ultimo_estado1").is(":checked")) {
            tipo_busqueda_estado = 1;
        }

        $("#contenidos").load(liga + "?page=" + currentPage + "&filter=" + filtro, {"cerrado": cerrado, "cliente": clientes, "color": $("#" + color).val(),
            "estado": $("#" + estado).val(), "moroso": moroso, "cancelado": cancelado, "mostrar": mostrarGrid, "NoSerie": $("#" + NoSerie).val(),
            "FechaInicio": $("#" + FechaInicio).val(), "FechaFin": $("#" + FechaFin).val(), "area": $("#" + area).val(),
            "tipoReporte": $("#" + tipoReporte).val(), "NoGuia": $("#" + NoGuia).val(), "tipo_busqueda_estado": tipo_busqueda_estado, "Prioridad": $("#" + prioridad).val(),
            "estadoT": $("#" + estadot).val()},
        function () {
            $(".button").button();
            finished();
        });
    }
}

function reabrirTicket(idTicket) {
    loading("Abriendo ticket ...");
    var controlador = "WEB-INF/Controllers/Ajax/updates.php";
    $("#mensajes").load(controlador, {"idTicket": idTicket, "reabrir": true}, function () {
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
    $("#contenidos").load(pagina, {"idTicket": idTicket, "area": area, "detalle": detalle, "regresar":regresar}, function () {
        $(".button").button();
        finished();
    });
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

function submitform() {
    $("#FormularioExportacion").submit();
}

function buscar_cliente() {
    loading("Cargando ...");
    var id_cliente = $("#sl_cliente").val();
    var rfc = $("#txt_rfc").val();
    var id_vendedor = $("#sl_vendedor").val();
    var estatus = $("#sl_estatus").val();
    var tipo = $("#sl_tipo").val();
    var ejecutivo_atencion = $("#sl_atencion").val();

    $("#contenidos").load("ventas/mis_clientes.php", {id_cliente: id_cliente, rfc: rfc, id_vendedor: id_vendedor,
        estatus: estatus, tipo: tipo, id_ejecutivo: ejecutivo_atencion}, function () {
        finished();
    });
}

function buscar_cliente_pakal() {
    loading("Cargando ...");
    var id_cliente = $("#sl_cliente").val();
    var rfc = $("#txt_rfc").val();
    var id_vendedor = $("#sl_vendedor").val();
    var estatus = $("#sl_estatus").val();
    var tipo = $("#sl_tipo").val();
    var ejecutivo_atencion = $("#sl_atencion").val();

    $("#contenidos").load("ventas/mis_clientes_pakal.php", {id_cliente: id_cliente, rfc: rfc, id_vendedor: id_vendedor,
        estatus: estatus, tipo: tipo, id_ejecutivo: ejecutivo_atencion}, function () {
        finished();
    });
}

function mostrarRefacciones()
{
    var mostrar_lecturas = true;
    if ($("#mostrar_contadores").length && $("#mostrar_contadores").val() == "0") {
        mostrar_lecturas = false;
    }

    var id = $("#estatusN").val();
    if (id === "9") {
        $("#refacciones").show();
        if (mostrar_lecturas) {
            $("#div_contadores").show();
        }
    } else {
        $("#refacciones").hide();
        if (mostrar_lecturas) {
            $("#div_contadores").hide();
        }
    }
    if (id === "12")
        $("#reasignacion").show();
    else
        $("#reasignacion").hide();
    if (id === "50")
        $("#asignaProveedor").show();
    else
        $("#asignaProveedor").hide();
    if (id === "67")
        $("#suministro").show();
    else
        $("#suministro").hide();

    if (id === "274") //Loyalty-> gastos de viaticos
        $("#viatico").show();
    else
        $("#viatico").hide();

    if (id === "278") { //Loyalty-> Vale Físico
        $("#noBoleto").show();
        $("#kmdiv").show();
        $("#tiempoE").show();
    } else {
        if (id === "277") //Loyalty-> gastos de viaticos
            $("#noBoleto").show();
        else
            $("#noBoleto").hide();

        if (id === "276") //Loyalty-> gastos de viaticos
            $("#tiempoE").show();
        else
            $("#tiempoE").hide();

        if (id === "275") //Loyalty-> gastos de viaticos
            $("#kmdiv").show();
        else
            $("#kmdiv").hide();
    }

    if (id === "16") {
        if (mostrar_lecturas) {
            $("#div_contadores").show();
        }
    } else {
        if (id !== "9") {
            if (mostrar_lecturas) {
                $("#div_contadores").hide();
            }
        }
    }

}


function copia(num) {
    if (num == 1) {
        document.getElementById('mensaje_enviar2').value = document.getElementById('km').value + " Kilómetros Recorridos";
    } else {
        if (num == 2) {
            document.getElementById('mensaje_enviar2').value = document.getElementById('no_boleto').value + " como No. de Boleto";
        } else {
            if (num == 3) {
                document.getElementById('mensaje_enviar2').value = document.getElementById('tiempo_esperaR').value + " minutos de tiempo de espera";
            } else {
                if (num == 4) {
                    var valor = $("#tipo_viatico option:selected").html();
                    document.getElementById('mensaje_enviar2').value = "$" + document.getElementById('monto').value + " de " + valor;
                } else {
                    if (num == 5) {
                        var valor = $("#estatusN option:selected").html();
                        document.getElementById('mensaje_enviar2').value = valor;
                    }
                }
            }
        }
    }
}

function eliminarCamion(id){
    loading("Eliminando camión...");
    var controlador = "WEB-INF/Controllers/compras/Controller_CrearCamion.php";
    $("#mensajes").load(controlador, {"idTicket": id, "eliminar": true}, function () {
        $('#contenidos').load("mesa/lista_ticket_pakal.php", function() {
            finished();
        });        
    });
}