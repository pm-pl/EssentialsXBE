<?php

namespace wock\essentialx\Events;

use Exception;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wock\essentialx\Utils\Utils;

class EssentialsXEvent implements Listener {

    private array $lastDeathPositions = [];

    private array $lastTeleportPositions = [];

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $displayname = $player->getDisplayName();
        $nametag = $player->getNameTag();
        $name = $player->getName();
        $config = Utils::getEngMsgConfig();

        $joinConnect = $config->getNested("join.connect", "&r&a{nametag} has joined the server!");
        $joinConnect = str_replace("{name}", $name, $joinConnect);
        $joinConnect = str_replace("{display_name}", $displayname, $joinConnect);
        $joinConnect = str_replace("{nametag}", $nametag, $joinConnect);

        $event->setJoinMessage(TextFormat::colorize($joinConnect));

        $joinMessage = $config->getNested("join.messages");
        $messages = [];

        if ($joinMessage !== null) {
            $messages = str_split($joinMessage, "\n");
        }

        foreach ($messages as $message) {
            $message = str_replace("{name}", $name, $message);
            $message = str_replace("{display_name}", $displayname, $message);
            $message = str_replace("{nametag}", $nametag, $message);
            $player->sendMessage(TextFormat::colorize($message));
        }
    }

    public function onQuit(PlayerQuitEvent $event): void{
        $player = $event->getPlayer();
        $displayname = $player->getDisplayName();
        $nametag = $player->getNameTag();
        $name = $player->getName();

        $config = Utils::getEngMsgConfig();

        $quitMessage = $config->getNested("quit.disconnect", "&r&c{nametag} has disconnected from the server!");
        $quitMessage = str_replace("{name}", $name, $quitMessage);
        $quitMessage = str_replace("{display_name}", $displayname, $quitMessage);
        $quitMessage = str_replace("{nametag}", $nametag, $quitMessage);

        $event->setQuitMessage(TextFormat::colorize($quitMessage));
    }

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
            if ($damager->isFlying()) {
                Utils::toggleFlight($damager, true);
            }
            if ($target->isFlying()) {
                Utils::toggleFlight($target, true);
            }
        } elseif ($damager instanceof Player) {
            if ($damager->isFlying()) {
                Utils::toggleFlight($damager, true);
            }
        } elseif ($target instanceof Player) {
            if ($target->isFlying()) {
                Utils::toggleFlight($target, true);
            }
        }
    }
}
