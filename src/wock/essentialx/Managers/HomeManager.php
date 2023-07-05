<?php

namespace wock\essentialx\Managers;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use wock\essentialx\Tasks\Homes\CreateHomeTask;
use wock\essentialx\Tasks\Homes\TeleportToHomeTask;
use wock\essentialx\Utils\DatabaseConnection;

class HomeManager
{
    private DatabaseConnection $database;

    /**
     * @throws \Exception
     */
    public function __construct(DatabaseConnection $database)
    {
        $this->database = $database;
        $this->initializeDatabase();
    }

    /**
     * @throws \Exception
     */
    private function initializeDatabase(): void
    {
        $query = "CREATE TABLE IF NOT EXISTS homes (player_uuid TEXT, home_name TEXT, x DOUBLE, y DOUBLE, z DOUBLE, world TEXT)";
        $this->database->execute($query);
    }

    /**
     * @throws \Exception
     */
    public function createHome(Player $player, string $homeName)
    {
        $uuid = $player->getUniqueId()->toString();
        $x = $player->getPosition()->getFloorX();
        $y = $player->getPosition()->getFloorY();
        $z = $player->getPosition()->getFloorZ();
        $world = $player->getWorld()->getFolderName();

        $query = "INSERT INTO homes (player_uuid, home_name, x, y, z, world) VALUES (?, ?, ?, ?, ?, ?)";
        $this->database->execute($query, [$uuid, $homeName, $x, $y, $z, $world]);

        $player->sendMessage(TextFormat::GREEN . "Home '{$homeName}' has been created.");
    }

    /**
     * @throws \Exception
     */
    public function removeHome(Player $player, string $homeName): void
    {
        $uuid = $player->getUniqueId()->toString();

        $query = "DELETE FROM homes WHERE player_uuid = ? AND home_name = ?";
        $this->database->execute($query, [$uuid, $homeName]);

        $player->sendMessage(TextFormat::GREEN . "Home '{$homeName}' has been removed.");
    }

    /**
     * @throws \Exception
     */
    public function getHome(Player $player, string $homeName): ?array
    {
        $uuid = $player->getUniqueId()->toString();

        $query = "SELECT * FROM homes WHERE player_uuid = ? AND home_name = ?";
        $result = $this->database->query($query, [$uuid, $homeName]);

        return $result->fetch_array();
    }

    /**
     * @throws \Exception
     */
    public function getAllHomes(Player $player): array
    {
        $uuid = $player->getUniqueId()->toString();

        $statement = $this->database->prepareStatement("SELECT home_name, x, y, z, world FROM homes WHERE player_uuid = ?");
        $statement->bind_param("s", $uuid);
        $statement->execute();
        $result = $statement->get_result();

        $homes = [];
        while ($row = $result->fetch_assoc()) {
            $homes[$row['home_name']] = [
                'x' => $row['x'],
                'y' => $row['y'],
                'z' => $row['z'],
                'world' => $row['world']
            ];
        }

        $statement->close();

        return $homes;
    }


    /**
     * @throws \Exception
     */
    public function teleportToHome(Player $player, string $homeName): bool
    {
        $home = $this->getHome($player, $homeName);
        if ($home !== null) {
            $task = new TeleportToHomeTask($player->getName(), $homeName, $home);
            Server::getInstance()->getAsyncPool()->submitTask($task);
            return true;
        }

        return false;
    }
}
