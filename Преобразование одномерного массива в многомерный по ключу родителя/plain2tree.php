<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


function restore_tree($data)
{
    $lst = prepare_parents($data);

    foreach ($lst as &$parent) {
        restore_tree_impl($parent, $lst);
    }
    return $lst[null];
}


function restore_tree_impl(&$parent, &$data) {
    foreach ($parent['child'] as &$child) {
        // Если текущий ребенок сам является родителем
        if (array_key_exists($child['id'], $data)) {
            // восстановить связь
            $child['child'] = $data[$child['id']]['child'];
            // проверить детей
            restore_tree_impl($child, $data);
        } else {
         //   $child['child'] = [];
        }
    }
}
function make($id) {
    return ['id' => $id, 'title' => '[root]', 'child' => []];
}

function prepare_parents($data) {
    $res = [];
    foreach ($data as $item) {
        $parent = $item['parent'];
        if (!array_key_exists($parent, $res)) {
            $res[$parent] = make($parent);
            if (array_key_exists($parent, $data)) {
                $res[$parent]['title'] = $data[$parent]['title'];
            }
        }
        unset($item['parent']);
        $res[$parent]['child'][$item['id']] = $item;
    }
    $data = $res;
    return $data;
}


$intArray = array(
    0 => array(
        "id" => 2,
        "parent" => 4
    ),
    1 => array(
        "id" => 1
    ),
    2 => array(
        "id" => 4,
        "parent" => 1
    ),
    3 => array(
        "id" => 6,
        "parent" => 1
    ),
    4 => array(
        "id" => 14,
        "parent" => 4
    )
);
$root = restore_tree($intArray);



echo "<pre>";
print_r($root);
echo "</pre>";
?>