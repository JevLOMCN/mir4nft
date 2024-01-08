<?php

namespace RPurinton\Mir4nft;

class DealText
{
    public static function get(int|float $pct): string
    {
        return match (true) {
            $pct >= 50 => "Insane Steal! 🔥",
            $pct >= 40 => "Amazing Deal!",
            $pct >= 30 => "Great Find!",
            $pct >= 20 => "Good Value!",
            $pct >= 10 => "Fair Offer",
            $pct >= 5 => "Slightly Undervalued",
            $pct >= 1 => "Just About Right",
            $pct > -10 => "Slightly Overpriced",
            $pct > -20 => "Bit Costly...",
            $pct > -30 => "Not Worth It.",
            $pct <= -30 => "Total Ripoff! 😡",
            default => "Unknown"
        };
    }

    public static function color(int|float $pct): string
    {
        return match (true) {
            $pct >= 50 => "00ff00", // Bright Green for Insane Steal
            $pct >= 40 => "33cc33", // Greener for Amazing Deal
            $pct >= 30 => "66cc66", // Lighter Green for Great Find
            $pct >= 20 => "99cc99", // Soft Green for Good Value
            $pct >= 10 => "ccccff", // Light Blue for Fair Offer
            $pct >= 5 => "9999ff", // Blue for Slightly Undervalued
            $pct >= 1 => "6666ff", // Darker Blue for Just About Right
            $pct > -10 => "ffcc00", // Yellow for Slightly Overpriced
            $pct > -20 => "ff9900", // Orange for Bit Costly
            $pct > -30 => "ff6600", // Dark Orange for Not Worth It
            $pct <= -30 => "ff0000", // Red for Total Ripoff
            default => "808080", // Grey for anything else (neutral)
        };
    }
}
