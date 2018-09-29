<?php

namespace MicroShard\Core\Commands;

use MicroShard\Core\Console\Command;
use MicroShard\Core\Migration\Manager;

class Migrate extends Command
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Migrate Database.';
    }

    protected function execute()
    {
        $manager = new Manager($this->getContainer());
        $that = $this;
        $manager->setLogCallback(function(string $message) use ($that) {
            $that->echoLine($message);
        });
        $manager->migrate();

        $this->echoLine('Migration complete.');
    }
}
