<?php

require_once("lib/WebSocket/Application/Application.php");

class JsTreeApplication extends \Websocket\Application\Application {
    private $clients = array();
    private $seq_nr = 0;

    public function onConnect($client)
    {
        $this->clients[] = $client;
    }

    public function onDisconnect($client)
    {
        $key = array_search($client, $this->clients);
        if ($key) {
            unset($this->clients[$key]);
        }
    }

    public function onTick() {
        // TODO: implement as described below or use queuing mechanism (zeromq, memcacheq)

        // get all parent nodes that were changed since last seq_nr 
        // $this->db->query("SELECT id, parent_id FROM {$this->table} WHERE id > {$this->seq_nr}");
        $changed_parent_nodes = $this->change_notification->changed_parent_nodes();

        // get all children, TODO: überlappungen abdecken, alle nodes zusammenfassen
        $nodes = $this->tree->get_children($changed_parent_nodes

        // transform to html
        $data = $nodes->toHTML();

        // send to clients
        foreach ($this->clients as $client) {
            $client->send($data);
        }
    }

    public function onData($raw_data, $client)
    {
        // TODO
        $data = json_decode($raw_data);
        foreach ($this->clients as $sendto) {
            $sendto->send($data);
        }
    }
}

?>
