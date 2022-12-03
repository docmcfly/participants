<?php
namespace Cylancer\Participants\Utility;

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 C. Gogolin <service@cylancer.net>
 *
 * Utility class with static methods
 */
class Utility
{

    /**
     * Calculates from the counter a percentage distribution.
     *
     * @param int $presentCount
     * @param int $presentDefaultCount
     * @return array
     */
    public static function calculatePresentDatas(int $presentCount, int $presentDefaultCount): array
    {
        if ($presentDefaultCount == $presentCount) {
            $presentCountPercent = $presentCount == 0 ? 0 : 100;
            $presentOverCount = 0;
            $presentOverPercent = 0;
            $displayPercent = $presentCount == 0 ? 0 : 100;
        } else if ($presentDefaultCount > $presentCount) {
            $presentCountPercent = $presentCount * 100 / $presentDefaultCount;
            $presentOverCount = 0;
            $presentOverPercent = 0;
            $displayPercent = $presentCountPercent;
        } else {
            $presentOverCount = $presentCount - $presentDefaultCount;
            $presentCountPercent = $presentDefaultCount * 100 / $presentCount;
            $presentOverPercent = 100 - $presentCountPercent;
            $displayPercent = $presentDefaultCount == 0 ? ($presentCount == 0 ? 0 : 100) : $presentCount * 100 / $presentDefaultCount;
        }

        return [
            'presentCount' => $presentCount,
            'presentDefaultCount' => $presentDefaultCount,
            'presentOverCount' => $presentOverCount,
            'presentPercent' => round($presentCountPercent, 1),
            'presentOverPercent' => round($presentOverPercent, 1),
            'displayPercent' => round($displayPercent, 1)
        ];
    }
}
