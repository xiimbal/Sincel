var directionsDisplay;
var directionsService = new google.maps.DirectionsService();
var map;
var bounds = new google.maps.LatLngBounds();
var caomi = new google.maps.LatLng(19.5123, -99.033096);  /*latitud,longitud*/
var tickets_seleccionados = [];
var zoom = 8;
var center = caomi;
var ruta = new Array();
var colorTecnico = new Array();
var lineas;
var tid;
var inicio_recarga = false;

function intervalo() {
    if ($("#boton_aceptar").length && $("#PrioridadesT").length) {
        $("#boton_aceptar").click();
    } else {
        clearInterval(tid);
    }
}

$(document).ready(function () {
    initialize();
    var paginaExito = "";
    var form = "#formEnviarMensaje";
    if ($("#monitorServicios").val()) {
        paginaExito = "mesa/monitorP.php";
        $("#map-canvas").hide();
        $("#ServiciosSi").hide();
    } else {
        paginaExito = "mesa/monitoreo.php";
    }
    var controlador = "WEB-INF/Controllers/Controler_Mensaje.php";
    clearInterval(tid);
    
    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Seleccione un elemento de la lista");

    jQuery.validator.addMethod('selecTic', function (value, element, param) {
        var idTicket = 0;
        if($("#tabla_serv").length){
            /*var oTable = $('#tabla_serv').dataTable();
            $(oTable.fnGetNodes()).find(':checkbox').each(function () {
                $this = $(this);
                if ($this.prop('checked')) {
                    idTicket++;
                }
            });*/
            for(var i=0;;i++){
                if($("#kservicio_"+i).length){
                    var valor = $("#kservicio_"+i).val();
                    if( $("#servicio_"+valor).prop( "checked" )){
                        idTicket++;
                    }
                }else{
                    break;
                }
            }
        }
        if (idTicket > 0 || $("#ticket_mensaje2").val() != 0)
            return true;
        else
            return false;
    }, " * Seleccione por lo menos un servicio");
    
    jQuery.validator.addMethod('integer', function (value, element, param) {
        if ($("#estatusN").val() == "274") {
            if ((value == parseInt(value, 10)))
                return true;
            else
                return false;
        } else
            return true
    }, 'Ingresa solo numeros');
    
    jQuery.validator.addMethod('integer1', function (value, element, param) {
        if ($("#estatusN").val() == "275") {
            if ((value == parseFloat(value, 10)))
                return true;
            else
                return false;
        } else
            return true
    }, 'Ingresa solo numeros');
    
    jQuery.validator.addMethod('integer2', function (value, element, param) {
        if ($("#estatusN").val() == "276") {
            if ((value == parseInt(value, 10)))
                return true;
            else
                return false;
        } else
            return true
    }, 'Ingresa solo numeros');


    $.validator.addMethod("cantidadKm", function (value, element) {
        if ($("#estatusN").val() == "275") {
            if ($("#km").val() != '')
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, "* Ingrese la cantidad");

    $.validator.addMethod("monto", function (value, element) {
        if ($("#estatusN").val() == "274") {
            if ($("#monto").val() != '')
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, "* Ingrese la cantidad");

    $.validator.addMethod("SlcTipoV", function (value, element) {
        if ($("#estatusN").val() == "274") {
            if ($("#tipo_viatico").val() != '0')
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, "* Seleccione un tipo de viático");

    $.validator.addMethod("noBoleto", function (value, element) {
        if ($("#estatusN").val() == "277") {
            if ($("#no_boleto").val() != '')
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, "* Ingrese No. Boleto");

    $.validator.addMethod("tiempoEspera", function (value, element) {
        if ($("#estatusN").val() == "276") {
            if ($("#tiempo_esperaR").val() != '' || $("#tiempo_esperaM").val() != '')
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, "* Ingrese la cantidad en minutos");
    
    if ($("#monitorServicios").val()) {
        
        $(form).validate({
            //errorClass: "my-error-class",
            rules: {
                ticket_mensaje2: {selecTic: true},
                km: {cantidadKm: true, integer1: true},
                monto: {monto: true, integer: true},
                tipo_viatico: {SlcTipoV: true},
                no_boleto: {noBoleto: true},
                tiempo_esperaR: {tiempoEspera: true, integer2: true},
                tiempo_esperaM: {tiempoEspera: true, integer2: true},
                mensaje_enviar2: {required: true, minlength: 1}
            },
            messages: {
                ticket_mensaje2: {required: " * Selecciona el ticket para enviar el mensaje"},
                mensaje_enviar2: {required: " * Escribe un mensaje", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
            }
        });
    } else {        
        $(form).validate({
            //errorClass: "my-error-class",
            rules: {
                ticket_mensaje2: {selectcheck: true},
                km: {cantidadKm: true, integer1: true},
                monto: {monto: true, integer: true},
                tipo_viatico: {SlcTipoV: true},
                no_boleto: {noBoleto: true},
                tiempo_esperaR: {tiempoEspera: true, integer2: true},
                tiempo_esperaM: {tiempoEspera: true, integer2: true},
                mensaje_enviar2: {required: true, minlength: 1}
            },
            messages: {
                ticket_mensaje2: {required: " * Selecciona el ticket para enviar el mensaje"},
                mensaje_enviar2: {required: " * Escribe un mensaje", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
            }
        });
    }
//    if ($("#otraNota").val()) {
    //var form = document.getElementById("formEnviarMensaje");

    $(form).submit(function (event) {        
        if ($(form).valid()) {            
            loading("Enviando mensaje");
            var ticket = $("#busqueda_ticket").val();
            var color = $("#ticket_color").val();
            var estado = $("#estado_ticket").val();
            var area = $("#area").val();
            var tecnico = $("#tecnico").val();
            var prioridad = $("#prioridad").val();

            var filtro = currentFilter;
            if ((filtro == "" && $("#filter").val() != "") || filtro == null) {
                filtro = $("#filter").val();
            }
            if (filtro == null) {
                filtro = "";
            }
            filtro = filtro.replace(/ /g, "_XX__XX_");            
            /*Procesamos los clientes que vengan concatenados*/
            var res = $("#cliente_ticket").val();
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
            var ticketM = "";
            if ($("#ticket_mensaje2").val() != "") {
                ticketM = $("#ticket_mensaje2").val();
            }

            var tickets = "";
            if($('#tabla_serv').length){
                /*var oTable = $('#tabla_serv').dataTable();
                $(oTable.fnGetNodes()).find(':checkbox').each(function () {
                    $this = $(this);
                    if ($this.prop('checked')) {
                        var idTicket = $this.val();
                        tickets += (idTicket + ",");
                    }
                });*/
                for(var i=0;;i++){
                    if($("#kservicio_"+i).length){
                        var valor = $("#kservicio_"+i).val();
                        if( $("#servicio_"+valor).prop( "checked" )){
                            tickets += (valor + ",");
                        }
                    }else{
                        break;
                    }
                }
            }            
            if (tickets !== "") {
                tickets = quitarUltimoCaracter(tickets);
            }

            //$("#enviar_mensaje").hide();
            var formData = new FormData($('form')[0]);
            $.ajax({
                url: controlador + "?IdTickets=" + tickets, //+"?totalrefacciones="+tamanoTabla+"&boton="+b,  //Server script to process data
                type: 'POST',
                xhr: function () {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    /*if(myXhr.upload){ // Check if upload property exists
                     myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                     }*/
                    return myXhr;
                },
                success: function (data) {

                    $('#mensajes').html(data);
                    if (data.toString().indexOf("Error:") === -1) {
                        $('#contenidos').load(paginaExito + "?page=" + currentPage + "&filter=" + filtro, {"cliente": clientes, "color": color,
                            "estado": estado, "area": area, "tipo_busqueda_estado": tipo_busqueda_estado, "idTicket": ticket,
                            "tecnico": tecnico, "prioridad": prioridad, "ticketMensaje": ticketM}, function () {
                            $(".button").button();
                            if ($("#PrioridadesT").length) {//Si etsamos en monitoreo
                                // set interval
                                if(!inicio_recarga){
                                    tid = setInterval(intervalo, 300000);
                                    inicio_recarga = true;
                                }
                            }
                            finished();
                        });
                    } else {
                        $(".button").button();
                        if ($("#PrioridadesT").length) {//Si etsamos en monitoreo
                            // set interval
                            if(!inicio_recarga){
                                tid = setInterval(intervalo, 300000);
                                inicio_recarga = true;
                            }
                        }
                        finished();
                    }
                },
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            /* stop form from submitting normally */
            event.preventDefault();
        }
    });
});

function initialize() {
    directionsDisplay = new google.maps.DirectionsRenderer();
    //alert(zoom+" - "+center)
    var mapOptions = {
        zoom: zoom,
        center: center,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
    directionsDisplay.setMap(map);

    if ($("#LatitudesTickets").length && $("#LatitudesTickets").val() != "") {
        //Datos tickets
        latitudes = $("#LatitudesTickets").val().split('/');
        longitudes = $("#LongitudesTickets").val().split('/');
        tickets = $("#NumeroTicket").val().split('/');
        //Datos tecnico
        if ($("#inactivo30").length) {
            inactivos = $("#inactivo30").val().split('/');
        } else {
            inactivos = null;
        }
        latitudesT = $("#LatitudesTecnico").val().split('/');
        longitudesT = $("#LongitudesTecnico").val().split('/');
        fechaT = $("#FechaTecnico").val().split('/');
        usuarioT = $("#UsuarioTecnico").val().split('/');
        latitudesT = $("#LatitudesTecnico").val().split('/');
        porcentajes = $("#PorcentajeBateria").val().split('/');
        if ($("#PrioridadesT").length) {
            prioridades = $("#PrioridadesT").val().split('/');
            tiempos = $("#TiempoT").val().split('/');
            fechas = $("#FechaInicioT").val().split('/');
            estatus = $("#EstatusTicket").val().split('/');
            latitudesDia = $("#LatitudesTecnicoDia").val().split('/');
            longitudesDia = $("#LongitudesTecnicoDia").val().split('/');
        } else {
            prioridades = null;
            tiempos = null;
            fechas = null;
            estatus = null;
            latitudesDia = null;
            longitudesDia = null;
        }

        getMarkers(latitudes, longitudes, tickets, latitudesT, longitudesT, fechaT, usuarioT, prioridades, tiempos, fechas, estatus, porcentajes,
                latitudesDia, longitudesDia, inactivos);
    }

    //map.fitBounds(bounds);
}

function getMarkers(latitudes, longitudes, tickets, latT, lonT, fechaT, userT, prioridades, tiempos, fechas, estatus, porcentajes, latitudesDia, longitudesDia, inactivos) {
    var bounds = new google.maps.LatLngBounds();
    for (var i = 1; i < latitudes.length; i++) {
        var location = new google.maps.LatLng(latitudes[i], longitudes[i]);
        var icono = $("#valor_20").val();

        if ($.inArray(tickets[i], tickets_seleccionados) >= 0) {
            icono = $("#valor_25").val();
        }

        if (estatus != null && estatus[i] == 51) {//Check-in
            icono = $("#valor_22").val();
        } else if (estatus != null && estatus[i] == 16) {//Realizados exitosos
            icono = $("#valor_23").val();
        } else if (estatus != null && estatus[i] == 14) {//Realizado fallido
            icono = $("#valor_24").val();
        } else if (estatus != null) {
            icono = $("#valor_21").val();
        }

        var nombre_ticket = "Ticket";
        if ($("#nombre_ticket").length) {
            nombre_ticket = $("#nombre_ticket").val();
        }
        var texto = nombre_ticket + ' <b>#' + tickets[i] + "</b>";
        var texto_visible = texto;

        if (prioridades != null && prioridades[i] != "") {
            texto += ("<br/>Prioridad: " + prioridades[i]);
        }

        if (tiempos != null && tiempos[i] != "") {
            texto += ("<br/>Duración Estimada: " + tiempos[i]);
        }

        if (fechas != null && fechas[i] != "") {
            if (estatus[i] === "51") {
                texto += ("<br/>Check-in: " + fechas[i]);
                texto_visible += ("<br/>Check-in: " + fechas[i]);
            } else if (estatus[i] === "16") {
                texto += ("<br/>Check-out exitoso: " + fechas[i]);
                texto_visible += ("<br/>Check-out exitoso: " + fechas[i]);
            } else if (estatus[i] === "14") {
                texto += ("<br/>Check-out no exitoso: " + fechas[i]);
                texto_visible += ("<br/>Check-out no exitoso: " + fechas[i]);
            } else {
                texto += ("<br/>Fecha Programada: " + fechas[i]);
                texto_visible += ("<br/>Fecha Programada: " + fechas[i]);
            }
        }

        var marker = new google.maps.Marker({
            position: location,
            map: map,
            title: texto,
            icon: icono,
            draggable: true
        });
        bounds.extend(location);

        var label = new Label({map: map}, texto_visible);
        label.bindTo('position', marker, 'position');
        label.bindTo('text', marker, 'position');
    }

    /*lineas = new google.maps.Polyline({
     path: ruta,
     geodesic: true,
     map: map,
     strokeColor: '#FF0000',
     strokeWeight: 7,        
     clickable: true
     });
     lineas.setMap(map);*/

    colorTecnico = new Array();
    for (var i = 1; i < latT.length; i++) {
        ruta = new Array();
        var location = new google.maps.LatLng(latT[i], lonT[i]);
        var icono = "";
        if (inactivos != null) {
            if (inactivos[i] === "false") {
                icono = "resources/images/user.png";
            } else {
                icono = "resources/images/userGray.png";
            }
        } else {
            icono = "resources/images/user.png";
        }

        var texto = userT[i] + ', ' + fechaT[i];
        var texto_visible = texto;

        if (porcentajes != null && porcentajes[i] != "") {
            texto_visible += ("<br/>Porcentaje bateria: " + porcentajes[i]);
        }

        if (latitudesDia != null && longitudesDia != null && latitudesDia[i] != null && latitudesDia[i] != "" && longitudesDia[i] != null && longitudesDia[i] != "") {
            var lats = latitudesDia[i].split(',');
            var lons = longitudesDia[i].split(',');
            for (var j = 0; j < lats.length; j++) {
                var location2 = new google.maps.LatLng(lats[j], lons[j]);
                ruta[j] = location2;
            }
            colorTecnico[i - 1] = '#' + Math.floor(Math.random() * 16777215).toString(16);
        }

        var marker = new google.maps.Marker({
            position: location,
            map: map,
            title: texto_visible,
            icon: icono,
            draggable: true
        });
        bounds.extend(location);
        var label = new Label({map: map}, texto);
        label.bindTo('position', marker, 'position');
        label.bindTo('text', marker, 'position');

        lineas = new google.maps.Polyline({
            path: ruta,
            geodesic: true,
            map: map,
            strokeColor: colorTecnico[i - 1],
            strokeWeight: 7,
            clickable: true
        });
        lineas.setMap(map);
    }

    //map.setZoom(10);
    //now fit the map to the newly inclusive bounds
    if (zoom <= 8) {
        map.fitBounds(bounds);
    }
}

function seleccionarTicket() {
    //alert(map.getZoom());
    zoom = map.getZoom();
    center = map.getCenter();
    llenarTicketsSeleccionados();
    initialize();
}

function llenarTicketsSeleccionados() {
    tickets_seleccionados = [];
    /*$(oTable.fnGetNodes()).find(':checkbox').each(function () {
        $this = $(this);
        if ($this.prop('checked')) {
            tickets_seleccionados.push($this.val());
        }
    });*/
    for(var i=0;;i++){
        if($("#kservicio_"+i).length){
            var valor = $("#kservicio_"+i).val();
            if( $("#servicio_"+valor).prop( "checked" )){
                tickets_seleccionados.push(valor);
            }
        }else{
            break;
        }
    }
}

function relacionarTecnicoGeneral() {
    $("#asigna_tecnicos").hide();
    loading("Asignando registros");
    $("#error_tecnico").text("");

    var tickets = "";
    var prioridades = "";
    var duraciones = "";
    var unidades = "";
    var fechas = "";

    $(oTable.fnGetNodes()).find(':checkbox').each(function () {
        $this = $(this);
        if ($this.prop('checked')) {
            var idTicket = $this.val();
            tickets += (idTicket + ",");
            prioridades += ($("#prioridad_" + idTicket).val() + ",");
            duraciones += ($("#tiempo_" + idTicket).val() + ",");
            unidades += ($("#um_" + idTicket).val() + ",");
            fechas += ($("#fecha_" + idTicket).val() + ",");
        }
    });

    if (tickets !== "") {
        tickets = quitarUltimoCaracter(tickets);
        prioridades = quitarUltimoCaracter(prioridades);
        duraciones = quitarUltimoCaracter(duraciones);
        unidades = quitarUltimoCaracter(unidades);
        fechas = quitarUltimoCaracter(fechas);
        var myRadio = $('input[name=radio_tec]');
        var tecnico = myRadio.filter(':checked').val();
        //var tecnico = $("#radio_tec").val();
        if (tecnico == null || tecnico == "") {
            $("#error_tecnico").text("Selecciona al menos un técnico");
            $("#asigna_tecnicos").show();
            finished();
        } else {
            $("#mensajes").load("WEB-INF/Controllers/Controler_AsignaTecnico.php",
                    {"tecnico": tecnico, "idTicket": tickets, "IdPrioridad": prioridades, "Duracion": duraciones, "IdUnidadDuracion": unidades, "FechaHora": fechas},
                    function (data) {
                        $("#boton_aceptar").click();
                        //$("#asigna_tecnicos").show();
                        finished();
                    });
        }
    } else {
        $("#error_tecnico").text("Selecciona al menos un registro para asociar");
        $("#asigna_tecnicos").show();
        finished();
    }
}

function quitarUltimoCaracter(string) {
    return string.substring(0, string.length - 1);
}

function recargarListaTicketTecnico(liga, ticket, checkbox, cliente, color, estado, checkmoroso, checkcancelado, mostrarGrid,
        NoSerie, FechaInicio, FechaFin, area, tipoReporte, NoGuia, tecnico, prioridad) {
    if ($("#monitorServicios").val()) {
        limpiarMensaje();
    }
    if ($("#PrioridadesT").length) {//Si etsamos en monitoreo
        clearInterval(tid);
    }
    loading("Actualizando ...");
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

    var ticketM = "";
    if ($("#ticket_mensaje2").val() != "") {
        ticketM = $("#ticket_mensaje2").val();
    }

    $("#contenidos").load(liga + "?page=" + currentPage + "&filter=" + filtro, {"cerrado": cerrado, "cliente": clientes, "color": $("#" + color).val(),
        "estado": $("#" + estado).val(), "moroso": moroso, "cancelado": cancelado, "mostrar": mostrarGrid, "NoSerie": $("#" + NoSerie).val(),
        "FechaInicio": $("#" + FechaInicio).val(), "FechaFin": $("#" + FechaFin).val(), "area": $("#" + area).val(),
        "tipoReporte": $("#" + tipoReporte).val(), "NoGuia": $("#" + NoGuia).val(), "tipo_busqueda_estado": tipo_busqueda_estado, "idTicket": $("#" + ticket).val(),
        "tecnico": $("#" + tecnico).val(), "prioridad": $("#" + prioridad).val(), "ticketMensaje": ticketM},
            function () {
                $(".button").button();
                if ($("#PrioridadesT").length) {//Si etsamos en monitoreo
                    // set interval
                    if(!inicio_recarga){
                        tid = setInterval(intervalo, 300000);
                        inicio_recarga = true;
                    }
                }
                finished();
            });

}

function enviarMensaje(ticket, mensaje, error_ticket, boton, error_mensaje) {
    $("#" + error_ticket).text("");
    $("#" + error_mensaje).text("");
    if ($("#" + ticket).val() == "") {
        $("#" + error_ticket).text("Selecciona el ticket para enviar el mensaje");
        return;
    }

    if ($("#" + mensaje).val() == "") {
        $("#" + error_mensaje).text("Escribe un mensaje");
        return;
    }

    var estatus = "";
    if ($("#otraNota").val()) {        
        //var form = "#formEnviarMensaje";
        var form = document.getElementById("formEnviarMensaje");
        loading("Enviando mensaje");
        $("#" + boton).hide();
        var paginaExito = "mesa/monitoreo.php";
        var controlador = "WEB-INF/Controllers/Controler_Mensaje.php";
        estatus = $("#estatusN").val();
        $(form).submit(function (event) {            
            var formData = new FormData($('form')[0]);
            $.ajax({
                url: controlador, //+"?totalrefacciones="+tamanoTabla+"&boton="+b,  //Server script to process data
                type: 'POST',
                xhr: function () {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    /*if(myXhr.upload){ // Check if upload property exists
                     myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                     }*/
                    return myXhr;
                },
                success: function (data) {

                    $('#mensajes').html(data);
                    if (data.toString().indexOf("Error:") === -1) {
                        $('#contenidos').load(paginaExito, function () {
                            $("#boton_aceptar").click();
                            finished();
                        });
                    } else {
                        $("#boton_aceptar").click();
                        finished();
                    }
                },
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            /* stop form from submitting normally */
            event.preventDefault();
        });
    } else {

        loading("Enviando mensaje");
        $("#" + boton).hide();

        $("#mensajes").load("WEB-INF/Controllers/Controler_Mensaje.php", {'idTicket': $("#" + ticket).val(), 'diagnostico': $("#" + mensaje).val(), 'estatusNota': estatus},
                function () {
                    $("#boton_aceptar").click();
                    finished();
                });
    }
}

function VerMapa() {
    $("#map-canvas").show();
    $("#lista_servicios").hide();
    $("#ServiciosSi").show();
    $("#mapaSi").hide();
}

function VerLista() {
    $("#map-canvas").hide();
    $("#lista_servicios").show();
    $("#ServiciosSi").hide();
    $("#mapaSi").show();
}