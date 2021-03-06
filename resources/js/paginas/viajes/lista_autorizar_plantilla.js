$(document).ready(function () {
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
    var form = "#formAutoPlantilla";
    var paginaExito = "viajes/lista_actualizar_plantilla.php";
    var controlador = "WEB-INF/Controllers/Viajes/Controller_Plantilla.php";

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Seleccione un elemento de la lista");

    /*validate form*/
    $(form).validate({
        rules: {
            CampaniaFiltro: {selectcheck: true},
            TurnoFiltro: {selectcheck: true},
            txtfecha: {required: true, maxlength: 20, minlength: 1},
            tipo_evento: {selectcheck: true}

        },
        messages: {
            txtfecha: {required: " * Ingrese fecha", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
        }
    });

    /*Prevent form*/
    $(form).submit(function (event) {
        if ($(form).valid()) {
            loading("Cargando ...");
            /* stop form from submitting normally */
            event.preventDefault();
            var ids = "";

            $(oTable.fnGetNodes()).find(':checkbox').each(function () {
                $this = $(this);
                if ($this.prop('checked')) {
                    var id = $this.val();
                    ids += (id + ",");
                }
            });
            if (ids == "") {
                alert("Selecciona al menos un usuario");
                finished();
                return;
            } else {
                ids = ids.slice(0, -1);
                //alert(ids);

                /*Serialize and post the form*/
                $.post(controlador, {form: $(form).serialize(), 'ids': ids})
                        .done(function (data) {
                            var idCampania = $("#CampaniaFiltro").val();
                            var idTurno = $("#TurnoFiltro").val();
                            $('#mensajes').html(data);
                            if (data.toString().indexOf("Error:") === -1) {
                                $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function () {
                                    finished();
                                });
                            } else {
                                finished();
                            }
                        });
            }


        }
    });
    
    
    

    $('.boton').button().css('margin-top', '20px');

    $(".filtro").multiselect({
        noneSelectedText: "Selecciona localidad",
        selectedList: 1,
        selectedText: "# seleccionados",
        multiple: false
    }).multiselectfilter();


    if ($('#tAlmacen').length) {
        oTable = $('#tAlmacen').dataTable({
            "bJQueryUI": true,
            "bRetrieve": true,
            "bDestroy": true,
            "oLanguage": espanol,
            "sPaginationType": "full_numbers",
            "bDeferRender": true,
            "iDisplayLength": 100,
            "aaSorting": [[0, "desc"]]
        });
    }

   /* $(".fecha").mask("9999-99-99");
    $('.fecha').each(function () {
        $(this).datepicker({
            dateFormat: 'yy-mm-dd',
            changeYear: true,
            changeMonth: true,
            maxDate: "+0D",
            minDate: "+0D"
        });
    });*/


//   $("#mensajes").load("WEB-INF/Controllers/Controller_Plantilla.php",
//                   {"idUsuario": usuarios, "Asistencia": asistencias},
//           function (data) {
//               $(".submit").click();
//               //$("#asigna_tecnicos").show();
//               finished();
//           });

});




function mostrarCampaniaTurno(liga, campania, turno) {
    loading("Cargando ...");
    $("#contenidos").load(liga, {'CampaniaFiltro': $("#" + campania).val(), 'TurnoFiltro': $("#" + turno).val(), 'mostrar': true}, function () {
        $(".button").button();
        finished();
    });
}

function cancelar(){
    loading("Cargando ...");
    var paginaExito = "viajes/lista_actualizar_plantilla.php";
    var idCampania = $("#CampaniaFiltro").val();
    var idTurno = $("#TurnoFiltro").val();
    $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function() {
        $(".button").button();
        finished();
    });
}

$('#txtfecha').datepicker({dateFormat: 'yy-mm-dd',
                            changeYear: true,
                            changeMonth: true,
                            minDate: "+0D",});
$('#txtfecha').mask("9999-99-99");