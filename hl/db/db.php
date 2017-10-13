<?php
namespace havana;

require __DIR__.'/dbclient_mysql.php';
require __DIR__.'/dbclient_sqllite.php';

use PDO;

class dbclient
{
	static function make($url)
	{
		$u = parse_url($url);
		if (!isset($u['scheme'])) {
			throw new Exception("Invalid URL: $url");
		}

		switch ($u['scheme']) {
			case 'mysql': return new dbclient_mysql($url);
			case 'sqlite': return new dbclient_sqlite($url);
			default: throw new Exception("Unknown database type: $u[scheme]");
		}
	}

	/*
	 * The connection object
	 */
	protected $db = null;

	/*
	 * Number of rows affected by the last query
	 */
	private $affected_rows = 0;

	/*
	 * Executes a query, returns true or false
	 */
	function exec($query, ...$args)
	{
		$st = $this->run($query, $args);
		if (!$st) {
			return false;
		}
		$st->closeCursor();
		return true;
	}

	// Queries and returns multiple rows.
	function getRows($query, ...$args)
	{
		$st = $this->run($query, $args);
		$rows = $st->fetchAll(PDO::FETCH_ASSOC);
		$st->closeCursor();
		return $rows;
	}

	// Queries and returns one row from the result.
	// Returns null if there are no rows in the result.
	function getRow($query, ...$args)
	{
		$st = $this->run($query, $args);
		$rows = $st->fetchAll(PDO::FETCH_ASSOC);
		$st->closeCursor();
		if (empty($rows)) {
			return null;
		}
		return $rows[0];
	}

	// Queries and returns one column as an array
	function getValues($query, ...$args)
	{
		$st = $this->run($query, $args);
		$values = [];
		while (1) {
			$row = $st->fetch(PDO::FETCH_NUM);
			if (!$row) {
				break;
			}
			$values[] = $row[0];
		}
		$st->closeCursor();
		return $values;
	}

	// Queries and returns a single value.
	// Returns null if there are now rows in the result.
	function getValue($query, ...$args)
	{
		$st = $this->run($query, $args);
		$row = $st->fetch(PDO::FETCH_NUM);
		$st->closeCursor();
		if (!$row) return null;
		return $row[0];
	}

	// Inserts a row given as a dict into the specified table.
	function insert($table, $row)
	{
		list ($query, $args) = sqlWriter::insert($table, $row);
		$st = $this->run($query, $args);
		$st->closeCursor();
		return $this->db->lastInsertId();
	}

	// Updates the specified table setting values from the 'values' dict
	// where rows match the given filter.
	function update($table, $values, $filter)
	{
		list ($query, $args) = sqlWriter::update($table, $values, $filter);
		$st = $this->run($query, $args);
		$st->closeCursor();
		return $this->affectedRows();
	}

	// Runs the given query with the given arguments.
	// The arguments list is given as array.
	// Returns the prepared statement after its execution.
	protected function run($query, $args)
	{
		$this->affected_rows = 0;
		$st = $this->db->prepare($query);
		$st->execute($args);
		$this->affected_rows = $st->rowCount();
		return $st;
	}

	function affectedRows()
	{
		return $this->affected_rows;
	}
}

class sqlWriter
{
	static function insert($table, $row)
	{
		$cols = array_keys($row);
		$header =  '("'.implode('", "', $cols).'")';

		$placeholders = array_fill(0, count($row), '?');
		$tuple = '('.implode(', ', $placeholders).')';

		$q = "INSERT INTO \"$table\" $header VALUES $tuple";
		$args = array_values($row);
		return [$q, $args];
	}

	static function update($table, $values, $filter)
	{
		$q = 'UPDATE "'.$table.'" SET ';

		$args = [];

		$set = [];
		foreach ($values as $field => $value) {
			$set[] = '"'.$field.'" = ?';
			$args[] = $value;
		}
		$q .= implode(', ', $set);

		$where = [];
		foreach ($filter as $field => $value) {
			$where[] = '"'.$field.'" = ?';
			$args[] = $value;
		}

		$q .= ' WHERE '.implode(' AND ', $where);

		return [$q, $args];

		$st = $this->db->prepare($q);
		$r = $st->execute($args);
		return $this->affectedRows();
	}
}
