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

class FeedCommand extends Command implements PluginOwned
{
    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct()
    {
        parent::__construct("feed", "Settle your appetite", "/feed");
        $this->setPermission("essentialsx.feed");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be run by a player.");
            return false;
        }
        $sender->getHungerManager()->setFood($sender->getHungerManager()->getMaxFood());
        $sender->sendMessage(TextFormat::GOLD . "Your appetite has been sated");
        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}