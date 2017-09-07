<?php

namespace alkemann\jsonapi;

use alkemann\h2l\traits\Entity;
use alkemann\h2l\traits\Model as ModelTrait;

/**
 * Class Model
 *
 * @package alkemann\jsonapi
 */
class Model implements \JsonSerializable
{
    use ModelTrait {
        save as public saveModel;
    }
    use Entity;

    /**
     * Export that data of this model in a format that can be json_encoded according to JsonAPI spec
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        $pk = $this->pk();
        $data = $this->data();
        unset($data[$pk]);
        $type = strtolower((new \ReflectionClass($this))->getShortName());
        return [
            'type' => $type,
            'id' => $this->{$pk},
            'attributes' => $data
        ];
    }

    /**
     * @param array $data
     * @param array $options
     * @return bool
     * @throws exceptions\InternalSeverError if save returns false
     */
    public function save(array $data = [], array $options = []): bool
    {
        $result = $this->saveModel($data, $options);
        if (!$result) {
            throw new exceptions\InternalSeverError("Unable to save", "SERVER_ERROR");
        }
        return true;
    }
}
