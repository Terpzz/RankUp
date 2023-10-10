<?php

namespace Taco\RU;

use pocketmine\{command\ConsoleCommandSender, player\Player};

class API {

    /**
     * @param Player $player
     * @return string
     *
     * Return the current rank of the player
     */
    public function getRank(Player $player) : string {
        return Main::getInstance()->ranks->get($player->getName());
    }

    /**
     * @param Player $player
     * @param string $new
     *
     * Changes the current rank of the player
     */
    public function setRank(Player $player, string $new) : void {
        Main::getInstance()->ranks->set($player->getName(), $new);
        Main::getInstance()->ranks->save();
    }

    /**
     * @param Player $player
     * @return string
     *
     * Return the player's next rank
     */
    public function getNextRank(Player $player) : string {
        $current = $this->getRank($player);
        $next = false;
        foreach(Main::getInstance()->config["ranks"] as $rank => $i) {
            if ($next) return $rank;
            if ($rank == $current) $next = true;
        }
        return "";
    }

    /**
     * @param Player $player
     * @return int
     *
     * Return the player's next rank rank-up price
     */
    public function getNextRankPrice(Player $player) : int {
        $current = $this->getRank($player);
        $next = false;
        foreach(Main::getInstance()->config["ranks"] as $rank => $i) {
            if ($next) return (int)$i["price"];
            if ($rank == $current) $next = true;
        }
        return 0;
    }

    /**
     * @param Player $player
     *
     *
     */
    public function rankUp(Player $player) : void {
        $next = $this->getNextRank($player);
        $current = $this->getRank($player);
        
        if ($this->getNextRank($player) !== "") {
            $money = BedrockEconomy::getMoney($player);
            
            $nextRankPrice = $this->getNextRankPrice($player);
            
            if ($money >= $nextRankPrice) {
                foreach (Main::getInstance()->config["ranks"][$next]["commands"] as $command) {
                    $command = str_replace("{player}", $player->getName(), $command);
                    Main::getInstance()->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);
                }
                
                $message = Main::getInstance()->config["rankup-success"];
                $message = str_replace("{new-rank}", $this->getNextRank($player), $message);
                $player->sendMessage($message);
                
                BedrockEconomy::reduceMoney($player, $nextRankPrice);
                
                $this->setRank($player, $this->getNextRank($player));
            } else {
                $message = Main::getInstance()->config["rankup-fail"];
                $message = str_replace("{new-rank}", $next, $message);
                $message = str_replace("{needs-money}", ($nextRankPrice - $money), $message);
                $player->sendMessage($message);
            }
        } else {
            $player->sendMessage(Main::getInstance()->config["rankup-max"]);
        }
    }
}
