<?php
declare(strict_types=1);

namespace App\Generator;

use Doctrine\Common\Inflector\Inflector;
use K3ssen\GeneratorBundle\MetaData\MetaEntityInterface;

class CrudGenerator extends \K3ssen\GeneratorBundle\Generator\CrudGenerator
{
    public function createViewTemplate(MetaEntityInterface $metaEntity, $action): string
    {
        $fileContent = $this->render('templates/'.$action.'.txt.twig', $metaEntity);

        $templatesTargetDir = rtrim($this->generateOptions->getTemplatesDirectory() ?: $this->projectDir .'/templates', '/') . '/';

        $targetSubdir = $this->generateOptions->getControllerSubdirectory();
        $targetFile = $templatesTargetDir.
            ($targetSubdir ? Inflector::tableize($targetSubdir).'/' : '').
            Inflector::tableize($metaEntity->getName()). '/'.
            $action.'.vue.twig'; //here, we use '.vue' instead of '.html'
        ;
        $this->getFileSystem()->dumpFile($targetFile, $fileContent);

        return $targetFile;
    }
}