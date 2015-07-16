<?php
/**
 * @todo    General file information
 *
 * @package ...
 * @author  Tim Lochmüller
 */

/**
 * @todo   General class information
 *
 * @author Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class Table extends AbstractRendering {

	/**
	 * @param ContentObjectRenderer $contentObject
	 *
	 * @return array
	 */
	public function render($contentObject) {
		$controller = GeneralUtility::makeInstance('TYPO3\\CMS\\CssStyledContent\\Controller\\CssStyledContentController');
		$controller->cObj = $contentObject;
		$htmlTable = $controller->render_table();
		$tableData = $this->parseHtmlTable($htmlTable);
		$lines[] = $this->getTable($tableData);
		return $lines;
	}

	protected function parseHtmlTable($html) {
		$dom = new \DOMDocument();

		//load the html
		$html = $dom->loadHTML(utf8_decode($html));

		//discard white space
		$dom->preserveWhiteSpace = FALSE;

		//the table by its tag name
		$tables = $dom->getElementsByTagName('table');

		//get all rows from the table
		$rows = $tables->item(0)
			->getElementsByTagName('tr');
		// get each column by tag name
		$cols = $rows->item(0)
			->getElementsByTagName('th');
		$row_headers = NULL;
		foreach ($cols as $node) {
			//print $node->nodeValue."\n";
			$row_headers[] = $node->nodeValue;
		}

		$table = array();
		//get all rows from the table
		$rows = $tables->item(0)
			->getElementsByTagName('tr');
		foreach ($rows as $row) {
			// get each column by tag name
			$cols = $row->getElementsByTagName('td');
			$row = array();
			$i = 0;
			foreach ($cols as $node) {
				# code...
				//print $node->nodeValue."\n";
				if ($row_headers == NULL) {
					$row[] = $node->nodeValue;
				} else {
					$row[$row_headers[$i]] = $node->nodeValue;
				}
				$i++;
			}
			$table[] = $row;
		}

		return $table;
	}

	const SPACING_X = 1;

	const SPACING_Y = 0;

	const JOINT_CHAR = '+';

	const LINE_X_CHAR = '-';

	const LINE_Y_CHAR = '|';

	/**
	 * @param $table
	 *
	 * @return string
	 */
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

	/**
	 * @param $table
	 *
	 * @return array
	 */
	function columns_headers($table) {
		return array_keys(reset($table));
	}

	/**
	 * @param $table
	 * @param $columns_headers
	 *
	 * @return array
	 */
	function columns_lengths($table, $columns_headers) {
		$lengths = array();
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

	/**
	 * @param $columns_lengths
	 *
	 * @return string
	 */
	function row_seperator($columns_lengths) {
		$row = '';
		foreach ($columns_lengths as $column_length) {
			$row .= self::JOINT_CHAR . str_repeat(self::LINE_X_CHAR, (self::SPACING_X * 2) + $column_length);
		}
		$row .= self::JOINT_CHAR;

		return $row;
	}

	/**
	 * @param $columns_lengths
	 *
	 * @return string
	 */
	function row_spacer($columns_lengths) {
		$row = '';
		foreach ($columns_lengths as $column_length) {
			$row .= self::LINE_Y_CHAR . str_repeat(' ', (self::SPACING_X * 2) + $column_length);
		}
		$row .= self::LINE_Y_CHAR;

		return $row;
	}

	/**
	 * @param $columns_headers
	 * @param $columns_lengths
	 *
	 * @return string
	 */
	function row_headers($columns_headers, $columns_lengths) {
		$row = '';
		foreach ($columns_headers as $header) {
			$row .= self::LINE_Y_CHAR . str_pad($header, (self::SPACING_X * 2) + $columns_lengths[$header], ' ', STR_PAD_BOTH);
		}
		$row .= self::LINE_Y_CHAR;

		return $row;
	}

	/**
	 * @param $row_cells
	 * @param $columns_headers
	 * @param $columns_lengths
	 *
	 * @return string
	 */
	function row_cells($row_cells, $columns_headers, $columns_lengths) {
		$row = '';
		foreach ($columns_headers as $header) {
			$row .= self::LINE_Y_CHAR . str_repeat(' ', self::SPACING_X) . str_pad($row_cells[$header], self::SPACING_X + $columns_lengths[$header], ' ', STR_PAD_RIGHT);
		}
		$row .= self::LINE_Y_CHAR;

		return $row;
	}
}