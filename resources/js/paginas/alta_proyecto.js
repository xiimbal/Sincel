var select = true;
$(document).ready(function(){
    var form = "#frmAltaTicket";
    var paginaExito = $("#paginaExito").val();//"mesa/lista_proyectos.php";
    var controlador = "WEB-INF/Controllers/Controler_Proyecto.php";
    var amount = $("#amount").val();
    if(amount === ""){
        amount = "0";
    }
    /*validate form*/
    $(form).validate({
        rules: {
            nombre: {required: true, maxlength: 50, minlength: 4},
            tecnico: {required: true},
            areaAtencionGral: {required:true},
            cliente_ticket: {required: true},
            presupuesto: {number: true, min: 0}
        },
        messages: {
            nombre: {required: " * Ingrese el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"},
            tecnico: {required: " * Seleccione el técnico"},
            areaAtencionGral: {required: " * Seleccione el área de atención"},
            cliente_ticket: {required: " * Seleccione el cliente"},
            presupuesto: {number: " * Sólo números son permitidos", min: " * El número mínimo a ingresar es {0}"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            //Preguntamos si quieren ya cerrar el proyecto o no.
            if($("#cerrarProyecto").length && $("#cerrarProyecto").is(":unchecked")){
                var nombreNota = $("#nombreNota").val(), nombreProyecto = $("#nombreProyecto").val();
                var respuesta = confirm(nombreNota + " finalizadas. ¿Desea cerrar " + nombreProyecto + "?");
                if(respuesta){
                    document.getElementById("cerrarProyecto").checked = true;
                }
            }
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()})
                    .done(function(data) {
                
                if (data.toString().indexOf("Error:") === -1) {
                    /*$('#contenidos').load(paginaExito, function() {
                        finished();
                    });*/
                    //cambiarContenidos(paginaExito, "Mesa de ayuda > Proyectos");
                    /*$('#mensajes').html(data);*/
                    var respuestas = data.split("||");
                    $("#mensajes").html(respuestas[0]);
                    if(respuestas.length >= 2){
                        var cerrado = false;
                        if(respuestas[2] !== ""){
                            cerrado = respuestas[2];
                        }
                        $('#contenidos').load(paginaExito, {idTicket: respuestas[1],cerrado: cerrado}, function() {
                            $(".button").button();
                            finished();
                        });
                    }else{
                        $('#contenidos').load(paginaExito,  function() {
                            $(".button").button();
                            finished();
                        });
                    }
                    finished();
                } else {
                    $('#mensajes').html(data);
                    finished();
                }
            });
        }
    });
    
    
    //openNav();
    closeNav();
    $(".tabs").tabs();
    
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
    
    $(".multiselect").multiselect({
        noneSelectedText: "Todos los registros",
        selectedList: 3,
        selectedText: "# seleccionados",
        checkAllText: "Seleccionar todo",
        uncheckAllText: "Deseleccionar todo"
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    });

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
    
    $(".boton").button();/*Estilo de botones*/
    
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
            maxDate: "+0D",
            changeMonth: true,
            changeYear: true
        });
    });
    $('.fecha').mask("9999-99-99");
    
    $('.fechaFin').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
    });
    $('.fechaFin').mask("9999-99-99");

    var espanol = {
        "sProcessing": "Procesando...",
        "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados",
        "sEmptyTable": "Ning\u00fan dato disponible en esta tabla",
        "sInfo": "Mostrando de _START_ a _END_ de  _TOTAL_ registros",
        "sInfoEmpty": "Mostrando 0 registros",
        "sInfoFiltered": "(filtrado de _MAX_ registros)",
        "sInfoPostFix": "",
        "sSearch": "Buscar:",
        "sUrl": "",
        "sInfoThousands": ",",
        "sLoadingRecords": "Cargando...",
        "oPaginate": {
            "sFirst": "Primero",
            "sLast": "\u00daltimo",
            "sNext": "Siguiente",
            "sPrevious": "Anterior"
        },
        "oAria": {
            "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
            "sSortDescending": ": Activar para ordenar la columna de manera descendente"
        }
    };
    /*$("#cambiarContacto").click(function(){
        if(select){//Muestra select
            $("#cambiarContacto").val("Seleccionar contacto");            
            select = false;
        }else{//Muestra campo de texto
            $("#cambiarContacto").val("Ingresar contacto");
            select = true;
        }
    });*/
    //Vamos a seleccionar el contacto que ya habían guardado.
    /*cargarContactos('cliente_ticket','contacto_cliente');//Para que cargue los datos automáticamente
    if($("#contactoTemp").length){  
        //console.log("DDC #contacto_cliente > option[value='" + $("#contactoTemp").val() + "']");
        //$('#contacto_cliente > option[value="' + $("#contactoTemp").val() +  '"]').attr('selected', 'selected');
        $("#contacto_cliente").val($("#contactoTemp").val());
        $("#contacto_cliente").change();
        //alert('#contacto_cliente > option[value="' + $("#contactoTemp").val() +  '"]');
        $("#contacto_cliente").multiselect({
            multiple: false,
            selectedList: 1,
            selectedText: "# seleccionados",
            checkAllText: "Seleccionar todo",
            uncheckAllText: "Deseleccionar todo"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
    }else{
        console.log("Comunicación");
    }*/
    
    //Para mostrar DDL o input de contacto
    $("input[name=tipoContacto]").click(function(){
        if($(this).val() === "1"){
            $("#DDLContacto").show();
            $("#inputContacto").hide();
        }else{
            $("#DDLContacto").hide();
            $("#inputContacto").show();
        }
    });
    
});

function cargarContactos(origen, destino){
    loading("Buscando contactos");
    $("#"+destino).load("WEB-INF/Controllers/Ajax/CargaSelect.php",{'ClaveCliente':$("#"+origen).val(),'contactos':true}, function(data){
        
        /*Refrescamos las opciones*/
        var x = $(this).find('option');
        $(this).multiselect("refresh", x).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
        /*Volvemos a aplicar filtros*/
        $(this).multiselect({
            multiple: false,
            selectedList: 1,
            selectedText: "# seleccionados",
            checkAllText: "Seleccionar todo",
            uncheckAllText: "Deseleccionar todo"
        }).multiselectfilter({
            label: 'Filtro',
            placeholder: 'Escribe el filtro'
        });
                    
        finished();
    });
}