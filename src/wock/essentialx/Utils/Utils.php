<?php

namespace wock\essentialx\Utils;

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;

class Utils {

    // PLAYYER QUERY
    public const PLAYERS_INIT   = "players.initialize";
    public const PLAYERS_SELECT = "players.select";
    public const PLAYERS_CREATE = "players.create";
    public const PLAYERS_UPDATE = "players.update";
    public const PLAYERS_DELETE = "players.delete";

    // HOMES QUERY
    public const HOMES_INIT   = "players.homes.initialize";
    public const HOMES_SELECT = "players.homes.select";
    public const HOMES_CREATE = "players.homes.create";
    public const HOMES_UPDATE = "players.homes.update";
    public const HOMES_DELETE = "players.homes.delete";

    /**
     * @param int $level
     * @return int
     */
    public static function getExpToLevelUp(int $level): int
    {
        if ($level <= 15) {
            return 2 * $level + 7;
        } else if ($level <= 30) {
            return 5 * $level - 38;
        } else {
            return 9 * $level - 158;
        }
    }

    public static function toggleFlight(Player $player, bool $forceOff = false): void
    {
        if ($forceOff) {
            $player->setAllowFlight(false);
            $player->setFlying(false);
            $player->resetFallDistance();
            $message = self::getConfiguration(EssentialsX::getInstance(), "messages-eng.yml")->getNested("fly.disabled", "§cYou can no longer fly.");
            $player->sendMessage(TextFormat::colorize($message));
        } else {
            if (!$player->getAllowFlight()) {
                $player->setAllowFlight(true);
                $message = self::getConfiguration(EssentialsX::getInstance(), "messages-eng.yml")->getNested("fly.enabled", "§sYou can now fly.");
                $player->sendMessage(TextFormat::colorize($message));
            } else {
                $player->setAllowFlight(false);
                $player->setFlying(false);
                $player->resetFallDistance();
                $message = self::getConfiguration(EssentialsX::getInstance(), "messages-eng.yml")->getNested("fly.disabled", "§cYou can no longer fly.");
                $player->sendMessage(TextFormat::colorize($message));
            }
        }
    }

    /**
     * Returns an online player whose name begins with or equals the given string (case insensitive).
     * The closest match will be returned, or null if there are no online matches.
     *
     * @param string $name The prefix or name to match.
     * @return Player|null The matched player or null if no match is found.
     */
    public static function getPlayerByPrefix(string $name): ?Player {
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;

        /** @var Player[] $onlinePlayers */
        $onlinePlayers = Server::getInstance()->getOnlinePlayers();

        foreach ($onlinePlayers as $player) {
            if (stripos($player->getName(), $name) === 0) {
                $curDelta = strlen($player->getName()) - strlen($name);

                if ($curDelta < $delta) {
                    $found = $player;
                    $delta = $curDelta;
                }

                if ($curDelta === 0) {
                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param Entity $player
     * @param string $sound
     * @param int $volume
     * @param int $pitch
     * @param int $radius
     */
    public static function playSound(Entity $player, string $sound, $volume = 1, $pitch = 1, int $radius = 5): void
    {
        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $spk = new PlaySoundPacket();
                    $spk->soundName = $sound;
                    $spk->x = $p->getLocation()->getX();
                    $spk->y = $p->getLocation()->getY();
                    $spk->z = $p->getLocation()->getZ();
                    $spk->volume = $volume;
                    $spk->pitch = $pitch;
                    $p->getNetworkSession()->sendDataPacket($spk);
                }
            }
        }
    }

    /**
     * @param Entity $player
     * @param string $particleName
     * @param int $radius
     */
    public static function spawnParticle(Entity $player, string $particleName, int $radius = 5): void {
        $packet = new SpawnParticleEffectPacket();
        $packet->particleName = $particleName;
        $packet->position = $player->getPosition()->asVector3();

        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $p->getNetworkSession()->sendDataPacket($packet);
                }
            }
        }
    }

    public static function translateTime(int $seconds): string
    {
        $timeUnits = [
            'w' => 60 * 60 * 24 * 7,
            'd' => 60 * 60 * 24,
            'h' => 60 * 60,
            'm' => 60,
            's' => 1,
        ];

        $parts = [];

        foreach ($timeUnits as $unit => $value) {
            if ($seconds >= $value) {
                $amount = floor($seconds / $value);
                $seconds %= $value;
                $parts[] = $amount . $unit;
            }
        }

        return implode(', ', $parts);
    }

    public static function getConfiguration(PluginBase $plugin, string $fileName): Config {
        $pluginFolder = $plugin->getDataFolder();
        $filePath = $pluginFolder . $fileName;

        $config = null;

        if (!file_exists($filePath)) {
            $plugin->getLogger()->warning("Configuration file '$fileName' not found.");
        } else {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

            switch ($extension) {
                case 'yml':
                case 'yaml':
                    $config = new Config($filePath, Config::YAML);
                    break;

                case 'json':
                    $config = new Config($filePath, Config::JSON);
                    break;

                default:
                    $plugin->getLogger()->warning("Unsupported configuration file format for '$fileName'.");
                    break;
            }
        }

        return $config;
    }
}
