<?php

declare(strict_types=1);

namespace App\Controller;

use Exception;

class ProgramController
{
    private static array $config = [];
    private array $list;
    private array $tree;

    public function __construct($config)
    {
        self::$config = $config;

        if (!file_exists(self::$config['list'])) {
            throw new Exception("Error: file list.json not found");
        } else if (!$this->isJson(file_get_contents(self::$config['list']))) {
            throw new Exception("Error: file list.json is not in json format");
        }
        $this->list = $this->newListData(json_decode(file_get_contents(self::$config['list']), true));

        if (!file_exists(self::$config['tree'])) {
            throw new Exception('Error: file tree.json not found');
        } else if (!$this->isJson(file_get_contents(self::$config['tree']))) {
            throw new Exception("Error: file tree.json is not in json format");
        }
        $this->tree = json_decode(file_get_contents(self::$config['tree']), true);
    }

    private function isJson($file): bool
    {
        json_decode($file);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function newListData($list)
    {
        foreach ($list as $key => $value) {
            if (!array_key_exists('category_id', $list[$key]) || !array_key_exists('name', $list[$key]['translations']['pl_PL'])) {
                throw new Exception('Error: wrong list.json file sctructure. Problematic key: ' . $key);
            }
            $newList[$key]['category_id'] = $value['category_id'];
            $newList[$key]['name'] = $value['translations']['pl_PL']['name'];
        }
        return $newList;
    }

    private function searchTree($tree, $searchValue, $name, $id_path = [])
    {
        if (is_array($tree) && count($tree) > 0) {
            foreach ($tree as $key => $leaf) {
                $path = $id_path;
                array_push($path, $key);

                if (is_array($leaf) && count($leaf) > 0) {
                    $this->searchTree($leaf, $searchValue, $name, $path);
                } else if ($key == 'id' && $leaf == $searchValue) {
                    array_pop($path);
                    $this->updateLeaf($path, $name);
                }
            }
        }
    }

    private function updateLeaf($path, $name)
    {
        $tempTree = &$this->tree;

        foreach ($path as $pathIndex) {
            $tempTree = &$tempTree[$pathIndex];
        }

        $removedElements = array_splice($tempTree, 1);
        $tempTree += ["name" => $name] + $removedElements;
        unset($tempTree);
    }

    public function update_tree()
    {
        foreach ($this->list as $data) {
            $this->searchTree($this->tree, $data['category_id'], $data['name']);
        }
        file_put_contents('data\tree_' . date('m-d-Y_h-i-a') . '.json', json_encode($this->tree));
    }
}
