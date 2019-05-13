<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../../Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$id_noserie = "";
if (isset($_POST['id'])) {
    $id_noserie = $_POST['id'];
}
$query = $catalogo->obtenerLista("SELECT
	t.IdTicket,
	t.FechaHora,
	t.DescripcionReporte,
	t.NombreCentroCosto,
	t.TipoReporte,
	(
		SELECT
			CASE
		WHEN e2.Suministro = 1 THEN
			(
				SELECT
					group_concat(
						ClaveEspEquipo SEPARATOR ', '
					)
				FROM
					`c_pedido`
				WHERE
					IdTicket = t.IdTicket
			)
		ELSE
			t.NoSerieEquipo
		END
	) AS NumSerie,
	DATEDIFF(NOW(), t.FechaHora) AS diferencia,
	t.NombreCliente,
	cl.IdEstatusCobranza,
	e.IdEstadoTicket AS estadoTicket,
	e1.Nombre AS tipo,
	tc.IdTipoCliente AS tipoCliente,
	e2.Nombre AS area,
	u.Nombre AS ubicacion,
	cgz.nombre AS ubicacionTicket,
	e3.Nombre AS estadoNota,
	nt.IdEstatusAtencion,
	nt.DiagnosticoSol,
	nt.FechaHora AS FechaNota
FROM
	c_ticket AS t
INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
LEFT JOIN c_gzona AS cgz ON cgz.id_gzona = dt.Id_gzona
LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion
LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (
	SELECT
		MAX(IdNotaTicket)
	FROM
		c_notaticket AS nt2
	WHERE
		nt2.IdTicket = t.IdTicket
)
LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
WHERE t.NoSerieEquipo = '$id_noserie'
ORDER BY IdTicket;");
$cabeceras = array("Ticket","Fecha","No Serie","Cliente","Área de atención","Ubicación","Falla","Último estatus ticket","Última Nota","Fecha nota");
?>

<div class="table-responsive">
    <table id="tinfo" class="table">
        <thead class="thead-dark">
            <tr>
                <?php foreach ($cabeceras as $a) echo "<th>" . $a . "</th>"; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($rs = mysql_fetch_array($query)) {/*Recorremos todos los tickets resultantes del query*/                        
                /***********************    Obtenemos el color de la fila   *********************************/                        
                $color = "#F7F7DE";                                                

                if(isset($rs['IdEstatusAtencion'])){/*Si hay estado de la ultima nota*/
                    if($rs['IdEstatusAtencion']!= "16" && (isset($rs['estadoTicket']) && $rs['estadoTicket'] != "2")){/*Si el ticket no esta cerrado*/
                        if(strtoupper($rs['tipoCliente']) == "1"){/*Si el cliente es VIP*/                                                                        
                            if(number_format($rs['diferencia']) >= 2){/*Si ya van mas de 2 dias que se levanto el ticket*/                            
                                $color = "#DC381F";                                        
                            }else{                            
                                $color = "#FFF380";                                        
                            }
                        }else{/*Si no es cliente VIP*/
                            if(number_format($rs['diferencia']) >= 7){/*Si ya van mas de 7 dias que se levanto el ticket*/                            
                                $color = "#DC381F";
                            }
                        }
                    }
                }else{/*Si no hay notas, vemos el estado del ticket*/
                    if($rs['estadoTicket'] != "2"){/*Si el ticket no esta cerrado*/
                        if(strtoupper($rs['tipoCliente']) == "1"){/*Si el cliente es VIP*/
                            if(number_format($rs['diferencia']) >= 2){/*Si ya van mas de 2 dias que se levanto el ticket*/                            
                                $color = "#DC381F";                                        
                            }else{                            
                                $color = "#FFF380";                                        
                            }
                        }else{/*Si no es cliente VIP*/
                            if(number_format($rs['diferencia']) >= 7){/*Si ya van mas de 7 dias que se levanto el ticket*/                            
                                $color = "#DC381F";                                        
                            }
                        }
                    }
                }                                                
                
                if($rs['IdEstatusCobranza'] == "2"){/*Cliente moroso*/
                    $color = "#D462FF";
                }

                if($rs['estadoTicket'] == "4"){/*Ticket cancelado*/
                    $color = "#D1D0CE";
                }

                if($rs['TipoReporte'] == "26"){/*Si es Mtto preventivo*/
                    $color = "#00FFFF";
                }

                echo "<tr style='background-color: $color; color:black;'>";
                echo "<td>" .$rs['IdTicket']. "</td>";
                echo "<td>" .$rs['FechaHora']. "</td>";
                echo "<td>" .$rs['NumSerie']. "</td>";
                echo "<td>" .$rs['NombreCliente']. " - ".$rs['NombreCentroCosto']."</td>";

                echo "<td>" .$rs['area']. "</td>";
                echo "<td>" .$rs['ubicacionTicket']. "</td>";
                echo "<td>" .$rs['DescripcionReporte']. "</td>";

                if(isset($rs['estadoNota'])){
                    echo "<td>" .$rs['estadoNota']. "</td>";
                }else{
                    echo "<td></td>";
                }
                if(isset($rs['DiagnosticoSol'])){
                    echo "<td>" .$rs['DiagnosticoSol']. "</td>";
                }else{
                    echo "<td></td>";
                }
                if(isset($rs['FechaNota'])){
                    echo "<td>" .$rs['FechaNota']. "</td>";
                }else{
                    echo "<td></td>";
                }                        
            
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>
