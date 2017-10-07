<?php

namespace AbuseIO\Parsers;

use AbuseIO\Models\Incident;
use Illuminate\Support\Facades\Log;
/**
 * Class Sony
 * @package AbuseIO\Parsers
 */
class Sony extends Parser
{
    /**
     * Create a new Sony instance
     *
     * @param \PhpMimeMailParser\Parser $parsedMail phpMimeParser object
     * @param array $arfMail array with ARF detected results
     */
    public function __construct($parsedMail, $arfMail)
    {
        parent::__construct($parsedMail, $arfMail, $this);
    }

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
        if ($this->isKnownFeed() && $this->isEnabledFeed()) {
            if (strpos($subject, 'were blacklisted from the PlayStation Network') !== false){
                $bodyLines = explode("\n",$body);
                foreach($bodyLines as $line){
                    if (preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}) ~ (\d{4}-\d{2}-\d{2} \d{2}:\d{2}) \(UTC\),\s+([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}),\s+(.+)/',
                        $line,
                        $matches)){
                        $report = [
                            'Source-IP' => $matches[3],
                            'Abuse-Type' => $matches[4],
                            'Abuse-Date' => $matches[1],
                            'Abuse-Text' => config("{$this->configBase}.parser.abuse-text")                          
                        ];
                        $reports[] = $report;
                    }    
                }
                    
            }
            foreach($reports as $report){
                if ($this->hasRequiredFields($report) === true) {
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
            }
        }
        return $this->success();
    }
}
