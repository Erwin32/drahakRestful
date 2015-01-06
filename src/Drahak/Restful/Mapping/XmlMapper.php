<?php
namespace Drahak\Restful\Mapping;

use DOMDocument;
use Traversable;
use SimpleXMLElement;
use Nette\Object;
use Nette\Utils\Json;
use Nette\Utils\Arrays;
use Nette\Utils\JsonException;
use Drahak\Restful\InvalidArgumentException;

/**
 * XmlMapper
 * @package Drahak\Restful\Mapping
 * @author Drahomír Hanák
 *
 * @property string|NULL $rootElement
 */
class XmlMapper extends Object implements IMapper
{

	/** @internal */
	const ITEM_ELEMENT = 'item';

	/** @var DOMDocument */
	private $xml;

	/** @var null|string */
	private $rootElement;

	/**
	 * @param string|null $rootElement
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($rootElement = 'root')
	{
		$this->rootElement = $rootElement;
	}

	/**
	 * Set XML root element
	 * @param string|null $rootElement
	 * @return XmlMapper
	 *
	 * @throws InvalidArgumentException
	 */
	public function setRootElement($rootElement)
	{
		if (!is_string($rootElement) && $rootElement !== NULL) {
			throw new InvalidArgumentException('Root element must be of type string or null if disabled');
		}
		$this->rootElement = $rootElement;
		return $this;
	}

	/**
	 * Get XML root element
	 * @return null|string
	 */
	public function getRootElement()
	{
		return $this->rootElement;
	}

	/**
	 * Parse traversable or array resource data to XML
	 * @param array|Traversable $data
	 * @param bool $prettyPrint
	 * @return mixed|string
	 *
	 * @throws InvalidArgumentException
	 */
	public function stringify($data, $prettyPrint = TRUE)
	{
		if (!is_array($data) && !($data instanceof Traversable)) {
			throw new InvalidArgumentException('Data must be of type array or Traversable');
		}

		if ($data instanceof Traversable) {
			$data = iterator_to_array($data, TRUE);
		}

		$this->xml = new DOMDocument('1.0', 'UTF-8');
		$this->xml->formatOutput = $prettyPrint;
		$this->xml->preserveWhiteSpace = $prettyPrint;
		$root = $this->xml->createElement($this->rootElement);
		$this->xml->appendChild($root);
		$this->toXml($data, $root, self::ITEM_ELEMENT);
		return $this->xml->saveXML();
	}

	/**
	 * Parse XML to array
	 * @param string $data
	 * @return array
	 * 
	 * @throws  MappingException If XML data is not valid
	 */
	public function parse($data)
	{
		return $this->fromXml($data);
	}

	/**
	 * @param string $data
	 * @return array
	 *
	 * @throws  MappingException If XML data is not valid
	 */
	private function fromXml($data)
	{
		try {
			$useErrors = libxml_use_internal_errors(true);
			$xml = simplexml_load_string($data); 
			if (!$xml) {
				$error = libxml_get_last_error();
				throw new MappingException('Input is not valid XML document: ' . $error->message . ' on line ' . $error->line);
			}
			libxml_clear_errors();
			libxml_use_internal_errors($useErrors);
			return $this->normalize(Json::decode(Json::encode((array) $xml), Json::FORCE_ARRAY));
		} catch (JsonException $e) {
			throw new MappingException('Error in parsing response: ' . $e->getMessage());
		}
	}

	/**
	 * Normalize data structure to accepted form
	 * @param  array|* $value 
	 * @return array        
	 */
	private function normalize($value)
	{
		if (isset($value['@attributes'])) unset($value['@attributes']);
		if (count($value) === 0) return '';

		foreach ($value as $key => $node) {
			if (!is_array($node)) continue;
			$value[$key] = $this->normalize($node);
		}
		return $value;
	}

	/**
	 * @param array|mixed $data
	 * @param \DOMNode $xml
	 * @param string|NULL $previousKey
	 */
	private function toXml($data, \DOMNode $xml, $previousKey = NULL)
	{
		if (is_array($data) || $data instanceof Traversable) {
			foreach ($data as $key => $value) {
				$node = $xml;
				if (is_int($key)) {
					$node = $this->xml->createElement($previousKey);
					$xml->appendChild($node);
				} else if (!Arrays::isList($value)) {
					$node = $this->xml->createElement($key);
					$xml->appendChild($node);
				}
				$this->toXml($value, $node, is_string($key) ? $key : $previousKey);
			}
		} else {
			$xml->appendChild($this->xml->createTextNode($data));
		}
	}

}
