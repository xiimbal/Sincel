$(document).ready(function() {
    var form = "#formAgregarNota";
    var paginaExito = $("#paginaLista").val(); //"hardware/lista_validarRefaccion.php";  


    var controlador = "";
    var pag = "";
    var tipo = $("#externa").val();
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
            diagnostico: {required: true, maxlength: 400, minlength: 3},
            estatus: {selectcheck: true},
            cantidad1: {cantidadRefaccion: true, integer: true},
            refaccion1: {Refac: true}
        },
        messages: {
            fecha: {required: " * Ingrese la fecha"},
            hora: {required: " * Ingrese la hora", number: " * Ingresa s\u00f3lo n\u00fameros"},
            diagnostico: {required: " * Ingrese el diagnostico o soluci\u00f3n", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            event.preventDefault();
            var b = $("#botonGuardar").val();
            var tipoSolicitud = $("#estatus").val();
            var idTicket = "";
            if (tipoSolicitud === '9')
                var tamanoTabla = $("#nuevaRefaccion tr").length;
            else if (tipoSolicitud === '67') {
                var tamanoTabla = $("#nuevaSuministro tr").length;
                idTicket = $("#idTicket").val();
//                validarNota("hardware/lista_validarRefaccion.php", "", 1);
            }
            //var banderaDatos = 1;
            if ($("#estatus").val() == "59" || $("#estatus").val() == "16") {
                var tick = $("#idTicket").val();
                $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"idTicket": tick, "buscar": "notaTicketAbierto"}).done(function(data1) {                   
                    //alert(data1);
                    if (data1 != "0") {
                        $("#MesajeTicekt").html("El ticket tiene un flujo en proceso, no se puede cerrar");
                        $(function() {
                            $("#MesajeTicekt").dialog({
                                resizable: false,
                                height: 200,
                                modal: true,
                                closeOnEscape: false,
                                open: function(event, ui) {
                                    $(".ui-dialog-titlebar-close", ui.dialog).hide();
                                },
                                buttons: {
                                    /*"Continuar": function() {
                                        $.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                                                .done(function(data) {
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
                                                                });
                                                            }
                                                            else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                                                $("#contenidos").load(paginaExito, function() {
                                                                    finished();
                                                                });
                                                            } else {
                                                                if (pag === 1) {
                                                                    var idTicketNota = $("#idTicket").val();
                                                                    if(paginaExito != "almacen/lista_refaccionesSolicitadas.php"){
                                                                        paginaExito = "mesa/lista_ticket.php";
                                                                    }
                                                                    $("#contenidos").load(paginaExito, {"idTicket": idTicketNota}, function() {
                                                                        finished();
                                                                    });
                                                                }
                                                                else if (pag === 2)
                                                                {
                                                                    function redireccion() {
                                                                        document.location.href = paginaExito;
                                                                    }
                                                                    setTimeout(redireccion(), 1000);
                                                                    finished();
                                                                }

                                                            }
                                                        } else {
                                                            finished();
                                                        }
                                                    }
                                                });//post agregar
                                        $(this).dialog("close");
                                    },*/
                                    "Acpetar": function() {
                                        finished();
                                        banderaDatos = 1;
                                        $(this).dialog("close");
                                    }
                                }
                            });
                        });

                    } else {
                        $.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                                .done(function(data) {
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
                                                });
                                            }
                                            else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                                $("#contenidos").load(paginaExito, function() {
                                                    finished();
                                                });
                                            } else {
                                                if (pag === 1) {
                                                    var idTicketNota = $("#idTicket").val();
                                                    if(paginaExito != "almacen/lista_refaccionesSolicitadas.php"){
                                                        paginaExito = "mesa/lista_ticket.php";
                                                    }
                                                    $("#contenidos").load(paginaExito, {"idTicket": idTicketNota, "mostrar":true}, function() {
                                                        $(".button").button();
                                                        finished();                                                        
                                                    });
                                                }
                                                else if (pag === 2)
                                                {
                                                    function redireccion() {
                                                        document.location.href = paginaExito;
                                                    }
                                                    setTimeout(redireccion(), 1000);
                                                    finished();
                                                }

                                            }
                                        } else {
                                            finished();
                                        }
                                    }
                                });//post agregar
                    }
                });//
            } else {
                $.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                        .done(function(data) {
                            //alert(data);
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
                                        });
                                    }
                                    else if (paginaExito == "hardware/lista_validarRefaccion.php") {
                                        $("#contenidos").load(paginaExito, function() {
                                            finished();
                                        });
                                    } else {
                                        if (pag === 1) {
                                            var idTicketNota = $("#idTicket").val();
                                            if(paginaExito != "almacen/lista_refaccionesSolicitadas.php"){
                                                paginaExito = "mesa/lista_ticket.php";
                                            }
                                            $("#contenidos").load(paginaExito, {"idTicket": idTicketNota, "mostrar":true}, function() {
                                                $(".button").button();
                                                finished();                                                
                                            });
                                        }
                                        else if (pag === 2)
                                        {
                                            function redireccion() {
                                                document.location.href = paginaExito;
                                            }
                                            setTimeout(redireccion(), 1000);
                                            finished();
                                        }

                                    }
                                } else {
                                    $('#mensajes').html(data);
                                    finished();
                                }
                            }
                        });//post agregar
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
    var id = $("#estatus").val();
    if (id === "9")
        $("#refacciones").show();
    else
        $("#refacciones").hide();
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

}
var numeroRefaccion = $("#tamano").val();
function otraRefaccion(tipo) {
    var datosBD = new Array();
    datosBD = ArregloCondatos();
    //  alert(datosBD.toString());
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




//     