<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Managers\WarpManager;

class AddWarpCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public WarpManager $warpManager;

    public function __construct(EssentialsX $plugin, WarpManager $warpManager){
        parent::__construct("addwarp", "Create a new Warp", "/addwarp <name>", ["setwarp", "createwarp", "ecreatewarp", ""]);
        $this->setPermission("essentialsx.addwarp");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
        $this->plugin = $plugin;
        $this->warpManager = $warpManager;
    }

    /**
     * @throws \JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return true;
        }

        if (count($args) !== 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /setwarp <warp-name>");
            return true;
        }

        $warpName = $args[0];

        if ($this->warpManager->createWarp($sender, $warpName)) {
            $sender->sendMessage(TextFormat::GREEN . "Warp '{$warpName}' has been created.");
        } else {
            $sender->sendMessage(TextFormat::RED . "Warp '{$warpName}' already exists.");
        }
        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}