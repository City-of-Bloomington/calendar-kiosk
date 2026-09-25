<?php
/**
 * @copyright 2017-2026 City of Bloomington, Indiana
 * @license http://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
namespace Web;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Channel;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\Events;

class GoogleGateway
{
    private static function getClient()
    {
        static $client = null;

        if (!$client) {
            $client = new \Google_Client();
            $client->setAuthConfig(GOOGLE_CREDENTIALS_FILE);
            $client->setScopes([\Google_Service_Calendar::CALENDAR_READONLY]);
            $client->setSubject(GOOGLE_USER_EMAIL);
        }
        return $client;
    }

    /**
     * @see https://developers.google.com/calendar/v3/reference/calendars/get
     */
    public static function calendar(string $calendar_id): Calendar\Calendar
    {
        $service = new Calendar(self::getClient());
        return $service->calendars->get($calendar_id);
    }

    /**
     * @see https://developers.google.com/calendar/v3/reference/events/get
     */
    public static function event(string $calendarId, string $eventId): Event
    {
        $service = new Calendar(self::getClient());
        return $service->events->get($calendarId, $eventId);
    }

    /**
     * @see https://developers.google.com/google-apps/calendar/v3/reference/events/list
     * @throws \Exception
     * @return array [nextSyncToken=>'', events=>[]]
     */
    public static function events(string $calendarId,
                              ?\DateTime $start=null,
                              ?\DateTime $end=null,
                                   ?bool $singleEvents=true,
                                    ?int $maxResults=null): array
    {
        $events = [];
        $FIELDS = 'description,end,endTimeUnspecified,htmlLink,id,location,'
                . 'originalStartTime,recurrence,recurringEventId,sequence,'
                . 'start,summary,attendees,organizer';

        $opts = [
            'fields'       => "items($FIELDS)",
            'singleEvents' => $singleEvents,
            'maxResults'   => $maxResults
        ];
        if ($singleEvents) { $opts['orderBy'] = 'startTime'; }

        if ($start) { $opts['timeMin'] = $start->format(\DateTime::RFC3339); }
        if ($end  ) { $opts['timeMax'] = $end  ->format(\DateTime::RFC3339); }

        return self::gatherPaginatedResults($calendarId, $opts);
    }

    /**
     * @throws \Exception
     * @return array [nextSyncToken=>'', events=>[]]
     */
    private static function gatherPaginatedResults(string $calendarId, array $opts): array
    {
        $service = new Calendar(self::getClient());
        $events  = [];
        $hasMore = true;
        while ($hasMore) {
            try {
                $res     = $service->events->listEvents($calendarId, $opts);
                $events  = array_merge($events, $res->getItems());
                $hasMore = $res->getNextPageToken() ? true : false;
                if ($hasMore) { $opts['pageToken'] = $res->getNextPageToken(); }
            }
            catch (\Google\Service\Exception $e) {
                if ($e->getCode() == 410) {
                    unset($opts['syncToken']);
                    return self::gatherPaginatedResults($calendarId, $opts);
                }
                throw $e;
            }
        }
        return [
            'nextSyncToken' => $res->getNextSyncToken(),
            'events'        => $events
        ];
    }

    public static function limitEvents(Events $events, int $maxevents): array
    {
        $display = [];
        $count   = 0;
        foreach ($events as $e) {
            if (++$count > $maxevents) { break; }

            $display[] = $e;
        }
        return $display;
    }
}
