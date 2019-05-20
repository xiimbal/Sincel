$(document).ready(function () {
    var form = "#formBitacora";
    var paginaExito = "admin/alta_evento_operador.php";
    var controlador = "WEB-INF/Controllers/Controller_EventoOperador.php";

    if (document.forms[0].elements['ln'].value == 0) {
        document.forms[0].elements['ln'].focus();
    }
    $('#fecha').datepicker({dateFormat: 'yy-mm-dd'});
    $('#hora').mask("99:99:99");

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Seleccione un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            descripcion: {required: true, maxlength: 100, minlength: 1},
            ln: {selectcheck: true},
            evento: {selectcheck: true},
            operador: {selectcheck: true},
            localidad: {selectcheck: true},
            area: {selectcheck: true}

        },
        messages: {
            descripcion: {required: " * Ingrese descripción", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            var formData = new FormData($('form')[0]);
            $.ajax({
                url: controlador, //+"?totalrefacciones="+tamanoTabla+"&boton="+b,  //Server script to process data
                type: 'POST',
                xhr: function () {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    /*if(myXhr.upload){ // Check if upload property exists
                     myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                     }*/
                    return myXhr;
                },
                success: function (data) {

                    $('#mensajes').html(data);
                    if (data.toString().indexOf("Error:") === -1) {
                        $('#contenidos').load(paginaExito, function () {
                            finished();
                        });
                    } else {
                        finished();
                    }
                },
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });
            /* stop form from submitting normally */
            event.preventDefault();
        }
    });

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        noneSelectedText: "Selecciona localidad",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();
});
function verPorEvento(pagina)
{
    var fecha = $("#fecha").val();
    var hora = $("#hora").val();
    var idln = $("#ln").val();
    var idBitacora = $("#id").val();
    var Comentario = $("#comentario").val();
    var Dato = $("#dato").val();
    var Servicio = $("#servicio").val();
    //alert(claveCliente);
    loading("Cargando ...");
    $('#contenidos').load(pagina, {"idBitacora": idBitacora, "fecha": fecha, "hora": hora, "idLN": idln, "Comentario": Comentario, "Dato": Dato, "Servicio": Servicio}, function () {
        if (document.forms[0].elements['evento'].value == 0) {
            document.forms[0].elements['evento'].focus();
        }
        finished();
    });
}

//function getHoraF(d)
//{
//
//    var hora = d.getHours();
//    var min = d.getMinutes();
//    var seg = d.getSeconds();
//    var str_segundo = new String(seg);
//    if (str_segundo.length == 1)
//        seg = "0" + seg;
//
//    var str_minuto = new String(min);
//    if (str_minuto.length == 1)
//        min = "0" + min;
//
//    var str_hora = new String(hora);
//    if (str_hora.length == 1)
//        hora = "0" + hora;
//    return hora + ":" + min + ":" + seg;
//}

function checkKey(key)
{
    var unicode
    if (key.charCode)
    {unicode=key.charCode;}
    else
    {unicode=key.keyCode;}
    //alert(unicode); // Para saber que codigo de tecla presiono
    if (unicode == 46){
        document.forms[0].elements['guardar'].focus();
    }
}