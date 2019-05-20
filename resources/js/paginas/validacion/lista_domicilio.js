$(document).ready(function(){
    limpiarDependencia("contacto2");         
});

function copiarDomicilioDeClienteADomicilio(ClaveCliente, ClaveLocalidad){
    var pagina = "../WEB-INF/Controllers/Ajax/updates.php";    
    $("#contenidos_invisibles").load(pagina, {'ClaveCliente':ClaveCliente, 'ClaveLocalidad':ClaveLocalidad, 'copia_domicilio':true}, function(data){
        alert(data);
        cargarDependencia("domicilio2","../cliente/validacion/lista_domicilio.php?idCliente="+ClaveCliente,ClaveLocalidad,"check_cc_"+ClaveLocalidad,null);
    });
}