<?php
// reset_laravel_with_images.php - Limpieza completa incluyendo imágenes en storage público

echo "====================================\n";
echo " Reseteo Laravel + Limpieza de Imágenes\n";
echo "====================================\n";

// 1. Configurar la aplicación Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// 2. Limpieza de caché
echo "\n[1/5] Limpiando caché...\n";
$cacheCommands = [
    'cache:clear',
    'view:clear',
    'route:clear',
    'config:clear',
    'event:clear',
    'optimize:clear'
];

foreach ($cacheCommands as $command) {
    echo "> Ejecutando {$command}... ";
    $kernel->call($command);
    echo "OK\n";
}

// 3. Limpiar imágenes en storage público
echo "\n[2/5] Limpiando imágenes en storage público...\n";
try {
    $storagePath = __DIR__.'/storage/app/public';
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];

    // Verificar si existe el directorio
    if (!file_exists($storagePath)) {
        echo "> Directorio storage público no encontrado\n";
    } else {
        $dirIterator = new RecursiveDirectoryIterator($storagePath, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);

        $deletedFiles = 0;
        $deletedDirs = 0;

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                if (in_array($ext, $imageExtensions)) {
                    echo "> Eliminando imagen {$file->getFilename()}... ";
                    unlink($file->getPathname());
                    $deletedFiles++;
                    echo "OK\n";
                }
            }
        }

        // Limpiar subdirectorios vacíos (excepto el directorio principal)
        foreach (glob($storagePath.'/*', GLOB_ONLYDIR) as $dir) {
            if (count(glob($dir.'/*')) === 0 && basename($dir) !== 'public') {
                echo "> Eliminando directorio vacío {$dir}... ";
                rmdir($dir);
                $deletedDirs++;
                echo "OK\n";
            }
        }

        echo "> Resumen: {$deletedFiles} imágenes eliminadas, {$deletedDirs} directorios vacíos eliminados\n";
    }
} catch (Exception $e) {
    die("Error al limpiar imágenes: " . $e->getMessage());
}

// 4. Eliminar todas las tablas
echo "\n[3/5] Eliminando todas las tablas...\n";
try {
    // Obtener todas las tablas
    $tables = DB::select('SHOW TABLES');
    $tables = array_map('current', $tables);

    if (count($tables) > 0) {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            echo "> Eliminando tabla {$table}... ";
            DB::statement("DROP TABLE {$table}");
            echo "OK\n";
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    } else {
        echo "> No hay tablas para eliminar\n";
    }
} catch (Exception $e) {
    die("Error al eliminar tablas: " . $e->getMessage());
}

// 5. Ejecutar migraciones
echo "\n[4/5] Ejecutando migraciones...\n";
try {
    $kernel->call('migrate', ['--force' => true]);
    echo "> Migraciones ejecutadas correctamente\n";
} catch (Exception $e) {
    die("Error en migraciones: " . $e->getMessage());
}

// 6. Opcional: Ejecutar seeders
echo "\n[5/5] ¿Deseas ejecutar los seeders? (y/n): ";
$answer = trim(fgets(STDIN));

if (strtolower($answer) === 'y') {
    try {
        $kernel->call('db:seed', ['--force' => true]);
        echo "> Seeders ejecutados correctamente\n";
    } catch (Exception $e) {
        die("Error en seeders: " . $e->getMessage());
    }
}

// 7. Recrear enlace simbólico a storage (opcional)
echo "\n> Recreando enlace simbólico a storage público... ";
$kernel->call('storage:link');
echo "OK\n";

// 8. Limpieza final
$kernel->call('optimize');
echo "\n====================================\n";
echo " Proceso completado con éxito!\n";
echo "====================================\n";