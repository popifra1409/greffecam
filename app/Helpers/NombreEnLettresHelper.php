<?php

namespace App\Helpers;

class NombreEnLettresHelper
{
    protected static array $unites = [
        '',
        'un',
        'deux',
        'trois',
        'quatre',
        'cinq',
        'six',
        'sept',
        'huit',
        'neuf',
        'dix',
        'onze',
        'douze',
        'treize',
        'quatorze',
        'quinze',
        'seize',
        'dix-sept',
        'dix-huit',
        'dix-neuf',
    ];

    protected static array $dizaines = [
        0 => '',
        2 => 'vingt',
        3 => 'trente',
        4 => 'quarante',
        5 => 'cinquante',
        6 => 'soixante',
        7 => 'soixante',
        8 => 'quatre-vingt',
        9 => 'quatre-vingt',
    ];

    public static function convertir(int $nombre): string
    {
        if ($nombre === 0) {
            return 'zéro';
        }

        if ($nombre < 0) {
            return 'moins ' . self::convertir(abs($nombre));
        }

        if ($nombre < 1_000_000_000) {
            return trim(self::convertirMoinsDUnMilliard($nombre));
        }

        $milliards = intdiv($nombre, 1_000_000_000);
        $reste = $nombre % 1_000_000_000;

        $texte = self::convertirMoinsDUnMilliard($milliards) . ' milliard' . ($milliards > 1 ? 's' : '');

        if ($reste > 0) {
            $texte .= ' ' . self::convertirMoinsDUnMilliard($reste);
        }

        return trim($texte);
    }

    protected static function convertirMoinsDUnMilliard(int $nombre): string
    {
        if ($nombre >= 1_000_000) {
            $millions = intdiv($nombre, 1_000_000);
            $reste = $nombre % 1_000_000;

            $texte = ($millions === 1 ? 'un million' : self::convertirMoinsDUnMillion($millions) . ' millions');

            if ($reste > 0) {
                $texte .= ' ' . self::convertirMoinsDUnMillion($reste);
            }

            return $texte;
        }

        return self::convertirMoinsDUnMillion($nombre);
    }

    protected static function convertirMoinsDUnMillion(int $nombre): string
    {
        if ($nombre >= 1000) {
            $milliers = intdiv($nombre, 1000);
            $reste = $nombre % 1000;

            $texte = ($milliers === 1 ? 'mille' : self::convertirMoinsDeMille($milliers) . ' mille');

            if ($reste > 0) {
                $texte .= ' ' . self::convertirMoinsDeMille($reste);
            }

            return $texte;
        }

        return self::convertirMoinsDeMille($nombre);
    }

    protected static function convertirMoinsDeMille(int $nombre): string
    {
        if ($nombre >= 100) {
            $centaines = intdiv($nombre, 100);
            $reste = $nombre % 100;

            $texte = ($centaines === 1 ? 'cent' : self::$unites[$centaines] . ' cent');

            if ($reste === 0 && $centaines > 1) {
                $texte .= 's';
            }

            if ($reste > 0) {
                $texte .= ' ' . self::convertirMoinsDeCent($reste);
            }

            return $texte;
        }

        return self::convertirMoinsDeCent($nombre);
    }

    protected static function convertirMoinsDeCent(int $nombre): string
    {
        if ($nombre < 20) {
            return self::$unites[$nombre];
        }

        $dizaine = intdiv($nombre, 10);
        $unite = $nombre % 10;

        // Cas particuliers 70-79 et 90-99 (base soixante/quatre-vingt + dix-neuf)
        if ($dizaine === 7 || $dizaine === 9) {
            return self::$dizaines[$dizaine] . '-' . self::$unites[10 + $unite];
        }

        $texte = self::$dizaines[$dizaine];

        if ($unite === 1 && in_array($dizaine, [2, 3, 4, 5, 6])) {
            $texte .= ' et un';
        } elseif ($unite > 0) {
            $texte .= '-' . self::$unites[$unite];
        } elseif ($dizaine === 8) {
            $texte .= 's'; // quatre-vingts
        }

        return $texte;
    }
}
