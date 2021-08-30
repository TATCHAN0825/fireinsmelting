<?php

declare(strict_types=1);

namespace tatchan\fireinsmelting;

use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @priority HIGHEST
     */
    public function ondamge(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        $cause = $event->getCause();
        if ($entity instanceof ItemEntity && ($cause === $event::CAUSE_FIRE || $cause === $event::CAUSE_FIRE_TICK)) {
            if ($entity->getHealth() - $event->getFinalDamage() < 0) {
                //このダメージによって死んでいたら
                $item = $entity->getItem();
                $recipe = $this->getServer()->getCraftingManager()->matchFurnaceRecipe($item);
                if ($recipe !== null) {
                    $count = $item->getCount();
                    for ($i = 0; $i < $count; $i++) {
                        $smeltedItem = $recipe->getResult();
                        //$smeltedItem->setCount($item->getCount());
                        $dropPosition = $entity->asVector3();
                        $dropPosition->y += 1.25;//すぐ燃えないように
                        do {//TODO もっとちゃんと計算したい
                            $motionX = $this->randomValue(-0.1, 0.1, 0);
                            $motionZ = $this->randomValue(-0.1, 0.1, 0);
                        } while ($motionX === 0 && $motionZ === 0);
                        $entity->getLevel()->dropItem($dropPosition, $smeltedItem, new Vector3($motionX, 0.25, $motionZ));
                    }
                }
            }
        }
    }

    public function randomValue(...$values) {
        return $values[array_rand($values)];
    }
}
