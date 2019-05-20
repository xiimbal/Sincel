$(document).ready(function () {
    var form = "#frmReporteCambioToner";
    var paginaExito = "tfs/ReportarCambioToner.php";
    var controlador = "WEB-INF/Controllers/Controler_ReportarCambioToner.php";
    $(".fecha").mask("9999-99-99");
    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");
    jQuery.validator.addMethod('contadorColor', function (value, element, param) {
        if ($("#txtContadorColorNuevo").length) {
            if ($("#txtContadorColorNuevo").val() != "")
                return true;
            else
                return false;
        } else
            return true;
    }, '* Ingresa el contador color');
    jQuery.validator.addMethod('numerico', function (value, element, param) {
        if ($("#txtNivelCainNuevo").length) {
            if (value !== "") {
                if ((value == parseInt(value, 10)))
                    return true;
                else
                    return false;
            } else
                return true;
        } else
            return true;

    }, '* Ingresa solo n\u00fameros');

    jQuery.validator.addMethod('minimoPor', function (value, element, param) {
        if ($("#txtNivelCainNuevo").length) {
            if (value !== "") {
                if (parseInt(value) < 0)
                    return false;
                else
                    return true;
            } else
                return true;
        } else
            return true;
    }, '* El valor debe ser de 0 a 100');
    jQuery.validator.addMethod('maximoPor', function (value, element, param) {
        if ($("#txtNivelCainNuevo").length) {
            if (value !== "") {
                if (parseInt(value) > 100)
                    return false;
                else
                    return true;
            } else
                return true;
        } else
            return true;

    }, '* El valor debe ser de 0 a 100');

    jQuery.validator.addMethod('ContadorMeyorIgual', function (value, element, param) {
        // if (parseInt($("#txtContadorBNNuevo").val()) < parseInt($("#lecturaCorteBN").val())) {
        if (parseInt($("#txtContadorBNNuevo").val()) < parseInt($("#txtContadorBNAnterior").val())) {
            return false;
        } else
            return true;

    }, '* El contador B/N debe ser mayor o igual al contador de cambio anterior');
    jQuery.validator.addMethod('ValidarContadorColor', function (value, element, param) {
        if ($("#txtNivelCainNuevo").length) {
            // if (parseInt($("#txtContadorColorNuevo").val()) < parseInt($("#lecturaCorteColor").val()))
            if (parseInt($("#txtContadorColorNuevo").val()) < parseInt($("#txtContadorColorAnterior").val()))
                return false;
            else
                return true;
        } else
            return true;

    }, '* El contador a color debe ser mayor o igual al contador de cambio anterior');

    /*validate form*/
    $(form).validate({
        rules: {
            cliente: {selectcheck: true},
            Localidad: {selectcheck: true},
            NoSerie: {selectcheck: true},
            toner: {selectcheck: true},
            txtContadorBNNuevo: {required: true, number: true, ContadorMeyorIgual: true},
            txtContadorColorNuevo: {contadorColor: true, number: true, ValidarContadorColor: true},
            txtNivelNegroNuevo: {number: true, min: 0, max: 100},
            txtNivelCainNuevo: {numerico: true, maximoPor: true, minimoPor: true},
            txtNivelMagentaNuevo: {numerico: true, maximoPor: true, minimoPor: true},
            txtNivelAmarilloNuevo: {numerico: true, maximoPor: true, minimoPor: true},
        },
        messages: {
            txtContadorBNNuevo: {required: " * Ingrese el contador B/N", number: " * Ingresa solo n\u00fameros"},
            txtContadorColorNuevo: {number: " * Ingresa solo n\u00fameros"},
            txtNivelNegroNuevo: {number: " * Ingresa solo n\u00fameros", min: "* El valor debe ser de 0 a 100", max: "* El valor debe ser de 0 a 100"}
        }
    });

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            validarRendimientoToner(controlador, paginaExito, form);
            // controler(controlador, paginaExito, form);

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
});

function controler(controlador, paginaExito, form) {    
    $.post(controlador, {form: $(form).serialize()})
            .done(function (data) {                                
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function () {
                        finished();
                    });
                } else {
                    finished();
                }
            });
}

function MostrarClientesTFS(idUsuario) {
    loading("Cargando ...");
    $('#contenidos').load("tfs/ReportarCambioToner.php", {"usuarioTFS": idUsuario}, function () {
        finished();
    });
}
function MostrarLocalidadCliente(claveCliente) {
    var idUsuario = "";
    if ($("#tfS").length)
        idUsuario = $("#tfS").val();
    if (idUsuario == "")
        idUsuario = "S0";
    loading("Cargando ...");
    $('#contenidos').load("tfs/ReportarCambioToner.php", {"claveCliente": claveCliente, "usuarioTFS": idUsuario}, function () {
        finished();
    });
}
function MostrarEquiposLocalidad(claveLocalidad) {
    var idUsuario = "";
    if ($("#tfS").length)
        idUsuario = $("#tfS").val();
    var claveCliente = $("#cliente").val();
    loading("Cargando ...");
    $('#contenidos').load("tfs/ReportarCambioToner.php", {"claveLocalidad": claveLocalidad, "claveCliente": claveCliente, "usuarioTFS": idUsuario}, function () {
        finished();
    });
}

function MostrarComponentesCompatible(noParte) {
    var datos = noParte.split(" / ");
    var idUsuario = "";
    if ($("#tfS").length) {
        idUsuario = $("#tfS").val();
    }
    var claveCliente = $("#cliente").val();
    var claveLocalidad = $("#Localidad").val();
    loading("Cargando ...");
    //alert(datos[0]+" "+datos[1]+" "+claveLocalidad+" "+claveCliente+" "+idUsuario);
    $('#contenidos').load("tfs/ReportarCambioToner.php", {"noParteEquipo": datos[0], "noSerieEquipos": datos[1],
        "claveLocalidad": claveLocalidad, "claveCliente": claveCliente, "usuarioTFS": idUsuario}, function () {
        finished();
    });
}

function buscarSerie(NoSerie) {

    $("#error_serie").text("");
    $("#error_serie").hide();
    //alert("1");
    if ($("#" + NoSerie).val() != "") {
        var dir = "WEB-INF/Controllers/Ajax/CargaSelect.php";
        var SerieBuscar = $("#" + NoSerie).val();
        $.post(dir, {"NoSerie": $("#" + NoSerie).val(), 'cliente': true, 'minialmacen': true, 'tfs': true}).done(function (data) {//obtener el rendimiento del toner            
            if (data.toString().indexOf("Error:") === -1) {
                var dataJson = eval(data);
                for (var i in dataJson) {
                    $("#cliente").append('<option value="' + dataJson[i].ClaveCliente + '" selected="selected">' + dataJson[i].NombreCliente + '</option>');
                    $('#cliente').multiselect('refresh');
                    $("#Localidad").append('<option value="' + dataJson[i].ClaveCentroCosto + '" selected="selected">' + dataJson[i].CentroCostoNombre + '</option>');
                    $('#Localidad').multiselect('refresh');
                    $("#NoSerie").append('<option value="' + dataJson[i].NoParteEquipo + ' / ' + SerieBuscar + '" selected="selected">'
                            + SerieBuscar + ' / ' + dataJson[i].Modelo + '</option>');
                    //$('#NoSerie').multiselect( 'refresh' );
                    /*Refrescamos las opciones*/
                    var x = $("#NoSerie").find('option');
                    $("#NoSerie").multiselect("refresh", x).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    /*Refrescamos las opciones*/
                    $("#NoSerie").multiselect({
                        multiple: false,
                        noneSelectedText: "No ha seleccionado",
                        selectedList: 1
                    }).multiselectfilter({
                        label: 'Filtro',
                        placeholder: 'Escribe el filtro'
                    });
                    $("#NoSerie").css('width', '230px');
                    if ($("#tfS").length) {
                        $('#tfS').load(dir, {'tfs': true, 'cliente': dataJson[i].ClaveCliente}, function () {/*Refrescamos el select y volvemos a poner filtros*/
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
                            MostrarComponentesCompatible(dataJson[i].NoParteEquipo + ' / ' + SerieBuscar);
                        });
                    } else {
                        MostrarComponentesCompatible(dataJson[i].NoParteEquipo + ' / ' + SerieBuscar);
                    }
                }
                //alert("Poniendo Serie "+SerieBuscar);
                $("#buscar_serie").val(SerieBuscar);
            } else {
                $("#error_serie").text(data);
                $("#error_serie").show();
            }
        });
    } else {
        $("#error_serie").text("Ingresa el número de serie");
        $("#error_serie").show();
    }
}

function mostraContadoresToner(noParte) {
    var equipo = $("#NoSerie").val();
    var color = noParte.split(" // ");
    var serie = equipo.split(" / ");
    $("#txtFechaContadorAnterior").val("");
    $("#txtContadorBNAnterior").val("");
    $("#txtContadorColorAnterior").val("");
    $("#txtNivelNegroAnterior").val("");
    $("#txtNivelCainAnterior").val("");
    $("#txtNivelMagentaAnterior").val("");
    $("#txtNivelAmarilloAnterior").val("");
    $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"cargarContadoresPorColor": "1", "color": color[1], "serie": serie[1]}).done(function (data) {//obtener el rendimiento del toner
        var dataJson = eval(data);
        for (var i in dataJson) {
            $("#txtFechaContadorAnterior").val(dataJson[i].fecha);
            $("#txtContadorBNAnterior").val(dataJson[i].contadornegro);
            $("#txtContadorColorAnterior").val(dataJson[i].contadorcolor);
            $("#txtNivelNegroAnterior").val(dataJson[i].nivelnegro);
            $("#txtNivelCainAnterior").val(dataJson[i].nivelcia);
            $("#txtNivelMagentaAnterior").val(dataJson[i].nivelmagenta);
            $("#txtNivelAmarilloAnterior").val(dataJson[i].nivelamarillo);
        }
    });
//    var datos = noParte.split(" / ");
//    var idUsuario = "";
//    if ($("#tfS").length)
//        idUsuario = $("#tfS").val();
//    var claveCliente = $("#cliente").val();
//    var claveLocalidad = $("#Localidad").val();
//    loading("Cargando ...");
//    $('#contenidos').load("tfs/ReportarCambioToner.php", {"noParteEquipo": datos[0], "noSerieEquipos": datos[1], "claveLocalidad": claveLocalidad, "claveCliente": claveCliente, "usuarioTFS": idUsuario}, function() {
//        finished();
//    });
}
function validarRendimientoToner(controlador, paginaExito, form) {
    var contadorAnterior = "";
    var contadorNuevo = "";
    var totalContadores = "";
    var aux = $("#toner").val();
    var noParte = aux.split(" // ");
    if (noParte[1] == "1") {//si el toner es negro
        contadorAnterior = $("#txtContadorBNAnterior").val();
        contadorNuevo = $("#txtContadorBNNuevo").val();
    } else {
        contadorAnterior = $("#txtContadorColorAnterior").val();
        contadorNuevo = $("#txtContadorColorNuevo").val();
    }

    if (contadorAnterior == "") {
        controler(controlador, paginaExito, form);
    } else {
        $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"noParteComponenteRendimiento": noParte[0]}).done(function (rendimiento) {//obtener el rendimiento del toner
            if (parseInt(rendimiento) > 0) {
                if (contadorAnterior != null && contadorAnterior != "" && contadorNuevo != null && contadorNuevo != "") {
                    totalContadores = parseInt(contadorNuevo) - parseInt(contadorAnterior);
                } else {
                    totalContadores = "desconocido";
                }
                var porcentaje = (parseInt(totalContadores) * 100) / parseInt(rendimiento);
                $.post("WEB-INF/Controllers/Ajax/CargaSelect.php", {"porcRendimiento": "0"}).done(function (porcentajeMinimo) {//obtener porcentaje de rendimiento de los toner
                    if (parseInt(porcentaje) > parseInt(porcentajeMinimo) || porcentajeMinimo < 1) {
                        controler(controlador, paginaExito, form);
                    } else {
                        if ($("#txtPermisoRendimiento").val() == "1") {//permiso de rendimiento
                            $("#dialog").html("El consumo fue de " + totalContadores + " impresiones. El rendimiento del tóner es de " + rendimiento + " impresiones. ¿Desea continuar?");
                            $(function () {
                                $("#dialog").dialog({
                                    resizable: false, height: 200, modal: true, closeOnEscape: false,
                                    open: function (event, ui) {
                                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                                    },
                                    buttons: {
                                        "Cancelar": function () {
                                            $(this).dialog("close");
                                            finished();
                                        },
                                        "Continuar": function () {
                                            $(this).dialog("close");
                                            controler(controlador + "?incidencia", paginaExito, form);
                                        }
                                    }
                                });
                            });
                        } else {//sin permiso de rendimiento
                            $("#dialog").html("El consumo fue de " + totalContadores + " impresiones. El rendimiento del tóner es de " + rendimiento + " impresiones. <br/>Comunicarse con almacén para verificar rendimiento del tóner. <br/>Si desea continuar solicite autorización, en caso contrario, cancele.");
                            $(function () {
                                $("#dialog").dialog({
                                    resizable: false, height: 300, modal: true, closeOnEscape: false,
                                    open: function (event, ui) {
                                        $(".ui-dialog-titlebar-close", ui.dialog).hide();
                                    },
                                    buttons: {
                                        "Cancelar": function () {
                                            $(this).dialog("close");
                                            finished();
                                        },
                                        "Solicitar Autorización": function () {
                                            $(this).dialog("close");
                                            controler(controlador + "?pendiente_autorizar=1",paginaExito,form);
                                            finished();
                                        }
                                    }
                                });
                            });
                        }

                    }
                });
            } else {
                controler(controlador, paginaExito, form);
            }
        });
    }
}
