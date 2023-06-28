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

class SpawnCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("spawn", "Teleports you the spawn of the world you're in", "/spawn");
        $this->setPermission("essentialsx.spawn");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be run by a player!");
            return false;
        }
        $position = $sender->getPosition();
        if (count($args) > 0 && $sender->hasPermission("essentialsx.spawn.others")) {
            $otherPlayer = Server::getInstance()->getPlayerByPrefix($args[0]);
            if ($otherPlayer !== null) {
                $otherPlayer->teleport($sender->getWorld()->getSpawnLocation());
                Utils::playSound($sender, "mob.enderdragon.flap");
                EssentialsX::getInstance()->getScheduler()->scheduleRepeatingTask(new ParticleRepeatTask($sender, "minecraft:campfire_tall_smoke_particle"), 60);
                $sender->sendMessage("Teleported " . $otherPlayer->getName() . " to the spawn point.");
            } else {
                $sender->sendMessage(TextFormat::RED . "Player not found.");
            }
            return true;
        }

        switch (strtolower($args[0] ?? "")) {
            case "set":
                if (!$sender->hasPermission("essentialsx.spawn.set")) { // Check for permission
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this subcommand!");
                    return false;
                }
                $sender->getWorld()->setSpawnLocation($sender->getPosition());
                Utils::playSound($sender, "portal.portal");
                $sender->getWorld()->addParticle($position, new EndermanTeleportParticle());
                $sender->sendMessage(TextFormat::GREEN . "Spawn point set to your current location.");
                return true;
            default:
                $sender->teleport($sender->getWorld()->getSpawnLocation());
                Utils::playSound($sender, "mob.enderdragon.flap");
                Utils::spawnParticle($sender, "minecraft:blue_flame_particle");
                $sender->sendMessage(TextFormat::GREEN . "Teleporting to spawn point.");
                return true;
        }
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}