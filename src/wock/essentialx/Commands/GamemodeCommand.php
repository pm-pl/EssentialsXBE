<?php

namespace wock\essentialx\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;

class GamemodeCommand extends Command {

    public function __construct() {
        parent::__construct("gamemode", "Change the game mode", "/gamemode <mode> [player]", ["gm"]);
        $this->setPermission("pocketmine.command.gamemode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(str_replace("&", "§", $this->getConfigMessage()->getNested("gamemode.in_game_only", "&cYou must run this command in-game")));
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage(str_replace("&", "§", $this->getConfigMessage()->getNested("gamemode.invalid_arguments", "&cInvalid arguments: /gamemode <mode> [player]")));
            return false;
        }

        $gameMode = $this->matchGameMode($args[0]);

        if ($gameMode === null) {
            $sender->sendMessage(str_replace("&", "§", $this->getConfigMessage()->getNested("gamemode.invalid_gamemode", "&cInvalid gamemode")));
            return false;
        }

        $targetPlayer = $sender;
        if (count($args) > 1) {
            $targetPlayer = Server::getInstance()->getPlayerExact($args[1]);
            if ($targetPlayer === null) {
                $message = $this->getConfigMessage()->getNested("gamemode.player_not_found", "&cPlayer '{player}' does not exist or is offline.");
                $message = str_replace("{player}", $args[1], $message);
                $sender->sendMessage(str_replace("&", "§", $message));
                return false;
            }
        }

        if ($this->isProhibitedChange($sender, $gameMode)) {
            $sender->sendMessage(str_replace("&", "§", $this->getConfigMessage()->getNested("gamemode.no_permission", "&cYou don't have permission to change to that game mode.")));
            return false;
        }

        $targetPlayer->setGamemode($gameMode);

        $message = $this->getConfigMessage()->getNested("gamemode.set_gamemode", "&aSet {target_player}'s game mode to {gamemode}.");
        $message = str_replace("{target_player}", $targetPlayer->getName(), $message);
        $message = str_replace("{gamemode}", $gameMode->getEnglishName(), $message);
        $sender->sendMessage(str_replace("&", "§", $message));

        return true;
    }

    private function getConfigMessage(): Config {
       $config = new Config(EssentialsX::getInstance()->getDataFolder() . "config.yml", Config::YAML);
       return $config;
    }

    private function matchGameMode(string $modeString): ?GameMode {
        $modeString = strtolower($modeString);

        $gameModes = [
            "gmc" => GameMode::CREATIVE(),
            "c" => GameMode::CREATIVE(),
            "creative" => GameMode::CREATIVE(),
            "gms" => GameMode::SURVIVAL(),
            "s" => GameMode::SURVIVAL(),
            "survival" => GameMode::SURVIVAL(),
            "gma" => GameMode::ADVENTURE(),
            "a" => GameMode::ADVENTURE(),
            "adventure" => GameMode::ADVENTURE(),
            "gmsp" => GameMode::SPECTATOR(),
            "sp" => GameMode::SPECTATOR(),
            "spectator" => GameMode::SPECTATOR(),
        ];

        return $gameModes[$modeString] ?? null;
    }

    private function isProhibitedChange(Player $player, GameMode $gameMode): bool {
        return !$player->hasPermission("pocketmine.command.gamemode");
    }
}
