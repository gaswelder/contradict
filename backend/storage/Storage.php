<?php

interface Storage
{
    /**
     * How many correct answers needed for an entry to be "finished".
     */
    const GOAL = 10;

    /**
     * How many entries are in the "learning pool".
     * This limit is applied separately to both directions,
     * so the actual pool limit is twice this value.
     */
    const WINDOW = 200;

    function dicts(): array;
    function dict(string $id): Dict;
    function dictStats(string $dict_id): Stats;

    function lastScores(string $dict_id): array;

    /**
     * Generates a test for a given dictionary.
     */
    function test(string $dict_id): Test;
    function entry(string $id): Entry;
    function saveEntry(Entry $e);
    function entries(array $ids): array;
}
