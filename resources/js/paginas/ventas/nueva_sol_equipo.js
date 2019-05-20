var contador = 2;
var propio = 0;
var id_solicitud = null;
var form = "#solform";
var edicion = null;
var filas = 0;
var cambiar_tipo_solicitud = true;
var cambiar_vendedor = true;
var cambiar_cliente = true;
var cambiar_localidad = true;
$(document).ready(function() {
    var controlador = "WEB-INF/Controllers/Ventas/Controller_Nueva_Solicitud.php";
    var controlador2 = "WEB-INF/Controllers/Ajax/solicitudEquipo.php";

    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            tipo_solicitud: {required: true},
            cliente: {required: true},
            dias_credito: {number: true, maxlength: 4},
            dias_revision: {number: true, maxlength: 2}
        },
        messages: {
            tipo_solicitud: {required: " * Selecciona el tipo de solicitud"},
            cliente: {required: " * Selecciona el cliente"},
            dias_credito: {number: " * Ingresa un número", maxlength: " * Máximo {0} números"},
            dias_revision: {number: " * Ingresa un número", maxlength: " * Máximo {0} números"}
        }
    });
   
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            $("#mensajes").text(" ");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            controlador = "WEB-INF/Controllers/Ventas/Controller_Nueva_Solicitud.php";
            if (id_solicitud == null) {
                loading("Cargando componentes ...");
                $.post(controlador, {form: $(form).serialize()}).done(function(data) {
                    if (data.toString().indexOf("Error:") === -1) {
                        finished();
                        id_solicitud = data;
                        $("#contacto_sol").load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'ClaveCliente': $("#cliente").val(), 'contactos': true}, function() {
                            $(".row_contacto").show();
                            $("#tabla_detalles").load("ventas/SolicitudTabla.php", {id: id_solicitud, tipo: $("#tipo_solicitud").val()}, function(data) {
                                /*Actualizamos el select para solo tener un cliente*/
                                var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
                                $('#cliente').load(dir, {'ClaveCliente': $("#cliente").val(), 'ClienteUnico': true}, function() {/*Refrescamos el select y volvemos a poner filtros*/
                                    /*Refrescamos las opciones*/
                                    var x = $(this).find('option');
                                    $(this).multiselect("refresh", x).multiselectfilter({
                                        label: 'Filtro',
                                        placeholder: 'Escribe el filtro'
                                    });
                                    /*Volvemos a aplicar filtros*/
                                    $(this).multiselect({
                                        multiple: false,
                                        noneSelectedText: "No ha seleccionado",
                                        selectedList: 1
                                    }).multiselectfilter({
                                        label: 'Filtro',
                                        placeholder: 'Escribe el filtro'
                                    });
                                    $(this).css('width', '250px');//Width del select            
                                });
                                /*Actualizamos el select para solo poder elegir un tipo de solicitud*/
                                $('#tipo_solicitud').load(dir, {'IdTipoSolicitud': $("#tipo_solicitud").val(), 'TipoSolicitudUnico': true}, function() {/*Refrescamos el select y volvemos a poner filtros*/
                                    /*Refrescamos las opciones*/
                                    var x = $(this).find('option');
                                    $(this).multiselect("refresh", x).multiselectfilter({
                                        label: 'Filtro',
                                        placeholder: 'Escribe el filtro'
                                    });
                                    /*Volvemos a aplicar filtros*/
                                    $(this).multiselect({
                                        multiple: false,
                                        noneSelectedText: "No ha seleccionado",
                                        selectedList: 1
                                    }).multiselectfilter({
                                        label: 'Filtro',
                                        placeholder: 'Escribe el filtro'
                                    });
                                    $(this).css('width', '250px');//Width del select            
                                });
                                if ($("#vendedor").length) {
                                    /*Actualizamos el select para solo poder elegir un vendedor*/
                                    $('#vendedor').load(dir, {'IdUsuario': $("#vendedor").val(), 'UsuarioUnico': true}, function() {/*Refrescamos el select y volvemos a poner filtros*/
                                        /*Refrescamos las opciones*/
                                        var x = $(this).find('option');
                                        $(this).multiselect("refresh", x).multiselectfilter({
                                            label: 'Filtro',
                                            placeholder: 'Escribe el filtro'
                                        });
                                        /*Volvemos a aplicar filtros*/
                                        $(this).multiselect({
                                            multiple: false,
                                            noneSelectedText: "No ha seleccionado",
                                            selectedList: 1
                                        }).multiselectfilter({
                                            label: 'Filtro',
                                            placeholder: 'Escribe el filtro'
                                        });
                                        $(this).css('width', '250px');//Width del select            
                                    });
                                }
                                if ($("#localidad_vd").length) {
                                    /*Actualizamos el select para solo poder elegir una localidad*/
                                    $('#localidad_vd').load(dir, {'ClaveCentroCosto': $("#localidad_vd").val(), 'LocalidadUnico': true}, function() {/*Refrescamos el select y volvemos a poner filtros*/
                                        /*Refrescamos las opciones*/
                                        var x = $(this).find('option');
                                        $(this).multiselect("refresh", x).multiselectfilter({
                                            label: 'Filtro',
                                            placeholder: 'Escribe el filtro'
                                        });
                                        /*Volvemos a aplicar filtros*/
                                        $(this).multiselect({
                                            multiple: false,
                                            noneSelectedText: "No ha seleccionado",
                                            selectedList: 1
                                        }).multiselectfilter({
                                            label: 'Filtro',
                                            placeholder: 'Escribe el filtro'
                                        });
                                        $(this).css('width', '250px');//Width del select            
                                    });
                                }
                                /*Una vez guardada la solicitud, ya no se puede cambiar el tipo de solicitud, cliente, vendedor o localidad*/
                                cambiar_tipo_solicitud = false;
                                cambiar_vendedor = false;
                                cambiar_cliente = false;
                                cambiar_localidad = false;
                                /*Damos estilo a las listas desplegables*/
                                $(".tipo").multiselect({
                                    multiple: false,
                                    noneSelectedText: "No ha seleccionado",
                                    selectedList: 1,
                                    minWidth: "130"
                                }).multiselectfilter({
                                    label: 'Filtro',
                                    placeholder: 'Escribe el filtro'
                                });
                                $(".filtro").multiselect({
                                    multiple: false,
                                    noneSelectedText: "No ha seleccionado",
                                    selectedList: 1,
                                    minWidth: "130"
                                }).multiselectfilter({
                                    label: 'Filtro',
                                    placeholder: 'Escribe el filtro'
                                });
                                $("#tipo_solicitud").multiselect({
                                    multiple: false,
                                    noneSelectedText: "No ha seleccionado",
                                    selectedList: 1,
                                    minWidth: "130"
                                }).multiselectfilter({
                                    label: 'Filtro',
                                    placeholder: 'Escribe el filtro'
                                });
                                cambiarccosto('cliente');
                                cargarClientePropio('cliente');
                                $(".boton").button();
                                $("#cancelar").hide();
                                $("#aceptar").val("Terminar");
                            });
                        });
                    } else {
                        $("#mensajes").html(data);
                        finished();
                    }
                });
                $("#divinfo").empty();
            } else {
                if (filas > 0) {
                    //alert("Contador: " + contador);
                    var hayEquipos = false;
                    var hayComponente = false;
                    var tipoSolicitud = parseInt($('#tipo_solicitud').val());
                    var equipos = "";
                    var localidad = "";
                    for(var i = 1; i < contador; i++){
                        if($("#tipo"+i).val() === "0"){
                            hayEquipos = true;
                            equipos += $("#modelo"+i).val() + ",";
                            localidad += $("#localidad"+i).val() + ",";
                        }
                    }
                    equipos = equipos.substring(0, equipos.length - 1);
                    localidad = localidad.substring(0, localidad.length - 1);
                    if(hayEquipos){
                        for(var i = 1; i < contador; i++){
                            if($("#tipo"+i).val() === "2"){
                                hayComponente = true;
                            }
                        }
                    }
                    if(!hayEquipos || (hayEquipos && hayComponente) || tipoSolicitud === 4 || tipoSolicitud === 6 || tipoSolicitud === 5){
                        loading("Guardando y enviando correos ...");
                        $.post(controlador, {form: $(form).serialize(), solicitud: id_solicitud, edicion: edicion}).done(function(data) {
                            if (data.toString().indexOf("Error:") === -1) {
                                cambiarContenidos('ventas/list_sol_equipo.php', 'Solicitudes');
                                $("#mensajes").html(data);
                                finished();
                            } else {
                                $("#mensajes").html(data);
                                finished();
                            }
                        });
                    }else{
                        //Debemos revisar en el cliente para ver si tiene componentes compatibles con el equipo seleccionado
                        $.post(controlador2, {equipos: equipos, localidad: localidad}).done(function(data) {
                            if(data === "SinAlmacen" || data === "Exito"){
                                $("#mensajes").html(data);
                                loading("Guardando y enviando correos ...");
                                $.post(controlador, {form: $(form).serialize(), solicitud: id_solicitud, edicion: edicion}).done(function(data) {
                                    if (data.toString().indexOf("Error:") === -1) {
                                        cambiarContenidos('ventas/list_sol_equipo.php', 'Solicitudes');
                                        $("#mensajes").html(data);
                                        finished();
                                    } else {
                                        $("#mensajes").html(data);
                                        finished();
                                    }
                                });
                                finished();
                            }else{
                                $("#mensajes").html(data);
                                finished();
                            }
                        });
                    }
                } else {
                    alert("Debe al menos agregar un equipo o componente");
                    finished();
                }
            }
        }
    });

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
            changeMonth: true,
            changeYear: true,
            minDate: '-0D'
        });
    });
    //$('.periodo_fac').mask("99-9999");


    $(".tipo").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1,
        minWidth: "130"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $("#tipo_solicitud").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1,
        minWidth: "130"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $(".boton").button();/*Estilo de botones*/

    if (id_solicitud != null) {//Si se esta editando una solicitud, se habilita el boton de guardar        
        $("#aceptar").show();
        /*Una vez guardada la solicitud, ya no se puede cambiar el tipo de solicitud, cliente, vendedor o localidad*/
        cambiar_tipo_solicitud = false;
        /*cambiar_vendedor = false;
         cambiar_cliente = false;
         cambiar_localidad = false;*/
    }
});

function agregarcamposol() {    
    $("#tsolform").append("<tr id='filaSolicitud_" + contador + "'><td><label for=\"numero" + contador + "\">Cantidad</label></td><td><input type=\"text\" id=\"numero" + contador + "\" name=\"numero" + contador + "\" maxlength=\"5\" style=\"width: 40px;\"/>" +
            "</td><td><label for=\"tipo" + contador + "\">Tipo</label></td><td><select id=\"tipo" + contador + "\" name=\"tipo" + contador + "\"" +
            " class=\"tipo\" onchange=\"cambiarselectmodelo('tipo" + contador + "', 'modelo" + contador + "'); mostrarTipoInventario('tipo" + contador + "','tipo_inventario" + contador + "','div_serie_cliente" + contador + "');\" style=\"width: 100px;\">" +
            "</select></td>" +
            "<td><select id=\"modelo" + contador + "\" name=\"modelo" + contador + "\" class=\"size filtro\" style=\"width: 250px;\"><option value=\"\">Selecciona el modelo</option></select></td>" +
            "<td><select id=\"localidad" + contador + "\" name=\"localidad" + contador + "\" class=\"size filtro localidad\" style=\"width: 250px;\" onchange=\"actualizarDatosContrato(); mostrarEquiposLocalidad('localidad" + contador + "','serie_con_cliente" + contador + "');\"></select></td>" +
            "<td>" +
            "<select id=\"tipo_inventario" + contador + "\" name=\"tipo_inventario" + contador + "\" style=\"display: none;\"></select>" +
            "<div id=\"div_serie_cliente" + contador + "\" style=\"display: none;\">" +
            "<label for=\"serie_con_cliente" + contador + "\">Equipo en localidad</label>" +
            "<select id=\"serie_con_cliente" + contador + "\" name=\"serie_con_cliente" + contador + "\">" +
            "<option value=\"\">Selecciona un equipo</option>" +
            "</select>" +
            "</div>" +
            "</td></tr>");
    /*Copiamos los tipos de componentes*/
    var $options = $("#tipo1 > option").clone();
    $('#tipo' + contador).append($options);

    var $options = $("#tipo_inventario1 > option").clone();
    $('#tipo_inventario' + contador).append($options);

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width', '150px');

    $(".tipo").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1,
        minWidth: "130"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

    dir = "WEB-INF/Controllers/Ventas/Controller_select_localidades.php";

    $("#numero" + contador).rules('add', {
        required: true,
        number: true,
        messages: {
            required: " * Ingrese la cantidad", number: " * Ingresa un número"
        }
    });
    $("#tipo" + contador).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona el tipo"
        }
    });
    $("#modelo" + contador).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona el modelo"
        }
    });

    //if(parseInt($("#tipo_solicitud").val()) <= 2){
    addRequiredLocalidad(contador);
    //}

    //cargartipocompo("tipo" + contador);
    cambiarccostoEspecifico('cliente', contador);
    contador++;
}

function deleteRowSolicitud(row) {
    var fila = "filaSolicitud_" + row;
    var trs = $("#tsolform tr").length;
    if (trs > 1) {
        $("#" + fila).remove();
        for (var i = (row + 1); i <= contador; i++) {
            if ($("#numero" + (i)).length) {
                $('#numero' + i).attr('id', function() {
                    return 'numero' + (i - 1);  // change id
                }).attr('name' + i, function() {
                    return 'numero' + (i - 1);  // change name
                });
            }
        }
    }
}

function addRequiredLocalidad(indice) {
    if ($("#localidad" + indice).length) {
        $("#localidad" + indice).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona la localidad"
            }
        });
    }
}

function deleteRequiredLocalidad(indice) {
    if ($("#localidad" + indice).length) {
        $("#localidad" + indice).rules("remove");
    }
}

function cargartipocompo(destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_tipocompo.php";
    $("#" + destino).load(dir);
}

function establecercontador(count) {
    contador = count;
}

function cambiarselectmodelo(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_compoequip.php";
    $('#' + destino).load(dir, {'id': $("#" + origen).val()}, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#" + destino).css('width', '250px');
    });
    //$("#" + destino).load(dir, {id: $("#" + origen).val()});
}

function cambiarccosto(origen) {
    dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#contenidos_invisibles").load(dir, {"cliente": $("#" + origen).val(), "is_suspendido": true}, function(data) {
        if (data == "false") {
            dir = "WEB-INF/Controllers/Ventas/Controller_select_localidades.php";
            if ($('#localidad').length) {
                $('#localidad').load(dir, {'id': $("#" + origen).val()}, function() {/*Refrescamos el select y volvemos a poner filtros*/
                    /*Refrescamos las opciones*/
                    var x = $(this).find('option');
                    $(this).multiselect("refresh", x).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    /*Volvemos a aplicar filtros*/
                    $(this).multiselect({
                        multiple: false,
                        noneSelectedText: "No ha seleccionado",
                        selectedList: 1
                    }).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    $(this).css('width', '250px');//Width del select            
                });
            }
            $("#aceptar").show();
        } else {
            $("#aceptar").hide();
            alert("Este cliente está marcado como suspendido o moroso, no se puede continuar el proceso");
            if ($("#localidad1").length) {
                $('.localidad').empty();
                var x = $("#localidad1").find('option');
                $(".localidad").multiselect("refresh", x).multiselectfilter({
                    label: 'Filtro',
                    placeholder: 'Escribe el filtro'
                });
            }
            /*Cuando el cliente está marcado como moroso, lo deseleccionamos y deshabilitamos el boton de guardar*/
            $("#" + origen).val("");
            /*Refrescamos las opciones*/
            var x = $("#" + origen).find('option');
            $("#" + origen).multiselect("refresh", x).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            /*Volvemos a aplicar filtros*/
            $("#" + origen).multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            return false;
        }
        
    });
    //Con esto obtenemos el valor ID del cliente que esta en el select y lo enviamos al archivo div_info para que realice la consulta que nos devuelva 
    //los dias de credito y formas de pago del contrato del cliente que se seleccione.
    var idCliente = document.getElementById("cliente").value;
            $.post("ventas/div_info.php", {idClientePHP: idCliente }, function(data){
                $("#DatClin").html(data);
            });//Fin
   
}   


function cargarClientePropio(origen) {
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#contenidos_invisibles").load(dir, {'cliente': $("#" + origen).val(), 'tipo_cliente': true}, function(data) {
        if (data == "7") {
            $('.oculto').show();
            propio = 1;
            $("#cliente_propio").val(propio);
        } else {
            $('.oculto').hide();
            propio = 0;
            $("#cliente_propio").val(propio);
        }
    });
}

function cambiarccostoEspecifico(origen, numeroCC) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_localidades.php";
    if ($('#localidad' + numeroCC).length) {
        $('#localidad' + numeroCC).load(dir, {'id': $("#" + origen).val()}, function() {/*Refrescamos el select y volvemos a poner filtros*/
            /*Refrescamos las opciones*/
            var x = $("#localidad" + numeroCC).find('option');
            $("#localidad" + numeroCC).multiselect("refresh", x).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            /*Volvemos a aplicar filtros*/
            $("#localidad" + numeroCC).multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            $("#localidad" + numeroCC).css('width', '250px');//Width del select        
        });
    }
    /*$("#localidad" + i).load(dir, {id: $("#" + origen).val()});*/
}

function cargarContratos(origen, destino) {
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'cliente': $("#" + origen).val(), 'contrato': 'true'});
}

function cargarAnexos(origen, destino, cc, servicio) {
    /*En caso de que no exista, creamos anexos y/o asociamos a las localidad elegida. Tambien se crean los servicios*/
    var dir = "WEB-INF/Controllers/Ajax/updates.php";
    //$("#contenidos_invisibles").load(dir, {'contrato': $("#" + origen).val(), 'cc':cc , 'client':$("#cliente").val(), 'anexo': 'true'}, function(data){            
    $("#contenidos_invisibles").load(dir, {'cc': cc, 'crear': 'true'}, function(data) {/*Asociamos o creamos anexos y contratos cuando sea necesario*/
        dir = "WEB-INF/Controllers/Ventas/Controller_select_clianexo.php";/*Cargamos los anexos*/
        $("#" + destino).load(dir, {'ccosto': cc, 'group': true, 'contrato': $("#" + origen).val(), 'omite_selecciona': true}, function() {
            cargarServicios(destino, servicio);
        });
    });
}

function cargarIdAnexoClienteCC(origen, destino) {
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'anexo': $("#" + origen).val(), 'idAnexoClienteCC': 'true'});
}

function cargarServicios(origen, destino) {
    /*var dir = "WEB-INF/Controllers/Ajax/updates.php";
     var tipo = 1; /*0: servicio global, 1: servicio particular.*/
    /*if($("#tipo_servicio").length){
     tipo = $("#tipo_servicio").val();
     }
     $("#contenidos_invisibles").load(dir,{'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'tipo':tipo}, function(){*/
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'idAnexoClienteCC': $("#" + origen).val(), 'catalogo_servicios': 'true', 'anexo_completo': true}, function(data) {
        if($("#numero_contratos").length){
            //Precargamos informacion
            var registros = $("#numero_contratos").val();
            
            for(var i = 0; i<registros; i++){
                if($("#kservicio_pre"+i).length){//Si hay datos de esta localidad ya cargados
                    //alert("Primera opcion contrato: "+$("#contrato"+i+" option:first").val());                    
                    if($("#contrato"+i).val() != $("#contrato_pre"+i).val()){
                        //alert("Diferente contrato");
                        $("#contrato"+i).val($("#contrato_pre"+i).val());//Asignamos el contrato ya seleccionado previamente
                        cargarAnexos('contrato'+i,'anexo'+i,''+$("#fila"+i).val(),'servicio'+i);
                    }/*else if($("#anexo"+i).val() != $("#idanexo_pre"+i).val()){
                        alert("Diferente anexo");
                        $("#anexo"+i).val($("#idanexo_pre"+i).val());//Asignamos el anexo ya seleccionado previamente
                        cargarServicios('anexo'+i,'servicio'+i);
                    }*/else{
                        //alert("Final");
                        $("#contrato"+i).val($("#contrato_pre"+i).val());//Asignamos el contrato ya seleccionado previamente
                        $("#anexo"+i).val($("#idanexo_pre"+i).val());//Asignamos el anexo ya seleccionado previamente
                        $("#servicio"+i).val($("#servicio_pre"+i).val()+"-"+$("#kservicio_pre"+i).val());//ASignamos el servicio ya seleccionado previamente
                    }
                }
            }
        }
    });
    //});    
}

function addRequiredFormaPago(required) {
    if (required) {
        $("#formas_pago").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona la forma de pago"
            }
        });
        $("#dias_credito").rules('add', {
            required: true,
            messages: {
                required: " * Escribe los días de crédito"
            }
        });
    } else {
        $("#formas_pago").rules("remove");
        $("#dias_credito").rules("remove");
    }
}

function addRequiredDiasRevision(required) {
    if (required) {
        $("#dias_revision").rules('add', {
            required: true,
            messages: {
                required: " * Escribe los días de revisión"
            }
        });
    } else {
        $("#dias_revision").rules("remove");
    }
}

function cambioTipoSolicitud() {
    if (cambiar_tipo_solicitud) {
        var tipo = parseInt($("#tipo_solicitud").val());
        if (edicion !== 1) {
            $.post("ventas/Solicitud_div.php", {id: tipo}, function(data) {
                $("#cambiable_div").html(data);
                tiposNVD();
                if (tipo <= 2) {/*Todo es obligatorio*/
                    addRequiredContratos();
                } else {
                    deleteRequiredContratos();
                }
                if (tipo == 4 || tipo == 5) {
                    activarRetorno(true);
                } else {
                    activarRetorno(false);
                }
                $("#aceptar").show();
                //$("#aceptar").val("Terminar");
            });
        } else {
            $.post("ventas/Solicitud_div.php", {id: tipo, solicitud: id_solicitud}, function(data) {
                $("#cambiable_div").html(data);
                tiposNVD();
                if (tipo <= 2) {/*Todo es obligatorio*/
                    addRequiredContratos();
                } else {
                    deleteRequiredContratos();
                }
                if (tipo == 4 || tipo == 5) {
                    activarRetorno(true);
                } else {
                    activarRetorno(false);
                }
                $("#aceptar").show();
                $("#aceptar").val("Terminar");
            });
        }
    }
}

function activarRetorno(activar) {
    if (!activar) {
        $("#fecha_regreso").rules("remove");
        $("#retorno").hide();
    } else {
        $('#fecha_regreso').each(function() {
            $(this).datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true,
                minDate: '-0D'
            });
        });
        $("#retorno").show();
        $("#fecha_regreso").rules('add', {
            required: true,
            messages: {
                required: " * Selecciona la fecha de devoluci\u00f3n"
            }
        });
    }
}

function deleteRequiredContratos() {
    /*Borramos rules de required a los campos en caso de que existan*/
    for (var j = 0; j <= 20; j++) {
        if ($("#contrato" + j).length) {
            $("#contrato" + j).rules("remove");
        }
        if ($("#anexo" + j).length) {
            $("#anexo" + j).rules("remove");
        }
        if ($("#servicio" + j).length) {
            $("#servicio" + j).rules("remove");
        }
    }
}

function addRequiredContratos() {
    var num_contratos = parseInt($("#numero_contratos").val());
    for (var i = 0; i < num_contratos; i++) {
        $("#contrato" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el contrato"
            }
        });
        $("#anexo" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el anexo"
            }
        });
        $("#servicio" + i).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el servicio"
            }
        });
    }
}

function actualizarDatosContrato() {
    var id_solicitud_aux = "null";
    
    if(id_solicitud != null){        
        id_solicitud_aux = id_solicitud;
    }
    
    var localidades = []; //Array con localidades no repetidas
    var searchTerm = $("#localidad").val();
    if (searchTerm != "" && searchTerm != "null" && searchTerm != null && localidades.indexOf(searchTerm) == -1) {//Si la localidad no se encuentra en el array
        localidades.push(searchTerm);
    }
    for (var i = 1; i < contador; i++) {//Recorremos todas las localidades y las guardamos (sin localidades repetidas)
        var searchTerm = $("#localidad" + i).val();
        if (searchTerm != "" && searchTerm != "null" && searchTerm != null && localidades.indexOf(searchTerm) == -1) {//Si la localidad no se encuentra en el array
            localidades.push(searchTerm);
        }
    }
    /*Concatenamos en un strin las localidades separadas por &_&*/
    var localidades_concatenadas = "";
    for (i = 0; i < localidades.length; i++) {
        localidades_concatenadas += (localidades[i] + "&_&");
    }

    localidades_concatenadas = localidades_concatenadas.substring(0, localidades_concatenadas.length - 3);
    if ($("#localidades_anteriores").val() != localidades_concatenadas) {//Si ya variaron las localidades con respecto a las ya seleccionadas
        deleteRequiredContratos();
        $("#datos_contratos").load("WEB-INF/Controllers/Ajax/cargaDivs.php", {'clavesCC': localidades_concatenadas, 'servicios': true, 'IdSolicitud':id_solicitud_aux}, function() {
            if (parseInt($("#tipo_solicitud").val()) <= 2) {
                addRequiredContratos();
            }            
        });
    }
    $("#localidades_anteriores").val(localidades_concatenadas); /*Guardamos las localidades como anteriores para el siguiente procesamiento*/
}


function mostrarTipoInventario(origen, destino, div_serie) {
    if ($("#" + origen).val() == "0") {
        $("#" + div_serie).hide();
        $("#" + destino).show();
    } else {
        $("#" + destino).hide();
        $("#" + div_serie).show();
    }
}

function mostrarEquiposLocalidad(origen, destino) {
    var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
    $("#" + destino).load(dir, {'localidad': $("#" + origen).val(), 'equipos': 'true'});
}

function tiposNVD() {
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            minDate: '-0D'
        });
    });

//calendario para el periodo de facturacion
    $('.periodo_fac').each(function() {
        $(this).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'MM/yy',
            onClose: function() {
                var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(iYear, iMonth, 1));
            }
        });
    });

    $('.boton').button().css('margin-top', '20px');
    $('.oculto').hide();
}



function agregarreglas(ax) {
	
    $("#numero" + ax).rules("add", {
        required: true,
        number: true,
        min: 1,
        messages: {
            required: "* Ingresa la cantidad",
            number: "* Ingresa un número",
            min: " * Ingresa el un valor mayor a {0}"
        }
    });
	
    $("#tipo" + ax).rules("add", {
        required: true,
        messages: {
            required: "* Selecciona el tipo"
        }
    });
	
    $("#modelo" + ax).rules("add", {
        required: true,
        messages: {
            required: "* Selecciona el modelo"
        }
    });
	
    $("#costo" + ax).rules("add", {
        required: true,
        messages: {
            required: " * Ingresa el costo",
        }
    });
	
    $("#costotro" + ax).rules("add", {
        required: true,
        number: true,
        min: 1,
        messages: {
            required: " * Ingresa el costo",
            number: " * Ingresa un número",
            min: " * Ingresa el un valor mayor a {0}"
        }
    });
}
function cambiarselectmodelo(origen, destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_compoequip.php";
    $("#" + destino).load(dir, {id: $("#" + origen).val()}, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#" + destino).multiselect('widget').width(125);
    });
}

function cargarprecio(tipo, modelo, destino) {
    var dir = "WEB-INF/Controllers/Ventas/Controller_select_precioabc.php";    
    $("#" + destino).load(dir, {"tipo": $('#' + tipo).val(), "modelo": $('#' + modelo).val()}, function(data) {
        
        $("#" + destino).multiselect().trigger('reset');
    });

}

function cargartipocompo(destino) {
    dir = "WEB-INF/Controllers/Ventas/Controller_select_tipocompo.php";
    $("#" + destino).load(dir, function() {
        /*Refrescamos las opciones*/
        var x = $('#' + destino).find('option');
        $('#' + destino).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Refrescamos las opciones*/
        $('#' + destino).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#" + destino).multiselect('widget').width(125);
    });
}

function calcularcostop(cantidad, costo, destino, otro, label, input) {
    if ($("#" + costo).val() !== "none") {
        var suma = $("#" + cantidad).val() * $("#" + costo).val();
        $("#" + destino).val(suma);
        $("#" + label).hide();
        $("#" + input).hide();
        $("#" + otro).val('0');
    } else {
        $("#" + label).show();
        $("#" + input).show();
    }
}
function calcularcosto(cantidad, costo, destino) {
    var suma = $("#" + cantidad).val() * $("#" + costo).val();
    $("#" + destino).val(suma);
}

function calcularcostocant(cantidad, costo, destino, otro) {
    if ($("#" + costo).val() !== "" && $("#" + costo).val() !== "none") {
        var suma = $("#" + cantidad).val() * $("#" + costo).val();
        $("#" + destino).val(suma);
    } else {
        var suma = $("#" + cantidad).val() * $("#" + otro).val();
        $("#" + destino).val(suma);
    }
}

function eliminarfilaulti() {
    if (contador == 2) {
        alert("No se pueden borrar mas.");
    } else {
        $("#pedidos tr:last").remove();
        contador--;
    }
}

function setcontador(count) {
    contador = count;
    for (var i = 2; contador < i; i++) {
		if($("#numero" + i).length){
			agregarreglas(i);
		}
    }
}

function setpaginaExito(liga) {
    paginaExito = liga;
}

function hidecosto(num) {
    $("#otrolabel" + num).hide();
    $("#otroinput" + num).hide();
}

function showcosto(num) {
    $("#otrolabel" + num).show();
    $("#otroinput" + num).show();
}

function cambiarlocalidad(origen, destino) {
    if (cambiar_cliente && !$("#IdSolicitud").length) {
        dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
        $("#contenidos_invisibles").load(dir, {"cliente": $("#" + origen).val(), "is_suspendido": true}, function(data) {
            if (data == "false") {
                dir = "WEB-INF/Controllers/Ventas/Controller_select_localidades.php";
                $("#" + destino).load(dir, {id: $("#" + origen).val()}, function() {
                    /*Refrescamos las opciones*/
                    var x = $("#" + destino).find('option');
                    $("#" + destino).multiselect("refresh", x).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    /*Volvemos a aplicar filtros*/
                    $("#" + destino).multiselect({
                        multiple: false,
                        noneSelectedText: "No ha seleccionado",
                        selectedList: 1
                    }).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    $("#" + (destino)).css('width', '250px');//Width del select
                });
                $("#aceptar").show();
            } else {
                $("#aceptar").hide();
                alert("Este cliente está marcado como suspendido o moroso, no se puede continuar el proceso");
                $('#' + destino).empty();
                if ($("#" + destino).length) {
                    var x = $("#" + destino).find('option');
                    $(".localidad").multiselect("refresh", x).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                }
                /*Cuando el cliente está marcado como moroso, lo deseleccionamos y deshabilitamos el boton de guardar*/
                $("#" + origen).val("");
                /*Refrescamos las opciones*/
                var x = $("#" + origen).find('option');
                $("#" + origen).multiselect("refresh", x).multiselectfilter({
                    label: 'Filtro',
                    placeholder: 'Escribe el filtro'
                });
                /*Volvemos a aplicar filtros*/
                $("#" + origen).multiselect({
                    multiple: false,
                    noneSelectedText: "No ha seleccionado",
                    selectedList: 1
                }).multiselectfilter({
                    label: 'Filtro',
                    placeholder: 'Escribe el filtro'
                });
                return false;
            }
        });
    }
}

function cargarclientes(origen, componente) {
    if (cambiar_vendedor && !$("#IdSolicitud").length) {
        $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clientes.php", {cliente: $("#" + origen).val()}, function() {
            /*Refrescamos las opciones*/
            var x = $("#" + componente).find('option');
            $("#" + componente).multiselect("refresh", x).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            /*Volvemos a aplicar filtros*/
            $("#" + componente).multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            $("#" + (componente)).css('width', '250px');//Width del select
        });
    }
}

function enviardetalle() {
    var tipo = $("#tipo_solicitud").val();
    var tipo2 = $("#tipo").val();
    var cliente = $("#cliente").val();
    var contrato0 = $("#contrato0").val();
    var modelo = $("#modelo").val();
    var servicio0 = $("#servicio0").val();
    if (tipo != 6) {
        
        $("#numero").rules('add', {
            required: true,
            number: true,
            min: 1,
            messages: {
                required: " * Escribe la cantidad",
                number: " * Introduce un número",
                min: " * Introduce un minímo de uno"
            }
        });
        $("#tipo").rules('add', {
            required: true,
            messages: {
                required: " * selecciona el tipo"
            }
        });
        $("#modelo").rules('add', {
            required: true,
            messages: {
                required: " * selecciona el modelo"
            }
        });
        if ($("#localidad").length) {
            $("#localidad").rules('add', {
                required: true,
                messages: {
                    required: " * selecciona la localidad"
                }
            });
        }

        if ($(form).valid()) {
            loading("Guardando y actualizando ...");
            ///Meter validacion de modelos y servicios
            controlador = "WEB-INF/Controllers/Ventas/Controller_VerificarServicio.php";
            $.post(controlador, {form: $(form).serialize(), cliente: cliente, tipo: tipo2, contrato0: contrato0, modelo: modelo, servicio0: servicio0}).done(function(data) {
                $("#contenidos_invisibles").html(data);
                finished();
                if($.trim(data) !== "No"){
                    var aux = servicio0.split("-");
                    if(confirm("¿Está seguro que desea asignar el servicio "+ aux[1] + " ("+aux[0]+")?, \n este modelo de equipo está asociado en otro servicio: \n\ " + data)){
                            cargarPedido(tipo);
                        }
                }else{
                    cargarPedido(tipo);
                }
            });
        }
    } else {
        
        $("#numero").rules('add', {
            required: true,
            number: true,
            min: 1,
            messages: {
                required: " * Escribe la cantidad",
                number: " * Introduce un número",
                min: " * Introduce un minímo de uno"
            }
        });
        $("#tipo").rules('add', {
            required: true,
            messages: {
                required: " * selecciona el tipo"
            }
        });
        $("#modelo").rules('add', {
            required: true,
            messages: {
                required: " * selecciona el modelo"
            }
        });
        if ($("#localidad").length) {
            $("#localidad").rules('add', {
                required: true,
                messages: {
                    required: " * selecciona la localidad"
                }
            });
        }
        $("#costotro").rules('add', {
            required: true,
            messages: {
                required: " * selecciona la localidad"
            }
        });
        $("#costo").rules('add', {
            required: true,
            messages: {
                required: " * selecciona el costo"
            }
        });

        if ($(form).valid()) {
            var servicio = false;
            loading("Guardando y actualizando ...");
            /*Serialize and post the form*/
            ///Meter validacion de modelos y servicios
            cargarPedido(tipo);
        }
    }
}

function editarfilasol(partida) {
    var tipo = $("#tipo_solicitud").val();
    if (tipo != 6) {
        $("#numero" + partida).prop("disabled", false);
        $("#tipo" + partida).prop("disabled", false);
        $("#modelo" + partida).prop("disabled", false);
        $("#ubicacion" + partida).prop("disabled", false);
        $("#retiro" + partida).prop("disabled", false);

        if ($("#localidad" + partida).length) {
            $("#localidad" + partida).prop("disabled", false);
        }

        $("#numero" + partida).rules('add', {
            required: true,
            number: true,
            min: 1,
            messages: {
                required: " * Escribe la cantidad",
                number: " * Introduce un número",
                min: " * Introduce un minímo de uno"
            }
        });
        $("#tipo" + partida).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el tipo"
            }
        });
        $("#modelo" + partida).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el modelo"
            }
        });
        if ($("#localidad" + partida).length) {
            $("#localidad" + partida).rules('add', {
                required: true,
                messages: {
                    required: " * Selecciona la localidad"
                }
            });
        }
        $("#tipo" + partida).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1,
            minWidth: "130"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#modelo" + partida).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1,
            minWidth: "130"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        if ($("#localidad" + partida).length) {
            $("#localidad" + partida).multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1,
                minWidth: "130"
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
        }
    } else {
        $("#numero" + partida).prop("disabled", false);
        $("#tipo" + partida).prop("disabled", false);
        $("#modelo" + partida).prop("disabled", false);
        $("#ubicacion" + partida).prop("disabled", false);
        $("#retiro" + partida).prop("disabled", false);
        $("#localidad" + partida).prop("disabled", false);
        $("#costotro" + partida).prop("disabled", false);
        $("#costo" + partida).prop("disabled", false);
        $("#numero" + partida).rules('add', {
            required: true,
            number: true,
            min: 1,
            messages: {
                required: " * Escribe la cantidad",
                number: " * Introduce un número",
                min: " * Introduce un minímo de uno"
            }
        });
        $("#tipo" + partida).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el tipo"
            }
        });
        $("#modelo" + partida).rules('add', {
            required: true,
            messages: {
                required: " * Selecciona el modelo"
            }
        });
        if ($("#localidad" + partida).length) {
            $("#localidad" + partida).rules('add', {
                required: true,
                messages: {
                    required: " * Selecciona la localidad"
                }
            });
        }
        $("#costotro" + partida).rules('add', {
            required: true,
            messages: {
                required: " * selecciona la localidad"
            }
        });
        $("#costo" + partida).rules('add', {
            required: true,
            messages: {
                required: " * selecciona el costo"
            }
        });
        $("#tipo" + partida).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1,
            minWidth: "130"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        $("#modelo" + partida).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1,
            minWidth: "130"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        if ($("#localidad" + partida).length) {
            $("#localidad" + partida).multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1,
                minWidth: "130"
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
        }
        $("#costo" + partida).multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1,
            minWidth: "130"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    }
    $("#editarsolrow" + partida).html("<a onclick='guardarfilasol(" + partida + ")'><img src='resources/images/Apply.png' title='Guardar Fila'/></a>");
}

function borrar() {
    var tipo = $("#tipo_solicitud").val();
    if (tipo != 6) {
        $("#numero").rules('remove');
        $("#tipo").rules('remove');
        $("#modelo").rules('remove');
        $("#modelo").rules('remove');
        $("#numero").val("");
        $("#tipo").val('');
        $("#modelo").val('');
        $("#ubicacion").val('');
        $("#retiro").val('');
        $("#tipo").multiselect("refresh");
        $("#modelo").multiselect("refresh");
        if ($("#localidad").length) {
            $("#localidad").rules('remove');
            $("#localidad").val('');
            $("#localidad").multiselect("refresh");
        }
    } else {
        $("#numero").rules('remove');
        $("#tipo").rules('remove');
        $("#modelo").rules('remove');
        $("#total").rules('remove');
        $("#costo").rules('remove');
        $("#costotro").rules('remove');
        $("#numero").val("");
        $("#tipo").val('');
        $("#modelo").val('');
        if ($("#localidad").length) {
            $("#localidad").rules('remove');
            $("#localidad").val('');
            $("#localidad").multiselect("refresh");
        }
        $("#total").val('');
        $("#costo").val('');
        $("#costotro").val('');
        $("#ubicacion").val('');
        $("#retiro").val('');
        $("#tipo").multiselect("refresh");
        $("#modelo").multiselect("refresh");
    }
}

function guardarfilasol(partida) {
    var tipo = $("#tipo_solicitud").val();
    if (tipo != 6) {
        if ($(form).valid()) {
            loading("Guardando y actualizando tabla..");
            /*Serialize and post the form*/
            controlador = "WEB-INF/Controllers/Ventas/ControllerGuardarDetalleSol.php";
            $.post(controlador, {form: $(form).serialize(), solicitud: id_solicitud, tipo: tipo, partida: partida}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {
                    finished();
                    $("#tabla_edicion_sol").html(data);
                    $("#numero" + partida).rules('remove');
                    $("#tipo" + partida).rules('remove');
                    $("#modelo" + partida).rules('remove');
                    if ($("#localidad").length) {
                        $("#localidad" + partida).rules('remove');
                    }
                    borrar();
                } else {
                    $("#tabla_edicion_sol").html(data);
                    finished();
                }
            });
        }
    } else {
        if ($(form).valid()) {
            loading("Guardando y actualizando tabla..");
            /*Serialize and post the form*/
            controlador = "WEB-INF/Controllers/Ventas/ControllerGuardarDetalleSol.php";
            $.post(controlador, {form: $(form).serialize(), solicitud: id_solicitud, tipo: tipo, partida: partida}).done(function(data) {
                if (data.toString().indexOf("Error:") === -1) {
                    $("#tabla_edicion_sol").html(data);
                    $("#numero" + partida).rules('remove');
                    $("#tipo" + partida).rules('remove');
                    $("#modelo" + partida).rules('remove');
                    if ($("#localidad").length) {
                        $("#localidad" + partida).rules('remove');
                    }
                    $("#costo" + partida).rules('remove');
                    $("#costotro" + partida).rules('remove');
                    finished();
                    borrar();
                } else {
                    $("#tabla_edicion_sol").html(data);
                    finished();
                }
            });
        }
    }
}

function setId_solicitud(id) {
    id_solicitud = id;
    /*cambiar_tipo_solicitud = false;
     cambiar_vendedor = false;
     cambiar_cliente = false;
     cambiar_localidad = false; */
}

function setEdicion(id) {
    edicion = id;
}

function cargarTablaDetalles() {
    var tipo = $("#tipo_solicitud").val();
    $("#tabla_detalles").load("ventas/SolicitudTabla.php", {id: id_solicitud, tipo: $("#tipo_solicitud").val()}, function(data) {
        $(".filtro").multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1,
            minWidth: "130"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        cambiarccosto('cliente');
        cargarClientePropio('cliente');
        $(".boton").button();
    });
    $.post("WEB-INF/Controllers/Ventas/ControllerGuardarDetalleSol.php", {solicitud: id_solicitud, tipo: tipo, SoloTabla: 1}, function(data) {
        if (data.toString().indexOf("Error:") === -1) {
            $("#tabla_edicion_sol").html(data);
            finished();
        } else {
            $("#mensajes").html(data);
            finished();
        }
    });
}

function eliminarfilasol(partida) {
    var tipo = $("#tipo_solicitud").val();
    var r = confirm("¿Esta seguro que desea borrar la fila?");
    if (r == true) {
        $.post("WEB-INF/Controllers/Ventas/ControllerEliminarDetalleSol.php", {solicitud: id_solicitud, tipo: tipo, partida: partida}, function(data) {
            if (data.toString().indexOf("Error:") === -1) {
                $("#tabla_edicion_sol").load("WEB-INF/Controllers/Ventas/ControllerGuardarDetalleSol.php", {solicitud: id_solicitud, tipo: tipo, SoloTabla: 1});
            } else {
                $("#mensajes").html(data);
                finished();
            }

        });
    }
}


function actualizarDatosContratoPrecargados(localidades_concatenadas) {
    var id_solicitud_aux = "null";
    
    if(id_solicitud != null){        
        id_solicitud_aux = id_solicitud;
    }
    
    var res = localidades_concatenadas.split("&_&");
    var localidades = []; //Array con localidades no repetidas
    for (var i = 0; i < res.length; i++) {
        var searchTerm = res[i];
        if (searchTerm != "" && searchTerm != "null" && searchTerm != null && localidades.indexOf(searchTerm) == -1) {//Si la localidad no se encuentra en el array
            localidades.push(searchTerm);
        }
    }
    localidades_concatenadas = "";
    for (i = 0; i < localidades.length; i++) {
        localidades_concatenadas += (localidades[i] + "&_&");
    }
    localidades_concatenadas = localidades_concatenadas.substring(0, localidades_concatenadas.length - 3);
    if ($("#localidades_anteriores").val() != localidades_concatenadas) {//Si ya variaron las localidades  
        deleteRequiredContratos();
        $("#datos_contratos").load("WEB-INF/Controllers/Ajax/cargaDivs.php", {'clavesCC': localidades_concatenadas, 'servicios': true, 'IdSolicitud':id_solicitud_aux}, function() {
            if (parseInt($("#tipo_solicitud").val()) <= 2) {
                addRequiredContratos();
            }
            
        });
    }
    $("#localidades_anteriores").val(localidades_concatenadas);
}

function setfilas(id) {
    filas = id;
}

function cargarPedido(tipo){    
    controlador = "WEB-INF/Controllers/Ventas/ControllerGuardarDetalleSol.php";
    $.post(controlador, {form: $(form).serialize(), solicitud: id_solicitud, tipo: tipo}).done(function(data) {             
        if (data.toString().indexOf("Error:") === -1) {
            $("#tabla_edicion_sol").html(data);            
            finished();
            if($("#numero").length){
                $("#numero").rules('remove');
            }
            if($("#tipo").length){
                $("#tipo").rules('remove');
            }
            if($("#modelo").length){
                $("#modelo").rules('remove');
            }
            if($("#localidad").length){
                $("#localidad").rules('remove');
            }
            if($("#ubicacion").length){
                $("#ubicacion").val('');
            }
            if($("#retiro").length){
                $("#retiro").val('');
            }
            
            $(".tipo").multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1,
                minWidth: "130"
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            $(".filtro").multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1,
                minWidth: "130"
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            
            borrar();
        } else {
            
            $("#tabla_edicion_sol").html(data);
            finished();
        }
    });
}

function verValoresContrato(num){
    var contrato = $("#contrato"+num).val();
    lanzarPopUpAjustable("Valores contrato","cliente/lista_parametrosContrato.php?contrato="+contrato,600,400);
}
