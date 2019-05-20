var form = "#form_lectura";
var controlador = "../WEB-INF/Controllers/Controler_Lectura_Corte.php";
$(document).ready(function() {
    $(".boton").button().css('height', '25px').css('font-size', '11px');
    /*validate form*/
    $(form).validate({
        rules: {
        }, messages: {
        }
    });

    /*Prevent form*/
    /*$(form).submit(function(event) {
     if ($(form).valid()) {
     $("#cargando_lectura").show();
     /* stop form from submitting normally */
    //event.preventDefault();
    /*Serialize and post the form*/
    /*$.post(controlador, {form: $(form).serialize()}).done(function(data) {
     $('#mensaje_lecturas').html(data);  
     $("#guardar_lectura").hide();
     $("#cargando_lectura").hide();                
     });
     }
     });*/
    var year = Number($("#year").val());
    var month = Number($("#month").val());
    var minDate = new Date(year, month - 1, +1); //one day next before month
    var maxDate = new Date(year, month, 0); // one day before next month
    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: maxDate,
            minDate: minDate,
            onSelect: function(dateText) {
                cargarEquipos($("#cliente").val(), $("#cc").val(), 'month', 'year', dateText);
            }
        });
    });

    var numero_equipos = Number($("#numero_equipos").val());
    for (var i = 1; i <= numero_equipos; i++) {
        var minimo = $("#contador_bnA_" + i).val();
        $("#contador_bn_" + i).rules('add', {
            required: false,
            number: true,
            min: minimo,
            messages: {
                required: " * Ingrese el contador", number: " * Ingresa solo números", min: " * El valor mínimo permitido es {0} por la lectura anterior del equipo"
            }
        });
        minimo = $("#contador_colorA_" + i).val();
        if ($("#contador_color_" + i).length) {
            $("#contador_color_" + i).rules('add', {
                required: false,
                number: true,
                min: minimo,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", min: " * El valor mínimo permitido es {0} por la lectura anterior del equipo"
                }
            });
        }
        if ($("#toner_bn_" + i).length) {
            $("#toner_bn_" + i).rules('add', {
                required: false,
                number: true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: " * El valor mínimo es {0}"
                }
            });
        }
        if ($("#toner_cian_" + i).length) {
            $("#toner_cian_" + i).rules('add', {
                required: false,
                number: true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: " * El valor mínimo es {0}"
                }
            });
        }
        if ($("#toner_mag_" + i).length) {
            $("#toner_mag_" + i).rules('add', {
                required: false,
                number: true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: " * El valor mínimo es {0}"
                }
            });
        }
        if ($("#toner_amarillo_" + i).length) {
            $("#toner_amarillo_" + i).rules('add', {
                required: false,
                number: true,
                max: 100,
                min: 0,
                messages: {
                    required: " * Ingrese el contador", number: " * Ingresa solo números", max: "Ingresa un valor menor a {0}", min: " * El valor mínimo es {0}"
                }
            });
        }
    }
});

function cargarEquipos(ClaveCliente, ClaveCC, mes, anio, fecha_lectura, sugerir_check) {
    $("#inicia_captura").hide();
    $("#cargando_lectura").show();
    $('#mensaje_lecturas').empty();
    var sugerir = 1;
    if($('#'+sugerir_check).is(':checked')){
        sugerir = 1;
    }else{
        sugerir = 0;
    }
    if (ClaveCC != null && ClaveCC != "") {//
        $("#div_lectura").load("toma_lecturas.php", {'ClaveCC': ClaveCC, 'mes': $("#" + mes).val(), 'anio': $("#" + anio).val(), 'fecha_lectura': fecha_lectura, 'sugerir':sugerir}, function() {
            $("#cargando_lectura").hide();
            $("#inicia_captura").show();
        });
    } else {
        $("#div_lectura").load("toma_lecturas.php", {'ClaveCliente': ClaveCliente, 'mes': $("#" + mes).val(), 'anio': $("#" + anio).val(), 'fecha_lectura': fecha_lectura, 'sugerir':sugerir}, function() {
            $("#cargando_lectura").hide();
            $("#inicia_captura").show();
        });
    }
}

function seleccionarTodo(seleccionar){
    var n = Number($("#numero_equipos").val());
    for (var i = 1; i <= n; i++) {
        if($('#check_bn_'+i).length){
            $('#check_bn_'+i).prop('checked', seleccionar);
        }
    }
}

function validarMaximosHistoricos() {
    var permiso_insertar = new Array();
    var n = Number($("#numero_equipos").val());
    for (var i = 1; i <= n; i++) {
        var serie = $("#serie_" + i).val();
        if (!$("#check_bn_" + i).is(":checked")) {//Sino esta chequeado para guardar, no se da permiso
            permiso_insertar.push("0");
        } else {
            /*Validamos contador bn*/
            var actual = parseFloat($("#contador_bn_" + i).val());
            var maximo = parseFloat($("#contador_bnMaximo_" + i).val());

            if (maximo > 0 && actual < maximo) {/*Hay una incidencia para el contador b/n*/
                if (confirm('El equipo ' + serie + ' tiene registrada una lectura máxima de ' + maximo + ', aún así desea registrar el contador ' + actual)) {
                    permiso_insertar.push("1");
                    insertarIncidencia(serie, $("#date").val(), $("#date").val(), 'Lectura de contador b/n menor (' + actual + ') a la máxima encontrada (' + maximo + ')', '1', $("#cc").val(), 'null');
                } else {/*El usuario decidio no insertar los registros del equipo*/
                    permiso_insertar.push("0");
                }
            } else {/**/
                /*Verificamos que los contadores de color tampoco tengan incidencias*/
                actual = parseFloat($("#contador_color_" + i).val());
                maximo = parseFloat($("#contador_colorMaximo_" + i).val());
                if (maximo > 0 && actual < maximo) {/*Hay una incidencia para el contador de color*/
                    if (confirm('El equipo ' + serie + ' tiene registrada una lectura máxima de color de ' + maximo + ', aún así desea registrar el contador ' + actual)) {
                        permiso_insertar.push("1");
                        insertarIncidencia(serie, $("#date").val(), $("#date").val(), 'Lectura de contador de color menor (' + actual + ') a la máxima encontrada (' + maximo + ')', '1', $("#cc").val(), 'null');
                    } else {/*El usuario decidio no insertar los registros del equipo*/
                        permiso_insertar.push("0");
                    }
                } else {/*No hay incidencias en ninguno de los contadores*/
                    permiso_insertar.push("1");/**/
                }
            }
        }
    }
    return permiso_insertar;
}

function guardarLecturas() {
    if ($(form).valid()) {
        $("#guardar_lectura").hide();
        $("#cargando_lectura").show();
        var permisos = validarMaximosHistoricos();
        /* stop form from submitting normally */
        //event.preventDefault();
        /*Serialize and post the form*/
        $.post(controlador, {'form': $(form).serialize(), 'permisos': permisos}).done(function(data) {
            $('#mensaje_lecturas').html(data);
            $("#guardar_lectura").hide();
            $("#cargando_lectura").hide();
        });
    }
}