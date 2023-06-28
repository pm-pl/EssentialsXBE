<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Utils\Utils;

class NearCommand extends Command implements PluginOwned {

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin){
        parent::__construct("near", "View players near a specific radius from you", "/near");
        $this->setPermission("essentialsx.near");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used by players");
            return;
        }

        $config = Utils::getConfig(); // Assuming you have a configuration file or access to plugin settings
        $radius = $config->get("near-radius", 10); // Get the radius value from the configuration with a default of 10
        $world = $sender->getWorld();

        $nearby = [];
        foreach ($world->getPlayers() as $p) {
            if ($p !== $sender) {
                $x = $p->getPosition()->getX() - $sender->getPosition()->getX();
                $y = $p->getPosition()->getY() - $sender->getPosition()->getY();
                $z = $p->getPosition()->getZ() - $sender->getPosition()->getZ();
                $dist = round(sqrt($x * $x + $y * $y + $z * $z));
                if ($dist <= $radius) {
                    $nearby[] = $p->getNameTag() . " ($dist blocks)";
                }
            }
        }

        if (count($nearby) > 0) {
            $sender->sendMessage(TextFormat::GOLD . "Players within " . TextFormat::RED . $radius . TextFormat::GOLD . " blocks: " . TextFormat::RED . implode(", ", $nearby));
        } else {
            $sender->sendMessage(TextFormat::GOLD . "No players found within " . TextFormat::RED .$radius . TextFormat::GOLD . " blocks.");
        }
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
