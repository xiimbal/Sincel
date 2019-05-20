$(document).ready(function() {
    $(".boton").button();/*Estilo de botones*/
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
    oTable = $('#tAlmacen1').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 10,
        "aaSorting": [[0, "desc"]]
    });
    var form = "#frmAltaTicket";
    var paginaExito = "mesa/lista_ticket.php";
    var controlador = "WEB-INF/Controllers/Controler_NuevoTicket.php";
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");



    jQuery.validator.addMethod('emailResp', function(value) {
        if ($("#correoElectronico").val() !== "") {
            var filter = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
            if (filter.test($("#correoElectronico").val()))
                return true;
            else
                return false;
        } else
            return true;
    }, " * Ingrese un correo válido");


    jQuery.validator.addMethod('emailAtencion', function(value) {
        if ($("#txtCorreoElectronico").val() !== "") {
            var filter = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
            if (filter.test($("#txtCorreoElectronico").val()))
                return true;
            else
                return false;
        } else
            return true;
    }, " * Ingrese un correo válido");

    jQuery.validator.addMethod('NoSerieEquipo', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtNoSerieEquipoFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                return true;
//                if ($("#txtNoSerieEquipoToner").val() !== "")
//                    return true;
//                else
//                    return false;
            }
        } else
            return true;
    }, " * Ingrese el no. serie equipo ");
    jQuery.validator.addMethod('modelo', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtModeloFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                return true;
//                if ($("#txtModeloToner").val() !== "")
//                    return true;
//                else
//                    return false;
            }
        } else
            return true;
    }, " * Ingrese el modelo del equipo ");
    jQuery.validator.addMethod('domicilio', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtDomicilioFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtDomicilioToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");

    jQuery.validator.addMethod('claveCliente', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtClaveClienteFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtClaveClienteToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");
    jQuery.validator.addMethod('colonia', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtColoniaFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtColoniaToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");
    jQuery.validator.addMethod('calle', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtCalleFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtCalleToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");

    jQuery.validator.addMethod('delegacion', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtDelegacionFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtDelegacionToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");
    jQuery.validator.addMethod('noExterior', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtNoExteriorFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtNoExteriorToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");
    jQuery.validator.addMethod('ciudad', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtCiudadFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtCiudadToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");
    jQuery.validator.addMethod('cp', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtCpFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtCpToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");

    jQuery.validator.addMethod('nombreContactoToner', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtNombreCFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtNombreCToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");

    jQuery.validator.addMethod('telefonoContacto', function(value) {
        if ($("#sltTipoReporte").val() !== "0") {
            if ($("#sltTipoReporte").val() === "1") {//falla
                if ($("#txtTelefonoCFalla").val() !== "")
                    return true;
                else
                    return false;
            } else if ($("#sltTipoReporte").val() === "15") {//suministro
                if ($("#txtTelefonoCToner").val() !== "")
                    return true;
                else
                    return false;
            }
        } else
            return true;
    }, " * campo obligatorio ");

    jQuery.validator.addMethod('agregarPedido', function(value) {
        if ($("#txtModeloToner").val() !== "") {
            return false;
        } else
            return true;
    }, " * Agrege el pedido");



    /*validate form*/
    $(form).validate({
        rules: {
            sltTipoReporte: {selectcheck: true},
            areaAtencionGral: {selectcheck: true},
            sltEstadoTicket: {selectcheck: true},
            correoElectronico: {emailResp: true, required: true},
            txtCorreoElectronico: {emailAtencion: true},
            txtNoSerieEquipoToner: {NoSerieEquipo: true},
            txtNoSerieEquipoFalla: {NoSerieEquipo: true},
            txtModeloToner: {modelo: true, agregarPedido: true},
            txtModeloFalla: {modelo: true},
            txtClaveClienteToner: {claveCliente: true},
            txtClaveClienteFalla: {claveCliente: true},
            txtDomicilioToner: {domicilio: true},
            txtDomicilioFalla: {domicilio: true},
            txtCalleToner: {calle: true},
            txtCalleFalla: {calle: true},
            txtColoniaToner: {colonia: true},
            txtColoniaFalla: {colonia: true},
            txtDelegacionToner: {delegacion: true},
            txtDelegacionFalla: {delegacion: true},
            txtNoExteriorToner: {noExterior: true},
            txtNoExteriorFalla: {noExterior: true},
            txtCiudadFalla: {ciudad: true},
            txtCiudadToner: {ciudad: true},
            txtCpFalla: {cp: true},
            txtCpToner: {cp: true},
            txtNombreCToner: {nombreContactoToner: true},
            txtNombreCFalla: {nombreContactoToner: true},
            txtTelefonoCToner: {telefonoContacto: true},
            txtTelefonoCFalla: {telefonoContacto: true},
        },
        messages: {
            correoElectronico: {required: "* Ingrese el correo electrónico"}
//            descripcion: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
        }
    });
    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            var tamanoTabla = $("#tablaPedido tr").length;
            $.post(controlador, {form: $(form).serialize(), "tamanoTabla": tamanoTabla}).done(function(data) {                        
                        if (data.toString().indexOf("Error:") === -1 && $.isNumeric(data.toString())) {
                            $('#mensajes').html("El ticket <b>"+data+"</b> se editó correctamente");
                            $('#contenidos').load(paginaExito,  {'idTicket':data},  function() {
                                finished();
                            });
                        } else {
                            $('#mensajes').html(data);
                            finished();
                        }
                    });
        }
    });
    $("#dialogContadorFalla").hide();
    var opcion = $('input:radio[name=rdContacto]:checked').val();
    if (opcion == "1") {
        $("#contactoNuevo").show();
        $("#contactoExistente").hide();
    } else {
        $("#contactoExistente").show();
        $("#contactoNuevo").hide();
    }

});
function MostrarTipoReporte(tipo) {
    loading("Cargando ...");
    $('#contenidos').load("mesa/alta_ticketphp.php", {"mostrarDatos": tipo}, function() {
        finished();
    });
}
function MostraDatosEquipoTicket() {
    var tipoTicket = $("#sltTipoReporte").val();
    var centroCosto = $("#claveCC").val();
    // alert(tipoTicket);
    var noSerie = "";
    if (tipoTicket === "1") {
        noSerie = $("#txtNoSerieEquipoFalla").val();
    } else if (tipoTicket === "15") {
        noSerie = $("#txtNoSerieEquipoToner").val();
    }
    var idTicket = $("#idTicket").val();
    $("#contenidos_invisibles").html("");
    var clonarTabla = $("#divTabla").clone(true);
    loading("Cargando ...");
    $.post("WEB-INF/Controllers/Controler_NuevoTicket.php", {"buscar": noSerie, "tipo": tipoTicket}).done(function(datos) {
        var variableDatos = datos.split(" // ");
        // var dataAUX = variableDatos[0];
        var dataAUX = variableDatos[0].split(" *** ");
        var data = dataAUX[0];
        var fechaUltimoTicket = dataAUX[1];
        var moroso = variableDatos[1];
        var suspendido = variableDatos[2];
        var nombreCliente = variableDatos[3];
        var localidadCliente = variableDatos[4];
        if (suspendido == "1") {
            $("#dialog").html("El cliente <b>" + nombreCliente + "</b> se encuentra suspendido, no se puede levantar ticket");
            $(function() {
                $("#dialog").dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    closeOnEscape: false,
                    open: function(event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Aceptar": function() {
                            AgregarIncidencia(noSerie, data, 4, localidadCliente, "Cliente suspendido");
                            finished();
                            $(this).dialog("close");
                        }
                    }
                });
            });
        } else {
            if (data == "") {
                if (moroso == "2") {
                    $("#dialog").html("El cliente <b>" + nombreCliente + "</b> se encuentra como moroso");
                    $(function() {
                        $("#dialog").dialog({
                            resizable: false,
                            height: 200,
                            modal: true,
                            closeOnEscape: false,
                            open: function(event, ui) {
                                $(".ui-dialog-titlebar-close", ui.dialog).hide();
                            },
                            buttons: {
                                "Continuar": function() {
                                    $('#contenidos').load("mesa/alta_ticketphp.php", {"noSerie": noSerie, "mostrarDatos": tipoTicket, "centroCosto": centroCosto, "contador": contador}, function() {
                                        $("#divTabla").html("");
                                        clonarTabla.appendTo("#divTabla");
                                        AgregarIncidencia(noSerie, data, 3, localidadCliente, "Cliente moroso");
                                        finished();
                                    });
                                    $(this).dialog("close");

                                },
                                "Cancelar": function() {
                                    $(this).dialog("close");
                                    finished();
                                }
                            }
                        });
                    });
                } else {
                    $('#contenidos').load("mesa/alta_ticketphp.php", {"noSerie": noSerie, "mostrarDatos": tipoTicket, "centroCosto": centroCosto, "contador": contador}, function() {
                        $("#divTabla").html("");
                        clonarTabla.appendTo("#divTabla");
                        finished();
                    });
                }
            } else {
                if (moroso == "2") {
                    CrearIncidenciaMoroso(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, localidadCliente, nombreCliente, fechaUltimoTicket);
                } else {
                    if (data == "") {
                        MostrarDates(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, localidadCliente);
                        // $(this).dialog("close");
                    } else {
                        incidencidenciaTicketAbierto(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, localidadCliente, nombreCliente, fechaUltimoTicket);
                        // $(this).dialog("close");
                    }
                }
            }
        }
    });
}
function CrearIncidenciaMoroso(tipo, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto, nombreCliente, fechaUltimoTicket) {
    $("#dialog").html("El cliente <b>" + nombreCliente + "</b> se encuentra como moroso");
    $(function() {
        $("#dialog").dialog({
            resizable: false,
            height: 200,
            modal: true,
            closeOnEscape: false,
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog).hide();
            },
            buttons: {
                "Continuar": function() {
                    AgregarIncidencia(noSerie, data, 3, centroCosto, "Cliente moroso");
                    $(this).dialog("close");
                    incidencidenciaTicketAbierto(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto, nombreCliente, fechaUltimoTicket);
                    // MostrarDates(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto);

                },
                "Cancelar": function() {
                    finished();
                    $(this).dialog("close");
                }
            }
        });
    });
}
function incidencidenciaTicketAbierto(tipo, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto, nombreCliente, fechaUltimoTicket) {
    var tipo_ticket = "";
    if(tipoTicket == 15){
        tipo_ticket = "de toner";
    }else{
        tipo_ticket = "de falla";
    }
    $("#dialog").html("Existe un ticket abierto "+tipo_ticket+" para el No. de Serie proporcionado: ticket <b>" + data + "</b>");
    $(function() {
        $("#dialog").dialog({
            resizable: false,
            height: 200,
            modal: true,
            closeOnEscape: false,
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog).hide();
            },
            buttons: {
                "Continuar": function() {
                    AgregarIncidencia(noSerie, data, 2, centroCosto, "Existe ticket abierto");
                    $(this).dialog("close");
                    //incidencia menoer de 30 dias
                    if (fechaUltimoTicket <= "30") {
                        incidencidenciaMenos30dias(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto, nombreCliente, fechaUltimoTicket);
                    } else {
                        MostrarDates(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto);
                    }

                },
                "Ir al ticket": function() {
                    MostrarDates(2, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto);
                    $(this).dialog("close");
                }
            }
        });
    });
}
function incidencidenciaMenos30dias(tipo, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto, nombreCliente) {
    $("#dialog").html("EL equipo con numero de serie <b>" + noSerie + "</b> tiene un ticket en menos de 30 dias ¿desea continuar?");
    $(function() {
        $("#dialog").dialog({
            resizable: false,
            height: 200,
            modal: true,
            closeOnEscape: false,
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog).hide();
            },
            buttons: {
                "Continuar": function() {
                    MostrarDates(1, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto);
                    $(this).dialog("close");
                    AgregarIncidencia(noSerie, data, 5, centroCosto, "El equipo tiene un ticket abierto en menos de 30 dias");
                    //incidencia menoer de 30 dias

                },
                "Cancelar": function() {
                    finished();
                    $(this).dialog("close");
                }
            }
        });
    });
}
function MostrarDates(opcion, noSerie, tipoTicket, idTicket, contador, clonarTabla, data, centroCosto) {
    var area = "";
    if (tipoTicket == "15")
        area = "2";
    else
        area = "1";
    if (opcion == 1) {
        $('#contenidos').load("mesa/alta_ticketphp.php", {"noSerie": noSerie, "mostrarDatos": tipoTicket, "idTicket": idTicket, "contador": contador, "centroCosto": centroCosto}, function() {
            $("#divTabla").html("");
            clonarTabla.appendTo("#divTabla");
            finished();
            
        });
    } else {
        $('#contenidos').load("mesa/alta_ticketphp.php", {"idTicket": data, "area": area, "noSerie": noSerie, "mostrarDatos": tipoTicket, "contador": contador, }, function() {
            finished();
        });
    }
}
function CopiarDatosContacto() {
    // $("#txtNombreAtencion").val($("#txtNombre").val());
    var datos = $("#txtNombre").val().split(" / ");
    $("#txtNombreAtencion").val(datos[0]);
    $("#txtTelefono1Atencion").val($("#txtTelefono1").val());
    $("#txtExtencion1Atencion").val($("#txtExtencion1").val());
    $("#txtTelefono2Atencion").val($("#txtTelefono2").val());
    $("#txtExtencion2Atencion").val($("#txtExtencion2").val());
    $("#txtCorreoElectronico").val($("#correoElectronico").val());
    $("#txtCelularAtencion").val($("#txtCelular").val());
    $("#lstHA option[value=" + $("#lstHR").val() + "]").attr("selected", true);
    $("#lstMA option[value=" + $("#lstMR").val() + "]").attr("selected", true);
    $("#lstTA option[value=" + $("#lstTA").val() + "]").attr("selected", true);
    $("#lstFinHA option[value=" + $("#lstFinHR").val() + "]").attr("selected", true);
    $("#lstFinMA option[value=" + $("#lstFinMR").val() + "]").attr("selected", true);
    $("#lstFinTA option[value=" + $("#lstFinTR").val() + "]").attr("selected", true);
}
//var arregloPedidoToner = new Array();
//var contador = 0;
function validarDatosPedido(noSerie, modelo, ubicacion, color) {
    var validar1 = false;
    var validar2 = false;
    var validar3 = false;
    var validar4 = false;
    var bandera = true;
    var banderaNiveles = true;
    if (color == 1) {//color
        if (!/^([0-9])*$/.test($("#txtContadorBNTonerNuevo").val()) || $("#txtContadorBNTonerNuevo").val() == "" || $("#txtContadorBNTonerNuevo").val() < $("#txtContadorBNTonerAnterior").val()) {
            $("#idErrorContadorBN").html("* Campo obligatorio, númerico, mayor o igual al contador anterior");
            bandera = false;
        } else {
            $("#idErrorContadorBN").html("");
            if (!/^([0-9])*$/.test($("#txtContadorColorTonerNuevo").val()) || $("#txtContadorColorTonerNuevo").val() == "" || $("#txtContadorColorTonerNuevo").val() < $("#txtContadorColorTonerNuevo").val()) {
                $("#idErrorContadorColor").html("* Campo obligatorio, númerico, mayor o igual al contador anterior color");
                bandera = false;
            } else {
                $("#idErrorContadorColor").html("");
                bandera = true;
            }
        }
    } else if (color == 3) {//blanco y negro
        if (!/^([0-9])*$/.test($("#txtContadorBNTonerNuevo").val()) || $("#txtContadorBNTonerNuevo").val() == "" || $("#txtContadorBNTonerNuevo").val() < $("#txtContadorBNTonerAnterior").val()) {
            $("#idErrorContadorBN").html("* Campo obligatorio, númerico, mayor o igual al contador anterior");
            bandera = false;
        } else {
            $("#idErrorContadorBN").html("");
            bandera = true;
        }
    }
    //validar niveles
    if (color == 1) {
        if ($("#txtNivelNegroTonerNuevo").val() != "" || $("#txtNivelCainTonerNuevo").val() != "" || $("#txtNivelMagentaTonerNuevo").val() != "" || $("#txtNivelAmarilloTonerNuevo").val() != "") {
            if ($("#txtNivelNegroTonerNuevo").val() != "") {
                if (!/^([0-9])*$/.test($("#txtNivelNegroTonerNuevo").val())) {
                    $("#idErrorNivelBN").html("* El campo  debe ser númerico en unrango del 0 al 100");
                    banderaNiveles = false;
                } else {
                    $("#idErrorNivelBN").html("");
                    banderaNiveles = true;
                }
            }
            if ($("#txtNivelCainTonerNuevo").val() != "") {
                if (!/^([0-9])*$/.test($("#txtNivelCainTonerNuevo").val())  || $("#txtNivelCainTonerNuevo").val() < 0 || $("#txtNivelCainTonerNuevo").val() > 100) {
                    $("#idErrorNivelCian").html("* El campo  debe ser númerico en unrango del 0 al 100");
                    banderaNiveles = false;
                } else {
                    $("#idErrorNivelCian").html("");
                    banderaNiveles = true;
                }
            }
            

        } else {
            banderaNiveles = true;
        }
    } else if (color == 3) {
        if ($("#txtNivelNegroTonerNuevo").val() != "") {
            if (!/^([0-9])*$/.test($("#txtNivelNegroTonerNuevo").val()) || $("#txtNivelNegroTonerNuevo").val() < 0 || $("#txtNivelNegroTonerNuevo").val() > 100) {
                $("#idErrorNivelBN").html("* El campo  debe ser númerico en unrango del 0 al 100");
                banderaNiveles = false;
            } else {
                $("#idErrorNivelBN").html("");
                banderaNiveles = true;
            }
        } else {
            banderaNiveles = true;
        }
    }
    if (bandera && banderaNiveles) {
        if ($("#noParteComponente").length && $("#noParteComponente").val() != "") {
            var valorAux = $("#noParteComponente").val();
            if (valorAux.indexOf(" / ") != -1) {
                $("#errorNegro").html("");
                if (/^([0-9])*$/.test($("#txtTonerNegroSolicitada").val()) && $("#txtTonerNegroSolicitada").val() != "" && parseInt($("#txtTonerNegroSolicitada").val()) > 0) {
                    validar1 = true;
                    $("#errorNegroCantidad").html("");
                } else {
                    validar1 = false;
                    $("#errorNegroCantidad").html("La cantidad debe se mayor a 0");
                }
            } else {
                $("#errorNegro").html("Ingrese un componente existente");
                validar1 = false;
            }
        }
        else {
            $("#errorNegro").html("");
            $("#errorNegroCantidad").html("");
            validar1 = true;
        }
        //validar cian
        if ($("#noParteComponenteCia").length && $("#noParteComponenteCia").val() != "") {
            var valorAux = $("#noParteComponenteCia").val();
            if (valorAux.indexOf(" / ") != -1) {
                $("#errorCyan").html("");
                if (/^([0-9])*$/.test($("#txtTonerCiaSolicitada").val()) && $("#txtTonerCiaSolicitada").val() != "" && parseInt($("#txtTonerCiaSolicitada").val()) > 0) {
                    validar2 = true;
                    $("#errorCyanCantidad").html("");
                } else {
                    validar2 = false;
                    $("#errorCyanCantidad").html("La cantidad debe se mayor a 0");
                }
            } else {
                $("#errorCyan").html("Ingrese un componente existente");
                validar2 = false;
            }
        }
        else {
            $("#errorCyan").html("");
            $("#errorCyanCantidad").html("");
            validar2 = true;
        }
        if ($("#noParteComponenteMagenta").length && $("#noParteComponenteMagenta").val() != "") {
            var valorAux = $("#noParteComponenteMagenta").val();
            if (valorAux.indexOf(" / ") != -1) {
                $("#errorMagenta").html("");
                if (/^([0-9])*$/.test($("#txtTonerMagentaSolicitada").val()) && $("#txtTonerMagentaSolicitada").val() != "" && parseInt($("#txtTonerMagentaSolicitada").val()) > 0) {
                    validar3 = true;
                    $("#errorMagentaCantidad").html("");
                } else {
                    validar3 = false;
                    $("#errorMagentaCantidad").html("La cantidad debe se mayor a 0");
                }
            } else {
                $("#errorMagenta").html("Ingrese un componente existente");
                validar3 = false;
            }
        }
        else {
            $("#errorMagenta").html("");
            $("#errorMagentaCantidad").html("");
            validar3 = true;
        }
        if ($("#noParteComponenteAmarillo").length && $("#noParteComponenteAmarillo").val() != "") {
            var valorAux = $("#noParteComponenteAmarillo").val();
            if (valorAux.indexOf(" / ") != -1) {
                $("#errorAmarillo").html("");
                if (/^([0-9])*$/.test($("#txtTonerAmarilloSolicitada").val()) && $("#txtTonerAmarilloSolicitada").val() != "" && parseInt($("#txtTonerAmarilloSolicitada").val()) > 0) {
                    validar4 = true;
                    $("#errorAmarilloCantidadAgregarPedido").html("");
                } else {
                    validar4 = false;
                    $("#errorAmarilloCantidadAgregarPedido").html("La cantidad debe se mayor a 0");
                }
            } else {
                $("#errorAmarillo").html("Ingrese un componente existente");
                validar4 = false;
            }
        }
        else {
            $("#errorAmarillo").html("");
            $("#errorAmarilloCantidadAgregarPedido").html("");
            validar4 = true;
        }


        if ($("#noParteComponente").length && $("#noParteComponente").val() == "" && $("#noParteComponenteCia").length && $("#noParteComponenteCia").val() == "" && $("#noParteComponenteMagenta").length && $("#noParteComponenteMagenta").val() == "" && $("#noParteComponenteAmarillo").length && $("#noParteComponenteAmarillo").val() == "") {
            alert("Ingresa un pedido color");
        } else if ($("#noParteComponente").val() == "" && !$("#noParteComponenteCia").length && !$("#noParteComponenteMagenta").length && !$("#noParteComponenteAmarillo").length) {
            alert("Ingresa un pedido negro");
        } else {
            if (validar1 && validar2 && validar3 && validar4) {
                AgregarPedido(noSerie, modelo, ubicacion, color);
            }
        }
    }


}
var contador = $("#contador").val();
function AgregarPedido(noSerie, modelo, ubicacion, color) {
    var noParteNegro = "0";
    var noParteCian = "0";
    var noParteMagenta = "0";
    var noParteAmarillo = "0";
    var cantidadNegro = "0";
    var cantidadCian = "0";
    var cantidadMagenta = "0";
    var cantidadAmarillo = "0";
    //datos de los componentes
    if ($("#noParteComponente").length && $("#noParteComponente").val() !== "") {
        noParteNegro = $("#noParteComponente").val();
    }
    if ($("#noParteComponenteCia").length && $("#noParteComponenteCia").val() !== "") {
        noParteCian = $("#noParteComponenteCia").val();
    }
    if ($("#noParteComponenteMagenta").length && $("#noParteComponenteMagenta").val() !== "") {
        noParteMagenta = $("#noParteComponenteMagenta").val();
    }
    if ($("#noParteComponenteAmarillo").length && $("#noParteComponenteAmarillo").val() !== "") {
        noParteAmarillo = $("#noParteComponenteAmarillo").val();
    }
    //datos de las cantidades solicitadas
    if ($("#txtTonerNegroSolicitada").length && $("#txtTonerNegroSolicitada").val() !== "") {
        cantidadNegro = $("#txtTonerNegroSolicitada").val();
    }
    if ($("#txtTonerCiaSolicitada").length && $("#txtTonerCiaSolicitada").val() !== "") {
        cantidadCian = $("#txtTonerCiaSolicitada").val();
    }
    if ($("#txtTonerMagentaSolicitada").length && $("#txtTonerMagentaSolicitada").val() !== "") {
        cantidadMagenta = $("#txtTonerMagentaSolicitada").val();
    }
    if ($("#txtTonerAmarilloSolicitada").length && $("#txtTonerAmarilloSolicitada").val() !== "") {
        cantidadAmarillo = $("#txtTonerAmarilloSolicitada").val();
    }
    if ($("#idFila").val() !== "") {
        var idFila = $("#idFila").val();
        $('#toner' + idFila).val(noParteNegro);
        $('#noParteCiag' + idFila).val(noParteCian);
        $('#noParteMagentag' + idFila).val(noParteMagenta);
        $('#noParteAmarillog' + idFila).val(noParteAmarillo);

        $('#idNegro' + idFila).text(cantidadNegro);
        $('#negro' + idFila).val(cantidadNegro);
        $('#idCia' + idFila).text(cantidadCian);
        $('#cia' + idFila).val(cantidadCian);
        $('#idMagenta' + idFila).text(cantidadMagenta);
        $('#magenta' + idFila).val(cantidadMagenta);
        $('#idAmarillo' + idFila).text(cantidadAmarillo);
        $('#amarillo' + idFila).val(cantidadAmarillo);
        $("#editPedido" + idFila).html("<img class='imagenMouse' src='resources/images/Modify.png' title='modificar pedido' onclick='changeToTab(\"" + noParteNegro + "\",\"" + noParteCian + "\",\"" + noParteMagenta + "\",\"" + noParteAmarillo + "\",\"" + noSerie + "\",\"" + modelo + "\"," + contador + "," + cantidadNegro + "," + cantidadCian + "," + cantidadMagenta + "," + cantidadAmarillo + ",2);' style='float: right; cursor: pointer;' />");
        $("#detallePedido" + idFila).html("<img class='imagenMouse' src='resources/images/Textpreview.png' title='modificar pedido' onclick='changeToTab(\"" + noParteNegro + "\",\"" + noParteCian + "\",\"" + noParteMagenta + "\",\"" + noParteAmarillo + "\",\"" + noSerie + "\",\"" + modelo + "\"," + contador + "," + cantidadNegro + "," + cantidadCian + "," + cantidadMagenta + "," + cantidadAmarillo + ",1);' style='float: right; cursor: pointer;' />");

    } else {
        var fechaAnterior = "";
        var fechaContador = $("#txtFechaContadorTonerNuevo").val();
        var contadorBN = $("#txtContadorBNTonerNuevo").val();
        var contadorColor = $("#txtContadorColorTonerNuevo").val();
        var nivelNegro = $("#txtNivelNegroTonerNuevo").val();
        var nivelCia = $("#txtNivelCainTonerNuevo").val();
        var nivelMagenta = $("#txtNivelMagentaTonerNuevo").val();
        var nivelAmarillo = $("#txtNivelAmarilloTonerNuevo").val();
        var fechaContadorAnterior = $("#txtFechaContadorTonerAnterior").val();
        var contadorBNAnterior = $("#txtContadorBNTonerAnterior").val();
        var contadorColorAnterior = $("#txtContadorColorTonerAnterior").val();
        var nivelNegroAnterior = $("#txtNivelNegroTonerAnterior").val();
        var nivelCiaAnterior = $("#txtNivelCainTonerAnterior").val();
        var nivelMagentaAnterior = $("#txtNivelMagentaTonerAnterior").val();
        var nivelAmarilloAnterior = $("#txtNivelAmarilloTonerAnterior").val();
        if (!$("#txtContadorBNTonerNuevo").length)
            contadorBN = "";
        if (!$("#txtContadorColorTonerNuevo").length)
            contadorColor = "";
        if (!$("#txtNivelNegroTonerNuevo").length)
            nivelNegro = "";
        if (!$("#txtNivelCainTonerNuevo").length)
            nivelCia = "";
        if (!$("#txtNivelMagentaTonerNuevo").length)
            nivelMagenta = "";
        if (!$("#txtNivelAmarilloTonerNuevo").length)
            nivelAmarillo = "";

        if (fechaContadorAnterior != "") {
            var fechaHora = fechaContadorAnterior.split(" ");
            var fecha = fechaHora[0];
            var hora = fechaHora[1];
            var fechaAux = fecha.split("-");
            var dia = fechaAux[0];
            var mes = fechaAux[1];
            var anio = fechaAux[2];
            fechaAnterior = anio + "-" + mes + "-" + dia + " " + hora;
        }
        if (!$("#txtContadorBNTonerAnterior").length)
            contadorBNAnterior = "";
        if (!$("#txtContadorColorTonerAnterior").length)
            contadorColorAnterior = "";
        if (!$("#txtNivelNegroTonerAnterior").length)
            nivelNegroAnterior = "";
        if (!$("#txtNivelCainTonerAnterior").length)
            nivelCiaAnterior = "";
        if (!$("#txtNivelMagentaTonerAnterior").length)
            nivelMagentaAnterior = "";
        if (!$("#txtNivelAmarilloTonerAnterior").length)
            nivelAmarilloAnterior = "";

        var estado = "Validar Existencia";
        if (color == "1")
            color = "No";
        else
            color = "Si";


        var newRow = "<tr id='filaArreglo" + contador + "'>" +
                "<td align='center' style='background-color: palegoldenrod '>" + noSerie + contador + "<input type='hidden' id='serie" + contador + "' name='serie" + contador + "' value='" + noSerie + "'/>" +
                "<input type='hidden' id='contadorBN" + contador + "' name='contadorBN" + contador + "' value='" + contadorBN + "'/><input type='hidden' id='contadorBNAnterio" + contador + "' name='contadorBNAnterio" + contador + "' value='" + contadorBNAnterior + "'/>" +
                "<input type='hidden' id='contadorColor" + contador + "' name='contadorColor" + contador + "' value='" + contadorColor + "'/><input type='hidden' id='contadorColorAnterio" + contador + "' name='contadorColorAnterio" + contador + "' value='" + contadorColorAnterior + "'/>" +
                "<input type='hidden' id='nivelCia" + contador + "' name='nivelCia" + contador + "' value='" + nivelCia + "'/><input type='hidden' id='nivelCiaAnterio" + contador + "' name='nivelCiaAnterio" + contador + "' value='" + nivelCiaAnterior + "'/>" +
                "<input type='hidden' id='nivelNegro" + contador + "' name='nivelNegro" + contador + "' value='" + nivelNegro + "'/><input type='hidden' id='nivelNegroAnterior" + contador + "' name='nivelNegroAnterior" + contador + "' value='" + nivelNegroAnterior + "'/>" +
                "<input type='hidden' id='nivelMagenta" + contador + "' name='nivelMagenta" + contador + "' value='" + nivelMagenta + "'/><input type='hidden' id='nivelMagentaAnterio" + contador + "' name='nivelMagentaAnterio" + contador + "' value='" + nivelMagentaAnterior + "'/>" +
                "<input type='hidden' id='nivelAmarillo" + contador + "' name='nivelAmarillo" + contador + "' value='" + nivelAmarillo + "'/><input type='hidden' id='nivelAmarilloAnterior" + contador + "' name='nivelAmarilloAnterior" + contador + "' value='" + nivelAmarilloAnterior + "'/>" +
                "<input type='hidden' id='fechaContador" + contador + "' name='fechaContador" + contador + "' value='" + fechaContador + "'/><input type='hidden' id='fechaContadorAnterior" + contador + "' name='fechaContadorAnterior" + contador + "' value='" + fechaAnterior + "'/>" +
                "<input type='hidden' id='toner" + contador + "' name='toner" + contador + "' value='" + noParteNegro + "'/>" +
                "<input type='hidden' id='noParteCiag" + contador + "' name='noParteCiag" + contador + "' value='" + noParteCian + "'/>" +
                "<input type='hidden' id='noParteMagentag" + contador + "' name='noParteMagentag" + contador + "' value='" + noParteMagenta + "'/>" +
                "<input type='hidden' id='noParteAmarillog" + contador + "' name='noParteAmarillog" + contador + "' value='" + noParteAmarillo + "'/>" +
                "</td>" +
                "<td align='center' style='background-color: palegoldenrod '>" + modelo + "<input type='hidden' id='modelo" + contador + "' name='modelo" + contador + "' value='" + modelo + "'/></td>" +
                "<td align='center' style='background-color: palegoldenrod '>" + ubicacion + "<input type='hidden' id='ubicacion" + contador + "' name='ubicacion" + contador + "' value='" + ubicacion + "' /></td>" +
                "<td align='center' style='background-color: palegoldenrod '>" + color + "<input type='hidden' id='color" + contador + "' name='color" + contador + "' value='" + color + "' /></td>" +
                "<td align='center' style='background-color: palegoldenrod '> <div id='idNegro" + contador + "'>" + cantidadNegro + "</div><input type='hidden' id='negro" + contador + "' name='negro" + contador + "' value='" + cantidadNegro + "'/></td>" +
                "<td align='center' style='background-color: palegoldenrod '> <div id='idCia" + contador + "'>" + cantidadCian + "</div><input type='hidden' id='cia" + contador + "' name='cia" + contador + "' value='" + cantidadCian + "'/></td>" +
                "<td align='center' style='background-color: palegoldenrod '> <div id='idMagenta" + contador + "'>" + cantidadMagenta + "</div><input type='hidden' id='magenta" + contador + "' name='magenta" + contador + "' value='" + cantidadMagenta + "'/></td>" +
                "<td align='center' style='background-color: palegoldenrod '> <div id='idAmarillo" + contador + "'>" + cantidadAmarillo + "</div><input type='hidden' id='amarillo" + contador + "' name='amarillo" + contador + "' value='" + cantidadAmarillo + "'/></td>" +
                "<td align='center' style='background-color: palegoldenrod '> <div id='idEstado" + contador + "'>" + estado + "</div><input type='hidden' id='estado" + contador + "' name='estado" + contador + "' value='" + estado + "'/></td>" +
                "<td align='center' style='background-color: palegoldenrod '><div id='detallePedido" + contador + "'><img class='imagenMouse' src='resources/images/Textpreview.png' title='modificar pedido' onclick='changeToTab(\"" + noParteNegro + "\",\"" + noParteCian + "\",\"" + noParteMagenta + "\",\"" + noParteAmarillo + "\",\"" + noSerie + "\",\"" + modelo + "\"," + contador + "," + cantidadNegro + "," + cantidadCian + "," + cantidadMagenta + "," + cantidadAmarillo + ",1);' style='float: right; cursor: pointer;' /></div></td>" +
                "<td align='center' style='background-color: palegoldenrod '><div id='editPedido" + contador + "'><img class='imagenMouse' src='resources/images/Modify.png' title='modificar pedido' onclick='changeToTab(\"" + noParteNegro + "\",\"" + noParteCian + "\",\"" + noParteMagenta + "\",\"" + noParteAmarillo + "\",\"" + noSerie + "\",\"" + modelo + "\"," + contador + "," + cantidadNegro + "," + cantidadCian + "," + cantidadMagenta + "," + cantidadAmarillo + ",2);' style='float: right; cursor: pointer;' /></div></td>" +
                "<td align='center' style='background-color: palegoldenrod '><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar pedido' onclick='deletePedido(" + contador + ");' style='float: right; cursor: pointer;' /></td></tr>";
        $('#tablaPedido tr:last').after(newRow);//add the new row    
        contador++;
    }
    changeToPedido(2);
    $('#txtNoSerieEquipoToner').val("");
    $("#txtModeloToner").val("");

    $("#txtTonerNegroSolicitada").val("");
    $("#txtTonerCiaSolicitada").val("");
    $("#txtTonerMagentaSolicitada").val("");
    $("#txtTonerAmarilloSolicitada").val("");
    $('#txtFechaContadorTonerNuevo').val("");
    $("#txtContadorBNTonerNuevo").val("");
    $('#txtContadorColorTonerNuevo').val("");
    $("#txtNivelNegroTonerNuevo").val("");
    $('#txtNivelCainTonerNuevo').val("");
    $("#txtNivelMagentaTonerNuevo").val("");
    $('#txtNivelAmarilloTonerNuevo').val("");
    $("#txtFechaContadorTonerAnterior").val("");
    $('#txtContadorBNTonerAnterior').val("");
    $("#txtContadorColorTonerAnterior").val("");
    $('#txtNivelNegroTonerAnterior').val("");
    $("#txtNivelCainTonerAnterior").val("");
    $('#txtNivelMagentaTonerAnterior').val("");
    $("#txtNivelAmarilloTonerAnterior").val("");

    $("#txtTonerNegroSolicitada").val("");
    $("#txtTonerCiaSolicitada").val("");
    $("#txtTonerMagentaSolicitada").val("");
    $("#txtTonerAmarilloSolicitada").val("");

    $("#noParteComponente").val("");
    $("#noParteComponenteMagenta").val("");
    $("#noParteComponenteCia").val("");
    $("#noParteComponenteAmarillo").val("");
    var tonerColor = $("#noParteComponente").val("");

}
function deletePedido(idFila) {
    var fila = "filaArreglo" + idFila;
    var trs = $("#tablaPedido tr").length;
    if (trs > 1) {
        $("#" + fila).remove();
    }
}
function changeToTab(componenteN, componenteC, componenteM, componenteA, serie, modelo, contador, negro, cia, magenta, amarillo, tipo) {
    if (tipo == 1) {
        $('#txtNoSerieEquipoToner').prop('readonly', true);
        $('#txtModeloToner').prop('readonly', true);
        $('#txtTonerNegroSolicitada').prop('readonly', true);
        $('#txtTonerCiaSolicitada').prop('readonly', true);
        $('#txtTonerMagentaSolicitada').prop('readonly', true);
        $('#txtTonerAmarilloSolicitada').prop('readonly', true);
//desactivar los botnes
        $("#idGuardarPedido").css("display", "none");
        $("#idCancelarPedido").css("display", "none");
    } else {
        $('#txtNoSerieEquipoToner').prop('readonly', false);
        $('#txtModeloToner').prop('readonly', false);
        $('#txtTonerNegroSolicitada').prop('readonly', false);
        $('#txtTonerCiaSolicitada').prop('readonly', false);
        $('#txtTonerMagentaSolicitada').prop('readonly', false);
        $('#txtTonerAmarilloSolicitada').prop('readonly', false);
        $("#idGuardarPedido").css("display", "block");
        $("#idCancelarPedido").css("display", "block");
    }
    if (componenteN == "0")
        componenteN = "";
    if (componenteC == "0")
        componenteC = "";
    if (componenteM == "0")
        componenteM = "";
    if (componenteA == "0")
        componenteA = "";
    if (negro == "0")
        negro = "";
    if (cia == "0")
        cia = "";
    if (magenta == "0")
        magenta = "";
    if (amarillo == "0")
        amarillo = "";

    $("#tabs").tabs("option", "active", 0);
    $("#txtNoSerieEquipoToner").val(serie);
    $("#txtModeloToner").val(modelo);
    if ($("#idFila").val() == "")
        $("#idFila").val(contador);
    else
        $("#idFila").val($("#idFila").val());
    $("#txtTonerNegroSolicitada").val(negro);
    $("#txtTonerCiaSolicitada").val(cia);
    $("#txtTonerMagentaSolicitada").val(magenta);
    $("#txtTonerAmarilloSolicitada").val(amarillo);
    $("#noParteComponente").val(componenteN);
    $("#noParteComponenteCia").val(componenteC);
    $("#noParteComponenteMagenta").val(componenteM);
    $("#noParteComponenteAmarillo").val(componenteA);
}
function changeToPedido(idTabs) {
    $("#tabs").tabs("option", "active", idTabs);
}
function mostrarContadoresNiveles(noSerie) {
    $(function() {
        $("#dialogContadorFalla").dialog({
            resizable: false,
            height: 500,
            width: 900,
            modal: true,
            closeOnEscape: false,
            open: function(event, ui) {
                $(".ui-dialog-titlebar-close", ui.dialog).hide();
            },
            buttons: {
                "Guardar": function() {
                    agregarLecturaFalla($("#txtContadorBNFallaNuevo").val(), $("#txtContadorColorFallaNuevo").val(), $("#txtNivelNegroFallaNuevo").val(), $("#txtNivelCainFallaNuevo").val(), $("#txtNivelMagentaFallaNuevo").val(), $("#txtNivelAmarilloFallaNuevo").val());
                    $(this).dialog("close");
                },
                "Cancelar": function() {
                    $(this).dialog("close");
                }
            }
        });
    });
}
function DatosContacto(contacto) {
    var datos = contacto.split(" / ");
    $("#txtTelefono1").val(datos[1]);
    $("#txtCelular").val(datos[2]);
    $("#correoElectronico").val(datos[3]);

}
function mostrarTipoContacto(opcion) {
    if (opcion == "1") {
        $("#contactoNuevo").show();
        $("#contactoExistente").hide();
    } else {
        $("#contactoExistente").show();
        $("#contactoNuevo").hide();
    }
}
/*
 $incidencia->setFecha($_POST['Fecha']);
 $incidencia->setFechaFin($_POST['FechaFin']);*/
function AgregarIncidencia(noSerie, ticket, tipo, centroCosto, descripcion) {
    var localidad = "";
    var fecha = getFecha();
    if (centroCosto == "")
        localidad = $("#claveCC").val();
    else
        localidad = centroCosto;
    $('#mensajes').load("WEB-INF/Controllers/Controler_Incidencia.php", {"NoSerie": noSerie, "id_ticket": ticket, "Descripcion": descripcion, "cc": localidad, "status": 1, "tipo": tipo, "Fecha": fecha, "FechaFin": fecha}, function() {

    });
}
function getFecha() {
    var f = new Date();
    var mes = (f.getMonth() + 1);
    var dia = f.getDate();
    var hora = f.getHours();
    var min = f.getMinutes();
    var seg = f.getSeconds();
    if (mes < 10)
        mes = "0" + mes;
    if (dia < 10)
        dia = "0" + dia;
    if (hora < 10)
        hora = "0" + hora;
    if (min < 10)
        min = "0" + min;
    if (seg < 10)
        seg = "0" + seg;
    var fecha = f.getFullYear() + "-" + mes + "-" + dia + " " + hora + ":" + min + ":" + seg;
    return fecha;
}
function mostrarDetalleNota(pagina, idNota) {
    loading("Cargando ...");
    $("#detalleNota").load(pagina, {"idNota": idNota}, function() {
        $("#detalleNota").dialog({
            resizable: false,
            height: 'auto',
            width: 'auto',
            modal: true,
            title: "Detalle nota",
            buttons: {
                "Cancelar": function() {
                    $(this).dialog("close");
                }
            }
        });
        finished();
    });
}
function agregarLecturaFalla(negro, color, nivelN, nivelC, nivelM, nivelA) {
    $("#contadorBNFallaNuevo").val(negro);
    $("#contadorColorFallaNuevo").val(color);
    $("#nivelNegroFallaNuevo").val(nivelN);
    $("#nivelCianFallaNuevo").val(nivelC);
    $("#nivelMagentaFallaNuevo").val(nivelM);
    $("#nivelAmarilloFallaNuevo").val(nivelA);
}
