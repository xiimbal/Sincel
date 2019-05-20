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
            if ($("#refaccion1").val() != '0') {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Selecciona un elemento de la lista");
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
            refaccion1: {validarRefaccion: true}
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
            var tamañoTabla = $("#nuevaRefaccion tr").length;
            $.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamañoTabla, "boton": b})
                    .done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    if (paginaExito == "hardware/lista_validarRefaccion.php") {
                        $("#contenidos").load(paginaExito, function() {
                            finished();
                        });
                    } else {

                        if (pag === 1) {
                            $('#contenidos').html("<iframe src='" + paginaExito + "' width='100%' height='900px' frameborder='0' scrolling='si'></iframe>", function() {

                            });
                            finished();
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
            });
        }
    });
    mostrarRefacciones();
    numeroRefaccion = $("#tamano").val();
    $(".filtroComponentes").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
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

}
var numeroRefaccion = $("#tamano").val();
function otraRefaccion() {
    var newRow = "<tr id='filaRefaccion_" + numeroRefaccion + "'><td>Refacción&nbsp;&nbsp;&nbsp;&nbsp;</td><td><select id='refaccion" + numeroRefaccion + "' name='refaccion" + numeroRefaccion + "' style='width: 600px' class='filtroComponentes'>" +
            "</select></td>" +
            "<td>Cantidad:</td><td><input type='text' style='max-width: 100px' id='cantidad" + numeroRefaccion + "' name='cantidad" + numeroRefaccion + "' /></td>" +
            "<td><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar refacción' onclick='deleteRow(" + numeroRefaccion + ")' style='float: right; cursor: pointer;' /> </td></tr>";
    $('#nuevaRefaccion tr:last').after(newRow);//add the new row
    $('#refaccion1 option').clone().appendTo('#refaccion' + numeroRefaccion);
    var nombre = "#refaccion" + numeroRefaccion;
    $(nombre).rules("add", {
        selectcheck: true,
        messages: {required: " * Seleccione un elemento de la lista"}
    });
    nombre = "#cantidad" + numeroRefaccion;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {required: " * Ingrese la cantidad", number: " * S\u00f3lo puedes ingresar n\u00fameros", required: "* Ingresa la cantidad"}
    });
    $("#refaccion" + numeroRefaccion).multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
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




//     