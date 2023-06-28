<?php

namespace wock\essentialx\Utils;

use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class Utils {

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
            $message = self::getConfigMessage()->getNested("fly.disabled", "§cYou can no longer fly.");
            $message = str_replace("&", "§", $message);
            $player->sendMessage($message);
        } else {
            if (!$player->getAllowFlight()) {
                $player->setAllowFlight(true);
                $message = self::getConfigMessage()->getNested("fly.enabled", "§sYou can now fly.");
                $message = str_replace("&", "§", $message);
                $player->sendMessage($message);    
            } else {
                $player->setAllowFlight(false);
                $player->setFlying(false);
                $player->resetFallDistance(); 
                $message = self::getConfigMessage()->getNested("fly.disabled", "§cYou can no longer fly.");
                $message = str_replace("&", "§", $message);
                $player->sendMessage($message);            }
        }
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

        public static function formatTime(int $seconds): string
    {
        $years = floor($seconds / 31536000);
        $months = floor(($seconds % 31536000) / 2592000);
        $days = floor((($seconds % 31536000) % 2592000) / 86400);
        $hours = floor(((($seconds % 31536000) % 2592000) % 86400) / 3600);
        $minutes = floor((((($seconds % 31536000) % 2592000) % 86400) % 3600) / 60);
        $seconds = (((($seconds % 31536000) % 2592000) % 86400) % 3600) % 60;

        $timeString = "";
        if ($years > 0) {
            $timeString .= $years . "y ";
        }
        if ($months > 0) {
            $timeString .= $months . "mo ";
        }
        if ($days > 0) {
            $timeString .= $days . "d ";
        }
        if ($hours > 0) {
            $timeString .= $hours . "h ";
        }
        if ($minutes > 0) {
            $timeString .= $minutes . "m ";
        }
        if ($seconds > 0) {
            $timeString .= $seconds . "s";
        }

        return trim($timeString);
    }

    public static function getConfigMessage(): Config {
        return new Config(EssentialsX::getInstance()->getDataFolder() . "config.yml", Config::YAML);
    }
}
