<?php


namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;

class CondenseCommand extends Command implements PluginOwned
{

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin)
    {
        parent::__construct("condense", "Condense items into their block variants", "/ban <player>");
        $this->setPermission("essentialsx.condense");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("You must run this command in-game.");
            return false;
        }

        $item = $sender->getInventory()->getItemInHand();
        if ($item->isNull()) {
            $sender->sendMessage(TextFormat::RED . "Hold an item in your hand to condense it.");
            return false;
        }

        $block = $this->getCondensedBlock($item);

        if ($block === null) {
            $sender->sendMessage(TextFormat::RED . "This item cannot be condensed.");
            return false;
        }

        $blockCount = $item->getCount() / 9;
        $block->setCount($blockCount);

        $sender->getInventory()->removeItem($item);
        $sender->getInventory()->addItem($block);

        $sender->sendMessage(TextFormat::GREEN . "Condensed " . $item->getCount() . " " . $item->getName() . " into " . $blockCount . " " . $block->getName() . " block(s).");

        return true;
    }

    private function getCondensedBlock(Item $item): ?Item {
        $block = null;

        $condensedItems = [
            "minecraft:iron_ingot" => "minecraft:iron_block",
            "coal" => "coal_block",
            "diamond" => "diamond_block",
            "emerald" => "emerald_block",
            "snowball" => "snow_block",
        ];

        if (isset($condensedItems[$item->getName()])) {
            $blockString = $condensedItems[$item->getName()];
            $blockItem = StringToItemParser::getInstance()->parse($blockString);

            if ($blockItem !== null) {
                $block = $blockItem;
                $block->setCustomName(TextFormat::YELLOW . $block->getName());
            }
        }

        return $block;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}