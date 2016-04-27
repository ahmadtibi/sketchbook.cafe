<?php
// Current Page Calculator
function current_page($ppage,$total)
{
    error('CURRENT_PAGE is no longer used. use SBC::currentPage() instead');

    $ppage  = isset($ppage) ? (int) $ppage : 0;
    $total  = isset($total) ? (int) $total : 0;

    // Calculate
    $pageno = floor ($total / $ppage);

    // Return
    return $pageno;
}