$(document).ready(function() {
    var form = "#formUsuario";
    var paginaExito = "admin/lista_usuario.php";
    var controlador = "WEB-INF/Controllers/Controler_Usuario.php";

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");
   
    $.validator.addMethod("validateAlmacen", function(value, element) {
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
            correo: {required: true, email: true},            
            perfil: {selectcheck: true},
            almacen: {validateAlmacen: true}
        }, messages: {
            usuario: {required: " * Ingrese el nombre de usuario", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            paterno: {required: " * Ingrese el apellido materno", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            materno: {maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            correo: {required: " * Ingresa el correo electrónico",email: " * Ingresa un correo electr\u00f3nico v\u00e1lido"},
            pass1: {required: " * Ingrese el password", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            pass2: {equalTo: " * Las contrase\u00f1as no coinciden"}
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
                    $('#contenidos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                }
            });
        }
    });

    jQuery(function($) {
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

    $('.fecha').each(function() {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });
    
    $(".multiselect").multiselect({
        multiple: true,
        noneSelectedText: "Todos los registros",
        selectedList: 3,selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    
    $('.boton').button().css('margin-top', '20px');
});

function activarDesactivarPassword(index){
    if($("#"+index).is(":checked")){
        addRequiredPassword();
    }else{
        deleteRequiredPassword();
    }
}

function addRequiredPassword(){
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

function deleteRequiredPassword(){
    $("#pass1").rules( "remove");
    $("#pass2").rules( "remove");
}