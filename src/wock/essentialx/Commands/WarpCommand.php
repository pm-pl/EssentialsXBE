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

class WarpCommand extends Command implements PluginOwned {

    public WarpManager $warpManager;

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin, WarpManager $warpManager){
        parent::__construct("warp", "List all warps or warp to the specified location.", "/warp <warp> [player]");
        $this->setPermission("essentialsx.warp");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
        $this->warpManager = $warpManager;
        $this->plugin = $plugin;
    }

    /**
     * @throws \JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): mixed
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        if (count($args) === 0) {
            $sender->sendMessage(TextFormat::RED . "Usage: /warp <warp-name> [player]");
            return false;
        }

        $subCommand = strtolower($args[0]);

        if ($subCommand === "list") {
            $warps = $this->warpManager->getWarps();
            if (empty($warps)) {
                $sender->sendMessage(TextFormat::DARK_RED . "No Available warps.");
            } else {
                $sender->sendMessage(TextFormat::GOLD . "Available warps: " . TextFormat::RED . implode(TextFormat::WHITE . ", " . TextFormat::RED , array_keys($warps)));
            }
            return false;
        }

        if (count($args) === 1) {
            $warpName = $args[0];
            $warpPosition = $this->warpManager->getWarpPosition($warpName);

            if ($warpPosition !== null) {
                $this->warpManager->createWarp($sender, $warpName);
                $sender->teleport($warpPosition);
                $sender->sendMessage(TextFormat::GREEN . "You have been warped to '{$warpName}'.");
                return true;
            } else {
                $sender->sendMessage(TextFormat::RED . "Warp '{$warpName}' does not exist.");
                return false;
            }
        }

        if (count($args) <= 2) {
            $sender->sendMessage(TextFormat::RED . "Usage: /warp <warp-name> <player>");
            return false;
        }

        $warpName = $args[0];
        $targetPlayer = $args[1];

        $warpPosition = $this->warpManager->getWarpPosition($warpName);

        if ($warpPosition !== null) {
            if ($sender->hasPermission("essentialsx.warp.other")) {
                $player = Server::getInstance()->getPlayerByPrefix($targetPlayer);
                if ($player === null) {
                    $sender->sendMessage(TextFormat::RED . "Player '{$targetPlayer}' not found.");
                    return false;
                }
                $this->warpManager->createWarp($player, $warpName);
                $player->teleport($warpPosition);
                $sender->sendMessage(TextFormat::GREEN . "Player '{$targetPlayer}' has been warped to '{$warpName}'.");
                return true;
            } else {
                $sender->sendMessage(EssentialsX::NOPERMISSION);
                return false;
            }
        } else {
            $sender->sendMessage(TextFormat::RED . "Warp '{$warpName}' does not exist.");
            return false;
        }
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
