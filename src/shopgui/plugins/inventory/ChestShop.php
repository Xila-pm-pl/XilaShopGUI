<?php declare(strict_types = 1);

namespace shopgui\plugins\inventory;



use pocketmine\inventory\Inventory;

use pocketmine\player\Player;

use muqsit\invmenu\InvMenu;

use shopgui\plugins\manager\ManagerItem;

use shopgui\plugins\utils\Translate;



class ChestShop extends ManagerItem {

	
	use Translate;



    /** @var integer */

    private int $page = 0;

    

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

     * @return integer

     */

    public function getPage(): int {

        return $this->page;

    }



    /**

     * @param integer $page

     * @return void

     */

    public function setPage(int $page) {

        $this->page = $page;

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

     * @param array $data_shops

     * @return void

     */

    public function setItems(array $data_shops = []) {

        foreach ($data_shops as $pages => $shops) {

            if ($pages === $this->page) {

                foreach ($shops['list'] as $slots => $data) {

                    $array = explode('--', $data);

                    $id = $this->registerItem($array[0], "id");

                    $meta = $this->registerItem($array[0], "meta");



                    $item = parent::Item()->get($id, $meta, ($amount = (isset($array[2])) ? (int)$array[2] : 1));

                    if (isset($array[3]) && strtolower($array[3]) !== 'default') {

                        $item->setCustomName($array[3]);

                    }

                    $lore = '§l§bBuy Item: x'.$amount.' | Price: '.($price = (isset($array[1])) ? (int)$array[1] : 0);

                    if (isset($array[4])) {

                        $lore = $this->translate(

                                          ["{amount}", "{price}"],

                                          [$amount, $price],

                                          $array[4]);

                    }

                    $item->setLore([$lore, ' ', '§r§6Add trolly, if wanna buy this item']);

                    

                    $this->getInventory()->setItem((int)$slots, $item);

                    

                    if ((int)$slots > (($this->getSize() === 27) ? 17 : 44)) {

                        break;

                    }

                }

            }

        }

    }



    /**

     * @param array $data_shops

     * @return void

     */

    public function setOutLine(array $data_shops = []) {

        if (($size = $this->getSize()) === 27) {

            $i = 18;

        } elseif ($size === 54) {

            $i = 45;

        }

        while ($i < $size) {

            $this->getInventory()->setItem($i, parent::Item()->get(parent::OUTLINE, 0, 1)->setCustomName('---'));

            $i++;

        }



        if (($maxPage = count($data_shops)) > 1) {

            $page = $this->page + 1;

            if ($page === 1) {

                ($size === 27) ? $this->getInventory()->setItem(26, parent::Item()->get(parent::NEXT, 0, 1)->setCustomName('§l§aNext')) : $this->getInventory()->setItem(53, parent::Item()->get(parent::NEXT, 0, 1)->setCustomName('§l§aNext'));

            } elseif ($page > 1 and $page < $maxPage) {

                ($size === 27) ? $this->getInventory()->setItem(18, parent::Item()->get(parent::BACK, 0, 1)->setCustomName('§l§cBack')) : $this->getInventory()->setItem(45, parent::Item()->get(parent::BACK, 0, 1)->setCustomName('§l§cBack'));

                ($size === 27) ? $this->getInventory()->setItem(26, parent::Item()->get(parent::NEXT, 0, 1)->setCustomName('§l§aNext')) : $this->getInventory()->setItem(53, parent::Item()->get(parent::NEXT, 0, 1)->setCustomName('§l§aNext'));

            } elseif ($page === $maxPage) {

                ($size === 27) ? $this->getInventory()->setItem(18, parent::Item()->get(parent::BACK, 0, 1)->setCustomName('§l§cBack')) : $this->getInventory()->setItem(45, parent::Item()->get(parent::BACK, 0, 1)->setCustomName('§l§cBack'));

            }

        }

    }



    /**

     * @param Player $player

     * @return void

     */

    public function sendTo(Player $player) {

        return $this->inventory->send($player);

    }

}
