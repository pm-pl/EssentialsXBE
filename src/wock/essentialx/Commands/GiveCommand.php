<?php

declare(strict_types=1);

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\PopSound;
use wock\essentialx\EssentialsX;

class GiveCommand extends Command implements PluginOwned
{
    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct()
    {
        parent::__construct("give", "Give items to a player", "/give <player> <item> [amount]", ["item", "i"]);
        $this->setPermission("essentialsx.give");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . "Usage: /give <player> <item> [amount]");
            return true;
        }

        $playerName = $args[0];
        $itemName = $args[1];
        $amount = (int)($args[2] ?? 1);

        $player = $this->getPlayerByName($playerName);
        if (!$player instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "Invalid player.");
            return true;
        }

        $item = StringToItemParser::getInstance()->parse($itemName);
        if (!$item) {
            $sender->sendMessage(TextFormat::RED . "Invalid item.");
            return true;
        }

        $item->setCount($amount);
        $player->getInventory()->addItem($item);

        $player->getWorld()->addSound($player->getLocation(), new PopSound());
        $sender->sendMessage(TextFormat::GREEN . "Successfully given $amount $itemName to $playerName.");

        return true;
    }

    private function getPlayerByName(string $name): ?Player
    {
        $player = $this->plugin->getServer()->getPlayerByPrefix($name);
        if ($player instanceof Player) {
            return $player;
        }
        return null;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
