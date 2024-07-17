<?php

function isValidTime(string $time): bool
{
    return preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $time);
}

function validateCoordLat($lat): int
{
    return preg_match('/^-?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $lat);
}

function validateCoordLng($lng): int
{
    return preg_match('/^-?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', $lng);
}