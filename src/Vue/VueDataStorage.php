<?php
declare(strict_types=1);

namespace App\Vue;

class VueDataStorage
{
    protected $vueData = [];

    public function addData(String $key, $value): void
    {
        $this->vueData[$key] = $value;
    }

    public function getData(): array
    {
        return $this->vueData;
    }

    public function getJson(): string
    {
        if (!$this->vueData) {
            return "{}";
        }
        return json_encode($this->vueData);
    }
}