<?php

namespace Taco\RU;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use pocketmine\{event\Listener, event\player\PlayerJoinEvent, event\player\PlayerPreLoginEvent, plugin\Plugin, plugin\PluginBase, utils\Config};
use Taco\RU\commands\RankUp;

class Main extends PluginBase implements Listener {

    /**
     * @var array
     */
    public $config;

    /**
     * @var Config
     */
    public $ranks;

    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var BedrockEconomy $economy
     */
    public $economy;

    public function onEnable(): void {
        self::$instance = $this;
        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->config = $this->getConfig()->getAll();
        $this->ranks = new Config($this->getDataFolder() . "ranks.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Initialize Bedrock Economy
        $bedrockEconomy = $this->getServer()->getPluginManager()->getPlugin("BedrockEconomy");
        if ($bedrockEconomy instanceof BedrockEconomy) {
            $this->economy = $bedrockEconomy;
        } else {
            $this->getLogger()->error("You must have Bedrock Economy by CoolDoge installed to use this plugin!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

        $this->getServer()->getCommandMap()->register("RankUp", new RankUp($this));
    }

    /**
     * @return self
     */
    public static function getInstance(): self {
        return self::$instance;
    }

    /**
     * @return API
     */
    public function getAPI(): API {
        return new API();
    }

    /**
     * @param PlayerPreLoginEvent $event
     */
    public function preJoin(PlayerPreLoginEvent $event): void {
        $player = $event->getPlayer();
        if (!$this->ranks->exists($player->getName())) {
            $this->ranks->set($player->getName(), $this->config["default-rank"]);
            $this->ranks->save();
        }
    }
}
