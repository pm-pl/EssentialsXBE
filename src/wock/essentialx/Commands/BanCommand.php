<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;

class BanCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("ban", "Ban a player from the server", "/ban <player>");
        $this->setPermission("pocketmine.command.ban");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("You must run this command in-game.");
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Not enough arguments. Usage: /ban <player> [reason]");
            return false;
        }

        $user = Server::getInstance()->getPlayerExact($args[0]);

        if ($user === null) {
            $sender->sendMessage(TextFormat::RED . "Player '$args[0]' does not exist or is offline.");
            return false;
        }

        if ($user->hasPermission("essentialsx.ban.exempt")) {
            $sender->sendMessage(TextFormat::RED . "The player cannot be banned.");
            return false;
        }

        if (!$user->isOnline() && !$sender->hasPermission("essentialsx.ban.offline")) {
            $sender->sendMessage(TextFormat::RED . "Cannot ban an offline player.");
            return false;
        }

        $senderName = $sender->getName();
        $banReason = isset($args[1]) ? implode(" ", array_slice($args, 1)) : "default ban reason";

        $config = new Config(EssentialsX::getInstance()->getDataFolder() . "config.yml", Config::YAML);

        $banFormat = $config->get("ban-format", "Ban Format");
        $banFormat = str_replace("&", "§", $banFormat);
        $banFormat = str_replace("{reason}", $banReason, $banFormat);
        $banFormat = str_replace("{sender}", $senderName, $banFormat);
        $banFormat = str_replace("{user}", $user->getName(), $banFormat);

        Server::getInstance()->getNameBans()->addBan($user->getName(), $banReason, null, $senderName);
        $user->kick(TextFormat::colorize($banFormat));

        $banMessage = $config->get("ban-message", "Player banned message");
        $banMessage = str_replace("&", "§", $banMessage);
        $banMessage = str_replace("{sender}", $senderName, $banMessage);
        $banMessage = str_replace("{user}", $user->getName(), $banMessage);

        Server::getInstance()->getLogger()->info($senderName . " has banned " . $user->getName());

        Server::getInstance()->broadcastMessage($banMessage);

        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}