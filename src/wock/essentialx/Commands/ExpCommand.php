<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
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
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $config = Utils::getConfiguration(EssentialsX::getInstance(), "messages-eng.yml");
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize($config->getNested("xp.in_game_only", "&cThis command must be used in-game.")));
            return;
        }
        if (empty($args)) {
            $exp = number_format($sender->getXpManager()->getCurrentTotalXp());
            $level = $sender->getXpManager()->getXpLevel();
            $levelup = Utils::getExpToLevelUp($sender->getXpManager()->getCurrentTotalXp());
            $message = $config->getNested("xp.self_info", "{player} §r§6has §r§c{exp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up.");
            $message = str_replace(["{levelup}", "{level}", "{exp}", "{player}"], [number_format($levelup), number_format($level), $exp, $sender->getNameTag()], $message);
            $sender->sendMessage(TextFormat::colorize($message));
            return;
        }
        switch ($args[0]) {
            case 'add':
                if (!$sender->hasPermission('essentialsx.xp.add')) {
                    $message = $config->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                if (count($args) !== 3) {
                    $message = $config->getNested("xp.add_usage", "&cUsage: /xp add <player> <amount>");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $player = Utils::getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $config->getNested("xp.player_not_found", "&cPlayer not found.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $amount = (int) $args[2];
                if ($amount <= 0) {
                    $message = Utils::getConfiguration(EssentialsX::getInstance(), "config.yml")->getNested("xp.invalid_amount", "&cAmount must be a positive integer.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $player->getXpManager()->addXp($amount);
                $newXp = $player->getXpManager()->getCurrentTotalXp();
                $sender->getWorld()->addSound($sender->getPosition(), new XpCollectSound());
                $message = $config->getNested("xp.add_success", "&aAdded {amount} XP to {player}. Their new XP is {new_xp}.");
                $sender->sendMessage(TextFormat::colorize(str_replace(["{new_xp}", "{player}", "{amount}"], [number_format($newXp), $player->getName(), number_format($amount)], $message)));
                break;
            case 'remove':
                if (!$sender->hasPermission('essentialsx.xp.remove')) {
                    $message = $config->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                if (count($args) !== 3) {
                    $message = $config->getNested("xp.remove_usage", "&cUsage: /xp remove <player> <amount>");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $config->getNested("xp.player_not_found", "&cPlayer not found.");
                    $message = str_replace("&", "§", $message); // Allowing color codes with '&'
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $amount = (int) $args[2];
                if ($amount <= 0) {
                    $message = $config->getNested("xp.invalid_amount", "&cAmount must be a positive integer.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $currentXp = $player->getXpManager()->getCurrentTotalXp();
                if ($amount > $currentXp) {
                    $message = $config->getNested("xp.insufficient_xp", "&c{player} does not have that much XP.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $player->getXpManager()->subtractXp($amount);
                $newXp = $player->getXpManager()->getCurrentTotalXp();
                $sender->getWorld()->addSound($sender->getPosition(), new FizzSound());
                $message = $config->getNested("xp.remove_success", "&aRemoved {amount} XP from {player}. Their new XP is {new_xp}.");
                $message = str_replace(["{new_xp}", "{player}", "{amount}"], [number_format($newXp), $player->getName(), number_format($amount)], $message);
                $sender->sendMessage(TextFormat::colorize($message));
                break;
            case 'set':
                if (!$sender->hasPermission('essentialsx.xp.set')) {
                    $message = $config->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                if (count($args) !== 3) {
                    $message = $config->getNested("xp.set_usage", "&cUsage: /xp set <player> <amount>");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $config->getNested("xp.player_not_found", "&cPlayer not found.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $amount = (int) $args[2];
                if ($amount < 0) {
                    $message = $config->getNested("xp.invalid_amount", "&cAmount must be a non-negative integer.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $player->getXpManager()->setCurrentTotalXp($amount);
                $message = $config->getNested("xp.set_success", "&aSet {player}'s XP to {amount}.");
                $message = str_replace(["{amount}", "{player}"], [number_format($amount), $player->getName()], $message);
                $sender->sendMessage(TextFormat::colorize($message));
                break;
            case 'show':
                if (!$sender->hasPermission('essentialsx.xp.show')) {
                    $message = $config->getNested("xp.no_permission", "&cYou do not have permission to use this command.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                if (count($args) !== 2) {
                    $message = $config->getNested("xp.show_usage", "&cUsage: /xp show <player>");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $player = Server::getInstance()->getPlayerByPrefix($args[1]);
                if (!$player) {
                    $message = $config->getNested("xp.player_not_found", "&cPlayer not found.");
                    $sender->sendMessage(TextFormat::colorize($message));
                    return;
                }
                $xp = number_format($player->getXpManager()->getCurrentTotalXp(), 1);
                $level = $player->getXpManager()->getXpLevel();
                $levelup = Utils::getExpToLevelUp($player->getXpManager()->getCurrentTotalXp());
                $message = $config->getNested("xp.show_info", "{player} §r§6has §r§c{xp} EXP §r§6(level §r§c{level}§r§6) §r§6and needs {levelup} more exp to level up.");
                $message = str_replace(["{levelup}", "{level}", "{xp}", "{player}"], [number_format($levelup), $level, $xp, $player->getNameTag()], $message);
                $sender->sendMessage(TextFormat::colorize($message));
                break;
            default:
                $message = $config->getNested("xp.invalid_command", "&cUsage: /xp [add|remove|set|show] <player> <amount>");
                $sender->sendMessage(TextFormat::colorize($message));
                break;
        }
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
