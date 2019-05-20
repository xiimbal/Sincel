$(document).ready(function(){
   var numero = parseInt($("#numero_series").val());
   //console.log(numero);
   for(var i=0;i<=numero;i++){
       $("#cbNoSerie_"+i).html('<img src="../reportes/codebar.php?texto='+$("#serie_"+i).val()+'" class="imagen"/>');
       //alert("cbNoSerie"+i+" "+$("#serie_"+i).val());
   }
   $("#div_ticket").html('<img src="../reportes/codebar.php?texto='+$("#id_ticket").val()+'" class="imagens"/>');
});