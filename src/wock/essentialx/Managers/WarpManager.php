<?php

declare(strict_types=1);

namespace wock\essentialx\Managers;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;

class WarpManager {

    /** @var Config */
    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * @throws \JsonException
     */
    public function createWarp(Player $player, string $warpName): bool {
        $warpName = strtolower($warpName);

        if ($this->warpExists($warpName)) {
            return false;
        }

        $location = $player->getLocation();
        $position = $location->asPosition();
        $this->config->set($warpName, [
            "x" => $position->getFloorX(),
            "y" => $position->getFloorY(),
            "z" => $position->getFloorZ(),
            "yaw" => $location->getYaw(),
            "pitch" => $location->getPitch(),
            "world" => $position->getWorld()->getFolderName()
        ]);
        $this->config->save();

        return true;
    }

    /**
     * @throws \JsonException
     */
    public function deleteWarp(string $warpName): bool {
        $warpName = strtolower($warpName);

        if (!$this->warpExists($warpName)) {
            return false;
        }

        $this->config->remove($warpName);
        $this->config->save();

        return true;
    }

    public function getWarpPosition(string $warpName): ?Position
    {
        $warpName = strtolower($warpName);

        if ($this->warpExists($warpName)) {
            $warpData = $this->config->get($warpName, []);

            $x = (float) ($warpData['x'] ?? 0);
            $y = (float) ($warpData['y'] ?? 0);
            $z = (float) ($warpData['z'] ?? 0);
            $yaw = (isset($warpData['yaw']) ? (float) $warpData['yaw'] : 0);
            $pitch = (isset($warpData['pitch']) ? (float) $warpData['pitch'] : 0);
            $worldName = $warpData['world'] ?? "";

            $world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
            if ($world !== null) {
                return new Position($x, $y, $z, $world, $yaw, $pitch);
            }
        }

        return null;
    }

    public function warpExists(string $warpName): bool {
        $warpName = strtolower($warpName);
        return $this->config->exists($warpName);
    }

    public function getWarps(): array {
        return $this->config->getAll() ?? [];
    }
}
