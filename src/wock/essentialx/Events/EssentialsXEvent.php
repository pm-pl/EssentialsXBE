<?php

namespace wock\essentialx\Events;

use Exception;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wock\essentialx\Utils\Utils;

class EssentialsXEvent implements Listener {

    private array $lastDeathPositions = [];

    private array $lastTeleportPositions = [];

    public function onPlayerLogin(PlayerLoginEvent $event): void {
        $player = $event->getPlayer();
        $name = $player->getName();
        $playerip = $player->getServer()->getIp();
        $banList = Server::getInstance()->getNameBans();
        $banListIps = Server::getInstance()->getIPBans();

        if ($banList->isBanned($name)) {
            $banEntry = $banList->getEntry($name);
            $banReason = $banEntry->getReason();

            $player->kick(TextFormat::RED . "You are banned from this server.\nReason: " . $banReason);
        }

        if ($banListIps->isBanned($playerip)) {
            $banEntryIp = $banListIps->getEntry($playerip);
            $banReason = $banEntryIp->getReason();

            $player->kick(TextFormat::RED . "You are banned from this server.\nReason: " . $banReason);
        }
    }

    public function onDeath(PlayerDeathEvent $event) {
        $player = $event->getPlayer();
        $position = $player->getPosition();

        $this->lastDeathPositions[$player->getName()] = $position;
    }

    public function onEntityTeleport(EntityTeleportEvent $event) {
        $entity = $event->getEntity();

        if ($entity instanceof Player) {
            $position = $entity->getPosition();

            $this->lastTeleportPositions[$entity->getName()] = $position;
        }
    }

    /**
     * @throws Exception
     */
    public function getLastDeathPosition(Player $player): ?Vector3 {
        return $this->lastDeathPositions[$player->getName()] ?? null;
    }

    public function getLastTeleportPosition(Player $player): ?Vector3 {
        return $this->lastTeleportPositions[$player->getName()] ?? null;
    }

    public function onEntityDamage(EntityDamageByEntityEvent $event): void
    {
        $damager = $event->getDamager();
        $target = $event->getEntity();

        if ($damager instanceof Player && $target instanceof Player) {
            Utils::toggleFlight($damager, true);
            Utils::toggleFlight($target, true);
        } elseif ($damager instanceof Player) {
            Utils::toggleFlight($damager, true);
        } elseif ($target instanceof Player) {
            Utils::toggleFlight($target, true);
        }
    }

}
