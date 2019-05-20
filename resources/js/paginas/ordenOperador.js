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
    //var form = "#formAutoPlantilla";
    var paginaExito = "mesa/orden_operador.php";
    var controlador = "WEB-INF/Controllers/Controler_OrdenOperador.php";

    jQuery.validator.addMethod('selectcheck', function (value) {
        return (value != '0');
    }, " * Seleccione un elemento de la lista");

    /*validate form*/
//    $(form).validate({
//        rules: {
//            CampaniaFiltro: {selectcheck: true},
//            TurnoFiltro: {selectcheck: true},
//            txtfecha: {required: true, maxlength: 20, minlength: 1},
//            tipo_evento: {selectcheck: true}
//
//        },
//        messages: {
//            txtfecha: {required: " * Ingrese fecha", maxlength: " * Ingresa m\u00e1ximo {0} caracteres", minlength: " * Ingresa m\u00ednimo {0} caracteres"}
//        }
//    });

    /*Prevent form*/
//    $(form).submit(function (event) {
//        if ($(form).valid()) {
//            loading("Cargando ...");
//            /* stop form from submitting normally */
//            event.preventDefault();
//            var ids = "";
//
//            $(oTable.fnGetNodes()).find(':checkbox').each(function () {
//                $this = $(this);
//                if ($this.prop('checked')) {
//                    var id = $this.val();
//                    ids += (id + ",");
//                }
//            });
//            if (ids == "") {
//                alert("Selecciona al menos un usuario");
//                finished();
//                return;
//            } else {
//                ids = ids.slice(0, -1);
//                //alert(ids);
//
//                /*Serialize and post the form*/
//                $.post(controlador, {form: $(form).serialize(), 'ids': ids})
//                        .done(function (data) {
//                            var idCampania = $("#CampaniaFiltro").val();
//                            var idTurno = $("#TurnoFiltro").val();
//                            $('#mensajes').html(data);
//                            if (data.toString().indexOf("Error:") === -1) {
//                                $('#contenidos').load(paginaExito, {"CampaniaFiltro": idCampania, "TurnoFiltro": idTurno, 'mostrar': true}, function () {
//                                    finished();
//                                });
//                            } else {
//                                finished();
//                            }
//                        });
//            }
//
//
//        }
//    });




    $('.boton').button().css('margin-top', '20px');

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


//    if ($('#tAlmacen').length) {
//        oTable = $('#tAlmacen').dataTable({
//            "bJQueryUI": true,
//            "bRetrieve": true,
//            "bDestroy": true,
//            "oLanguage": espanol,
//            "sPaginationType": "full_numbers",
//            "bDeferRender": true,
//            "iDisplayLength": 100,
//            "aaSorting": [[0, "desc"]]
//        });
//    }

    iTable = $('.tablaUsuarios').dataTable({
        "bJQueryUI": true,
        "bRetrieve": true,
        "bDestroy": true,
        "oLanguage": espanol,
        "sPaginationType": "full_numbers",
        "bDeferRender": true,
        "iDisplayLength": 10,
        "aaSorting": [[0, "asc"]]
    });

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

function relacionarOperadorServicio(pagina) { //Asignar Servicio a Operador Loyalty
    $("#asigna_operador").hide();
    loading("Asignando registros");
    $("#error_operador").text("");
    var paginaExito = pagina;
    var myRadio = $('input[name=radio_op]');
    var tecnico = myRadio.filter(':checked').val();
    //var tecnico = $("#radio_tec").val();
    if (tecnico == null || tecnico == "") {
        $("#error_operador").text("Selecciona un operador");
        $("#asigna_operador").show();
        finished();
    } else {
        var ticket = $("#servicio").val();
        if (ticket != "") {
            var asignar = true;
            $("#contenidos").load("WEB-INF/Controllers/Controler_OrdenOperador.php",
                    {"IdOrdenO": tecnico, "IdTicket": ticket, "Asignar": asignar},
            function (data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(paginaExito, function () {
                        $(".button").button();
                        finished();
                        return;
                    });
                } else {
                    finished();
                    return;
                }
            });
        } else {
            $("#error_operador").text("Selecciona un servicio");
            $("#asigna_operador").show();
            finished();
        }
    }

}

function AgregarOperador(operador, base, pagina) {
    $("#agregarOperador").hide();
    loading("Agregando Operador");
    $("#error_agregar").text("");
    var paginaExito = pagina;
    if ($("#" + operador).val() == "") {
        $("#error_agregar").text("Selecciona un operador");
        $("#agregarOperador").show();
        finished();
        return;
    } else {
        var operadorO = $("#" + operador).val();
        var IdBase = $("#" + base).val();
        var asignar = true;
        $("#contenidos").load("WEB-INF/Controllers/Controler_OrdenOperador.php",
                {"IdUsuario": operadorO, "IdBase":IdBase, "Agregar": asignar},
        function (data) {
            $('#mensajes').html(data);
            if (data.toString().indexOf("Error:") === -1) {
                $('#contenidos').load(paginaExito, function () {
                    $(".button").button();
                    finished();
                    return;
                });
            } else {
                finished();
                return;
            }
        });
    }
}

function subirOperador(liga){
    $("#subirOperador").hide();
    loading("Posicionando Operador");
    $("#error_accion").text("");
    var myRadio = $('input[name=radio_op]');
    var tecnico = myRadio.filter(':checked').val();
    //var tecnico = $("#radio_tec").val();
    if (tecnico == null || tecnico == "") {
        $("#error_accion").text("Selecciona un operador");
        $("#subirOperador").show();
        finished();
    } else {
        var asignar = true;
            $("#contenidos").load("WEB-INF/Controllers/Controler_OrdenOperador.php",
                    {"IdOrdenO": tecnico, "Subir": asignar},
            function (data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(liga, function () {
                        $(".button").button();
                        finished();
                        return;
                    });
                } else {
                    finished();
                    return;
                }
            });
    }
}

function bajarOperador(liga){
    $("#bajarOperador").hide();
    loading("Posicionando Operador");
    $("#error_accion").text("");
    var myRadio = $('input[name=radio_op]');
    var tecnico = myRadio.filter(':checked').val();
    //var tecnico = $("#radio_tec").val();
    if (tecnico == null || tecnico == "") {
        $("#error_accion").text("Selecciona un operador");
        $("#bajarOperador").show();
        finished();
    } else {
        var asignar = true;
            $("#contenidos").load("WEB-INF/Controllers/Controler_OrdenOperador.php",
                    {"IdOrdenO": tecnico, "Bajar": asignar},
            function (data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(liga, function () {
                        $(".button").button();
                        finished();
                        return;
                    });
                } else {
                    finished();
                    return;
                }
            });
    }
}

function eliminarOperador(liga){
    $("#eliminarOperador").hide();
    loading("Eliminando Operador");
    $("#error_accion").text("");
    var myRadio = $('input[name=radio_op]');
    var tecnico = myRadio.filter(':checked').val();
    //var tecnico = $("#radio_tec").val();
    if (tecnico == null || tecnico == "") {
        $("#error_accion").text("Selecciona un operador");
        $("#eliminarOperador").show();
        finished();
    } else {
        var asignar = true;
            $("#contenidos").load("WEB-INF/Controllers/Controler_OrdenOperador.php",
                    {"IdOrdenO": tecnico, "Eliminar": asignar},
            function (data) {
                $('#mensajes').html(data);
                if (data.toString().indexOf("Error:") === -1) {
                    $('#contenidos').load(liga, function () {
                        $(".button").button();
                        finished();
                        return;
                    });
                } else {
                    finished();
                    return;
                }
            });
    }
}