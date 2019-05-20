$(document).ready(function() {
    var form = "#formComponente";
    var controlador = "WEB-INF/Controllers/Controler_Componente.php";
    var paginaExito = "admin/alta_componentes.php";
    var paginaLista = "admin/lista_componentes.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '');
    }, " * Selecciona un elemento de la lista");

    $.validator.addMethod('filesize', function(value, element, param) {
        return this.optional(element) || (element.files[0].size <= param)
    });

    $(form).validate({
        rules: {
            // orden: {required: true, maxlength: 10, minlength: 4},
            parte: {required: true, maxlength: 20, minlength: 1},
            nombre: {required: true, maxlength: 20, minlength: 1},
            descripcion: {required: true, maxlength: 150, minlength: 1},
            /*parte_anterior: {required: true, maxlength: 10, minlength: 4},            */
            precio: {required: true, number: true},
            tipo: {selectcheck: true},
            imagen: {filesize: 6291456, accept: "png|jpe?g"}
        },
        messages: {
            //folio_entrada: {required: " * Escribe el folio", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"}
            //orden: {required: true, maxlength: 10, minlength: 4},
            parte: {required: " * Ingrese el n\u00famero de parte", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            nombre: {required: " * Ingrese el modelo", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            descripcion: {required: " * Ingrese la descripción", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},
            /*parte_anterior: {required: " * Ingrese el n\u00famero de parte anterior", minlength: " * Escribe m\u00ednimo {0} caracteres", maxlength: " * Escribe m\u00e1ximo {0} caracteres"},               */
            precio: {required: " * Ingrese el precio", number: " * Ingresa s\u00f3lo n\u00fameros"},
            imagen: "El archivo pedimento debe ser JPG o PNG y pesar menos de un mega 1MB"
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            event.preventDefault();
            $.post(controlador, {form: $(form).serialize()}).done(function(data) {

                var data_aux = data.split(" //*// ");
                $('#mensajes').html(data_aux[0]);
                if (data_aux[0].toString().indexOf("Error:") === -1) {/*En caso de que no hay error*/
                    uploadFileC(data_aux[1]);
                    var tipo = $("#tipo").val();
                    editarRegistroComponentes(paginaExito, data_aux[1], tipo);
                    if (!$("#activo").is(':checked')) {
                        cargarPyS(paginaLista);
                    }
                    //cargarPyS(paginaExito);
                } else {
                    $('#mensajes').html(data_aux[1]);
                }
            });
        }
    });
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

function uploadFileC(idFolio) {
    var inputFileImage = document.getElementById("imagen");
    var file = inputFileImage.files[0];
    var data = new FormData();
    data.append("archivo", file);
    data.append("folio", idFolio);
    data.append("tipo", "componentes");
    var url = "WEB-INF/Controllers/Controler_uploadFile.php";
    $.ajax({
        url: url,
        type: "POST",
        contentType: false,
        data: data,
        processData: false,
        cache: false
    }).done(function(data) {
        /*if (data !== "") {
         $("#mensaje").text(JSON.stringify(data));
         alert(JSON.stringify(data));
         }   */
    });
}
function TerminarEdicion(lista)
{
    if (confirm("Los cambio no guardados no se realizar\u00e1n ¿Desea continuar?")) {
        loading("Cargando ...");
        $('#contenidos').load(lista, function() {
            finished();
        });
    }
}
function MostrarColorTonerComponente() {
    var tipo = $("#tipo").val();
    if (tipo == 2) {
        $("#colorComponente").show();
        var nombre = "#color";
        $(nombre).rules("add", {
            selectcheck: true
        });
    } else {
        var nombre = "#color";
        $(nombre).rules("remove");
        $("#colorComponente").hide()();
    }
}