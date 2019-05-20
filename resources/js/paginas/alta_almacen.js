$(document).ready(function() {
    var form = "#formAlmacen";
    var paginaExito = "admin/lista_almacen.php";
    var controlador = "WEB-INF/Controllers/Controler_Almacen.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Seleccione un elemento de la lista");

    $.validator.addMethod("validarCliente", function(value, element) {
        if ($("#tipo").val() === "1") {
            if ($("#cliente_0").val() != "0") {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Seleccione el cliente");



    /*validate form*/
    $(form).validate({
        rules: {
            tipo: {selectcheck: true},
            cliente_0: {validarCliente: true},
            nombre: {required: true, maxlength: 100, minlength: 2},
            txtcp: {required: true, number: true, maxlength: 5, minlength: 5},
            txtPais: {required: true},
            txtDelegacion: {required: true},
            txtCiudad: {required: true},
            txtColonia: {required: true},
            txtExterior: {required: true},
            slcEstado: {selectcheck: true},
            txtCalle: {required: true},
            Latitud: {number: true, maxlength: 12},
            Longitud: {number: true, maxlength: 12},
            prioridad: {required: true, number: true, maxlength: 1}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txtcp: {required: " * Ingrese el código postal", number: "*Ingresa solo números", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            txtPais: {required: " * Ingrese el país"},
            txtDelegacion: {required: " * Ingrese la delegación"},
            txtCiudad: {required: " * Ingrese la ciudad"},
            txtExterior: {required: " * Ingrese el número exterior"},
            txtCalle: {required: " * Ingrese la calle"},
            txtColonia: {required: " * Ingrese la colonia"},
            Latitud: {number: " * Ingrese un número", maxlength: " * Ingrese un máximo de {0} números"},
            Longitud: {number: " * Ingrese un número", maxlength: " * Ingrese un máximo de {0} números"},
            prioridad: {required: " * Ingrese la prioridad para mostrar",number: " * Ingrese un número", maxlength: " * Ingrese un máximo de {0} números"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            var tamanoTabla = $("#TablaClienteFila tr").length;
            var cont = 1;
            var cont1 = 1;
            var verificar = 1;
            var verificarCliente = 1;
            if ($("#tipo").val() == "2") {
                verificar = 1;
            } else {
                if (validar(0)) {
                    verificarCliente = 1;
                    $("#mensajeError0").html("");
                } else {
                    verificarCliente = 0;
                    $("#mensajeError0").html("Necesitas seleccionar al menos una opcion");
                    finished();
                }
                if (verificarCliente == 1) {
                    while (cont < tamanoTabla) {
                        if ($("#cliente_" + cont1).length) {
                            verificar = 1;
                            if (validar(cont1)) {
                                verificar = 1;
                                $("#mensajeError" + cont1).html("");
                            } else {
                                verificar = 0;
                                $("#mensajeError" + cont1).html("Necesitas seleccionar al menos una opcion");
                                finished();
                                break;
                            }
                            cont++;
                        }
                        cont1++;
                    }
                }
            }
            if (verificar === 1) {
                $.post(controlador, {form: $(form).serialize(), "tamanoTabla": tamanoTabla})
                        .done(function(data) {
                            $('#mensajes').html(data);
                            if (data.toString().indexOf("Error:") === -1) {
                                $('#contenidos').load(paginaExito, function() {
                                    finished();
                                });
                            } else {
                                finished();
                            }
                        });
            } else {
                finished();
            }
        }
    });
    
    mostrarFormatoCliente();
    
    if ($("#todoGrupo").is(':checked')) {
        $("#divSelectLocalidad").hide();
    }
    //if ($("#todoGrupo").is(':checked')) {
    //verifytipodeInsercion();
    $(".localidad").multiselect({
        multiple: true,
        noneSelectedText: "No ha seleccionado",
        selectedList: 3,
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".cliente").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 3,
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    function validar(cont) {
        if (getSelected(cont) === "") {
            $("#mensajeError" + cont).html("Necesitas seleccionar al menos una opcion");
            return false;
        }
        return true;
    }
    function getSelected(cont) {
        var str = "";
        if (cont > 0) {
            $("#localidad_" + cont + " option:selected").each(function() {
                str += $(this).text() + " ";
            });
        } else {
            $("#localidad" + cont + " option:selected").each(function() {
                str += $(this).text() + " ";
            });
        }
        return str;
    }
});

function mostrarFormatoCliente() {
    var tipoCliente = $("#tipo").val();
    if (tipoCliente == 1) {
        $("#divLocalidad").show();
        $("#divSelectLocalidad").show();
        //$("#div_clientes_propios").hide();
    } /*else if(tipoCliente == 2){
        $("#divLocalidad").hide();
        $("#divSelectLocalidad").hide();
        $("#div_clientes_propios").show();
    }*/else{
        $("#divLocalidad").hide();
        $("#divSelectLocalidad").hide();
        //$("#div_clientes_propios").hide();
    }
}

function mostrarlocalidades() {
    var tipoAlmacen = $("#tipo").val();
    var cliente = $("#cliente_0").val();
    var idAlmacen = $("#idAlmacen").val();
    var nombre = $("#nombre").val();
    var todo = "";
    var elem = cliente.split("***");
    var grupo = elem[0];
    var cliente1 = elem[1];
    if (cliente !== "0") {
        if ($("#todoGrupo").is(':checked'))
            todo = "seleccionado";
        else
            todo = "";
        var paginaExito = "admin/alta_almacen.php";
        loading("Cargando ...");
        $("#contenidos").load(paginaExito, {"cliente": cliente1, "tipoAlmacen": tipoAlmacen, "idAlmacen": idAlmacen, "nombre": nombre, "todo": todo, "grupo": grupo}, function() {
            finished();
        });
    }

}
function verificarCheckbox() {
    if ($("#todoGrupo").is(':checked')) {
        $("#divSelectLocalidad").hide();
    } else {
        $("#divSelectLocalidad").show();
    }
}
var numeroFilaCliente = parseInt($("#tamano").val());
function nuevoCliente() {
    var newRow = "<tr id='filaCliente_" + numeroFilaCliente + "'>" +
            "<td>Cliente<span class='obligatorio'> *</span></td>" +
            "<td><select id='cliente_" + numeroFilaCliente + "' name='cliente_" + numeroFilaCliente + "[]' style='width: 190px' class='filtro' onchange='mostraLocalidadAjax(" + numeroFilaCliente + ")'></select></td>" +
            "<td></td>" +
            "<td>Localidad<span class='obligatorio'> *</span></td>" +
            "<td><select id='localidad_" + numeroFilaCliente + "' name='localidad_" + numeroFilaCliente + "[]' style='width: 190px' class='filtro' multiple='multiple'></select> <br/><div id='mensajeError" + numeroFilaCliente + "'></div></td>" +
            "<td><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar fila' onclick='deleteRowCliente(" + numeroFilaCliente + ")' style='float: right; cursor: pointer;' /> </td>" +
            "</tr>";
    $('#TablaClienteFila tr:last').after(newRow);//add the new row
    $('#cliente_0 option').clone().appendTo('#cliente_' + numeroFilaCliente);
    $('#localidad option').clone().appendTo('#localidad' + numeroFilaCliente);
    $("#cliente_" + numeroFilaCliente + " option[value='0']").attr("selected", true);
    $("#cliente_" + numeroFilaCliente).multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    $("#localidad_" + numeroFilaCliente).multiselect({
        multiple: true,
        noneSelectedText: "No ha seleccionado",
        selectedList: 3
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    var nombre = "#cliente_" + numeroFilaCliente;
    $(nombre).rules("add", {
        selectcheck: true
    });
    numeroFilaCliente++;
}
function deleteRowCliente(numero) {
    var fila = "filaCliente_" + numero;
    var trs = $("#TablaClienteFila tr").length;
    if (trs > 1) {
        $("#" + fila).remove();
        //numeroRefaccion--;
    }
}
function mostraLocalidadAjaxCliente(fila) {//Controler_MostrarLocalidadAjax
    var clienteAux = $("#cliente_" + fila).val();
    var datos = clienteAux.split("***");
    var cliente = datos[1];
    $.post("WEB-INF/Controllers/Controler_MostrarLocalidadAjax.php", {"cliente": cliente}).done(function(data) {
        var localidadAux = JSON.parse(data);
        var sel = $("#localidad" + fila);
        sel.empty();
        for (var i = 0; i < localidadAux.length; i++) {
            var cc = localidadAux[i].split("/*");
            sel.append("<option value='" + cc[0] + "'>" + cc[1] + "</option>");
        }
        $("#localidad" + fila).multiselect({
            multiple: true,
            noneSelectedText: "No ha seleccionado",
            selectedList: 3
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    });
}
function mostraLocalidadAjax(fila) {//Controler_MostrarLocalidadAjax
    var clienteAux = $("#cliente_" + fila).val();
    var datos = clienteAux.split("***");
    var cliente = datos[1];
    $.post("WEB-INF/Controllers/Controler_MostrarLocalidadAjax.php", {"cliente": cliente}).done(function(data) {
        var localidadAux = JSON.parse(data);
        var sel = $("#localidad_" + fila);
        sel.empty();
        for (var i = 0; i < localidadAux.length; i++) {
            var cc = localidadAux[i].split("/*");
            sel.append("<option value='" + cc[0] + "'>" + cc[1] + "</option>");
        }
        $("#localidad_" + fila).multiselect({
            multiple: true,
            noneSelectedText: "No ha seleccionado",
            selectedList: 3
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    });
}

function generarTicketResurtido(idAlmacen){
    $("#resurtir").hide();
    var controlador = "WEB-INF/Controllers/Controler_Almacen.php";
    $.post(controlador, {"idAlmacen": idAlmacen, "resurtir": "1"})
        .done(function(data) {
            $('#mensajes').html(data);
            $("#resurtir").show();
        });
}