<?php declare(strict_types = 1);
namespace shopgui\plugins\inventory;

use pocketmine\inventory\Inventory;
use pocketmine\player\Player;
use muqsit\invmenu\InvMenu;
use shopgui\plugins\manager\ManagerItem;
use shopgui\plugins\utils\Translate;

class ChestTrolly extends ManagerItem {

    use Translate;
    
    public function __construct(private InvMenu $inventory, string $name_chest) {
        $this->inventory = $inventory;
        $this->inventory->setName($name_chest);
    }

    /**
     * @return Inventory
     */
    public function getInventory(): Inventory {
        return $this->inventory->getInventory();
    }

    /**
     * @return integer
     */
    public function getSize(): int {
        return $this->inventory->getInventory()->getSize();
    }

    /**
     * @param callable|null $callable
     * @return void
     */
    public function setListener(?callable $callable) {
        $this->inventory->setListener($callable);
    }

    /**
     * @param callable|null $callable
     * @return void
     */
    public function setInventoryCloseListener(?callable $callable) {
        $this->inventory->setInventoryCloseListener($callable);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function onClose(Player $player) {
        $this->getInventory()->clearAll();
        $player->removeCurrentWindow();
    }

    /**
     * @param array $trolly
     * @return void
     */
    public function setItems(array $trolly = []) {
        if (count($trolly) > 0) {
            foreach ($trolly as $slots => $data) {
                $array = explode('--', $data);
                $id = $this->registerItem($array[0], "id");
                $meta = $this->registerItem($array[0], "meta");

                $item = parent::Item()->get($id, $meta, ($amount = (isset($array[2])) ? (int)$array[2] : 1));
                $item->setCustomName('§l§bName Item: §r§f'.$item->getName());
                $item->setLore(['§6========================',
                                ' ',
                                '§l§bBuying x'.$amount,
                                '§l§bPrice: '.$array[1],
                                ' ',
                                '§r§6Click this item',
                                '§6for §cdelete §6in list your trolly']);
                
                $this->getInventory()->setItem((int)$slots, $item);
                
                if ((int)$slots > 44) {
                    break;
                }
            }
        } else {
            $this->getInventory()->setItem(0, parent::Item()->get(0));
        }
    }

    /**
     * @param array $trolly
     * @param string|null $myMoney
     * @return void
     */
    public function setOutLine(array $trolly, ?string $myMoney) {
    	if ($myMoney === null) {
    	    $myMoney = '--';
        }
        
        $i = 45;
        while ($i < $this->getSize()) {
            $this->getInventory()->setItem($i, parent::Item()->get(parent::OUTLINE, 0, 1)->setCustomName('---'));
            $i++;
        }

        $amount = count($trolly['Trolly']);
        $price = $trolly['Total_price'];

        $itemCancle = parent::Item()->get(parent::CANCEL, 0, 1)->setCustomName('§l§o§cCancle buy all items in trolly');
        $itemCancle->setLore(['§r§6=============================',
                              '  ',
                              '§6Click this item',
                              '§6for §ccancle §6'.$amount.' item in trolly']);
        
        $itemConfirm = parent::Item()->get(parent::CONFIRM, 0, 1)->setCustomName('§l§o§aConfirm buy all items in trolly');
        $itemConfirm->setLore(['§r§6==============================',
                              '  ',
                              '§l§bAll Item: '.$amount,
                              ($myMoney === '--') ? '§l§bTotal Price: §o§aFree§r' : '§l§bTotal Price: '.$price,
                              '§l§bYour Money: '.$myMoney,
                              '  ',
                              '§r§6Click this item',
                              '§6for §aconfirm §6buy all.']);

        $this->getInventory()->setItem(45, $itemCancle);
        $this->getInventory()->setItem(53, $itemConfirm);
    }

    /**
     * @param Player $player
     * @return void
     */
    public function sendTo(Player $player) {
        return $this->inventory->send($player);
    }
}
