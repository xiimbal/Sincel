var contador = 0;
var numerador = 0;
$(document).ready(function() {
    var form = "#formFlujoFalla";
    var paginaExito = "admin/lista_flujoFalla.php?tipo=" + $("#tipo").val();
    var controlador = "WEB-INF/Controllers/Controler_FlujoFalla.php";

    $('#color_0').ColorPicker({
	onSubmit: function(hsb, hex, rgb, el) {
		$(el).val(hex);
		$(el).ColorPickerHide();
	},
	onBeforeShow: function () {
		$(this).ColorPickerSetColor(this.value);
	}
    })
    .bind('keyup', function(){
            $(this).ColorPickerSetColor(this.value);
    });
    
    contador = $("#numeroEscalamientos").val();
    numerador = $("#numeroEscalamientos").val(); 

    $('#clientes').change(function(){
        var area = $('#area').val();
        var aux = 0;
        var i = 0;
        for(aux = 0; aux<=numerador; aux++)
        {
            while(!$('#tiempoEnvio_'+i).length){
                i++;
            }
            if($('#clientes').is(':checked')){
                if($('#contactos').is(':checked')){
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "todo", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }else{
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basicoClientes", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }
            }else{
                if($('#contactos').is(':checked')){
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basicoContactos", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }else{
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basico", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }
            }
            i++;
        }
    });

    $('#contactos').change(function(){
        var i = 0;
        var area = $('#area').val();
        var aux = 0;
        for(aux = 0; aux<=numerador; aux++)
        {
            while(!$('#tiempoEnvio_'+i).length){
                i++;
            }
            if($('#contactos').is(':checked')){
                if($('#clientes').is(':checked')){
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "todo", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }else{
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basicoContactos", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }
            }else{
                if($('#clientes').is(':checked')){
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basicoClientes", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }else{
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basico", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                }
            }
            i++;
        }
    });
    
    $("#area").change(function(){
        var area = $('#area').val();
        if($('#clientes').is(':checked')){
            if($('#contactos').is(':checked')){
                var i = 0;
                var aux = 0;
                for(aux = 0; aux<=numerador; aux++)
                {
                    while(!$('#tiempoEnvio_'+i).length){
                        i++;
                    }
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "todo", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                    i++;
                }
            }else{
                var i = 0;
                var aux = 0;
                for(aux = 0; aux<=numerador; aux++)
                {
                    while(!$('#tiempoEnvio_'+i).length){
                        i++;
                    }
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basicoClientes", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                    i++;
                }
            }
        }else{
            if($('#contactos').is(':checked')){
                var i = 0;
                var aux = 0;
                for(aux = 0; aux<=numerador; aux++)
                {
                    while(!$('#tiempoEnvio_'+i).length){
                        i++;
                    }
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basicoContactos", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                    i++;
                }
            }else{
                var i = 0;
                var aux = 0;
                for(aux = 0; aux<=numerador; aux++)
                {
                    while(!$('#tiempoEnvio_'+i).length){
                        i++;
                    }
                    $("#correos_"+i).load("WEB-INF/Controllers/Ajax/CargaSelect.php", {'FlujoFalla': 1, 'agregar': "basico", 'area': area}, function() {
                        /*Refrescamos las opciones*/
                        var x = $(this).find('option');
                        $(this).multiselect("refresh", x).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                        /*Volvemos a aplicar filtros*/
                        $(this).multiselect({
                            multiple: true,
                            noneSelectedText: "Todos los registros",
                            selectedList: 3,selectedText: "# seleccionados",
                            checkAllText: "Seleccionar todo",
                            uncheckAllText: "Deseleccionar todo"
                        }).multiselectfilter({
                            label: 'Filtro',
                            placeholder: 'Escribe el filtro'
                        });
                    });
                    i++;
                }
            }
        }
    });

    jQuery.validator.addMethod('selectcheck', function(value) {
        return (value != '0');
    }, " * Selecciona un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            estado: {required: true},
            orden: {required: true, maxlength: 2, number: true},
            tiempoEnvio_0: {number: true}
        },
        messages: {
            estado: {required: " * Ingresa el nombre del estado"},
            orden: {required: " * Ingrese el orden", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", number: " * Ingresa solamente n\u00fameros"},
            tiempoEnvio_0: {number: " * Ingresa solamente n\u00fameros"}
        }
    });

    /*Prevent form*/
    $(form).submit(function(event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize(), numerador: numerador})
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
    $('.boton').button().css('margin-top', '20px');
    
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
});

function agregarEscalamiento(){
    if($("#prioridad_" + contador).val() == "1"){
        alert("La prioridad más alta es 1, no puede haber un escalamiento con prioridad mayor");
    }else{
        contador++;
        numerador++;
        loading("Cargando...");
        $("#tescalamiento").append("<tr id='escalamiento_" + contador +"'><td><table><tr><td><h2 class='titulos'>Nuevo</h2></td></tr><tr><td>Tiempo de envio <span class='obligatorio'> *</span></td>" +
                "<td><input type='text' name='tiempoEnvio_"+contador+"' id='tiempoEnvio_"+contador+"'></td>" +
                "<td>Color <span class='obligatorio'> *</span></td><td><input type='text' name='color_" + contador + "' id='color_"+contador+"'></td>" +
                "<td><img class='imagenMouse' src='resources/images/add.png' title='Nuevo' onclick='agregarEscalamiento();return false;' style='float: right; cursor: pointer;' /></td>" +
                "<td><img class='imagenMouse' src='resources/images/Erase.png' title='Borrar' onclick='eliminarEscalamiento(" + contador + ");return false;' style='float: right; cursor: pointer;' /></td></tr>"+
                "<tr><td>Prioridad <span class='obligatorio'> *</span></td><td><select id='prioridad_" + contador +"' name='prioridad_" + contador +"'></td>" +
                "<td>Correos <span class='obligatorio'> *</span></td><td><select id='correos_" + contador + "' name='correos_" + contador + "[]' multiple='multiple' class='multiselect' style='width: 150px'>" +
                "</select></td></tr><tr><td>Mensaje</td><td><textarea id='mensaje_" + contador + "' name='mensaje_" + contador + "' rows='10' cols='50'></textarea></td></tr>" +
                "</table></td></tr>");

        /*Copiamos los tipos de componentes*/
        var $options = $("#correos_0 > option").clone();
        $('#correos_' + contador).append($options);

        /*Copiamos los tipos de componentes*/
        var $options = $("#prioridad_0 > option").clone();
        $('#prioridad_' + contador).append($options);

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

        $('#color_'+contador).ColorPicker({
            onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val(hex);
                    $(el).ColorPickerHide();
            },
            onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
            }
        })
        .bind('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
        });

        $("#tiempoEnvio_" + contador).rules('add', {
            required: true, number: true,
            messages: {
                required: " * Inserta el tiempo de envio",
                number: " * Ingresa solamente n\u00fameros"
            }
        });
        
        $("#prioridad_" + contador).rules('add', {
            required: true, number:true,
            messages: {
                required: " * Inserta la prioridad",
                number: " * Ingresa solamente n\u00fameros"
            }
        });

        finished();
    }
}

function eliminarEscalamiento(row){
    $("#prioridad_" + row).rules("remove");
    $("#tiempoEnvio_" + row).rules("remove");
    var fila = "escalamiento_" + row;
    var trs = $("#tescalamiento tr").length;
    if (trs > 1) {
        $("#" + fila).remove();
    }    
    numerador--;
}