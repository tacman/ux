<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\Iconify;
use Symfony\UX\Icons\Registry\LocalSvgIconRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
#[AsCommand(
    name: 'ux:icons:import',
    description: 'Import icon(s) from iconify.design',
)]
final class ImportIconCommand extends Command
{
    public function __construct(private Iconify $iconify, private LocalSvgIconRegistry $registry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'names',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Icon name from iconify.design (suffix with "@<name>" to rename locally)',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $names = $input->getArgument('names');
        $result = Command::SUCCESS;

        foreach ($names as $name) {
            if (!preg_match('#^(([\w-]+):([\w-]+))(@([\w-]+))?$#', $name, $matches)) {
                $io->error(sprintf('Invalid icon name "%s".', $name));
                $result = Command::FAILURE;

                continue;
            }

            [,,$prefix, $name] = $matches;
            $localName = $matches[5] ?? $name;

            $io->comment(sprintf('Importing <info>%s:%s</info> as <info>%s</info>...', $prefix, $name, $localName));

            try {
                $svg = $this->iconify->fetchSvg($prefix, $name);
            } catch (IconNotFoundException $e) {
                $io->error($e->getMessage());
                $result = Command::FAILURE;

                continue;
            }

            $this->registry->add($localName, $svg);

            $io->text(sprintf("<info>Imported Icon</info>, render with <comment>{{ ux_icon('%s') }}</comment>.", $localName));
            $io->newLine();
        }

        return $result;
    }
}
