<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Utils\Utils;

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
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "You must run this command in-game");
            return false;
        }

        if (isset($args[0])) {
            $playerName = $args[0];
        } else {
            $sender->sendMessage(TextFormat::RED . "Please specify a player to heal.");
            return false;
        }

        $session = EssentialsX::getSessionManager()->getSession($sender);
        $config = Utils::getConfiguration(EssentialsX::getInstance(), "config.yml");
        if (!$session->getCooldown("heal")) {
            if ($playerName === $sender->getName()) {
                $sender->setHealth($sender->getMaxHealth());
                $sender->sendMessage(TextFormat::GREEN . "You have been healed.");
            } else {
                $player = Server::getInstance()->getPlayerByPrefix($playerName);
                if ($player instanceof Player) {
                    $player->setHealth($player->getMaxHealth());
                    $player->sendMessage(TextFormat::GREEN . "You have been healed by " . $sender->getName() . ".");
                    $sender->sendMessage(TextFormat::GREEN . "You have healed " . $player->getName() . ".");
                } else {
                    $sender->sendMessage(TextFormat::RED . "Player not found.");
                    return false;
                }
            }

            $session->addCooldown("heal", $config->getNested("cooldowns.heal"));
        } else {
            $sender->sendMessage(TextFormat::RED . "You must wait " . Utils::translateTime($session->getCooldown("heal")) . " before using this command again.");
        }

        return true;
    }
}
