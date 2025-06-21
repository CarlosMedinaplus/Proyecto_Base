/*
  Creación de una función personalizada para jQuery que detecta cuando se detiene el scroll en la página
*/
$.fn.scrollEnd = function(callback, timeout) {
  $(this).scroll(function(){
    var $this = $(this);
    if ($this.data('scrollTimeout')) {
      clearTimeout($this.data('scrollTimeout'));
    }
    $this.data('scrollTimeout', setTimeout(callback,timeout));
  });
};
/*
  Función que inicializa el elemento Slider
*/

function inicializarSlider(){
  $("#rangoPrecio").ionRangeSlider({
    type: "double",
    grid: false,
    min: 0,
    max: 100000,
    from: 200,
    to: 80000,
    prefix: "$"
  });
}
/*
  Función que reproduce el video de fondo al hacer scroll, y detiene la reproducción al detener el scroll
*/
function playVideoOnScroll(){
  var ultimoScroll = 0,
      intervalRewind;
  var video = document.getElementById('vidFondo');
  $(window)
    .scroll((event)=>{
      var scrollActual = $(window).scrollTop();
      if (scrollActual > ultimoScroll){
       video.play();
     } else {
       //this.rewind(1.0, video, intervalRewind); 
       video.play();
     }
     ultimoScroll = scrollActual;
    })
    .scrollEnd(()=>{
      video.pause();
    }, 10)
}

// **NUEVA FUNCIÓN PARA REALIZAR LA BÚSQUEDA VIA AJAX**
function realizarBusqueda(mostrarTodosFlagValue) {
    // 1. Establece el valor del campo oculto 'mostrarTodosFlag'
    $('#mostrarTodosFlag').val(mostrarTodosFlagValue);

    // 2. Serializa los datos del formulario para enviarlos vía AJAX
    // Esto toma todos los campos del formulario por su "name" y los formatea para la solicitud POST
    var formData = $('#formulario').serialize();

    // 3. Realiza la llamada AJAX a buscador.php
    $.ajax({
        url: 'buscador.php', // El script PHP que procesará la búsqueda
        type: 'POST',
        data: formData,
        beforeSend: function() {
            // Opcional: Mostrar un spinner o mensaje de "cargando..."
            // Puedes personalizar este HTML para un spinner de MaterializeCSS más bonito
            $('#resultadosBusqueda').html('<div class="center-align" style="margin-top: 50px;"><div class="preloader-wrapper big active"><div class="spinner-layer spinner-green-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></div>');
        },
        success: function(response) {
            // Cuando la solicitud es exitosa, inserta la respuesta (el HTML de las tarjetas)
            // en el div de resultados
            $('#resultadosBusqueda').html(response);
            // Si necesitaras reinicializar algún componente de Materialize dentro de los resultados
            // (por ejemplo, si las tarjetas tuvieran dropdowns o tooltips), lo harías aquí.
            // Para las tarjetas simples que estamos usando, no es necesario.
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Manejo de errores en la solicitud AJAX
            $('#resultadosBusqueda').html('<p class="red-text center-align" style="margin-top: 50px;">Error al cargar los resultados: ' + textStatus + '. Por favor, intenta de nuevo.</p>');
            console.error("Error AJAX:", textStatus, errorThrown);
            // jqXHR.responseText es útil para ver los errores de PHP en la consola del navegador
            console.error("Respuesta detallada del servidor:", jqXHR.responseText);
        }
    });
}


// Bloque que se ejecuta cuando el DOM está completamente cargado
$(document).ready(function() {
  inicializarSlider();
  playVideoOnScroll();

  // IMPORTANTE: Inicializa los elementos select de MaterializeCSS
  $('select').material_select(); 

  // Para asegurar que el Materialize select actualice el <select> original al cambiar
  $('select').on('change', function() {
    $(this).material_select('update'); 
  });

  // **LÓGICA ACTUALIZADA PARA EL BOTÓN "Mostrar Todos" (USA AJAX)**
  $('#mostrarTodos').on('click', function(event) {
      event.preventDefault(); // <-- MUY IMPORTANTE: Previene el envío normal del formulario que recargaría la página
      realizarBusqueda('true'); // Llama a la nueva función AJAX con el flag 'true'
  });

  // **LÓGICA ACTUALIZADA PARA EL BOTÓN "Buscar" (USA AJAX)**
  $('#submitButton').on('click', function(event) {
      event.preventDefault(); // <-- MUY IMPORTANTE: Previene el envío normal del formulario
      realizarBusqueda('false'); // Llama a la nueva función AJAX con el flag 'false'
  });

  // OPCIONAL: Realizar una búsqueda inicial al cargar la página
  // Por ejemplo, para que el buscador muestre todos los resultados por defecto al inicio
  // Descomenta la siguiente línea si quieres esta funcionalidad:
  // realizarBusqueda('true'); 
});