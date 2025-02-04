<?php

declare(strict_types=1);

namespace SPC\command;

use SPC\exception\FileSystemException;
use SPC\exception\ValidationException;
use SPC\store\FileSystem;
use SPC\util\ConfigValidator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 修改 config 后对其 kv 进行排序的操作
 */
class SortConfigCommand extends BaseCommand
{
    protected static $defaultName = 'sort-config';

    public function configure()
    {
        $this->setDescription('After config edited, sort it by alphabet');
        $this->addArgument('config-name', InputArgument::REQUIRED, 'Your config to be sorted, you can sort "lib", "source" and "ext".');
    }

    /**
     * @throws ValidationException
     * @throws FileSystemException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        switch ($name = $input->getArgument('config-name')) {
            case 'lib':
                $file = json_decode(FileSystem::readFile(ROOT_DIR . '/config/lib.json'), true);
                ConfigValidator::validateLibs($file);
                ksort($file);
                file_put_contents(ROOT_DIR . '/config/lib.json', json_encode($file, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
            case 'source':
                $file = json_decode(FileSystem::readFile(ROOT_DIR . '/config/source.json'), true);
                ConfigValidator::validateSource($file);
                ksort($file);
                file_put_contents(ROOT_DIR . '/config/source.json', json_encode($file, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
            case 'ext':
                $file = json_decode(FileSystem::readFile(ROOT_DIR . '/config/ext.json'), true);
                ConfigValidator::validateExts($file);
                ksort($file);
                file_put_contents(ROOT_DIR . '/config/ext.json', json_encode($file, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
            default:
                $output->writeln("<error>invalid config name: {$name}</error>");
                return 1;
        }
        $output->writeln('<info>sort success</info>');
        return 0;
    }
}
