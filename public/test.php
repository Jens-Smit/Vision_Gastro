<?php
require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

// Initialisiere das Symfony-Kernel
$kernel = new \App\Kernel('prod', false);
var_dump($kernel);
//$kernel->boot();

// Erstelle eine neue Symfony-Konsolenanwendung
//$application = new Application($kernel);
//$application->setAutoExit(false);

$output = new ConsoleOutput();

// Cache leeren
//$input = new ArrayInput(['command' => 'cache:clear', '--env' => 'prod', '--no-debug' => true]);
//$application->run($input, $output);

// Cache aufwärmen
//$input = new ArrayInput(['command' => 'cache:warmup', '--env' => 'prod', '--no-debug' => true]);
//$application->run($input, $output);

// Datenbankmigrationen ausführen
//$input = new ArrayInput(['command' => 'doctrine:schema:update', '--force' => true]);
//$application->run($input, $output);

// Assets installieren
//$input = new ArrayInput(['command' => 'assets:install', '--symlink' => true, '--relative' => true]);
//$application->run($input, $output);

echo "Deployment abgeschlossen!";
?>