<?php

function map_data($api_data)
{
    $records = [];
    foreach($api_data as $data)
    {
        $record["name"] = $data["title"];
        $record["rank"] = $data["rank"];
        $record["score"] = $data["score"];
        $record["picture"] = $data["picture_url"];
        array_push($records, $record);
    }
    return $records;
}