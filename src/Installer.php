<?php

namespace MicroShard\Core;

use Composer\Script\Event;


class Installer
{

    public static function copyDistFiles(Event $event)
    {
        $appDir = getcwd();
        $distDir = __DIR__ . '/dist';

        if (!file_exists($appDir . '/bootstrap.php')) {
            copy($distDir . '/bootstrap.php', $appDir . '/bootstrap.php');
            $event->getIO()->write("File created: " . $appDir . '/bootstrap.php');
        }
        if (!file_exists($appDir . '/bootstrap.dev.php')) {
            copy($distDir . '/bootstrap.dev.php', $appDir . '/bootstrap.dev.php');
            $event->getIO()->write("File created: " . $appDir . '/bootstrap.dev.php');
        }

        $binDir = $appDir . '/bin';
        if (!is_dir($binDir)) {
            mkdir($binDir);
        }
        if (!file_exists($binDir . '/console.php')) {
            copy($distDir . '/console.php', $binDir . '/console.php');
            $event->getIO()->write("File created: " . $binDir . '/console.php');
        }

        $webDir = $appDir . '/web';
        if (!is_dir($webDir)) {
            mkdir($webDir);
        }
        if (!file_exists($webDir . '/index.php')) {
            copy($distDir . '/index.php', $webDir . '/index.php');
            $event->getIO()->write("File created: " . $webDir . '/index.php');
        }

    }

}