<?php
/**
 * @todo    General file information
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * @todo General class information
 *
 */
class Image extends AbstractRendering {

	/**
	 * Render the given element
	 *
	 * @return array
	 */
	public function renderInternal() {
		$objectManager = new ObjectManager();
		/** @var FileRepository $fileRepository */
		$fileRepository = $objectManager->get('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$files = $fileRepository->findByRelation('tt_content', 'image', $this->contentObject->data['uid']);

		$images_arr = array();
		foreach ($files as $file) {
			/** @var $file File */
			$images_arr[] = $this->getLink($file->getPublicUrl());
		}

		$lines[] = $this->renderImagesHelper($images_arr, !$this->contentObject->data['image_zoom'] ? $this->contentObject->data['image_link'] : '', $this->contentObject->data['imagecaption']);

		return $lines;

	}

	/**
	 * Render block of images - which means creating lines with links to the images.
	 *
	 * @param     array $images_arr : the image array
	 * @param    string $links      : Link value from the "image_link" field in tt_content records
	 * @param    string $caption    : Caption text
	 *
	 * @return    string        Content
	 * @see getImages()
	 */
	function renderImagesHelper($images_arr, $links, $caption) {
		$linksArr = explode(',', $links);
		$lines = array();
		$imageExists = FALSE;

		foreach ($images_arr as $k => $file) {
			if (strlen(trim($file)) > 0) {
				$lines[] = $file;
				if ($links && count($linksArr) > 1) {
					if (isset($linksArr[$k])) {
						$ll = $linksArr[$k];
					} else {
						$ll = $linksArr[0];
					}

					$theLink = $this->getLink($ll);
					if ($theLink) {
						$lines[] = $this->configuration['images.']['linkPrefix'] . $theLink;
					}
				}
				$imageExists = TRUE;
			}
		}
		if ($this->configuration['images.']['header'] && $imageExists) {
			array_unshift($lines, $this->configuration['images.']['header']);
		}
		if ($links && count($linksArr) == 1) {
			$theLink = $this->getLink($links);
			if ($theLink) {
				$lines[] = $this->configuration['images.']['linkPrefix'] . $theLink;
			}
		}
		if ($caption) {
			$lines[] = '';
			$cHeader = trim($this->configuration['images.']['captionHeader']);
			if ($cHeader) {
				$lines[] = $cHeader;
			}
			$lines[] = $this->breakContent($caption);
		}

		return chr(10) . implode(chr(10), $lines);
	}

}