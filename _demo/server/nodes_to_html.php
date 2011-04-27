<?php

class NodesToHTML {
    private static function generateHTML(&$node, &$tree) {
        // is this the root node?
        if (count($node) == 1)
            $html = "";
        else
            $html = "<li rel='{$node["type"]}' id='node_{$node["id"]}'><ins class='jstree-icon'>&#160;</ins><a href=''><ins class='jstree-icon'>&#160;</ins>{$node["title"]}</a><span>{$node["hint"]}</span>";

        // check whether node has children
        $children = $tree[$node["id"]];
        if (count($children) == 0)
            $html .= "</li>";
        else {
            $html .= "<ul>";
            foreach ($children as $child) {
                $html .= static::generateHTML($child, $tree);
            }
            $html .= "</ul></li>";
        }

        return $html;
    }

    private static function childrenToHTML(&$children, $parent_id) {
        $root = array("id" => $parent_id);
        $tree = array($parent_id => array());

        foreach ($children as &$node) {
            $tree[$node["id"]] = array();
        }
       
        foreach ($children as &$node) {
            // has this node a parent node?
            if (isset($tree[$node["parent_id"]]))
                $tree[$node["parent_id"]][] = $node;
        }

        return static::generateHTML($root, $tree);
    }

    public static function toHTML($nodes) {
        $html = array();
        foreach ($nodes as $id => &$children) {
            $html[$id] = static::childrenToHTML($children, $id);
        }

        return $html;
    }
}

?>
