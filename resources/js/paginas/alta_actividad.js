$(document).ready(function(){
    closeNav();
    $(".tabs").tabs();
    $(".boton").button();
    $(".select").multiselect({
        multiple: false,
        selectedList: 1,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });
    var amount = $("#amount").val();
    if(amount === ""){
        amount = "0";
    }
    $( "#slider" ).slider({
        value:amount,
        min: 0,
        max: 100,
        step: 5,
        slide: function( event, ui ) {
            $( "#amount" ).val( ui.value );
        }
    });    
    $( "#amount" ).val( $( "#slider" ).slider( "value" ) );
    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });
    $('.fecha').mask("9999-99-99");
    var form = "#frmActividad";
    /*var idTicket = $("#idTicket").val();
    var paginaExito = "mesa/lista_actividades.php?id=" + idTicket;*/
    var paginaExito = $("#paginaExito").val();
    var controlador = "WEB-INF/Controllers/Controler_Actividad.php";
    /*validate form*/
    $(form).validate({
        rules: {
            nombre: {required: true, maxlength: 50, minlength: 4},
            usuario: {required: true},
            horasT: {number: true, min: 0},
            tipo: {required: true},
            fechaI: {required: true},
            relacionado: {required: true},
            estado: {required: true}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            usuario: {required: " * Seleccione el usuario"},
            horasT: {number: " * Sólo números son permitidos", min: " * El valor mínimo es {0}"},
            tipo: {required: " * Seleccione tipo"},
            fechaI: {required: "* Ingresa fecha de inicio"},
            relacionado: {required: "* Selecciona una opción"},
            estado: {required: "* Selecciona un estado"}
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
                
                if (data.toString().indexOf("Error:") === -1) {
                    $("#mensajes").html(data);
                    $('#contenidos').load(paginaExito, function() {
                        $(".button").button();
                        finished();
                    });
                } else {
                    $('#mensajes').html(data);
                    finished();
                }
            });
        }
    });
});