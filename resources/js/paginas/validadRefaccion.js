function validarNota(pagina, nota, tipo) {
    $("#valida_"+nota).hide();
    if (tipo == "1") {
        var controler = "WEB-INF/Controllers/Controler_CambiarEstatusNota.php";
        loading("Cargando ...");
        $("#mensajes").load(controler, {"accion": "validar", "nota": nota}, function() {
            $("#contenidos").load(pagina, function() {
                $("#valida_"+nota).show();
                finished();                
            });
        });
    }else if (tipo == 2) {
        var controler = "WEB-INF/Controllers/Controler_Solicitud_Toner.php";
        loading("Cargando ...");
        $("#mensajes").load(controler, {"enviarSolicitudToner": "1", "idTicket": nota}, function() {            
            finished();
            $('#contenidos').html("<iframe src='" + pagina + "' width='100%' height='900px' frameborder='0' scrolling='si'></iframe>", function() {
                $("#valida_"+nota).show();
            });
        });
    }
}

function editarRefacciones(idNota, usuario, validada) {
    loading("Cargando ...");
    var pagina = "nota/AgregarNota.php";
    $("#contenidos").load(pagina, {"idNota": idNota, "usuario": usuario, "validada": validada, "editaRefaccion":true}, function() {
        finished();
    });
}
function mostrarExistencias(numLista, refacccion) {
    loading("Cargando ...");
    var controler = "WEB-INF/Controllers/Controler_CambiarEstatusNota.php";
    var almacen = $("#almacen" + numLista).val();
    //$.post(controler, {"almacen": almacen, "refaccion": refacccion}).done(function(data) {
    $("#cantidadExistente" + numLista).load(controler, {"almacen": almacen, "refaccion": $("#" + refacccion).val()}, function(data) {
        $("#cantidadExix" + numLista).val(data);
        if (data == "0") {
            $("#estatus_" + numLista + " option[value='20']").attr("selected", true);
        } else {
            $("#estatus_" + numLista + " option[value='21']").attr("selected", true);
        }
        finished();
    });
}

function guardarMultiplesSeleccionadas(pagina) {
    $("#guardar_seleccionados").hide();
    $("#mensajes").empty();
    loading("Cargando ...");
    var seleccionados = "";
    var refaccionesPasadas = new Array();
    var cantidadesSolicitadasPasadas = new Array();
    var cantidadesDisponiblesPasadas = new Array();
    var almacenesPasados = new Array();
    var numeroRefacciones = 0;
    var tabla_envios_size = parseInt($("#cantidad_solicitadas").val());
    var mensaje = "";
    var repetido = false;
    for (var i = 1; i <= tabla_envios_size; i++) {
        if ($('#check_guardar_' + i).prop('checked')) {
            repetido = false;
            for(var j = 0; j < numeroRefacciones; j++){ //Recorremos las refacciones que ya se mandaron
                if(refaccionesPasadas[j] === $('#cambiarComponente_' + i).val() && (almacenesPasados[j] === $('#almacen' + i).val())){
                    //Se repite la refacción, hay que verificar si la cantidad es suficiente para atender ambas solicitudes y que sea el mismo almacén que atiene a ambos
                    repetido = true;
                    if((cantidadesDisponiblesPasadas[j] - cantidadesSolicitadasPasadas[j]) < $('#cantidadRestante' + i).val()){
                        $('#cantidadExix' + i).val(0);
                        $('#estatus_' + i).val('20');
                    }else{
                        cantidadesDisponiblesPasadas[j] -= cantidadesSolicitadasPasadas[j];
                        cantidadesSolicitadasPasadas[j] = $('#cantidadRestante' + i).val();
                    }
                }
            }
            if(!repetido){
                refaccionesPasadas[numeroRefacciones] = $('#cambiarComponente_' + i).val();
                cantidadesSolicitadasPasadas[numeroRefacciones] = $('#cantidadRestante' + i).val();
                cantidadesDisponiblesPasadas[numeroRefacciones] = $('#cantidadExix' + i).val();
                almacenesPasados[numeroRefacciones] = $('#almacen' + i).val();
                numeroRefacciones++;
            }
            mensaje += "Refaccion: " + $('#cambiarComponente_' + i).val() + " Cantidad a enviar: " + $('#cantidadRestante' + i).val() + "<br/>";
            var valor = $('#check_guardar_' + i).val();
            seleccionados += (valor + ",");
        }
    }

    if($('#slc_todo_solicitado').prop('checked')){
        $("<div>"+mensaje+"</div>").dialog({
            resizable: true, height: 300, width: 450, modal: true, closeOnEscape: false,
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog).hide();
            },
            buttons: {
                "Cancelar": function() {
                    $(this).dialog("close");
                    $(this).dialog('destroy').remove();    
                    finished();
                },
                "Continuar": function() {
                    $(this).dialog("close");
                    $(this).dialog('destroy').remove();
                    seleccionados = seleccionados.substring(0, seleccionados.length - 1);
                    var filas = seleccionados.split(",");
                    var recargar = false;
                    for (var i = 0; i < filas.length; i++) {
                        var index = filas[i];
                        if ((i + 1) == filas.length) {
                            recargar = true;
                        }
                        CambiarEstatusRefaccion(index, 0, $("#id_ticket_" + index).val(), $("#id_estatus_" + index).val(), $("#activo_" + index).val(),
                                $("#usuarios_" + index).val(), $("#mostrar_" + index).val(), $("#nota_" + index).val(), $("#parte_" + index).val(),
                                $("#cantidad_" + index).val(), $("#estatus_cobranza_" + index).val(), recargar, pagina);
                    }
                    finished();
                }
            }
        });
    }else{
        if (seleccionados != "") {
            seleccionados = seleccionados.substring(0, seleccionados.length - 1);
            //finished();
            var filas = seleccionados.split(",");
            var recargar = false;
            for (var i = 0; i < filas.length; i++) {
                var index = filas[i];
                if ((i + 1) == filas.length) {
                    recargar = true;
                }
                CambiarEstatusRefaccion(index, 0, $("#id_ticket_" + index).val(), $("#id_estatus_" + index).val(), $("#activo_" + index).val(),
                        $("#usuarios_" + index).val(), $("#mostrar_" + index).val(), $("#nota_" + index).val(), $("#parte_" + index).val(),
                        $("#cantidad_" + index).val(), $("#estatus_cobranza_" + index).val(), recargar, pagina);
            }
            finished();
        } else {
            $("#guardar_seleccionados").show();
            alert("Selecciona al menos una solicitud para guardar");
            finished();
        }
    }
}

function CambiarEstatusRefaccion(numLista, cantidadPedidas, ticket, idEstatusAtencion, activo, usuarioSolicitud,
        MostrarCliente, nota, refaccion, cantidadP, estatusCobranza, recargar, pagina) {

    if ($("#guardar_refaccion_" + numLista).length) {
        $("#guardar_refaccion_" + numLista).hide();
    }

    var controlador = "WEB-INF/Controllers/Controler_CambiarEstatusNota.php";
    $("#contenidos_invisibles").load(controlador, {'idTicket': ticket, 'original': refaccion, 'nueva': $("#cambiarComponente_" + numLista).val(),
        'accion': 'cambiarRefaccion'}, function(data) {

        if ($("#cambiarComponente_" + numLista).length && $("#cambiarComponente_" + numLista).val() != refaccion) {
            refaccion = $("#cambiarComponente_" + numLista).val();
        }
        var cantidad = $("#cantidadExix" + numLista).val();
        var estatus = $("#estatus_" + numLista).val();
        var solicitadas = $("#cantidadRestante" + numLista).val();
        var almacen = $("#almacen" + numLista).val();
        $("#errorCantidad" + numLista).html("");
        $("#errorEstado" + numLista).html("");
        $("#errorAlmacen" + numLista).html("");
        if (estatus == 0) {
            $("#errorEstado" + numLista).html("Seleccione una acción");
        } else {
            if (parseInt(solicitadas) > parseInt(cantidadP)) {
                $("#errorCantidad" + numLista).html("La cantidad debe ser menor o igual a la cantidad solicitada");
                finished();
            } else if (parseInt(solicitadas) == 0) {                
                $("#contenidos_invisibles").load(controlador, {'ticket':ticket, 'refaccion':refaccion, 'accion':'cambiar_cero'} ,function(data){
                    
                    if(recargar){
                        BuscarTicket(pagina);
                        finished();
                    }
                });
            } else if (solicitadas < 0) {
                $("#errorCantidad" + numLista).html("La cantidad no puede ser menor a cero");
                finished();
            } else {
                $("#errorEstado" + numLista).html("");
                if (estatus !== "21") {
                    //backorder o buscando en usado
                    RealizarPeticion(ticket, idEstatusAtencion, estatus, activo, usuarioSolicitud, MostrarCliente, nota, refaccion,
                            cantidadP, solicitadas, almacen, recargar, pagina);
                } else {
                    if (almacen == 0) {
                        $("#errorAlmacen" + numLista).html("Seleccione un almacén");
                        finished();
                    } else {
                        //listo para entregar
                        if (cantidad === "0") {
                            $("#errorEstado" + numLista).html("El almacén no cuenta con refacciones y no se puede poner en éste estado");
                            finished();
                        } else
                            RealizarPeticion(ticket, idEstatusAtencion, estatus, activo, usuarioSolicitud, MostrarCliente, nota, refaccion,
                                    cantidadP, solicitadas, almacen, recargar, pagina);
                    }
                }
            }
        }
    });
}

function RealizarPeticionToner(nota, refaccion, cantidad, cantidadAlmacen, solicitadas, estatus, almacen, cliente, localidad, idTicket, 
        refaccionesSustituidas, series, enviar, pagina) {
    var controler = "WEB-INF/Controllers/Controler_Solicitud_Toner.php";
    loading("Cargando ...");
    //alert(cantidad.toString());
    $.post(controler, {"idNota": nota, "componente": refaccion, "cantidad": cantidad, "cantidadAlmacen": cantidadAlmacen, "cantidadSolicitadas": solicitadas, "estatus": estatus, "almacen": almacen, "accion": "solicitarAlmacen", "series": series}, function(datos_restult) {
        var result = datos_restult.split(" /*/*/ ");
        $("#mensajes").html(result[0]);
        if (refaccionesSustituidas != null && refaccionesSustituidas.length > 0) {
            $("#contenidos_invisibles").load(controler, {"accion": "cambiarToner", "ticket": idTicket, "toners": refaccionesSustituidas, "cantidades": solicitadas, "series": series}, function(data) {
                if (enviar == "1") {
                    antender_enviar_toner(result[1]);
                } else {
                    $("#contenidos").load(pagina, {"cliente": cliente, "localidad": localidad, "ticket": idTicket}, function() {
                        finished();
                    });
                }
            });
        } else {
            if (enviar == "1") {
                antender_enviar_toner(result[1]);
            } else {
                $("#contenidos").load(pagina, {"cliente": cliente, "localidad": localidad, "ticket": idTicket}, function() {
                    finished();
                });
            }
        }
    });
}

function RealizarPeticion(ticket, idEstatusAtencion, estatus, activo, usuarioSolicitud, MostrarCliente, nota,
        refaccion, cantidadP, solicitadas, almacen, recargar, pagina) {
    var controler = "WEB-INF/Controllers/Controler_CambiarEstatusNota.php";
    loading("Cargando ...");
    $("#mensajes").load(controler, {"accion": "nuevoStatus", "ticket": ticket, "idEstatusAtencion": idEstatusAtencion, "estatus": estatus, "activo": activo, "usuarioSolicitud": usuarioSolicitud, "MostrarCliente": MostrarCliente, "nota": nota, "refaccion": refaccion, "cantidad": cantidadP, "solicitadas": solicitadas, "almacen": almacen}, function() {
        if (recargar) {
            BuscarTicket(pagina);
            $("#guardar_seleccionados").show();
        }
    });
}

function pickingToner()
{
    loading("Cargando ...");
    var controler = "WEB-INF/Controllers/Controler_Solicitud_Toner.php";
    var pagina = "almacen/toner_listo_entregar.php";
    var id = new Array();
    var contador = 0;
    $("input:checkbox:checked").each(function() {
//cada elemento seleccionado
        id[contador] = $(this).val();
        contador++;
    });
    // alert(id.length);
    var c = 0;
    var tamano = 0;
    var ticket = new Array();
    var nota = new Array();
    var descripcion = new Array();
    var refaccion = new Array();
    var cantidad = new Array();
    var mostrar = new Array();
    var usuarioSolicitud = new Array();
    var cantidadPeticion = new Array();
    var almacen = new Array();
    var cliente = new Array();
    var modelo = new Array();
    var mensajeria = new Array();
    //$("#erroPeticion"+)
    var contadorArray = 0;
    if (id.length > 0) {
        while (c < id.length) {
            var num = id[c];
            var cantidadSolicitada = $("#cantidad1" + num).val();
            var cantidadEntregada = $("#maximoPeticion" + num).val();
            if (parseInt(cantidadSolicitada) > parseInt(cantidadEntregada)) {
                $("#erroPeticion" + num).html("La cantidad debe ser menor a las restantes");
            } else {
                $("#erroPeticion" + num).html("");
                ticket[contadorArray] = $("#ticket" + num).val();
                nota[contadorArray] = $("#nota" + num).val();
                descripcion[contadorArray] = $("#descripcion" + num).val();
                refaccion[contadorArray] = $("#refaccion" + num).val();
                cantidad[contadorArray] = $("#cantidad" + num).val();
                mostrar[contadorArray] = $("#mostrar" + num).val();
                usuarioSolicitud[contadorArray] = $("#usuarioSolicitud" + num).val();
                cantidadPeticion[contadorArray] = $("#cantidad" + num).val();
                almacen[contadorArray] = $("#almacen" + num).val();
                cliente[contadorArray] = $("#cliente" + num).val();
                modelo[contadorArray] = $("#modelo" + num).val();
                mensajeria[contadorArray] = $("#mensajeria" + num).val();
                contadorArray++;
                tamano = tamano + 1;
            }
            c++;
        }
        if (tamano == id.length) {
            alert(cantidadSolicitada);
            $("#mensajes").load(controler, {"accion": "enviarToner", "nota": nota, "refaccion": refaccion, "cantidadPeticion": cantidadSolicitada, "almacen": almacen, "cantidad": cantidad}, function() {
                $("#contenidos").load(pagina, function() {
                    finished();
                });
            });
        } else {
            finished();
        }
    } else {
        finished();
        alert("Seleccione almenos una opción");
    }

}

function picking(pagina)
{
    loading("Cargando ...");
    var controler = "WEB-INF/Controllers/Controler_EntregaRefacciones.php";
    var id = new Array();
    var contador = 0;
    $("input:checkbox:checked").each(function() {
//cada elemento seleccionado
        id[contador] = $(this).val();
        contador++;
    });
    // alert(id.length);
    var c = 0;
    var tamano = 0;
    var ticket = new Array();
    var nota = new Array();
    var descripcion = new Array();
    var refaccion = new Array();
    var activo = new Array();
    var mostrar = new Array();
    var usuarioSolicitud = new Array();
    var cantidadPeticion = new Array();
    var almacen = new Array();
    var cliente = new Array();
    var modelo = new Array();
    var noSerie = new Array();
    var localidad = new Array();
    //$("#erroPeticion"+)
    var contadorArray = 0;
    if (id.length > 0) {
        while (c < id.length) {
            var num = id[c];
            var cantidadSolicitada = $("#cantidad" + num).val();
            var cantidadEntregada = $("#maximoPeticion" + num).val();
            if (parseInt(cantidadSolicitada) > parseInt(cantidadEntregada)) {
                $("#erroPeticion" + num).html("La cantidad debe ser menor a las restantes");
            } else {
                $("#erroPeticion" + num).html("");
                ticket[contadorArray] = $("#ticket" + num).val();
                nota[contadorArray] = $("#nota" + num).val();
                descripcion[contadorArray] = $("#descripcion" + num).val();
                refaccion[contadorArray] = $("#refaccion" + num).val();
                activo[contadorArray] = $("#activo" + num).val();
                mostrar[contadorArray] = $("#mostrar" + num).val();
                usuarioSolicitud[contadorArray] = $("#usuarioSolicitud" + num).val();
                cantidadPeticion[contadorArray] = $("#cantidad" + num).val();
                almacen[contadorArray] = $("#almacen" + num).val();
                cliente[contadorArray] = $("#cliente" + num).val();
                modelo[contadorArray] = $("#modelo" + num).val();
                noSerie[contadorArray] = $("#noSerie" + num).val();
                localidad[contadorArray] = $("#localidad" + num).val();
                contadorArray++;
                tamano = tamano + 1;
            }
            c++;
        }
        if (tamano == id.length) {
            $("#mensajes").load(controler, {"ticket": ticket, "nota": nota, "descripcion": descripcion, "refaccion": refaccion, "activo": activo, "mostrar": mostrar, "usuarioSolicitud": usuarioSolicitud, "cantidadPeticion": cantidadPeticion, "almacen": almacen, "claveCliente": cliente, "modelo": modelo, "noSerie": noSerie, "localidad": localidad}, function() {
                BuscarTicket(pagina);
            });
        } else {
            finished();
        }
    } else {
        finished();
        alert("Seleccione al menos una opción");
    }
}

function buscarUsuario()
{
    var usuario = $("#usuarioslc").val();
    var cliente = $("#cliente_ticket").val();
    var color = $("#ticket_color").val();
    var pagina = "hardware/lista_notasLista.php";
    loading("Cargando ...");
    $("#contenidos").load(pagina, {"usuario": usuario, "cliente": cliente, "color": color}, function() {
        finished();
    });
//alert(usuario +">"+cliente+">"+color)
}

function TonerEntregado(nota) {
    loading("Cargando ...");
    var controler = "WEB-INF/Controllers/Controler_Solicitud_Toner.php";
    var pagina = "admin/lista_toner_entregado.php";
    $("#mensajes").load(controler, {"idNota": nota, "accion": "TonerEntregado"}, function() {
        $("#contenidos").load(pagina, function() {
            finished();
        });
    });
}

function VerEnvioToner(pagina, idNota) {
    loading("Cargando ...");
    $("#contenidos").load(pagina, {"idNota": idNota}, function() {
        finished();
    });
}

function MostrarTransporte(tipo) {
    if (tipo == 1) {//propio    
        $("#propio").show();
        $("#mensajeria").hide();        
        $("#div_otro").hide();
        //addRulesPropio();
    } else if (tipo == 2) {//mensajeria
        $("#propio").hide();
        $("#mensajeria").show();        
        $("#div_otro").hide();
        //addRulesMensajeria();
    } else{
        $("#propio").hide();
        $("#mensajeria").hide();        
        $("#div_otro").show();
    }
}
function addRulesPropio() {
    $("#tranporteMensajeria").rules("remove");
    $("#noGuia").rules("remove");
    $("#conductor").rules('add', {
        required: true,
        messages: {
            required: " * Selecciona la conductor"
        }
    });
    $("#tranportepropio").rules('add', {
        required: true,
        messages: {
            required: " * Selecciona la fecha transporte"
        }
    });
}
function addRulesMensajeria() {
    $("#tranportepropio").rules("remove");
    $("#conductor").rules("remove");
    $("#tranporteMensajeria").rules('add', {
        required: true,
        messages: {
            required: " * Selecciona la mensajeria"
        }
    });
    $("#noGuia").rules('add', {
        required: true,
        messages: {
            required: " * Selecciona la fecha numero de guia"
        }
    });
}
function CambiarEstatusRefaccionToner(numLista, nota, refaccion, cantidad) {
    var cantidadAlmacen = $("#cantidadExix" + numLista).val();
    var estatus = $("#estatus_" + numLista).val();
    var solicitadas = $("#cantidadRestante" + numLista).val();
    var almacen = $("#almacen" + numLista).val();
//    alert(numLista + "  " + nota + "  " + refaccion + "  " + cantidad + "  " + cantidadExistente + "   " + estatus + "    " + solicitadas);
    if (estatus == 0) {
        $("#errorEstado" + numLista).html("Seleccione una acción");
    } else {
        $("#errorEstado" + numLista).html("");
        if (parseInt(cantidad) < parseInt(solicitadas)) {
            $("#errorCantidad" + numLista).html("La cantidad debe ser menor a la cantidad solicitada");
        } else {
            $("#errorCantidad" + numLista).html("");
            if (estatus == 68) {
                if (parseInt(solicitadas) == 0) {
                    $("#errorCantidad" + numLista).html("La cantidad debe ser mayor a 0");
                } else {
                    $("#errorCantidad" + numLista).html("");
                    RealizarPeticionToner(nota, refaccion, cantidad, cantidadAlmacen, solicitadas, estatus, almacen, null, null);
                }
            }
            else if (estatus == 21 && almacen == 0) {
                $("#errorAlmacen" + numLista).html("Seleccione un almacén");
            } else {
                $("#errorAlmacen" + numLista).html("");
                if (estatus == 21) {
                    if (cantidadAlmacen == "0" || parseInt(cantidadAlmacen) < parseInt(solicitadas)) {
                        $("#errorEstado" + numLista).html("El almacén no cuenta con toner suficientes y no se puede poner en este estado");
                    } else {
                        RealizarPeticionToner(nota, refaccion, cantidad, cantidadAlmacen, solicitadas, estatus, almacen, null, null);
                    }
                }
                else {
                    cantidadAlmacen = 0;
                    RealizarPeticionToner(nota, refaccion, cantidad, cantidadAlmacen, solicitadas, estatus, almacen, null, null);
                }
            }
        }

    }
}

function atenderNotaTonerSolicitado(pagina, enviar) {
    $("#boton_atender").hide();
    $("#mensajes").empty();
    var fila = new Array();
    var idNotaTicket = new Array();
    var componente = new Array();
    var componentes_cambiados = new Array();
    var cantidad = new Array();
    var series = new Array();
    var contador = 0;
    var valida = 0;
    var cliente = $("#cliente_ticket").val();
    var localidad = $("#localidad").val();
    var ticket = $("#busqueda_ticket").val();
    var n = parseInt($("#contador_solicitados").val());

    for (var i = 1; i < n; i++) {
        if ($('#ckTonerSeleccionado' + i).is(':checked') && $('#ckTonerSeleccionado' + i).val() != "" && $('#ckTonerSeleccionado' + i).val() != "0") {
            contador++;
            var str = $('#ckTonerSeleccionado' + i).val();
            var datos = str.split(" /** ");
            fila[contador] = datos[0];
            idNotaTicket[contador] = datos[1];

            if (datos[2] != $("#cambiarComponente_" + i).val()) {
                componentes_cambiados[contador] = $("#cambiarComponente_" + i).val() + "/**/" + datos[2];
                componente[contador] = $("#cambiarComponente_" + i).val();
            } else {
                componente[contador] = datos[2];
            }

            if ($("#serie_toner_" + i).length) {
                series[contador] = $("#serie_toner_" + i).val();
            }
            cantidad[contador] = datos[3];
        }
    }

    if (contador > 0) {
        var cantidadAlmacen = new Array();
        var estatus = new Array();
        var solicitadas = new Array();
        var almacen = new Array();
        $('#boton_atender').hide();
        for (var x = 0; x < fila.length; x++) {
            cantidadAlmacen[x] = $("#cantidadExix" + fila[x]).val();
            estatus[x] = $("#estatus_" + fila[x]).val();
            solicitadas[x] = $("#cantidadRestante" + fila[x]).val();
            almacen[x] = $("#almacen" + fila[x]).val();
            if (estatus[x] == "0") {
                $("#errorEstado" + fila[x]).html("Seleccione una acción");
            } else {
                $("#errorEstado" + fila[x]).html("");
                if (parseInt(cantidad[x]) < parseInt(solicitadas[x])) {
                    $("#errorCantidad" + fila[x]).html("La cantidad debe ser menor a la cantidad solicitada");
                } else {
                    $("#errorCantidad" + fila[x]).html("");
                    if (estatus[x] == "68") {
                        if (parseInt(solicitadas[x]) == "0" || parseInt(solicitadas[x]) < 0) {
                            $("#errorCantidad" + fila[x]).html("La cantidad debe ser mayor a 0");
                        } else {
                            $("#errorCantidad" + fila[x]).html("");
                            valida++;
                        }
                    } else if (estatus[x] == "21" && almacen[x] == "0") {
                        $("#errorAlmacen" + fila[x]).html("Seleccione un almacÃ©n");
                    } else {
                        $("#errorAlmacen" + fila[x]).html("");
                        if (estatus[x] == 21) {
                            if (parseInt(solicitadas[x]) < 1) {
                                $("#errorCantidad" + fila[x]).html("La cantidad debe ser mayor a 0");
                            } else {
                                $("#errorCantidad" + fila[x]).html("");
                                if (cantidadAlmacen[x] == "0" || parseInt(cantidadAlmacen[x]) < parseInt(solicitadas[x])) {
                                    $("#errorEstado" + fila[x]).html("El almacÃ©n no cuenta con toner suficientes y no se puede poner en este estado");
                                } else {
                                    $("#errorEstado" + fila[x]).html("");
                                    valida++;
                                }
                            }
                        }
                        else {
                            if (estatus[x] == 20 && parseInt(solicitadas[x]) > 0) {
                                cantidadAlmacen = 0;
                                valida++;
                                $("#errorCantidad" + fila[x]).html("");
                            } else {
                                $("#errorCantidad" + fila[x]).html("La cantidad debe ser mayor a 0");
                            }
                        }
                    }
                }
            }
        }

        if (valida == fila.length - 1) {
            RealizarPeticionToner(idNotaTicket, componente, cantidad, cantidadAlmacen, solicitadas, estatus, almacen, cliente, localidad, ticket, componentes_cambiados, series, enviar, pagina);
        } else {
            // alert("No enviar");
            $('#boton_atender').show();
        }
        //enviar datos al controler
    } else {
        //alert("Seleccione minimo una solicitud.");
        $('#boton_atender').show();
    }
}

function antender_enviar_toner(arrayidNota) {
    $("#contenidos").load("almacen/altaEnvioToner.php", {"idNota_array": arrayidNota}, function() {
        finished();
    });
}
