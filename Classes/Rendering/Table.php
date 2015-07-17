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
	 * Table settings
	 *
	 * @var array
	 */
	protected $tableSettings = array(
		'default' => array(
			'join'   => '+',
			'xChar'  => '-',
			'yChar'  => '|',
			'xSpace' => 1,
			'ySpace' => 0,
		)
	);

	/**
	 * Render mode
	 *
	 * @var string
	 */
	protected $renderMode = 'default';

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
		return $this->getTable($tableData);
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

	/**
	 * @param $table
	 *
	 * @return string
	 */
	function getTable($table) {
		$lines = array();
		$columnsHeaders = $this->columns_headers($table);
		$columns_lengths = $this->columns_lengths($table, $columnsHeaders);

		$lines[] = $this->renderLine($columns_lengths);
		#$lines[] = str_repeat($row_spacer, $this->tableSettings['default']['ySpace']);
		$lines[] = $this->renderHeader($columns_lengths, $columnsHeaders);
		#$lines[] = str_repeat($row_spacer, $this->tableSettings['default']['ySpace']);
		$lines[] = $this->renderLine($columns_lengths);
		#$lines[] = str_repeat($row_spacer, $this->tableSettings['default']['ySpace']);
		foreach ($table as $row_cells) {
			$lines[] = $this->renderCell($row_cells, $columnsHeaders, $columns_lengths);
			#$lines[] = str_repeat($row_spacer, $this->tableSettings['default']['ySpace']);
		}
		$lines[] = $this->renderLine($columns_lengths);
		return $lines;
	}

	/**
	 * Render a separation line
	 *
	 * @param $columnsLengths
	 *
	 * @return string
	 */
	protected function renderLine($columnsLengths) {
		$row = '';
		foreach ($columnsLengths as $column_length) {
			$row .= $this->tableSettings[$this->renderMode]['join'] . str_repeat($this->tableSettings[$this->renderMode]['xChar'], ($this->tableSettings[$this->renderMode]['xSpace'] * 2) + $column_length);
		}
		return $row . $this->tableSettings[$this->renderMode]['join'];
	}

	/**
	 * @param $columnsLengths
	 * @param $columnsHeaders
	 *
	 * @return string
	 */
	protected function renderHeader($columnsLengths, $columnsHeaders) {
		$row = '';
		foreach ($columnsHeaders as $header) {
			$row .= $this->tableSettings[$this->renderMode]['yChar'] . str_pad($header, ($this->tableSettings[$this->renderMode]['xSpace'] * 2) + $columnsLengths[$header], ' ', STR_PAD_BOTH);
		}
		return $row . $this->tableSettings[$this->renderMode]['yChar'];
	}

	/**
	 * @param $row_cells
	 * @param $columns_headers
	 * @param $columns_lengths
	 *
	 * @return string
	 */
	function renderCell($row_cells, $columns_headers, $columns_lengths) {
		$row = '';
		foreach ($columns_headers as $header) {
			$stringLength = mb_strlen(utf8_decode($row_cells[$header]));
			$line = array(
				$this->tableSettings['default']['yChar'],
				str_repeat(' ', $this->tableSettings['default']['xSpace']),
				$row_cells[$header],
				str_repeat(' ', $columns_lengths[$header] - $stringLength + $this->tableSettings['default']['xSpace']),
			);
			$row .= implode('', $line);
		}
		$row .= $this->tableSettings['default']['yChar'];

		return $row;
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
	function row_spacer($columns_lengths) {
		$row = '';
		foreach ($columns_lengths as $column_length) {
			$row .= $this->tableSettings['default']['yChar'] . str_repeat(' ', ($this->tableSettings['default']['xSpace'] * 2) + $column_length);
		}
		$row .= $this->tableSettings['default']['yChar'];

		return $row;
	}
}