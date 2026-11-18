<?php

final class DatabaseResult
{
    private $rows;
    private $position = 0;
    public $num_rows;
    public $num_fields;

    public function __construct(array $rows, int $affectedRows = 0, int $fieldCount = 0)
    {
        $this->rows = $rows;
        $this->num_rows = count($rows) > 0 ? count($rows) : $affectedRows;
        $this->num_fields = $fieldCount;
    }

    public function fetch(int $mode)
    {
        if (!array_key_exists($this->position, $this->rows)) {
            return false;
        }

        $row = $this->rows[$this->position++];

        if ($mode === PDO::FETCH_ASSOC) {
            return array_filter(
                $row,
                function ($key) {
                    return is_string($key);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        if ($mode === PDO::FETCH_NUM) {
            return array_filter(
                $row,
                function ($key) {
                    return is_int($key);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return $row;
    }
}

function db_query(PDO $conn, string $query): DatabaseResult
{
    $statement = $conn->query($query);
    return db_result_from_statement($statement);
}

function db_query_params(PDO $conn, string $query, array $parameters): DatabaseResult
{
    $statement = $conn->prepare($query);
    $statement->execute(array_values($parameters));

    return db_result_from_statement($statement);
}

function db_result_from_statement(PDOStatement $statement): DatabaseResult
{
    $fieldCount = $statement->columnCount();
    if ($fieldCount > 0) {
        return new DatabaseResult(
            $statement->fetchAll(PDO::FETCH_BOTH),
            0,
            $fieldCount
        );
    }

    return new DatabaseResult([], $statement->rowCount());
}

function db_prepare(PDO $conn, string $query): PDOStatement
{
    return $conn->prepare($query);
}

function db_stmt_bind_param(PDOStatement $statement, string $types, ...$values): bool
{
    if (strlen($types) !== count($values)) {
        throw new InvalidArgumentException(
            "The number of parameter types must match the number of values."
        );
    }

    foreach ($values as $index => $value) {
        $parameterType = PDO::PARAM_STR;

        if ($types[$index] === "i") {
            $parameterType = PDO::PARAM_INT;
        } elseif ($types[$index] === "b") {
            $parameterType = PDO::PARAM_LOB;
        }

        $statement->bindValue($index + 1, $value, $parameterType);
    }

    return true;
}

function db_stmt_execute(PDOStatement $statement): bool
{
    return $statement->execute();
}

function db_stmt_get_result(PDOStatement $statement): DatabaseResult
{
    $fieldCount = $statement->columnCount();
    $rows = $fieldCount > 0 ? $statement->fetchAll(PDO::FETCH_BOTH) : [];

    return new DatabaseResult($rows, $statement->rowCount(), $fieldCount);
}

function db_stmt_close(PDOStatement $statement): bool
{
    return $statement->closeCursor();
}

function db_fetch_assoc(DatabaseResult $result)
{
    return $result->fetch(PDO::FETCH_ASSOC);
}

function db_fetch_row(DatabaseResult $result)
{
    return $result->fetch(PDO::FETCH_NUM);
}

function db_fetch_array(DatabaseResult $result)
{
    return $result->fetch(PDO::FETCH_BOTH);
}

function db_num_rows(DatabaseResult $result): int
{
    return $result->num_rows;
}

function db_num_fields(DatabaseResult $result): int
{
    return $result->num_fields;
}

function db_escape(PDO $conn, string $value): string
{
    $quoted = $conn->quote($value);

    return substr($quoted, 1, -1);
}

function db_error(PDO $conn): string
{
    $error = $conn->errorInfo();

    return implode(": ", array_filter($error));
}
