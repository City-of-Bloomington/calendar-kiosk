<?php
/**
 * @copyright 2023 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web\Views;

use Google\Service\Calendar\Events;
use Web\View;

class HomeView extends View
{
    public function __construct(Events $events)
    {
        parent::__construct();
        
        foreach ($events as $e) {
            if ($e->start->dateTime) {
                $allDay = false;
                $eventStart = new \DateTime($e->start->dateTime);
                $eventEnd   = new \DateTime($e->end  ->dateTime);
            }
            else {
                $allDay = true;
                $eventStart = new \DateTime($e->start->date);
                $eventEnd   = new \DateTime($e->end  ->date);
            }

            $date     = $eventStart->format('Y-m-d');
            $event_id = $e->id;
            
            //$location
            $location       = preg_replace(array_keys($LOCATION_MAP), array_values($LOCATION_MAP),$e->location);
            $matches        = [];
            preg_match('/https:\/\/bloomington\.zoom\.us\/.\/\d+\?pwd=\w+/',$e->summary,$matches);
            if  ($matches) {
                $location.= " <a href=\"$matches[0]\">Join Zoom</a>";
            }

            $meetings[$date][$event_id] = [
                'eventId'  => $e->id,
                'summary'  => $e->summary,
                'location' => $e->location,
                'start'    => $eventStart->format('c'),
                'end'      => $eventEnd  ->format('c'),
                'htmlLink' => $e->htmlLink
            ];
        }
        $this->vars = ['events' => $meetings];
    }
    public function render(): string
    {
        return $this->twig->render("{$this->outputFormat}/index.twig", $this->vars);
    }
}
