<?php
/**
 * @copyright 2023-2026 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);

namespace Web;

use Google\Service\Calendar\Events;

class HomeController extends Controller
{
    public function __invoke(array $params): View
    {
        if (!empty($_GET['date'])) {
            try {
                $start = new \DateTime($_GET['date']);
            }
            catch (\Exception $e) {
                $start = new \DateTime('midnight');
            }
        }
        else {
            $start = new \DateTime('midnight');
        }

        $end = clone($start);
        $end->add(new \DateInterval('P1W'));
        $res = GoogleGateway::events(GOOGLE_CALENDAR_ID, $start, $end);

        return new Views\HomeView($res['events'], $start);
    }
}
