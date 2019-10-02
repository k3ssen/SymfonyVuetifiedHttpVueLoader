<?php
declare(strict_types=1);

namespace App\Command;

use K3ssen\GeneratorBundle\Command\Style\CommandStyle;
use K3ssen\GeneratorBundle\Generator\CrudGenerator;
use K3ssen\GeneratorBundle\Generator\CrudGenerateOptions;
use K3ssen\GeneratorBundle\MetaData\MetaEntityFactory;
use K3ssen\GeneratorBundle\MetaData\MetaEntityInterface;
use K3ssen\GeneratorBundle\Reader\BundleProvider;
use K3ssen\GeneratorBundle\Reader\ExistingEntityToMetaEntityReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CrudCommand extends \K3ssen\GeneratorBundle\Command\CrudCommand
{
    protected const TITLE = 'Generate CRUD';

    protected static $defaultName = 'generator:crud';

    /**
     * Overwrite parent-method, since we do not depend on the SgDatatablesBundle
     */
    protected function determineUseDatatable(InputInterface $input, OutputInterface $output)
    {
        $io = new CommandStyle($input, $output);
        $useDatatable = $input->getOption('use-datatable') ?? $this->generateOptions->getUseDatatableDefault();
        $useDatatable = $io->confirm('Use Datatable?', $useDatatable);
        $this->generateOptions->setUseDatatable($useDatatable);
    }
}
