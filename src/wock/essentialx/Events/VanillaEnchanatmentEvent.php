<?php

namespace wock\essentialx\Events;

use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\Living;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\Server;
use wock\essentialx\Enchantments\BaneOfArthropodsEnchantment;
use wock\essentialx\Enchantments\FortuneEnchantment;
use wock\essentialx\Enchantments\LootingEnchantment;
use wock\essentialx\Enchantments\SmiteEnchantment;
use wock\essentialx\Utils\Utils;

class VanillaEnchanatmentEvent implements Listener {

    public const UNDEAD = [
        EntityIds::ZOMBIE,
        EntityIds::HUSK,
        EntityIds::WITHER,
        EntityIds::SKELETON,
        EntityIds::STRAY,
        EntityIds::WITHER_SKELETON,
        EntityIds::ZOMBIE_PIGMAN,
        EntityIds::ZOMBIE_VILLAGER
    ];

    public const ARTHROPODS = [
        EntityIds::SPIDER,
        EntityIds::CAVE_SPIDER,
        EntityIds::SILVERFISH,
        EntityIds::ENDERMITE
    ];

    public function onBreak(BlockBreakEvent $event): void
    {
        $block = $event->getBlock();
        $item = $event->getItem();
        $enchantment = new FortuneEnchantment();

        if ($block->isSameState(VanillaBlocks::OAK_LEAVES())) {
            if (mt_rand(1, 99) <= 10) {
                $event->setDrops([VanillaItems::APPLE()]);
            }
        }

        if (($level = $item->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId($enchantment->getMcpeId()))) > 0) {
            $add = mt_rand(0, $level + 1);

            if ($block->isSameState(VanillaBlocks::OAK_LEAVES())) {
                if (mt_rand(1, 99) <= 10) {
                    $event->setDrops([VanillaItems::APPLE()]);
                }
            }

            foreach (Utils::getConfig()->get("fortune.blocks", []) as $str) {
                $itemFortune = LegacyStringToItemParser::getInstance()->parse($str);

                if ($block->asItem()->equals($itemFortune)) {
                    if (mt_rand(1, 99) <= 10 * $level) {
                        if (!empty($event->getDrops())) {
                            $event->setDrops(array_map(function (Item $drop) use ($add) {
                                $drop->setCount($drop->getCount() + $add);
                                return $drop;
                            }, $event->getDrops()));
                        }
                    }
                    break;
                }
            }
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     */
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $player = $event->getEntity();
        $killer = $event->getDamager();
        if ($killer instanceof Player) {
            $item = $killer->getInventory()->getItemInHand();
            $smiteEnchantment = new SmiteEnchantment();
            $arthropodsEnchantment = new BaneOfArthropodsEnchantment();
            $lootingEnchantment = new LootingEnchantment();
            if ($item->hasEnchantment(EnchantmentIdMap::getInstance()->fromId($smiteEnchantment->getMcpeId()))) {
                if (in_array($player::getNetworkTypeId(), self::UNDEAD)) {
                    $event->setBaseDamage($event->getBaseDamage() + (2.5 * $item->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId($smiteEnchantment->getMcpeId()))));
                }
            }
            if ($item->hasEnchantment(EnchantmentIdMap::getInstance()->fromId($arthropodsEnchantment->getMcpeId()))) {
                if (in_array($player::getNetworkTypeId(), self::ARTHROPODS)) {
                    $event->setBaseDamage($event->getBaseDamage() + (2.5 * $item->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId($arthropodsEnchantment->getMcpeId()))));
                }
            }
            if (($level = $killer->getInventory()->getItemInHand()->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId($lootingEnchantment->getMcpeId()))) > 0) {
                if (
                    !$player instanceof Player and
                    $player instanceof Living and
                    $event->getFinalDamage() >= $player->getHealth()
                ) {
                    $add = mt_rand(0, $level + 1);
                    if (is_bool(Utils::getConfig()->get("looting.entities"))) {
                        Server::getInstance()->getLogger()->debug("There is an error (looting) in the config of vanillaEC");
                        return;
                    }
                    $lootingMultiplier = Utils::getConfig()->get("looting.drop_multiplier", 1); // Drop multiplier from config

                    foreach (Utils::getConfig()->get("looting.entities", []) as $items) {
                        $items = []; // Assign an empty array or populate it with the required values

                        $drops = $this->getLootingDrops($player->getDrops(), $items, $add, $lootingMultiplier);
                        foreach ($drops as $drop) {
                            $killer->getWorld()->dropItem($player->getPosition()->asVector3(), $drop);
                        }
                        $player->flagForDespawn();
                    }
                }
            }
        }
    }

    /**
     * @param array $drops
     * @param array $items
     * @param int   $add
     * @param int   $multiplier
     * @return array
     */
    public function getLootingDrops(array $drops, array $items, int $add, int $multiplier): array
    {
        $lootingDrops = [];

        foreach ($items as $ite) {
            $item = LegacyStringToItemParser::getInstance()->parse($ite);
            /** @var Item $drop */
            foreach ($drops as $drop) {
                if ($drop->equals($item)) {
                    $drop->setCount($drop->getCount() + ($add * $multiplier));
                }
                $lootingDrops[] = $drop;
                break;
            }
        }

        return $lootingDrops;
    }

    /**
     * @param EntityShootBowEvent $event
     */
    public function onShoot(EntityShootBowEvent $event): void
    {
        $arrow = $event->getProjectile();

        if ($arrow::getNetworkTypeId() == EntityIds::ARROW) {
            $event->setForce($event->getForce() + 0.95);
        }
    }
}
