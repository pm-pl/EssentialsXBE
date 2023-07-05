<?php

namespace wock\essentialx\Utils;

use mysqli;
use function Symfony\Component\String\s;

class DatabaseConnection
{
    private mysqli $connection;

    public function __construct(string $host, string $username, string $password, string $database, int $port = 3306)
    {
        $this->connection = new mysqli($host, $username, $password, $database, $port);
        if ($this->connection->connect_error) {
            throw new \Exception("Database connection failed: " . $this->connection->connect_error);
        }
    }

    /**
     * @throws \Exception
     */
    public function execute(string $query, array $parameters = []): void
    {
        $statement = $this->prepareStatement($query, $parameters);
        $statement->execute();
        $statement->close();
    }

    /**
     * @throws \Exception
     */
    public function query(string $query, array $parameters = []): \mysqli_result
    {
        $statement = $this->prepareStatement($query, $parameters);
        $statement->execute();
        $result = $statement->get_result();
        $statement->close();
        return $result;
    }

    public function prepareStatement(string $query, array $parameters = []): \mysqli_stmt
    {
        $statement = $this->connection->prepare($query);
        if (!$statement) {
            throw new \Exception("Failed to prepare statement: " . $this->connection->error);
        }
        if (!empty($parameters)) {
            $types = str_repeat("s", count($parameters));
            $statement->bind_param($types, ...$parameters);
        }
        return $statement;
    }

    public function __destruct()
    {
        $this->connection->close();
    }
}
