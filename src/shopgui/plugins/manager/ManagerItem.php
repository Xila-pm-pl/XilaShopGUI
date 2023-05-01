<?php declare(strict_types = 1);
namespace shopgui\plugins\manager;

use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class ManagerItem {

    # For SHOP
    public const NEXT = 399;
    public const BACK = 381;
    # For TROLLY
    public const CONFIRM = 399;
    public const CANCEL = 381;

    public const OUTLINE = ItemIds::VINE;

    /**
     * @return ItemFactory
     */
    public static function Item(): ItemFactory {
        return ItemFactory::getInstance();
    }
    
    /**
     * @param string $format
     * @param string $type
     * @return integer
     */
    public function registerItem(string $format, string $type = "id"): int {
        if (strpos($format, ':')) {
            $item = explode(':', $format);
            $id = $this->registerItemWithString($item[0]);
            $meta = (int)$item[1];
        } else {
            $id = $this->registerItemWithString($format);
            $meta = 0;
        }
        return ($type === "id") ? $id : $meta;
    }
    
    /**
     * @param string|integer $items
     * @return integer
     */
    public function registerItemWithString(string|int $item): int {
        if (!is_numeric($item)) {
            $item = LegacyStringToItemParser::getInstance()->parse($item)->getId();
        }
        return (int)$item;
    }
    
    /**
     * @param Item $item
     * @return boolean
     */
    public function itemClicked(string $action, Item $item): bool {
        switch ($action) {
            case 'next':
                return ($item->getId() === self::NEXT && $item->getMeta() === 0);
            
            case 'back':
                return ($item->getId() === self::BACK && $item->getMeta() === 0);
                break;

            case 'confirm':
                return ($item->getId() === self::CONFIRM && $item->getMeta() === 0);
                break;

            case 'cancel':
                return ($item->getId() === self::CANCEL && $item->getMeta() === 0);
                break;

            case 'outline':
                return ($item->getId() === self::OUTLINE && $item->getMeta() === 0);
                break;
        }
    } 
}