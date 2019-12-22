<?php

namespace Jbourdin\Exif;

use DirectoryIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExifRewriteCommand
 */
class ExifRewriteCommand extends Command
{
    /**
     * @var array
     */
    private $result = 0;
    /**
     * @var InputInterface
     */
    private $input;
    /**
     * @var OutputInterface
     */
    private $output;

    public static $defaultName = 'exif:fix:date';


    protected function configure()
    {
        $this
            ->setDescription('Add mising date for filtes')
            ->addArgument('path', InputArgument::REQUIRED, 'path to strat processing');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $path         = $input->getArgument('path');
        $this->iterate($path);
        $output->writeln('');
        $output->writeln('<info>' . $this->result . ' images processes</info>');

        return 0;
    }


    /**
     * @param string $path
     *
     * @return bool
     */
    private function iterate(string $path): bool
    {
        $directory = new DirectoryIterator($path);
        $this->output->writeln('');
        $this->output->writeln('Handling ' . $directory->getRealPath());

        $countDir = iterator_count($directory) - 2;

        $progressBar = null;
        foreach ($directory as $item) {


            if ($item->isDot() || strpos($item->getFilename(), '.') === 0) {
                continue;
            }
            if ($item->isDir()) {
                $this->iterate($item->getRealPath());
                continue;
            }
            if (!$progressBar instanceof ProgressBar) {
                $progressBar = new ProgressBar($this->output, $countDir);
            }
            $progressBar->advance();
            $this->processExif($item);
        }
        if ($progressBar instanceof ProgressBar) {
            $progressBar->finish();
        }

        return true;
    }

    /**
     * @param SplFileInfo $file
     */
    private function processExif(SplFileInfo $file): void
    {
        $year = substr($file->getFilename(), 0, 4);
        if (!is_numeric($year)) {
            return;
        }
        $targetPath = escapeshellarg($file->getRealPath());
        $cmd        = "exiftool -datetimeoriginal=\"{$year}-01-03 12:00:00\" \"-DateTimeDigitized<MDItemContentCreationDate\"  -resolutionunit=inches -XResolution=300 -YResolution=300 -overwrite_original_in_place $targetPath";
        exec($cmd);
        $this->result++;
    }

}