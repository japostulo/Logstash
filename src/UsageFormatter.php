<?php


namespace HAOC\Logstash;


use Monolog\Formatter\NormalizerFormatter;


class UsageFormatter extends NormalizerFormatter
{
    const VERSION = 1;
    public $index = '';

    /**

     * @param string      $applicationName The application that sends the data, used as the "type" field of logstash
     * @param string|null $systemName      The system/machine name, used as the "source" field of logstash, defaults to the hostname of the machine
     */

    public function __construct(public string $applicationName = '', public string $host = '')
    {
        // logstash requires a ISO 8601 format date with optional millisecond precision.
        parent::__construct('Y-m-d\TH:i:s.uP');
    }

    /**
     * {@inheritdoc}
     */

    public function format(array $record): string
    {
        $record = parent::format($record);

        $this->setLogProperties($record, $message);

        $this->setLevelProperties($record, $message);

        if (!empty($record['extra'])) $message[$this->extraKey] = $record['extra'];

        $this->setContext($record['context'], $message);
        $message['index'] = $this->getIndex();

        return $this->toJson($message) . "\n";
    }

    private function setLevelProperties($record, &$message)
    {
        if ($record['level'] != 500) unset($message['exception']);

        $message['level'] = [
            'code' => $record['level'],
            'type' => $record['level_name'],
        ];
    }

    private function setLogProperties($record, &$message): void
    {
        $message['@timestamp'] = $record['datetime'];
        $message['@version'] = self::VERSION;
        $message['application'] = $this->applicationName;
        $message['host'] = $this->host;
        $message['channel'] = $record['channel'];
        $message['message'] = $record['message'] ?? null;
    }

    private function setContext(array $context, array &$message): void
    {
        if (!is_array($context)) return;

        foreach ($context as $key => $value) {
            if ($value) $message[$key] = $value;
        }
    }

    public function getIndex()
    {
        return strlen($this->index) ? $this->index : env('LOGSTASH_INDEX', 'log') . '_' . env('APP_ENV', 'local');
    }
}
