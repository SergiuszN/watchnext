<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;

class TranslatorOrderKeysCommand implements CliCommandInterface
{
    private string $translationsPath;

    public function __construct()
    {
        $this->translationsPath = ROOT_PATH . '/config/translations';
    }

    public function getHelp(): string
    {
        return 'This command reorder translations file keys in alphabetical order
Ensure add that command in some process before commit changes
';
    }

    public function execute(CliInput $input, CliOutput $output): void
    {
        $output->writeln('Translation order key command started');
        $translationFiles = array_filter(scandir($this->translationsPath), fn ($file) => !in_array($file, ['.', '..']));

        $output->writeln('Found ' . count($translationFiles) . ' translations');

        foreach ($translationFiles as $file) {
            $translationFileFullPath = $this->translationsPath . '/' . $file;

            $translations = require $translationFileFullPath;
            ksort($translations);

            $newFile = "<?php\n\nreturn [\n";

            foreach ($translations as $key => $translation) {
                $translation = str_replace("'", "\'", $translation);
                $translation = str_replace(["\r", "\n"], '', $translation);

                $newFile .= "    '{$key}' => '$translation',\n";
            }

            $newFile .= "];\n";
            file_put_contents($translationFileFullPath, $newFile);

            $output->writeln("Fixed: $file");
        }

        $output->writeln('Done');
    }
}
