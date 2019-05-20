$(document).ready(function() {
    var form = "#formCompMiniAlmacen";
    var paginaExito = "almacen/lista_componentesMiniAlmacen.php";
    var controlador = "WEB-INF/Controllers/Controler_ComponentesMiniAlmacen.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");


    jQuery.validator.addMethod('minimaRefaccion', function(value) {
        return (parseInt($("#existente1").val()) > parseInt($("#minima1").val()));
    }, " * Las existencias debe ser mayor a la cantidad mínima");

    jQuery.validator.addMethod('maximaRefaccion', function(value) {
        return (parseInt($("#existente1").val()) > parseInt($("#maxima1").val()));
    }, " * Las existencias debe ser menor a la cantidad maxima");


    jQuery.validator.addMethod('minimamaxima', function(value) {
        return (parseInt($("#minima1").val()) < parseInt($("#maxima1").val()));
    }, " * Las cantidad minima debe ser menor a la cantidad maxima");

//$.validator.addMethod("validarServicio", function(value, element) {
//        if (parseInt($("#existente1").val()) < parseInt($("#minima1").val())) {
//           return true;
//        } else {
//            return false;
//        }
//    }, "* Seleccione el tipo de servicio");


    /*validate form*/
    $(form).validate({
        rules: {
            existente1: {required: true, number: true, minimaRefaccion: true, maximaRefaccion: true},
            minima1: {required: true, number: true, minimamaxima: true},
            maxima1: {required: true, number: true},
            componente1: {selectcheck: true},
        },
        messages: {
            existente1: {required: " * Ingrese la cantidad existente", number: " * S\u00f3lo puedes ingresar n\u00fameros"},
            minima1: {required: " * Ingrese la cantidad mínima", number: " * S\u00f3lo puedes ingresar n\u00fameros"},
            maxima1: {required: " * Ingrese la cantidad máxima", number: " * S\u00f3lo puedes ingresar n\u00fameros"},
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    var idCliente = $("#cliente").val();
                    var idMiniAlmacen = $("#idminiAlmacen").val();
                    $('#contenidos').load(paginaExito, {"id": idCliente, "idM": idMiniAlmacen}, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });
});
function verComponentesMiniAlmacen()
{
    loading("Cargando ...");
    var pagina = "almacen/alta_componenteMiniAlmacen.php";
    var tipo = $("#tipoComponente").val();
    var idMini = $("#idminiAlmacen").val();
    var cliente = $("#cliente").val();
    $('#contenidos').load(pagina, {"minialmacen": idMini, "tipo": tipo, "cliente": cliente}, function() {
        finished();
    });
}
function regresarListaMinialmacen(pagina)
{
    loading("Cargando ...");
    var idMini = $("#idminiAlmacen").val();
    var cliente = $("#cliente").val();
    $('#contenidos').load(pagina, {"idM": idMini, "id": cliente}, function() {
        finished();
    });
}
var numeroComponenteMinialmacen = 2;
function otroComponente()
{
    var newRow = "<tr><td>Componente:<span class='obligatorio'> *</span></td><td><select id='componente" + numeroComponenteMinialmacen + "' name='componente" + numeroComponenteMinialmacen + "' style='max-width: 180px'>" +
            "</select></td>" +
            "<td>Cantidad Existente:<span class='obligatorio'> *</span></td><td><input type='text' style='max-width: 50px' id='existente" + numeroComponenteMinialmacen + "' name='existente" + numeroComponenteMinialmacen + "' /></td>" +
            "<td>Cantidad mínima:<span class='obligatorio'> *</span></td><td><input type='text' style='max-width: 50px' id='minima" + numeroComponenteMinialmacen + "' name='minima" + numeroComponenteMinialmacen + "' /></td>" +
            "<td>Cantidad máxima:<span class='obligatorio'> *</span></td><td><input type='text' style='max-width: 50px' id='maxima" + numeroComponenteMinialmacen + "' name='maxima" + numeroComponenteMinialmacen + "' /></td>" +
            "</tr>";
    $('#tablaComponente tr:last').after(newRow);//add the new row
    $('#componente1 option').clone().appendTo('#componente' + numeroComponenteMinialmacen);
    var nombre = "#componente" + numeroComponenteMinialmacen;
    $(nombre).rules("add", {
        selectcheck: true,
        messages: {required: " * Seleccione el componente"}
    });
    nombre = "#existente" + numeroComponenteMinialmacen;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {number: " * S\u00f3lo puedes ingresar n\u00fameros", required: "* Ingresa la cantidad existente"}
    });
    nombre = "#minima" + numeroComponenteMinialmacen;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {number: " * S\u00f3lo puedes ingresar n\u00fameros", required: "* Ingresa la cantidad m\u00ednima"}
    });
    nombre = "#maxima" + numeroComponenteMinialmacen;
    $(nombre).rules("add", {
        number: true,
        required: true,
        messages: {number: " * S\u00f3lo puedes ingresar n\u00fameros", required: "* Ingresa la cantidad m\u00e1xima"}
    });
    numeroComponenteMinialmacen++;
}
function deleteComponente() {
    var trs = $("#tablaComponente tr").length;
    if (trs > 1) {
        $("#tablaComponente tr:last").remove();
        numeroComponenteMinialmacen--;
    }

}
function editarComponenteMini(pagina, idcliente, idminialmacen, componente)
{
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"minialmacen": idminialmacen, "cliente": idcliente, "idComponente": componente}, function() {
        finished();
    });
}