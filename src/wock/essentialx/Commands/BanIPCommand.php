<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\BanEntry;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;

class BanIPCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("banip", "Ban a player's ip from the server'", "/banip <player>");
        $this->setPermission("pocketmine.command.banip");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("You must run this command in-game.");
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Not enough arguments. Usage: /banip <address> [reason]");
            return false;
        }

        $ipAddress = $args[0];
        $banReason = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "default ban reason";
        $senderName = $sender->getName();

        $banList = Server::getInstance()->getIPBans();
        if ($banList->isBanned($ipAddress)) {
            $sender->sendMessage(TextFormat::RED . "IP address is already banned.");
            return false;
        }

        $banList->addBan($ipAddress, $banReason, null, $senderName);
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($player->getServer()->getIp() === $ipAddress) {
                $player->kick(TextFormat::RED . "You have been banned. Reason: " . $banReason);
            }
        }

        $sender->sendMessage(TextFormat::GREEN . "IP address banned: " . $ipAddress);

        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}