<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

// Initialisiere das Symfony-Kernel
$kernel = new \App\Kernel('prod', false);
$kernel->boot();

echo "Kernel gebootet\n";

// Erstelle eine neue Symfony-Konsolenanwendung
$application = new Application($kernel);
$application->setAutoExit(false);

$output = new ConsoleOutput();

// Funktion zum Ausführen von Befehlen mit Ausgabe
function runCommand(Application $application, array $command, ConsoleOutput $output) {
    $input = new ArrayInput($command);
    $result = $application->run($input, $output);
    echo "Befehl '" . implode(' ', $command) . "' ausgeführt mit Ergebnis: $result\n <br>";
}

// Cache leeren
runCommand($application, ['command' => 'cache:clear', '--env' => 'prod', '--no-debug' => true], $output);

// Cache aufwärmen
runCommand($application, ['command' => 'cache:warmup', '--env' => 'prod', '--no-debug' => true], $output);
// Datenbank erstellen
runCommand($application, ['command' => 'doctrine:database:create'], $output);
// datenbank Emigrienen
runCommand($application, ['command' => 'doctrine:schema:update', '--force' => true], $output);
// Assets installieren
runCommand($application, ['command' => 'assets:install', '--symlink' => true, '--relative' => true], $output);

echo "Deployment abgeschlossen!!!\n";
?>

