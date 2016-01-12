<?php

namespace Bigfoot\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MediaMetadata
 *
 * @ORM\Table(name="portfolio_media_metadata")
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\MediaBundle\Entity\MediaMetadataRepository")
 */
class MediaMetadata
{
    /**
     * @var Media
     *
     * @ORM\ManyToOne(targetEntity="Media", inversedBy="metadatas", fetch="EAGER")
     * @ORM\Id
     */
    private $media;

    /**
     * @var Metadata
     *
     * @ORM\ManyToOne(targetEntity="Metadata", inversedBy="mediaMetadata", fetch="EAGER")
     * @ORM\Id
     */
    private $metadata;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Returns the metadata type (name of underlying metadata object)
     *
     * @return string
     */
    public function getType()
    {
        return $this->metadata->getName();
    }

    /**
     * Get media
     *
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param Media $media
     * @return MediaMetadata
     */
    public function setMedia(Media $media)
    {
        $this->media = $media;
    
        return $this;
    }

    /**
     * Get metadata
     *
     * @return Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set metadata
     *
     * @param Metadata $metadata
     * @return MediaMetadata
     */
    public function setMetadata(Metadata $metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set metadata
     *
     * @param string $value
     * @return MediaMetadata
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
