<?php

declare(strict_types=1);

namespace SPC\command;

use SPC\builder\BuilderProvider;
use SPC\exception\ExceptionHandler;
use SPC\exception\FileSystemException;
use SPC\exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** @noinspection PhpUnused */
class BuildLibsCommand extends BuildCommand
{
    protected static $defaultName = 'build:libs';

    public function configure()
    {
        $this->setDescription('Build dependencies');
        $this->addArgument('libraries', InputArgument::REQUIRED, 'The libraries will be compiled, comma separated');
        $this->addOption('clean', null, null, 'Clean old download cache and source before fetch');
        $this->addOption('all', 'A', null, 'Build all libs that static-php-cli needed');
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        // --all 等于 ""
        if ($input->getOption('all')) {
            $input->setArgument('libraries', '');
        }
        parent::initialize($input, $output);
    }

    /**
     * @throws RuntimeException
     * @throws FileSystemException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // 从参数中获取要编译的 libraries，并转换为数组
        $libraries = array_map('trim', array_filter(explode(',', $input->getArgument('libraries'))));

        // 删除旧资源
        if ($input->getOption('clean')) {
            logger()->warning('You are doing some operations that not recoverable: removing directories below');
            logger()->warning(BUILD_ROOT_PATH);
            logger()->warning('I will remove these dir after you press [Enter] !');
            echo 'Confirm operation? [Yes] ';
            fgets(STDIN);
            if (PHP_OS_FAMILY === 'Windows') {
                f_passthru('rmdir /s /q ' . BUILD_ROOT_PATH);
            } else {
                f_passthru('rm -rf ' . BUILD_ROOT_PATH);
            }
        }

        try {
            // 构建对象
            $builder = BuilderProvider::makeBuilderByInput($input);
            // 只编译 library 的情况下，标记
            $builder->setLibsOnly();
            // 编译和检查库完整
            $builder->buildLibs($libraries);

            $time = round(microtime(true) - START_TIME, 3);
            logger()->info('Build libs complete, used ' . $time . ' s !');
            return 0;
        } catch (\Throwable $e) {
            if ($input->getOption('debug')) {
                ExceptionHandler::getInstance()->handle($e);
            } else {
                logger()->critical('Build failed with ' . get_class($e) . ': ' . $e->getMessage());
                logger()->critical('Please check with --debug option to see more details.');
            }
            return 1;
        }
    }
}
