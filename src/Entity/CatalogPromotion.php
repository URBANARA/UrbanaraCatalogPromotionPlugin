<?php

declare(strict_types=1);

namespace Urbanara\CatalogPromotionPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Model\TimestampableTrait;

class CatalogPromotion implements CatalogPromotionInterface
{
    use TimestampableTrait;

    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $exclusive = false;

    /**
     * When exclusive, promotion with top priority will be applied
     *
     * @var int
     */
    protected $priority = 0;

    /**
     * @var \DateTime
     */
    protected $startsAt;

    /**
     * @var \DateTime
     */
    protected $endsAt;

    /**
     * @var string
     */
    protected $discountType;

    /**
     * @var array
     */
    protected $discountConfiguration = [];

    /**
     * @var ChannelInterface[]|Collection
     */
    protected $channels;

    /**
     * @var Collection|CatalogRuleInterface[]
     */
    protected $rules;

    /**
     * @var Collection|CatalogPromotionDecorationInterface[]
     */
    protected $decorations;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
        $this->rules = new ArrayCollection();
        $this->decorations = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function isExclusive()
    {
        return $this->exclusive;
    }

    /**
     * {@inheritdoc}
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartsAt()
    {
        return $this->startsAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setStartsAt(\DateTime $startsAt = null)
    {
        $this->startsAt = $startsAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndsAt()
    {
        return $this->endsAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndsAt(\DateTime $endsAt = null)
    {
        $this->endsAt = $endsAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountConfiguration()
    {
        return $this->discountConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountConfiguration(array $discountConfiguration)
    {
        $this->discountConfiguration = $discountConfiguration;
    }

    /**
     * @return ChannelInterface[]|Collection
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * {@inheritdoc}
     */
    public function addChannel(ChannelInterface $channel)
    {
        if (!$this->hasChannel($channel)) {
            $this->channels->add($channel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeChannel(ChannelInterface $channel)
    {
        if ($this->hasChannel($channel)) {
            $this->channels->removeElement($channel);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasChannel(ChannelInterface $channel)
    {
        return $this->channels->contains($channel);
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRules()
    {
        return !$this->rules->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRule(CatalogRuleInterface $rule)
    {
        return $this->rules->contains($rule);
    }

    /**
     * {@inheritdoc}
     */
    public function addRule(CatalogRuleInterface $rule)
    {
        if (!$this->hasRule($rule)) {
            $rule->setCatalogPromotion($this);
            $this->rules->add($rule);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRule(CatalogRuleInterface $rule)
    {
        $rule->setCatalogPromotion(null);
        $this->rules->removeElement($rule);
    }

    /**
     * {@inheritdoc}
     */
    public function getDecorations()
    {
        return $this->decorations;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDecorations()
    {
        return !$this->decorations->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function hasDecoration(CatalogPromotionDecorationInterface $decoration)
    {
        return $this->decorations->contains($decoration);
    }

    /**
     * {@inheritdoc}
     */
    public function addDecoration(CatalogPromotionDecorationInterface $decoration)
    {
        if (!$this->hasDecoration($decoration)) {
            $decoration->setCatalogPromotion($this);
            $this->decorations->add($decoration);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeDecoration(CatalogPromotionDecorationInterface $decoration)
    {
        $decoration->setCatalogPromotion(null);
        $this->decorations->removeElement($decoration);
    }
}
