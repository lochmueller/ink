<?php
/**
 * @todo    General file information
 *
 * @package ...
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use FRUIT\Ink\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo   General class information
 *
 * @author Tim Lochmüller
 */
class Table extends AbstractRendering {

	/**
	 * Table settings
	 *
	 * @var array
	 */
	protected $tableSettings = array(
		'default'       => array(
			'join'           => '+',
			'xChar'          => '-',
			'xCharHeader'    => '-',
			'yChar'          => '|',
			'yCharHeader'    => '|',
			'xSpace'         => 1,
			'ySpace'         => 0,
			'separateData'   => FALSE,
			'separateHeader' => TRUE,
			'outerTop'       => TRUE,
			'outerBottom'    => TRUE,
			'outerLeft'      => TRUE,
			'outerRight'     => TRUE,
		),
		'ascii_old'     => array(
			'join'           => '+',
			'xChar'          => '-',
			'xCharHeader'    => '=',
			'yChar'          => '|',
			'yCharHeader'    => '|',
			'xSpace'         => 1,
			'ySpace'         => 0,
			'separateData'   => TRUE,
			'separateHeader' => TRUE,
			'outerTop'       => TRUE,
			'outerBottom'    => TRUE,
			'outerLeft'      => TRUE,
			'outerRight'     => TRUE,
		),
		'ascii_compact' => array(
			'join'           => ' ',
			'xChar'          => ' ',
			'xCharHeader'    => '=',
			'yChar'          => ' ',
			'yCharHeader'    => ' ',
			'xSpace'         => 0,
			'ySpace'         => 0,
			'separateData'   => FALSE,
			'separateHeader' => TRUE,
			'outerTop'       => FALSE,
			'outerBottom'    => FALSE,
			'outerLeft'      => FALSE,
			'outerRight'     => FALSE,
		),
		'unicode'       => array(
			'join'           => '╬',
			'xChar'          => '═',
			'xCharHeader'    => '═',
			'yChar'          => '║',
			'yCharHeader'    => '║',
			'xSpace'         => 1,
			'ySpace'         => 0,
			'separateData'   => FALSE,
			'separateHeader' => TRUE,
			'outerTop'       => TRUE,
			'outerBottom'    => TRUE,
			'outerLeft'      => TRUE,
			'outerRight'     => TRUE,
		),
		'markdown'      => array(
			'join'           => ' ',
			'xChar'          => ' ',
			'xCharHeader'    => '-',
			'yChar'          => '|',
			'yCharHeader'    => '|',
			'xSpace'         => 1,
			'ySpace'         => 0,
			'separateData'   => FALSE,
			'separateHeader' => TRUE,
			'outerTop'       => FALSE,
			'outerBottom'    => FALSE,
			'outerLeft'      => TRUE,
			'outerRight'     => TRUE,
		),
	);

	/**
	 * Render mode
	 *
	 * @var string
	 */
	protected $renderMode = 'markdown';

	/**
	 * @return array
	 */
	public function renderInternal() {
		$controller = GeneralUtility::makeInstance('TYPO3\\CMS\\CssStyledContent\\Controller\\CssStyledContentController');
		$controller->cObj = $this->contentObject;
		$this->renderMode = Configuration::getTableMode();
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

		$topLine = $this->renderLine($columns_lengths, 'top', 'CharHeader');
		if ($topLine) {
			$lines[] = $topLine;
		}
		#$lines[] = str_repeat($row_spacer, $this->tableSettings['default']['ySpace']);
		$lines[] = $this->renderHeader($columns_lengths, $columnsHeaders);
		#$lines[] = str_repeat($row_spacer, $this->tableSettings['default']['ySpace']);
		if ($this->tableSettings[$this->renderMode]['separateHeader']) {
			$lines[] = $this->renderLine($columns_lengths, 'default', 'CharHeader');
		}
		#$lines[] = str_repeat($row_spacer, $this->tableSettings['default']['ySpace']);
		foreach ($table as $row_cells) {
			$lines[] = $this->renderCell($row_cells, $columnsHeaders, $columns_lengths);
			if ($this->tableSettings[$this->renderMode]['separateData']) {
				$lines[] = $this->renderLine($columns_lengths);
			}
		}

		$bottomLine = $this->renderLine($columns_lengths, 'bottom');
		if ($bottomLine) {
			$lines[] = $bottomLine;
		}
		return $lines;
	}

	/**
	 * Render a separation line
	 *
	 * @param        $columnsLengths
	 * @param string $specialLine
	 * @param string $charMode
	 *
	 * @return string
	 */
	protected function renderLine($columnsLengths, $specialLine = 'default', $charMode = 'Char') {
		if (isset($this->tableSettings[$this->renderMode]['outer' . ucfirst($specialLine)]) && $this->tableSettings[$this->renderMode]['outer' . ucfirst($specialLine)] === FALSE) {
			return FALSE;
		}
		$row = '';
		foreach ($columnsLengths as $key => $column_length) {
			if ($key !== 0 || $this->tableSettings[$this->renderMode]['outerLeft']) {
				$row .= $this->tableSettings[$this->renderMode]['join'];
			}
			$row .= str_repeat($this->tableSettings[$this->renderMode]['x' . $charMode], ($this->tableSettings[$this->renderMode]['xSpace'] * 2) + $column_length);
		}
		if ($this->tableSettings[$this->renderMode]['outerRight']) {
			$row .= $this->tableSettings[$this->renderMode]['join'];
		}
		return $row;
	}

	/**
	 * @param $columnsLengths
	 * @param $columnsHeaders
	 *
	 * @return string
	 */
	protected function renderHeader($columnsLengths, $columnsHeaders) {
		$row = '';
		foreach ($columnsHeaders as $key => $header) {
			if ($key !== 0 || $this->tableSettings[$this->renderMode]['outerLeft']) {
				$row .= $this->tableSettings[$this->renderMode]['yChar'];
			}
			$row .= str_pad($header, ($this->tableSettings[$this->renderMode]['xSpace'] * 2) + $columnsLengths[$header], ' ', STR_PAD_BOTH);
		}
		if ($this->tableSettings[$this->renderMode]['outerRight']) {
			$row .= $this->tableSettings[$this->renderMode]['yChar'];
		}
		return $row;
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
		foreach ($columns_headers as $key => $header) {
			$line = array();
			$stringLength = mb_strlen(utf8_decode($row_cells[$header]));
			if ($key !== 0 || $this->tableSettings[$this->renderMode]['outerLeft']) {
				$line[] = $this->tableSettings[$this->renderMode]['yChar'];
			}
			$line[] = str_repeat(' ', $this->tableSettings[$this->renderMode]['xSpace']);
			$line[] = $row_cells[$header];
			$line[] = str_repeat(' ', $columns_lengths[$header] - $stringLength + $this->tableSettings[$this->renderMode]['xSpace']);

			$row .= implode('', $line);
		}
		if ($this->tableSettings[$this->renderMode]['outerRight']) {
			$row .= $this->tableSettings[$this->renderMode]['yChar'];
		}
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
}