$(document).ready(function() {
    var form = "#formTarifa";
    var paginaExito = "catalogos/lista_rangotarifa.php";
    var controlador = "WEB-INF/Controllers/Controler_RangoTarifa.php";


    /*validate form*/
    $(form).validate({
        errorElement: 'div',
        rules: {                               
            nombre: {required: true, maxlength: 100},            
        }, messages: {
            marca: {required: " * Ingresa el nombre", maxlength: " * Ingresa m\u00e1ximo {0} caracteres"}
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
    
    $('.boton').button().css('margin-top', '20px');
    var contador = parseInt($("#TotalDetalles").val());
    
    for(var i=0;i<contador;i++){
        validarDetalle(i);
    }
});

function agregarDetalle() {
    var contador = parseInt($("#TotalDetalles").val());            
    var html = 
            "<tr id='fila_detalle_" + contador + "'>"
            + "<td>Rango Inicial:</td>"
            + "<td><input type='number' step='any' name='r_inicial_"+contador+"' id='r_inicial_"+contador+"' value=''></td>"
            + "<td>Rango Final:</td>"
            + "<td><input type='number' step='any' name='r_final_"+contador+"' id='r_final_"+contador+"' value=''></td>"
            + "<td>Costo:</td>"
            + "<td><input type='number' step='any' name='costo_"+contador+"' id='costo_"+contador+"' value=''></td>"
            + "<td>"
            + "<input type='hidden' id='id_"+contador+"' name='id_"+contador+"' value=''/>"
            + "<a href='#' id='add_" + contador + "' onclick='agregarDetalle(); return false;' title='Agrega otro detalle'><img src='resources/images/add.png' /></a>"
            + "<a href='#' id='delete_"+contador+"' onclick='eliminarDetalle("+contador+"); return false;' title='Elimina este detalle'><img src='resources/images/Erase.png' /></a>";
            + "</td>"
            + "</tr>";               

    $("#tabla_detalles").append(html);      
    validarDetalle(contador);                 
    
    contador++;
    $("#TotalDetalles").val(contador);
}

function validarDetalle(i){  
    $("#r_inicial_" + i).rules('add', {
        required: true,
        maxlength: 7,
        number:true,
        messages: {
            required: " * Ingresa una descripción",
            maxlength: " *  Ingresa máximo {0} caracteres",
            number: "Ingresa sólo números"
        }
    });
       
    $("#r_final_" + i).rules('add', {
        required: true,
        maxlength: 7,
        number:true,
        messages: {
            required: " * Ingresa una descripción",
            maxlength: " *  Ingresa máximo {0} caracteres",
            number: "Ingresa sólo números"
        }
    });
    
    $("#costo_" + i).rules('add', {
        required: true,
        maxlength: 9,
        number:true,
        messages: {
            required: " * Ingresa una descripción",
            maxlength: " *  Ingresa máximo {0} caracteres",
            number: "Ingresa sólo números"
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
        if ($("#r_inicial_" + indice).length) {
            $("#r_inicial_" + indice).rules("remove");
        }
        
        if ($("#r_final_" + indice).length) {
            $("#r_final_" + indice).rules("remove");
        }
        
        if ($("#costo_" + indice).length) {
            $("#costo_" + indice).rules("remove");
        }
        $("#" + fila).remove();
        
        for (var i = (indice); i < contador; i++) {                 
            if ($("#r_inicial_" + (i)).length) {//Campo de proveedores
                $('#r_inicial_' + i).attr('id', function() {
                    return 'r_inicial_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'r_inicial_' + (i - 1);  // change name
                });
            } 
            
            if ($("#r_final_" + (i)).length) {//Campo de proveedores
                $('#r_final_' + i).attr('id', function() {
                    return 'r_final_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'r_final_' + (i - 1);  // change name
                });
            } 
            
            if ($("#costo_" + (i)).length) {//Campo de proveedores
                $('#costo_' + i).attr('id', function() {
                    return 'costo_' + (i - 1);  // change id
                }).attr('name', function() {
                    return 'costo_' + (i - 1);  // change name
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