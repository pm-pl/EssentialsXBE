<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use wock\essentialx\EssentialsX;

class AfkCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    private array $afkPlayers = [];

    public function __construct(EssentialsX $plugin){
        parent::__construct("afk", "Go afk without being kicked", "/afk");
        $this->setPermission("essentialsx.afk");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("You must run this command in-game");
            return false;
        }
        if (!$sender->hasPermission("essentialsx.afk")) {
            $sender->sendMessage("You don't have permission to use this command");
            return false;
        }

        $player = $sender;
        $isAfk = $this->toggleAfk($player);

        // Send appropriate message based on AFK status
        if ($isAfk) {
            $sender->sendMessage("You are now AFK");
        } else {
            $sender->sendMessage("You are no longer AFK");
        }

        return true;
    }

    public function toggleAfk(Player $player): bool {
        if ($this->isAfk($player)) {
            unset($this->afkPlayers[$player->getName()]);
            return false;
        } else {
            $this->afkPlayers[$player->getName()] = true;
            return true;
        }
    }

    public function isAfk(Player $player): bool {
        return isset($this->afkPlayers[$player->getName()]);
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
