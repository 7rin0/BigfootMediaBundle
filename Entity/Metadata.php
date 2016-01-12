<?php

namespace Bigfoot\Bundle\MediaBundle\Entity;

use Bigfoot\Bundle\MediaBundle\Entity\Translation\MetadataTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Metadata
 *
 * @Gedmo\TranslationEntity(class="Bigfoot\Bundle\MediaBundle\Entity\Translation\MetadataTranslation")
 * @ORM\Table(name="portfolio_metadata")
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\MediaBundle\Entity\MetadataRepository")
 */
class Metadata
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=true, unique=true)
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var MediaMetadata
     *
     * @ORM\OneToMany(targetEntity="MediaMetadata", mappedBy="metadata", cascade={"persist", "remove"})
     */
    private $mediaMetadata;

    /**
     * @Gedmo\Locale
     */
    private $locale;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Bigfoot\Bundle\MediaBundle\Entity\Translation\MetadataTranslation",
     *   mappedBy="object",
     *   cascade={"persist", "remove"}
     * )
     */
    private $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Metadata
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Metadata
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param \Bigfoot\Bundle\MediaBundle\Entity\MediaMetadata $mediaMetadata
     */
    public function setMediaMetadata($mediaMetadata)
    {
        $this->mediaMetadata = $mediaMetadata;

        return $this;
    }

    /**
     * @return \Bigfoot\Bundle\MediaBundle\Entity\MediaMetadata
     */
    public function getMediaMetadata()
    {
        return $this->mediaMetadata;
    }

    /**
     * @param $locale The object's locale.
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param MetadataTranslation $t
     */
    public function addTranslation(MetadataTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }
}
