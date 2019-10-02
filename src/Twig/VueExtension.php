<?php
declare(strict_types=1);

namespace App\Twig;

use App\Vue\VueDataStorage;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VueExtension extends AbstractExtension
{
    /**
     * @var VueDataStorage
     */
    private $vueDataStorage;

    public function __construct(VueDataStorage $vueDataStorage)
    {
        $this->vueDataStorage = $vueDataStorage;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_json_request', [$this, 'isJsonRequest']),
            new TwigFunction('add_vue_data', [$this, 'addVueData']),
            new TwigFunction('get_vue_data', [$this, 'getVueData']),
        ];
    }

    /**
     * Add data to vue-instance.
     * Effectively this would be similar to using <script>vue.data = Object.assign(vue.data, {key: value})</script>
     * However, using script within rendered vue components is not allowed.
     * By using this addVueData method the data can be rendered outside the rendered component, using getVueData
     *
     * @param String $key
     * @param $value
     */
    public function addVueData(String $key, $value): void
    {
        $this->vueDataStorage->addData($key, $value);
    }

    public function getVueData(): string
    {
        return $this->vueDataStorage->getJson();
    }

    public function isJsonRequest(Request $request): bool
    {
        return $request->getContentType() && stripos($request->getContentType(), 'json') !== -1;
    }
}
