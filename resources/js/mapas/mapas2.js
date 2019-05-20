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

function intervalo() {
    if ($("#boton_aceptar").length && $("#PrioridadesT").length) {
        $("#boton_aceptar").click();
    } else {
        clearInterval(tid);
    }
}

$(document).ready(function () {
    initialize();
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
        var icono = "resources/images/Textpreview.png";

        if ($.inArray(tickets[i], tickets_seleccionados) >= 0) {
            icono = "resources/images/TextpreviewRed.png";
        }

        if (estatus != null && estatus[i] == 51) {//Check-in
            icono = "resources/images/TextpreviewBlue.png";
        } else if (estatus != null && estatus[i] == 16) {//Realizados exitosos
            icono = "resources/images/TextpreviewGreen.png";
        } else if (estatus != null && estatus[i] == 14) {//Realizado fallido
            icono = "resources/images/TextpreviewOrange.png";
        } else if (estatus != null) {
            icono = "resources/images/TextpreviewRed.png";
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

    var idPlantilla = "";
    var idArea = "";
    var plantillas = "";
    var areas = "";
    var fechas = "";
    var selcts = 0;

    $(oTable.fnGetNodes()).find(':checkbox').each(function () {
        $this = $(this);
        if ($this.prop('checked')) {
            var PlantillaCuadrante = $this.val();
            var pc = PlantillaCuadrante.split("_");
            var idPlantilla = pc[0];
            var idArea = pc[1];
            plantillas = idPlantilla;
            areas = idArea;
            //tickets += (idTicket + ",");
            //prioridades += ($("#prioridad_" + idTicket).val() + ",");
            //duraciones += ($("#tiempo_" + idTicket).val() + ",");
            //unidades += ($("#um_" + idTicket).val() + ",");
            fechas = $("#fecha_" + idPlantilla + "_" + idArea).val();
            selcts++;
        }
    });
    if (selcts > 1) {
        $("#error_tecnico").text("Selecciona SOLO UN  registro de la primer tabla para asociar");
        $("#asigna_tecnicos").show();
        finished();
    } else {
        if (plantillas !== "") {
//        tickets = quitarUltimoCaracter(tickets);
//        prioridades = quitarUltimoCaracter(prioridades);
//        duraciones = quitarUltimoCaracter(duraciones);
//        unidades = quitarUltimoCaracter(unidades);
            fechas = quitarUltimoCaracter(fechas);
            var myRadio = $('input[name=radio_tec]');
            var tecnico = myRadio.filter(':checked').val();
            //var tecnico = $("#radio_tec").val();
            if (tecnico == null || tecnico == "") {
                $("#error_tecnico").text("Selecciona al menos un técnico");
                $("#asigna_tecnicos").show();
                finished();
            } else {
                $("#mensajes").load("WEB-INF/Controllers/Controler_AsignaTecnicoC.php",
                        {"tecnico": tecnico, "idPlantilla": plantillas, "idArea": areas, "FechaHora": fechas},
                function (data) {
                    $("#boton_aceptar").click();
                    //$("#asigna_tecnicos").show();
                    finished();
                });
            }
        } else {
            $("#error_tecnico").text("Selecciona un registro para asociar");
            $("#asigna_tecnicos").show();
            finished();
        }
    }
}

function quitarUltimoCaracter(string) {
    return string.substring(0, string.length - 1);
}

function recargarListaTicketTecnico(liga, ticket, checkbox, cliente, color, estado, checkmoroso, checkcancelado, mostrarGrid,
        NoSerie, FechaInicio, FechaFin, area, tipoReporte, NoGuia, tecnico, prioridad, plantilla) {
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

    $("#contenidos").load(liga + "?page=" + currentPage + "&filter=" + filtro, {"cerrado": cerrado, "cliente": clientes, "color": $("#" + color).val(),
        "estado": $("#" + estado).val(), "moroso": moroso, "cancelado": cancelado, "mostrar": mostrarGrid, "NoSerie": $("#" + NoSerie).val(),
        "FechaInicio": $("#" + FechaInicio).val(), "FechaFin": $("#" + FechaFin).val(), "area": $("#" + area).val(),
        "tipoReporte": $("#" + tipoReporte).val(), "NoGuia": $("#" + NoGuia).val(), "tipo_busqueda_estado": tipo_busqueda_estado, "idTicket": $("#" + ticket).val(),
        "tecnico": $("#" + tecnico).val(), "prioridad": $("#" + prioridad).val(), "idPlantilla": $("#" + plantilla).val()},
    function () {
        $(".button").button();
        if ($("#PrioridadesT").length) {//Si etsamos en monitoreo
            // set interval
            //alert("Programando ..");
            tid = setInterval(intervalo, 300000);
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

    loading("Enviando mensaje");
    $("#" + boton).hide();

    $("#mensajes").load("WEB-INF/Controllers/Controler_Mensaje.php", {'idTicket': $("#" + ticket).val(), 'diagnostico': $("#" + mensaje).val()},
    function () {
        $("#boton_aceptar").click();
        finished();
    });
}