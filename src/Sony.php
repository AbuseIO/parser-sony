<?php

namespace AbuseIO\Parsers;

use AbuseIO\Models\Incident;
use Illuminate\Support\Facades\Log;

class Sony extends ParserBase
{
    /**
     * Parse body
     * @return array    Returns array with failed or success data
     *                  (See parser-common/src/Parser.php) for more info.
     */
    public function parse()
    {
        /**
         *  There is no attached report, the information is all in the mail body
         */
        $this->feedName = 'abuse';
        $body = $this->parsedMail->getMessageBody();
        $subject = $this->parsedMail->getHeader('subject');
        $reports = [];
        $knownAndEnabledFeed = $this->isKnownFeed() && $this->isEnabledFeed();
        if (!$knownAndEnabledFeed) {
            return $this->success();
        }
        $blacklisted = strpos($subject, 'were blacklisted from the PlayStation Network');
        if ($blacklisted === false){
            return $this->success();
        }
        $regex = '/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) ~ '
            . '(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) \(UTC\),\s+'
            . '([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}),\s+'
            . '(.+)/';
        $bodyLines = explode("\n",$body);
        foreach($bodyLines as $line){
            $match = preg_match($regex, $line, $matches);
            if (!$match) {
                continue;
            }
            $report = [
                'Source-IP' => $matches[3],
                'Abuse-Type' => $matches[4],
                'Abuse-Date' => $matches[1],
                'Abuse-Text' => config("{$this->configBase}.parser.abuse-text")                          
            ];
            $reports[] = $report;
        }

        foreach($reports as $report){
            if ($this->hasRequiredFields($report) !== true) {
                continue;
            }
            $report = $this->applyFilters($report);
            $incident = new Incident();
            $incident->source      = config("{$this->configBase}.parser.name");
            $incident->source_id   =false;
            $incident->ip          =$report['Source-IP'];
            $incident->domain      =false;
            $incident->class       =config("{$this->configBase}.feeds.{$this->feedName}.class");
            $incident->type        =config("{$this->configBase}.feeds.{$this->feedName}.type");
            $incident->timestamp   =strtotime($report['Abuse-Date']);
            $incident->information =json_encode($report);
            $this->incidents[] = $incident;
        }
        return $this->success();
    }
}
