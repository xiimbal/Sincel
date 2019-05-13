<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Retiro{
    public function tieneRetiro($serie) {        
        $consulta = "SELECT * FROM c_bitacora as b
            INNER JOIN c_solicitudretiro AS csr ON csr.IdBitacora=b.id_bitacora
            INNER JOIN c_solictudretirogeneral AS csrg ON csr.IdSolicitudRetiroGeneral=csrg.IdSolicitudRetiroGeneral
            WHERE b.NoSerie='$serie' AND csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1
            ORDER BY csr.IdSolicitudRetiro ";
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            return TRUE;
        }
        return FALSE;
    }
}