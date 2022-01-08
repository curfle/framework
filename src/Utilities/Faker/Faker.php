<?php

namespace Curfle\Utilities\Faker;

class Faker
{
    /**
     * Sets the seed.
     *
     * @param int $seed
     */
    public static function setSeed(int $seed)
    {
        srand($seed);
    }

    /**
     * Returns a random firstname.
     *
     * @return string
     */
    public static function firstname(): string
    {
        $names = ["Dario", "Wieland", "Felix", "Carolin", "Luna", "Niklas", "Leia", "Anna-Lena", "Johanna", "Lukas", "Charlotte", "Pauline", "Luise", "Justus", "Mathis", "Tristan", "Ben"];
        return $names[rand(0, count($names) - 1)];
    }

    /**
     * Returns a random lastname.
     *
     * @return string
     */
    public static function lastname(): string
    {
        $names = ["Larry", "Stapen", "Barrier", "Dahle", "Mandle", "Frosch", "Spuli", "Qualis", "Mikrola", "Kett", "Tensit", "Erdal", "Kaisi", "Otto", "Protma", "Laurith", "Schmaurit", "Propül"];
        return $names[rand(0, count($names) - 1)];
    }

    /**
     * Returns a random full name.
     *
     * @return string
     */
    public static function name(): string
    {
        return self::firstname() . " " . self::lastname();
    }

    /**
     * Returns a random email.
     *
     * @return string
     */
    public static function email(): string
    {
        $names = ["potassium", "sodium", "formic", "adipe", "cocamido", "carbona", "prunus", "victus", "dolciuo", "ami", "player", "dalus", "palm", "oleate", "gelbersack", "tucom"];
        $domains = ["fixmail.dd", "supermail.dd", "badmail.dd", "middlemail.dd", "littlemail.dd", "sodamail.dd", "streammail.dd"];
        return $names[rand(0, count($names) - 1)] . rand(1000, 10000) . "@" . $domains[rand(0, count($domains) - 1)];
    }
}