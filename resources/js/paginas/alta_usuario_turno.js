$(document).ready(function () {
    var form = "#formUsuario";
    var paginaExito = "";
    var controlador = "WEB-INF/Controllers/Controler_UsuarioTurno.php";

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    $.validator.addMethod("validateAlmacen", function (value, element) {
        if ($("#puesto").val() == "24") {
            if ($("#almacen").val() != 'null') {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Selecciona un almacén");

    /*validate form*/
    $(form).validate({
        rules: {
            usuario: {required: true, maxlength: 50, minlength: 2},
            nombre: {required: true, maxlength: 50, minlength: 2},
            paterno: {required: true, maxlength: 30, minlength: 2},
            puesto: {selectcheck: true},
            materno: {maxlength: 30, minlength: 2},
            correo: {email: true},
            perfil: {selectcheck: true},
            almacen: {validateAlmacen: true},
            costo_fijo: {number:true, maxlength: 9}
        }, messages: {
            usuario: {required: " * Ingrese el nombre de usuario", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            paterno: {required: " * Ingrese el apellido materno", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            materno: {maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            correo: {required: " * Ingresa el correo electrónico", email: " * Ingresa un correo electr\u00f3nico v\u00e1lido"},
            pass1: {required: " * Ingrese el password", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            pass2: {equalTo: " * Las contrase\u00f1as no coinciden"},
            costo_fijo: {number: " * El costo fijo debe de ser un valor númerico", maxlength: " * Ingresa m\u00e1ximo {0} caracteres"}
        }
    });
    
    var irViajeE = $("#ve").val();
    if(irViajeE!=""){
        paginaExito = irViajeE;
    }

    var NoAlta = parseInt($("#NoAlta").val());
    if (NoAlta == 1) {
        paginaExito = "catalogos/lista_usuario_turno.php";
    } else {
        if (NoAlta == 2) {
            paginaExito = "catalogos/lista_empleados_loyalty.php";
        } else {
            if (NoAlta == 3) {
                paginaExito = "catalogos/lista_chofer.php";
            } else {
                if (NoAlta == 4) {
                    paginaExito = "catalogos/lista_jefe_campana.php";
                } else {
                    if (NoAlta == 5) {
                        paginaExito = "catalogos/lista_coordinador.php";
                    }
                }
            }
        }
    }

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
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
    });

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
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });

    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Todos los registros",
        selectedList: 3, selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        noneSelectedText: "Selecciona localidad",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();
});

function activarDesactivarPassword(index) {
    if ($("#" + index).is(":checked")) {
        addRequiredPassword();
    } else {
        deleteRequiredPassword();
    }
}

function addRequiredPassword() {
    $("#pass1").rules('add', {
        required: true,
        maxlength: 50,
        minlength: 6,
        messages: {
            required: " * Selecciona la localidad",
            maxlength: " * Ingresa m\u00e1ximo {0} caracteres",
            minlength: " * Ingresa m\u00ednimo {0} caracteres"
        }
    });
    $("#pass2").rules('add', {
        equalTo: "#pass1",
        messages: {
            equalTo: " * Las contrase\u00f1as no coinciden"
        }
    });
}

function deleteRequiredPassword() {
    $("#pass1").rules("remove");
    $("#pass2").rules("remove");
}

function agregarConcepto() {
    var numero = $("#numero_conceptos").val();
    numero++;

    $("#t_datos_addenda").append("<tr id='row_" + numero + "'>" +
            "<td style='width: 30%'><select id='slcCampania_" + numero + "' name='slcCampania_" + numero + "' class='filtro'>" + "<option value=\"\">Seleccione una campaña</option></select></td>" +
            "<td style='width: 25%'><select id='slcTurno_" + numero + "' name='slcTurno_" + numero + "' class='filtro'>" + "<option value=\"\">Seleccione un Turno</option></select></td>" +
            "<td><input type=\"image\" src=\"resources/images/add.png\" title=\"Agregar otro concepto\" onclick=\"agregarConcepto(); return false;\" /></td>" +
            "<td><input type='image' id='erase" + numero + "' src='resources/images/Erase.png' title='Eliminar este concepto' onclick='borrarConcepto(" + numero + "); return false;'/></td>" +
            "</tr>");

    /*Copiamos los tipos de componentes*/
    var $options = $("#slcCampania_1 > option").clone();
    $('#slcCampania_' + numero).append($options);

    var $options = $("#slcTurno_1 > option").clone();
    $('#slcTurno_' + numero).append($options);

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width', '150px');

    $("#slcCampania_" + numero).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona campaña"
        }
    });
    $("#slcTurno_" + numero).rules('add', {
        required: true,
        messages: {
            required: " * Selecciona turno"
        }
    });

    $("#numero_conceptos").val(numero);
}


function borrarConcepto(fila) {
    var trs = $("#t_datos_addenda tr").length;
    var contador = $("#numero_conceptos").val();
    if (fila > contador) {
        fila = contador;
    }
    var row = 'row_' + fila;
    if (trs > 1) {//Si hay filas en la tabla        
        $("#" + row).remove();
        //$("#" + row).rules("remove");
        for (var i = (fila + 1); i <= contador; i++) {
            if ($("#slcCampania_" + i).length) {
                $('#slcCampania_' + i).attr('id', function () {
                    return 'slcCampania_' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'slcCampania_' + (i - 1);  // change name
                });
            }

            if ($("#slcTurno_" + i).length) {
                $('#slcTurno_' + i).attr('id', function () {
                    return 'slcTurno_' + (i - 1);  // change id
                }).attr('name', function () {
                    return 'slcTurno_' + (i - 1);  // change name
                });
            }

            if ($("#row_" + i).length) {
                $('#row_' + i).attr('id', function () {
                    return 'row_' + (i - 1);  // change id
                });
            }
        }
        $("#numero_conceptos").val(contador - 1);
    }
}
