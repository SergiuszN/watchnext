<?php

namespace WatchNext\Engine\Cli;

use Exception;
use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Cli\IO\LockedCommandTrait;
use WatchNext\Engine\Container;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Event\CommandBusStatusEnum;
use WatchNext\Engine\Event\DatabaseEventDispatcher;
use WatchNext\Engine\Logger;

class DatabaseEventWorker implements CliCommandInterface
{
    use LockedCommandTrait;

    public function __construct(
        private readonly Container $container,
        private readonly Database $database,
        private readonly Logger $logger,
        private readonly DatabaseEventDispatcher $databaseEventDispatcher,
    ) {
    }

    public function getHelp(): string
    {
        return 'This command watch on command_bus queue and execute actions that in queue';
    }

    public function execute(CliInput $input, CliOutput $output): void
    {
        $this->setLockPrefix($input->getArgument(0, ''));

        if ($this->isLocked()) {
            $output->writeln('Command already executing in other trait!');

            return;
        }

        $this->createLock(60 * 60);

        $output->writeln('Worker started!');
        $this->database->setIsDebug(false);

        while (true) {
            do {
                if (!$this->isLocked()) {
                    $this->unlock();
                    exit;
                }
            } while ($this->pull($output));

            // If queue is empty lets rest some time
            echo "SLEEP\n";
            sleep(1);
        }
    }

    /**
     * @return bool Return true if queue had more messages that butch size
     */
    private function pull(CliOutput $output): bool
    {
        $this->database->transactionBegin();

        $message = $this->database->prepare('SELECT * FROM command_bus WHERE status=:status LIMIT 1 FOR UPDATE')
            ->execute(['status' => CommandBusStatusEnum::NEW->value])
            ->fetch();

        if (empty($message)) {
            $this->database->transactionCommit();

            return false;
        }

        $updateEventStatement = $this->database->prepare('UPDATE command_bus SET status = :status WHERE id = :id');
        $updateEventStatement->execute(['status' => CommandBusStatusEnum::IN_PROGRESS->value, 'id' => $message['id']]);
        $this->database->transactionCommit();

        $output->writeln("Loaded {$message['id']}:{$message['message_class']} event");

        try {
            $this->databaseEventDispatcher->execute($message['message_class'], $message['message']);
            $updateEventStatement->execute(['status' => CommandBusStatusEnum::SUCCESS->value, 'id' => $message['id']]);
        } catch (Exception $exception) {
            $output->writeln("Error: {$exception->getMessage()}");
            $this->logger->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);
            $updateEventStatement->execute(['status' => CommandBusStatusEnum::ERROR->value, 'id' => $message['id']]);
        }

        $output->writeln("Executed {$message['id']}:{$message['message_class']} event");

        return true;
    }
}
