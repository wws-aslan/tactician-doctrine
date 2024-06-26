<?php

declare(strict_types=1);

namespace League\Tactician\Doctrine\DBAL;

use Doctrine\DBAL\Driver\Connection;
use Exception;
use League\Tactician\Middleware;
use Throwable;

/**
 * Wraps command execution inside a Doctrine DBAL transaction
 */
class TransactionMiddleware implements Middleware
{
    protected Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Executes the given command and optionally returns a value
     *
     * @return mixed
     *
     * @throws Exception
     * @throws Throwable
     */
    public function execute($command, callable $next)
    {
        $this->connection->beginTransaction();

        try {
            $returnValue = $next($command);

            $this->connection->commit();
        } catch (Exception $exception) {
            $this->connection->rollBack();

            throw $exception;
        } catch (Throwable $exception) {
            $this->connection->rollBack();

            throw $exception;
        }

        return $returnValue;
    }
}
