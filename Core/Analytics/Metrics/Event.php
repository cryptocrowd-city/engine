<?php

namespace Minds\Core\Analytics\Metrics;

use Minds\Core;

/**
 * Class Event.
 *
 * @method Event setType($value)
 * @method Event setAction($value)
 * @method Event setProduct($value)
 * @method Event setUserPhoneNumberHash($value)
 * @method Event setEntityGuid($value)
 * @method Event setEntityContainerGuid($value)
 * @method Event setEntityAccessId($value)
 * @method Event setEntityType($value)
 * @method Event setEntitySubtype($value)
 * @method Event setEntityOwnerGuid($value)
 * @method Event setCommentGuid($value)
 * @method Event setRatelimitKey($value)
 * @method Event setRatelimitPeriod($value)
 * @method Event setPlatform($value)
 * @method Event setEmailCampaign($value)
 * @method Event setEmailTopic($topic)
 * @method Event setEmailState($state)
 */
class Event
{
    /** @var Core\Data\ElasticSearch\Client */
    private $elastic;
    private $index = 'minds-metrics-';
    protected $data;

    public function __construct($elastic = null)
    {
        $this->elastic = $elastic ?: Core\Di\Di::_()->get('Database\ElasticSearch');
        $this->index = 'minds-metrics-'.date('m-Y', time());
    }

    public function setUserGuid($guid)
    {
        $this->data['user_guid'] = (string) $guid;
        return $this;
    }

    public function setTimestamp(int $timestamp)
    {
        $this->data['@timestamp'] = $timestamp;
        return $this;
    }

    public function push()
    {
        if (!isset($this->data['@timestamp'])) {
            $this->data['@timestamp'] = (int)microtime(true) * 1000;
        }

        $this->data['user_agent'] = $this->getUserAgent();
        $this->data['ip_hash'] = $this->getIpHash();
        $this->data['ip_range_hash'] = $this->getIpRangeHash();

        if (isset($_SERVER['HTTP_APP_VERSION'])) {
            $this->data['mobile_version'] = $_SERVER['HTTP_APP_VERSION'];
        }

        if (!isset($this->data['platform'])) {
            $platform = isset($_REQUEST['cb']) ? 'mobile' : 'browser';
            if (isset($_REQUEST['platform'])) { //will be the sole method once mobile supports
                $platform = $_REQUEST['platform'];
            }
            $this->data['platform'] = $platform;
        }

        $query = [
            'body' => $this->data,
            'index' => $this->index,
            'type' => $this->data['type']
        ];

        /* Generate index for active days so we can use delete API in testing */
        if ($this->data['action'] === 'active') {
            $query['id'] = $this->getId();
        }

        $prepared = new Core\Data\ElasticSearch\Prepared\Index();
        $prepared->query($query);

        try {
            return $this->elastic->request($prepared);
        } catch (\Exception $e) {
            //TODO: Should we be silently suppressing this exception???
        }
    }

    /**
     * Magic method for getter and setters.
     */
    public function __call($name, array $args = [])
    {
        if (strpos($name, 'set', 0) === 0) {
            $attribute = str_replace('set', '', $name);
            $attribute = implode('_', preg_split('/([\s])?(?=[A-Z])/', $attribute, -1, PREG_SPLIT_NO_EMPTY));
            $attribute = strtolower($attribute);
            $this->data[$attribute] = $args[0];

            return $this;
        }
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * For security, record the user agent.
     *
     * @return string
     */
    protected function getUserAgent()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }

        return '';
    }

    protected function getIpHash()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return hash('sha256', $_SERVER['HTTP_X_FORWARDED_FOR']);
        }

        return '';
    }

    protected function getIpRangeHash()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode('.', $_SERVER['HTTP_X_FORWARDED_FOR']);
            array_pop($parts);

            return hash('sha256', implode('.', $parts));
        }

        return '';
    }

    public function delete(): bool
    {
        $query = [
            'index' => $this->index,
            'type' => $this->data['type'],
            'id' => $this->getId()
        ];
        $prepared = new Core\Data\ElasticSearch\Prepared\Delete();
        $prepared->query($query);

        try {
            $this->elastic->request($prepared);
        } catch (\Exception $e) {
            error_log('Error deleting: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    public function getId(): string
    {
        $uniques = [
            $this->data['type'],
            $this->data['@timestamp'],
            $this->data['user_guid']
        ];
        return implode('-', $uniques);
    }
}
