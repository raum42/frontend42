<?php
namespace Frontend42\Model;

use Core42\Model\AbstractModel;

class PageContent extends AbstractModel
{
    /**
     * @var array
     */
    protected $autoFilledForcedProperties = [];

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        if (!empty($properties)) {
            $this->setProperties($properties);
        }
    }

    /**
     * @param  string $name
     * @param mixed $default
     * @return mixed
     * @throws \Exception
     */
    protected function get($name, $default = null)
    {
        if (!array_key_exists($name, $this->data)) {
            return $default;
        }

        return $this->data[$name];
    }

    /**
     * @param array $properties
     * @return $this
     */
    protected function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @param string $property
     * @return $this
     */
    public function addAutoFilledProperty($property)
    {
        $this->autoFilledForcedProperties[] = $property;

        return $this;
    }

    /**
     * @param string $property
     * @return bool
     */
    public function hasAutoFilledProperty($property)
    {
        return in_array($property, $this->autoFilledForcedProperties);
    }
}
