<?php

declare(strict_types=1);

require_once("src/utils/debug.php");
require_once("src/Controller/ProgramController.php");

use App\Controller\ProgramController;

$config = require_once("config/config.php");

try {
    (new ProgramController($config))->update_tree();
} catch (Throwable $e) {
    dump($e->getMessage());
    dump($e->getFile());
    dump('Error on line ' . $e->getLine());
}
