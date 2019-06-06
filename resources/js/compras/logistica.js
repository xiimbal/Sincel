jq(document).ready(function () {
    cargarDatos();
});

let contador_inputs_checkbox = 0;
let contador_inputs = 0;

let cargarDatos = function () {

    let tabla_viajes = $('#tabla_rutas').DataTable({
        "destroy": true,
        "ajax": {
            method: "POST",
            url: "WEB-INF/Controllers/compras/Controler_Logistica.php",
            data: {
                "proceso": "consultar"
            },
            dataSrc: 'data'
        },
        "columns": [
            { "data": "fecha" },
            { "data": "ruta" },
            { "data": "nombre_destino" },
            { "data": "pedido" },
            { "data": "etiqueta" },
            { "data": "clave_embarque" },
            { "data": "CV" },
            { "data": "suma_piezas" },            
            { 
                "data": "suma_piezas",
                "render": function ( data, type, row, meta ) {   
                    contador_inputs += 1;                                    
                    return '<input id="input_'+contador_inputs+'" class="form-control form-control-sm" type="number" placeholder="No. piezas en camión" value="' + data + '">';
                } 
            },                        
            {               
                "data": "id_csv",
                "render": function (data) {                                       
                    contador_inputs_checkbox += 1;
                    return '<center><input class="form-check-input" type="checkbox" value="'+ data +'" id="'+ contador_inputs_checkbox +'" checked></center>';
                }
            }
        ],
        "language": espaniol
    });

    recorrerTabla(tabla_viajes);
};


let recorrerTabla =  function (tabla){

    jq("#btn-cargar-camion").click(function (e) { 
        e.preventDefault();        

        let total_checks = 0;
        let respuestas_exitosas = 0;
        
        jq("input:checkbox").each(function() {

            if ($(this).is(':checked')){

                total_checks +=1;
    
                let id_csv = jq(this).attr("value");
                let id_check = jq(this).attr("id");
                let piezas_en_camion = jq("#input_" + id_check +"").val();                

                $.ajax({
                    type: "POST",
                    url: "WEB-INF/Controllers/compras/Controler_Logistica.php",
                    data: {
                        "proceso": "actualizar_piezas_camion",
                        "piezas_encamion": piezas_en_camion,                        
                        "id_csv": id_csv,
                    }
                }).done(function(info){

                    let respuesta_servidor = JSON.parse(info);

                    if (respuesta_servidor == 'Exito') {

                        respuestas_exitosas += 1;

                        if (respuestas_exitosas == total_checks) {
                            
                            swal({

                                icon: "success",
            
                                text: "Exito al iniciar las rutas. Continue con los siguientes viajes",
            
                            });
                            
                            cambiarContenidos('compras/cargar_camion_log.php', 'Cargar Camion');
                        }
                        
                    }else{
                        swal({

                            title: "¡Algo salió mal!",
        
                            text: "Al parecer ha ocurrido un error, pruebe intentarlo más tarde",
        
                            icon: "error",
        
                        });
                    }
                })
                
            }else{
                    
    
            }
                            
        });       
    });
}

let espaniol = {
    "sProcessing": "Procesando...",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": "No se encontraron resultados",
    "sEmptyTable": "Ningún dato disponible en esta tabla",
    "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
    "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix": "",
    "sSearch": "Buscar:",
    "sUrl": "",
    "sInfoThousands": ",",
    "sLoadingRecords": "Cargando...",
    "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Anterior"
    },
    "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    }
};