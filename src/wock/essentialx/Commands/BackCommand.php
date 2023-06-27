<?php

namespace wock\essentialx\Commands;

use Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use wock\essentialx\EssentialsX;
use wock\essentialx\Events\EssentialsXEvent;

class BackCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public EssentialsXEvent $event;

    public function __construct(EssentialsX $plugin, EssentialsXEvent $event){
        parent::__construct("back", "Teleport to your last location", "/back");
        $this->setPermission("essentialsx.back");
        $this->plugin = $plugin;
        $this->event = $event;
    }

    /**
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("You must run this command in-game");
            return false;
        }

        $player = $sender;
        $lastDeath = $this->event->getLastDeathPosition($player);
        $lastTeleport = $this->event->getLastTeleportPosition($player);

        if ($lastDeath === null && $lastTeleport === null) {
            $sender->sendMessage("No known location to teleport to.");
            return true;
        }

        if ($commandLabel === "back" && $lastTeleport !== null) {
            $player->teleport($lastTeleport);
            $sender->sendMessage("Teleported to your last teleport location.");
        } elseif ($lastDeath !== null) {
            $player->teleport($lastDeath);
            $sender->sendMessage("Teleported to your last death location.");
        } elseif ($lastTeleport !== null) {
            $player->teleport($lastTeleport);
            $sender->sendMessage("Teleported to your last teleport location.");
        } else {
            $sender->sendMessage("No known location to teleport to.");
        }

        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
