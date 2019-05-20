var contadorEscalas = "";
$(document).ready(function () {
    $('#sin_origen').hide();
    $('#sin_detino').hide();
    var form = "#formEspecial";
    var paginaExito = "viajes/lista_especial.php";
    var controlador = "WEB-INF/Controllers/Viajes/Controller_AutorizarEspecial.php";
    $('.boton').button().css('margin-top', '20px');
    $('#fecha').datepicker({dateFormat: 'yy-mm-dd'});
    $('#hora').mask("99:99");

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $(form).validate({
        rules: {
//            txtOrigen: {required: true, maxlength: 100, minlength: 2},
//            txtDestino: {required: true, maxlength: 100, minlength: 2},
            area: {selectcheck: true},
            slcCampania: {selectcheck: true},
            slcTurno: {selectcheck: true},
            slcEmpleado: {selectcheck: true}
        },
        messages: {
//            txtOrigen: {required: " * Ingrese Origen", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
//            txtDestino: {required: " * Ingrese Destino", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    /*Prevent form*/

    $(form).submit(function (event) {
        if ($(form).valid()) {
//            if ($("#txtOrigen").val() == "" && $("#origenR").val() == 0) {
//                $('#sin_origen').show();
//                finished();
//                return;
//            } else {
//                if ($("#txtDestino").val() == "" && $("#destinoR").val() == 0) {
//                    $('#sin_detino').show();
//                    finished();
//                    return;
//                } else {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function (data) {
                        var idCampania = $("#slcCampania").val();
                        var idTurno = $("#slcTurno").val();
                        $('#mensajes').html(data);
                        if (data.toString().indexOf("Error:") === -1) {
                            $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function () {
                                $(".button").button();
                                finished();
                            });
                        } else {
                            finished();
                        }
                    });
//                }
//            }
        }
    });

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        noneSelectedText: "Selecciona localidad",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();
    finished();
    initialize();
    return;
});

function guardarActualizar() {
    var form = "#formEspecial";
    var paginaExito = "viajes/lista_especial.php";
    var controlador = "WEB-INF/Controllers/Viajes/Controller_AutorizarEspecial.php";
    if ($(form).valid()) {
        if ($("#txtOrigen").val() == "" && $("#origenR").val() == 0) {
            $('#sin_origen').show();
            finished();
            return;
        } else {
            $('#sin_origen').hide();
            if ($("#txtDestino").val() == "" && $("#destinoR").val() == 0) {
                $('#sin_detino').show();
                finished();
                return;
            } else {
                $('#sin_origen').hide();
                loading("Cargando ...");
                /* stop form from submitting normally */
                //event.preventDefault();
                /*Serialize and post the form*/
                $.post(controlador, {form: $(form).serialize(), auto: 1})
                        .done(function (data) {
                            
                            var idCampania = $("#slcCampania").val();
                            var idTurno = $("#slcTurno").val();
                            $('#mensajes').html(data);
                            if (data.toString().indexOf("Error:") === -1) {
                                $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function () {
                                    $(".button").button();
                                    finished();
                                });
                            } else {
                                finished();
                            }
                        });
            }
        }
    }
}


function cancelar() {
    loading("Cargando ...");
    var paginaExito = "viajes/lista_especial.php";
    var idCampania = $("#slcCampania").val();
    var idTurno = $("#slcTurno").val();
    $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function initialize() {
    var lat = 19.4326;
    var log = -99.1339;
    var zm = 10;
    if ($("#Latitud_or").val() != "")
        lat = $("#Latitud_or").val();
    if ($("#Longitud_or").val() != "") {
        log = $("#Longitud_or").val();
        zm = 10;
    }

    var lat1 = 19.433085646568;
    var log1 = -99.14900620117186;
    var zm1 = 10;
    if ($("#Latitud_des0").val() != "")
        lat1 = $("#Latitud_des0").val();
    if ($("#Longitud_des0").val() != "") {
        log1 = $("#Longitud_des0").val();
        zm1 = 13;
    }

    var ruta = new Array();
    var latlng = new google.maps.LatLng(lat, log);  /*latitud,longitud*/
    var latlng1 = new google.maps.LatLng(lat1, log1);
    ruta[0] = latlng;

    var myOptions = {
        zoom: zm, /*Nivel de zoom*/
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var myOptions1 = {
        zoom: zm1, /*Nivel de zoom*/
        center: latlng1,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    //map = new google.maps.Map(document.getElementById("map_canvas"), myOptions1);

    /*Inicializar marker con la posicion fija(centro de la republica mï¿½xicana)*/
    var marker = new google.maps.Marker({
        animation: google.maps.Animation.BOUNCE,
        position: latlng,
        map: map,
        title: "Origen",
        icon: ("resources/images/origen.png"),
        draggable: true
    });

    var contadorEscalas = $("#TotalEscalas").val();
    for (var i = 0; i < contadorEscalas; i++) {
        if ($("#Latitud_des" + i).val() != "")
            lat1 = $("#Latitud_des" + i).val();
        if ($("#Longitud_des" + i).val() != "") {
            log1 = $("#Longitud_des" + i).val();
        }

        var latlng1 = new google.maps.LatLng(lat1, log1);
        ruta[i + 1] = latlng1;

        /*Eventos de la Interfaz de Usuario para google.maps.Marker*/
        var marker1 = new google.maps.Marker({//opciones
            animation: google.maps.Animation.BOUNCE,
            position: latlng1,
            map: map,
            title: "Destino " + (i + 1),
            icon: ("resources/images/destino.png"),
            draggable: true
        });
    }

    lineas = new google.maps.Polyline({
        path: ruta,
        geodesic: true,
        map: map,
        strokeColor: '#4A7EBB',
        strokeWeight: 7,
        clickable: true
    });
    lineas.setMap(map);

    /*Agregar evento de dragend, para cuando detenga el movimiento del marker me de la posicion del marker*/
    google.maps.event.addListener(marker, 'dragend', function () {
        getCoordenadas();
    });
    /*google.maps.event.addListener(marker1, 'dragend', function () {
     getCoordenadas2();
     });*/

    return;
}

function getCoordenadas() {
    $('#fotocargandoPI').show();
    var markerLatLon = marker.getPosition();
    $("#Latitud_or").val(markerLatLon.lat());
    $("#Longitud_or").val(markerLatLon.lng());

    /*var jqxhr = $.getJSON( "http://maps.googleapis.com/maps/api/geocode/json?latlng="+markerLatLon.lat()+","+markerLatLon.lng()+"&sensor=true", function() {                                   
     }).done(function(data) { alert("second success: "+JSON.stringify(data)); })
     .fail(function() { alert("error"); })
     .always(function() { alert("complete");});*/
    $('#fotocargandoPI').hide();
    finished();
    return;
}

function getLatLngText() {
    $('#fotocargandoPI').show();
    initialize();
    $('#fotocargandoPI').hide();
    finished();
    return;
}

function getLatLngText2(fila) {
    $('#fotocargandoPI2' + fila).show();
    initialize();
    $('#fotocargandoPI2' + fila).hide();
    return;
}

//function getLatLngText() {
//                $('#fotocargandoPI').show();
//                dir = "ObtieneLatLng.php";
//                var latlong;
//                var lat;
//                var lon;
//                if ($("#Latitud_or").val() == "" || $("#Longitud_or").val() == "") {
//                    $("#contenidos_invisibles").load(dir, {"Calle": $("#txtCalle_or").val(),"Exterior": $("#txtExterior_or").val(),"Colonia": $("#txtColonia_or").val(),
//                    "Cp": $("#txtcp_or").val(),"Delegacion": $("#txtDelegacion_or").val(),"Estado": $("#slcEstado_or").val(),'mostrar': true}, function(data) {
//                    $('#mensajes').html(data);
//                    latlong = data.split("-");
//                    lat = latlong[0];
//                    lon = latlong[1];
//                });
//                } else {
//                    lat = $("#Latitud_or").val();
//                    lon = $("#Longitud_or").val();
//                }
//                $("#Latitud_or").val(lat);
//                $("#Longitud_or").val(lon);
//                var latlng = new google.maps.LatLng(lat, lon);
//                marker.setPosition(latlng);
//                map.setCenter(latlng);
//                $('#fotocargandoPI').hide();
//                finished();
//                return;
//            }
//function getLatLngText() {
//    $('#fotocargandoPI').show();
//    var paginaExito = "viajes/alta_autoriza_especial.php";
//    var idEspecial = $("#id").val();
//    var empleado = $("#slcEmpleado").val();
//    var campania = $("#slcCampania").val();
//    var turno = $("#slcTurno").val();
//    var hora = $("#hora").val();
//    var minutos = $("#minutos").val();
//    var horamin = hora + ":" + minutos + ":00";
//    var origen = $("#txtOrigen").val();
//    var destino = $("#txtDestino").val();
//    var calle_or = $("#txtCalle_or").val();
//    var exterior_or = $("#txtExterior_or").val();
//    var interior_or = $("#txtInterior_or").val();
//    var colonia_or = $("#txtColonia_or").val();
//    var ciudad_or = $("#txtCiudad_or").val();
//    var delegacion_or = $("#txtDelegacion_or").val();
//    var cp_or = $("#txtcp_or").val();
//    var localidad_or = $("#txtLocalidad_or").val();
//    var estado_or = $("#slcEstado_or").val();
//    var latitud_or = $("#Latitud_or").val();
//    var longitud_or = $("#Longitud_or").val();
//    var comentarios_or = $("#Comentario_or").val();
//    var cuadrante = $("#area").val();
//    var calle_des = $("#txtCalle_des").val();
//    var exterior_des = $("#txtExterior_des").val();
//    var interior_des = $("#txtInterior_des").val();
//    var colonia_des = $("#txtColonia_des").val();
//    var ciudad_des = $("#txtCiudad_des").val();
//    var delegacion_des = $("#txtDelegacion_des").val();
//    var cp_des = $("#txtcp_des").val();
//    var localidad_des = $("#txtLocalidad_des").val();
//    var estado_des = $("#slcEstado_des").val();
//    var latitud_des = $("#Latitud_des").val();
//    var longitud_des = $("#Longitud_des").val();
//    var comentarios_des = $("#Comentario_des").val();
//
//    if ($("#Latitud_or").val() == "" || $("#Longitud_or").val() == "") {
//        var lat = 19.4326;
//        var lon = -99.1339;
//    } else {
//        var lat = $("#Latitud_or").val();
//        var lon = $("#Longitud_or").val();
//    }
//
//    $('#contenidos').load(paginaExito, {"IdEspecialFiltro": idEspecial, "EmpleadoFiltro": empleado, "CampaniaFiltro": campania, "TurnoFiltro": turno, "HoraFiltro": horamin, "OrigenFiltro": origen,
//        "DestinoFiltro": destino, "Calle_orFiltro": calle_or, "Exterior_orFiltro": exterior_or, "Interior_orFiltro": interior_or, "Colonia_orFiltro": colonia_or, "Ciudad_orFiltro": ciudad_or, "Delegacion_orFiltro": delegacion_or,
//        "Cp_orFiltro": cp_or, "Localidad_orFiltro": localidad_or, "Estado_orFiltro": estado_or, "Latitud_orFiltro": latitud_or, "Longitud_orFiltro": longitud_or, "Comentarios_orFiltro": comentarios_or, "CuadranteFiltro": cuadrante,
//        "Calle_desFiltro": calle_des, "Exterior_desFiltro": exterior_des, "Interior_desFiltro": interior_des, "Colonia_desFiltro": colonia_des, "Ciudad_desFiltro": ciudad_des, "Delegacion_desFiltro": delegacion_des, "Cp_desFiltro": cp_des,
//        "Localidad_desFiltro": localidad_des, "Estado_desFiltro": estado_des, "Latitud_desFiltro": latitud_des, "Longitud_desFiltro": longitud_des, "Comentarios_desFiltro": comentarios_des,
//        'mostrar_or': true}, function () {
//        $(".button").button();
//        var latlng = new google.maps.LatLng(lat, lon);
//        marker.setPosition(latlng);
//        map.setCenter(latlng);
//        $('#fotocargandoPI').hide();
//        finished();
//        return;
//    });
//}

function getCoordenadas2(fila) {
    $('#fotocargandoPI2' + fila).show();
    var markerLatLon = marker1.getPosition();
    $("#Latitud_des" + fila).val(markerLatLon.lat());
    $("#Longitud_des" + fila).val(markerLatLon.lng());
    /*var jqxhr = $.getJSON( "http://maps.googleapis.com/maps/api/geocode/json?latlng="+markerLatLon.lat()+","+markerLatLon.lng()+"&sensor=true", function() {                                   
     }).done(function(data) { alert("second success: "+JSON.stringify(data)); })
     .fail(function() { alert("error"); })
     .always(function() { alert("complete");});*/
    $('#fotocargandoPI2' + fila).hide();
    finished();
    return;
}

//function getLatLngText2() {
//    $('#fotocargandoPI2').show();
//    var paginaExito = "viajes/alta_autoriza_especial.php";
//    var idEspecial = $("#id").val();
//    var empleado = $("#slcEmpleado").val();
//    var campania = $("#slcCampania").val();
//    var turno = $("#slcTurno").val();
//    var hora = $("#hora").val();
//    var minutos = $("#minutos").val();
//    var horamin = hora + ":" + minutos + ":00";
//    var origen = $("#txtOrigen").val();
//    var destino = $("#txtDestino").val();
//    var calle_or = $("#txtCalle_or").val();
//    var exterior_or = $("#txtExterior_or").val();
//    var interior_or = $("#txtInterior_or").val();
//    var colonia_or = $("#txtColonia_or").val();
//    var ciudad_or = $("#txtCiudad_or").val();
//    var delegacion_or = $("#txtDelegacion_or").val();
//    var cp_or = $("#txtcp_or").val();
//    var localidad_or = $("#txtLocalidad_or").val();
//    var estado_or = $("#slcEstado_or").val();
//    var latitud_or = $("#Latitud_or").val();
//    var longitud_or = $("#Longitud_or").val();
//    var comentarios_or = $("#Comentario_or").val();
//    var cuadrante = $("#area").val();
//    var calle_des = $("#txtCalle_des").val();
//    var exterior_des = $("#txtExterior_des").val();
//    var interior_des = $("#txtInterior_des").val();
//    var colonia_des = $("#txtColonia_des").val();
//    var ciudad_des = $("#txtCiudad_des").val();
//    var delegacion_des = $("#txtDelegacion_des").val();
//    var cp_des = $("#txtcp_des").val();
//    var localidad_des = $("#txtLocalidad_des").val();
//    var estado_des = $("#slcEstado_des").val();
//    var latitud_des = $("#Latitud_des").val();
//    var longitud_des = $("#Longitud_des").val();
//    var comentarios_des = $("#Comentario_des").val();
//
//    if ($("#Latitud_des").val() == "" || $("#Longitud_des").val() == "") {
//        var lat1 = 19.4326;
//        var lon1 = -99.1339;
//    } else {
//        var lat1 = $("#Latitud_des").val();
//        var lon1 = $("#Longitud_des").val();
//    }
//
//    $('#contenidos').load(paginaExito, {"IdEspecialFiltro": idEspecial, "EmpleadoFiltro": empleado, "CampaniaFiltro": campania, "TurnoFiltro": turno, "HoraFiltro": horamin, "OrigenFiltro": origen,
//        "DestinoFiltro": destino, "Calle_orFiltro": calle_or, "Exterior_orFiltro": exterior_or, "Interior_orFiltro": interior_or, "Colonia_orFiltro": colonia_or, "Ciudad_orFiltro": ciudad_or, "Delegacion_orFiltro": delegacion_or,
//        "Cp_orFiltro": cp_or, "Localidad_orFiltro": localidad_or, "Estado_orFiltro": estado_or, "Latitud_orFiltro": latitud_or, "Longitud_orFiltro": longitud_or, "Comentarios_orFiltro": comentarios_or, "CuadranteFiltro": cuadrante,
//        "Calle_desFiltro": calle_des, "Exterior_desFiltro": exterior_des, "Interior_desFiltro": interior_des, "Colonia_desFiltro": colonia_des, "Ciudad_desFiltro": ciudad_des, "Delegacion_desFiltro": delegacion_des, "Cp_desFiltro": cp_des,
//        "Localidad_desFiltro": localidad_des, "Estado_desFiltro": estado_des, "Latitud_desFiltro": latitud_des, "Longitud_desFiltro": longitud_des, "Comentarios_desFiltro": comentarios_des,
//        'mostrar_or': true}, function () {
//        $(".button").button();
//        var latlng1 = new google.maps.LatLng(lat1, lon1);
//        marker1.setPosition(latlng1);
//        map.setCenter(latlng1);
//        $('#fotocargandoPI2').hide();
//        finished();
//        return;
//    });
//
//}

function verReferencia(pagina)
{
    var empleado = $("#slcEmpleado").val();
    var visible = 1;
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"FiltroEmpleado": empleado, "visible": visible}, function () {
        finished();
    });
}

function verDomicilio()
{
    var dir = "WEB-INF/Controllers/Viajes/Controller_AutorizarEspecial.php";
    $("#contenidos_invisibles").load(dir, {"Origen": $("#origenR").val(), "Destino": $("#destinoR").val(), 'mostrar': true}, function (data) {
        //$('#mensajes').html(data);
        var referencia = data.split("+_+");
        if (referencia[0] != "") {
            var origen = referencia[0].split("///:///");
            $("#txtCalle_or").val(String(origen[0]));
            $("#txtExterior_or").val(String(origen[1]));
            $("#txtInterior_or").val(String(origen[2]));
            $("#txtColonia_or").val(String(origen[3]));
            $("#txtCiudad_or").val(String(origen[4]));
            $("#txtDelegacion_or").val(String(origen[5]));
            $("#txtcp_or").val(String(origen[6]));
            $("#txtLocalidad_or").val(String(origen[7]));
            $("#slcEstado_or").val(origen[8]);
            $("#Latitud_or").val(String(origen[9]));
            $("#Longitud_or").val(String(origen[10]));
            $("#area").val(origen[11]);
            if($("#origenR").val() !== "0"){
                $("#txtOrigen").val($("#origenR option:selected").text());
            }
            initialize();
        }
        if (referencia[1] != "") {
            var destino = referencia[1].split("///:///");
            $("#txtCalle_des0").val(String(destino[0]));
            $("#txtExterior_des0").val(String(destino[1]));
            $("#txtInterior_des0").val(String(destino[2]));
            $("#txtColonia_des0").val(String(destino[3]));
            $("#txtCiudad_des0").val(String(destino[4]));
            $("#txtDelegacion_des0").val(String(destino[5]));
            $("#txtcp_des0").val(String(destino[6]));
            $("#txtLocalidad_des0").val(String(destino[7]));
            $("#slcEstado_des0").val(destino[8]);
            $("#Latitud_des0").val(String(destino[9]));
            $("#Longitud_des0").val(String(destino[10]));
            if($("#destinoR").val() !== "0"){
                $("#txtDestino").val($("#destinoR option:selected").text());
            }
            initialize();
        }
    });
    finished();
    return;
}

function geocodificarDireccion(fila){
    if($("#txtCalle_des"+fila).val() !== "" && $("#txtExterior_des"+fila).val()!== "" && $("#txtDelegacion_des"+fila).val() !== "" 
            && $("#slcEstado_des"+fila+" option:selected").text() !== ""){
        $.post("WEB-INF/Controllers/Ajax/cargaDivs.php",
        {'geocodificar':true, 'calle':$("#txtCalle_des"+fila).val(), 'exterior':$("#txtExterior_des"+fila).val(), 'colonia':$("#txtColonia_des"+fila).val(), 
        'delegacion':$("#txtDelegacion_des"+fila).val(), 'cp':$("#txtcp_des"+fila).val(), 'estado':$("#slcEstado_des"+fila+" option:selected").text()},
        function(data){
            
            var latlng = data.split(",");
            if(latlng[0] !== null && latlng[1] !== null){
                $("#Latitud_des"+fila).val(latlng[0]);
                $("#Longitud_des"+fila).val(latlng[1]);
                initialize();
            }
        });
    }else{
        alert("Para utilizar la geocodificación ingresa la calle, el numero exterior, delegacion y el estado");
    }
}

function geocodificarDireccionOrigen(){
    if($("#txtCalle_or").val() !== "" && $("#txtExterior_or").val()!== "" && $("#txtDelegacion_or").val() !== "" 
            && $("#slcEstado_or option:selected").text() !== ""){
        $.post("WEB-INF/Controllers/Ajax/cargaDivs.php",
        {'geocodificar':true, 'calle':$("#txtCalle_or").val(), 'exterior':$("#txtExterior_or").val(), 'colonia':$("#txtColonia_or").val(), 
        'delegacion':$("#txtDelegacion_or").val(), 'cp':$("#txtcp_or").val(), 'estado':$("#slcEstado_or option:selected").text()},
        function(data){
            
            var latlng = data.split(",");
            if(latlng[0] !== null && latlng[1] !== null){
                $("#Latitud_or").val(latlng[0]);
                $("#Longitud_or").val(latlng[1]);
                initialize();
            }
        });
    }else{
        alert("Para utilizar la geocodificación ingresa la calle, el numero exterior, delegacion y el estado");
    }
}

function agregarDestinoViaje() {
    var contador = parseInt($("#TotalEscalas").val());
    var html = "<tr id='fila_detalle_" + contador + "'>" +
            "<td>" +
            "<fieldset>" +
            "<legend>Domicilio Destino</legend>" +
            "<table style='width:100%'>" +
            "<tr>" +
            "<td>Calle</td>" +
            "<td>" +
            "<input type='text' id='txtCalle_des" + contador + "' name='txtCalle_des" + contador + "' value='' >" +
            "<input type='hidden' id='idDetalle" + contador + "' name='idDetalle" + contador + "' value='' />" +
            "</td>" +
            "<td>No. Exterior</td><td><input type='text' id='txtExterior_des" + contador + "' name='txtExterior_des" + contador + "' value='' ></td>" +
            "</tr>" +
            "<tr>" +
            "<input type='hidden' id='txtInterior_des" + contador + "' name='txtInterior_des" + contador + "' value='' />" +
            "<td>Colonia</td><td><input type='text' id='txtColonia_des" + contador + "' name='txtColonia_des" + contador + "' value='' ></td>" +
            "</tr>" +
            "<tr>" +
            "<input type='hidden' id='txtCiudad_des" + contador + "' name='txtCiudad_des" + contador + "' value=''/>" +
            "<td>Delegación</td><td><input type='text' id='txtDelegacion_des" + contador + "' name='txtDelegacion_des" + contador + "' value='' ></td>" +
            "</tr>" +
            "<tr>" +
            "<td>C.P.</td><td><input type='text' id='txtcp_des" + contador + "' name='txtcp_des" + contador + "' value='' ></td>" +
            "<input type='hidden' id='txtLocalidad_des" + contador + "' name='txtLocalidad_des" + contador + "' value='' />" +
            "</tr>" +
            "<tr>" +
            "<td>Estado</td>" +
            "<td>" +
            "<select id='slcEstado_des" + contador + "' name='slcEstado_des" + contador + "' >" +
            "</select>" +
            "</td>" +
            "<td>Comentario</td><td><input type='text' id='Comentario_des" + contador + "' name='Comentario_des" + contador + "' value='' ></td>" +
            "</tr>" +
            "<tr>" +
            "<td>Latitud</td><td><input type='number' id='Latitud_des" + contador + "' name='Latitud_des" + contador + "' value='' step='any' ></td>" +
            "<td>Longitud</td><td><input type='number' id='Longitud_des" + contador + "' name='Longitud_des" + contador + "' value='' step='any' ></td>" +
            "</tr>" +
            "<tr>" +
            "<td></td>" +
            "<td></td>" +
            "</tr>" +
            "</table>" +
            "<table style='width: 100%;'>" +
            "<tr>" +
            "<td> " +
            "<input align='center' type='button' value='Buscar Ubicación' class='boton' title='Buscar Dimicilio de acuerdo con las coordenadas' onclick='getLatLngText2(" + contador + ");' />" +
            "</td>" +
            "<td> " +
            "<input align='center' type='button' value='Buscar Coordenada' class='boton' title='Buscar coordenadas de acuerdo con la dirección' onclick='geocodificarDireccion(" + contador + ");' />" +
            "<br/><span style='font-size:8px;font-style: italic;color:grey;'>Servicio bajo las condiciones de Google Maps Geocoding API</span>"+
            "</td>" +
            "<td>" +
            "<div id='fotocargandoPI2" + contador + "' style='width:100%; display: none; '>" +
            "<img src='resources/img/loading.gif'/>" +
            "</div>" +
            "</td>" +
            "<td>" +
            "<a href='#' onclick='agregarDestinoViaje(); return false;' title='Agregar una escala'>" +
            "<img src='resources/images/add.png'/>" +
            "</a>" +
            "</td>" +
            "</tr>" +
            "</table>" +
            "</fieldset> " +
            "</td>" +
            "</tr>";

    $("#tabla_origen_destino").append(html);
    /*Copiamos los tipos de componentes*/
    var $options = $("#slcEstado_des0 > option").clone();
    $('#slcEstado_des' + contador).append($options);
    $(".boton").button();

    contador++;
    $("#TotalEscalas").val(contador);
    initialize();
}


function eliminarDetalle(indice) {
    var contador = parseInt($("#TotalEscalas").val());
    var minimas_filas = 1;

    if (contador <= minimas_filas) {
        alert("No puedes borrar esta fila, el mínimo de eventos es de " + minimas_filas);
        return false;
    }

    var fila = "fila_detalle_" + indice;
    var trs = $("#tabla_origen_destino tr").length;
    if (trs > minimas_filas) {
        if ($("#IdTipoEvento" + indice).length) {
            $("#IdTipoEvento" + indice).rules("remove");
        }
        if ($("#Fecha" + indice).length) {
            $("#Fecha" + indice).rules("remove");
        }
        if ($("#Lugar" + indice).length) {
            $("#Lugar" + indice).rules("remove");
        }
        if ($("#IdEstatusEvento" + indice).length) {
            $("#IdEstatusEvento" + indice).rules("remove");
        }
        if ($("#Potencial" + indice).length) {
            $("#Potencial" + indice).rules("remove");
        }
        if ($("#Comentario" + indice).length) {
            $("#Comentario" + indice).rules("remove");
        }

        $("#" + fila).remove();
        for (var i = (indice); i < contador; i++) {
            if ($("#IdTipoEvento" + (i)).length) {//Campo de proveedores
                $('#IdTipoEvento' + i).attr('id', function () {
                    return 'IdTipoEvento' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'IdTipoEvento' + (i - 1);  // change name
                });
            }

            if ($("#Fecha" + (i)).length) {//Campo de costo
                $('#Fecha' + i).attr('id', function () {
                    return 'Fecha' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'Fecha' + (i - 1);  // change name
                });
            }

            if ($("#Lugar" + (i)).length) {//Campo de costo
                $('#Lugar' + i).attr('id', function () {
                    return 'Lugar' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'Lugar' + (i - 1);  // change name
                });
            }

            if ($("#IdEstatusEvento" + (i)).length) {//Campo de costo
                $('#IdEstatusEvento' + i).attr('id', function () {
                    return 'IdEstatusEvento' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'IdEstatusEvento' + (i - 1);  // change name
                });
            }

            if ($("#IdEstatusEvento" + (i)).length) {//Campo de costo
                $('#IdEstatusEvento' + i).attr('id', function () {
                    return 'IdEstatusEvento' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'IdEstatusEvento' + (i - 1);  // change name
                });
            }

            if ($("#Potencial" + (i)).length) {//Campo de costo
                $('#Potencial' + i).attr('id', function () {
                    return 'Potencial' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'Potencial' + (i - 1);  // change name
                });
            }

            if ($("#Comentario" + (i)).length) {//Campo de costo
                $('#Comentario' + i).attr('id', function () {
                    return 'Comentario' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'Comentario' + (i - 1);  // change name
                });
            }

            if ($("#delete_" + (i)).length) {//Campo de costo
                $('#delete_' + i).attr('id', function () {
                    return 'delete_' + (i - 1);  // change id
                });
                $("#delete_" + (i - 1)).attr("onclick", "eliminarDetalle(" + (i - 1) + "); return false;");
            }

            if ($("#fila_detalle_" + (i)).length) {//Campo de costo
                $('#fila_detalle_' + i).attr('id', function () {
                    return 'fila_detalle_' + (i - 1);  // change id
                });
            }
        }
    } else {
        alert("No puedes borrar esta fila, el mínimo de proveedores es de " + minimas_filas);
        return false;
    }

    contador--;
    $("#TotalEscalas").val(contador);
}

function validarDetalle(i) {
    $("#IdTipoEvento" + i).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona un tipo de evento"
        }
    });

    $("#Fecha" + i).rules('add', {
        required: true, maxlength: 19, minlength: 16,
        messages: {
            required: " * Ingresa la fecha", maxlength: " *  Ingresa máximo {0} caracteres", minlength: " *  Ingresa mínimo {0} caracteres"
        }
    });

    $("#Lugar" + i).rules('add', {
        minlength: 1, maxlength: 150,
        messages: {
            maxlength: " *  Ingresa máximo {0} caracteres", minlength: " *  Ingresa mínimo {0} caracteres"
        }
    });

    $("#IdEstatusEvento" + i).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona un estado"
        }
    });

    $("#Potencial" + i).rules('add', {
        required: true, number: true, maxlength: 12, min: 0,
        messages: {
            required: " * Ingresa el potencial", number: " * Ingresa sólo números", maxlength: " *  Ingresa máximo {0} caracteres", min: " * Sólo se aceptan número positivos"
        }
    });

    $("#Comentario" + i).rules('add', {
        maxlength: 1000,
        messages: {
            maxlength: " *  Ingresa máximo {0} caracteres"
        }
    });
}
