<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Managers\HomeManager;
use wock\essentialx\Managers\WarpManager;

class HomesCommand extends Command implements PluginOwned {

    public HomeManager $homeManager;

    /** @var EssentialsX */
    public EssentialsX $plugin;

    public function __construct(EssentialsX $plugin, HomeManager $homeManager){
        parent::__construct("homes", "View all your homes", "/homes");
        $this->setPermission("essentialsx.home");
        $this->setPermissionMessage(EssentialsX::NOPERMISSION);
        $this->homeManager = $homeManager;
        $this->plugin = $plugin;
    }

    /**
     * @throws \JsonException
     * @throws \Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        if (count($args) === 0) {
            $homes = $this->homeManager->getAllHomes($sender);
            if (empty($homes)) {
                $sender->sendMessage(TextFormat::RED . "You have no homes set.");
            } else {
                $sender->sendMessage(TextFormat::GOLD . "Your homes:");
                foreach ($homes as $homeName => $homePosition) {
                    $sender->sendMessage(TextFormat::GREEN . "- {$homeName}: {$homePosition['x']}, {$homePosition['y']}, {$homePosition['z']} ({$homePosition['world']})");
                }
            }
            return true;
        }

        $homeName = $args[0];
        $player = $sender;

        $result = $this->homeManager->teleportToHome($player, $homeName);
        if ($result) {
            $player->sendMessage(TextFormat::GREEN . "Teleported to home '{$homeName}'.");
            return true;
        } else {
            $sender->sendMessage(TextFormat::RED . "Home '{$homeName}' does not exist.");
            return false;
        }
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}