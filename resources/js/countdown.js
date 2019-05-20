var t_restante, t_transcurrido = 0, interval;

$(".formulario-sesion").submit(function (e) { 
  $("#usuario_incorrecto").removeAttr("hidden");  
});

function convertSeconds(s) {
  var min = Math.floor(s / 60),
    sec = s % 60;
  return ('0' + min).slice(-2) + ':' + ('0' + sec).slice(-2);
}

function setup() {
  t_restante = document.getElementById('tiempo').value;
  var timer = document.getElementById('timer'),
    mens_time = document.getElementById("mensaje_time"),
    mens_time2 = document.getElementById("mensaje_time2");
  if (t_restante != 0) {  
    timer.style.display = "inline";
    timer.removeAttribute("hidden"); //CAMBIO AGREGADO MAM
    mens_time.style.display = "inline";
    mens_time.removeAttribute("hidden"); //CAMBIO AGREGADO MAM
    mens_time2.style.display = "inline";
    mens_time2.removeAttribute("hidden"); //CAMBIO AGREGADO MAM
    timer.innerHTML = convertSeconds(t_restante - t_transcurrido);

    interval = setInterval(timeIt, 1000);

    function timeIt() {
      t_transcurrido += 1;
      timer.innerHTML = convertSeconds(t_restante - t_transcurrido);
      if (t_transcurrido == t_restante) {
        t_transcurrido = 0;
        t_restante = 0;
        location.href = "http://omiguel.factury.mx/index.php"; //CAMBIO AGREGADO MAM
        clearInterval(interval);
      }
    }

  }
}
