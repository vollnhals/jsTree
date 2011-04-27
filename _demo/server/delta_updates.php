<?php

// TODO: rework jstree _demo code includes and DB access
require_once("config.php");
require_once("nodes_to_html.php");

class DeltaUpdates {
    const table_name = 'delta_updates';

    function __construct($db, $seq_nr = 0) {
        $this->db = $db;
        $this->seq_nr = (int)$seq_nr;
    }

    public function recordChange($parent_id) {
        $query = $this->db->prepare("INSERT INTO " . self::table_name . " (node_id) VALUES (?)");
        $query->execute(array((int)$parent_id));
    }

    private function changedParentIds() {
        // TODO: implement as described below or use queuing mechanism (zeromq, memcacheq)
        // TODO: transaction for all db requests?
        $parent_ids = array();

        $query = $this->db->prepare("SELECT id, node_id FROM " . self::table_name . " WHERE id > ? ORDER BY id ASC");
        if ($query->execute(array($this->seq_nr))) {
            while ($row = $query->fetch()) {
                $node_id = (int)$row["node_id"];
                if (!in_array($node_id, $parent_ids))
                    $parent_ids[] = $node_id;

                // set seq_nr to seq_nr of processed change
                $this->seq_nr = $row["id"];
            }
        }

        return $parent_ids;
    }

    // returns an associative array of parent node id keys and children node values, that where involved in a recent change
    // TODO: optimization, only get immediate children and replace partially in client
    public function changedNodes() {
        $jstree = new json_tree();
        $parent_ids = $this->changedParentIds();

        $parent_nodes = array(); 
        foreach ($parent_ids as $parent_id) {
            $node = $jstree->_get_node($parent_id);
            $parent_nodes[(int)$node["left"]] = $node;
        }
        // sort after left attribute
        ksort($parent_nodes);

        // get changed nodes, discarding already included parents
        $changed_nodes = array();
        $right = 0;
        foreach ($parent_nodes as $parent) {
            if ($parent["left"] > $right) {
                $changed_nodes[$parent["id"]] = $jstree->_get_children($parent["id"], true);
                $right = $parent["right"];
            }
        }

        return $changed_nodes;
    }

    public function encodedDeltaUpdate() {
        $changed_notes = $this->changedNodes();
        if (empty($changed_notes))
            return "";

        $result = array(
            'nodes' => NodesToHTML::toHTML($changed_notes),
            'seq_nr' => $this->seq_nr,
        );

        return json_encode($result);
    }
}

?>
