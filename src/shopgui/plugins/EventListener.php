<?php 

 declare(strict_types = 1);
 
namespace shopgui\plugins;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\player\Player;
use shopgui\plugins\data\DataTrolly;

class EventListener implements Listener {

    public function __construct(private DataTrolly $data) {}

    /**
     * @param PlayerLoginEvent $event
     * @return void
     */
    public function onLogin(PlayerLoginEvent $event): void {
        if (!($player = $event->getPlayer()) instanceof Player) {
            return;
        }

        if (!$this->data->hasDataTrolly($player_name = $player->getName())) {
            $this->data->createDataTrolly($player_name);
        }
    }

    // public function onBlockPlace(BlockPlaceEvent $event) {
    //     $player = $event->getPlayer();
    //     if(!$player instanceof Player) {
    //         return;
    //     }

    //     $item = $event->getItem();
    //     $inventory = $player->getInventory();
    //     $tag = $item->getNamedTagEntry("Packet Box");
    //     if($tag === null) {
    //         return;
    //     }

    //     if($tag instanceof CompoundTag) {
    //         $event->setCancelled(true);
    //         $inventory->setItemInHand($item->setCount($item->getCount() - 1));
            
    //         $block = $event->getBlock();
    //         $x = $block->getX();
    //         $y = $block->getY();
    //         $z = $block->getZ();

    //         $level = Server::getInstance()->getLevelByName($player->getLevel()->getName());
    //         $level->setBlock(new Vector3($x, $y, $z), new Chest());
    //         $nbt = new CompoundTag(" ", [
    //             new StringTag("id", Tile::CHEST),
    //             new IntTag("x", $x),
    //             new IntTag("y", $y),
    //             new IntTag("z", $z)
    //         ]);
    //         $chest = Tile::createTile("Chest", $level, $nbt);
    //         $level->addTile($chest);
    //         $inventory = $chest->getInventory();
    //         $listpacket = $tag->getString("Listpacket");
    //         foreach ($listpacket as $slots => $value) {
    //             // 'id:meta--price--amount';
    //             $amount = explode('--', $value)[2];
    //             $items = explode(':', $value[0]);
    //             $packet = Item::get((int)$items[0], (int)$items[1], (int)$amount);
    //             $inventory->setItem($slots, $packet);
    //         }
    //     }
    // }
}
