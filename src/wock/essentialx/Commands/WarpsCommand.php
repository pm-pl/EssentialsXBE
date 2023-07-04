<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Managers\WarpManager;

class WarpsCommand extends Command implements PluginOwned {

    public WarpManager $warpManager;

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin, WarpManager $warpManager){
        parent::__construct("warps", "List all warps.", "/warps");
        $this->setPermission("essentialsx.warp");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
        $this->warpManager = $warpManager;
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): mixed
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        $warps = $this->warpManager->getWarps();
        if (empty($warps)) {
            $sender->sendMessage(TextFormat::DARK_RED . "No Available warps.");
        } else {
            $sender->sendMessage(TextFormat::GOLD . "Available warps: " . TextFormat::RED . implode(TextFormat::WHITE . ", " . TextFormat::RED , array_keys($warps)));
        }
        return true;

    }


    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}