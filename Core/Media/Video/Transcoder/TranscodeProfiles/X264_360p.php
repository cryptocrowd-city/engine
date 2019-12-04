<?php
/**
 * 360p MP4
 */
namespace Minds\Core\Media\Video\Transcoder\TranscodeProfiles;

class X264_360p extends AbstractTranscodeProfile
{
    /** @var string */
    protected $format = 'video/mp4';

    /** @var int */
    protected $width = 640;

    /** @var int */
    protected $height = 360;

    /** @var int */
    protected $bitrate = 500;

    /** @var int */
    protected $audioBitrate = 80;

    /** @var bool */
    protected $proOnly = false;

    /** @var string */
    protected $storageName = '360p.mp4';
}
