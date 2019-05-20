var activos = 0;
$(document).ready(function () {
    activos = 0;
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
        "iDisplayLength": -1,
        "aLengthMenu": [[-1], ["Todo"]],
        "aaSorting": [[1, "asc"]],
        "bFilter": false
    });
    oTable = $('#tAlmacen').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": -1,
        "aLengthMenu": [[-1], ["Todo"]],
        "aaSorting": [[1, "asc"]],
        "bFilter": false
    });
    oTable = $('#tAlmacen2').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 100,
        "aaSorting": [[0, "desc"]],
        "bFilter": false
    });
    var form = "#frmAltaTicket";

    if ($("#tipoUsuario").val() == "21") {
        var paginaExito = "mesa/alta_ticket.php";
    } else {
        var paginaExito = "mesa/lista_ticket.php";
    }

    var controlador = "WEB-INF/Controllers/Controler_NuevoTicket2.php";

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");
    jQuery.validator.addMethod('emailResp', function (value) {
        if ($("#correoElectronico").val() !== "") {
            var filter = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
            if (filter.test($("#correoElectronico").val()))
                return true;
            else
                return false;
        } else
            return true;
    }, " * Ingrese un correo válido");
    jQuery.validator.addMethod('emailAtencion', function (value) {
        if ($("#txtCorreoElectronico").val() !== "") {
            var filter = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
            if (filter.test($("#txtCorreoElectronico").val()))
                return true;
            else
                return false;
        } else
            return true;
    }, " * Ingrese un correo válido");
    jQuery.validator.addMethod('ValidarDatosContacto', function (value) {
        if ($("input:radio[name=rdContacto]:checked").val() == "1") {
            if (value !== "")
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, " * Campo obligatorio");
    jQuery.validator.addMethod('ValidarDatosContactoExistente', function (value) {
        if ($("input:radio[name=rdContacto]:checked").val() == "0") {
            if (value !== "0")
                return true;
            else
                return false;
        } else {
            return true;
        }
    }, " * Selecciona el contacto");
    jQuery.validator.addMethod('validarContadorNegro', function (value) {
        var x = 0;
        while (x < $("#tAlmacen tr").length - 1) {
            if ($("#activar_" + x).is(':checked')) {
                if ($("#txtContadorNegro_" + x).val() == "") {
                    return false;
                } else {
                    return true;
                }
                break;
            }
            x++;
        }
    }, " * Ingresa el contador negro");
    jQuery.validator.addMethod('validarSeleccion', function (value) {
        if ($("#txtNoSrieFallaBuscar").val() != "") {
            if ($("input[name='rdEquipoFalla']").is(':checked')) {
                return true;
            } else {
                return false;
            }
        } else
            return true;
    }, " * Selecciona un equipo");
    jQuery.validator.addMethod('validarNoserie', function (value) {
        if ($("#sltTipoReporte").val() == "1") {
            if ($("#txtNoSrieFallaBuscar").val() != "") {
                return true;
            } else {
                return false;
            }
        } else
            return true;
    }, " * Ingrese el numero de serie");
    jQuery.validator.addMethod('validarClaveCliente', function (value) {
        if ($("#sltTipoReporte").val() == "1") {
            if ($("#txtClaveClienteToner").val() != "") {
                return true;
            } else {
                return false;
            }
        } else
            return true;
    }, " * Ingrese el numero de serie");
//    jQuery.validator.addMethod('validarContadroBNFalla', function(value) {
//        if ($("#sltTipoReporte").val() != "15" ) {
//            if (parseInt($("#txtContadorNegroAnterior_0").val()) > parseInt($("#txtContadorNegro_0").val())) {
//                return false;
//            } else {
//                return true;
//            }
//        } else
//            return true;
//    }, " * El contador debe ser mayor o igual al contador anterior ");
//    jQuery.validator.addMethod('validarContadroColorFalla', function(value) {
//        if ($("#sltTipoReporte").val() != "15") {
//            if (parseInt($("#txtContadorColorAnterior_0").val()) > parseInt($("#txtContadorColor_0").val())) {
//                return false;
//            } else {
//                return true;
//            }
//        } else
//            return true;
//    }, " * El contador debe ser mayor o igual al contador anterior ");
    jQuery.validator.addMethod('validarDescripcionFalla', function (value) {
        if ($("#sltTipoReporte").val() != "15") {
            if ($("#descripcion").val() != "") {
                return true;
            } else {
                return false;
            }
        } else
            return true;
    }, " * Ingrese la descripción del ticket");

    /*validate form*/
    $(form).validate({
        rules: {
            sltTipoReporte: {selectcheck: true},
            sltEstadoTicket: {selectcheck: true},
            slcCliente: {selectcheck: true},
            slcLocalidad: {selectcheck: true},
            correoElectronico: {emailResp: true, ValidarDatosContacto: true},
            txtCorreoElectronico: {emailAtencion: true},
            areaAtencionGral: {selectcheck: true},            
//            activar_0: {validarPedido: true},
            //txtNoSrieFallaBuscar: {validarNoserie: true},
//            txtClaveClienteToner: {validarClaveCliente: true},
            txtNombre1: {ValidarDatosContacto: true},
            txtTelefono1: {ValidarDatosContacto: true},
            txtNombre: {ValidarDatosContactoExistente: true},
            txtContadorNegro_0: {number: true},
            txtContadorColor_0: {number: true},
            descripcion: {validarDescripcionFalla: true}
        },
        messages: {
            txtContadorNegro_0: {number: "* Ingrese solo números", },
            txtContadorColor_0: {number: "* Ingrese solo números", }
        }
    });

    $(form).submit(function (event) {
        if ($(form).valid()) {
            if ($("#botonGuardar").length) {
                $("#botonGuardar").hide();
            }
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();

            $.post(controlador, {form: $(form).serialize(), "tabla": 1}).done(function (data) {
                if (data.toString().indexOf("Error:") === -1 && $.isNumeric(data.toString())) {
                    $('#mensajes').html("El ticket <b>" + data + "</b> se registró correctamente");
                    $('#contenidos').load(paginaExito, {'idTicket': data}, function () {
                        $('.button').button().css('margin-top', '20px');
                        finished();
                        if ($("#botonGuardar").length) {
                            $("#botonGuardar").show();
                        }
                    });
                } else {
                    $('#mensajes').html(data);
                    finished();
                    if ($("#botonGuardar").length) {
                        $("#botonGuardar").show();
                    }
                }
            });

        }
    });

    if ($("#unCliente").length && $("#unCliente").val() == "") {
        var cliente = $("#slcCliente").val();
    }

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter();
    $("#selectNoSerie").multiselect({
        multiple: true,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });


    if ($("#mostrarDatos").length && $("#mostrarDatos").val() == "0") {
        MostrarTipoReporte($("#sltTipoReporte").val());
    }
});

function marcarActivo(id_check) {
    if ($("#" + id_check).is(':checked')) {
        activos++;
    } else {
        activos--;
    }
}

function MostrarTipoReporte(tipo) {
    loading("Cargando ...");
    $('#contenidos').load("mesa/alta_ticket2.php", {"mostrarDatos": tipo}, function () {
        finished();
    });
}

function CopiarDatosContacto() {
// $("#txtNombreAtencion").val($("#txtNombre").val());
    var radio = $('input:radio[name=rdContacto]:checked').val();
    if (radio == "0") {
        var datos = $("#txtNombre").val().split(" // ");
        $("#txtNombreAtencion").val(datos[0]);
    } else if (radio == "1") {
        $("#txtNombreAtencion").val($("#txtNombre1").val());
    }
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
function mostrarTipoContacto(opcion) {
    if (opcion == "1") {
        $("#contactoNuevo").show();
        $("#txtNombre1").show();
        $("#contactoExistente").hide();
        $("#txtNombre").rules("remove");
        $("#txtNombre1").rules("add", {required: true, messages: {required: " * Ingrese el nombre del responsable"}});
        $('#txtNombre1').attr('readonly', false);
        $('#correoElectronico').attr('readonly', false);
        $('#txtCelular').attr('readonly', false);
        $('#txtExtencion2').attr('readonly', false);
        $('#txtTelefono2').attr('readonly', false);
        $('#txtExtencion1').attr('readonly', false);
        $('#txtTelefono1').attr('readonly', false);
    } else {
        $("#contactoExistente").show();
        $("#txtNombre1").hide();
        $("#contactoNuevo").hide();
        $("#txtNombre").rules("add", {selectcheck: true});
        $("#txtNombre1").rules("remove");
        $('#correoElectronico').attr('readonly', false);
        $('#txtCelular').attr('readonly', false);
        $('#txtExtencion2').attr('readonly', true);
        $('#txtTelefono2').attr('readonly', true);
        $('#txtExtencion1').attr('readonly', true);
        $('#txtTelefono1').attr('readonly', false);
    }
}
function mostrarLocalidadTicket(cliente, serie) {
    loading("Cargando ...");
    var tipoReporte = $("#sltTipoReporte").val();
    $('#contenidos').load("mesa/alta_ticket2.php", {"claveCliente": cliente, "mostrarDatos": tipoReporte}, function () {
        $("#txtNoSrieFallaBuscar").val(serie);
        finished();
    });
}
function CambioLocalidadTicketToner(localidad, permiso) {
    loading("Cargando ...");
    $.post("WEB-INF/Controllers/Validacion/Controller_Localidad_Valida.php", {"localidad": localidad}).done(function (data) {
        var tipo = data.substring(0, 1);
        var mensaje = data.substring(2);
        if (tipo === "0") {
            $("#dialog").html("<h3>" + mensaje + "</h3>");
            $(function () {
                $("#dialog").dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    closeOnEscape: false,
                    open: function (event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Aceptar": function () {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        } else {
            if ($("#idTicket").val() != "") {
                $("#dialog").html("Si cambia de localidad generara un nuevo ticket. ¿Desea continuar?");
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {//crear incidencia
                                MostrarEquipoLocalidad(localidad, permiso);
                                $(this).dialog("close");
                            },
                            "Cancelar": function () {
                                $(this).dialog("close");
                                $("#slcLocalidad option[value='" + localidad + "']").attr("selected", true);
                                finished();
                            }
                        }
                    });
                });
            } else {
                MostrarEquipoLocalidad(localidad, permiso);
            }
        }
    });
}
function MostrarEquipoLocalidad(localidad, permiso) {
    loading("Cargando ...");
    var claveCliente = $("#slcCliente").val();
    var tipoReporte = $("#sltTipoReporte").val();
    if (tipoReporte == "15") {
        $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuxcarLocalidad", "localidad": localidad}).done(function (data) {
            var valores = data.split(" / ");
            if (data != "" && valores[0] != "" && permiso == "0") {
                $("#dialog").html("La localidad  <b>" + valores[1] + "</b> tiene un mini almacén , no se puede levantar ticket, favor de ir a la opción de cambiar toner");
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {
                                finished();
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            } else {
                $('#contenidos').load("mesa/alta_ticket2.php", {"claveCliente": claveCliente, "mostrarDatos": tipoReporte, "claveLocalidad": localidad}, function () {
                    finished();
                });
            }
        });
    } else {
        $('#contenidos').load("mesa/alta_ticket2.php", {"claveCliente": claveCliente, "mostrarDatos": tipoReporte, "claveLocalidad": localidad}, function () {
            finished();
        });
    }
}
function DatosContacto(contacto) {
    var datos = contacto.split(" // ");
    $("#txtTelefono1").val(datos[1]);
    $("#txtCelular").val(datos[2]);
    $("#correoElectronico").val(datos[3]);
}
function incidenciaClienteSuspendido(cliente) {
    $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarCliente", "cliente": cliente, "tipo": "15"}).done(function (data) {
        var valores = data.split(" / ");
        if (valores[0] == "1") {
            $("#dialog").html("El cliente <b>" + valores[2] + "</b> se encuentra suspendido, no se puede levantar ticket");
            $(function () {
                $("#dialog").dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    closeOnEscape: false,
                    open: function (event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Aceptar": function () {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        } else {
            if (valores[1] == "2") {
                if (valores[3] == "1") { //Si se permite agregar tickets a morosos
                    $("#dialog").html("El cliente <b>" + valores[2] + "</b> se encuentra como moroso");
                } else {
                    $("#dialog").html("El cliente <b>" + valores[2] + "</b> se encuentra como moroso, no se puede levantar ticket");
                }
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {//crear incidencia                                
                                if (valores[3] == "1") { //Si se permite agregar tickets a morosos
                                    mostrarLocalidadTicket(cliente);
                                }
                                finished();
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            } else {
                mostrarLocalidadTicket(cliente);
            }
        }
    });
}
function incidenciaByTicket(fila, noSerie, tipoColor) {
    if ($("#activar_" + fila).is(':checked')) {
        jQuery.validator.addMethod('validarPedidoSelect', function (value) {
            if (!$("#ckbNegro_" + fila).is(':checked') && !$("#ckbCian_" + fila).is(':checked') && !$("#ckbMagenta_" + fila).is(':checked') && !$("#ckbAmarillo_" + fila).is(':checked')) {
                return false;
            }
            else {
                return true;
            }
        }, " * Seleccione minimo un toner");
        jQuery.validator.addMethod('validarContadorNegroToner', function (value) {
            if (parseInt($("#txtContadorNegro_" + fila).val()) < parseInt($("#txtContadorNegroAnterior_" + fila).val())) {
                return false;
            }
            else {
                return true;
            }
        }, " * El contador debe ser igual o mayor al contador anterior");
        jQuery.validator.addMethod('validarContadorNegroTonerMayor', function (value) {
            if (parseInt($("#txtContadorNegro_" + fila).val()) > parseInt($("#txtContadorNegroAnterior_" + fila).val()) + 100000) {
                return false;
            }
            else {
                return true;
            }
        }, " * El contador no debe superar mas de 100,000 al anterior");
        $("#activar_" + fila).rules("add", {validarPedidoSelect: true});
        $("#txtContadorNegro_" + fila).rules("remove", "range");
        $("#txtContadorNegro_" + fila).rules("add", {required: true, number: true, validarContadorNegroToner: true, validarContadorNegroTonerMayor: true, messages: {required: " * Ingrese la el contador", number: " * Ingrese solo números"}});
        $("#txtNivelNegro_" + fila).rules("add", {min: 0, max: 100, messages: {min: " * El nivel debe ser igual o mayor a 0", max: " * El nivel debe ser igual o menor a 100"}});
        if (tipoColor == "1") {
            jQuery.validator.addMethod('validarContadorColorToner', function (value) {
                if (parseInt($("#txtContadorColor_" + fila).val()) < parseInt($("#txtContadorColorAnterior_" + fila).val())) {
                    return false;
                }
                else {
                    return true;
                }
            }, " * El contador debe ser igual o mayor al contador anterior");
            jQuery.validator.addMethod('validarContadorColorTonerMayor', function (value) {
                if (parseInt($("#txtContadorColor_" + fila).val()) > parseInt($("#txtContadorColorAnterior_" + fila).val()) + 100000) {
                    return false;
                }
                else {
                    return true;
                }
            }, " * El contador no debe superar mas de 100,000 al anterior");
            $("#txtContadorColor_" + fila).rules("remove", "range");
            $("#txtContadorColor_" + fila).rules("add", {required: true, number: true, validarContadorColorToner: true, validarContadorColorTonerMayor: true, messages: {required: " * Ingrese la el contador", number: " * Ingrese solo números"}});
            $("#txtNivelCian_" + fila).rules("add", {min: 0, max: 100, messages: {min: " * El nivel debe ser igual o mayor a 0", max: " * El nivel debe ser igual o menor a 100"}});
            $("#txtNivelMagenta_" + fila).rules("add", {min: 0, max: 100, messages: {min: " * El nivel debe ser igual o mayor a 0", max: " * El nivel debe ser igual o menor a 100"}});
            $("#txtNivelAmarillo_" + fila).rules("add", {min: 0, max: 100, messages: {min: " * El nivel debe ser igual o mayor a 0", max: " * El nivel debe ser igual o menor a 100"}});
        }

        $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuuscarByNoSerie", "NoSerie": noSerie, "tipo": "15"}).done(function (data) {
            if (data != "") {
                $("#dialog").html("Existe un ticket abierto de toner para el No. de Serie proporcionado: ticket <b>" + data + "</b>");
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Continuar": function () {
                                incidenciaXDias(fila, noSerie);
                                $(this).dialog("close");
                            },
                            "Ir al ticket": function () {
                                loading("Cargando ...");
                                $('#contenidos').load("mesa/alta_ticket2.php", {"idTicket": data, "area": "15", "detalle": "0"}, function () {
                                    finished();
                                });
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            } else {
                incidenciaXDias(fila, noSerie);
            }
        });
    } else {
        $("#activar_" + fila).rules("remove");
        $("#txtContadorNegro_" + fila).rules("remove");
        $("#txtNivelNegro_" + fila).rules("remove");
        if (tipoColor == "1") {
            $("#txtContadorColor_" + fila).rules("remove");
            $("#txtNivelCian_" + fila).rules("remove");
            $("#txtNivelMagenta_" + fila).rules("remove");
            $("#txtNivelAmarillo_" + fila).rules("remove");
        }
        NostrarInputs(fila, noSerie);
    }
}
function incidenciaXDias(fila, noSerie) {
//crear incidencias  Controler_BuscarDatosIncidencia
    if ($("#activar_" + fila).is(':checked')) {
        $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarXdia", "NoSerie": noSerie, "tipo": "15"}).done(function (data) {
            if (parseInt(data) < 30) {
                $("#dialog").html("Se registro un ticket para el equipo <b>" + noSerie + "</b> seleccionado en menos de 30 dias");
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Continuar": function () {
                                NostrarInputs(fila, noSerie);
                                $(this).dialog("close");
                            },
                            "Cancelar": function () {
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            } else {
                NostrarInputs(fila, noSerie);
            }
        });
    }
}
function NostrarInputs(fila, noSerie) {
    if ($("#activar_" + fila).is(':checked')) {
        $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "LecturaNoSerie", "NoSerie": noSerie, "tipo": "15"}).done(function (data) {
            var valor = data.split(" / ");
            $("#txtContadorNegro_" + fila).show();
            $("#txtContadorColor_" + fila).show();
            $("#txtNivelNegro_" + fila).show();
            $("#txtNivelCian_" + fila).show();
            $("#txtNivelMagenta_" + fila).show();
            $("#txtNivelAmarillo_" + fila).show();
            $("#ckbNegro_" + fila).attr("disabled", false);
            $("#ckbCian_" + fila).attr("disabled", false);
            $("#ckbMagenta_" + fila).attr("disabled", false);
            $("#ckbAmarillo_" + fila).attr("disabled", false);
            $("#txtContadorNegroAnterior_" + fila).show().val(valor[0]);
            $("#txtContadorColorAnterior_" + fila).show().val(valor[1]);
            $("#txtNivelNegroAnterior_" + fila).show().val(valor[2]);
            $("#txtNivelCianAnterior_" + fila).show().val(valor[3]);
            $("#txtNivelMagentaAnterior_" + fila).show().val(valor[4]);
            $("#txtNivelAmarilloAnterior_" + fila).show().val(valor[5]);
            $("#txtfechaAnterior_" + fila).val(valor[6]);
        });
    } else {
        $("#txtContadorNegro_" + fila).hide();
        $("#txtContadorColor_" + fila).hide();
        $("#txtNivelNegro_" + fila).hide();
        $("#txtNivelCian_" + fila).hide();
        $("#txtNivelMagenta_" + fila).hide();
        $("#txtNivelAmarillo_" + fila).hide();
        $("#ckbNegro_" + fila).attr("disabled", true);
        $("#ckbCian_" + fila).attr("disabled", true);
        $("#ckbMagenta_" + fila).attr("disabled", true);
        $("#ckbAmarillo_" + fila).attr("disabled", true);
        $("#ckbNegro_" + fila).prop('checked', false);
        $("#ckbCian_" + fila).prop('checked', false);
        $("#ckbMagenta_" + fila).prop('checked', false);
        $("#ckbAmarillo_" + fila).prop('checked', false);
        $("#txtContadorNegroAnterior_" + fila).hide();
        $("#txtContadorColorAnterior_" + fila).hide();
        $("#txtNivelNegroAnterior_" + fila).hide();
        $("#txtNivelCianAnterior_" + fila).hide();
        $("#txtNivelMagentaAnterior_" + fila).hide();
        $("#txtNivelAmarilloAnterior_" + fila).hide();
    }
}


function MostrarContadoresFalla(fila, serie, tipoServicio) {
    if ($("#filaSeleccionada").val() != "") {
        var filaSeleccionada = $("#filaSeleccionada").val();
        $("#txtContadorNegro_" + filaSeleccionada).rules("remove");
        $("#txtContadorNegro_" + filaSeleccionada).val("");
        $("#txtContadorNegroAnterior_" + filaSeleccionada).val("");
        if ($("#txtContadorColorAnterior_" + filaSeleccionada).length) {
            $("#txtContadorColor_" + filaSeleccionada).rules("remove");
            $("#txtContadorColor_" + filaSeleccionada).val("");
            $("#txtContadorColorAnterior_" + filaSeleccionada).val("");
        }
    }
    $("#filaSeleccionada").val(fila);
    $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "LecturaNoSerie", "NoSerie": serie, "tipo": "1"}).done(function (data) {
        var valor = data.split(" / ");
        $("#txtContadorNegroAnterior_" + fila).show().val(valor[0]);
        if (tipoServicio == "1")
            $("#txtContadorColorAnterior_" + fila).show().val(valor[1]);
        $("#txtfechaAnterior_" + fila).show().val(valor[6]);
        jQuery.validator.addMethod('validarContadorNegroToner', function (value) {
            if (parseInt($("#txtContadorNegro_" + fila).val()) < parseInt($("#txtContadorNegroAnterior_" + fila).val())) {
                return false;
            }
            else {
                return true;
            }
        }, " * El contador debe ser igual o mayor al contador anterior");
        $("#txtContadorNegro_" + fila).rules("add", {required: true, number: true, validarContadorNegroToner: true, messages: {required: " * Ingrese la el contador", number: " * Ingrese solo números"}});
        if (tipoServicio == "1") {
            jQuery.validator.addMethod('validarContadorColorToner', function (value) {
                if (parseInt($("#txtContadorColor_" + fila).val()) < parseInt($("#txtContadorColorAnterior_" + fila).val())) {
                    return false;
                }
                else {
                    return true;
                }
            }, " * El contador debe ser igual o mayor al contador anterior");
            $("#txtContadorColor_" + fila).rules("add", {required: true, number: true, validarContadorColorToner: true, messages: {required: " * Ingrese la el contador", number: " * Ingrese solo números"}});
        }

    });
}
function AgregarIncidencia(noSerie, ticket, tipo, centroCosto, descripcion) {
    var localidad = "";
    var fecha = getFecha();
    $('#mensajes').load("WEB-INF/Controllers/Controler_Incidencia.php", {"NoSerie": noSerie, "id_ticket": ticket, "Descripcion": descripcion, "cc": localidad, "status": 1, "tipo": tipo, "Fecha": fecha, "FechaFin": fecha}, function () {

    });
}
function mostrarDetalleNota(pagina, idNota, tipoReporte) {
    loading("Cargando ...");
    $("#detalleNota").load(pagina, {"idNota": idNota, "tipoReporte": tipoReporte}, function () {
        $("#detalleNota").dialog({
            resizable: false,
            height: 'auto',
            width: 'auto',
            modal: true,
            title: "Detalle nota",
            buttons: {
                "Cancelar": function () {
                    $(this).dialog("close");
                }
            }
        });
        finished();
    });
}
function AgregarNotaTicket(pagina, area, descripcion) {
    loading("Cargando ...");
    if (area == "15")
        area = 2;
    $('#contenidos').load(pagina, {"area": area, "id": descripcion}, function () {
        finished();
    });
}
function ReportarEquipoNoExistente() {
    $("#correoExistencia").dialog({
        resizable: false,
        height: 'auto',
        width: 'auto',
        modal: true,
        title: "Enviar correo electronico",
        buttons: {
            "Enviar": function () {
                $(this).dialog("close");
            },
            "Cancelar": function () {
                $(this).dialog("close");
            }
        }
    });
}
function bucarVentaDirectaToner(fila, noSerie, tipo) {
    loading("Cargando ...");
    if (noSerie != "") {
        $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarDatosPorSerieVentaDirecta", "NoSerie": noSerie}).done(function (data) {
            var datos = data.split(" // ");
            if (datos[0] == "1" || datos[0] == "-1") {
                if (datos[0] == "1") {
                    $("#dialog").html("Equipo <b>" + noSerie + "</b> vendido, avisar al ejecutivo para enviar cotización.");
                } else {
                    $("#dialog").html("Equipo <b>" + noSerie + "</b> en demo");
                }
                $("#activar_" + fila).prop("checked", false);
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {
                                if (datos[0] == "1") {
                                    $('#mensajes').load("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "EnviarMailCotizacionVD", "cliente": datos[1], "ejecutivo": datos[2], "noSerie": noSerie}, function () {
                                        finished();
                                        $("#txtNoSrieFallaBuscar").val(noSerie);
                                        incidenciaByTicket(fila, noSerie, tipo);
                                    });
                                    finished();
                                    $(this).dialog("close");
                                } else {
                                    finished();
                                    $(this).dialog("close");
                                }
                            },
                            "Cancelar": function () {
                                finished();
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            } else {
                finished();
                incidenciaByTicket(fila, noSerie, tipo);
            }
        });
    } else {
        finished();
        incidenciaByTicket(fila, noSerie, tipo);
    }
}
function bucarVentaDirecta() {
    loading("Cargando ...");
    var noSerie = $("#txtNoSrieFallaBuscar").val();
    if (noSerie != "") {
        $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarDatosPorSerieVentaDirecta", "NoSerie": noSerie}).done(function (data) {
            var datos = data.split(" // ");
            if (datos[0] == "1" || datos[0] == "-1") {
                if (datos[0] == "1") {
                    $("#dialog").html("Equipo <b>" + noSerie + "</b> vendido, avisar al ejecutivo para enviar cotización.");
                } else {
                    $("#dialog").html("Equipo <b>" + noSerie + "</b> en demo");
                }
                $("#activar_" + fila).prop("checked", false);
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {
                                if (datos[0] == "1") {
                                    $("#txtNoSrieFallaBuscar").val(noSerie);
                                    $('#mensajes').load("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "EnviarMailCotizacionVD", "cliente": datos[1], "ejecutivo": datos[2], "noSerie": noSerie}, function () {
                                        finished();
                                        buscarDatosParaIncidencia();
                                    });
                                    $(this).dialog("close");
                                } else {
                                    $(this).dialog("close");
                                    finished();
                                }
                            },
                            "Cancelar": function () {
                                finished();
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            } else {
                finished();
                buscarDatosParaIncidencia();
            }
        });
    } else {
        finished();
        buscarDatosParaIncidencia();
    }
}

function buscarDatosParaIncidencia() {
    var noSerie = $("#txtNoSrieFallaBuscar").val();
    if (noSerie != "") {
        loading("Cargando ...");
        $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarDatosPorSerieFalla", "NoSerie": noSerie}).done(function (data) {
            var datos = data.split(" / ");
            if (datos[0] == "1") {
                $("#dialog").html("El cliente <b>" + datos[2] + "</b> se encuentra suspendido, no se puede levantar ticket");
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {
                                finished();
                                $(this).dialog("close");
                            }
                        }
                    });
                });
            } else {
                if (datos[1] == "2") {
                    if (datos[6] == "1") {//Si se permite levantar tickets a morosos
                        $("#dialog").html("El cliente <b>" + datos[4] + "</b> se encuentra como moroso");
                    } else {
                        $("#dialog").html("El cliente <b>" + datos[4] + "</b> se encuentra como moroso, no se puede levantar ticket");
                    }
                    $(function () {
                        $("#dialog").dialog({
                            resizable: false,
                            height: 200,
                            modal: true,
                            closeOnEscape: false,
                            open: function (event, ui) {
                                $(".ui-dialog-titlebar-close", ui.dialog).hide();
                            },
                            buttons: {
                                "Aceptar": function () {//crear incidencia
                                    var datos1 = data.split(" / ");
                                    $("#slcCliente option[value='" + datos1[5] + "']").attr("selected", true);
                                    $(this).dialog("close");
                                    finished();
                                    if (datos[6] == "1") {//Si se permite levantar tickets a morosos
                                        incidenciaByTicketFalla(noSerie, datos1[2], datos1[3]);
                                        mostrarLocalidadTicket(datos1[5], noSerie);
                                    }
                                }
                            }
                        });
                    });
                } else {
                    incidenciaByTicketFalla(noSerie, datos[2], datos[3]);
                }
            }
        });
    }
}
function incidenciaByTicketFalla(serie, idTicket, dias) {
    if (idTicket != "") {
        $("#dialog").html("Existe un ticket abierto de falla para el No. de Serie proporcionado: ticket <b>" + idTicket + "</b>");
        $(function () {
            $("#dialog").dialog({
                resizable: false,
                height: 200,
                modal: true,
                closeOnEscape: false,
                open: function (event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog).hide();
                },
                buttons: {
                    "Continuar": function () {
                        $(this).dialog("close");
                        incidenciaxDiasFalla(serie, dias);
                    },
                    "Ir al ticket": function () {
                        loading("Cargando ...");
                        $('#contenidos').load("mesa/alta_ticket2.php", {"idTicket": idTicket, "area": "1", "detalle": "0"}, function () {
                            finished();
                        });
                        $(this).dialog("close");
                    }
                }
            });
        });
    } else {

        buscarNoSerie(serie, dias);
    }
}
function incidenciaxDiasFalla(serie, dias) {
    if (parseInt(dias) < 30) {
        $("#dialog").html("Se registro un ticket para el equipo <b>" + serie + "</b> seleccionado en menos de 30 dias");
        $(function () {
            $("#dialog").dialog({
                resizable: false,
                height: 200,
                modal: true,
                closeOnEscape: false,
                open: function (event, ui) {
                    $(".ui-dialog-titlebar-close", ui.dialog).hide();
                },
                buttons: {
                    "Continuar": function () {
                        $(this).dialog("close");
                        buscarNoSerie(serie);
                    },
                    "Cancelar": function () {
                        finished();
                        $("#txtNoSrieFallaBuscar").val("");
                        $(this).dialog("close");
                    }
                }
            });
        });
    } else {
        buscarNoSerie(serie);
    }

}

function buscarNoSerie(noSerie) {
    var tipo = $("#sltTipoReporte").val();
    $('#contenidos').load("mesa/alta_ticket2.php", {"noSerie": noSerie, "area": tipo}, function () {
        finished();
    });
}

function incidenciaClienteSuspendidoFalla(cliente) {
    $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarCliente", "cliente": cliente, "tipo": "15"}).done(function (data) {
        var valores = data.split(" / ");
        if (valores[0] == "1") {
            $("#dialog").html("El cliente <b>" + valores[2] + "</b> se encuentra suspendido, no se puede levantar ticket");
            $(function () {
                $("#dialog").dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    closeOnEscape: false,
                    open: function (event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Aceptar": function () {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        } else {
            if (valores[1] == "2") {
                if (valores[3] == "1") { //Si se permite agregar tickets a morosos
                    $("#dialog").html("El cliente <b>" + valores[2] + "</b> se encuentra como moroso");
                } else {
                    $("#dialog").html("El cliente <b>" + valores[2] + "</b> se encuentra como moroso, no se puede levantar ticket");
                }
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {//crear incidencia
                                if (valores[3] == "1") { //Si se permite agregar tickets a morosos
                                    mostrarLocalidadTicket(cliente);
                                }
                                $(this).dialog("close");
                                finished();
                            }
                        }
                    });
                });
            } else {
                mostrarLocalidadTicket(cliente);
            }
        }
    });
}
function CambioLocalidadTicket(localidad) {
    loading("Cargando ...");
    $.post("WEB-INF/Controllers/Validacion/Controller_Localidad_Valida.php", {"localidad": localidad}).done(function (data) {
        var tipo = data.substring(0, 1);
        var mensaje = data.substring(2);
        if (tipo === "0") {
            $("#dialog").html("<h3>" + mensaje + "</h3>");
            $(function () {
                $("#dialog").dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    closeOnEscape: false,
                    open: function (event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Aceptar": function () {
                            $(this).dialog("close");
                        }
                    }
                });
            });
        } else {
            if ($("#idTicket").val() != "") {
                $("#dialog").html("Si cambia de localidad generara un nuevo ticket. ¿Desea continuar?");
                $(function () {
                    $("#dialog").dialog({
                        resizable: false,
                        height: 200,
                        modal: true,
                        closeOnEscape: false,
                        open: function (event, ui) {
                            $(".ui-dialog-titlebar-close", ui.dialog).hide();
                        },
                        buttons: {
                            "Aceptar": function () {//crear incidencia
                                //MostrarEquipoLocalidadFalla(localidad);
                                $(this).dialog("close");
                            },
                            "Cancelar": function () {
                                $(this).dialog("close");
                                $("#slcLocalidad option[value='" + localidad + "']").attr("selected", true);
                                finished();
                            }
                        }
                    });
                });
            } else {
                MostrarEquipoLocalidadFalla(localidad);
            }
        }
    });
}
function MostrarEquipoLocalidadFalla(localidad) {
    var claveCliente = $("#slcCliente").val();
    var tipoReporte = $("#sltTipoReporte").val();
    $('#contenidos').load("mesa/alta_ticket2.php", {"claveCliente": claveCliente, "mostrarDatos": tipoReporte, "claveLocalidad": localidad}, function () {
        finished();
    });
}
function incidenciaByTicketFallaCliente(serie) {
    $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarDatosPorSerieVentaDirecta", "NoSerie": serie}).done(function (data) {
        var datos = data.split(" // ");
        if (datos[0] == "1" || datos[0] == "-1") {
            if (datos[0] == "1") {
                $("#dialog").html("Equipo <b>" + serie + "</b> vendido, avisar al ejecutivo para enviar cotización.");
            } else {
                $("#dialog").html("Equipo <b>" + serie + "</b> en demo");
            }
            $("#activar_" + fila).prop("checked", false);
            $(function () {
                $("#dialog").dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    closeOnEscape: false,
                    open: function (event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Aceptar": function () {
                            if (datos[0] == "1") {
                                $('#mensajes').load("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "EnviarMailCotizacionVD", "cliente": datos[1], "ejecutivo": datos[2], "noSerie": serie}, function () {
                                    finished();
                                    $("#txtNoSrieFallaBuscar").val(serie);
                                    buscarDatosParaIncidencia();
                                });
                                finished();
                                $(this).dialog("close");
                            } else {
                                finished();
                                $(this).dialog("close");
                            }

                        },
                        "Cancelar": function () {
                            finished();
                            $('input:radio[name=rdEquipoFalla]').attr('checked', false);
                            $(this).dialog("close");
                        }
                    }
                });
            });
        } else {
            finished();
            loading("Cargando ...");
            $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuscarDatosPorSerieFalla", "NoSerie": serie}).done(function (data) {
                var datos = data.split(" / ");
                var idTicket = datos[2];
                var dias = datos[3];
                if (idTicket != "") {
                    $("#dialog").html("Existe un ticket abierto de falla para el No. de Serie proporcionado: ticket <b>" + idTicket + "</b>");
                    $(function () {
                        $("#dialog").dialog({
                            resizable: false,
                            height: 200,
                            modal: true,
                            closeOnEscape: false,
                            open: function (event, ui) {
                                $(".ui-dialog-titlebar-close", ui.dialog).hide();
                            },
                            buttons: {
                                "Continuar": function () {
                                    $(this).dialog("close");
                                    incidenciaxDiasFalla(serie, dias);
                                },
                                "Ir al ticket": function () {
                                    loading("Cargando ...");
                                    $('#contenidos').load("mesa/alta_ticket2.php", {"idTicket": idTicket, "area": "1", "detalle": "0"}, function () {
                                        finished();
                                    });
                                    $(this).dialog("close");
                                }
                            }
                        });
                    });
                } else {
                    incidenciaxDiasFalla(serie, dias);
                }
            });
        }
    });
}
function BuscarEquiposNumeroSerieLocalidad() {
    if (validar()) {
        $("#errorSelectNoSerie").html("");
        var claveCliente = $("#slcCliente").val();
        var tipoReporte = $("#sltTipoReporte").val();
        var localidad = $("#slcLocalidad").val();
        loading("Cargando ...");
        $('#contenidos').load("mesa/alta_ticket2.php", {"claveCliente": claveCliente, "mostrarDatos": tipoReporte, "claveLocalidad": localidad, "listSerie": getSelected()}, function () {
            finished();
        });
    } else {
        alert("Seleccione por lo menos un no. serie");
    }

}
function validar() {
    if (getSelected() === "") {
        $("#errorSelectNoSerie").html("Necesitas seleccionar al menos una opcion");
        return false;
    }
    return true;
}
function getSelected() {
    var str = "";
    $("#selectNoSerie option:selected").each(function () {
        str += $(this).val() + " / ";
    });
    return str;
}

function BuscarEquipoCliLocEqui(origen, cliente, localidad, equipo) {
    var noserie = $("#" + origen).val();
    $("#" + cliente).load("WEB-INF/Controllers/mesa/Controller_Cliente_Equipo.php", {id: noserie}, function (data) {
        if (data.toString().indexOf("Error:") === -1) {
            $("#" + cliente).multiselect({
                multiple: false,
                noneSelectedText: "No ha seleccionado",
                selectedList: 1
            }).multiselectfilter({
                label: 'Filtro',
                placeholder: 'Escribe el filtro'
            });
            $("#" + localidad).load("WEB-INF/Controllers/mesa/Controller_Localidad_Equipo.php", {id: noserie}, function (data) {
                if (data.toString().indexOf("Error:") === -1) {
                    $("#" + localidad).multiselect({
                        multiple: false,
                        noneSelectedText: "No ha seleccionado",
                        selectedList: 1
                    }).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });


                    $.post("WEB-INF/Controllers/Controler_BuscarDatosIncidencia.php", {"buscar": "BuxcarLocalidad", "localidad": $("#" + localidad).val()}).done(function (data) {
                        var valores = data.split(" / ");
                        if (data != "" && valores[0] != "" && (!$("#permisoTicketMiniAlmacen").length || $("#permisoTicketMiniAlmacen").val() != "1")) {
                            $("#dialog").html("La localidad  <b>" + valores[1] + "</b> tiene un mini almacén , no se puede levantar ticket, favor de ir a la opción de cambiar toner");
                            $(function () {
                                $("#dialog").dialog({
                                    resizable: false,
                                    height: 200,
                                    modal: true,
                                    closeOnEscape: false,
                                    open: function (event, ui) {
                                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                                    },
                                    buttons: {
                                        "Aceptar": function () {
                                            finished();
                                            $(this).dialog("close");
                                        }
                                    }
                                });
                            });
                        } else {
                            $("#" + equipo).load("WEB-INF/Controllers/mesa/Controller_Equipos_Localidad.php", {id: noserie}, function (data) {
                                if (data.toString().indexOf("Error:") === -1) {
                                    $("#" + equipo).multiselect({
                                        multiple: true,
                                        noneSelectedText: "No ha seleccionado",
                                        selectedList: 1
                                    }).multiselectfilter({
                                        label: 'Filtro',
                                        placeholder: 'Escribe el filtro'
                                    });
                                }
                                //BuscarEquiposNumeroSerieLocalidad();
                            });
                        }
                    });
                }

            });
        } else {
            $("#dialog").html(data);
            $(function () {
                $("#dialog").dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    closeOnEscape: false,
                    open: function (event, ui) {
                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                    },
                    buttons: {
                        "Aceptar": function () {
                            $(this).dialog("close");
                            finished();
                        }
                    }
                });
            });
        }
    });
}
function validarRendimiento(cktoner, color, fila, cian, magenta, amarillo) {
    var contadorAnterior = "";
    var contadorActual = "";
    var noParte = "";
    var totalContadorUso = "";
    if (color === 0) {//toner negro
        contadorAnterior = $("#txtContadorNegroAnterior_" + fila).val();
        contadorActual = $("#txtContadorNegro_" + fila).val();
        noParte = $("#txtTonerNegro" + fila).val();
        if (noParte == "") {
            $("input[name=" + cktoner + "]").attr('checked', false);
            $("#error_toner_compatible_negro" + fila).html(" * Selecciona toner compatible negro");
            return false;
        }
    } else {//toner color
        contadorAnterior = $("#txtContadorColorAnterior_" + fila).val();
        contadorActual = $("#txtContadorColor_" + fila).val();
        if (cian == "1") {//toner cian
            noParte = $("#txtTonerCian" + fila).val();
            if (noParte == "") {
                $("input[name=" + cktoner + "]").attr('checked', false);
                $("#error_toner_compatible_cian" + fila).html(" * Selecciona toner compatible cian");
                return false;
            }
        } else if (magenta == "1") {//toner magenta
            noParte = $("#txtTonerMagenta" + fila).val();
            if (noParte == "") {
                $("input[name=" + cktoner + "]").attr('checked', false);
                $("#error_toner_compatible_magenta" + fila).html(" * Selecciona toner compatible magenta");
                return false;
            }
        } else if (amarillo == "1") {//toner cia
            noParte = $("#txtTonerAmarillo" + fila).val();

            if (noParte == "") {
                $("input[name=" + cktoner + "]").attr('checked', false);
                $("#error_toner_compatible_amarillo" + fila).html(" * Selecciona toner compatible amarillo");
                return false;
            }
        }
    }
    $("#error_toner_compatible_negro" + fila).html("");
    $("#error_toner_compatible_cian" + fila).html("");
    $("#error_toner_compatible_magenta" + fila).html("");
    $("#error_toner_compatible_amarillo" + fila).html("");
    $("#divErrorContNegro" + fila).html("");
    $("#divErrorContColor" + fila).html("");
    if ($("#" + cktoner).is(":checked")) {
        if (contadorAnterior !== "") {
            if (contadorActual !== "") {
                $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"noParteComponenteRendimiento": noParte}).done(function (rendimiento) {//obtener el rendimiento del toner
                    if (parseInt(rendimiento) > 0) {
                        if (parseInt(contadorActual) >= parseInt(contadorAnterior)) {
                            totalContadorUso = parseInt(contadorActual) - parseInt(contadorAnterior);
                            var porcentaje = (parseInt(totalContadorUso) * 100) / parseInt(rendimiento);
                            $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"porcRendimiento": "0"}).done(function (porcentajeMinimo) {//obtener porcentaje de rendimiento de los toner
                                if (parseInt(porcentaje) > parseInt(porcentajeMinimo)) {
                                    $("input[name=" + cktoner + "]").attr('checked', true);
                                } else {
                                    if ($("#txtPermisoRendimiento").val() == "1") {//permiso de rendimiento
                                        $("#dialog").html("El consumo fue de " + totalContadorUso + " impresiones. El rendimiento del tóner es de " + rendimiento + " impresiones. ¿Desea continuar?");
                                        $(function () {
                                            $("#dialog").dialog({
                                                resizable: false, height: 200, modal: true, closeOnEscape: false,
                                                open: function (event, ui) {
                                                    $(".ui-dialog-titlebar-close", ui.dialog).hide();
                                                },
                                                buttons: {
                                                    "Cancelar": function () {
                                                        $(this).dialog("close");
                                                        $("input[name=" + cktoner + "]").attr('checked', false);
                                                    },
                                                    "Continuar": function () {
                                                        $("input[name=" + cktoner + "]").attr('checked', true);
                                                        $(this).dialog("close");
                                                    }
                                                }
                                            });
                                        });
                                    } else {//sin permiso de rendimiento
                                        $("#dialog").html("El consumo fue de " + totalContadorUso + " impresiones. El rendimiento del tóner es de " + rendimiento + " impresiones. Comunicarse con almacén para verificar rendimiento del tóner");
                                        $(function () {
                                            $("#dialog").dialog({
                                                resizable: false, height: 200, modal: true, closeOnEscape: false,
                                                open: function (event, ui) {
                                                    $(".ui-dialog-titlebar-close", ui.dialog).hide();
                                                },
                                                buttons: {
                                                    "Aceptar": function () {
                                                        $(this).dialog("close");
                                                        $("input[name=" + cktoner + "]").attr('checked', false);
                                                    }
                                                }
                                            });
                                        });
                                    }
                                }
                            });
                        } else {
                            $("input[name=" + cktoner + "]").attr('checked', false);
                            if (color === 0) {
                                $("#divErrorContNegro" + fila).html("El contador negro actual debe ser mayor o igual al contador anterior").css("color", "red");
                            } else {
                                $("#divErrorContColor" + fila).html("El contador color actual debe ser mayor o igual al contador anterior").css("color", "red");
                            }

                        }
                    }
                });
            } else {
                $("input[name=" + cktoner + "]").attr('checked', false);
                if (color === 0) {
                    $("#divErrorContNegro" + fila).html("Ingrese el contador negro actual").css("color", "red");
                } else {
                    $("#divErrorContColor" + fila).html("Ingrese el contador color actual").css("color", "red");
                }
            }
        }
    }
}
function verificarContadores(fila, color) {
    var contadorAnterior = "";
    var contadorActual = "";
    if (color === 0) {
        contadorAnterior = $("#txtContadorNegroAnterior_" + fila).val();
        contadorActual = $("#txtContadorNegro_" + fila).val();
        if (parseInt(contadorActual) <= parseInt(contadorAnterior)) {
            $("input[name=ckbNegro_" + fila + "]").attr('checked', false);
        }
    } else {
        contadorAnterior = $("#txtContadorColorAnterior_" + fila).val();
        contadorActual = $("#txtContadorColor_" + fila).val();
        if (parseInt(contadorActual) <= parseInt(contadorAnterior)) {
            $("input[name=ckbCian_" + fila + "]").attr('checked', false);
            $("input[name=ckbMagenta_" + fila + "]").attr('checked', false);
            $("input[name=ckbAmarillo_" + fila + "]").attr('checked', false);
        }
    }

}
