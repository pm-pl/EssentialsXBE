<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class HealCommand extends Command {

    private array $cooldowns = [];

    public function __construct() {
        parent::__construct("heal");
        $this->setDescription("Heals yourself or a player with a cooldown");
        $this->setUsage("/heal [player: target]");
        $this->setPermission("essentialsx.heal");

    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if(!$sender->hasPermission("essentialsx.heal")) {
            $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
            return false;
        }
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "You must run this command in-game");
            return false;
        }

            if(isset($args[0])) {
                $playerName = $args[0];
            } else {
                $sender->sendMessage(TextFormat::RED . "Please specify a player to heal.");
                return false;
            }
        }

        if(!$this->hasCooldown($sender)) {
            if($playerName === $sender->getName()) {
                $sender->setHealth($sender->getMaxHealth());
                $sender->sendMessage(TextFormat::GREEN . "You have been healed.");
            } else {
                $player = Server::getInstance()->getPlayerByPrefix($playerName);
                if($player instanceof Player) {
                    $player->setHealth($player->getMaxHealth());
                    $player->sendMessage(TextFormat::GREEN . "You have been healed by " . $sender->getName() . ".");
                    $sender->sendMessage(TextFormat::GREEN . "You have healed " . $player->getName() . ".");
                } else {
                    $sender->sendMessage(TextFormat::RED . "Player not found.");
                    return false;
                }
            }

            $this->setCooldown($sender);
        } else {
            $sender->sendMessage(TextFormat::RED . "You must wait " . $this->getCooldownTime($sender) . " seconds before using this command again.");
        }

        return true;
    }

    private function hasCooldown(Player $player): bool
    {
        $name = strtolower($player->getName());

        if(isset($this->cooldowns[$name]) && time() - $this->cooldowns[$name] < 60) {
            return true;
        }

        return false;
    }

    private function setCooldown(Player $player) {
        $name = strtolower($player->getName());
        $this->cooldowns[$name] = time();
    }

    private function getCooldownTime(Player $player) {
        $name = strtolower($player->getName());
        return 60 - (time() - $this->cooldowns[$name]);
    }

}
