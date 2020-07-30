<?php
/**
 * Permaweb Save Delegate
 * @author Ben Hayward
 */
namespace Minds\Core\Permaweb\Delegates;

use Minds\Core\Di\Di;

class SaveDelegate
{
    private $manager;
    private $text = '';
    private $userGuid = '';
    private $thumbnailSrc = '';
    private $mindsLink = '';

    public function __construct($manager = null)
    {
        $this->manager = $manager ?: Di::_()->get('Permaweb\Manager');
    }

    /**
     * Sets string data
     * @param string $data - e.g. hello world this is my post.
     * @return SaveDelegate - chainable
     */
    public function setText(string $text): SaveDelegate
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Sets thumbnail src.
     * @param string $url - thumbnail_src url
     * @return SaveDelegate - chainable.
     */
    public function setThumbnailSrc($url): SaveDelegate
    {
        $this->thumbnailSrc = $url;
        return $this;
    }

    /**
     * Sets user guid
     * @param string $guid - user guid
     * @return SaveDelegate - chainable
     */
    public function setUserGuid(string $guid): SaveDelegate
    {
        $this->userGuid = $guid;
        return $this;
    }

    /**
     * Sets link back to minds content
     * @param string $url - link back to content
     * @return SaveDelegate
     */
    public function setMindsLink(string $url): SaveDelegate
    {
        $this->mindsLink = $url;
        return $this;
    }
    
    /**
     * Assembles opts from class level variables.
     * @return array
     */
    private function assembleOpts(): array
    {
        return [
            'text' => $this->text,
            'guid' => $this->userGuid,
            'thumbnail_src' => $this->thumbnailSrc,
            'minds_link' => $this->mindsLink,
        ];
    }

    /**
     * Dispatch save call.
     */
    public function dispatch(): void
    {
        $this->manager->save(
            $this->assembleOpts()
        );
    }
}
