$(document).ready(function() {
    var form = "#formEquipo";
    var controlador = "WEB-INF/Controllers/Controler_Equipo.php?computo=1";
    var paginaExito = "catalogos/alta_equipoComputo.php";
    var paginaLista = "catalogos/lista_equiposComputo.php";
    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    $.validator.addMethod('filesize', function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param)
    });
    $.validator.addMethod("validarForm", function(value, element) {
        if ($("#caracteristica").val() != "Formato amplio") {
            if ($("input[name=formato1]:checked").val()) {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Seleccione el formato del equipo");

    $.validator.addMethod("validartipoServ", function(value, element) {
        if ($("#caracteristica").val() == "Formato amplio") {
            if ($("input[name=color]:checked").val()) {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Seleccione el tipo de servicio");

    $.validator.addMethod("validarServicio", function(value, element) {
        if ($("#caracteristica").val() != "Formato amplio") {
            if ($("input[name=color]:checked").val() || $("input[name=fax]:checked").val()) {
                return true;
            } else {
                return false;
            }

        } else {
            return true;
        }
    }, "* Seleccione el tipo de servicio");


    $(form).validate({
        errorClass: "my-error-class",
        rules: {
            // orden: {required: true, maxlength: 10, minlength: 4},
            partes: {required: true, maxlength: 50, minlength: 4},
            modelo: {required: true, maxlength: 50, minlength: 4},
            descripcion: {required: true, maxlength: 2000, minlength: 4},
            precio: {number: true},
            periodoMeses: {required: true, number: true},
            procesador: {required:true},
            hd: {required: true, number: true},
            ram: {required: true, number: true}
        },
        messages: {
            partes: {required: " * Ingrese el n\u00famero de parte", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            modelo: {required: " * Ingrese el modelo", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            descripcion: {required: " * Ingrese una descripcion", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            precio: {required: " * Ingrese el precio", number: " * Ingresa s\u00f3lo n\u00fameros"},
            periodoMeses: {required: " * Ingrese el periodo de grarantia(meses)", number: " * Ingresa s\u00f3lo n\u00fameros"}   ,
            procesador: {required: " * Ingrese el porcesador del equipo"},
            hd: {required: " * Ingrese la capacidad del disco duro", number: " * Ingresa s\u00f3lo n\u00fameros"},
            ram: {required: " * Ingrese la memoria RAM", number: " * Ingresa s\u00f3lo n\u00fameros"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            /* stop form from submitting normally */
            event.preventDefault();
            loading("Cargando ...");
            //alert(">>" + nombre1 + ">>" + nombre2 + ">>" + nombre3 + ">>" + nombre4 + ">>" + nombre5);
            var formData = new FormData($('form')[0]);
                $.ajax({
                url: controlador,  //Server script to process data
                type: 'POST',
                xhr: function() {  // Custom XMLHttpRequest
                    var myXhr = $.ajaxSettings.xhr();
                    /*if(myXhr.upload){ // Check if upload property exists
                        myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
                    }*/
                    return myXhr;
                },
                //Ajax events
                //beforeSend: beforeSendHandler,
                //$.post(controlador, {form: $(form).serialize(), "totalrefacciones": tamanoTabla, "boton": b})
                success: function(data) {
                     if (data.toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                        //$('#mensajes').html(data);
                        $('#contenidos').load(paginaLista, function() {
                            finished();
                        });                
                    } else {
                        $('#mensajes').html(data);
                        finished();
                    }
                },
                //error: errorHandler,
                // Form data
                data: formData,
                //Options to tell jQuery not to process data or worry about content-type.
                cache: false,
                contentType: false,
                processData: false
            });//post agregar
        }
    });//
    
    $('.boton').button().css('margin-top', '20px');
    $("#componenteCopiar").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
});

function comprobar(paginaExito, data, paginaLista){
    var interval = setInterval(function() {
        if (numerador == 6) {
            window.clearInterval(interval);
            editarRegistroEquipo(paginaExito, data);
            if (!$("#activo").is(':checked')) {
                cargarPyS(paginaLista);
                finished();
            }
        }
    }, 2000);
}
var numerador = 0;

function subirImagen(idFolio){
    var intp = $("#imagen").val();
    if (intp !== "") {
        var formData = new FormData($(".formulario")[0]);
        formData.append("tipo", "0");
        formData.append("folio", idFolio);
        $.ajax({
            url: 'WEB-INF/Controllers/Controler_UploadPruebas.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $("#arch0").text("cargando imagen...");
            },
            //una vez finalizado correctamente
            success: function(data) {
                var $fileupload = $('#imagen');
                $fileupload.replaceWith($fileupload.clone(true));
                $("#arch0").html("<img src='WEB-INF/Controllers/documentos/equipos/" + data + "' id='preview' name='preview' style='max-width: 200px; max-height: 150px;'/>");
                //$("#arch1").html("<a href='WEB-INF/Controllers/documentos/equipos/" + data + "' target='_blank'>" + data + "</a>");
                numerador++;
            },
            error: function() {
                $("#arch0").text("Error 1");
            }
        });
    }
}

function altaDetalle(pagina, div, id)
{
    $("#" + div).load(pagina, {"idEqipo": id}, function() {
        finished();
    });
}

function editarDetalle(pagina, div, id, id2)
{
    loading("Cargando ...");
    limpiarMensaje();
    $("#" + div).load(pagina, {"idEqipo": id, "id2": id2}, function() {
        finished();
    });
}
function regresarListaEq(liga, div, titulo) {
    loading("Cargando ...");
    limpiarMensaje();
    $('#' + div).load(liga, {"idEquipo": $("#idE").val()}, function() {
        $('#titulo').text(titulo);
        $(".tabs").tabs();
        $(".button").button();
        finished();
    });
}
function eliminraRegDetalle(controlador, div, id, lista)
{
    if (confirm("¿Esta seguro que desea eliminar este registro?")) {
        $('#mensajes').load(controlador, function() {
            $('#' + div).load(lista, {"idEquipo": id});
        });
    }
}
function dealleComponentes(pagina, div, id, idEquipo, lista)
{
    $("#" + div).load(pagina, {"id": id, "detalleC": "detalle", "idEquipo": idEquipo, "lista": lista, "div": div}, function() {
        finished();
    });
}

function OcultarCampos()
{
    var id = $("#caracteristica").val();
    id = id.toLowerCase();
    if (id === "formato amplio")
    {
        $("#ocFax").hide();
        $("#formato").hide();
    }
    else
    {
        $("#ocFax").show();
        $("#formato").show();
    }
}
function altaDetalleComponente(pagina, div, id, tipo)
{
    $("#" + div).load(pagina, {"idComponente": id, "tipo": tipo}, function() {
        finished();
    });
}

