<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\DragonEggTeleportParticle;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use wock\essentialx\EssentialsX;
use wock\essentialx\Tasks\ParticleRepeatTask;
use wock\essentialx\Utils\testSpawn;
use wock\essentialx\Utils\Utils;

class BanLookupCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("banlookup", "View a players ban infomration", "/banlookup <player|list>");
        $this->setPermission("essentialsx.banlookup");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be executed in-game.");
            return true;
        }

        if (count($args) < 1) {
            $sender->sendMessage(TextFormat::RED . "Usage: /banlookup <player>");
            return true;
        }

        $playerName = strtolower($args[0]);

        if ($playerName === "list") {
            $bannedPlayers = Server::getInstance()->getNameBans()->getEntries();
            $sender->sendMessage(TextFormat::GOLD . "Banned Players:");
            foreach ($bannedPlayers as $bannedPlayer) {
                $sender->sendMessage(TextFormat::GOLD . "- " . TextFormat::RED . $bannedPlayer->getName());
            }
            return true;
        }

        $banInfo = Server::getInstance()->getNameBans()->getEntry($playerName);

        if ($banInfo === null) {
            $sender->sendMessage(TextFormat::GOLD . "The player " . TextFormat::RED . $playerName . TextFormat::GOLD . " is not banned.");
        } else {
            $sender->sendMessage(TextFormat::GOLD . "Ban Information for " . TextFormat::RED . $playerName . TextFormat::GOLD . ":");
            $sender->sendMessage(TextFormat::GOLD ."- Reason: " . TextFormat::RED . $banInfo->getReason());
        }
        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
