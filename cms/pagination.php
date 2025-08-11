<?php
function showPagination($total_data, $data_per_page, $current_page, $base_url)
{
    $total_pages = ceil($total_data / $data_per_page);
    if ($total_pages <= 1) return;

    echo '<div class="pagination">';

    if ($current_page > 1) {
        echo "<a href='{$base_url}&page=" . ($current_page - 1) . "'>&laquo;</a>";
    }

    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        echo "<a class='{$active}' href='{$base_url}&page={$i}'>{$i}</a>";
    }

    if ($current_page < $total_pages) {
        echo "<a href='{$base_url}&page=" . ($current_page + 1) . "'>&raquo;</a>";
    }

    echo '</div>';
}
?>
