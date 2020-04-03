<?php

/**
 * Mapping for video object documents
 *
 * @author emi
 */

namespace Minds\Core\Search\Mappings;

class ObjectVideoMapping extends EntityMapping implements MappingInterface
{
    /**
     * ObjectVideoMapping constructor.
     */
    public function __construct()
    {
        $this->mappings = array_merge($this->mappings, [
            'license' => ['type' => 'text', '$exportField' => 'license'],
            'rating' => ['type' => 'integer', '$exportField' => 'rating'],
            'youtube_id' => ['type' => 'string', '$exportField' => 'youtube_id'],
            'youtube_channel_id' => ['type' => 'string', '$exportField' => 'youtube_channel_id'],
            'transcoding_status' => ['type' => 'string', '$exportField' => 'transcoding_status'],
            'time_transcoded' => ['type' => 'date'],
        ]);
    }

    /**
     * @param array $defaultValues
     * @return array
     * @throws \Exception
     */
    public function map(array $defaultValues = [])
    {
        $map = parent::map($defaultValues);

        $map['time_transcoded'] = $this->entity->getTimeTranscoded() * 1000;

        return $map;
    }
}
