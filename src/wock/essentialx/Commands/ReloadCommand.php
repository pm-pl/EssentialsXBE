<?php

declare(strict_types=1);

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Utils\Utils;

class ReloadCommand extends Command implements PluginOwned
{
    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct()
    {
        parent::__construct("essentialsxreload", "Reload all EssentialsX configurations", "/reload");
        $this->setPermission("essentialsx.reload");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be run by a player.");
            return false;
        }

        if ($sender->hasPermission("essentialsx.reload")) {
            Utils::getConfigMessage()->reload();
            $sender->sendMessage(TextFormat::GREEN . "Successfully reloaded EssentialsX configuration");
            return true;
        } else {
            $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
            return false;
        }
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}