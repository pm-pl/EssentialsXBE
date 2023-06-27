<?php

namespace wock\essentialx\Tasks;

use pocketmine\scheduler\Task;
use wock\essentialx\Utils\Utils;

class ParticleRepeatTask extends Task {

    public $player;

    public $particleName;

    private int $iterations = 0;

    public function __construct($player, $particleName) {
        $this->player = $player;
        $this->particleName = $particleName;
    }

    public function onRun(): void {
        if ($this->iterations >= 60) { // 60 iterations = 3 seconds (20 ticks per second)
            $this->getHandler()->cancel();
        } else {
            Utils::spawnParticle($this->player, $this->particleName);
            $this->iterations++;
        }
    }
}
