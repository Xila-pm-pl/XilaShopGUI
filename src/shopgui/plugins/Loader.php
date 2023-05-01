<?php declare(strict_types = 1);

namespace shopgui\plugins;

use pocketmine\plugin\PluginBase;

use pocketmine\network\mcpe\NetworkSession;

use pocketmine\network\mcpe\protocol\ContainerClosePacket;

use pocketmine\utils\Config;

use muqsit\invmenu\InvMenuHandler;

use muqsit\simplepackethandler\SimplePacketHandler;

use shopgui\library\sound\SoundAPI;

use shopgui\plugins\command\Commands;

use shopgui\plugins\data\DataTrolly;

use shopgui\plugins\EventListener;

use shopgui\plugins\manager\ManagerForm;

use shopgui\plugins\manager\ManagerShop;

use shopgui\plugins\manager\ManagerTrolly;

class Loader extends PluginBase {

    /** @var Loader */

    private static Loader $instance;

    /** @var DataTrolly */

    private static DataTrolly $dataTrolly;

    /** @var string[] */

    private array $defaultShop = [

        'armors', 'blocks', 'colors',

        'farms', 'foods', 'furnitures',

        'glass', 'ices', 'ores',

        'plants', 'tools', 'woods'

    ];

    /** @var EconomyAPI|null */

    public $economyAPI = null;

    /** @var bool */

    public $economyValue = false;

    protected function onLoad(): void {

        static::$instance = $this;

        # Load Resources Config

        foreach ($this->getResources() as $files) {

            if (!file_exists($this->getDataFolder().($file = $files->getFilename()))) {

                $this->saveResource($file);

            }

        }

        

        # Register Resources Shops

        $this->registerShops();

        $this->setterDataShops();

        # Register Commands

        $this->registerCommand();

        $this->getLogger()->info("§aComplate Load Resource.");

    }

    protected function onEnable(): void {

        static::$dataTrolly = new DataTrolly($this, new Config($this->getDataFolder()."dataTrolly.yml", Config::YAML));

        if(!InvMenuHandler::isRegistered()){

            InvMenuHandler::register($this);

        }

        # Register Economy for buy items

        $this->registerEconomy();

        # Register InvCrash

        if ($this->getServer()->getPluginManager()->getPlugin("InvCrash") === null) {

            $this->registerInvCrash();

        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(self::getDataTrolly()), $this);

        $this->getLogger()->info("\n" . implode("\n", $this->onTexter()));

    }

    /**

     * @return void

     */

    private function registerShops() {

        foreach ($this->getConfigs()['LoadShops'] as $nameData) {

            if (in_array($nameData, $this->defaultShop)) {

                if (!file_exists($this->getDataFolder().($path = "shops/".$nameData.".yml"))) {

                    $this->saveResource($path);

                }

            }

            if (!file_exists($this->getDataFolder().($path = "shops/".$nameData.".yml"))) {

                $this->getLogger()->warning("'$path' not found! Pleace add list data in config 'LoadShops'!");

            }

        }

    }

    /**

     * @return void

     */

    private function setterDataShops() {

        foreach ($this->getDataShops() as $nameData) {

            $data = new Config($this->getDataFolder() . "shops/".$nameData.".yml", Config::YAML);

            if (!$data->exists("Title")) {

                $data->setNested("Title", "§lTitle Shop");

                $data->save();

                $data->reload();

            }

            if (!$data->exists("Image")) {

                $data->setNested("Image", "");

                $data->save();

                $data->reload();

            }

            if (!$data->exists("TypeShop")) {

                $data->setNested("TypeShop", "single");

                $data->save();

                $data->reload();

            }

            if (!$data->exists("Shops")) {

                $data->setNested("Shops", [["list" => ["dirt--100--64--Default--§l§bBuy {amount}: {price}"]]]);

                $data->save();

                $data->reload();

            }

        }

    }

    /**

     * @return void

     */

    private function registerCommand(): void {

        $cmd = $this->getConfigs()['Command'];

        $this->getServer()->getCommandMap()->register('ShopGUI', new Commands(

            $this,

            (isset($cmd['name'])) ? $cmd['name'] : 'pixel',

            (isset($cmd['desc'])) ? $cmd['desc'] : 'Open shops  to buy items. [by: AriefaL]', 

            $cmd['alias']

        ));

    }

    /**

     * @return void

     */

    private function registerEconomy(): void {

        if (($ecoApi = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")) !== null) {

            if ($this->getConfigs()['Economy']) {

                $this->economyAPI = $ecoApi;

                $this->economyValue = true;

            } else {

                $this->getLogger()->warning('Economy shops turn disable, all shops will be free');

            }

        } else {

            $this->getLogger()->warning('EconomyAPI plugin is not installed, all shops will be free');

        }

    }

    /**

     * @return void

     */

    private function registerInvCrash(): void {

        static $send = false;

        SimplePacketHandler::createInterceptor($this)->interceptIncoming(static function(ContainerClosePacket $packet, NetworkSession $session) use(&$send) : bool {

            $send = true;

            $session->sendDataPacket($packet);

            $send = false;

            return true;

        })->interceptOutgoing(static function(ContainerClosePacket $packet, NetworkSession $session) use(&$send) : bool {

            return $send;

        });

    }

    /**

     * @return Main

     */

    public static function getInstance(): self {

        return static::$instance;

    }

    /**

     * @return DataTrolly

     */

    public static function getDataTrolly(): DataTrolly {

        return static::$dataTrolly;

    }

    /**

     * @param string $shops

     * @return Config

     */

    public function getDataShop(string $shops): Config {

        return new Config($this->getDataFolder()."shops/".$shops.".yml", Config::YAML);

    }

    /**

     * @return array

     */

    public function getDataShops(): array {

        $list = array();

        foreach(array_diff(scandir($this->getDataFolder() . "shops/"), ["..", "."]) as $files){

            $file = explode(".", $files);

            if($file[1] === "yml"){

                array_push($list, $file[0]);

            }

        }

        return $list;

    }

    /**

     * @return array

     */

    public function getConfigs(): array {

        return $this->getConfig()->getAll();

    }

    /**

     * @return array

     */

    public function getMessage(): array {

        return $this->getConfig()->getAll()['Message'];

    }

    /**

     * @param string $shop_type

     * @return ManagerShop

     */

    public function getShop(string $shop_type): ManagerShop {

        return new ManagerShop($this, $shop_type);

    }

    /**

     * @return ManagerTrolly

     */

    public function getTrolly(): ManagerTrolly {

        return new ManagerTrolly($this, self::getDataTrolly());

    }

    /**

     * @return ManagerForm

     */

    public function getForm(): ManagerForm {

        return new ManagerForm($this);

    }

    /**

     * @return SoundAPI

     */

    public function getSound(): SoundAPI {

        return new SoundAPI();

    }

    /**

     * @return string

     */

    public function getPrefix(): string {

    	return $this->getMessage()['prefix'] ?? "§l§o§6XShopGUI §r§l§b» §r";    }

    /**

     * @return array

     */

    private function onTexter() : array {

        return [

            " ",

            "§6dXXXXXXXXb dXb     dXb dXXXXXXXXXb dXXXXXXXXb    §cdXXXXXXXXXb dXb     dXb YXXXXXY",

            "§6XXX        XXX     XXX XXX     XXX XXX     XXX   §cXXX         XXX     XXX   XXX",

            "§6XXX        XXX     XXX XXX     XXX XXX     XXX   §cXXX         XXX     XXX   XXX",

            "§6YXXXXXXXXb XXXXXXXXXXX XXX     XXX XXXXXXXXXP    §cXXXXXXXXXXb XXX     XXX   XXX",

            "       §6XXX XXX     XXX XXX     XXX XXX           §cXXX     XXX XXX     XXX   XXX",

            "       §6XXX XXX     XXX XXX     XXX XXX           §cXXX     XXX XXX     XXX   XXX",

            "§6YXXXXXXXXP YXP     YXP YXXXXXXXXXP YXP           §cYXXXXXXXXXP YXXXXXXXXXP dXXXXXb",

            "  "

        ];

    }

}
