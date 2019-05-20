$(document).ready(function () {
    var form = "#formFinancial";
    var paginaExito = "catalogos/lista_financial.php";
    var controlador = "WEB-INF/Controllers/Controler_Financial.php";

    /*validate form*/
    $(form).validate({
        errorElement: 'div',
        rules: {
            Fecha: {required: true, maxlength: 10},
            IdOperador: {required: true},
            IdEstatus: {required: true},
            IdTipoRetencion: {required: true},
            PorcentajeInteres: {required: true, number:true, range: [0, 100]}           
        }, messages: {
            Fecha: {required: " * Ingresa la fecha del préstamo", maxlength: " * Ingresa m\u00e1ximo {0} caracteres"},
            IdOperador: {required: " * Selecciona el operador"},
            IdEstatus: {required: " * Selecciona el estatus"},
            IdTipoRetencion: {required: " * Seleciona el tipo de retención"},
            PorcentajeInteres: {required: " * Ingresa el procentaje de interés", number: "Ingresa sólo números", range: " * Sólo se permite un porcentaje entre el 0% y 100%"}
        }
    });

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {            
            $("#guardar_financial").hide();
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            /*Serialize and post the form*/
            $.post(controlador, {form: $(form).serialize()}).done(function (data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function() {
                        finished();
                    });
                } else {
                    finished();
                    $("#guardar_financial").show();
                }
            });
        }
    });

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width', '150px');
    
    $(".fecha").mask("9999-99-99");
    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true,
            maxDate: "+0D"
        });
    });

    var contador = parseInt($("#TotalDetalles").val());
    for(var i=0;i<contador;i++){
        validarDetalle(i);
    }
});

function agregarDetalle() {
    var contador = parseInt($("#TotalDetalles").val());            
    var html = "<tr id='fila_detalle_" + contador + "'>"
                + "<td>"
                    + "<table>"
                        +"<tr>"
                            +"<td>Concepto<span class='obligatorio'> *</span></td>"
                            +"<td>"
                                +"<select id='concepto_"+ contador +"' name='concepto_"+ contador +"'>"
                                +"</select>"
                            +"</td>"
                            +"<td>Monto<span class='obligatorio'> *</span></td>"
                            +"<td><input type='number' id='monto_"+ contador +"' name='monto_"+ contador +"' step='any' /></td>"
                            +"<td>Fecha<span class='obligatorio'> *</span></td>"
                            +"<td><input type='text' id='fecha_"+ contador +"' name='fecha_"+ contador +"' class='fecha' /></td>"
                        +"</tr>"
                        +"<tr>"
                            +"<td colspan='6'>"
                                +"<textarea id='comentario_"+ contador +"' name='comentario_"+ contador +"' style='resize: none; width: 500px;' rows='4'></textarea>"
                                +"<input type='hidden' id='id_"+ contador +"' name='id_"+ contador +"' value=''/>"
                            +"</td>"
                        +"</tr>"
                    +"</table>"
                +"</td>"
                +"<td>"
                +"<a href='#' id='add_" + contador + "' onclick='agregarDetalle(); return false;' title='Agrega otro detalle'><img src='resources/images/add.png' title='Nuevo'/></a>"                                    
                +"</td><td><a href='#' id='delete_" + contador + "' onclick='eliminarDetalle(" + contador + "); return false;' title='Elimina este detalle'><img src='resources/images/Erase.png'/></a>";
                +"</td>"
            + "</tr>";                   
    
    $("#tabla_detalles").append(html);      
    validarDetalle(contador);        
    
    /*Copiamos los tipos de componentes*/
    var $options = $("#concepto_1 > option").clone();
    $('#concepto_' + contador).append($options);
    $('#concepto_'+contador+' option[value="3"]').prop('selected', true)
    
    $("#fecha_"+contador).mask("9999-99-99");
    $('#fecha_'+contador).each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true,
            maxDate: "+0D"
        });
    });
    
    /*$(".filtro").multiselect({
        multiple: false,
        noneSelectedText: "No ha seleccionado",
        selectedList: 1
    }).multiselectfilter({
        label: 'Filtro',
        placeholder: 'Escribe el filtro'
    }).css('max-width', '150px');*/
    
    contador++;
    $("#TotalDetalles").val(contador);
}

function validarDetalle(i){  
    $("#concepto_" + i).rules('add', {                
        required:true,
        messages: {            
            required: " * Selecciona el concepto"       
        }
    });
    
    $("#monto_" + i).rules('add', {                
        required:true,
        maxlength: 7,
        number:true,
        messages: {            
            required: " * Ingresa el monto",   
            maxlength: " *  Ingresa máximo {0} caracteres",
            number: "Ingresa sólo números"
        }
    });
    
    $("#fecha_" + i).rules('add', {                
        required:true,
        messages: {            
            required: " * Ingresa la fecha del concepto"            
        }
    });
}

function eliminarDetalle(indice){         
    var contador = parseInt($("#TotalDetalles").val());  
    var minimas_filas = 1;              
    if(contador <= minimas_filas){
        alert("No puedes borrar esta fila, el mínimo de fotos es de "+minimas_filas);
        return false;
    }
    
    var fila = "fila_detalle_" + indice;
    var trs = $("#tabla_detalles tr").length;
    if (trs > minimas_filas) {       
        if ($("#concepto_" + indice).length) {
            $("#concepto_" + indice).rules("remove");
        }
        
        if ($("#monto_" + indice).length) {
            $("#monto_" + indice).rules("remove");
        }
        
        if ($("#fecha_" + indice).length) {
            $("#fecha_" + indice).rules("remove");
        }
                
        $("#" + fila).remove();        
        for (var i = (indice); i < contador; i++) {                 
            if ($("#concepto_" + (i)).length) {//Campo de proveedores
                $('#concepto_' + i).attr('id', function() {
                    return 'concepto_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'concepto_' + (i - 1);  // change name
                });
            } 
            
            if ($("#monto_" + (i)).length) {//Campo de proveedores
                $('#monto_' + i).attr('id', function() {
                    return 'monto_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'monto_' + (i - 1);  // change name
                });
            }
            
            if ($("#fecha_" + (i)).length) {//Campo de proveedores
                $('#fecha_' + i).attr('id', function() {
                    return 'fecha_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'fecha_' + (i - 1);  // change name
                });
            }
            
            if ($("#comentario_" + (i)).length) {//Campo de proveedores
                $('#comentario_' + i).attr('id', function() {
                    return 'comentario_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'comentario_' + (i - 1);  // change name
                });
            }
            
            if ($("#id_" + (i)).length) {//Campo de proveedores
                $('#id_' + i).attr('id', function() {
                    return 'id_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'id_' + (i - 1);  // change name
                });
            }
                        
            if ($("#delete_" + (i)).length) {//Campo de costo
                $('#delete_' + i).attr('id', function() {
                    return 'delete_' + (i - 1);  // change id
                });                                
                $("#delete_"+(i-1)).attr("onclick","eliminarDetalle("+(i-1)+"); return false;");
            }
            
            if ($("#fila_detalle_" + (i)).length) {//Campo de costo
                $('#fila_detalle_' + i).attr('id', function() {
                    return 'fila_detalle_' + (i - 1);  // change id
                });                                                
            }
        }
    }else{
        alert("No puedes borrar esta fila, el mínimo de proveedores es de "+minimas_filas);
        return false;
    }
    
    contador--;
    $("#TotalDetalles").val(contador);
}

function calcularInteresInicial(){
    if($("#PorcentajeInteres").val() !== "" && $("#monto_0").val() !== ""){
        var monto = ($("#PorcentajeInteres").val() / 100) * $("#monto_0").val();
        $("#monto_1").val(monto);
    }else{
        $("#monto_1").val("0");
    }
}
