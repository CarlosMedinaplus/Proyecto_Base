<?php
// PHP SCRIPT START //

// 1. Cargar y decodificar el archivo JSON (los datos generales)
// Asegúrate de que 'data-1.json' esté en la misma carpeta que este 'buscador.php'.
$jsonData = file_get_contents('data-1.json');
$bienesRaices = json_decode($jsonData, true);

// Manejo de errores si el JSON no se puede cargar o decodificar
if (json_last_error() !== JSON_ERROR_NONE || !is_array($bienesRaices)) {
    $bienesRaices = [];
    error_log("Error en buscador.php: No se pudieron cargar o decodificar los datos de data-1.json. Mensaje: " . json_last_error_msg());
}

// Inicializar un array para almacenar los resultados filtrados
$resultadosFiltrados = [];

// 2. Recuperar el parámetro de "Mostrar Todos" primero
$mostrarTodos = isset($_POST['mostrar_todos']) && $_POST['mostrar_todos'] === 'true';

// Si se ha pedido "Mostrar Todos", simplemente asigna todos los bienes al array de resultados y salta los filtros.
if ($mostrarTodos) {
    $resultadosFiltrados = $bienesRaices;
} else {
    // Si NO se ha pedido "Mostrar Todos", entonces aplicamos los filtros.

    // Recuperar los parámetros de búsqueda enviados por el formulario
    $ciudadSeleccionada = isset($_POST['ciudad']) ? $_POST['ciudad'] : '';
    $tipoSeleccionado = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $rangoPrecioString = isset($_POST['precio']) ? $_POST['precio'] : '';

    $precioMin = 0;
    // Usamos PHP_FLOAT_MAX para un límite superior "infinito" si no se especifica un máximo.
    $precioMax = PHP_FLOAT_MAX; 

    // --- PARSEO MEJORADO DEL RANGO DE PRECIO DEL ION.RANGESLIDER ---
    if (!empty($rangoPrecioString)) {
        // Primero, limpiaremos la cadena de todos los caracteres no numéricos, excepto el punto decimal
        // Esto dejará solo los números, lo que facilitará la extracción.
        // Ejemplo: "$50,858 - $80,000" se convierte en "5085880000" si quitamos todo
        // Pero necesitamos los dos números separados.
        // La mejor manera es usar una expresión regular para encontrar los números.
        
        // Expresión regular para encontrar todos los números (enteros o flotantes) en la cadena
        preg_match_all('/\d+(?:,\d{3})*(?:\.\d+)?/', $rangoPrecioString, $matches);
        
        // $matches[0] contendrá un array de todas las coincidencias encontradas.
        // Ejemplo: para "$50,858 - $80,000", $matches[0] sería ["50,858", "80,000"]
        
        $numerosEncontrados = [];
        foreach ($matches[0] as $numStr) {
            // Limpiamos cada número de comas y convertimos a float
            $numerosEncontrados[] = (float)str_replace(',', '', $numStr);
        }

        if (count($numerosEncontrados) === 2) {
            $precioMin = $numerosEncontrados[0];
            $precioMax = $numerosEncontrados[1];
        } else {
            // Si no se encuentran exactamente dos números, o el formato es inesperado,
            // registramos el error y los precios min/max se quedan en sus valores por defecto (0 y PHP_FLOAT_MAX).
            error_log("Formato de rango de precio inesperado en ion.rangeSlider: " . $rangoPrecioString . " - Números encontrados: " . implode(', ', $numerosEncontrados));
        }
    }
    // --- FIN DEL PARSEO MEJORADO ---

    // 3. Filtrar los datos iterando sobre los bienes
    foreach ($bienesRaices as $bien) {
        $cumpleFiltros = true;

        // Filtro por Ciudad
        if (!empty($ciudadSeleccionada) && (!isset($bien['Ciudad']) || $bien['Ciudad'] !== $ciudadSeleccionada)) {
            $cumpleFiltros = false;
        }

        // Si ya no cumple, no es necesario verificar los demás filtros para este bien
        if (!$cumpleFiltros) continue;

        // Filtro por Tipo de Vivienda
        if (!empty($tipoSeleccionado) && (!isset($bien['Tipo']) || $bien['Tipo'] !== $tipoSeleccionado)) {
            $cumpleFiltros = false;
        }

        // Si ya no cumple, no es necesario verificar el filtro de precio
        if (!$cumpleFiltros) continue;

        // Filtro por Rango de Precio
        $precioBien = 0;
        if (isset($bien['Precio'])) {
            // Limpiamos el precio del bien del JSON para asegurarnos de que sea numérico
            // Importante: Eliminar '$' y ',' para que (float) funcione correctamente
            $precioBien = (float)str_replace(['$', ','], '', $bien['Precio']);
        }
        
        // Aplica el filtro de precio
        // Solo se aplica si el precio del bien no está dentro del rango especificado
        if ($precioBien < $precioMin || $precioBien > $precioMax) {
            $cumpleFiltros = false;
        }

        // Si el bien cumple con todos los filtros, agregarlo a los resultados
        if ($cumpleFiltros) {
            $resultadosFiltrados[] = $bien;
        }
    }
}

// PHP SCRIPT END //

// AHORA, SOLO IMPRIMIMOS EL HTML DE LOS RESULTADOS.
// Este HTML se insertará en el div `id="resultadosBusqueda"` en `index.php`
// (asumiendo que tu JS en `index.js` maneja la respuesta y la inserta).
if (empty($resultadosFiltrados)): ?>
    <p class="center-align" style="margin-top: 20px;">No se encontraron bienes raíces con los criterios de búsqueda especificados.</p>
<?php else: ?>
    <?php foreach ($resultadosFiltrados as $bien): ?>
        <div class="propiedad-card col s12"> 
            <h5><?= isset($bien['Direccion']) ? htmlspecialchars($bien['Direccion']) : 'Dirección no disponible' ?></h5>
            <p><strong>Ciudad:</strong> <?= isset($bien['Ciudad']) ? htmlspecialchars($bien['Ciudad']) : 'No disponible' ?></p>
            <p><strong>Tipo:</strong> <?= isset($bien['Tipo']) ? htmlspecialchars($bien['Tipo']) : 'No disponible' ?></p>
            <p><strong>Descripción:</strong> <?= isset($bien['Descripcion']) ? htmlspecialchars($bien['Descripcion']) : 'No disponible' ?></p>
            <p class="precio"><strong>Precio:</strong> <?= isset($bien['Precio']) ? htmlspecialchars($bien['Precio']) : 'No disponible' ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>