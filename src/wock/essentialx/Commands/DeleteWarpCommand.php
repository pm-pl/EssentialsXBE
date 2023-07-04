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

class DeleteWarpCommand extends Command implements PluginOwned {

    public WarpManager $warpManager;

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin, WarpManager $warpManager){
        parent::__construct("delwarp", "List all warps or warp to the specified location.", "/delwarp <warp>", ["remwarp", "rmwarp"]);
        $this->setPermission("essentialsx.deletewarp");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
        $this->warpManager = $warpManager;
        $this->plugin = $plugin;
    }

    /**
     * @throws \JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (count($args) !== 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /delwarp <warp-name>");
            return false;
        }

        $warpName = $args[0];

        if ($this->warpManager->deleteWarp($warpName)) {
            $sender->sendMessage(TextFormat::DARK_RED . "Warp '{$warpName}' has been deleted.");
        } else {
            $sender->sendMessage(TextFormat::RED . "Warp '{$warpName}' does not exist.");
        }

        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}