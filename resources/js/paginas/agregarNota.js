$(document).ready(function() {
    var form = "#formAgregarNota";
    var paginaExito = $("#paginaLista").val();
    var tipo;
    var pag;

    var controlador = "";
    tipo = $("#externa").val();
    if (tipo === "interna") {
        controlador = "WEB-INF/Controllers/Controler_AgregarNota.php";
        pag = 1;
    }
    else if (tipo === "externa") {
        controlador = "../WEB-INF/Controllers/Controler_AgregarNota.php";
        pag = 2;
    }

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    jQuery.validator.addMethod('integer', function(value, element, param) {
        if ($("#estatus").val() == "9") {
            if ((value == parseInt(value, 10)))
                return true;
            else
                return false;
        }
        else
            return true
    }, 'Ingresa solo numeros');



    $.validator.addMethod("Refac", function(value, element) {
        if ($("#refacciones").is(':visible')) {
            if ($("#refaccion1").val() != '') {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Ingresa la refacción");
    $.validator.addMethod("canti", function(value, element) {
        if ($("#refacciones").is(':visible')) {
            if ($("#cantidad1").val() != '') {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }, "* Ingrese la cantidad ");


    $.validator.addMethod("validarRefaccion", function(value, element) {
        if ($("#estatus").val() == "9") {
            if ($("#refaccion1").val() != '0')
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, "* Selecciona un elemento de la lista");

    $.validator.addMethod("validarContadorNegro", function(value) {
        if($("#idNotaAnterior").length && $("#idNotaAnterior").val()!=""){//Si se esta editando una nota para validar refacciones, los contadores no son obligatorios
            return true;
        }else{
            if ($("#estatus").val() == "9") {
                if (value == "") {
                    return false;
                } else {
                    return true;
                }
            } else if ($("#estatus").val() == "16") {
                if ($("#txt_tipo_lectura").val() == "1") {
                    if (value == "") {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }, "*Ingrese el contador negro");

    $.validator.addMethod("validarAntNuevoNeg", function(value) {
        if($("#idNotaAnterior").length && $("#idNotaAnterior").val()!=""){//Si se esta editando una nota para validar refacciones, los contadores no son obligatorios
            return true;
        }else{
            if ($("#estatus").val() == "9") {
                var contAntN = $("#txt_negro_anterior").val();
                if (parseInt(value) < parseInt(contAntN)) {
                    return false;
                } else {
                    return true;
                }
            } else if ($("#estatus").val() == "16") {
                var contAntN = $("#txt_negro_anterior").val();
                if ($("#txt_tipo_lectura").val() == "1") {
                    if (parseInt(value) < parseInt(contAntN)) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }, "*El contador nuevo debe ser mayor o igual al anterior");


    $.validator.addMethod("validarContadorColor", function(value) {
        if($("#idNotaAnterior").length && $("#idNotaAnterior").val()!=""){//Si se esta editando una nota para validar refacciones, los contadores no son obligatorios
            return true;
        }else{
            if ($("#estatus").val() == "9") {
                if (value == "") {
                    return false;
                } else {
                    return true;
                }
            } else if ($("#estatus").val() == "16") {
                if ($("#txt_tipo_lectura").val() == "1") {
                    if (value == "") {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }, "*Ingrese el contador color");

    $.validator.addMethod("validarAntNuevocolor", function(value) {
        if($("#idNotaAnterior").length && $("#idNotaAnterior").val()!=""){//Si se esta editando una nota para validar refacciones, los contadores no son obligatorios
            return true;
        }else{
            if ($("#estatus").val() == "9") {
                if ($("#txt_tipo_equipo").val() == "1") {
                    var conColorAn = $("#txt_color_anterior").val();
                    if (parseInt(value) < parseInt(conColorAn)) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            } else if ($("#estatus").val() == "16") {
                if ($("#txt_tipo_equipo").val() == "1") {
                    var conColorAn = $("#txt_color_anterior").val();
                    if ($("#txt_tipo_lectura").val() == "1") {
                        if (parseInt(value) < parseInt(conColorAn)) {
                            return false;
                        } else {
                            return true;
                        }
                    }
                    else {
                        return true;
                    }
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }, "**El contador nuevo debe ser mayor o igual al anterior");

    $.validator.addMethod("cantidadRefaccion", function(value, element) {
        if ($("#estatus").val() == "9") {
            if ($("#cantidad1").val() != '')
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, "* Ingrese la cantidad");
    
    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            fecha: {required: true},
            hora: {required: true},
            diagnostico: {required: true, maxlength: 600, minlength: 3},
            estatus: {selectcheck: true},
            cantidad1: {cantidadRefaccion: true, integer: true},
            refaccion1: {Refac: true},
            txt_negro_nuevo: {validarContadorNegro: true, validarAntNuevoNeg: true, number: true},
            txt_color_nuevo: {validarContadorColor: true, validarAntNuevocolor: true, number: true}
        },
        messages: {
            fecha: {required: " * Ingrese la fecha"},
            hora: {required: " * Ingrese la hora", number: " * Ingresa s\u00f3lo n\u00fameros"},
            diagnostico: {required: " * Ingrese el diagnostico o soluci\u00f3n", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            txt_negro_nuevo: {number: " * Ingresa s\u00f3lo n\u00fameros"},
            txt_color_nuevo: {number: " * Ingresa s\u00f3lo n\u00fameros"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            $("#botonGuardar").hide();
            loading("Cargando ...");
            event.preventDefault();
            var realizarAccion = true;
            var b = $("#botonGuardar").val();
            var tipoSolicitud = $("#estatus").val();
            var idTicket = "";
            if (tipoSolicitud === '9'){
                //Si es una solicitud de refacción vamos a validar el rendimiento
                /*realizarAccion = false;
                validarRendimientoToner(); hay que arreglar esta funcion porque duplica/triplica las refacciones*/
                var tamanoTabla = $("#nuevaRefaccion tr").length;
            }else if (tipoSolicitud === '67') {
                var tamanoTabla = $("#nuevaSuministro tr").length;
                idTicket = $("#idTicket").val();
//                validarNota("hardware/lista_validarRefaccion.php", "", 1);
            }
      
            if(realizarAccion){
            //var banderaDatos = 1;
                if ($("#estatus").val() == "59" || $("#estatus").val() == "16") {
                    var tick = $("#idTicket").val();
                    $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"idTicket": tick, "buscar": "notaTicketAbierto", idEstatus:$("#estatus").val()}).done(function(data1) {
                        if (data1 != "0") {
                            var mensaje = "El ticket tiene un flujo en proceso, no se puede cerrar";
                            mensaje = "El ticket tiene un flujo pendiente de "+data1;
                            
                            $("#MesajeTicekt").html(mensaje);
                            $(function() {
                                $("#MesajeTicekt").dialog({
                                    resizable: false,
                                    height: 300,
                                    modal: true,
                                    closeOnEscape: false,
                                    open: function(event, ui) {
                                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                                    },
                                    buttons: {
                                        "Aceptar": function() {
                                            finished();
                                            $("#botonGuardar").show();
                                            banderaDatos = 1;
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            });

                        } else {
                            var formData = new FormData($('form')[0]);
                            $.ajax({
                            url: controlador+"?totalrefacciones="+tamanoTabla+"&boton="+b,  //Server script to process data
                            type: 'POST',
                            xhr: function() {  // Custom XMLHttpRequest
                                var myXhr = $.ajaxSettings.xhr();
                                /*if(myXhr.upload){ // Check if upload property exists
                                    myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                                }*/
                                return myXhr;
                            },
                            //Ajax events
                            //beforeSend: beforeSendHandler,
                            //$.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                            success: function(data) {
                                $('#mensajes').html(data);
                                        if (idTicket != "") {
                                            validarNota(paginaExito, idTicket, 2);
                                        } else {
                                            $('#mensajes').html(data);
                                            if (data.toString().indexOf("Error:") === -1) {
                                                if ($("#area").val() != "") {
                                                    var idTicketNota = $("#idTicket").val();
                                                    var area = $("#area").val();
                                                    $('#contenidos').load("mesa/alta_ticketphp.php", {"idTicket": idTicketNota, "area": area, "detalle": "0"}, function() {
                                                        finished();
                                                        $("#botonGuardar").show();
                                                    });
                                                }
                                                else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                                    $("#contenidos").load(paginaExito, function() {
                                                        finished();
                                                        $("#botonGuardar").show();
                                                    });
                                                } else {
                                                    if (pag === 1) {
                                                        var idTicketNota = $("#idTicket").val();
                                                        if (paginaExito != "almacen/lista_refaccionesSolicitadas.php") {
                                                            if($("#paginaLista").length){
                                                                paginaExito = $("#paginaLista").val(); 
                                                            }else{
                                                                paginaExito = "mesa/lista_ticket.php";
                                                            }
                                                        }
                                                        $("#contenidos").load(paginaExito, {"idTicket": idTicketNota, "mostrar": true}, function() {
                                                            $(".button").button();
                                                            finished();
                                                            $("#botonGuardar").show();
                                                        });
                                                    }
                                                    else if (pag === 2)
                                                    {
                                                        function redireccion() {
                                                            document.location.href = paginaExito;
                                                        }
                                                        setTimeout(redireccion(), 1000);
                                                        finished();
                                                        $("#botonGuardar").show();
                                                    }

                                                }
                                            } else {
                                                finished();
                                                $("#botonGuardar").show();
                                            }
                                        }
                                    },
                                    //error: errorHandler,
                                    // Form data
                                    data: formData,
                                    //Options to tell jQuery not to process data or worry about content-type.
                                    cache: false,
                                    contentType: false,
                                    processData: false
                                });//post agregar
                        }
                    });//
                } else {
                    var formData = new FormData($('form')[0]);
                    $.ajax({
                    url: controlador+"?totalrefacciones="+tamanoTabla+"&boton="+b,  //Server script to process data
                    type: 'POST',
                    xhr: function() {  // Custom XMLHttpRequest
                        var myXhr = $.ajaxSettings.xhr();
                        /*if(myXhr.upload){ // Check if upload property exists
                            myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                        }*/
                        return myXhr;
                    },
                    //Ajax events
                    //beforeSend: beforeSendHandler,
                    //$.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                    success: function(data) {
                        $('#mensajes').html(data);
                        if (idTicket != "") {
                            validarNota(paginaExito, idTicket, 2);
                        } else {
                            $('#mensajes').html(data);
                            if (data.toString().indexOf("Error:") === -1) {
                                if ($("#area").val() != "") {
                                    var idTicketNota = $("#idTicket").val();
                                    var area = $("#area").val();
                                    $('#contenidos').load("mesa/alta_ticketphp.php", {"idTicket": idTicketNota, "area": area, "detalle": "0"}, function() {
                                        finished();
                                        $("#botonGuardar").show();
                                    });
                                }
                                else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                    $("#contenidos").load(paginaExito, function() {
                                        finished();
                                        $("#botonGuardar").show();
                                    });
                                } else {
                                    if (pag === 1) {
                                        var idTicketNota = $("#idTicket").val();
                                        if (paginaExito != "almacen/lista_refaccionesSolicitadas.php") {
                                            if($("#paginaLista").length){
                                                paginaExito = $("#paginaLista").val(); 
                                            }else{
                                                paginaExito = "mesa/lista_ticket.php";
                                            }
                                        }
                                        $("#contenidos").load(paginaExito, {"idTicket": idTicketNota, "mostrar": true}, function() {
                                            $(".button").button();
                                            finished();
                                            $("#botonGuardar").show();
                                        });
                                    }
                                    else if (pag === 2)
                                    {
                                        function redireccion() {
                                            document.location.href = paginaExito;
                                        }
                                        setTimeout(redireccion(), 1000);
                                        finished();
                                        $("#botonGuardar").show();
                                    }

                                }
                            } else {
                                $('#mensajes').html(data);
                                finished();
                                $("#botonGuardar").show();
                            }
                        }
                    },
                    //error: errorHandler,
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                    });//post agregar
                }
            }
        }
    });
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

    mostrarRefacciones();
    numeroRefaccion = $("#tamano").val();
});

function mostrarRefacciones()
{
    var mostrar_lecturas = true;
    if ($("#mostrar_contadores").length && $("#mostrar_contadores").val() == "0") {
        mostrar_lecturas = false;
    }
    
    var id = $("#estatus").val();
    if (id === "9") {
        $("#refacciones").show();
        if(mostrar_lecturas){
            $("#div_contadores").show();
        }
    } else {
        $("#refacciones").hide();
        if(mostrar_lecturas){
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
    
     if (id === "275") //Loyalty-> gastos de viaticos
        $("#kmdiv").show();
    else
        $("#kmdiv").hide();
    
    if (id === "276") //Loyalty-> gastos de viaticos
        $("#tiempoE").show();
    else
        $("#tiempoE").hide();
    
    if (id === "277") //Loyalty-> gastos de viaticos
        $("#noBoleto").show();
    else
        $("#noBoleto").hide();
    
    if (id === "16"){
        if(mostrar_lecturas){
            $("#div_contadores").show();
        }
    }else {
        if (id !== "9") {
            if(mostrar_lecturas){
                $("#div_contadores").hide();
            }
        }
    }

}
var numeroRefaccion = $("#tamano").val();
function otraRefaccion(tipo) {
    var datosBD = new Array();
    datosBD = ArregloCondatos();
    var tipoTicket = "";
    if (tipo === "1") {
        tipoTicket = "resources/images/Erase.png";
    }
    if (tipo === "67") {
        tipoTicket = "../resources/images/Erase.png";
    }
    var newRow = "<tr id='filaRefaccion_" + numeroRefaccion + "'><td>Refacción:&nbsp;&nbsp;&nbsp;&nbsp;</td>" +
            "<td><input id='refaccion" + numeroRefaccion + "' name='refaccion" + numeroRefaccion + "' value='' class='refaccion' style='width: 250px'/></td>" +
            "<td>Cantidad:</td><td><input type='text' style='max-width: 100px' id='cantidad" + numeroRefaccion + "' name='cantidad" + numeroRefaccion + "' /></td>" +
            "<td><img class='imagenMouse' src='" + tipoTicket + "' title='Eliminar refacción' onclick='deleteRow(" + numeroRefaccion + ")' style='float: right; cursor: pointer;' /> </td></tr>";
    $('#nuevaRefaccion tr:last').after(newRow);//add the new row
    $('#refaccion1 option').clone().appendTo('#refaccion' + numeroRefaccion);

    var nombre = "#refaccion" + numeroRefaccion;
    $(nombre).rules("add", {
        required: true,
        messages: {required: " * Ingrese la refaccción"}
    });
    nombre = "#cantidad" + numeroRefaccion;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {required: " * Ingrese la cantidad", number: " * S\u00f3lo puedes ingresar n\u00fameros"}
    });

    $("#refaccion" + numeroRefaccion).autocomplete({
        source: datosBD,
        minLength: 2
    });
    numeroRefaccion++;

}
function deleteRow(numero) {
    var fila = "filaRefaccion_" + numero;
    var trs = $("#nuevaRefaccion tr").length;
    if (trs > 1) {
        $("#" + fila).remove();
        //numeroRefaccion--;
    }

}
function deleteRowSuministro(numero) {
    var fila = "filaSuministro" + numero;
    var trs = $("#nuevaSuministro tr").length;
    if (trs > 1) {
        $("#" + fila).remove();
        //numeroRefaccion--;
    }

}
function guardarValidar(nota)
{
    var controler = "../WEB-INF/Controllers/Controler_GuardarValidar.php";
    var paginaExito = "hardware/lista_validarRefaccion.php";
    loading("Cargando ...");
    $("#mensajes").load(controler, {"nota": nota}, function() {
        $("#contenidos").load(paginaExito, function() {
            finished();
        });
    });
}
var numeroSuministro = $("#tamano").val();
function AgregarSuministro(tipo) {
    var tipoTicket = "";
    if (tipo === "1")
        tipoTicket = "resources/images/Erase.png";
    else if (tipo === "67")
        tipoTicket = "../resources/images/Erase.png";
    var newRow = "<tr id='filaSuministro" + numeroSuministro + "'>" +
            "<td>Suministro:&nbsp;&nbsp;&nbsp;&nbsp;</td><td>" +
            "<select id='suministro" + numeroSuministro + "' name='suministro" + numeroSuministro + "' style='width: 600px' class='filtroComponentes'>" +
            "</select></td>" +
            "<td>Cantidad:</td><td><input type='text' style='max-width: 100px' id='cantidadsuministro" + numeroSuministro + "' name='cantidadsuministro" + numeroSuministro + "' /></td>" +
            "<td><img class='imagenMouse' src='" + tipoTicket + "' title='Eliminar refacción' onclick='deleteRowSuministro(" + numeroSuministro + ")' style='float: right; cursor: pointer;' /> </td></tr>";
    $('#nuevaSuministro tr:last').after(newRow);//add the new row
    $('#suministro1 option').clone().appendTo('#suministro' + numeroSuministro);
    var nombre = "#suministro" + numeroSuministro;
    $(nombre).rules("add", {
        selectcheck: true,
        messages: {required: " * Seleccione un elemento de la lista"}
    });
    nombre = "#cantidadsuministro" + numeroSuministro;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {required: " * Ingrese la cantidad", number: " * S\u00f3lo puedes ingresar n\u00fameros"}
    });

    $("#suministro" + numeroSuministro).multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    numeroSuministro++;

}
function cancelarNota(pagina, idTicket, area) {
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"idTicket": idTicket, "area": area, "detalle": "0"}, function() {
        finished();
    });
}

function validarRendimientoToner() {
    var contadorAnterior = "";
    var contadorNuevo = "";
    var totalContadores = "";
    var nota = "";
    var paginaExito = $("#paginaLista").val();
    var tipo;
    var pag;

    var controlador = "";
    tipo = $("#externa").val();
    if (tipo === "interna") {
        controlador = "WEB-INF/Controllers/Controler_AgregarNota.php";
        pag = 1;
    }
    else if (tipo === "externa") {
        controlador = "../WEB-INF/Controllers/Controler_AgregarNota.php";
        pag = 2;
    }
    
    var tamanoTabla = $("#nuevaRefaccion tr").length;
    var b = $("#botonGuardar").val();
    var noSerieEquipo = $('#txt_serie').val();
    var idTicket = "";
    
    for(var i = 1; i <= tamanoTabla; i++){    
        var aux = $("#refaccion"+i).val();
        var noParte = aux.split(" / ");
        contadorNuevo = $("#txt_negro_nuevo").val();

        $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"noParteComponenteRendimiento2": noParte[1], "NoSerieEquipo":noSerieEquipo}).done(function(data) {//obtener el rendimiento del toner
            var respuesta = data.split("//");
            var rendimiento = respuesta[1];
            contadorAnterior = parseInt(respuesta[0]);
            if (parseInt(rendimiento) > 0) {
                if(contadorAnterior!=null && contadorAnterior!=0 && contadorNuevo!=null && contadorNuevo!=""){
                    totalContadores = parseInt(contadorNuevo) - parseInt(contadorAnterior);
                }else{
                    totalContadores = "desconocido";
                    nota = "No se encontró un contador anterior de está refacción usada en el equipo del ticket";
                }
                var porcentaje = (parseInt(totalContadores) * 100) / parseInt(rendimiento);
                $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"porcRendimiento": "0"}).done(function(porcentajeMinimo) {//obtener porcentaje de rendimiento de los toner
                    if (parseInt(porcentaje) > parseInt(porcentajeMinimo) || porcentajeMinimo < 1) {
                        var formData = new FormData($('form')[0]);
                        $.ajax({
                        url: controlador+"?totalrefacciones="+tamanoTabla+"&boton="+b,  //Server script to process data
                        type: 'POST',
                        xhr: function() {  // Custom XMLHttpRequest
                            var myXhr = $.ajaxSettings.xhr();
                            /*if(myXhr.upload){ // Check if upload property exists
                                myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                            }*/
                            return myXhr;
                        },
                        //Ajax events
                        //beforeSend: beforeSendHandler,
                        //$.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                        success: function(data) {
                            $('#mensajes').html(data);
                            if (idTicket != "") {
                                validarNota(paginaExito, idTicket, 2);
                            } else {
                                $('#mensajes').html(data);
                                if (data.toString().indexOf("Error:") === -1) {
                                    if ($("#area").val() != "") {
                                        var idTicketNota = $("#idTicket").val();
                                        var area = $("#area").val();
                                        $('#contenidos').load("mesa/alta_ticketphp.php", {"idTicket": idTicketNota, "area": area, "detalle": "0"}, function() {
                                            finished();
                                            $("#botonGuardar").show();
                                        });
                                    }
                                    else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                        $("#contenidos").load(paginaExito, function() {
                                            finished();
                                            $("#botonGuardar").show();
                                        });
                                    } else {
                                        if (pag === 1) {
                                            var idTicketNota = $("#idTicket").val();
                                            if (paginaExito != "almacen/lista_refaccionesSolicitadas.php") {
                                                if($("#paginaLista").length){
                                                    paginaExito = $("#paginaLista").val(); 
                                                }else{
                                                    paginaExito = "mesa/lista_ticket.php";
                                                }
                                            }
                                            $("#contenidos").load(paginaExito, {"idTicket": idTicketNota, "mostrar": true}, function() {
                                                $(".button").button();
                                                finished();
                                                $("#botonGuardar").show();
                                            });
                                        }
                                        else if (pag === 2)
                                        {
                                            function redireccion() {
                                                document.location.href = paginaExito;
                                            }
                                            setTimeout(redireccion(), 1000);
                                            finished();
                                            $("#botonGuardar").show();
                                        }

                                    }
                                } else {
                                    $('#mensajes').html(data);
                                    finished();
                                    $("#botonGuardar").show();
                                }
                            }
                        },
                        //error: errorHandler,
                        // Form data
                        data: formData,
                        //Options to tell jQuery not to process data or worry about content-type.
                        cache: false,
                        contentType: false,
                        processData: false
                        });//post agregar
                    } else {
                        //Enviar el correo
                    $("<input type='hidden' value='1' />")
                        .attr("id", "correoBebe")
                        .attr("name", "correoBebe")
                        .appendTo("#formAgregarNota");
                    $("<input type='hidden' value='"+totalContadores+"' />")
                        .attr("id", "totalPaginas12")
                        .attr("name", "totalPaginas12")
                        .appendTo("#formAgregarNota");
                    $("<input type='hidden' value='"+rendimiento+"' />")
                        .attr("id", "rendimientoTotal12")
                        .attr("name", "rendimientoTotal12")
                        .appendTo("#formAgregarNota");
                    //controler(controlador+"?incidencia", paginaExito, form);
                    var formData = new FormData($('form')[0]);
                    $.ajax({
                    url: controlador+"?totalrefacciones="+tamanoTabla+"&boton="+b+"&data1="+noParte[1]+"&data3="+porcentajeMinimo,  //Server script to process data
                    type: 'POST',
                    xhr: function() {  // Custom XMLHttpRequest
                        var myXhr = $.ajaxSettings.xhr();
                        /*if(myXhr.upload){ // Check if upload property exists
                            myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                        }*/
                        return myXhr;
                    },
                    //Ajax events
                    //beforeSend: beforeSendHandler,
                    //$.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                    success: function(data) {
                        $('#mensajes').html(data);
                        if (idTicket != "") {
                            validarNota(paginaExito, idTicket, 2);
                        } else {
                            $('#mensajes').html(data);
                            if (data.toString().indexOf("Error:") === -1) {
                                if ($("#area").val() != "") {
                                    var idTicketNota = $("#idTicket").val();
                                    var area = $("#area").val();
                                    $('#contenidos').load("mesa/alta_ticketphp.php", {"idTicket": idTicketNota, "area": area, "detalle": "0"}, function() {
                                        finished();
                                        $("#botonGuardar").show();
                                    });
                                }
                                else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                    $("#contenidos").load(paginaExito, function() {
                                        finished();
                                        $("#botonGuardar").show();
                                    });
                                } else {
                                    if (pag === 1) {
                                        var idTicketNota = $("#idTicket").val();
                                        if (paginaExito != "almacen/lista_refaccionesSolicitadas.php") {
                                            if($("#paginaLista").length){
                                                paginaExito = $("#paginaLista").val(); 
                                            }else{
                                                paginaExito = "mesa/lista_ticket.php";
                                            }
                                        }
                                        $("#contenidos").load(paginaExito, {"idTicket": idTicketNota, "mostrar": true}, function() {
                                            $(".button").button();
                                            finished();
                                            $("#botonGuardar").show();
                                        });
                                    }
                                    else if (pag === 2)
                                    {
                                        function redireccion() {
                                            document.location.href = paginaExito;
                                        }
                                        setTimeout(redireccion(), 1000);
                                        finished();
                                        $("#botonGuardar").show();
                                    }

                                }
                            } else {
                                $('#mensajes').html(data);
                                finished();
                                $("#botonGuardar").show();
                            }
                        }
                    },
                    //error: errorHandler,
                    // Form data
                    data: formData,
                    //Options to tell jQuery not to process data or worry about content-type.
                    cache: false,
                    contentType: false,
                    processData: false
                    });//post agregar
                    }
                });
            } else {
                var formData = new FormData($('form')[0]);
                $.ajax({
                url: controlador+"?totalrefacciones="+tamanoTabla+"&boton="+b,  //Server script to process data
                type: 'POST',
                xhr: function() {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    /*if(myXhr.upload){ // Check if upload property exists
                        myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                    }*/
                    return myXhr;
                },
                //Ajax events
                //beforeSend: beforeSendHandler,
                //$.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                success: function(data) {
                    $('#mensajes').html(data);
                    if (idTicket != "") {
                        validarNota(paginaExito, idTicket, 2);
                    } else {
                        $('#mensajes').html(data);
                        if (data.toString().indexOf("Error:") === -1) {
                            if ($("#area").val() != "") {
                                var idTicketNota = $("#idTicket").val();
                                var area = $("#area").val();
                                $('#contenidos').load("mesa/alta_ticketphp.php", {"idTicket": idTicketNota, "area": area, "detalle": "0"}, function() {
                                    finished();
                                    $("#botonGuardar").show();
                                });
                            }
                            else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                $("#contenidos").load(paginaExito, function() {
                                    finished();
                                    $("#botonGuardar").show();
                                });
                            } else {
                                if (pag === 1) {
                                    var idTicketNota = $("#idTicket").val();
                                    if (paginaExito != "almacen/lista_refaccionesSolicitadas.php") {
                                        if($("#paginaLista").length){
                                            paginaExito = $("#paginaLista").val(); 
                                        }else{
                                            paginaExito = "mesa/lista_ticket.php";
                                        }
                                    }
                                    $("#contenidos").load(paginaExito, {"idTicket": idTicketNota, "mostrar": true}, function() {
                                        $(".button").button();
                                        finished();
                                        $("#botonGuardar").show();
                                    });
                                }
                                else if (pag === 2)
                                {
                                    function redireccion() {
                                        document.location.href = paginaExito;
                                    }
                                    setTimeout(redireccion(), 1000);
                                    finished();
                                    $("#botonGuardar").show();
                                }

                            }
                        } else {
                            $('#mensajes').html(data);
                            finished();
                            $("#botonGuardar").show();
                        }
                    }
                },
                //error: errorHandler,
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
                });//post agregar
            }
        });
    }
}


//     