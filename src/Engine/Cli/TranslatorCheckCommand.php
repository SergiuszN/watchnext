<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Container;

class TranslatorCheckCommand implements CliCommandInterface {

    private string $translationsPath;

    public function __construct(Container $container) {
        $this->translationsPath = $container->get('root.dir') . '/config/translations';
    }

    public function execute(): void {
        [$input, $output] = [new CliInput(), new CliOutput()];

        $baseLang = $input->getOption('base');

        $output->writeln('Translation check command started');
        $translationFiles = array_filter(scandir($this->translationsPath), fn ($file) => !in_array($file, ['.', '..', "messages.{$baseLang}.php"]));

        $baseTranslations = require $this->translationsPath . "/messages.{$baseLang}.php";
        $output->writeln('Found ' . count($translationFiles) . ' translations for check');

        foreach ($translationFiles as $file) {
            $translationFileFullPath = $this->translationsPath . '/' . $file;
            $translations = require $translationFileFullPath;

            $newFile = "<?php\n\nreturn [\n";

            foreach ($translations as $key => $translation) {
                $translation = str_replace("'", "\'", $translation);
                $translation = str_replace(["\r", "\n"], '', $translation);

                $newFile .= "\t'{$key}' => '$translation',\n";
            }

            $diff = array_diff_key($baseTranslations, $translations);
            $output->writeln('Found ' . count($diff) . ' missed keys');
            foreach ($diff as $key => $value) {
                $newFile .= "\t'{$key}' => '#!Change ME: {$value}',\n";
            }

            $newFile .= "];\n";
            file_put_contents($translationFileFullPath, $newFile);

            $output->writeln("$file checked");
        }

        $output->writeln('After adding new translations consider run reorder command');
        $output->writeln('Done');
    }
}