<?php

namespace Bigfoot\Bundle\MediaBundle\Entity;

use Bigfoot\Bundle\CoreBundle\Entity\Tag;
use Bigfoot\Bundle\MediaBundle\Entity\Translation\MediaTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Media
 *
 * @Gedmo\TranslationEntity(class="Bigfoot\Bundle\MediaBundle\Entity\Translation\MediaTranslation")
 * @ORM\Table(name="portfolio_media")
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\MediaBundle\Entity\MediaRepository")
 */
class Media
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
     * @ORM\Column(name="file", type="string", length=255)
     */
    private $file;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MediaUsage", mappedBy="mediaId", cascade={"persist", "remove"})
     */
    private $usages;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MediaMetadata", mappedBy="media", cascade={"persist", "remove"})
     */
    private $metadatas;

    /**
     * Metadatas, in an associative array slug => value to ease value retrieving
     *
     * @var array
     */
    private $sortedMetadatas;

    /**
     * @var \DateTime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="Bigfoot\Bundle\CoreBundle\Entity\Tag", cascade={"persist"})
     */
    private $tags;

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
        $this->translations    = new ArrayCollection();
        $this->usages          = new ArrayCollection();
        $this->tags            = new ArrayCollection();
        $this->metadatas       = new ArrayCollection();
        $this->sortedMetadatas = array();
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
     * Set file
     *
     * @param string $file
     * @return Media
     */
    public function setFile($file)
    {
        $this->file = $file;
    
        return $this;
    }

    /**
     * Get file
     *
     * @return string 
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Media
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get usages
     *
     * @return ArrayCollection
     */
    public function getUsages()
    {
        return $this->usages;
    }

    /**
     * Set metadatas
     *
     * @param array $metadatas
     * @return Media
     */
    public function setMetadatas($metadatas)
    {
        $this->metadatas = $metadatas;

        return $this;
    }

    /**
     * Add metadata
     *
     * @param MediaMetadata $metadata
     * @return Media
     */
    public function addMetadata(MediaMetadata $metadata)
    {
        $this->metadatas->add($metadata);

        return $this;
    }

    /**
     * Get metadatas in an associative array slug => MediaMetadata
     *
     * @return array
     */
    public function getMetadatas()
    {
        if (!count($this->sortedMetadatas)) {
            foreach ($this->metadatas as $mediaMetadada) {
                $this->sortedMetadatas[$mediaMetadada->getMetadata()->getSlug()] = $mediaMetadada;
            }
        }

        return $this->sortedMetadatas;
    }

    /**
     * Get the value of a specific metadata
     *
     * @param string Metadata slug
     * @return string Metadata value
     */
    public function getMetadata($slug)
    {
        if ($metadatas = $this->getMetadatas() and array_key_exists($slug, $metadatas)) {
            return $metadatas[$slug]->getValue();
        }

        return null;
    }

    public function resetSortedMetadatas()
    {
        $this->sortedMetadatas = array();
    }

    /**
     * Get creation time
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get last update time
     *
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set tags
     *
     * @param ArrayCollection $tags
     * @return Media
     */
    public function setTags(ArrayCollection $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Add tag
     *
     * @param Tag tag
     * @return Media
     */
    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);

        return $this;
    }

    /**
     * Remove tag
     *
     * @param Tag tag
     * @return Media
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFile();
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     * @return void
     */
    public function uploadFile(\Symfony\Component\Form\Form $form)
    {
        if (!file_exists($this->getUploadRootDir() . '/' . $form['file']->getData()))
        {
            list($width, $height) = getimagesize($form['file']->getData()->getPathName());

            $this->setWidth($width);
            $this->setHeight($height);
            $this->setType($form['file']->getData()->getClientMimeType());
            $this->setSize($this->convertFileSize($form['file']->getData()->getClientSize()));
        }

        parent::uploadFile($form);
    }

    /**
     * @param $bytes
     * @return string
     */
    public function convertFileSize($bytes)
    {
        switch ($bytes) {
            case $bytes > 1024*1024*1024:
                return round($bytes/1024/1024/1024, 2) ." Go";
            case $bytes > 1024*1024:
                return round($bytes/1024/1024, 2) ." Mo";
            case $bytes > 1024:
                return round($bytes/1024, 2) ." Ko";
            default:
                return $bytes;
        }
    }

    /**
     * @return string
     */
    public function getUploadDir()
    {
        return 'portfolio';
    }

    /**
     * @return string
     */
    public function getUploadRootDir()
    {
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    /**
     * @return array
     */
    public function getTagsForSlider()
    {
        $toReturn = array();
        foreach ($this->getPortfolioTags() as $tag)
        {
            if ($tag->getPortfolioTagCategory() && $tag->getPortfolioTagCategory()->getSlug() == 'camping')
            {
                $toReturn[] = $tag->getSlug();
            }
        }

        return $toReturn;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param MediaTranslation $t
     */
    public function addTranslation(MediaTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }
    }
}
