<?php

declare(strict_types=1);

namespace wock\essentialx\API;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use wock\essentialx\Managers\WarpManager;

class WarpAPI
{
    /** @var WarpManager */
    private WarpManager $warpManager;

    public function __construct(Config $config)
    {
        $this->warpManager = new WarpManager($config);
    }

    /**
     * @throws \JsonException
     */
    public function createWarp(Player $player, string $warpName): bool
    {
        return $this->warpManager->createWarp($player, $warpName);
    }

    /**
     * @throws \JsonException
     */
    public function deleteWarp(string $warpName): bool
    {
        return $this->warpManager->deleteWarp($warpName);
    }

    public function getWarpPosition(string $warpName): ?Position
    {
        return $this->warpManager->getWarpPosition($warpName);
    }

    public function warpExists(string $warpName): bool
    {
        return $this->warpManager->warpExists($warpName);
    }

    public function getWarps(): array
    {
        return $this->warpManager->getWarps();
    }

    public function teleportPlayerToWarp(Player $player, string $warpName): bool
    {
        $warpPosition = $this->getWarpPosition($warpName);

        if ($warpPosition !== null) {
            $player->teleport($warpPosition);
            return true;
        }

        return false;
    }

    public function teleportPlayerToWarpByName(Player $player, string $warpName, string $targetPlayer): bool
    {
        if (!$player->hasPermission("essentialsx.warp.other")) {
            return false;
        }

        $targetPlayerInstance = Server::getInstance()->getPlayerByPrefix($targetPlayer);

        if ($targetPlayerInstance === null) {
            return false;
        }

        $warpPosition = $this->getWarpPosition($warpName);

        if ($warpPosition !== null) {
            $targetPlayerInstance->teleport($warpPosition);
            return true;
        }

        return false;
    }
}
