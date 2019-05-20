function crearArbolMtto(){
    var controlador = "WEB-INF/Controllers/Ventas/Controller_json_mtto.php";
    if ($('#vendedor').length && $('#vendedor').val().length) {/*Si hay un vendedor*/        
        $('#tg2').tree({
            url: controlador,
            loadFilter: function(data){
                if (data.d){                    
                    return data.d;
                } else {                    
                    return data;
                }
            }
        });
    }
}