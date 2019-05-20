$(function() {
    jQuery(function($) {
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '<Ant',
            nextText: 'Sig>',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Mi\u00e9rcoles', 'Jueves', 'Viernes', 'S\u00e1bado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mi\u00e9', 'Juv', 'Vie', 'S\u00e1b'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'S\u00e1'],
            weekHeader: 'Sm',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''};
        $.datepicker.setDefaults($.datepicker.regional['es']);
    });
});

function eliminarRegistro(controlador, lista) {
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        loading("Eliminando ... ");
        $('#mensajes').load(controlador, function() {
            $('#contenidos').load(lista, function() {
                finished();
                $(".button").button();
            });
        });
    }
}

function eliminarRegistroProv(controlador, id, lista) {
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        $('#mensajes').load(controlador, function() {
            $('#contenidos').load(lista, {"id": id});
        });
    }
}

function limpiarMensaje() {
    $('#mensajes').empty();
}

var checando_sesion = false;
function cambiarContenidos(liga, titulo) {
    // console.log(titulo.split(" > "));        
    loading("Cargando ...");
    $("#contenidos").empty();
    limpiarMensaje();
    if( $('#sidebar').hasClass('active') ){
        console.log("Menu activo");        
    }else{
        $('#sidebar').toggleClass('active');
    }
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            
            /*Estás páginas necesita cargarse dos veces para no mostrar el banner*/
            if (liga == "facturacion/NominaATI.php" || liga == "facturacion/NominaSCG.php") {
                $('#contenidos_invisibles').load(liga, function() {
                    setTimeout(function() {
                        $('#contenidos').load(liga, function() {
                            $('#titulo').text(titulo);
                            $(".tabs").tabs();
                            $(".button").button();
                            if (!checando_sesion) {/*Corremos un timer para verificar que la sesion siga activada*/
                                setInterval(function() {
                                    checando_sesion = true;
                                    $('#loading_text').load("verificaSession.php", function(data) {
                                        if (data.toString().indexOf("false") !== -1) {/*En caso de que la sesion siga existiendo*/
                                            window.location = "index.php?session=finished";
                                        }
                                    });
                                }, 300000);/*Cada 5 minutos revisa que la sesion no haya caducado*/
                            }
                            finished();
                        });
                    }, 3000);
                });
            }else if(liga == "ventas/mis_clientes.php"){ 
                window.location = "principal.php?mnu=ventas&action=mis_clientes";
            }else {
                $('#contenidos').load(liga, function() {
                    $('#titulo').text(titulo);
                    $(".tabs").tabs();
                    $(".button").button();
                    if (!checando_sesion) {/*Corremos un timer para verificar que la sesion siga activada*/
                        setInterval(function() {
                            checando_sesion = true;
                            $('#loading_text').load("verificaSession.php", function(data) {
                                if (data.toString().indexOf("false") !== -1) {/*En caso de que la sesion siga existiendo*/
                                    window.location = "index.php?session=finished";
                                }
                            });
                        }, 300000);/*Cada 5 minutos revisa que la sesion no haya caducado*/
                    }
                    finished();
                });
            }
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function cambiarContenidosContacto(liga, titulo) {
    loading("Cargando ...");
    $("#contenidos").empty();
    limpiarMensaje();
    $('#loading_text').load("../verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $('#contenidos').load(liga, function() {
                $('#titulo').text(titulo);
                $(".tabs").tabs();
                $(".button").button();
                finished();
            });
            
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function cambiarContenidosProv(liga, id, titulo) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $('#contenidos').load(liga, {"id": id}, function() {
                $('#titulo').text(titulo);
                $(".tabs").tabs();
                $(".button").button();
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function editarRegistro(liga, id) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $("#contenidos").load(liga, {"id": id}, function() {
                $(".button").button();
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function editarPys(liga, id) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $("#contenidosSyP").load(liga, {"id": id}, function() {
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function copiarPys(liga) {
    loading("Cargando ...");
    limpiarMensaje();
    $("#contenidosSyP").load(liga, {"id": $("#componenteCopiar").val(), "copiar": true}, function() {
        finished();
    });
}

function editarRegistroProv(liga, id, id2) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $("#contenidos").load(liga, {"id": id, "id2": id2}, function() {
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function editarComponentesAlmacen(liga, id, id2, id3) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $("#contenidos").load(liga, {"id": id, "id2": id2, "id3": id3}, function() {
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}
function loading(mensaje) {
    $("#cargando").show();
    $("#loading_text").text(mensaje);
}

function finished() {
    $("#loading_text").text("");
    $("#cargando").hide();
}

function cargarProveedor() {
    $("#proveedor").load("WEB-INF/Controllers/obtenerDatosDependientes.php", {"id": $("#cliente").val(), "tipo": "proveedor"});
}

function cargarArea() {
    $("#area_almacen").load("WEB-INF/Controllers/obtenerDatosDependientes.php", {"id": $("#almacen").val(), "tipo": "area"});
    $('#datos_ocultos').load('WEB-INF/Controllers/obtenerDatosDependientes.php', {"id": $("#almacen").val(), "tipo": "ubicacion_almacen"}, function() {
        $('#ubicacion').val($('#datos_ocultos').text());
    });
}

function cargarRecurso(tipo_recurso, recurso) {
    $("#" + recurso).load("WEB-INF/Controllers/obtenerDatosDependientes.php", {"id": $("#" + tipo_recurso).val(), "tipo": "recurso"});
}

function mostrarRechazado() {
    if ($('#rechazado').val() != null && $('#rechazado').is(':checked')) {
        $("#table_rechazado").show();
    } else {
        $("#table_rechazado").hide();
    }
}

function readURL(input, preview) {
    if (input.files && input.files[0]) {
        //$('#preview_vacio').hide();
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#' + preview)
                    .attr('src', e.target.result)
                    .width(180)
                    .height(150);
        };
        //$('#preview').show();
        reader.readAsDataURL(input.files[0]);
    }
}

function cargarPyS(pagina) {
    if (pagina != "") {
        loading("Cargando ...");
        $('#contenidos').load(pagina, function() {
            finished();
        });
    } else {
        $('#contenidosSyP').empty();
    }
}

/**
 * Cambia los div de contenidos en la pantalla de validaciones
 * @param {type} div nombre del div a cambiar
 * @param {type} pagina pagina que se va a cargar
 * @param {type} idTicket id del ticket
 * @param {type} idComponente id del equipo, cliente, etc
 * @param {type} nuevo si se pidio un nuevo componente
 * @returns {undefined} Nada
 */
function cambiarContenidoValidaciones(div, pagina, idTicket, idComponente, nuevo) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#' + div).load(pagina, {"id": idComponente, "idTicket": idTicket, "nuevo": nuevo}, function() {
        finished();
    });
}

function limpiarContenido(){
    $("#contenidos").empty();
    $(":ui-dialog").dialog("close");
}

function cargarArrendamiento(pagina) {
    if (pagina !== "") {
        loading("Cargando ...");
        limpiarMensaje();
        $('#arrendamientos').load(pagina, function() {
            finished();
        });
    } else {
        $('#arrendamientos').empty();
    }
}

function cambiarContenidosArrend(liga, id, titulo) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            $('#arrendamientos').load(liga, {"id2": id}, function() {
                $('#titulo').text(titulo);
                $(".tabs").tabs();
                $(".button").button();
                finished();
            });
        } else {
            window.location = "index.php?session=finished";
        }
    });
}
function eliminarRegistroArrendamiento(controlador, id, lista) {
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        $('#mensajes').load(controlador, function() {
            $('#arrendamientos').load(lista, {"id": id});
        });
    }
}

function editarRegistroArrendamiento(liga, id, id2) {
    loading("Cargando ...");
    limpiarMensaje();
    $("#arrendamientos").load(liga, {"id": id, "id2": id2}, function() {
        finished();
    });
}

function cargarDependencia(div, liga, id, checkbox, idTicket) {
    if ($("#" + checkbox).is(":checked") || checkbox == null) {
        loading("Cargando ...");
        limpiarMensaje();
        if ($("#filtro_localidad").length && $("#filtro_localidad").is(":checked")) {
            $("#" + div).load(liga, {"id": id, "idTicket": idTicket, "filtro": true, "cc": $("#clave_localidad1").val()}, function() {
                finished();
            });
        } else {
            $("#" + div).load(liga, {"id": id, "idTicket": idTicket}, function() {
                finished();
            });
        }
    } else {
        $("#" + div).empty();
    }
}

function limpiarDependencia(div) {
    $("#" + div).empty();
}


function editarRegistroEquipo(pagina, id){
    loading("Cargando ...");
    limpiarMensaje();
    $('#loading_text').load("verificaSession.php", function(data) {
        if (data.toString().indexOf("false") === -1) {/*En caso de que la sesion siga existiendo*/
            if ($("#copiadocompo").val() === '1') {
                $.post("WEB-INF/Controllers/Controller_CopiadoEquipo.php", {id: $("#copiadoid").val(), noparte: $("#partes").val()}).done(function(data) {
                    $("#copiadocompo").val("0");
                    $("#contenidos").load(pagina, {"id": id}, function() {
                        listaPartesEq(id);
                        listaCompatiblesEquipo(id);
                        listaComponentesEquipo(id);
                        listaSimilaresEquipo(id);
                        finished();
                    });
                });
            } else {
                $("#contenidos").load(pagina, {"id": id}, function() {
                    listaPartesEq(id);
                    listaCompatiblesEquipo(id);
                    listaComponentesEquipo(id);
                    listaSimilaresEquipo(id);
                    finished();
                });
            }
        } else {
            window.location = "index.php?session=finished";
        }
    });
}

function listaPartesEq(id){
    var pagina2 = "admin/lista_partesEquipo.php";
    $("#editEquipo").show();
    $("#partesEquipo").load(pagina2, {"idEquipo": id}, function() {
        finished();
    });
}

function listaCompatiblesEquipo(id){
    var pagina2 = "admin/lista_componentesCompatiblesEq.php";
    $("#editEquipo").show();
    $("#ComponentesCompatibles").load(pagina2, {"idEquipo": id}, function() {
        finished();
    });
}

function listaSimilaresEquipo(id){
    var pagina2 = "admin/lista_SimilaresEquipos.php";
    $("#editEquipo").show();
    $("#EquiposSimiliares").load(pagina2, {"idEquipo": id}, function() {
        finished();
    });
}

function listaComponentesEquipo(id){
    var pagina2 = "admin/lista_componetesEquipo.php";
    $("#editEquipo").show();
    $("#ComponentesEquipo").load(pagina2, {"idEquipo": id}, function() {
        finished();
    });
}

function editarRegistroComponentes(pagina, id, tipo) {
    limpiarMensaje();
    loading("Cargando ...");
    if ($("#copiadocompo").val() === '1') {
        $.post("WEB-INF/Controllers/Controller_CopiadoCompo.php", {id: $("#copiadoid").val(), noparte: $("#parte").val()}).done(function(data) {
            $("#copiadocompo").val("0");
            $("#contenidos").load(pagina, {"id": id}, function() {
                $("#editComponentes").show();
                formaCompnentes(id);
                componentesNecesarios(id);
                partesDelCompnentes(id);
                //if (tipo == 2){
                equipoCompatible(id);
                //}
                finished();
            });
        });

    } else {
        $("#contenidos").load(pagina, {"id": id}, function() {
            $("#editComponentes").show();
            formaCompnentes(id);
            componentesNecesarios(id);
            partesDelCompnentes(id);
            //if (tipo == 2){
            equipoCompatible(id);
            //}
            finished();
        });
    }

}

function equipoCompatible(id) {
    var pagina2 = "admin/lista_equipoCompatible.php";

    $("#equipoCompatible").load(pagina2, {"idEquipo": id}, function() {
        //finished();
    });
}

function formaCompnentes(id)
{
    var pagina2 = "admin/lista_formoComponentes.php";
    $("#formatoComponentes").load(pagina2, {"idEquipo": id}, function() {
        //finished();
    });
}
function componentesNecesarios(id)
{
    var pagina2 = "admin/lista_componenteNecesariosC.php";
    $("#ComponentesNecesarios").load(pagina2, {"idEquipo": id}, function() {
        //finished();
    });
}
function partesDelCompnentes(id)
{
    var pagina2 = "admin/lista_partesDelComponente.php";
    $("#partesDelComponente").load(pagina2, {"idEquipo": id}, function() {
        //finished();
    });
}
function editarRegis(pagina, id, id2, id3, div)
{
    loading("Cargando ...");
    limpiarMensaje();
    $("#" + div).load(pagina, {"id": id, "id2": id2, "id3": id3}, function() {
        finished();
    });
}

function copiarDatos(liga) {
    var id = $("#componenteCopiar").val();
    loading("Cargando ...");
    limpiarMensaje();
    $("#contenidos").load(liga, {"id": id, "copiar": true}, function() {
        finished();
    });

}

function cambiarEstatus(nota, idTicket, descripcion, pagina, controler, nombre) {
    var idEstatus = $("#" + nombre).val();
    loading("Cargando ...");
    $("#mensajes").load(controler, {"nota": nota, "idTicket": idTicket, "solucion": descripcion, "estatus": idEstatus}, function() {
        $("#contenidos").load(pagina, function() {
            finished();
        });
    });
}

function cambiarEstatusAtencion(ticket, diagnostico, nota, noParte, atendida, estatus, controler, atendido, div, divCantidad, solicitadas, usuario) {
    var cantidadAtendida = $("#" + atendida).val();
    var idEstatus = $("#" + estatus).val();
    var solicitada = $("#" + solicitadas).val();
    var continuar = true;
    //alert(usuario);
    if (idEstatus == "0") {
        $("#" + div).text("Seleccione una opcion");
        continuar = false;
        return false;
    } else {
        $("#" + div).text("");
    }

    if (Number(cantidadAtendida) > Number(solicitada)) {
        $("#" + divCantidad).text("La cantidad debe ser menor a la solicitada");
        continuar = false;
    } else {
        $("#" + divCantidad).text("");
    }

    if (cantidadAtendida == "0") {
        $("#" + divCantidad).text("La cantidad debe mayor a cero");
        continuar = false;
    }
    else {
        $("#" + divCantidad).text("");
    }

    if (continuar) {
        var pagina = "almacen/lista_refaccionesSolicitadas.php";
        loading("Cargando ...");
        $("#mensajes").load(controler, {"ticket": ticket, "diagnostico": diagnostico, "nota": nota, "noParte": noParte, "cantidadAtendida": cantidadAtendida, "idEstatus": idEstatus, "idAtencion": atendido, "usuario": usuario}, function() {
            $("#contenidos").load(pagina, function() {
                finished();
            });
        });
    }
}

function lanzarPopUp(titulo, page) {
    var $dialog = $('<div></div>').css({'z-index': "1000",'height': "650px", 'overflow': "auto", 'position': "relative", 'top': "10px"})
            .html('<iframe style="border: 0px; " src="' + page + '" width="100%" height="99%" scrolling="yes"></iframe>')
            .dialog({
        autoOpen: false,
        modal: true,
        height: 700,
        width: 950,
        title: titulo,
        position: 'bottom',
        'z-index': 1000
    });
    $dialog.dialog('open');
}

function lanzarPopUpVerExistencias(titulo, page) {
    var tipo = "";
    var modelo = "";
    if($("#tipo").length){
        tipo = $("#tipo").val();
    }
    if($("#modelo").length){
        modelo = $("#modelo").val();
    }
    
    if(tipo != "" && modelo != ""){
        page += "?tipo="+tipo+"&modelo="+modelo;
        var $dialog = $('<div></div>').css({'z-index': "1000",'height': "650px", 'overflow': "auto", 'position': "relative", 'top': "10px"})
            .html('<iframe style="border: 0px; " src="' + page + '" width="100%" height="99%" scrolling="yes"></iframe>')
            .dialog({
            autoOpen: false,
            modal: true,
            height: 700,
            width: 950,
            title: titulo,
            position: 'bottom',
            'z-index': 1000
        });
        $dialog.dialog('open');
    }else{
        alert("Es necesario elegir un tipo y un modelo antes de ver las existencias");
    }
}

function lanzarPopUpVerExistenciasToner(titulo, page, contador, tipoToner) {
    var tipo = "2";
    var modelo = "";
    if(parseInt(tipoToner) == 1){
        if($("#txtTonerNegro"+contador).length){
            modelo = $("#txtTonerNegro"+contador).val();
        }
    }else if(parseInt(tipoToner) == 2){
        if($("#txtTonerCian"+contador).length){
            modelo = $("#txtTonerCian"+contador).val();
        }
    }else if(parseInt(tipoToner) == 3){
        if($("#txtTonerMagenta"+contador).length){
            modelo = $("#txtTonerMagenta"+contador).val();
        }
    }else{
        if($("#txtTonerAmarillo"+contador).length){
            modelo = $("#txtTonerAmarillo"+contador).val();
        }
    }
    
    if(tipo != "" && modelo != ""){
        page += "?tipo="+tipo+"&modelo="+modelo;
        var $dialog = $('<div></div>').css({'z-index': "1000",'height': "650px", 'overflow': "auto", 'position': "relative", 'top': "10px"})
            .html('<iframe style="border: 0px; " src="' + page + '" width="100%" height="99%" scrolling="yes"></iframe>')
            .dialog({
            autoOpen: false,
            modal: true,
            height: 700,
            width: 950,
            title: titulo,
            position: 'bottom',
            'z-index': 1000
        });
        $dialog.dialog('open');
    }else{
        alert("Es necesario elegir el tóner antes de ver las existencias");
    }
}

function lanzarPopUpAjustable(titulo, page, width, height) {
    var $dialog = $('<div></div>').css({height: "650px", overflow: "auto", position: "relative", top: "10px"})
            .html('<iframe style="border: 0px; " src="' + page + '" width="100%" height="99%" scrolling="yes"></iframe>')
            .dialog({
        autoOpen: false,
        modal: true,
        height: height,
        width: width,
        title: titulo,
        position: 'bottom'
    });
    $dialog.dialog('open');
}

function insertarIncidencia(NoSerie, Fecha, FechaFin, Descripcion, status, cc, id_ticket) {
    var controlador = "../WEB-INF/Controllers/Controler_Incidencia.php";
    $("#contenidos_invisibles").load(controlador, {'NoSerie': NoSerie, 'Fecha': Fecha, 'FechaFin': FechaFin, 'Descripcion': Descripcion,
        'status': status, 'cc': cc, 'id_ticket': id_ticket}, function(data) {
        //alert(""+data);
    });
}

function lanzarHistoricoRefacciones(serie) {
    loading("Cargando ...");
    $("#mensajes").load("almacen/consulta_refacciones.php", {"serie": serie}, function() {
        $("#mensajes").dialog({
            resizable: false,
            height: 'auto',
            width: '1000px',
            modal: true,
            title: "Historico de refacciones del equipo " + serie,
            buttons: {
                "Cancelar": function() {
                    $(this).dialog("close");
                }
            }
        });
        finished();
    });
}

function eliminarRegistroPlantilla(controlador, idCampania, idTurno, lista) {
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        loading("Eliminando ... ");
        $('#mensajes').load(controlador, function () {
            $('#contenidos').load(lista, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function () {
                $(".button").button();
                finished();
            });
        });
    }
}

function abrirNuevaVentana(params){
    window.open(window.location.href.split('?')[0].split('#')[0]+params,'_blank');
}
