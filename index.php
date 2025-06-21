<?php
// PHP SCRIPT START //

// 1. Lectura y decodificación del archivo JSON
// Asegúrate de que 'data-1.json' esté en la misma carpeta que este archivo 'index.php'
// o ajusta la ruta si está en una subcarpeta (ej. 'data/data-1.json').
$jsonData = file_get_contents('data-1.json');
// Decodifica el JSON a un array asociativo de PHP
$bienesRaices = json_decode($jsonData, true); 

// Verifica si la decodificación fue exitosa y si $bienesRaices es un array
if (json_last_error() !== JSON_ERROR_NONE || !is_array($bienesRaices)) {
    // Si hay un error al leer o decodificar el JSON, inicializa como un array vacío
    // para evitar errores posteriores en la iteración.
    $bienesRaices = [];
    error_log("Error al leer o decodificar data-1.json: " . json_last_error_msg());
    // Opcional: podrías mostrar un mensaje al usuario o manejar el error de otra forma
    // echo "<p>Error: No se pudieron cargar los datos de bienes raíces.</p>";
}

// 2. Extracción de ciudades y tipos únicos para los menús desplegables
$ciudadesUnicas = [];
$tiposUnicos = [];

// También obtendremos los precios mínimo y máximo de los datos existentes
$preciosExistentes = [];

foreach ($bienesRaices as $bien) {
    // Agrega la ciudad si no está ya en la lista
    if (isset($bien['Ciudad']) && !empty($bien['Ciudad']) && !in_array($bien['Ciudad'], $ciudadesUnicas)) {
        $ciudadesUnicas[] = $bien['Ciudad'];
    }
    // Agrega el tipo de vivienda si no está ya en la lista
    if (isset($bien['Tipo']) && !empty($bien['Tipo']) && !in_array($bien['Tipo'], $tiposUnicos)) {
        $tiposUnicos[] = $bien['Tipo'];
    }
    // Recolectar precios existentes para configurar el rango del slider
    if (isset($bien['Precio']) && is_numeric($bien['Precio'])) {
        $preciosExistentes[] = (float)$bien['Precio'];
    }
}

// Opcional: Ordenar alfabéticamente para una mejor experiencia de usuario
sort($ciudadesUnicas);
sort($tiposUnicos);

// Obtener el precio mínimo y máximo de los datos para el ion.rangeSlider
$minPrecioDatos = !empty($preciosExistentes) ? floor(min($preciosExistentes)) : 0;
$maxPrecioDatos = !empty($preciosExistentes) ? ceil(max($preciosExistentes)) : 1000000; // Valor por defecto alto si no hay datos

// PHP SCRIPT END //
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link type="text/css" rel="stylesheet" href="css/materialize.min.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/customColors.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/ion.rangeSlider.skinFlat.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/index.css"  media="screen,projection"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Buscador de Bienes Raíces</title>
</head>

<body>
  <video src="img/video.mp4" id="vidFondo" autoplay loop muted></video> 
  
  <div class="contenedor">
    <div class="card rowTitulo">
      <h1>Buscador</h1>
    </div>
    <div class="colFiltros">
      <form action="buscador.php" method="post" id="formulario">
        <div class="filtrosContenido">
          <div class="tituloFiltros">
            <h5>Realiza una búsqueda personalizada</h5>
          </div>
          <div class="filtroCiudad input-field">
            <label for="selectCiudad">Ciudad:</label>
            <select name="ciudad" id="selectCiudad">
              <option value="" selected>Elige una ciudad</option>
              <?php
              // Itera sobre las ciudades únicas para generar las opciones del select
              foreach ($ciudadesUnicas as $ciudad): ?>
                  <option value="<?= htmlspecialchars($ciudad) ?>"><?= htmlspecialchars($ciudad) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filtroTipo input-field">
            <label for="selectTipo">Tipo:</label><br>
            <select name="tipo" id="selectTipo">
              <option value="" selected>Elige un tipo</option>
              <?php
              // Itera sobre los tipos únicos para generar las opciones del select
              foreach ($tiposUnicos as $tipo): ?>
                  <option value="<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($tipo) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filtroPrecio">
            <label for="rangoPrecio">Precio:</label>
            <input type="text" id="rangoPrecio" name="precio" value="" /> 
          </div>
          <div class="botonField">
            <input type="submit" class="btn white" value="Buscar" id="submitButton">
          </div>
        </div>
        <input type="hidden" name="mostrar_todos" id="mostrarTodosFlag" value="false">
      </form>
    </div>

    <div class="colContenido">
      <div class="tituloContenido card">
        <h5>Resultados de la búsqueda:</h5>
        <div class="divider"></div>
        <button type="button" name="todos" class="btn-flat waves-effect" id="mostrarTodos">Mostrar Todos</button>
      </div>
      <div id="resultadosBusqueda" class="row">
          </div>
    </div>
  </div>

  <script type="text/javascript" src="js/jquery-3.0.0.js"></script>
  <script type="text/javascript" src="js/ion.rangeSlider.min.js"></script>
  <script type="text/javascript" src="js/materialize.min.js"></script>
  <script type="text/javascript" src="js/index.js"></script>

  <script type="text/javascript">
    // Script adicional para inicializar ion.rangeSlider
    $(function () {
      $("#rangoPrecio").ionRangeSlider({
        type: "double",
        grid: false,
        min: <?php echo $minPrecioDatos; ?>, // Usamos el mínimo precio de tus datos
        max: <?php echo $maxPrecioDatos; ?>, // Usamos el máximo precio de tus datos
        from: <?php echo $minPrecioDatos; ?>, // Valor inicial del deslizador izquierdo
        to: <?php echo $maxPrecioDatos; ?>,   // Valor inicial del deslizador derecho
        prefix: "$",
        decorate_both: true,
        values_separator: " a ",
        onFinish: function (data) {
          // Puedes usar data.from y data.to aquí si necesitas hacer algo con los valores seleccionados
          // Los valores ya se envían con el formulario debido al name="precio" del input
        }
      });
    });
  </script>
</body>
</html>