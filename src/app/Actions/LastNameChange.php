<?php

namespace App\Actions;

use Carbon\Carbon;

class LastNameChange
{
    public static function getFormerNameInfo($user, $daysLimit = 90)
    {
        $formerName = null;
        if ($user && $user->previous_name) {
            $lastNameChangeDate = $user->last_name_change ? Carbon::parse($user->last_name_change) : null;
            $daysPassed = $lastNameChangeDate ? $lastNameChangeDate->diffInDays(now()) : null;

            if ($daysPassed !== null && $daysPassed <= $daysLimit) {
                $formerName = $user->previous_name;
            }
        }
        return $formerName;
    }

    public static function getNameChangeStatus($lastNameChangeDate, $changeIntervalDays)
    {
        $daysPassed = $lastNameChangeDate ? Carbon::parse($lastNameChangeDate)->diffInDays(now()) : null;

        if ($daysPassed !== null && $daysPassed < $changeIntervalDays) {
            $daysRemaining = ceil($changeIntervalDays - $daysPassed); // Round up days remaining
            return "You can only change your name once every month. Please wait for $daysRemaining more day" . ($daysRemaining > 1 ? 's' : '') . ".";
        }

        return null;
    }
}
