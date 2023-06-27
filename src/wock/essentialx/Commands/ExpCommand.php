<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\sound\FizzSound;
use pocketmine\world\sound\XpCollectSound;
use wock\essentialx\EssentialsX;
use wock\essentialx\Utils\Utils;

class ExpCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("exp", "View your current total experience", "/exp", ["xp", "myxp", "myexp"]);
        $this->setPermission("essentialsx.exp");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->getConfigMessage()->getNested("xp.in_game_only", "&cThis command must be used in-game."));
            return;
        }
        if (empty($args)) {
            $exp = number_format($sender->getXpManager()->getCurrentTotalXp(), 1);
            $level = $sender->getXpManager()->getXpLevel();
            $levelup = Utils::getExpToLevelUp($sender->getXpManager()->getCurrentTotalXp());
            $message = $this->getConfigMessage()->getNested("xp.self_info", "{player} §r§6has §r§c{exp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up.");
            $message = str_replace("{player}", $sender->getNameTag(), $message);
            $message = str_replace("&", "§", $message); // Allowing color codes with '&'
            $message = str_replace("{exp}", $exp, $message);
            $message = str_replace("{level}", $level, $message);
            $message = str_replace("{levelup}", number_format($levelup), $message);
            $sender->sendMessage($message);
            return;
        }
        switch ($args[0]) {
            case 'add':
                if (!$sender->hasPermission('essentialsx.xp.add')) {
                    $message = $this->getConfigMessage()->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                if (count($args) !== 3) {
                    $message = $this->getConfigMessage()->getNested("xp.add_usage", "&cUsage: /xp add <player> <amount>");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $this->getConfigMessage()->getNested("xp.player_not_found", "&cPlayer not found.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $amount = (int) $args[2];
                if ($amount <= 0) {
                    $message = $this->getConfigMessage()->getNested("xp.invalid_amount", "&cAmount must be a positive integer.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $player->getXpManager()->addXp($amount);
                $newXp = $player->getXpManager()->getCurrentTotalXp();
                $sender->getWorld()->addSound($sender->getPosition(), new XpCollectSound());
                $message = $this->getConfigMessage()->getNested("xp.add_success", "&aAdded {amount} XP to {player}. Their new XP is {new_xp}.");
                $message = str_replace("{amount}", number_format($amount), $message);
                $message = str_replace("{player}", $player->getName(), $message);
                $message = str_replace("{new_xp}", number_format($newXp), $message);
                $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                $sender->sendMessage($message);
                break;
            case 'remove':
                if (!$sender->hasPermission('essentialsx.xp.remove')) {
                    $message = $this->getConfigMessage()->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                if (count($args) !== 3) {
                    $message = $this->getConfigMessage()->getNested("xp.remove_usage", "&cUsage: /xp remove <player> <amount>");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $this->getConfigMessage()->getNested("xp.player_not_found", "&cPlayer not found.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $amount = (int) $args[2];
                if ($amount <= 0) {
                    $message = $this->getConfigMessage()->getNested("xp.invalid_amount", "&cAmount must be a positive integer.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $currentXp = $player->getXpManager()->getCurrentTotalXp();
                if ($amount > $currentXp) {
                    $message = $this->getConfigMessage()->getNested("xp.insufficient_xp", "&c{player} does not have that much XP.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $player->getXpManager()->subtractXp($amount);
                $newXp = $player->getXpManager()->getCurrentTotalXp();
                $sender->getWorld()->addSound($sender->getPosition(), new FizzSound());
                $message = $this->getConfigMessage()->getNested("xp.remove_success", "&aRemoved {amount} XP from {player}. Their new XP is {new_xp}.");
                $message = str_replace("{amount}", number_format($amount), $message);
                $message = str_replace("{player}", $player->getName(), $message);
                $message = str_replace("{new_xp}", number_format($newXp), $message);
                $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                $sender->sendMessage($message);
                break;
            case 'set':
                if (!$sender->hasPermission('essentialsx.xp.set')) {
                    $message =  $this->getConfigMessage()->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                if (count($args) !== 3) {
                    $message = $this->getConfigMessage()->getNested("xp.set_usage", "&cUsage: /xp set <player> <amount>");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $this->getConfigMessage()->getNested("xp.player_not_found", "&cPlayer not found.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $amount = (int) $args[2];
                if ($amount < 0) {
                    $message = $this->getConfigMessage()->getNested("xp.invalid_amount", "&cAmount must be a non-negative integer.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $player->getXpManager()->setCurrentTotalXp($amount);
                $message = $this->getConfigMessage()->getNested("xp.set_success", "&aSet {player}'s XP to {amount}.");
                $message = str_replace("{player}", $player->getName(), $message);
                $message = str_replace("{amount}", number_format($amount), $message);
                $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                $sender->sendMessage($message);
                break;
            case 'show':
                if (!$sender->hasPermission('essentialsx.xp.show')) {
                    $message = $this->getConfigMessage()->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                if (count($args) !== 2) {
                    $message = $this->getConfigMessage()->getNested("xp.show_usage", "&cUsage: /xp show <player>");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $this->getConfigMessage()->getNested("xp.player_not_found", "&cPlayer not found.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage($message);
                    return;
                }
                $xp = number_format($player->getXpManager()->getCurrentTotalXp(), 1);
                $level = $player->getXpManager()->getXpLevel();
                $levelup = Utils::getExpToLevelUp($player->getXpManager()->getCurrentTotalXp());
                $message = $this->getConfigMessage()->getNested("xp.show_info", "{player} §r§6has §r§c{xp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up.");
                $message = str_replace("{player}", $player->getNameTag(), $message);
                $message = str_replace("{xp}", $xp, $message);
                $message = str_replace("{level}", $level, $message);
                $message = str_replace("{levelup}", number_format($levelup), $message);
                $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                $sender->sendMessage($message);
                break;
            default:
                $message = $this->getConfigMessage()->getNested("xp.invalid_command", "&cUsage: /xp [add|remove|set|show] <player> <amount>");
                $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                $sender->sendMessage($message);
                break;
        }
    }

    private function getConfigMessage(): Config {
        $config = new Config(EssentialsX::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        return $config;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}