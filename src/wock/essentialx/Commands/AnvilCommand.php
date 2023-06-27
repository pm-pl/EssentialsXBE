<?php

namespace wock\essentialx\Commands;

use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\world\Position;
use wock\essentialx\EssentialsX;

class AnvilCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("anvil", "Open a portable anvil", "/anvil");
        $this->setPermission("essentialsx.anvil");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("You must run this command in-game.");
            return false;
        }

        $x = floor($sender->getPosition()->getX());
        $y = floor($sender->getPosition()->getY());
        $z = floor($sender->getPosition()->getZ());
        $world = $sender->getWorld();

        $anvilPos = new Position($x, $y, $z, $world);
        $anvilinv = new AnvilInventory($anvilPos);
        $sender->setCurrentWindow($anvilinv);
        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}