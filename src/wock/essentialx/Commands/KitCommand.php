<?php

declare(strict_types=1);

namespace wock\essentialx\Commands;

use pocketmine\color\Color;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use wock\essentialx\EssentialsX;
use wock\essentialx\Utils\Utils;

class KitCommand extends Command implements PluginOwned
{
    /** @var EssentialsX */
    public EssentialsX $plugin;

    private array $kitCooldowns = [];

    public function __construct()
    {
        parent::__construct("kit", "Kit command", "/kit help", ["kits"]);
        $this->setPermission("essentialsx.kit");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be run by a player.");
            return false;
        }

        if (empty($args)) {
            $sender->sendMessage(TextFormat::RED . "Invalid usage. Use /kit help for more commands.");
            return false;
        }

        $session = EssentialsX::getSessionManager()->getSession($sender);
        $config = Utils::getConfiguration(EssentialsX::getInstance(), "config.yml");
        $kitConfig = Utils::getConfiguration(EssentialsX::getInstance(), "kits.yml");
        $kits = $kitConfig->getNested("kits", []);

        $itemParser = StringToItemParser::getInstance();
        $enchantmentParser = StringToEnchantmentParser::getInstance();

        $subcommand = strtolower($args[0]);
        switch ($subcommand) {
            case "help":
                $sender->sendMessage(TextFormat::RED . "Available commands: /kit list, /kit give <player> <kit>");
                break;

            case "list":
                $kitNames = array_keys($kits);
                $sender->sendMessage(TextFormat::GOLD . "Available kits: " . TextFormat::RED . implode(", ", $kitNames));
                break;

            case "give":
                if (!$sender->hasPermission("essentialsx.kit.give")) {
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
                    return false;
                }
                if (count($args) < 3) {
                    $sender->sendMessage(TextFormat::RED . "Usage: /kit give <player> <kit>");
                    return false;
                }

                $playerName = $args[1];
                $kitName = strtolower($args[2]);

                if (!isset($kits[$kitName])) {
                    $sender->sendMessage(TextFormat::RED . "Invalid kit name. Use /kit list to see available kits.");
                    return false;
                }

                $player = Server::getInstance()->getPlayerExact($playerName);
                if (!$player instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "Invalid player.");
                    return false;
                }

                $kitData = $kits[$kitName];
                $kitItems = $kitData["items"] ?? [];

                if ($session->getCooldown($kitName) !== null || $session !== 0) {
                    $remainingCooldown = $session->getCooldown($kitName);
                    $remainingTime = Utils::translateTime($remainingCooldown);
                    $sender->sendMessage(TextFormat::GOLD . "The kit '" . TextFormat::RED . $kitName . TextFormat::GOLD . "' is on cooldown. You can use it again in " . TextFormat::RED . $remainingTime . TextFormat::GOLD . ".");
                    return false;
                }

                $inventory = $player->getInventory();
                foreach ($kitItems as $itemData) {
                    $itemString = $itemData["item"] ?? "";
                    $amount = $itemData["amount"] ?? 1;
                    $item = $itemParser->parse($itemString);

                    if ($item instanceof Item) {
                        $item->setCount($amount);

                        $name = $itemData["name"] ?? "";
                        $lore = $itemData["lore"] ?? [];
                        $color = $itemData["color"] ?? null; // New line

                        if (!empty($name)) {
                            $item->setCustomName(TextFormat::colorize($name));
                        }

                        if (!empty($lore)) {
                            $formattedLore = [];
                            foreach ($lore as $line) {
                                $formattedLore[] = TextFormat::colorize($line);
                            }
                            $item->setLore($formattedLore);
                        }

                        if ($item instanceof Armor && $color !== null) {
                            $rgb = explode(",", $color);
                            $item->setCustomColor(Color::fromRGB((int)$rgb[0]));
                        }

                        $enchantments = $itemData["enchantments"] ?? [];
                        foreach ($enchantments as $enchantmentData) {
                            $enchantmentString = $enchantmentData["enchantment"];
                            $level = $enchantmentData["level"] ?? 1;

                            $enchantment = $enchantmentParser->parse($enchantmentString);
                            if ($enchantment instanceof Enchantment) {
                                $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
                            }
                        }

                        $inventory->addItem($item);
                    }
                }

                $session->addCooldown($kitName, Utils::getConfiguration(EssentialsX::getInstance(), "kits.yml")->getNested("kits.$kitName.cooldown"));

                $sender->sendMessage(TextFormat::GOLD . "Kit '" . TextFormat::RED . $kitName . TextFormat::GOLD . "' has been given to " . TextFormat::RED . $playerName);
                break;

            default:
                $kitName = $subcommand;

                if (!isset($kits[$kitName])) {
                    $sender->sendMessage(TextFormat::RED . "Invalid kit name. Use /kit list to see available kits.");
                    return false;
                }

                $kitData = $kits[$kitName];
                $kitItems = $kitData["items"] ?? [];

                if ($session->getCooldown($kitName) !== null && $session->getCooldown($kitName) > 0) {
                    $remainingCooldown = $session->getCooldown($kitName);
                    $remainingTime = Utils::translateTime($remainingCooldown);
                    $sender->sendMessage(TextFormat::GOLD . "The kit '" . TextFormat::RED . $kitName . TextFormat::GOLD . "' is on cooldown. You can use it again in " . TextFormat::RED . $remainingTime . TextFormat::GOLD . ".");
                    return false;
                }

                $inventory = $sender->getInventory();
                foreach ($kitItems as $itemData) {
                    $itemString = $itemData["item"] ?? "";
                    $amount = $itemData["amount"] ?? 1;
                    $item = $itemParser->parse($itemString);

                    if ($item instanceof Item) {
                        $item->setCount($amount);

                        $name = $itemData["name"] ?? "";
                        $lore = $itemData["lore"] ?? [];
                        $color = $itemData["color"] ?? null;

                        if (!empty($name)) {
                            $item->setCustomName(TextFormat::colorize($name));
                        }

                        if (!empty($lore)) {
                            $formattedLore = [];
                            foreach ($lore as $line) {
                                $formattedLore[] = TextFormat::colorize($line);
                            }
                            $item->setLore($formattedLore);
                        }

                        if ($item instanceof Armor && $color !== null) {
                            $rgb = explode(",", $color);
                            $item->setCustomColor(Color::fromRGB((int)$rgb[0]));
                        }

                        $enchantments = $itemData["enchantments"] ?? [];
                        foreach ($enchantments as $enchantmentData) {
                            $enchantmentString = $enchantmentData["enchantment"];
                            $level = $enchantmentData["level"] ?? 1;

                            $enchantment = $enchantmentParser->parse($enchantmentString);
                            if ($enchantment instanceof Enchantment) {
                                $item->addEnchantment(new EnchantmentInstance($enchantment, $level));
                            }
                        }

                        $inventory->addItem($item);
                    }
                }

                $cooldown = Utils::getConfiguration(EssentialsX::getInstance(), "kits.yml")->getNested("kits.$kitName.cooldown");
                $session->addCooldown($kitName, $cooldown ?? 0);

                $sender->sendMessage(TextFormat::GOLD . "Kit '" . TextFormat::RED . $kitName . TextFormat::GOLD . "' has been given.");
                break;

        }

        return true;
    }

    public function getOwningPlugin(): EssentialsX
    {
        return $this->plugin;
    }
}
