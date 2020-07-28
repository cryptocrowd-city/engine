<?php
/**
 * Minds Permaweb Manager, interfaces with our gateway.
 * @author Ben Hayward
 */
namespace Minds\Core\Permaweb;

use Minds\Core\Di\Di;

class Manager
{
    public function __construct($http = null, $config = null, $logger = null)
    {
        $this->http = $http ?: Di::_()->get('Http');
        $this->config = $config ?: Di::_()->get('Config');
        $this->logger = $logger ?: Di::_()->get('Logger');
    }

    /**
     * Get transaction data by ID
     *
     * @param string $id - transaction id
     * @return array - response from gateway
     */
    public function getById(string $id): array
    {
        try {
            $baseUrl = $this->buildUrl($this->config->get('arweave'));
            $response = $this->http->get($baseUrl.'permaweb/'.$id);
            return (array) json_decode($response);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Save to permaweb
     *
     * @param string  $data data to save
     * @param string  $guid user guid
     * @return array - response from gateway.
     */
    public function save(string $data, string $guid): array
    {
        $data = [
            'data' => $data,
            'guid' => $guid,
        ];
        try {
            $baseUrl = $this->buildUrl($this->config->get('arweave'));
            $response = $this->http->post($baseUrl.'permaweb/', $data, [
                'headers' => [
                    'Content-Type: application/x-www-form-urlencoded',
                ]
            ]);
            return (array) json_decode($response);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Gets ID by signing tx inside service container and passing the id back.
     *
     * @param string $data
     * @param string $guid
     * @return string id
     */
    public function generateId(string $data, string $guid): string
    {
        $data = [
            'data' => $data,
            'guid' => $guid,
        ];
        try {
            $baseUrl = $this->buildUrl($this->config->get('arweave'));
            $response = json_decode($this->http->post($baseUrl.'permaweb/getId/', $data, [
                'headers' => [
                    'Content-Type: application/x-www-form-urlencoded',
                ]
            ]));

            if ($response->status !== 200) {
                throw new \Exception('An unknown error occurred getting seeded permaweb id');
            }

            return $response->id;
        } catch (\Exception $e) {
            $this->logger->error($e);
            return '';
        }
    }

    private function buildUrl($arweaveConfig): string
    {
        return 'http://'.$arweaveConfig['host'].':'.$arweaveConfig['port'].'/';
    }
}
