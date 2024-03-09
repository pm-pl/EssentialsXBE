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
use wock\essentialx\Utils\Utils;

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

        $config = Utils::getConfiguration(EssentialsX::getInstance(), "messages-eng.yml");

        $banFormat = $config->get("ban-format", "Ban Format");

        Server::getInstance()->getNameBans()->addBan($user->getName(), $banReason, null, $senderName);
        $user->kick(TextFormat::colorize(str_replace(["{user}", "{sender}", "{reason}"], [$user->getName(), $senderName, $banReason], $banFormat)));

        $banMessage = $config->get("ban-message", "Player banned message");

        Server::getInstance()->getLogger()->info($senderName . " has banned " . $user->getName());

        Server::getInstance()->broadcastMessage(TextFormat::colorize(str_replace(["{user}", "{sender}"], [$user->getName(), $senderName], $banMessage)));

        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
