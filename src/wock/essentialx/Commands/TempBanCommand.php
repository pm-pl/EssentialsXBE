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

class TempBanCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("tempban", "Ban a player from the server temporarily", "/tempban <player> <duration> [reason]");
        $this->setPermission("essentialsx.tempban");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("You must run this command in-game.");
            return false;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . "Not enough arguments. Usage: /tempban <player> <duration> [reason]");
            return false;
        }

        $user = Server::getInstance()->getPlayerExact($args[0]);

        if ($user === null) {
            $sender->sendMessage(TextFormat::RED . "Player '$args[0]' does not exist or is offline.");
            return false;
        }

        if ($user->hasPermission("essentialsx.tempban.exempt")) {
            $sender->sendMessage(TextFormat::RED . "The player cannot be banned.");
            return false;
        }

        if (!$user->isOnline() && !$sender->hasPermission("essentialsx.tempban.offline")) {
            $sender->sendMessage(TextFormat::RED . "Cannot ban an offline player.");
            return false;
        }

        $duration = $this->parseDuration($args[1]);

        if ($duration === false) {
            $sender->sendMessage(TextFormat::RED . "Invalid duration format. Use the format 'XhYmZs' (X hours, Y minutes, Z seconds).");
            return false;
        }

        $banReason = isset($args[2]) ? implode(" ", array_slice($args, 2)) : "default ban reason";
        $senderName = $sender->getName();

        $config = new Config(EssentialsX::getInstance()->getDataFolder() . "config.yml", Config::YAML);

        $banFormat = $config->get("ban-format", "Ban Format");
        $banFormat = str_replace("&", "§", $banFormat);
        $banFormat = str_replace("{reason}", $banReason, $banFormat);
        $banFormat = str_replace("{sender}", $senderName, $banFormat);
        $banFormat = str_replace("{user}", $user->getName(), $banFormat);

        Server::getInstance()->getNameBans()->addBan($user->getName(), $banReason, $duration, $senderName);
        $user->kick(TextFormat::colorize($banFormat));

        $banMessage = $config->get("ban-message", "Player banned message");
        $banMessage = str_replace("&", "§", $banMessage);
        $banMessage = str_replace("{sender}", $senderName, $banMessage);
        $banMessage = str_replace("{user}", $user->getName(), $banMessage);

        Server::getInstance()->getLogger()->info($senderName . " has temporarily banned " . $user->getName() . " for " . $args[1]);

        Server::getInstance()->broadcastMessage($banMessage);

        return true;
    }

    private function parseDuration(string $duration): ?int {
        if (preg_match('/^(\d+)h(\d+)m(\d+)s$/', $duration, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) $matches[3];

            return $hours * 3600 + $minutes * 60 + $seconds;
        }

        return null;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
