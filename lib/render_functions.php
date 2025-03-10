<?php

function render_input($data = array())
{
    include(__dir__ . "/../partials/input_field.php");
}

function render_button($data = array())
{
    include(__DIR__ . "/../partials/button.php");
}

function render_table($data = array())
{
    include(__DIR__ . "/../partials/table.php");
}

function render_anime_card($anime = array())
{
    include(__DIR__ . "/../partials/anime_card.php");
}

function render_result_counts($result_count, $total_count)
{
    include(__DIR__ . "/../partials/result_counts.php");
}
