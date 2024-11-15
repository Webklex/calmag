<?php
/*
* File: additives.php
* Category: -
* Author: M.Goldenbaum
* Created: 08.11.24 19:51
* Updated: -
*
* Description:
*  -
*/

return [
    "calcium" => [
        "Canna Mono Calcium"   => [
            // Canna Mono Calcium 12% CaO
            "elements"      => [
                "calcium" => [
                    "CaO" => 12.0, // %
                ],
            ],
            "concentration" => 100.0, // %
            "density"       => 1.283, // g/cm³
        ],
        // Ca(C₂H₃O₂)₂ · H₂O = Calciumacetat (Calciumacetat-hydrat)
        "CaC2H3O2H2O"            => [
            // Calciumacetat
            "elements"      => [
                "calcium" => 22.7, // %
            ],
            "concentration" => 1.5, // %
            "density"       => 1.0, // g/cm³
        ],
        // Ca₃(C₆H₅O₇)₂ · 4H₂O = Calciumcitrat (Calciumcitrat-tetrahydrat)
        "Ca3C6H5O74H2O"          => [
            // Calciumcitrat
            "elements"      => [
                "calcium" => 21.1, // %
            ],
            "concentration" => 1.5, // %
            "density"       => 1.0, // g/cm³
        ],
    ],
    "magnesium" => [
        "Canna Mono Magnesium"   => [
            // Canna Mono Magnesium 7% MgO
            "elements"      => [
                "magnesium" => [
                    "MgO" => 7.0, // %
                ],
            ],
            "concentration" => 100.0, // %
            "density"       => 1.23, // g/cm³
        ],
        "MgSO4"        => [
            // Magnesiumsulfat
            "elements"      => [
                "magnesium" => [
                    "MgO" => 16.0, // %
                ],
                "sulfur"    => 13.0, // %
            ],
            "concentration" => 2.0, // %
        ],
        "C6H6MgO7"     => [
            // Magnesiumhydrogencitrat
            "elements"      => [
                "magnesium" => [
                    "MgO" => 11.0, // %
                ],
            ],
            "concentration" => 3.0, // %
        ],
        "C12H10Mg3O14" => [
            // Trimagnesiumdicitrat
            "elements"      => [
                "magnesium" => 16.0, // %
            ],
            "concentration" => 2.0, // %
        ],
        "Canna Mono Magnesium"   => [
            // Canna Mono Magnesium 7% MgO
            "elements"      => [
                "magnesium" => [
                    "MgO" => 7.0, // %
                ],
            ],
            "concentration" => 100.0, // %
            "density"       => 1.23, // g/cm³
        ],
    ],
];