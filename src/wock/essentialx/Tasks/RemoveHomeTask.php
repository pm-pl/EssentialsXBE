<?php

namespace wock\essentialx\Tasks\Homes;

use pocketmine\scheduler\AsyncTask;
use wock\essentialx\Utils\DatabaseConnection;

class RemoveHomeTask extends AsyncTask {

    private string $playerUuid;

    private string $homeName;

    public function __construct(string $playerUuid, string $homeName) {
        $this->playerUuid = $playerUuid;
        $this->homeName = $homeName;
    }

    /**
     * @throws \Exception
     */
    public function onRun(): void {
        $database = new DatabaseConnection('db4free.net', 'startesting', 'startesting123', 'startesting', 3306);
        $query = "DELETE FROM homes WHERE player_uuid = ? AND home_name = ?";
        $database->execute($query, [$this->playerUuid, $this->homeName]);
    }
}
