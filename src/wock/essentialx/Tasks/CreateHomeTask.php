<?php

namespace wock\essentialx\Tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use wock\essentialx\Utils\DatabaseConnection;

class CreateHomeTask extends AsyncTask {

    private string $playerUuid;

    private string $homeName;

    private float $x;

    private float $y;

    private float $z;

    private string $world;

    public function __construct(string $playerUuid, string $homeName, float $x, float $y, float $z, string $world) {
        $this->playerUuid = $playerUuid;
        $this->homeName = $homeName;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
        $this->world = $world;
    }

    /**
     * @throws \Exception
     */
    public function onRun(): void {
        $database = new DatabaseConnection('db4free.net', 'startesting', 'startesting123', 'startesting', 3306);
        $query = "INSERT INTO homes (player_uuid, home_name, x, y, z, world) VALUES (?, ?, ?, ?, ?, ?)";
        $database->execute($query, [$this->playerUuid, $this->homeName, $this->x, $this->y, $this->z, $this->world]);
    }
}
