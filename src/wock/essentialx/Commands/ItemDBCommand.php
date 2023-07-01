<?php

declare(strict_types=1);

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Utils\Utils;

class ItemDBCommand extends Command implements PluginOwned
{
    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct()
    {
        parent::__construct("itemdb", "View the item information in hand", "/itemdb");
        $this->setPermission("essentialsx.itemdb");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be run by a player.");
            return false;
        }

        $hand = $sender->getInventory()->getItemInHand();
        $sender->sendMessage(TextFormat::GOLD . "Item: " . TextFormat::RED . $hand->getVanillaName());
        $sender->sendMessage(TextFormat::GOLD . "ID: " . TextFormat::RED . $hand->getTypeId());
        if ($hand instanceof Durable) {
            $maxDurability = $hand->getMaxDurability();
            $currentDurability = $hand->getDamage();
            $usesLeft = $maxDurability - $currentDurability;
            $sender->sendMessage(TextFormat::GOLD . "This tool has " . TextFormat::RED . $usesLeft . TextFormat::GOLD . " uses left.");
        }
        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}