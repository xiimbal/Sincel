var anterior = 0;
$(document).ready(function () {
    $(".boton").button();/*Estilo de botones*/
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

    if ($('#tAlmacen').length) {
        oTable = $('#tAlmacen').dataTable({
            "bJQueryUI": true,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": espanol,
            "sPaginationType": "full_numbers",
            "bDeferRender": true,
            "iDisplayLength": 100,
            "aaSorting": [[0, "desc"]]
        });
    }

    $(".fecha").mask("9999-99-99");
    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true,
            maxDate: "+0D"
        });
    });

    $('.fecha_periodo').mask("99-9999");
    $('.fecha_periodo').each(function () {
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

    $(".filtro").multiselect({
        noneSelectedText: "Selecciona localidad",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();

    $("#cliente_ticket").multiselect({
        noneSelectedText: "Todos los clientes",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Todos los registros",
        selectedList: 3, selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

    if ($("#boton_aceptar").length && $("#idTicketHW").length) {
        $("#idTicketHW").keyup(function (event) {
            if (event.keyCode == 13) {
                $("#boton_aceptar").click();
            }
        });
    }

    if ($("#periodo_psf").length && $("#buscar_equipos").length) {
        $("#periodo_psf").keyup(function (event) {
            if (event.keyCode == 13) {
                $("#buscar_equipos").click();
            }
        });
    }

    if ($("#no_serie_psf").length && $("#buscar_equipos").length) {
        $("#no_serie_psf").keyup(function (event) {
            if (event.keyCode == 13) {
                $("#buscar_equipos").click();
            }
        });
    }

    if ($("#NoSerieConfi").length && $("#NoSerieConfi").length) {
        $("#NoSerieConfi").keyup(function (event) {
            if (event.keyCode == 13) {
                $("#ver_equiposConfi").click();
            }
        });

        $("#ModeloConfi").keyup(function (event) {
            if (event.keyCode == 13) {
                $("#ver_equiposConfi").click();
            }
        });
    }
});

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
function recargarListaTicket(liga, checkbox, cliente, color, estado, checkmoroso, checkcancelado, mostrarGrid, NoSerie, FechaInicio, FechaFin, area, tipoReporte, usuario) {
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

        /*var filtro = currentFilter;
         if ((filtro == "" && $("#filter").val() != "") || filtro == null) {
         filtro = $("#filter").val();
         }
         if (filtro == null) {
         filtro = "";
         }
         filtro = filtro.replace(/ /g, "_XX__XX_");*/

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

        $("#contenidos").load(liga, {"cerrado": cerrado, "cliente": clientes, "color": $("#" + color).val(),
            "estado": $("#" + estado).val(), "moroso": moroso, "cancelado": cancelado, "mostrar": mostrarGrid, "NoSerie": $("#" + NoSerie).val(),
            "FechaInicio": $("#" + FechaInicio).val(), "FechaFin": $("#" + FechaFin).val(), "area": $("#" + area).val(),
            "tipoReporte": $("#" + tipoReporte).val(), 'idUsuario': $("#" + usuario).val()},
        function () {
            $(".button").button();
            finished();
        });
    }
}

function recargarListaTicketUsuario(liga, checkbox, cliente, color, estado, checkmoroso, checkcancelado, mostrarGrid,
        idTicket, FechaInicio, FechaFin, area, tipoReporte, NoGuia, prioridad, estadot, idUsuario) {
    if ($("#" + idTicket).val() != "" && !$.isNumeric($("#" + idTicket).val())) {
        $("#error_ticket").show();
        $("#error_ticket").text("El ticket tiene que ser dato númerico");
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

        $("#contenidos").load(liga, {"idTicket": $("#" + idTicket).val(), "idUsuario": $("#" + idUsuario).val(),
            "cerrado": cerrado, "cliente": clientes, "color": $("#" + color).val(), "estado": $("#" + estado).val(),
            "moroso": moroso, "cancelado": cancelado, "mostrar": mostrarGrid, "FechaInicio": $("#" + FechaInicio).val(),
            "FechaFin": $("#" + FechaFin).val(), "area": $("#" + area).val(), "tipoReporte": $("#" + tipoReporte).val(),
            "NoGuia": $("#" + NoGuia).val(), "tipo_busqueda_estado": tipo_busqueda_estado, "Prioridad": $("#" + prioridad).val(),
            "estadoT": $("#" + estadot).val()}, function () {
            finished();
        });
    }
}

function actualizarEstatusMovimiento(liga, idMovimiento, i, comentario, autorizacion, almacenAnterior, noSerie, total) {
    if ($("#contador_bn_" + i).val() == "") {
        $("#span_" + anterior).hide();
        $("#spanColor_" + anterior).hide();
        $("#span_" + i).show();
        anterior = i;
        return false;
    }
    loading("Actualizando informaci\u00f3n ...");
    $("#mensajes").load("WEB-INF/Controllers/Controler_Movimiento.php", {"idMovimiento": idMovimiento, "almacen": $("#almacen_" + i).val(), "comentario": $("#" + comentario).val(), "estatus": autorizacion, "almacenAnterio": almacenAnterior, "noSerie": noSerie, "contadorBN": $("#contador_bn_" + i).val()}, function () {
        $("#contenidos").load(liga, function () {
            finished();
        });
    });
    anterior = i;
}

function actualizarEstatusMovimientoColor(liga, idMovimiento, i, comentario, autorizacion, almacenAnterior, noSerie, total) {
    if ($("#contador_bn_" + i).val() == "") {
        $("#span_" + anterior).hide();
        $("#spanColor_" + anterior).hide();
        $("#span_" + i).show();
        anterior = i;
        return false;
    }
    if ($("#contador_color_" + i).val() == "") {
        $("#span_" + anterior).hide();
        $("#spanColor_" + anterior).hide();
        $("#spanColor_" + i).show();
        anterior = i;
        return false;
    }
    loading("Actualizando informaci\u00f3n ...");
    $("#mensajes").load("WEB-INF/Controllers/Controler_Movimiento.php", {"idMovimiento": idMovimiento, "almacen": $("#almacen_" + i).val(), "comentario": $("#" + comentario).val(), "estatus": autorizacion, "almacenAnterio": almacenAnterior, "noSerie": noSerie, "contadorBN": $("#contador_bn_" + i).val(), "contadorColor": $("#contador_color_" + i).val()}, function () {
        $("#contenidos").load(liga, function () {
            finished();
        });
    });
}

function editarTicket(pagina, idTicket, area, detalle) {
    limpiarMensaje();
    loading("Cargando ...");
    var regresar = "";
    if ($("#regresar").length) {
        regresar = $("#regresar").val();
    }
    $("#contenidos").load(pagina, {"idTicket": idTicket, "area": area, "detalle": detalle, "regresar": regresar}, function () {
        $(".button").button();
        finished();
    });
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

function recargarPeriodoSinFacturar(liga, cliente, servicio, periodo, serie, facturado) {
    loading("Cargando ...");
    /*Procesamos los clientes que vengan concatenados*/
    var res = $("#" + cliente).val();
    var clientes = "";
    if (res != null) {
        for (var i = 0; i < res.length; i++) {
            clientes += ("'" + res[i] + "',");
        }
        if (clientes != "") {
            clientes = clientes.substring(0, clientes.length - 1);
        }
    }
    /*Procesamos los servicios que vengan concatenados*/
    res = $("#" + servicio).val();
    var servicios = "";
    if (res != null) {
        for (var i = 0; i < res.length; i++) {
            servicios += ("" + res[i] + ",");
        }
        if (servicios != "") {
            servicios = servicios.substring(0, servicios.length - 1);
        }
    }

    var fac = 0;
    if ($("#" + facturado).is(":checked")) {
        fac = 1;
    }

    $("#contenidos").load(liga, {"cliente": clientes, "servicio": servicios, "periodo": $("#" + periodo).val(), "serie": $("#" + serie).val(),
        "mostrar": true, "facturado": fac}, function () {
        $(".button").button();
        finished();
    }
    );
}

function mostrarEntradasDeEquipo(liga, almacen) {
    loading("Cargando ...");
    /*Procesamos los clientes que vengan concatenados*/
    var res = $("#" + almacen).val();
    var almacenes = "";
    if (res != null) {
        for (var i = 0; i < res.length; i++) {
            almacenes += ("" + res[i] + ",");
        }
        if (almacenes != "") {
            almacenes = almacenes.substring(0, almacenes.length - 1);
        }
    }

    $("#contenidos").load(liga, {"almacenes": almacenes, "mostrar": true}, function () {
        $(".button").button();
        finished();
    }
    );
}

function mostrarEquiposConfiguracion(liga, serie, modelo) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {'NoSerie': $("#" + serie).val(), 'Modelo': $("#" + modelo).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function mostrarComponentesAlmacen(liga, tipo, almacen, modelo) {
    loading("Cargando ...");
    /*Procesamos los clientes que vengan concatenados*/
    var res = $("#" + almacen).val();
    var almacenes = "";
    if (res != null) {
        for (var i = 0; i < res.length; i++) {
            almacenes += ("" + res[i] + ",");
        }
        if (almacenes != "") {
            almacenes = almacenes.substring(0, almacenes.length - 1);
        }
    }
    $("#contenidos").load(liga, {'tipoComponente': $("#" + tipo).val(), 'almacenes': almacenes, 'modelo': $("#" + modelo).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function mostrarEquipoAlmacen(liga, almacen, modelo, serie) {
    loading("Cargando ...");
    /*Procesamos los clientes que vengan concatenados*/
    var res = $("#" + almacen).val();
    var almacenes = "";
    if (res != null) {
        for (var i = 0; i < res.length; i++) {
            almacenes += ("" + res[i] + ",");
        }
        if (almacenes != "") {
            almacenes = almacenes.substring(0, almacenes.length - 1);
        }
    }
    $("#contenidos").load(liga, {'serie': $("#" + serie).val(), 'almacenes': almacenes, 'modelo': $("#" + modelo).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function mostrarComponentes(liga, modelo, parte, tipo) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {'tipoComponente': $("#" + tipo).val(), 'parte': $("#" + parte).val(), 'modelo': $("#" + modelo).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function mostrarCampaniaTurno(liga, campania, turno) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {'CampaniaFiltro': $("#" + campania).val(), 'TurnoFiltro': $("#" + turno).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function mostrarEspecial(liga, empleado, apellidop, apellidom, nombre, campania, turno) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {'EmpleadoFiltro': $("#" + empleado).val(), 'NombreFiltro': $("#" + nombre).val(), 'ApellidoPFiltro': $("#" + apellidop).val(), 'ApellidoMFiltro': $("#" + apellidom).val(), 'CampaniaFiltro': $("#" + campania).val(), 'TurnoFiltro': $("#" + turno).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function mostrarBitacora(liga, operador, ln, fecha) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {'OperadorFiltro': $("#" + operador).val(), 'LNFiltro': $("#" + ln).val(), 'FechaFiltro': $("#" + fecha).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}