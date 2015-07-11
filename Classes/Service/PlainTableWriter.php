<?php
/**
 * @todo    General file information
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Service;

/**
 * @todo   General class information
 *
 * @author Tim Lochmüller
 */
class PlainTableWriter {

	const SPACING_X = 1;

	const SPACING_Y = 0;

	const JOINT_CHAR = '+';

	const LINE_X_CHAR = '-';

	const LINE_Y_CHAR = '|';

	function getTable($table) {

		$nl = "\n";
		$columns_headers = $this->columns_headers($table);
		$columns_lengths = $this->columns_lengths($table, $columns_headers);
		$row_separator = $this->row_seperator($columns_lengths);
		$row_spacer = $this->row_spacer($columns_lengths);
		$row_headers = $this->row_headers($columns_headers, $columns_lengths);

		$out = $row_separator . $nl;
		$out .= str_repeat($row_spacer . $nl, self::SPACING_Y);
		$out .= $row_headers . $nl;
		$out .= str_repeat($row_spacer . $nl, self::SPACING_Y);
		$out .= $row_separator . $nl;
		$out .= str_repeat($row_spacer . $nl, self::SPACING_Y);
		foreach ($table as $row_cells) {
			$row_cells = $this->row_cells($row_cells, $columns_headers, $columns_lengths);
			$out .= $row_cells . $nl;
			$out .= str_repeat($row_spacer . $nl, self::SPACING_Y);
		}
		$out .= $row_separator . $nl;
		return $out;
	}

	function columns_headers($table) {
		return array_keys(reset($table));
	}

	function columns_lengths($table, $columns_headers) {
		$lengths = [];
		foreach ($columns_headers as $header) {
			$header_length = mb_strlen($header);
			$max = $header_length;
			foreach ($table as $row) {
				$length = mb_strlen($row[$header]);
				if ($length > $max) {
					$max = $length;
				}
			}

			if (($max % 2) != ($header_length % 2)) {
				$max += 1;
			}

			$lengths[$header] = $max;
		}

		return $lengths;
	}

	function row_seperator($columns_lengths) {
		$row = '';
		foreach ($columns_lengths as $column_length) {
			$row .= self::JOINT_CHAR . str_repeat(self::LINE_X_CHAR, (self::SPACING_X * 2) + $column_length);
		}
		$row .= self::JOINT_CHAR;

		return $row;
	}

	function row_spacer($columns_lengths) {
		$row = '';
		foreach ($columns_lengths as $column_length) {
			$row .= self::LINE_Y_CHAR . str_repeat(' ', (self::SPACING_X * 2) + $column_length);
		}
		$row .= self::LINE_Y_CHAR;

		return $row;
	}

	function row_headers($columns_headers, $columns_lengths) {
		$row = '';
		foreach ($columns_headers as $header) {
			$row .= self::LINE_Y_CHAR . str_pad($header, (self::SPACING_X * 2) + $columns_lengths[$header], ' ', STR_PAD_BOTH);
		}
		$row .= self::LINE_Y_CHAR;

		return $row;
	}

	function row_cells($row_cells, $columns_headers, $columns_lengths) {
		$row = '';
		foreach ($columns_headers as $header) {
			$row .= self::LINE_Y_CHAR . str_repeat(' ', self::SPACING_X) . str_pad($row_cells[$header], self::SPACING_X + $columns_lengths[$header], ' ', STR_PAD_RIGHT);
		}
		$row .= self::LINE_Y_CHAR;

		return $row;
	}

}
