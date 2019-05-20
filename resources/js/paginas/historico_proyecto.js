$(document).ready(function(){
    closeNav();
});

function editarElementos(pagina, id){
    var regresar = "?regresar=mesa/historico_proyecto.php?id=" + id;
    $("#contenidos").load(pagina + regresar, {idTicket : id});
}