var form = "#FormLectura";
var filas = 0;
$(document).ready(function () {
    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            cliente: {selectcheck: true},
            contrato: {required: true},
            anexo: {required: true}
        },
        messages: {
            contrato: {required: " * seleccione el contrato"},
            anexo: {required: " * seleccione el anexo"}
        }
    });

    $(".button").button();

    if (!$("#paramatros_lecturas").length) {
        $('.fecha').mask("99-9999");
        $(".select").multiselect({
            multiple: false,
            noneSelectedText: "No ha seleccionado",
            selectedList: 1
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    }
    $('.ui-multiselect').css('width', '150px');
    jQuery(function ($) {
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

    $('.fecha').each(function () {
        $(this).datepicker({
            changeMonth: true,
            changeYear: true,
            dateFormat: 'mm/yy',
            onClose: function () {
                var iMonth = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var iYear = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(iYear, iMonth, 1));
            }
        });

    });


    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            return true;
        } else {
            return false;
        }
    });

    if ($("#required_fecha").length && $("#required_fecha").val() === "1") {
        $("#cliente").rules("remove");
        $("#contrato").rules("remove");
        $("#anexo").rules("remove");
        $("#fecha").rules('add', {
            required: true,
            messages: {
                required: " * Ingrese la fecha"
            }
        });
    }
});

function guardarParametros() {
    if ($(form).valid()) {
        var controlador;
        if (!$("#independiente").length) {
            controlador = "WEB-INF/Controllers/facturacion/Controller_Parametros_Lectura.php";
            loading("Cargando ...");
        } else {
            controlador = "../WEB-INF/Controllers/facturacion/Controller_Parametros_Lectura.php";
            $("#mensaje_anexo2").html("Guardando configuración ...");
        }
        /* stop form from submitting normally */
        /*Serialize and post the form*/
        $.post(controlador, {form: $(form).serialize()}).done(function (data) {
            if (!$("#independiente").length) {
                $('#mensajes').html(data);
                finished();
            } else {
                if (data == "") {
                    $("#mensaje_anexo2").html("Configuración guardada exitosamente");
                } else {
                    $("#mensaje_anexo2").html(data);
                }
            }
        });
    }

}

function refrescarMulti() {
    $(".select").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $('.ui-multiselect').css('width', '150px');
}

function cargarContratos(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/facturacion/Controller_Selects.php", {id: $("#" + origen).val(), tipo: 1}, function (data) {
        refrescarMulti();
        cargarZona('contrato', 'zona');
        cargarAnexos('contrato', 'anexo');
    });
}
function cargarZona(origen, componente) {
    $("#" + componente).load("WEB-INF/Controllers/facturacion/Controller_Selects.php", {id: $("#" + origen).val(), tipo: 2}, function (data) {
        cargarCentroCosto('cliente', 'centro_costo', 'contrato', 'zona');
        refrescarMulti();
    });
}
function cargarclientes(origen, componente) {
    $("#parametros_lectura").empty();
    $("#" + componente).load("WEB-INF/Controllers/Ventas/Controller_select_clientes.php", {cliente: $("#" + origen).val(), 'modalidad': 'arrendamiento'}, function (data) {
        cargarZona('zona', 'centro_costo');
        refrescarMulti();
    });
}

function cargarLocalidad(origen, destino, cc) {
    $("#" + destino).load("WEB-INF/Controllers/facturacion/Controller_Selects.php", {cliente: $("#" + origen).val(), 'centro': $("#" + cc).val(), tipo: 5}, function (data) {
        refrescarMulti();
    });
}

function cargarCentroCosto(origen, destino, contrato, zona) {
    $("#" + destino).load("WEB-INF/Controllers/facturacion/Controller_Selects.php", {cliente: $("#" + origen).val(), 'contrato': $("#" + contrato).val(), 'zona': $("#" + zona).val(), 'tipo': 4}, function (data) {
        refrescarMulti();
        cargarLocalidad('cliente', 'localidad');
    });
}

function cargarAnexos(origen, destino) {
    $("#parametros_lectura").empty();
    $("#" + destino).load("WEB-INF/Controllers/facturacion/Controller_Selects.php", {contrato: $("#" + origen).val(), 'tipo': 6}, function (data) {
        refrescarMulti();
    });
}

function cargarLectura() {
    loading("Cargando ...");
    $("#parametros_lectura").empty();
    $("#parametros_lectura").load("facturacion/Parametros_Lectura.php", {anexo: $("#anexo").val()}, function () {
        finished();
        $(".button").button();
        if ($("#anexo").val() === "") {
            //$("#num_orden").rules('remove', 'required');
            $("#num_prov").rules('remove', 'required');
        } else {
            $("#boton_guardar").show();
            /*$("#num_orden").rules('add', {
                number: true,
                messages: {
                    number: "Ingresa un número"
                }
            });*/
            $("#num_prov").rules('add', {
                number: true,
                messages: {
                    number: "Ingresa un número"
                }
            });
        }
        refrescarMulti();
        $("#submit_lecturas2").show();
    });

}

function validarSerNom(id) {
    if ($("#check_serv_nom_" + id).is(':checked')) {
        $("#text_serv_nom_" + id).removeAttr('disabled');
        $("#text_serv_nom_" + id).rules('add', {
            required: true,
            messages: {
                required: "Ingresa el Nombre"
            }
        });
    } else {
        $("#text_serv_nom_" + id).attr('disabled', 'disabled');
        $("#text_serv_nom_" + id).rules('remove', 'required');
    }
}

function validarSerUni(id) {
    if ($("#check_serv_uni_" + id).is(':checked')) {
        $("#text_serv_renta_" + id).removeAttr('disabled');
        $("#text_serv_renta_" + id).rules('add', {
            required: true,
            messages: {
                required: "Ingresa la unidad"
            }
        });
        $("#text_serv_excedente_" + id).removeAttr('disabled');
        $("#text_serv_excedente_" + id).rules('add', {
            required: true,
            messages: {
                required: "Ingresa la unidad"
            }
        });
        $("#text_serv_impresiones_" + id).removeAttr('disabled');
        $("#text_serv_impresiones_" + id).rules('add', {
            required: true,
            messages: {
                required: "Ingresa la unidad"
            }
        });
    } else {
        $("#text_serv_renta_" + id).attr('disabled', 'disabled');
        $("#text_serv_renta_" + id).rules('remove', 'required');
        $("#text_serv_excedente_" + id).attr('disabled', 'disabled');
        $("#text_serv_excedente_" + id).rules('remove', 'required');
        $("#text_serv_impresiones_" + id).attr('disabled', 'disabled');
        $("#text_serv_impresiones_" + id).rules('remove', 'required');
    }
    refrescarMulti();
}

function calcularImporte(id) {
    $("#importe_con_adic_" + id).val($("#preciounitario_con_adic_" + id).val() * $("#cantidad_con_adic_" + id).val());
}

function agregarServicio() {
    $("#concepto_tabla").append("<tr>"
            + "<td><select name='select_con_adic_" + filas + "' id='select_con_adic_" + filas + "'></td>"
            + "<td><input type='text' name='text_con_adic_" + filas + "' id='text_con_adic_" + filas + "' value='' /></td>"
            + "<td><input type='text' name='cantidad_con_adic_" + filas + "' id='cantidad_con_adic_" + filas + "' value='' onchange='calcularImporte(" + filas + ")' style='width: 50px'/></td>"
            + "<td><input type='text' name='preciounitario_con_adic_" + filas + "' id='preciounitario_con_adic_" + filas + "' value='' onchange='calcularImporte(" + filas + ")' style='width: 50px'/></td>"
            + "<td><input type='text' name='importe_con_adic_" + filas + "' id='importe_con_adic_" + filas + "' value='' readonly='readonly' style='width: 100px'/></td>"
            + "<td><select name='producto_adic_"+filas+"' id='producto_adic_"+filas+"' class='select'></select></td>"
            + "</tr>");
    cargarNivelFacturacion("select_con_adic_" + filas);
    /*Copiamos los productos del SAT*/
    var $options = $("#impresionesSAT > option").clone();
    $('#producto_adic_' + filas).append($options);
    
    $('#producto_adic_' + filas).multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width', '150px');
    
    $("#text_con_adic_" + filas).rules('add', {
        required: true,
        messages: {
            required: "Ingresa la descripcion"
        }
    });
    $("#cantidad_con_adic_" + filas).rules('add', {
        required: true,
        messages: {
            required: "Ingresa el monto"
        }
    });
    $("#preciounitario_con_adic_" + filas).rules('add', {
        required: true,
        messages: {
            required: "Ingresa el monto"
        }
    });
    
    $("#select_con_adic_" + filas).rules('add', {
        required: true,
        messages: {
            required: "Selecciona el nivel de facturación"
        }
    });
    filas++;
    $("#filas_conceptos").val(filas);
    refrescarMulti();
}

function cargarNivelFacturacion(id) {
    $("#" + id).load("WEB-INF/Controllers/facturacion/Controller_Selects.php", {tipo: 3});
}

function setFilas(fila) {
    filas = fila;
}

function eliminarFila() {
    if (filas == 0) {
        alert("No se pueden borrar mas.");
    } else {
        filas--;
        $("#text_con_adic_" + filas).rules('remove', 'required');
        $("#cantidad_con_adic_" + filas).rules('remove', 'required');
        $("#preciounitario_con_adic_" + filas).rules('remove', 'required');        
        $("#select_con_adic_" + filas).rules('remove', 'required');
        $("#concepto_tabla tr:last").remove();
        $("#filas_conceptos").val(filas);
    }
}

function borrarParametro(id) {
    if ($("#" + id).val() === "") {
        $("#parametros_lectura").empty();
    }
}