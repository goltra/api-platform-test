<?php

// Configuración de la conexión a la base de datos
$host = 'mariadb_symfony'; // Cambia esto si tu servidor de base de datos está en un host diferente

$dbname = 'symfony_db';
$username = 'goltratec-dev-db-user';
$password = 'goltratec-dev-db-password';

try {
    // Crear una nueva instancia de PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Configurar el modo de error para lanzar excepciones en caso de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ejemplo de consulta: seleccionar todos los registros de una tabla
    $query = $pdo->query('SELECT * FROM user');

    // Iterar sobre los resultados
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        // Procesar cada fila
        print_r($row);
    }
} catch (PDOException $e) {
    // Capturar cualquier excepción de PDO
    echo "Error de conexión a la base de datos: " . $e->getMessage();
}
