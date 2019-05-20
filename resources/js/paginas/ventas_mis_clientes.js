var nserie = "";
var pattern = " - Modelo";

$(document).ready(function () {
    if ($("#cliente").length == 1) {
        if ($('#tipo').is(':checked')) {
            tglecturas(null, null);
        } else if ($('#tipo1').is(':checked')) {
            tgtickets(null, null);
        } else if ($('#tipo3').is(':checked')) {
            tgmtto(null, null);
        } else {
            tgmov(null, null);
        }
    }
});

$('.boton').button().css('margin-top', '20px');
$("#botonvarios").hide();
$("#vendedor").multiselect({
    multiple: false,
    noneSelectedText: "No ha seleccionado",
    selectedList: 1
}).multiselectfilter({
    label: 'Filtro',
    placeholder: 'Escribe el filtro'
});
$("#cliente").multiselect({
    multiple: false,
    noneSelectedText: "No ha seleccionado",
    selectedList: 1
}).multiselectfilter({
    label: 'Filtro',
    placeholder: 'Escribe el filtro'
});

$('#ss').searchbox({
    searcher: function (value, name) {
        var tipo = 1;
        if (name == "Nombre de cliente") {
            tipo = 2;
            if (value.length == 0) {
                if ($('#tipo').is(':checked')) {
                    tglecturas(null, null);
                } else if ($('#tipo1').is(':checked')) {
                    tgtickets(null, null);
                } else if ($('#tipo3').is(':checked')) {
                    tgmtto(null, null);
                } else {
                    tgmov(null, null);
                }
            } else {
                if ($('#tipo').is(':checked')) {
                    tglecturas(value, tipo);
                } else if ($('#tipo1').is(':checked')) {
                    tgtickets(value, tipo);
                } else if ($('#tipo3').is(':checked')) {
                    tgmtto(value, tipo);
                } else {
                    tgmov(value, tipo);
                }
            }
        } else {
            if (value.length == 0) {
                alert("Introduce el equipo");
            }
            else {
                if ($('#tipo').is(':checked')) {
                    tglecturas(value, tipo);
                } else if ($('#tipo1').is(':checked')) {
                    tgtickets(value, tipo);
                } else if ($('#tipo3').is(':checked')) {
                    tgmtto(value, tipo);
                } else {
                    tgmov(value, tipo);
                }
            }
        }
    },
    prompt: 'Inserta el equipo',
    menu: '#mm'
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

$("#tipo").change(function () {
    $("#divinfo").empty();
    tglecturas(null, null);
});


function tglecturas(like, tipo) {    
    $("#botonvarios").hide();
    var di = "WEB-INF/Controllers/Ventas/Controller_json_lecturas.php";
    if ($('#cliente').length && $('#cliente').val().length) {
        di += "?cliente=" + $('#cliente').val();
        if ($('#vendedor').length && $('#vendedor').val().length) {
            di += "&id=" + $('#vendedor').val();
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        } else {
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        }
    } else {
        if (like != null && like != "") {
            di += "?like=" + like;
        }
        if (tipo != null) {
            di += "&tipo=" + tipo;
        }
    }
    $('#tg').tree({
        animate: true,
        url: di,
        method: 'get',
        checkbox: false,
        onlyLeafCheck: false,
        onDblClick: function (node) {
            var n = node.id + "";
            if (n.substring(0, 4) === "cli%") {
                if($("#contactoPermiso").val() == "1"){
                    $("#divinfoup").html(
                        "<div align='right'>"
                        + "<a href='#' onclick=\"lanzarPopUp('Centro Costo', 'contrato/lista_cc.php?id=" + n.substring(4, n.length) + "'); return false;\">Centro de Costo</a>"
                        + "</div>" + "<br/><div align='right'>"
                        + "<a href='#' onclick=\"lanzarPopUp('Alta Localidad', 'contrato/alta_localidad.php?id=" + n.substring(4, n.length) + "'); return false;\">Alta localidad</a>"
                        + "</div>" + "<br/><div align='right'><a href='#' onclick=\"lanzarPopUpAjustable('Lectura corte', 'contrato/lecturas_corte.php?cliente=" + n.substring(4, n.length) + "',1200,700); return false;\">Lecturas de corte</a></div>"
                        + "<br/><div align='right'><a href='#' onclick=\"lanzarPopUp('Alta contacto', 'cliente/alta_contacto.php?ClaveCliente=" + $('#cliente').val() +"'); return false;\">Editar contactos</a></div>");
                }else{
                    $("#divinfoup").html(
                        "<div align='right'>"
                        + "<a href='#' onclick=\"lanzarPopUp('Centro Costo', 'contrato/lista_cc.php?id=" + n.substring(4, n.length) + "'); return false;\">Centro de Costo</a>"
                        + "</div>" + "<br/><div align='right'>"
                        + "<a href='#' onclick=\"lanzarPopUp('Alta Localidad', 'contrato/alta_localidad.php?id=" + n.substring(4, n.length) + "'); return false;\">Alta localidad</a>"
                        + "</div>" + "<br/><div align='right'><a href='#' onclick=\"lanzarPopUpAjustable('Lectura corte', 'contrato/lecturas_corte.php?cliente=" + n.substring(4, n.length) + "',1200,700); return false;\">Lecturas de corte</a></div>");
                }
                
            } else {
                n = node.id + ""
                n = n.substring(0, 2);
                if (n != "no") {
                    n = node.id + "";
                    n = n.substring(0, 3);
                    if (n != "lo%") {
                        var direccion = "WEB-INF/Controllers/Ventas/Controller_tabla_lecturas.php";
                        nserie = ((node.text + "").split(pattern))[0];
                        $("#divinfo").load(direccion, {id: nserie}, function () {
                            oTable = $('#tinfo').dataTable({
                                "bJQueryUI": true,
                                "bRetrieve": true,
                                "bDestroy": true,
                                "oLanguage": espanol,
                                "sPaginationType": "full_numbers",
                                "bDeferRender": true,
                                "iDisplayLength": 25
                            });
                        });
                    } else {
                        var direccion = "ventas/NuevasLecturasLocalidad.php";
                        n = node.id + "";
                        n = n.substring(3, n.length);
                        $("#divinfo").load(direccion, {id: n}, function () {
                            if (permiso_alta === 1) {
                                $("#divinfoup").html(
                                        "<div align='right'>"
                                        + "<a href='#' onclick=\"lanzarPopUp('Alta equipo', 'contrato/alta_equipo.php?id=" + n + "'); return false;\">Alta de equipos</a>"
                                        + "</div><br/>"
                                        + "<div align='right'>"
                                        + "<a href='#' onclick=\"lanzarPopUpAjustable('Lecturas de corte', 'contrato/lecturas_corte.php?id=" + n + "',1200,700); return false;\">Lecturas de corte</a>"
                                        + "</div>");
                            }
                        });
                    }
                }
            }
        },
        onLoadSuccess: function (row, data) {               
            $('#tg').tree('collapseAll');
        },
        onLoadError: function(arguments){
            console.log(arguments);
        }
    });
}


$("#tipo1").change(function () {
    $("#divinfo").empty();
    tgtickets(null, null);
});

function tgtickets(like, tipo) {
    $("#botonvarios").hide();
    var di = "WEB-INF/Controllers/Ventas/Controller_json_tickets.php";
    if ($('#cliente').length && $('#cliente').val().length) {
        di += "?cliente=" + $('#cliente').val();
        if ($('#vendedor').length && $('#vendedor').val().length) {
            di += "&id=" + $('#vendedor').val();
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        } else {
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        }
    } else {
        if (like != null && like != "") {
            di += "?like=" + like;
        }
        if (tipo != null) {
            di += "&tipo=" + tipo;
        }
    }
    $('#tg').tree({animate: true,
        url: di,
        method: 'get',
        checkbox: false,
        onlyLeafCheck: false,
        onDblClick: function (node) {
            var n = node.id + "";
            n = n.substring(0, 2);
            if (n != "no") {
                var direccion = "WEB-INF/Controllers/Ventas/Controller_tabla_tickets.php";
                nserie = ((node.text + "").split(pattern))[0];
                $("#divinfo").load(direccion, {id: node.text}, function () {
                    oTable = $('#tinfo').dataTable({
                        "bJQueryUI": true,
                        "bRetrieve": true,
                        "bDestroy": true,
                        "oLanguage": espanol,
                        "sPaginationType": "full_numbers",
                        "bDeferRender": true,
                        "iDisplayLength": 25
                    });
                });
            }

        },
        onLoadSuccess: function (row) {            
            $('#tg').tree('collapseAll');
        }
    });
}

$("#tipo2").change(function () {
    $("#divinfo").empty();
    tgmov(null, null);
});



function tgmov(like, tipo) {
    $("#botonvarios").show();
    var di = "WEB-INF/Controllers/Ventas/Controller_json_movimientos.php";
    if ($('#cliente').length && $('#cliente').val().length) {
        di += "?cliente=" + $('#cliente').val();
        if ($('#vendedor').length && $('#vendedor').val().length) {
            di += "&id=" + $('#vendedor').val();
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        } else {
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        }
    } else {
        if (like != null && like != "") {
            di += "?like=" + like;
        }
        if (tipo != null) {
            di += "&tipo=" + tipo;
        }
    }
    $('#tg').tree({
        animate: true,
        url: di,
        checkbox: true,
        onlyLeafCheck: true,
        method: 'get',
        onBeforeCheck: function (node, checked) {
            var id = node.id + "";
            if (id.substring(0, 2) == "no") {
                alert("No puedes seleccionarlo");
                $('#tg').tree('uncheck', node);
            }
        },
        onDblClick: function (node) {
            var id = node.id + "";
            if (id.substring(0, 2) !== "no") {
                $("#botonvarios").hide();
                var alma = 0;
                var cliente = id.substring(0, id.length);
                if (id.substring(0, 3) === "&A%") {
                    alma = 1;
                    cliente = id.substring(3, id.length);
                }
                var direccion = "WEB-INF/Controllers/Ventas/Controller_tabla_movimientos.php";
                nserie = ((node.text + "").split(pattern))[0];
                if ($('#vendedor').length && $('#vendedor').val().length) {
                    $("#divinfo").load(direccion, {id: $('#vendedor').val(), cliente: cliente, nserie: nserie, almacen: alma}, function () {
                        tgmovjs();
                    });
                } else {
                    $("#divinfo").load(direccion, {cliente: cliente, nserie: nserie, almacen: alma}, function () {
                        tgmovjs();
                    });
                }
            } else {
                alert("No puedes seleccionarlo");
                $('#tg').tree('uncheck', node);
            }

        },
        onLoadSuccess: function (row) {
            $('#tg').tree('collapseAll');
        }
    });
}


function tgmtto(like, tipo) {
    $("#botonvarios").hide();
    var di = "WEB-INF/Controllers/Ventas/Controller_json_mtto.php";
    if ($('#cliente').length && $('#cliente').val().length) {
        di += "?cliente=" + $('#cliente').val();
        if ($('#vendedor').length && $('#vendedor').val().length) {
            di += "&id=" + $('#vendedor').val();
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        } else {
            if (tipo != null) {
                di += "&tipo=" + tipo;
            }
            if (like != null && like != "") {
                di += "&like=" + like;
            }
        }
    } else {
        if (like != null && like != "") {
            di += "?like=" + like;
        }
        if (tipo != null) {
            di += "&tipo=" + tipo;
        }
    }
    $('#tg').tree({animate: true,
        url: di,
        method: 'get',
        checkbox: false,
        onlyLeafCheck: false,
        onDblClick: function (node) {
            var n = node.id + "";
            n = n.substring(0, 2);
            if (n != "no") {
                var direccion = "WEB-INF/Controllers/Ventas/Controller_tabla_mtto.php";
                nserie = node.id;
                $("#divinfo").load(direccion, {id: nserie}, function () {
                });
            }

        },
        onLoadSuccess: function (row) {
            $('#tg').tree('collapseAll');
        }
    });
}

$("#tipo3").change(function () {
    $("#divinfo").empty();
    tgmtto(null, null);
});

$("#cliente").change(function () {
    if ($('#tipo').is(':checked')) {
        tglecturas(null, null);
    } else if ($('#tipo1').is(':checked')) {
        tgtickets(null, null);
    } else if ($('#tipo3').is(':checked')) {
        tgmtto(null, null);
    } else {
        tgmov(null, null);
    }
});

function tgmovjs() {

}

$("#botonvarios").click(function () {
    $("#divinfoup").empty();
    var direccion = "WEB-INF/Controllers/Ventas/Controller_tabla_movimientos2.php";
    var nodes = $('#tg').tree('getChecked');
    var s = '';
    if (nodes.length != 0) {
        for (var i = 0; i < nodes.length; i++) {
            s += ((nodes[i].text + "").split(pattern))[0] + "&&";
        }
        s = s.substring(0, s.length - 2);
        var cliente = $("#cliente").val();
        if ($('#vendedor').length && $('#vendedor').val().length) {
            $("#divinfo").load(direccion, {id: $('#vendedor').val(), nserie: s, cliente: cliente}, function () {
                movimientos2();
            });
        } else {
            $("#divinfo").load(direccion, {nserie: s, cliente: cliente}, function () {
                movimientos2();
            });
        }
    } else {
        alert("Seleccione al menos un equipo para un movimiento multiple");
    }
});

function movimientos2() {
}


function cargarlocalidades(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_cliloc.php", {id: $("#" + origen).val(), vendedor: $('#vendedor').val()});
}

function cargaranexo(origen, componente) {
    var dir = "WEB-INF/Controllers/Ajax/updates.php";
    $("#contenidos_invisibles").load(dir, {'cc': $("#" + origen).val(), 'crear': 'true'}, function (data) {/*Asociamos o creamos anexos y contratos cuando sea necesario*/
        $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clianexo.php", {ccosto: $("#" + origen).val(), 'group': true}, function () {
            if ($("#id_bitacora").val() != "") {
                $("#anexo").val($("#id_anexo").val());
            }
            cargarServicios('anexo', 'servicio');
        });
    });
}

function cargarclientes(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clientes.php", {cliente: $("#" + origen).val(), tipo: 1, modalidad: 'arrendamiento'}, function (data) {
        $('#' + componente).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    });

}

function nuevaLectura(dir) {
    $("#divinfo").load(dir, {id: nserie});
}


function contadorbn(origen, valor) {
    if ($("#" + origen).length > 0) {
        $("#" + origen).rules("add", {
            min: valor,
            messages: {
                min: " * Ingrese un número mayor o igual a " + valor + "(Debido a lectura anterior)"
            }
        });
    }
}

function contadorcl(origen, valor) {
    if ($("#" + origen).length > 0) {
        $("#" + origen).rules("add", {
            min: valor,
            messages: {
                min: " * Ingrese un número mayor o igual a " + valor + "(Debido a lectura anterior)"
            }
        });
    }
}
